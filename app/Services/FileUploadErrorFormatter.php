<?php

namespace App\Services;

/**
 * FileUploadErrorFormatter provides standardized error message formatting for file upload issues.
 * 
 * This class centralizes error message formatting to ensure consistent, user-friendly error messages
 * across the application. It provides context-specific error messages with helpful suggestions
 * for resolving issues and formats file sizes in human-readable format.
 * 
 * Requirements: 1.1, 1.2, 1.3
 */
class FileUploadErrorFormatter
{
    /**
     * Format a file upload error with context-specific messaging.
     * 
     * @param string $errorType The type of error (size_exceeded, invalid_extension, etc.)
     * @param array $context Additional context for the error message
     * @return string Formatted error message with helpful suggestions
     */
    public static function formatError(string $errorType, array $context = []): string
    {
        switch ($errorType) {
            case 'size_exceeded':
                return self::formatSizeExceededError($context);
                
            case 'invalid_extension':
                return self::formatInvalidExtensionError($context);
                
            case 'mime_mismatch':
                return self::formatMimeMismatchError($context);
                
            case 'server_config':
                return self::formatServerConfigError($context);
                
            case 'insufficient_space':
                return self::formatInsufficientSpaceError($context);
                
            case 'php_upload_error':
                return self::formatPhpUploadError($context);
                
            case 'empty_file':
                return self::formatEmptyFileError($context);
                
            case 'invalid_file':
                return self::formatInvalidFileError($context);
                
            case 'memory_limit':
                return self::formatMemoryLimitError($context);
                
            case 'timeout_risk':
                return self::formatTimeoutRiskError($context);
                
            case 'image_validation':
                return self::formatImageValidationError($context);
                
            case 'pdf_validation':
                return self::formatPdfValidationError($context);
                
            case 'content_header_mismatch':
                return self::formatContentHeaderMismatchError($context);
                
            case 'strict_mime_validation':
                return self::formatStrictMimeValidationError($context);
                
            case 'header_mismatch':
                return self::formatHeaderMismatchError($context);
                
            case 'executable_detected':
                return self::formatExecutableDetectedError($context);
                
            case 'script_content_detected':
                return self::formatScriptContentDetectedError($context);
                
            case 'security_threat':
                return self::formatSecurityThreatError($context);
                
            case 'security_warning':
                return self::formatSecurityWarningError($context);
                
            case 'virus_detected':
                return self::formatVirusDetectedError($context);
                
            case 'test_virus_detected':
                return self::formatTestVirusDetectedError($context);
                
            default:
                return self::formatGenericError($context);
        }
    }
    
    /**
     * Format file size exceeded error with actual vs allowed sizes.
     * 
     * @param array $context Must contain 'actual_size', 'max_size', and optionally 'content_type'
     * @return string Formatted error message
     */
    private static function formatSizeExceededError(array $context): string
    {
        $actualSize = self::formatBytes($context['actual_size'] ?? 0);
        $maxSize = self::formatBytes($context['max_size'] ?? 0);
        $contentType = $context['content_type'] ?? 'file';
        
        return "File is too large ({$actualSize}). Maximum allowed size for {$contentType} files is {$maxSize}. " .
               "Try compressing the file or splitting it into smaller parts.";
    }
    
    /**
     * Format invalid file extension error with allowed types.
     * 
     * @param array $context Must contain 'extension' and 'allowed_types'
     * @return string Formatted error message
     */
    private static function formatInvalidExtensionError(array $context): string
    {
        $extension = $context['extension'] ?? 'unknown';
        $allowedTypes = $context['allowed_types'] ?? 'none specified';
        $contentType = $context['content_type'] ?? 'content';
        
        return "File type '.{$extension}' is not supported for {$contentType}. " .
               "Please use one of these file types: {$allowedTypes}";
    }
    
    /**
     * Format MIME type mismatch error.
     * 
     * @param array $context May contain 'file_mime', 'expected_mimes', 'extension'
     * @return string Formatted error message
     */
    private static function formatMimeMismatchError(array $context): string
    {
        $fileMime = $context['file_mime'] ?? 'unknown';
        $clientMime = $context['client_mime'] ?? null;
        $extension = $context['extension'] ?? 'unknown';
        $securityReason = $context['security_reason'] ?? null;
        
        $baseMessage = "File appears to be corrupted or has an incorrect extension. " .
                      "The file content (MIME type: {$fileMime}) doesn't match the '.{$extension}' extension.";
        
        if ($clientMime && $clientMime !== $fileMime) {
            $baseMessage .= " Browser detected: {$clientMime}, Server detected: {$fileMime}.";
        }
        
        if ($securityReason) {
            $baseMessage .= " Security concern: {$securityReason}.";
        }
        
        return $baseMessage . " Please check the file and ensure it's not corrupted, or try saving it with the correct extension.";
    }
    
    /**
     * Format content header mismatch error for strict file validation.
     * 
     * @param array $context Must contain file validation details
     * @return string Formatted error message
     */
    private static function formatContentHeaderMismatchError(array $context): string
    {
        $fileExtension = $context['file_extension'] ?? 'unknown';
        $detectedTypes = $context['detected_types'] ?? 'unknown';
        $headerSignature = $context['header_signature'] ?? 'unknown';
        $securityReason = $context['security_reason'] ?? 'File content does not match extension';
        
        return "File content validation failed. " .
               "The file has a '.{$fileExtension}' extension but the content appears to be: {$detectedTypes}. " .
               "File signature: {$headerSignature}. " .
               "{$securityReason}. " .
               "This may indicate file corruption or an attempt to bypass file type restrictions. " .
               "Please ensure the file is saved with the correct extension and is not corrupted.";
    }
    
    /**
     * Format strict MIME validation error.
     * 
     * @param array $context May contain strict validation details
     * @return string Formatted error message
     */
    private static function formatStrictMimeValidationError(array $context): string
    {
        $fileMime = $context['file_mime'] ?? 'unknown';
        $expectedMimes = $context['expected_mimes'] ?? 'unknown';
        $extension = $context['extension'] ?? 'unknown';
        $validationLevel = $context['validation_level'] ?? 'strict';
        
        return "Strict file type validation failed. " .
               "File extension '.{$extension}' requires MIME type to be one of: {$expectedMimes}, " .
               "but file has MIME type: {$fileMime}. " .
               "This {$validationLevel} validation prevents file type spoofing and ensures security. " .
               "Please ensure your file is saved in the correct format and is not corrupted.";
    }
    
    /**
     * Format server configuration error with actionable guidance.
     * 
     * @param array $context May contain 'config_issue', 'current_limit', 'recommended_action'
     * @return string Formatted error message
     */
    private static function formatServerConfigError(array $context): string
    {
        $configIssue = $context['config_issue'] ?? 'Server configuration prevents this upload';
        $recommendedAction = $context['recommended_action'] ?? 'Contact your administrator';
        
        return "{$configIssue}. {$recommendedAction} to resolve this server configuration issue.";
    }
    
    /**
     * Format insufficient disk space error.
     * 
     * @param array $context May contain 'available_space', 'required_space', 'location'
     * @return string Formatted error message
     */
    private static function formatInsufficientSpaceError(array $context): string
    {
        $availableSpace = isset($context['available_space']) ? self::formatBytes($context['available_space']) : 'unknown';
        $requiredSpace = isset($context['required_space']) ? self::formatBytes($context['required_space']) : 'unknown';
        $location = $context['location'] ?? 'server';
        
        return "Insufficient storage space on {$location}. " .
               "Available: {$availableSpace}, Required: {$requiredSpace}. " .
               "Contact your administrator to free up disk space or try uploading a smaller file.";
    }
    
    /**
     * Format PHP upload error with configuration guidance.
     * 
     * @param array $context Must contain 'error_code' and may contain server config details
     * @return string Formatted error message
     */
    private static function formatPhpUploadError(array $context): string
    {
        $errorCode = $context['error_code'] ?? UPLOAD_ERR_OK;
        $uploadMaxFilesize = $context['upload_max_filesize'] ?? ini_get('upload_max_filesize');
        $postMaxSize = $context['post_max_size'] ?? ini_get('post_max_size');
        $uploadTmpDir = $context['upload_tmp_dir'] ?? (ini_get('upload_tmp_dir') ?: sys_get_temp_dir());
        
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "File exceeds server upload limit. " .
                       "Current limit: {$uploadMaxFilesize} (upload_max_filesize). " .
                       "Contact your administrator to increase this limit in PHP configuration.";
                       
            case UPLOAD_ERR_FORM_SIZE:
                return "File exceeds form upload limit. " .
                       "The form specified a smaller limit than the server allows. " .
                       "Server limits: upload_max_filesize={$uploadMaxFilesize}, post_max_size={$postMaxSize}.";
                       
            case UPLOAD_ERR_PARTIAL:
                return "File upload was interrupted and only partially completed. " .
                       "This usually indicates a network interruption or timeout. " .
                       "Please check your internet connection and try uploading again. " .
                       "For large files, consider splitting into smaller parts.";
                       
            case UPLOAD_ERR_NO_FILE:
                return "No file was selected for upload. Please choose a file and try again.";
                
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Server configuration error: temporary directory is missing. " .
                       "Current upload_tmp_dir setting: '{$uploadTmpDir}'. " .
                       "Contact your administrator to configure a valid temporary directory in PHP settings.";
                       
            case UPLOAD_ERR_CANT_WRITE:
                return "Server cannot write uploaded file to disk. " .
                       "This may be due to insufficient disk space or permission issues. " .
                       "Temporary directory: '{$uploadTmpDir}'. " .
                       "Contact your administrator to check disk space and directory permissions.";
                       
            case UPLOAD_ERR_EXTENSION:
                return "Upload blocked by server extension. " .
                       "A PHP extension has prevented the file upload. " .
                       "Contact your administrator to check PHP extension configuration and security settings.";
                       
            default:
                $memoryLimit = $context['memory_limit'] ?? ini_get('memory_limit');
                return "Unknown upload error (code: {$errorCode}). " .
                       "Server configuration: upload_max_filesize={$uploadMaxFilesize}, " .
                       "post_max_size={$postMaxSize}, memory_limit={$memoryLimit}. " .
                       "Contact your administrator for assistance.";
        }
    }
    
    /**
     * Format empty file error.
     * 
     * @param array $context May contain 'file_name'
     * @return string Formatted error message
     */
    private static function formatEmptyFileError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the selected file';
        
        return "File '{$fileName}' is empty (0 bytes). " .
               "Please select a valid file with content or check if the file was corrupted during transfer.";
    }
    
    /**
     * Format invalid file error.
     * 
     * @param array $context May contain 'file_name', 'reason'
     * @return string Formatted error message
     */
    private static function formatInvalidFileError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the selected file';
        $reason = $context['reason'] ?? 'corrupted or invalid';
        
        return "File '{$fileName}' is {$reason}. " .
               "Please try uploading the file again or check if the file was corrupted during transfer.";
    }
    
    /**
     * Format memory limit error for large file processing.
     * 
     * @param array $context May contain file size and memory information
     * @return string Formatted error message
     */
    private static function formatMemoryLimitError(array $context): string
    {
        $fileSize = isset($context['file_size']) ? self::formatBytes($context['file_size']) : 'unknown';
        $availableMemory = isset($context['available_memory']) ? self::formatBytes($context['available_memory']) : 'unknown';
        $requiredMemory = isset($context['required_memory']) ? self::formatBytes($context['required_memory']) : 'unknown';
        $memoryLimit = isset($context['memory_limit']) ? self::formatBytes($context['memory_limit']) : 'unknown';
        
        return "Insufficient memory for processing this upload. " .
               "File size: {$fileSize}, Available memory: {$availableMemory}, Required memory: {$requiredMemory}. " .
               "Try uploading a smaller file or contact your administrator to increase memory_limit (current: {$memoryLimit}).";
    }
    
    /**
     * Format timeout risk error for large files.
     * 
     * @param array $context May contain file size and execution time information
     * @return string Formatted error message
     */
    private static function formatTimeoutRiskError(array $context): string
    {
        $fileSize = isset($context['file_size']) ? self::formatBytes($context['file_size']) : 'unknown';
        $maxExecutionTime = $context['max_execution_time'] ?? 'unknown';
        
        return "Large file upload may timeout due to PHP execution time limit ({$maxExecutionTime} seconds). " .
               "File size: {$fileSize}. " .
               "Contact your administrator to increase max_execution_time for large file uploads, " .
               "or try uploading a smaller file.";
    }
    
    /**
     * Format image validation error.
     * 
     * @param array $context May contain image-specific error details
     * @return string Formatted error message
     */
    private static function formatImageValidationError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the image file';
        $issue = $context['issue'] ?? 'does not appear to be a valid image';
        
        if (isset($context['dimensions']) && $context['dimensions']['too_large']) {
            $width = $context['dimensions']['width'];
            $height = $context['dimensions']['height'];
            return "Image dimensions ({$width}x{$height}) are unusually large. " .
                   "Consider resizing the image for better performance and faster loading.";
        }
        
        return "File '{$fileName}' {$issue}. " .
               "Please ensure the file is a valid image format and not corrupted. " .
               "Supported formats include JPEG, PNG, GIF, and WebP.";
    }
    
    /**
     * Format PDF validation error.
     * 
     * @param array $context May contain PDF-specific error details
     * @return string Formatted error message
     */
    private static function formatPdfValidationError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the PDF file';
        $issue = $context['issue'] ?? 'does not appear to be a valid PDF';
        
        return "File '{$fileName}' {$issue}. " .
               "Please ensure the file is a valid PDF document and not corrupted. " .
               "Try opening the file in a PDF viewer to verify it's working correctly.";
    }
    
    /**
     * Format generic upload error.
     * 
     * @param array $context May contain generic error information
     * @return string Formatted error message
     */
    private static function formatGenericError(array $context): string
    {
        $message = $context['message'] ?? 'Upload failed';
        $suggestion = $context['suggestion'] ?? 'Please check your file and try again';
        
        return "{$message}. {$suggestion}. " .
               "If the problem persists, contact your administrator for assistance.";
    }
    
    /**
     * Format bytes into human-readable format.
     * 
     * @param int $bytes Number of bytes to format
     * @param int $precision Number of decimal places
     * @return string Formatted size string (e.g., "1.5 MB", "512 KB")
     */
    public static function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }
        
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $base = 1024;
        
        $unitIndex = floor(log($bytes) / log($base));
        $unitIndex = min($unitIndex, count($units) - 1); // Ensure we don't exceed array bounds
        
        $size = $bytes / pow($base, $unitIndex);
        
        // Use appropriate precision based on size
        if ($unitIndex === 0) {
            // Bytes - no decimal places
            return number_format($size, 0) . ' ' . $units[$unitIndex];
        } elseif ($size >= 100) {
            // Large numbers - no decimal places
            return number_format($size, 0) . ' ' . $units[$unitIndex];
        } elseif ($size == floor($size)) {
            // Whole numbers - no decimal places
            return number_format($size, 0) . ' ' . $units[$unitIndex];
        } else {
            // Smaller numbers with decimals - use specified precision
            return number_format($size, $precision) . ' ' . $units[$unitIndex];
        }
    }
    
    /**
     * Get a correlation ID for error tracking.
     * 
     * @return string UUID for correlation tracking
     */
    public static function generateCorrelationId(): string
    {
        return \Illuminate\Support\Str::uuid()->toString();
    }
    
    /**
     * Format multiple errors into a single message.
     * 
     * @param array $errors Array of error messages
     * @param string $separator Separator between errors
     * @return string Combined error message
     */
    public static function combineErrors(array $errors, string $separator = ' '): string
    {
        if (empty($errors)) {
            return 'Unknown error occurred.';
        }
        
        if (count($errors) === 1) {
            return $errors[0];
        }
        
        return implode($separator, $errors);
    }
    
    /**
     * Format header mismatch error for basic file validation.
     * 
     * @param array $context Must contain header validation details
     * @return string Formatted error message
     */
    private static function formatHeaderMismatchError(array $context): string
    {
        $fileExtension = $context['file_extension'] ?? 'unknown';
        $headerSignature = $context['header_signature'] ?? 'unknown';
        $expectedHeaders = $context['expected_headers'] ?? 'unknown';
        $securityReason = $context['security_reason'] ?? 'File header does not match extension';
        
        return "File header validation failed. " .
               "The file has a '.{$fileExtension}' extension but the header signature ({$headerSignature}) " .
               "does not match expected signatures ({$expectedHeaders}). " .
               "{$securityReason}. " .
               "Please ensure the file is saved with the correct extension and is not corrupted.";
    }
    
    /**
     * Format executable file detected error.
     * 
     * @param array $context Must contain executable detection details
     * @return string Formatted error message
     */
    private static function formatExecutableDetectedError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the uploaded file';
        $signatureType = $context['signature_type'] ?? 'executable content';
        $securityReason = $context['security_reason'] ?? 'Executable files are not allowed for security';
        
        return "Security violation: '{$fileName}' contains {$signatureType}. " .
               "{$securityReason}. " .
               "Please upload only document, image, audio, or video files.";
    }
    
    /**
     * Format script content detected error.
     * 
     * @param array $context Must contain script detection details
     * @return string Formatted error message
     */
    private static function formatScriptContentDetectedError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the uploaded file';
        $scriptType = $context['script_type'] ?? 'script content';
        $securityReason = $context['security_reason'] ?? 'Script content is not allowed for security';
        
        return "Security violation: '{$fileName}' contains {$scriptType}. " .
               "{$securityReason}. " .
               "Please upload only safe document, image, audio, or video files.";
    }
    
    /**
     * Format security threat error from advanced scanning.
     * 
     * @param array $context Must contain threat details
     * @return string Formatted error message
     */
    private static function formatSecurityThreatError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the uploaded file';
        $threatDescription = $context['threat_description'] ?? 'security threat';
        $securityReason = $context['security_reason'] ?? 'Advanced security scan detected potential threat';
        
        return "Security threat detected: '{$fileName}' contains {$threatDescription}. " .
               "{$securityReason}. " .
               "This file cannot be uploaded for security reasons.";
    }
    
    /**
     * Format security warning error.
     * 
     * @param array $context Must contain warning details
     * @return string Formatted error message
     */
    private static function formatSecurityWarningError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the uploaded file';
        $warningDescription = $context['warning_description'] ?? 'suspicious characteristics';
        $securityReason = $context['security_reason'] ?? 'File exhibits suspicious characteristics';
        
        return "Security concern: '{$fileName}' exhibits {$warningDescription}. " .
               "{$securityReason}. " .
               "Please verify the file is safe and try uploading again.";
    }
    
    /**
     * Format virus detected error.
     * 
     * @param array $context Must contain virus detection details
     * @return string Formatted error message
     */
    private static function formatVirusDetectedError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the uploaded file';
        $scanner = $context['scanner'] ?? 'antivirus scanner';
        $securityReason = $context['security_reason'] ?? 'Virus detected by antivirus scanner';
        
        return "VIRUS DETECTED: '{$fileName}' contains malicious content detected by {$scanner}. " .
               "{$securityReason}. " .
               "This file cannot be uploaded. Please scan your computer for viruses.";
    }
    
    /**
     * Format test virus detected error.
     * 
     * @param array $context Must contain test virus details
     * @return string Formatted error message
     */
    private static function formatTestVirusDetectedError(array $context): string
    {
        $fileName = $context['file_name'] ?? 'the uploaded file';
        $testType = $context['test_type'] ?? 'test virus';
        $securityReason = $context['security_reason'] ?? 'Test virus file detected';
        
        return "Test virus detected: '{$fileName}' contains {$testType} signature. " .
               "{$securityReason}. " .
               "This appears to be an antivirus test file and cannot be uploaded.";
    }
    
    /**
     * Create a standardized error context array.
     * 
     * @param string $correlationId Correlation ID for tracking
     * @param array $fileInfo File information
     * @param array $serverConfig Server configuration details
     * @return array Standardized context array
     */
    public static function createErrorContext(string $correlationId, array $fileInfo = [], array $serverConfig = []): array
    {
        return [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'file_info' => $fileInfo,
            'server_config' => $serverConfig,
            'user_id' => auth()->check() ? auth()->id() : null,
        ];
    }
}