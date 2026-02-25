<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PdfStreamLogger provides comprehensive logging for PDF streaming operations.
 * 
 * This service logs signature validation failures, successful streams, and all
 * relevant request details to help diagnose 403 errors and monitor PDF access.
 * 
 * Requirements: 5.1, 5.2, 5.3, 5.4
 */
class PdfStreamLogger
{
    /**
     * Log signature validation failure with detailed diagnostics.
     * 
     * Logs comprehensive information about why a signed URL failed validation,
     * including the URL, signature parameters, and request metadata.
     * 
     * @param Request $request The HTTP request that failed validation
     * @param string $reason The reason for signature validation failure
     * @param array $additionalContext Additional context about the failure
     * @return string Correlation ID for tracking this failure
     */
    public function logSignatureFailure(Request $request, string $reason, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'event_type' => 'signature_validation_failure',
            'reason' => $reason,
            
            // URL and signature details
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            'signature' => $request->query('signature'),
            'expires' => $request->query('expires'),
            'expires_formatted' => $request->query('expires') ? date('Y-m-d H:i:s', $request->query('expires')) : null,
            'current_time' => time(),
            'is_expired' => $request->query('expires') ? (time() > $request->query('expires')) : null,
            
            // Request metadata
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            'origin' => $request->header('Origin'),
            
            // User information (if available)
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'authenticated' => auth()->check(),
            
            // Request headers
            'headers' => [
                'Accept' => $request->header('Accept'),
                'Content-Type' => $request->header('Content-Type'),
                'Range' => $request->header('Range'),
                'Cookie' => $request->hasHeader('Cookie') ? 'present' : 'absent',
            ],
            
            // Query parameters (excluding signature for security)
            'query_params' => $request->except(['signature']),
            
            // Additional context
            'additional_context' => $additionalContext,
        ];
        
        Log::error('PDF stream signature validation failed', $logData);
        
        return $correlationId;
    }
    
    /**
     * Log successful PDF stream request.
     * 
     * Logs successful PDF streaming operations with content details,
     * user information, and request metadata for monitoring and debugging.
     * 
     * @param Content $content The PDF content being streamed
     * @param Request $request The HTTP request
     * @param array $additionalContext Additional context about the stream
     * @return string Correlation ID for tracking this stream
     */
    public function logSuccessfulStream(Content $content, Request $request, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'event_type' => 'successful_stream',
            
            // Content information
            'content_id' => $content->id,
            'content_title' => $content->title,
            'content_type' => $content->type,
            'file_path' => $content->file_path,
            'file_size' => $content->file_size,
            'file_size_formatted' => $this->formatBytes($content->file_size),
            'storage_disk' => $content->storage_disk ?? 'protected',
            
            // URL and request details
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            'has_signature' => $request->has('signature'),
            'has_expiration' => $request->has('expires'),
            
            // Request metadata
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            'origin' => $request->header('Origin'),
            
            // User information (if available)
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'authenticated' => auth()->check(),
            
            // Range request information
            'is_range_request' => $request->hasHeader('Range'),
            'range_header' => $request->header('Range'),
            
            // Additional context
            'additional_context' => $additionalContext,
        ];
        
        Log::info('PDF streamed successfully', $logData);
        
        return $correlationId;
    }
    
    /**
     * Log PDF streaming error with detailed diagnostics.
     * 
     * @param Content|null $content The PDF content (if available)
     * @param Request $request The HTTP request
     * @param string $errorType The type of error that occurred
     * @param string $errorMessage The error message
     * @param array $additionalContext Additional context about the error
     * @return string Correlation ID for tracking this error
     */
    public function logStreamError(?Content $content, Request $request, string $errorType, string $errorMessage, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'event_type' => 'stream_error',
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            
            // Content information (if available)
            'content_id' => $content?->id,
            'content_title' => $content?->title,
            'content_type' => $content?->type,
            'file_path' => $content?->file_path,
            'storage_disk' => $content?->storage_disk ?? 'protected',
            
            // URL and request details
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            'has_signature' => $request->has('signature'),
            
            // Request metadata
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            
            // User information (if available)
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'authenticated' => auth()->check(),
            
            // Additional context
            'additional_context' => $additionalContext,
        ];
        
        Log::error('PDF stream error occurred', $logData);
        
        return $correlationId;
    }
    
    /**
     * Log range request details for debugging.
     * 
     * @param Content $content The PDF content being streamed
     * @param Request $request The HTTP request
     * @param int $start Start byte position
     * @param int $end End byte position
     * @param int $fileSize Total file size
     * @param array $additionalContext Additional context
     * @return string Correlation ID for tracking this range request
     */
    public function logRangeRequest(Content $content, Request $request, int $start, int $end, int $fileSize, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        $length = $end - $start + 1;
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'event_type' => 'range_request',
            
            // Content information
            'content_id' => $content->id,
            'content_title' => $content->title,
            
            // Range details
            'range_header' => $request->header('Range'),
            'start_byte' => $start,
            'end_byte' => $end,
            'length_bytes' => $length,
            'length_formatted' => $this->formatBytes($length),
            'file_size' => $fileSize,
            'file_size_formatted' => $this->formatBytes($fileSize),
            'percentage_of_file' => round(($length / $fileSize) * 100, 2),
            
            // Request metadata
            'ip_address' => $request->ip(),
            'user_id' => auth()->id(),
            
            // Additional context
            'additional_context' => $additionalContext,
        ];
        
        Log::info('PDF range request served', $logData);
        
        return $correlationId;
    }
    
    /**
     * Log URL generation for debugging.
     * 
     * @param Content $content The PDF content
     * @param string $url The generated signed URL
     * @param int $expirationMinutes Expiration time in minutes
     * @param array $additionalContext Additional context
     * @return string Correlation ID for tracking this URL generation
     */
    public function logUrlGeneration(Content $content, string $url, int $expirationMinutes, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        // Parse URL to extract signature and expiration
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'event_type' => 'url_generation',
            
            // Content information
            'content_id' => $content->id,
            'content_title' => $content->title,
            'content_type' => $content->type,
            
            // URL details
            'url' => $url,
            'url_length' => strlen($url),
            'has_signature' => isset($queryParams['signature']),
            'has_expiration' => isset($queryParams['expires']),
            'expiration_minutes' => $expirationMinutes,
            'expires_at' => isset($queryParams['expires']) ? date('Y-m-d H:i:s', $queryParams['expires']) : null,
            'expires_timestamp' => $queryParams['expires'] ?? null,
            
            // User information
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            
            // Additional context
            'additional_context' => $additionalContext,
        ];
        
        Log::info('PDF signed URL generated', $logData);
        
        return $correlationId;
    }
    
    /**
     * Log access denied event.
     * 
     * @param Content $content The PDF content
     * @param Request $request The HTTP request
     * @param string $reason The reason for access denial
     * @param array $additionalContext Additional context
     * @return string Correlation ID for tracking this denial
     */
    public function logAccessDenied(Content $content, Request $request, string $reason, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'event_type' => 'access_denied',
            'reason' => $reason,
            
            // Content information
            'content_id' => $content->id,
            'content_title' => $content->title,
            'content_type' => $content->type,
            'is_active' => $content->is_active,
            
            // URL and request details
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            
            // Request metadata
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            
            // User information (if available)
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'authenticated' => auth()->check(),
            
            // Additional context
            'additional_context' => $additionalContext,
        ];
        
        Log::warning('PDF access denied', $logData);
        
        return $correlationId;
    }
    
    /**
     * Log PDF viewer error from client-side.
     * Requirements 6.3, 6.4: Log errors with full context
     * 
     * @param Content $content The PDF content
     * @param Request $request The HTTP request
     * @param string $errorType The type of error that occurred
     * @param string $errorMessage The error message
     * @param array $additionalContext Additional context about the error
     * @return string Correlation ID for tracking this error
     */
    public function logViewerError(Content $content, Request $request, string $errorType, string $errorMessage, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'event_type' => 'viewer_error',
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            
            // Content information
            'content_id' => $content->id,
            'content_title' => $content->title,
            'content_type' => $content->type,
            'file_path' => $content->file_path,
            'file_size' => $content->file_size,
            'storage_disk' => $content->storage_disk ?? 'protected',
            
            // Request metadata
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            
            // User information (if available)
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'authenticated' => auth()->check(),
            
            // Additional context from client
            'additional_context' => $additionalContext,
        ];
        
        Log::error('PDF viewer error reported', $logData);
        
        return $correlationId;
    }
    
    /**
     * Format bytes to human-readable format.
     * 
     * @param int|null $bytes Number of bytes
     * @return string Formatted string
     */
    private function formatBytes(?int $bytes): string
    {
        if ($bytes === null) {
            return 'unknown';
        }
        
        if ($bytes === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
