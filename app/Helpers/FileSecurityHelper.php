<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileSecurityHelper
{
    /**
     * Advanced malware signatures and patterns
     */
    private const EXTENDED_MALWARE_SIGNATURES = [
        // EICAR test string
        'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*',
        
        // PHP malicious patterns
        'eval(',
        'exec(',
        'system(',
        'shell_exec(',
        'passthru(',
        'base64_decode(',
        'file_get_contents(',
        'file_put_contents(',
        'fopen(',
        'fwrite(',
        'include(',
        'require(',
        'preg_replace(',
        'create_function(',
        
        // JavaScript malicious patterns
        '<script',
        'javascript:',
        'vbscript:',
        'onload=',
        'onerror=',
        'onclick=',
        'onmouseover=',
        
        // SQL injection patterns
        'union select',
        'drop table',
        'delete from',
        'insert into',
        'update set',
    ];

    /**
     * Text-based file signatures (only scan these in text files)
     */
    private const TEXT_BASED_SIGNATURES = [
        // Command injection patterns (only dangerous in text/script files)
        '&&',
        '||',
        ';rm',
        ';cat',
        ';ls',
        '`cat',
        '$(cat',
        
        // File inclusion patterns (only dangerous in text/script files)
        '../',
        '..\\',
        '/etc/passwd',
        '/etc/shadow',
        'C:\\Windows',
        'C:\\Users',
    ];

    /**
     * Binary file header signatures (only check at file start)
     */
    private const BINARY_HEADER_SIGNATURES = [
        'MZ', // PE executable (only dangerous at file start)
    ];

    /**
     * Archive signatures (context-dependent)
     */
    private const ARCHIVE_SIGNATURES = [
        'PK', // ZIP archive (only suspicious in non-archive files)
    ];

    /**
     * Suspicious file extensions that should be blocked
     */
    private const BLOCKED_EXTENSIONS = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
        'app', 'deb', 'pkg', 'rpm', 'dmg', 'iso', 'msi', 'run',
        'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp',
        'pl', 'py', 'rb', 'sh', 'cgi', 'htaccess', 'htpasswd'
    ];

    /**
     * Maximum file sizes by type (in bytes)
     */
    private const MAX_FILE_SIZES = [
        'image' => 10485760,    // 10MB for images
        'video' => 104857600,   // 100MB for videos
        'audio' => 52428800,    // 50MB for audio
        'document' => 52428800, // 50MB for documents
        'archive' => 104857600, // 100MB for archives
        'default' => 52428800,  // 50MB default
    ];

    /**
     * Perform comprehensive security scan on uploaded file
     */
    public static function scanFile(UploadedFile $file): array
    {
        $results = [
            'safe' => true,
            'threats' => [],
            'warnings' => [],
            'info' => []
        ];

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::BLOCKED_EXTENSIONS)) {
            $results['safe'] = false;
            $results['threats'][] = "Blocked file extension: {$extension}";
        }

        // Check file size based on type
        $fileType = self::getFileTypeCategory($file->getMimeType());
        $maxSize = self::MAX_FILE_SIZES[$fileType] ?? self::MAX_FILE_SIZES['default'];
        
        if ($file->getSize() > $maxSize) {
            $results['safe'] = false;
            $results['threats'][] = "File size exceeds limit for {$fileType} files";
        }

        // Scan file content for malware signatures
        // For large files (>5MB), only scan first 1MB to avoid memory/timeout issues
        $fileSize = $file->getSize();
        $maxScanSize = 1048576; // 1MB
        
        try {
            if ($fileSize > 5242880) { // 5MB
                // Only read first 1MB for large files
                $handle = fopen($file->getRealPath(), 'rb');
                if (!$handle) {
                    Log::error('FileSecurityHelper: Cannot open file for scanning', [
                        'filename' => $file->getClientOriginalName(),
                        'file_path' => $file->getRealPath(),
                    ]);
                    $results['warnings'][] = 'Could not scan file content - file access error';
                    $content = '';
                } else {
                    $content = fread($handle, $maxScanSize);
                    fclose($handle);
                    
                    Log::info('FileSecurityHelper: Scanning large file - reading first 1MB only', [
                        'filename' => $file->getClientOriginalName(),
                        'total_size' => $fileSize,
                        'scanned_size' => strlen($content),
                    ]);
                }
            } else {
                // For smaller files, read entire content
                $content = @file_get_contents($file->getRealPath());
                if ($content === false) {
                    Log::error('FileSecurityHelper: Cannot read file content', [
                        'filename' => $file->getClientOriginalName(),
                        'file_path' => $file->getRealPath(),
                        'file_size' => $fileSize,
                    ]);
                    $results['warnings'][] = 'Could not scan file content - file read error';
                    $content = '';
                }
            }
        } catch (\Exception $e) {
            Log::error('FileSecurityHelper: Exception while reading file', [
                'filename' => $file->getClientOriginalName(),
                'exception' => $e->getMessage(),
            ]);
            $results['warnings'][] = 'Could not scan file content - exception occurred';
            $content = '';
        }
        
        $mimeType = $file->getMimeType();
        $isTextFile = self::isTextBasedFile($mimeType);
        $isBinaryFile = self::isBinaryFile($mimeType);
        
        // Always scan for universal malware signatures (if we have content)
        if (!empty($content)) {
            foreach (self::EXTENDED_MALWARE_SIGNATURES as $signature) {
                if (stripos($content, $signature) !== false) {
                    $results['safe'] = false;
                    $results['threats'][] = "Malware signature detected: {$signature}";
                    
                    Log::critical('Malware detected in file upload', [
                        'signature' => $signature,
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip()
                    ]);
                }
            }
        }
        
        // Only scan for text-based signatures in text files (if we have content)
        if ($isTextFile && !empty($content)) {
            foreach (self::TEXT_BASED_SIGNATURES as $signature) {
                if (stripos($content, $signature) !== false) {
                    $results['safe'] = false;
                    $results['threats'][] = "Command injection signature detected: {$signature}";
                    
                    Log::critical('Command injection detected in text file', [
                        'signature' => $signature,
                        'filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip()
                    ]);
                }
            }
        }
        
        // Check binary headers only at the start of files (if we have content)
        if (!empty($content)) {
            $header = substr($content, 0, 10);
            foreach (self::BINARY_HEADER_SIGNATURES as $signature) {
                if (strpos($header, $signature) === 0) {
                    $results['safe'] = false;
                    $results['threats'][] = "Executable file header detected: {$signature}";
                    
                    Log::critical('Executable file detected', [
                        'signature' => $signature,
                        'filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'user_id' => auth()->id(),
                        'ip_address' => request()->ip()
                    ]);
                }
            }
        }
        
        // Check archive signatures only in non-archive files (if we have content)
        if (!empty($content) && !self::isArchiveFile($mimeType)) {
            foreach (self::ARCHIVE_SIGNATURES as $signature) {
                if (strpos($content, $signature) !== false) {
                    $results['warnings'][] = "Archive signature found in non-archive file: {$signature}";
                }
            }
        }

        // Check for suspicious file headers (if we have content)
        if (!empty($content)) {
            $header = substr($content, 0, 10);
            if (self::hasSuspiciousHeader($header)) {
                $results['warnings'][] = 'File has suspicious header signature';
            }
        }

        // Validate MIME type consistency
        $expectedMimeType = self::getExpectedMimeType($extension);
        if ($expectedMimeType && $file->getMimeType() !== $expectedMimeType) {
            $results['warnings'][] = "MIME type mismatch: expected {$expectedMimeType}, got {$file->getMimeType()}";
        }

        // Check for embedded files (polyglot attacks)
        // Only check if we have the full file content (small files) and content is available
        if (!empty($content) && $fileSize <= 5242880 && self::hasEmbeddedFiles($content)) {
            $results['warnings'][] = 'File may contain embedded content';
        }

        // File entropy analysis (high entropy might indicate encryption/obfuscation)
        // Be less strict with binary files like images which naturally have high entropy
        // For large files, entropy is calculated on the scanned portion only
        // Only calculate if we have content
        if (!empty($content)) {
            $entropy = self::calculateEntropy($content);
            $entropyThreshold = self::isBinaryFile($file->getMimeType()) ? 8.0 : 7.5;
            
            if ($entropy > $entropyThreshold) {
                $results['warnings'][] = 'High entropy detected - file may be encrypted or obfuscated';
            }
            
            $results['info']['entropy'] = round($entropy, 2);
        } else {
            $results['info']['entropy'] = 'N/A';
        }

        $results['info'] = [
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'entropy' => $results['info']['entropy'] ?? 'N/A',
            'file_type_category' => $fileType,
            'content_scanned' => !empty($content),
            'scan_size' => !empty($content) ? strlen($content) : 0,
        ];

        return $results;
    }

    /**
     * Get file type category based on MIME type
     */
    private static function getFileTypeCategory(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        
        if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'application/rtf'
        ])) {
            return 'document';
        }
        
        if (in_array($mimeType, [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed'
        ])) {
            return 'archive';
        }
        
        return 'default';
    }

    /**
     * Check for suspicious file headers
     */
    private static function hasSuspiciousHeader(string $header): bool
    {
        $suspiciousHeaders = [
            'MZ',      // PE executable
            '\x7fELF', // ELF executable
            '\xca\xfe\xba\xbe', // Java class file
        ];

        foreach ($suspiciousHeaders as $suspiciousHeader) {
            if (str_starts_with($header, $suspiciousHeader)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get expected MIME type for file extension
     */
    private static function getExpectedMimeType(string $extension): ?string
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'zip' => 'application/zip',
        ];

        return $mimeTypes[$extension] ?? null;
    }

    /**
     * Check for embedded files (simple polyglot detection)
     */
    private static function hasEmbeddedFiles(string $content): bool
    {
        // Look for multiple file signatures in the same file
        // But be smarter about legitimate formats that may contain these signatures
        $signatures = [
            'PK' => 'ZIP/Office',
            'MZ' => 'Executable', 
            '\x89PNG' => 'PNG',
            '\xff\xd8\xff' => 'JPEG',
            'GIF8' => 'GIF',
            'PDF' => 'PDF'
        ];
        
        $foundSignatures = [];
        
        foreach ($signatures as $signature => $type) {
            if (strpos($content, $signature) !== false) {
                $foundSignatures[] = $type;
            }
        }
        
        // Only flag as suspicious if we find incompatible signatures
        // For example, executable + image, or multiple different image formats
        $suspiciousCombinations = [
            ['Executable', 'PNG'],
            ['Executable', 'JPEG'], 
            ['Executable', 'GIF'],
            ['PNG', 'JPEG', 'GIF'], // Multiple image formats in one file
        ];
        
        foreach ($suspiciousCombinations as $combination) {
            if (count(array_intersect($foundSignatures, $combination)) >= count($combination)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Calculate Shannon entropy of file content
     */
    private static function calculateEntropy(string $data): float
    {
        $frequencies = array_count_values(str_split($data));
        $length = strlen($data);
        $entropy = 0.0;

        foreach ($frequencies as $frequency) {
            $probability = $frequency / $length;
            $entropy -= $probability * log($probability, 2);
        }

        return $entropy;
    }

    /**
     * Generate secure filename
     */
    public static function generateSecureFilename(string $originalName): string
    {
        $pathInfo = pathinfo($originalName);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
        $basename = $pathInfo['filename'] ?? 'file';
        
        // Remove potentially dangerous characters
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = trim($basename, '_-');
        
        // Ensure filename is not empty
        if (empty($basename)) {
            $basename = 'file';
        }
        
        // Add timestamp and random string for uniqueness
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return "{$basename}_{$timestamp}_{$random}" . ($extension ? ".{$extension}" : '');
    }

    /**
     * Check if file is text-based and should be scanned for text signatures
     */
    private static function isTextBasedFile(string $mimeType): bool
    {
        $textMimeTypes = [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
            'application/x-php',
            'application/x-httpd-php',
        ];
        
        return in_array($mimeType, $textMimeTypes) || str_starts_with($mimeType, 'text/');
    }
    
    /**
     * Check if file is binary and should have different scanning rules
     */
    private static function isBinaryFile(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/') || 
               str_starts_with($mimeType, 'video/') || 
               str_starts_with($mimeType, 'audio/') ||
               in_array($mimeType, [
                   'application/pdf',
                   'application/msword',
                   'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
               ]);
    }
    
    /**
     * Check if file is an archive format
     */
    private static function isArchiveFile(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/x-tar',
            'application/gzip'
        ]);
    }

    /**
     * Validate file against whitelist
     */
    public static function isFileTypeAllowed(string $extension, string $mimeType): bool
    {
        $allowedTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            '7z' => 'application/x-7z-compressed',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
        ];

        return isset($allowedTypes[$extension]) && $allowedTypes[$extension] === $mimeType;
    }
}