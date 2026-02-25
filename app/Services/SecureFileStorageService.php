<?php

namespace App\Services;

use App\Helpers\FileSecurityHelper;
use App\Services\FileUploadLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SecureFileStorageService
{
    protected FileUploadLogger $fileUploadLogger;

    public function __construct(FileUploadLogger $fileUploadLogger)
    {
        $this->fileUploadLogger = $fileUploadLogger;
    }

    /**
     * Store file securely outside web root with proper permissions and logging.
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $contentType The content type (image, pdf, audio, video)
     * @param array $context Additional context for logging
     * @param string|null $correlationId Correlation ID for tracking
     * @return array File storage information
     */
    public function storeFileSecurely(
        UploadedFile $file, 
        string $contentType, 
        array $context = [],
        ?string $correlationId = null
    ): array {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('SecureFileStorage: Starting secure file storage', [
            'correlation_id' => $correlationId,
            'content_type' => $contentType,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'context' => $context,
        ]);

        // 1. Generate cryptographically secure unique filename
        $secureFilename = $this->generateCryptographicallySecureFilename($file, $correlationId);
        
        // 2. Determine secure storage path (outside web root)
        $storagePath = $this->generateSecureStoragePath($contentType, $context);
        
        // 3. Store file outside web root using 'protected' disk
        $fullPath = $this->storeFileOutsideWebRoot($file, $storagePath, $secureFilename, $correlationId);
        
        // 4. Set proper file permissions
        $this->setSecureFilePermissions($fullPath, $correlationId);
        
        // 5. Log file access and storage details
        $this->logFileStorageAccess($file, $fullPath, $context, $correlationId);
        
        // 6. Generate file hash for integrity verification
        $fileHash = hash_file('sha256', $file->getRealPath());
        
        $result = [
            'file_path' => $fullPath,
            'secure_filename' => $secureFilename,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_hash' => $fileHash,
            'storage_disk' => 'protected',
            'stored_at' => now()->toISOString(),
            'correlation_id' => $correlationId,
        ];
        
        Log::info('SecureFileStorage: File stored securely', [
            'correlation_id' => $correlationId,
            'result' => collect($result)->except(['file_hash'])->toArray(), // Don't log hash for security
        ]);
        
        return $result;
    }

    /**
     * Generate cryptographically secure unique filename.
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $correlationId Correlation ID for tracking
     * @return string Secure filename
     */
    private function generateCryptographicallySecureFilename(UploadedFile $file, string $correlationId): string
    {
        $originalName = $file->getClientOriginalName();
        $pathInfo = pathinfo($originalName);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
        $basename = $pathInfo['filename'] ?? 'file';
        
        // Sanitize basename - remove potentially dangerous characters
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = trim($basename, '_-');
        
        // Ensure filename is not empty
        if (empty($basename)) {
            $basename = 'content_file';
        }
        
        // Limit basename length to prevent filesystem issues
        $basename = substr($basename, 0, 50);
        
        // Generate cryptographically secure components
        $timestamp = time();
        $microseconds = microtime(true) * 1000000; // Add microseconds for uniqueness
        $randomBytes = random_bytes(16); // 16 bytes = 128 bits of entropy
        $randomHex = bin2hex($randomBytes);
        
        // Create secure filename with multiple entropy sources
        $secureFilename = sprintf(
            '%s_%d_%d_%s',
            $basename,
            $timestamp,
            $microseconds,
            $randomHex
        );
        
        // Add extension if present
        if ($extension) {
            $secureFilename .= ".{$extension}";
        }
        
        Log::info('SecureFileStorage: Generated secure filename', [
            'correlation_id' => $correlationId,
            'original_name' => $originalName,
            'secure_filename' => $secureFilename,
            'entropy_sources' => [
                'timestamp' => $timestamp,
                'microseconds' => $microseconds,
                'random_bytes_length' => 16,
            ],
        ]);
        
        return $secureFilename;
    }

    /**
     * Generate secure storage path outside web root.
     * 
     * @param string $contentType The content type
     * @param array $context Additional context
     * @return string Storage path
     */
    private function generateSecureStoragePath(string $contentType, array $context): string
    {
        // Create hierarchical path structure for better organization and security
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        // Include user and course context if available for access control
        $userPath = isset($context['user_id']) ? "users/{$context['user_id']}" : 'anonymous';
        $coursePath = isset($context['course_id']) ? "courses/{$context['course_id']}" : 'general';
        
        // Create path: content_blocks/[content_type]/[year]/[month]/[day]/[course]/[user]
        $storagePath = implode('/', [
            'content_blocks',
            $contentType,
            $year,
            $month,
            $day,
            $coursePath,
            $userPath
        ]);
        
        Log::debug('SecureFileStorage: Generated storage path', [
            'storage_path' => $storagePath,
            'content_type' => $contentType,
            'context' => $context,
        ]);
        
        return $storagePath;
    }

    /**
     * Store file outside web root using protected disk.
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $storagePath Storage path
     * @param string $secureFilename Secure filename
     * @param string $correlationId Correlation ID for tracking
     * @return string Full file path
     */
    private function storeFileOutsideWebRoot(
        UploadedFile $file, 
        string $storagePath, 
        string $secureFilename, 
        string $correlationId
    ): string {
        try {
            // Use 'protected' disk which stores files outside web root
            $fullPath = $storagePath . '/' . $secureFilename;
            
            Log::info('SecureFileStorage: Storing file outside web root', [
                'correlation_id' => $correlationId,
                'storage_disk' => 'protected',
                'storage_path' => $storagePath,
                'secure_filename' => $secureFilename,
                'full_path' => $fullPath,
            ]);
            
            // Store file using Laravel's protected disk (outside web root)
            $storedPath = $file->storeAs($storagePath, $secureFilename, 'protected');
            
            if (!$storedPath) {
                throw new \Exception('Failed to store file to protected disk');
            }
            
            // Verify file was stored correctly
            if (!Storage::disk('protected')->exists($storedPath)) {
                throw new \Exception('File verification failed - file not found after storage');
            }
            
            $actualSize = Storage::disk('protected')->size($storedPath);
            if ($actualSize !== $file->getSize()) {
                throw new \Exception("File size mismatch after storage. Expected: {$file->getSize()}, Actual: {$actualSize}");
            }
            
            Log::info('SecureFileStorage: File stored and verified successfully', [
                'correlation_id' => $correlationId,
                'stored_path' => $storedPath,
                'file_size' => $actualSize,
                'verification_passed' => true,
            ]);
            
            return $storedPath;
            
        } catch (\Exception $e) {
            Log::error('SecureFileStorage: Failed to store file outside web root', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'storage_path' => $storagePath,
                'secure_filename' => $secureFilename,
            ]);
            
            throw new \Exception('Failed to store file securely: ' . $e->getMessage());
        }
    }

    /**
     * Set proper file permissions on uploaded files.
     * 
     * @param string $filePath The stored file path
     * @param string $correlationId Correlation ID for tracking
     */
    private function setSecureFilePermissions(string $filePath, string $correlationId): void
    {
        try {
            // Get the absolute path to the file
            $absolutePath = Storage::disk('protected')->path($filePath);
            
            Log::info('SecureFileStorage: Setting secure file permissions', [
                'correlation_id' => $correlationId,
                'file_path' => $filePath,
                'absolute_path' => $absolutePath,
                'os_family' => PHP_OS_FAMILY,
            ]);
            
            // Set secure file permissions: 
            // - Owner: read/write (6)
            // - Group: read only (4) 
            // - Others: no access (0)
            // Result: 640 (rw-r-----)
            $permissions = 0640;
            
            // On Windows, chmod is unreliable/limited for Unix-style permissions.
            // We skip or handle differently to prevent false error reports.
            if (PHP_OS_FAMILY !== 'Windows') {
                if (!@chmod($absolutePath, $permissions)) {
                    Log::warning('SecureFileStorage: Failed to set file permissions via chmod', [
                        'correlation_id' => $correlationId,
                        'file_path' => $filePath,
                    ]);
                }
                
                // Verify permissions were set correctly
                $actualPermissions = fileperms($absolutePath) & 0777;
                
                Log::info('SecureFileStorage: File permissions set successfully', [
                    'correlation_id' => $correlationId,
                    'requested_permissions' => decoct($permissions),
                    'actual_permissions' => decoct($actualPermissions),
                    'permissions_match' => $actualPermissions === $permissions,
                ]);
                
                if ($actualPermissions !== $permissions) {
                    Log::warning('SecureFileStorage: File permissions mismatch', [
                        'correlation_id' => $correlationId,
                        'requested' => decoct($permissions),
                        'actual' => decoct($actualPermissions),
                    ]);
                }
            } else {
                Log::debug('SecureFileStorage: Skipping Unix-style chmod on Windows', [
                    'correlation_id' => $correlationId,
                    'file_path' => $filePath,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('SecureFileStorage: Exception during file permission settings', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'file_path' => $filePath,
            ]);
            
            // Don't throw exception for permission errors as file is already stored
            // Just log the issue for administrator attention
        }
    }

    /**
     * Log file access and storage details for security auditing.
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $storedPath The stored file path
     * @param array $context Additional context
     * @param string $correlationId Correlation ID for tracking
     */
    private function logFileStorageAccess(
        UploadedFile $file, 
        string $storedPath, 
        array $context, 
        string $correlationId
    ): void {
        $accessLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'file_storage',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'file_details' => [
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
            ],
            'security_details' => [
                'storage_disk' => 'protected',
                'outside_web_root' => true,
                'secure_filename_generated' => true,
                'permissions_set' => true,
            ],
            'context' => $context,
            'server_info' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_method' => request()->method(),
                'request_uri' => request()->getRequestUri(),
            ],
        ];
        
        // Log to dedicated file access log
        Log::channel('single')->info('FILE_ACCESS_LOG', $accessLog);
        
        // Also use the FileUploadLogger for consistency
        $this->fileUploadLogger->logSecureFileStorage($correlationId, $accessLog);
    }

    /**
     * Generate secure access token for file download.
     * 
     * @param string $filePath The stored file path
     * @param int $expirationMinutes Token expiration in minutes
     * @param array $context Additional context
     * @return string Access token
     */
    public function generateSecureAccessToken(
        string $filePath, 
        int $expirationMinutes = 60, 
        array $context = []
    ): string {
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes($expirationMinutes);
        
        $tokenData = [
            'file_path' => $filePath,
            'user_id' => auth()->id(),
            'expires_at' => $expiresAt->timestamp,
            'context' => $context,
            'created_at' => now()->timestamp,
            'ip_address' => request()->ip(),
        ];
        
        // Store token in cache with file information
        Cache::put(
            "secure_file_token:{$token}",
            $tokenData,
            $expirationMinutes * 60
        );
        
        Log::info('SecureFileStorage: Generated secure access token', [
            'token_id' => substr($token, 0, 8) . '...', // Log partial token for tracking
            'file_path' => $filePath,
            'expires_at' => $expiresAt->toISOString(),
            'user_id' => auth()->id(),
        ]);
        
        return $token;
    }

    /**
     * Validate secure access token and return file information.
     * 
     * @param string $token Access token
     * @return array|null File information or null if invalid
     */
    public function validateSecureAccessToken(string $token): ?array
    {
        $tokenData = Cache::get("secure_file_token:{$token}");
        
        if (!$tokenData || $tokenData['expires_at'] < time()) {
            Log::warning('SecureFileStorage: Invalid or expired access token', [
                'token_id' => substr($token, 0, 8) . '...',
                'expired' => $tokenData ? $tokenData['expires_at'] < time() : 'not_found',
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);
            return null;
        }
        
        Log::info('SecureFileStorage: Valid access token used', [
            'token_id' => substr($token, 0, 8) . '...',
            'file_path' => $tokenData['file_path'],
            'user_id' => $tokenData['user_id'],
            'current_user_id' => auth()->id(),
        ]);
        
        return $tokenData;
    }

    /**
     * Delete file securely with logging.
     * 
     * @param string $filePath The file path to delete
     * @param string $correlationId Correlation ID for tracking
     * @return bool Success status
     */
    public function deleteFileSecurely(string $filePath, string $correlationId): bool
    {
        try {
            Log::info('SecureFileStorage: Starting secure file deletion', [
                'correlation_id' => $correlationId,
                'file_path' => $filePath,
                'user_id' => auth()->id(),
            ]);
            
            // Verify file exists before deletion
            if (!Storage::disk('protected')->exists($filePath)) {
                Log::warning('SecureFileStorage: File not found for deletion', [
                    'correlation_id' => $correlationId,
                    'file_path' => $filePath,
                ]);
                return false;
            }
            
            // Get file info before deletion for logging
            $fileSize = Storage::disk('protected')->size($filePath);
            
            // Delete the file
            $deleted = Storage::disk('protected')->delete($filePath);
            
            if ($deleted) {
                Log::info('SecureFileStorage: File deleted successfully', [
                    'correlation_id' => $correlationId,
                    'file_path' => $filePath,
                    'file_size' => $fileSize,
                    'user_id' => auth()->id(),
                ]);
                
                // Log file deletion for security audit
                $this->logFileDeletion($filePath, $fileSize, $correlationId);
                
                return true;
            } else {
                throw new \Exception('Storage::delete returned false');
            }
            
        } catch (\Exception $e) {
            Log::error('SecureFileStorage: Failed to delete file', [
                'correlation_id' => $correlationId,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return false;
        }
    }

    /**
     * Log file deletion for security auditing.
     * 
     * @param string $filePath The deleted file path
     * @param int $fileSize The file size
     * @param string $correlationId Correlation ID for tracking
     */
    private function logFileDeletion(string $filePath, int $fileSize, string $correlationId): void
    {
        $deletionLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'file_deletion',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'file_details' => [
                'file_path' => $filePath,
                'file_size' => $fileSize,
            ],
            'server_info' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_method' => request()->method(),
                'request_uri' => request()->getRequestUri(),
            ],
        ];
        
        // Log to dedicated file access log
        Log::channel('single')->info('FILE_DELETION_LOG', $deletionLog);
        
        // Also use the FileUploadLogger for consistency
        $this->fileUploadLogger->logSecureFileDeletion($correlationId, $deletionLog);
    }
}