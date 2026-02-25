<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\FileSecurityHelper;

class FileSecurityService
{
    /**
     * Allowed file extensions for assignment submissions
     */
    private array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'txt', 'rtf',
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'zip', 'rar', '7z',
        'ppt', 'pptx', 'xls', 'xlsx', 'csv'
    ];

    /**
     * Allowed MIME types mapped to extensions
     */
    private array $allowedMimeTypes = [
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'text/plain' => ['txt'],
        'application/rtf' => ['rtf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'application/zip' => ['zip'],
        'application/vnd.rar' => ['rar'],
        'application/x-rar-compressed' => ['rar'],
        'application/x-7z-compressed' => ['7z'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'text/csv' => ['csv'],
    ];

    /**
     * Maximum file size per file (in bytes) - default 10MB
     */
    private int $maxFileSize;

    /**
     * Maximum total submission size (in bytes) - default 50MB
     */
    private int $maxTotalSize;

    public function __construct()
    {
        $this->maxFileSize = config('assignments.max_file_size', 10485760); // 10MB
        $this->maxTotalSize = config('assignments.max_total_size', 52428800); // 50MB
    }

    /**
     * Validate an uploaded file against all security rules
     *
     * @param UploadedFile $file
     * @param int $currentTotalSize Current total size of all files in submission
     * @return ValidationResult
     */
    public function validateFile(UploadedFile $file, int $currentTotalSize = 0): ValidationResult
    {
        $result = new ValidationResult();

        // 1. Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!$this->validateExtension($extension)) {
            $result->addError('file_extension', 
                "File type not allowed. Allowed types: " . implode(', ', $this->allowedExtensions));
            return $result;
        }

        // 2. Validate MIME type matches extension
        $mimeType = $file->getMimeType();
        if (!$this->validateMimeType($extension, $mimeType)) {
            $result->addError('mime_type', 
                "File type does not match extension. Possible security risk detected.");
            return $result;
        }

        // 3. Check file size
        if (!$this->validateFileSize($file->getSize())) {
            $maxSizeMB = round($this->maxFileSize / 1048576, 2);
            $result->addError('file_size', 
                "File size exceeds limit. Maximum: {$maxSizeMB}MB");
            return $result;
        }

        // 4. Check total submission size
        $newTotalSize = $currentTotalSize + $file->getSize();
        if (!$this->validateTotalSize($newTotalSize)) {
            $maxTotalMB = round($this->maxTotalSize / 1048576, 2);
            $result->addError('total_size', 
                "Total submission size exceeds limit. Maximum: {$maxTotalMB}MB");
            return $result;
        }

        // 5. Scan for malicious content
        $scanResult = $this->scanForMalware($file);
        if (!$scanResult['safe']) {
            $result->addError('malware', 
                "File contains potentially malicious content and cannot be uploaded.");
            
            // Log security incident
            Log::critical('Malicious file upload attempt blocked', [
                'filename' => $file->getClientOriginalName(),
                'threats' => $scanResult['threats'],
                'user_id' => auth()->id(),
                'ip_address' => request()->ip()
            ]);
            
            return $result;
        }

        // Add warnings if any
        if (!empty($scanResult['warnings'])) {
            foreach ($scanResult['warnings'] as $warning) {
                $result->addWarning($warning);
            }
        }

        $result->setValid(true);
        return $result;
    }

    /**
     * Validate file extension against whitelist
     */
    private function validateExtension(string $extension): bool
    {
        return in_array($extension, $this->allowedExtensions);
    }

    /**
     * Validate MIME type matches the file extension
     */
    private function validateMimeType(string $extension, string $mimeType): bool
    {
        // Check if MIME type is in allowed list
        if (!isset($this->allowedMimeTypes[$mimeType])) {
            return false;
        }

        // Check if extension matches the MIME type
        return in_array($extension, $this->allowedMimeTypes[$mimeType]);
    }

    /**
     * Validate file size
     */
    private function validateFileSize(int $fileSize): bool
    {
        return $fileSize <= $this->maxFileSize;
    }

    /**
     * Validate total submission size
     */
    private function validateTotalSize(int $totalSize): bool
    {
        return $totalSize <= $this->maxTotalSize;
    }

    /**
     * Scan file for malicious patterns using FileSecurityHelper
     */
    public function scanForMalware(UploadedFile $file): array
    {
        return FileSecurityHelper::scanFile($file);
    }

    /**
     * Generate a secure hashed filename
     */
    public function generateSecureFilename(UploadedFile $file): string
    {
        return FileSecurityHelper::generateSecureFilename($file->getClientOriginalName());
    }

    /**
     * Store file securely outside public web root
     *
     * @param UploadedFile $file
     * @param string $directory Directory within storage/app (e.g., 'assignments/submissions')
     * @return string Stored file path
     */
    public function storeSecurely(UploadedFile $file, string $directory = 'assignments'): string
    {
        $secureFilename = $this->generateSecureFilename($file);
        $path = $file->storeAs($directory, $secureFilename, 'local');
        
        Log::info('File stored securely', [
            'original_name' => $file->getClientOriginalName(),
            'secure_name' => $secureFilename,
            'path' => $path,
            'size' => $file->getSize(),
            'user_id' => auth()->id()
        ]);
        
        return $path;
    }

    /**
     * Clean up a failed upload
     */
    public function cleanupFailedUpload(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            
            Log::info('Failed upload cleaned up', [
                'path' => $path,
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Generate a signed URL for secure file access
     *
     * @param string $path File path in storage
     * @param int $expirationMinutes Expiration time in minutes (default 60)
     * @return string Signed URL
     */
    public function generateSignedUrl(string $path, int $expirationMinutes = 60): string
    {
        // Generate a signed route URL that expires
        return url()->temporarySignedRoute(
            'assignments.files.download',
            now()->addMinutes($expirationMinutes),
            ['path' => encrypt($path)]
        );
    }

    /**
     * Verify user has permission to access a file
     *
     * @param string $filePath File path in storage
     * @param int $userId User ID attempting access
     * @return bool
     */
    public function authorizeFileAccess(string $filePath, int $userId): bool
    {
        // This will be implemented with proper authorization logic
        // For now, return true - will be enhanced in sub-task 2.7
        return true;
    }

    /**
     * Get allowed extensions
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Get max file size in bytes
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * Get max total size in bytes
     */
    public function getMaxTotalSize(): int
    {
        return $this->maxTotalSize;
    }
}

/**
 * Validation result class
 */
class ValidationResult
{
    private bool $valid = false;
    private array $errors = [];
    private array $warnings = [];

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function addError(string $key, string $message): void
    {
        $this->errors[$key] = $message;
        $this->valid = false;
    }

    public function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
}
