<?php

namespace App\Http\Controllers;

use App\Services\SecureFileStorageService;
use App\Services\FileStorageDiagnosticService;
use App\Services\FileOperationLogger;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class SecureFileController extends Controller
{
    protected SecureFileStorageService $secureFileStorage;
    protected FileStorageDiagnosticService $diagnosticService;
    protected $fileOperationLogger;

    public function __construct(
        SecureFileStorageService $secureFileStorage,
        FileStorageDiagnosticService $diagnosticService,
        ?FileOperationLogger $fileOperationLogger = null
    ) {
        $this->secureFileStorage = $secureFileStorage;
        $this->diagnosticService = $diagnosticService;
        $this->fileOperationLogger = $fileOperationLogger ?? new class {
            public function __call($method, $args) {
                \Log::info("FileOperationLogger::{$method} called", $args);
                return \Illuminate\Support\Str::uuid()->toString();
            }
        };
    }

    /**
     * Download a file using a secure access token.
     * 
     * @param Request $request
     * @param string $token Secure access token
     * @return Response
     */
    public function downloadByToken(Request $request, string $token)
    {
        try {
            // Validate the secure access token
            $tokenData = $this->secureFileStorage->validateSecureAccessToken($token);
            
            if (!$tokenData) {
                Log::warning('SecureFileController: Invalid or expired token', [
                    'token_id' => substr($token, 0, 8) . '...',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                return response()->json([
                    'error' => 'Invalid or expired access token',
                    'error_code' => 'INVALID_TOKEN',
                    'details' => 'The provided access token is either invalid or has expired.'
                ], 403);
            }

            $filePath = $tokenData['file_path'];
            
            // Verify file exists in protected storage
            if (!Storage::disk('protected')->exists($filePath)) {
                Log::error('SecureFileController: File not found in protected storage', [
                    'file_path' => $filePath,
                    'token_id' => substr($token, 0, 8) . '...',
                    'user_id' => $tokenData['user_id'],
                    'ip_address' => $request->ip(),
                ]);
                
                return response()->json([
                    'error' => 'File not found',
                    'error_code' => 'FILE_NOT_FOUND',
                    'details' => 'The requested file could not be located in secure storage.'
                ], 404);
            }

            // Log file access for security auditing
            $this->logFileAccess($tokenData, $request);

            // Get file content and metadata
            $fileContent = Storage::disk('protected')->get($filePath);
            $fileSize = Storage::disk('protected')->size($filePath);
            $fileName = basename($filePath);
            
            // Determine MIME type from file extension or stored metadata
            $mimeType = $this->getMimeTypeFromPath($filePath);
            
            // Generate ETag and last modified time for conditional requests
            $etag = $this->generateETag($filePath, $fileSize);
            $lastModified = $this->getLastModifiedTime($filePath);
            
            // Check if we can return 304 Not Modified
            if ($this->shouldReturn304($request, $etag, $lastModified)) {
                Log::info('SecureFileController: Returning 304 Not Modified', [
                    'file_path' => $filePath,
                    'token_id' => substr($token, 0, 8) . '...',
                    'user_id' => $tokenData['user_id'],
                ]);
                
                return response('', 304, [
                    'ETag' => '"' . $etag . '"',
                    'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
                    'Cache-Control' => 'private, max-age=7200',
                ]);
            }
            
            Log::info('SecureFileController: File download successful', [
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'token_id' => substr($token, 0, 8) . '...',
                'user_id' => $tokenData['user_id'],
            ]);

            // Return file with comprehensive security and optimization headers
            $headers = $this->buildSecureFileHeaders($mimeType, $fileSize, $fileName, $filePath);
            return response($fileContent, 200, $headers);

        } catch (\Exception $e) {
            Log::error('SecureFileController: File download failed', [
                'token_id' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
            ]);
            
            return response()->json([
                'error' => 'File download failed',
                'error_code' => 'DOWNLOAD_FAILED',
                'details' => 'An unexpected error occurred while processing your file download request.'
            ], 500);
        }
    }

    /**
     * Download a content file with proper authorization and enhanced diagnostics.
     * 
     * @param Request $request
     * @param Content $content
     * @return Response
     */
    public function downloadContentFile(Request $request, Content $content)
    {
        try {
            // Comprehensive permission validation
            $permissionResult = $this->validateFileAccess($request, $content);
            if ($permissionResult !== true) {
                return $permissionResult; // Return the error response
            }

            // Verify content has a file
            if (!$content->file_path) {
                $this->fileOperationLogger->logFileServingError(
                    $content,
                    'Content has no file path',
                    ['access_method' => 'secure_file_controller']
                );
                
                Log::warning('SecureFileController: Content has no file path', [
                    'content_id' => $content->id,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);
                
                return response()->json([
                    'error' => 'Content has no associated file',
                    'error_code' => 'NO_FILE_ASSOCIATED',
                    'details' => 'This content item does not have a file attached to it.'
                ], 404);
            }

            // Run comprehensive diagnostic check
            $diagnosticResult = $this->diagnosticService->diagnoseFileStorageIssues($content);
            
            if (!$diagnosticResult->fileExists()) {
                // File not found - log comprehensive diagnostic information
                $correlationId = $this->fileOperationLogger->logFileServingError(
                    $content,
                    'File not found during secure file download',
                    [
                        'access_method' => 'secure_file_controller',
                        'diagnostic_result' => $diagnosticResult->toArray(),
                        'inconsistencies' => $diagnosticResult->getInconsistencies(),
                        'recommendations' => $diagnosticResult->getRecommendations(),
                    ]
                );
                
                // Log storage inconsistency if detected
                if ($diagnosticResult->hasInconsistencies()) {
                    $this->fileOperationLogger->logStorageInconsistency(
                        $content,
                        $diagnosticResult->getInconsistencies()
                    );
                }
                
                Log::error('SecureFileController: Content file not found', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'recorded_file_path' => $content->file_path,
                    'recorded_storage_disk' => $content->storage_disk,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'diagnostic_summary' => [
                        'file_exists' => false,
                        'inconsistencies_count' => count($diagnosticResult->getInconsistencies()),
                        'has_recommendations' => !empty($diagnosticResult->getRecommendations()),
                    ]
                ]);
                
                return response()->json([
                    'error' => 'File not found',
                    'error_code' => 'FILE_NOT_FOUND',
                    'details' => 'The requested file could not be located on the server.',
                    'correlation_id' => $correlationId
                ], 404);
            }
            
            // Get actual file location from diagnostic result
            $actualLocation = $diagnosticResult->getActualLocation();
            $storageDisk = $actualLocation->getDisk();
            $filePath = $actualLocation->getPath();
            
            // Log storage inconsistency if file is in different location than recorded
            if ($storageDisk !== ($content->storage_disk ?? 'protected')) {
                $this->fileOperationLogger->logStorageInconsistency(
                    $content,
                    [
                        'type' => 'storage_disk_mismatch',
                        'recorded_disk' => $content->storage_disk ?? 'protected',
                        'actual_disk' => $storageDisk,
                        'file_path' => $filePath,
                        'message' => 'File found on different storage disk than recorded',
                    ]
                );
            }

            // Log file access for security auditing
            $this->logContentFileAccess($content, $request);

            // Get file content and metadata
            $fileContent = Storage::disk($storageDisk)->get($filePath);
            $fileSize = $content->file_size ?? Storage::disk($storageDisk)->size($filePath);
            $fileName = $content->original_filename ?? $content->file_name ?? basename($filePath);
            $mimeType = $content->mime_type ?? $this->getMimeTypeFromPath($filePath);
            
            // Generate ETag and last modified time for conditional requests
            $etag = $this->generateETag($filePath, $fileSize);
            $lastModified = $this->getLastModifiedTime($filePath);
            
            // Check if we can return 304 Not Modified
            if ($this->shouldReturn304($request, $etag, $lastModified)) {
                Log::info('SecureFileController: Returning 304 Not Modified for content', [
                    'content_id' => $content->id,
                    'file_path' => $filePath,
                    'user_id' => auth()->id(),
                ]);
                
                return response('', 304, [
                    'ETag' => '"' . $etag . '"',
                    'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
                    'Cache-Control' => 'private, max-age=7200',
                ]);
            }
            
            // Log successful file access
            $this->fileOperationLogger->logFileAccess(
                $content,
                'secure_file_controller_download',
                [
                    'actual_storage_disk' => $storageDisk,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'diagnostic_passed' => true,
                ]
            );
            
            Log::info('SecureFileController: Content file download successful', [
                'content_id' => $content->id,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'storage_disk' => $storageDisk,
                'user_id' => auth()->id(),
            ]);

            // Return file with comprehensive security and optimization headers
            $headers = $this->buildSecureFileHeaders($mimeType, $fileSize, $fileName, $filePath);
            return response($fileContent, 200, $headers);

        } catch (\Exception $e) {
            $correlationId = $this->fileOperationLogger->logFileServingError(
                $content,
                'Content file download failed: ' . $e->getMessage(),
                [
                    'access_method' => 'secure_file_controller',
                    'exception_type' => get_class($e),
                    'exception_trace' => $e->getTraceAsString(),
                ]
            );
            
            Log::error('SecureFileController: Content file download failed', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);
            
            return response()->json([
                'error' => 'File download failed',
                'error_code' => 'DOWNLOAD_FAILED',
                'details' => 'An unexpected error occurred while processing your file download request.',
                'correlation_id' => $correlationId
            ], 500);
        }
    }

    /**
     * Generate a temporary secure access URL for a content file with diagnostic validation.
     * 
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSecureUrl(Request $request, Content $content)
    {
        try {
            // Comprehensive permission validation
            $permissionResult = $this->validateFileAccess($request, $content);
            if ($permissionResult !== true) {
                return $permissionResult; // Return the error response
            }

            // Verify content has a file
            if (!$content->file_path) {
                $this->fileOperationLogger->logFileServingError(
                    $content,
                    'Cannot generate URL for content without file path',
                    ['access_method' => 'secure_url_generation']
                );
                
                Log::warning('SecureFileController: Cannot generate URL for content without file', [
                    'content_id' => $content->id,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);
                
                return response()->json([
                    'error' => 'Content has no associated file',
                    'error_code' => 'NO_FILE_ASSOCIATED',
                    'details' => 'Cannot generate a secure URL for content without an attached file.'
                ], 404);
            }

            // Run diagnostic check before generating URL
            $diagnosticResult = $this->diagnosticService->diagnoseFileStorageIssues($content);
            
            if (!$diagnosticResult->fileExists()) {
                // File not found - log comprehensive diagnostic information
                $correlationId = $this->fileOperationLogger->logUrlGenerationFailure(
                    $content,
                    'File not found during secure URL generation',
                    [
                        'access_method' => 'secure_url_generation',
                        'diagnostic_result' => $diagnosticResult->toArray(),
                        'inconsistencies' => $diagnosticResult->getInconsistencies(),
                        'recommendations' => $diagnosticResult->getRecommendations(),
                    ]
                );
                
                // Log storage inconsistency if detected
                if ($diagnosticResult->hasInconsistencies()) {
                    $this->fileOperationLogger->logStorageInconsistency(
                        $content,
                        $diagnosticResult->getInconsistencies()
                    );
                }
                
                Log::warning('SecureFileController: Cannot generate URL for missing file', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'recorded_file_path' => $content->file_path,
                    'recorded_storage_disk' => $content->storage_disk,
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'diagnostic_summary' => [
                        'file_exists' => false,
                        'inconsistencies_count' => count($diagnosticResult->getInconsistencies()),
                        'has_recommendations' => !empty($diagnosticResult->getRecommendations()),
                    ]
                ]);
                
                return response()->json([
                    'error' => 'File not found',
                    'error_code' => 'FILE_NOT_FOUND',
                    'details' => 'Cannot generate a secure URL for a file that does not exist.',
                    'correlation_id' => $correlationId,
                    'diagnostic_info' => app()->environment('local', 'development') ? [
                        'inconsistencies' => $diagnosticResult->getInconsistencies(),
                        'recommendations' => $diagnosticResult->getRecommendations(),
                    ] : null
                ], 404);
            }

            // Get actual file location from diagnostic result
            $actualLocation = $diagnosticResult->getActualLocation();
            $actualFilePath = $actualLocation->getPath();
            
            // Log storage inconsistency if file is in different location than recorded
            if ($actualLocation->getDisk() !== ($content->storage_disk ?? 'protected')) {
                $this->fileOperationLogger->logStorageInconsistency(
                    $content,
                    [
                        'type' => 'storage_disk_mismatch',
                        'recorded_disk' => $content->storage_disk ?? 'protected',
                        'actual_disk' => $actualLocation->getDisk(),
                        'file_path' => $actualFilePath,
                        'message' => 'File found on different storage disk than recorded during URL generation',
                    ]
                );
            }

            // Generate secure access token using actual file path
            $context = [
                'content_id' => $content->id,
                'user_id' => auth()->id(),
                'subpage_id' => $content->subpage_id,
                'access_type' => 'content_file',
                'diagnostic_validated' => true,
                'actual_storage_disk' => $actualLocation->getDisk(),
            ];

            $expirationMinutes = $request->input('expiration_minutes', 60);
            $token = $this->secureFileStorage->generateSecureAccessToken(
                $actualFilePath, 
                $expirationMinutes, 
                $context
            );

            $secureUrl = route('secure-files.download-token', ['token' => $token]);

            // Log successful URL generation
            $this->fileOperationLogger->logFileOperation(
                'secure_url_generation',
                $content,
                'success',
                [
                    'expiration_minutes' => $expirationMinutes,
                    'token_id' => substr($token, 0, 8) . '...',
                    'actual_storage_disk' => $actualLocation->getDisk(),
                    'diagnostic_passed' => true,
                ]
            );

            Log::info('SecureFileController: Secure URL generated', [
                'content_id' => $content->id,
                'token_id' => substr($token, 0, 8) . '...',
                'expiration_minutes' => $expirationMinutes,
                'user_id' => auth()->id(),
                'actual_storage_disk' => $actualLocation->getDisk(),
            ]);

            return response()->json([
                'success' => true,
                'secure_url' => $secureUrl,
                'expires_in_minutes' => $expirationMinutes,
                'expires_at' => now()->addMinutes($expirationMinutes)->toISOString(),
            ]);

        } catch (\Exception $e) {
            $correlationId = $this->fileOperationLogger->logFileServingError(
                $content,
                'Secure URL generation failed: ' . $e->getMessage(),
                [
                    'access_method' => 'secure_url_generation',
                    'exception_type' => get_class($e),
                    'exception_trace' => $e->getTraceAsString(),
                ]
            );
            
            Log::error('SecureFileController: Secure URL generation failed', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);
            
            return response()->json([
                'error' => 'Failed to generate secure URL',
                'error_code' => 'URL_GENERATION_FAILED',
                'details' => 'An error occurred while generating the secure access URL.',
                'correlation_id' => $correlationId
            ], 500);
        }
    }

    /**
     * Build comprehensive security and optimization headers for file serving.
     * 
     * @param string $mimeType File MIME type
     * @param int $fileSize File size in bytes
     * @param string $fileName Original filename
     * @param string $filePath File path for extension detection
     * @return array HTTP headers
     */
    private function buildSecureFileHeaders(string $mimeType, int $fileSize, string $fileName, string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $isImage = $this->isImageFile($extension);
        $isDownloadable = $this->isDownloadableFile($extension);
        $isMedia = $this->isMediaFile($extension);
        $isDocument = $this->isDocumentFile($extension);
        
        // Debug logging
        Log::info('SecureFileController: Building headers', [
            'extension' => $extension,
            'isImage' => $isImage,
            'isDownloadable' => $isDownloadable,
            'isMedia' => $isMedia,
            'isDocument' => $isDocument,
            'filePath' => $filePath
        ]);
        
        // Debug: Log which path will be taken
        if ($isImage) {
            Log::info('SecureFileController: Will apply image headers');
        } elseif ($isMedia) {
            Log::info('SecureFileController: Will apply media headers');
        } elseif ($isDocument) {
            Log::info('SecureFileController: Will apply document headers');
        } elseif ($isDownloadable) {
            Log::info('SecureFileController: Will apply downloadable headers');
        } else {
            Log::info('SecureFileController: Will apply default secure headers');
        }
        
        // Base security headers - comprehensive security foundation
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => (string) $fileSize,
            
            // Prevent MIME type sniffing attacks
            'X-Content-Type-Options' => 'nosniff',
            
            // Prevent XSS attacks (deprecated but still supported)
            'X-XSS-Protection' => '1; mode=block',
            
            // Control referrer information - enhanced privacy
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            
            // Prevent clickjacking (will be overridden for images)
            'X-Frame-Options' => 'DENY',
            
            // Strict transport security for HTTPS - enhanced with preload
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            
            // Prevent cross-domain policy abuse
            'X-Permitted-Cross-Domain-Policies' => 'none',
            
            // Enhanced permissions policy for modern browsers
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()',

            
            // Cross-Origin policies for enhanced security
            'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            
            // Default CSP - restrictive by default
            'Content-Security-Policy' => "default-src 'none'; object-src 'none'; script-src 'none'; base-uri 'none'; form-action 'none';",
            
            // Server identification headers for security
            'X-Robots-Tag' => 'noindex, nofollow, noarchive, nosnippet',
            
            // Performance and compression hints
            'Vary' => 'Accept-Encoding, User-Agent',
        ];
        
        // Content disposition and type-specific security headers
        if ($isImage) {
            $this->applyImageHeaders($headers, $fileName);
        } elseif ($isMedia) {
            $this->applyMediaHeaders($headers, $fileName);
        } elseif ($isDocument) {
            $this->applyDocumentHeaders($headers, $fileName, $extension);
        } elseif ($isDownloadable) {
            $this->applyDownloadableHeaders($headers, $fileName);
        } else {
            // Default to secure attachment for unknown file types
            $this->applyDefaultSecureHeaders($headers, $fileName);
        }
        
        // Apply caching strategy based on file type
        $cacheHeaders = $this->getCacheHeaders($extension, $isImage, $isMedia, $isDocument);
        $headers = array_merge($headers, $cacheHeaders);
        
        // Generate ETag for better caching and integrity
        $etag = $this->generateETag($filePath, $fileSize);
        $headers['ETag'] = '"' . $etag . '"';
        
        // Add Last-Modified header for better caching
        $lastModified = $this->getLastModifiedTime($filePath);
        $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModified) . ' GMT';
        
        // Add Accept-Ranges for partial content support (useful for media files)
        if ($isMedia || $fileSize > 1024 * 1024) { // Files larger than 1MB
            $headers['Accept-Ranges'] = 'bytes';
        }
        
        // Add performance optimization headers
        if ($fileSize > 10 * 1024 * 1024) { // Files larger than 10MB
            $headers['X-Large-File'] = 'true';
            $headers['X-Accel-Buffering'] = 'no'; // Disable proxy buffering for large files
        }
        
        // Add integrity headers for sensitive files
        if (in_array($extension, ['exe', 'msi', 'dmg', 'pkg'])) {
            $headers['X-Content-Integrity'] = 'sha256-' . base64_encode(hash('sha256', $filePath . $fileSize, true));
        }
        
        // Final debug logging
        Log::info('SecureFileController: Final headers', [
            'X-Frame-Options' => $headers['X-Frame-Options'],
            'Content-Disposition' => $headers['Content-Disposition'] ?? 'not set'
        ]);
        
        return $headers;
    }

    /**
     * Get appropriate cache headers based on file type.
     * 
     * @param string $extension File extension
     * @param bool $isImage Whether the file is an image
     * @param bool $isMedia Whether the file is media
     * @param bool $isDocument Whether the file is a document
     * @return array Cache headers
     */
    private function getCacheHeaders(string $extension, bool $isImage, bool $isMedia = false, bool $isDocument = false): array
    {
        // Different caching strategies for different file types
        if ($isImage) {
            // Images can be cached longer as they rarely change
            // Add immutable directive for better performance
            return [
                'Cache-Control' => 'private, max-age=7200, must-revalidate, immutable', // 2 hours
                'Expires' => gmdate('D, d M Y H:i:s', time() + 7200) . ' GMT',
            ];
        }
        
        if ($isMedia) {
            // Media files can be cached longer due to their static nature
            // Add stale-while-revalidate for better UX
            return [
                'Cache-Control' => 'private, max-age=14400, stale-while-revalidate=3600, immutable', // 4 hours + 1 hour stale
                'Expires' => gmdate('D, d M Y H:i:s', time() + 14400) . ' GMT',
            ];
        }
        
        if ($isDocument || in_array($extension, ['pdf', 'doc', 'docx', 'txt', 'rtf'])) {
            // Documents have moderate caching with conditional requests
            return [
                'Cache-Control' => 'private, max-age=3600, must-revalidate, stale-while-revalidate=1800', // 1 hour + 30 min stale
                'Expires' => gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT',
            ];
        }
        
        // Sensitive or executable files - minimal caching
        if (in_array($extension, ['exe', 'msi', 'bat', 'cmd', 'sh', 'ps1'])) {
            return [
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ];
        }
        
        // Default caching for other files - shorter duration for security
        return [
            'Cache-Control' => 'private, max-age=1800, must-revalidate', // 30 minutes
            'Expires' => gmdate('D, d M Y H:i:s', time() + 1800) . ' GMT',
        ];
    }

    /**
     * Apply headers specific to image files.
     * 
     * @param array &$headers Headers array to modify
     * @param string $fileName Original filename
     */
    private function applyImageHeaders(array &$headers, string $fileName): void
    {
        // Allow images to be displayed in same-origin contexts
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        
        // More permissive CSP for images to allow inline display
        $headers['Content-Security-Policy'] = "default-src 'none'; img-src 'self' data: blob:; style-src 'unsafe-inline'; object-src 'none'; script-src 'none'; frame-ancestors 'self';";
        
        // Allow inline display for images
        $headers['Content-Disposition'] = 'inline; filename="' . $this->sanitizeFilename($fileName) . '"';
        
        // Add image-specific security headers
        $headers['X-Image-Options'] = 'nosniff';
        
        // Enhanced Cross-Origin policies for images
        $headers['Cross-Origin-Resource-Policy'] = 'cross-origin'; // Allow cross-origin for images
        
        // Add image optimization hints
        $headers['Accept-CH'] = 'DPR, Width, Viewport-Width'; // Client hints for responsive images
        
        // Debug logging
        Log::info('SecureFileController: Applied image headers', [
            'X-Frame-Options' => $headers['X-Frame-Options'],
            'filename' => $fileName
        ]);
    }

    /**
     * Apply headers specific to media files (audio/video).
     * 
     * @param array &$headers Headers array to modify
     * @param string $fileName Original filename
     */
    private function applyMediaHeaders(array &$headers, string $fileName): void
    {
        // Allow media to be displayed in same-origin contexts
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        
        // Enhanced CSP for media files with better streaming support
        $headers['Content-Security-Policy'] = "default-src 'none'; media-src 'self' blob:; object-src 'none'; script-src 'none'; frame-ancestors 'self';";
        
        // Allow inline display for media
        $headers['Content-Disposition'] = 'inline; filename="' . $this->sanitizeFilename($fileName) . '"';
        
        // Add media-specific headers for streaming
        $headers['Accept-Ranges'] = 'bytes';
        $headers['X-Media-Type'] = 'streaming';
        
        // Enhanced Cross-Origin policies for media
        $headers['Cross-Origin-Resource-Policy'] = 'cross-origin'; // Allow cross-origin for media
        
        // Add media optimization headers
        $headers['X-Content-Duration'] = 'unknown'; // Will be set if duration is known
        
        // Add buffering hints for better streaming performance
        $headers['X-Accel-Buffering'] = 'no'; // Disable proxy buffering for streaming
    }

    /**
     * Apply headers specific to document files.
     * 
     * @param array &$headers Headers array to modify
     * @param string $fileName Original filename
     * @param string $extension File extension
     */
    private function applyDocumentHeaders(array &$headers, string $fileName, string $extension): void
    {
        if ($extension === 'pdf') {
            // PDFs can be viewed inline with enhanced security
            $headers['Content-Disposition'] = 'inline; filename="' . $this->sanitizeFilename($fileName) . '"';
            $headers['X-Frame-Options'] = 'SAMEORIGIN';
            $headers['Content-Security-Policy'] = "default-src 'none'; object-src 'self'; plugin-types application/pdf; frame-ancestors 'self'; script-src 'none';";
            
            // PDF-specific security headers
            $headers['X-PDF-Options'] = 'noopen';
            
        } elseif (in_array($extension, ['txt', 'html', 'htm', 'xml', 'json', 'csv'])) {
            // Text files can be viewed inline with strict CSP
            $headers['Content-Disposition'] = 'inline; filename="' . $this->sanitizeFilename($fileName) . '"';
            $headers['Content-Security-Policy'] = "default-src 'none'; script-src 'none'; object-src 'none'; frame-ancestors 'none';";
            
            // Additional security for HTML files
            if (in_array($extension, ['html', 'htm'])) {
                $headers['X-Frame-Options'] = 'DENY'; // Strict for HTML
                $headers['Content-Security-Policy'] = "default-src 'none'; script-src 'none'; object-src 'none'; style-src 'unsafe-inline'; frame-ancestors 'none';";
            }
            
        } else {
            // Other documents should be downloaded with enhanced security
            $headers['Content-Disposition'] = 'attachment; filename="' . $this->sanitizeFilename($fileName) . '"';
            $headers['X-Download-Options'] = 'noopen';
            
            // Enhanced security for Office documents
            if (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])) {
                $headers['X-Office-Security'] = 'strict';
                $headers['Content-Security-Policy'] = "default-src 'none'; object-src 'none'; script-src 'none'; frame-ancestors 'none';";
            }
        }
        
        // Debug logging
        Log::info('SecureFileController: Applied document headers', [
            'extension' => $extension,
            'X-Frame-Options' => $headers['X-Frame-Options'] ?? 'not set',
            'filename' => $fileName
        ]);
    }

    /**
     * Apply headers specific to downloadable files.
     * 
     * @param array &$headers Headers array to modify
     * @param string $fileName Original filename
     */
    private function applyDownloadableHeaders(array &$headers, string $fileName): void
    {
        // Force download for downloadable file types
        $headers['Content-Disposition'] = 'attachment; filename="' . $this->sanitizeFilename($fileName) . '"';
        
        // Additional security headers for downloads
        $headers['X-Download-Options'] = 'noopen';
        $headers['X-Content-Type-Options'] = 'nosniff';
        
        // Strict CSP for downloads
        $headers['Content-Security-Policy'] = "default-src 'none'; object-src 'none'; script-src 'none'; frame-ancestors 'none';";
        
        // Enhanced security for executable files
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($extension, ['exe', 'msi', 'bat', 'cmd', 'sh', 'ps1'])) {
            $headers['X-Executable-Warning'] = 'potentially-dangerous';
            $headers['X-Virus-Scanned'] = 'required';
            
            // Override caching for executables - no caching
            $headers['Cache-Control'] = 'private, no-cache, no-store, must-revalidate';
            $headers['Pragma'] = 'no-cache';
            $headers['Expires'] = '0';
        }
        
        // Archive-specific headers
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            $headers['X-Archive-Type'] = $extension;
            $headers['X-Content-Scan'] = 'recommended';
        }
    }

    /**
     * Apply default secure headers for unknown file types.
     * 
     * @param array &$headers Headers array to modify
     * @param string $fileName Original filename
     */
    private function applyDefaultSecureHeaders(array &$headers, string $fileName): void
    {
        // Default to secure attachment for unknown file types
        $headers['Content-Disposition'] = 'attachment; filename="' . $this->sanitizeFilename($fileName) . '"';
        
        // Very strict security headers for unknown files
        $headers['X-Download-Options'] = 'noopen';
        $headers['Content-Security-Policy'] = "default-src 'none'; object-src 'none'; script-src 'none'; frame-ancestors 'none';";
        
        // Additional warnings for unknown file types
        $headers['X-Unknown-File-Type'] = 'true';
        $headers['X-Security-Warning'] = 'unknown-file-type-download-at-own-risk';
        
        // Force no caching for unknown file types
        $headers['Cache-Control'] = 'private, no-cache, no-store, must-revalidate';
        $headers['Pragma'] = 'no-cache';
        $headers['Expires'] = '0';
    }

    /**
     * Generate ETag for file caching and integrity.
     * 
     * @param string $filePath File path
     * @param int $fileSize File size
     * @return string ETag value
     */
    private function generateETag(string $filePath, int $fileSize): string
    {
        // Create ETag based on file path, size, and modification time for uniqueness
        // This ensures ETags change when files are updated
        try {
            $storagePath = Storage::disk('protected')->path($filePath);
            $modTime = file_exists($storagePath) ? filemtime($storagePath) : time();
        } catch (\Exception $e) {
            // Fallback to current time if file access fails
            $modTime = time();
        }
        
        // Include file characteristics in ETag for better cache invalidation
        $etagData = [
            'path' => $filePath,
            'size' => $fileSize,
            'mtime' => $modTime,
            'version' => '2.1' // Version for ETag format changes

        ];
        
        return md5(json_encode($etagData));
    }

    /**
     * Check if file extension represents an image.
     * 
     * @param string $extension File extension
     * @return bool
     */
    private function isImageFile(string $extension): bool
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico', 'tiff', 'tif']);
    }

    /**
     * Check if file extension represents a media file (audio/video).
     * 
     * @param string $extension File extension
     * @return bool
     */
    private function isMediaFile(string $extension): bool
    {
        $audioFormats = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'];
        $videoFormats = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v', '3gp'];
        
        return in_array($extension, array_merge($audioFormats, $videoFormats));
    }

    /**
     * Check if file extension represents a document.
     * 
     * @param string $extension File extension
     * @return bool
     */
    private function isDocumentFile(string $extension): bool
    {
        return in_array($extension, [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'rtf', 'odt', 'ods', 'odp',
            'html', 'htm', 'xml', 'json', 'csv'
        ]);
    }

    /**
     * Check if file can be viewed inline in browser.
     * 
     * @param string $extension File extension
     * @return bool
     */
    private function isInlineViewableFile(string $extension): bool
    {
        return in_array($extension, ['pdf', 'txt', 'html', 'htm', 'xml', 'json', 'csv']);
    }

    /**
     * Check if file should be forced to download.
     * 
     * @param string $extension File extension
     * @return bool
     */
    private function isDownloadableFile(string $extension): bool
    {
        return in_array($extension, [
            // Archive formats
            'zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz',
            // Executable formats
            'exe', 'msi', 'dmg', 'pkg', 'deb', 'rpm',
            // Office documents (force download for security)
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            // Text documents (except those that can be viewed inline)
            'rtf', 'odt', 'ods', 'odp',
            // Other potentially dangerous formats
            'bat', 'cmd', 'sh', 'ps1'
        ]);
    }

    /**
     * Sanitize filename for safe HTTP header usage.
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove or replace potentially dangerous characters
        $filename = preg_replace('/[^\w\-_\.]/', '_', $filename);
        
        // Ensure filename is not empty and has reasonable length
        if (empty($filename) || strlen($filename) > 255) {
            $filename = 'download_' . time();
        }
        
        return $filename;
    }

    /**
     * Log file access for security auditing.
     * 
     * @param array $tokenData Token data
     * @param Request $request HTTP request
     */
    private function logFileAccess(array $tokenData, Request $request): void
    {
        $accessLog = [
            'event_type' => 'secure_file_access',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'token_user_id' => $tokenData['user_id'],
            'file_path' => $tokenData['file_path'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'context' => $tokenData['context'] ?? [],
            'access_method' => 'secure_token',
            'server_info' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_method' => $request->method(),
                'request_uri' => $request->getRequestUri(),
            ],
        ];
        
        Log::channel('single')->info('SECURE_FILE_ACCESS_LOG', $accessLog);
    }

    /**
     * Log content file access for security auditing.
     * 
     * @param Content $content Content model
     * @param Request $request HTTP request
     */
    private function logContentFileAccess(Content $content, Request $request): void
    {
        $accessLog = [
            'event_type' => 'content_file_access',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'file_path' => $content->file_path,
            'storage_disk' => $content->storage_disk ?? 'unknown',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'access_method' => 'direct_authorization',
            'content_details' => [
                'type' => $content->type,
                'subpage_id' => $content->subpage_id,
                'visibility' => $content->visibility,
            ],
            'server_info' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_method' => $request->method(),
                'request_uri' => $request->getRequestUri(),
            ],
        ];
        
        Log::channel('single')->info('CONTENT_FILE_ACCESS_LOG', $accessLog);
    }

    /**
     * Validate comprehensive file access permissions.
     * 
     * @param Request $request
     * @param Content $content
     * @return true|\Illuminate\Http\JsonResponse
     */
    private function validateFileAccess(Request $request, Content $content)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            Log::warning('SecureFileController: Unauthenticated access attempt', [
                'content_id' => $content->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return response()->json([
                'error' => 'Authentication required',
                'error_code' => 'UNAUTHENTICATED',
                'details' => 'You must be logged in to access this file.'
            ], 401);
        }

        $user = auth()->user();

        // Check if content is file-based
        if (!$content->isFile()) {
            Log::warning('SecureFileController: Non-file content access attempt', [
                'content_id' => $content->id,
                'content_type' => $content->type,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);
            
            return response()->json([
                'error' => 'Content is not a file',
                'error_code' => 'NOT_A_FILE',
                'details' => 'This content item is not a downloadable file.'
            ], 400);
        }

        // Check download permission using the specific download policy
        try {
            Gate::authorize('download', $content);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // Determine specific reason for denial
            $denialReason = $this->determineDenialReason($user, $content);
            
            Log::warning('SecureFileController: File download authorization failed', [
                'content_id' => $content->id,
                'user_id' => $user->id,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'denial_reason' => $denialReason['reason'],
                'ip_address' => $request->ip(),
                'content_details' => [
                    'type' => $content->type,
                    'subpage_id' => $content->subpage_id,
                    'visibility' => $content->visibility ?? 'unknown',
                ],
            ]);
            
            return response()->json([
                'error' => 'Access denied',
                'error_code' => $denialReason['code'],
                'details' => $denialReason['message']
            ], $denialReason['status_code']);
        }

        return true; // Access granted
    }

    /**
     * Determine the specific reason for access denial.
     * 
     * @param User $user
     * @param Content $content
     * @return array
     */
    private function determineDenialReason($user, Content $content): array
    {
        // Check if user has any valid roles
        if (!$user->hasAnyRole(['Admin', 'Teacher', 'Student'])) {
            return [
                'reason' => 'no_valid_role',
                'code' => 'INSUFFICIENT_ROLE',
                'message' => 'Your account does not have the required role to access files.',
                'status_code' => 403
            ];
        }

        // Check if content can be accessed by user (subpage/course access)
        if (!$content->canBeAccessedBy($user)) {
            // Further determine if it's course access or subpage access
            if ($content->subpage && $content->subpage->module && $content->subpage->module->course) {
                $course = $content->subpage->module->course;
                
                // Check if user is enrolled in the course
                if ($user->hasRole('Student') && !$user->enrolledCourses()->where('courses.id', $course->id)->exists()) {
                    return [
                        'reason' => 'not_enrolled',
                        'code' => 'NOT_ENROLLED',
                        'message' => 'You are not enrolled in the course that contains this file.',
                        'status_code' => 403
                    ];
                }
                
                // Check if teacher owns the course
                if ($user->hasRole('Teacher') && $course->teacher_id !== $user->id) {
                    return [
                        'reason' => 'not_course_owner',
                        'code' => 'NOT_COURSE_OWNER',
                        'message' => 'You do not have permission to access files from this course.',
                        'status_code' => 403
                    ];
                }
            }
            
            return [
                'reason' => 'content_access_denied',
                'code' => 'CONTENT_ACCESS_DENIED',
                'message' => 'You do not have permission to access this content.',
                'status_code' => 403
            ];
        }

        // Default denial reason
        return [
            'reason' => 'general_access_denied',
            'code' => 'ACCESS_DENIED',
            'message' => 'Access to this file is not permitted.',
            'status_code' => 403
        ];
    }

    /**
     * Get the last modified time for a file.
     * 
     * @param string $filePath File path
     * @return int Unix timestamp
     */
    private function getLastModifiedTime(string $filePath): int
    {
        try {
            // Try to get the actual file modification time
            $storagePath = Storage::disk('protected')->path($filePath);
            if (file_exists($storagePath)) {
                return filemtime($storagePath);
            }
            
            // Fallback: try public disk
            $publicPath = Storage::disk('public')->path($filePath);
            if (file_exists($publicPath)) {
                return filemtime($publicPath);
            }
        } catch (\Exception $e) {
            Log::warning('SecureFileController: Could not get file modification time', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
        }
        
        // Fallback to current time
        return time();
    }

    /**
     * Check if the request can be served with a 304 Not Modified response.
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $etag
     * @param int $lastModified
     * @return bool
     */
    private function shouldReturn304(Request $request, string $etag, int $lastModified): bool
    {
        // Check If-None-Match header (ETag)
        $ifNoneMatch = $request->header('If-None-Match');
        if ($ifNoneMatch && $ifNoneMatch === '"' . $etag . '"') {
            return true;
        }
        
        // Check If-Modified-Since header
        $ifModifiedSince = $request->header('If-Modified-Since');
        if ($ifModifiedSince) {
            $ifModifiedSinceTime = strtotime($ifModifiedSince);
            if ($ifModifiedSinceTime && $ifModifiedSinceTime >= $lastModified) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get MIME type from file path.
     * 
     * @param string $filePath File path
     * @return string MIME type
     */
    private function getMimeTypeFromPath(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            
            // Web formats
            'html' => 'text/html',
            'htm' => 'text/html',
            'xml' => 'application/xml',
            'json' => 'application/json',
            'csv' => 'text/csv',
            
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            
            // Audio
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'flac' => 'audio/flac',
            'aac' => 'audio/aac',
            'm4a' => 'audio/mp4',
            'wma' => 'audio/x-ms-wma',
            
            // Video
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            'm4v' => 'video/x-m4v',
            '3gp' => 'video/3gpp',
            
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/vnd.rar',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            'bz2' => 'application/x-bzip2',
            'xz' => 'application/x-xz',
            
            // Executables
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msi',
            'dmg' => 'application/x-apple-diskimage',
            'pkg' => 'application/x-newton-compatible-pkg',
            'deb' => 'application/vnd.debian.binary-package',
            'rpm' => 'application/x-rpm',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}