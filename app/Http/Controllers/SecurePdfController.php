<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\PdfStreamLogger;
use App\Services\SecurePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurePdfController
 * 
 * Enhanced secure PDF delivery with:
 * - PDF.js viewer integration
 * - Dynamic watermarking
 * - Anti-download protections
 * - Page-level tracking
 * - Short-lived signed URLs
 */
class SecurePdfController extends Controller
{
    protected SecurePdfService $pdfService;
    protected PdfStreamLogger $logger;

    public function __construct(SecurePdfService $pdfService, PdfStreamLogger $logger)
    {
        // Apply auth middleware to all methods EXCEPT stream, logError, and logDevToolsDetection
        // The stream method uses signed URLs for security and should work without session auth
        // The logging methods should work without auth to capture errors from unauthenticated contexts
        // This allows PDF.js to fetch the PDF data without requiring cookies/session
        // Requirements: 1.2, 4.1, 6.3, 6.4
        $this->middleware('auth')->except(['stream', 'logError', 'logDevToolsDetection']);
        $this->pdfService = $pdfService;
        $this->logger = $logger;
    }

    /**
     * Show PDF.js viewer with anti-download protections.
     * 
     * @param Request $request
     * @param Content $content
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function viewer(Request $request, Content $content, string $token)
    {
        // ADMIN BYPASS: Admins can view any PDF without token validation
        // This allows admins to access PDFs for troubleshooting and system administration
        // without being blocked by expired tokens or enrollment restrictions
        $isAdmin = auth()->user()->hasRole('Admin');
        
        if ($isAdmin) {
            Log::info('SecurePDF: Admin bypass - skipping token validation', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'content_id' => $content->id,
                'content_title' => $content->title,
                'token_provided' => substr($token, 0, 16) . '...',
            ]);
        } else {
            // Regular users: validate session token with content ID and user ID verification
            $session = $this->pdfService->validateSessionToken($token, $content->id, auth()->id());
            
            if (!$session) {
                // Log failed access attempt with PdfStreamLogger
                $correlationId = $this->logger->logAccessDenied($content, $request, 'invalid_or_expired_token', [
                    'session_token' => substr($token, 0, 32),
                ]);
                
                // Log failed access attempt to PdfAccessLog table
                \App\Models\PdfAccessLog::create([
                    'user_id' => auth()->id(),
                    'content_id' => $content->id,
                    'session_token' => substr($token, 0, 32),
                    'ip_address' => $request->ip(),
                    'access_granted' => false,
                    'failure_reason' => 'invalid_or_expired_token',
                    'accessed_at' => now(),
                ]);
                
                abort(403, 'Invalid or expired viewing session. Please refresh the page to get a new link.');
            }
        }

        // Verify content type
        if ($content->type !== 'pdf') {
            // Log failed access attempt with PdfStreamLogger
            $correlationId = $this->logger->logAccessDenied($content, $request, 'invalid_content_type', [
                'expected_type' => 'pdf',
                'actual_type' => $content->type,
            ]);
            
            // Log failed access attempt to PdfAccessLog table
            \App\Models\PdfAccessLog::create([
                'user_id' => auth()->id(),
                'content_id' => $content->id,
                'session_token' => substr($token, 0, 32),
                'ip_address' => $request->ip(),
                'access_granted' => false,
                'failure_reason' => 'invalid_content_type',
                'accessed_at' => now(),
            ]);
            
            abort(404, 'Content not found');
        }

        // Check permissions (admins bypass this check via canView)
        if (!$this->pdfService->canView($content, auth()->user())) {
            // Log failed access attempt with PdfStreamLogger
            $correlationId = $this->logger->logAccessDenied($content, $request, 'insufficient_permissions');
            
            // Log failed access attempt to PdfAccessLog table
            \App\Models\PdfAccessLog::create([
                'user_id' => auth()->id(),
                'content_id' => $content->id,
                'session_token' => $isAdmin ? 'admin_bypass' : substr($token, 0, 32),
                'ip_address' => $request->ip(),
                'access_granted' => false,
                'failure_reason' => 'insufficient_permissions',
                'accessed_at' => now(),
            ]);
            
            abort(403, 'You do not have permission to view this PDF');
        }

        // Log successful access attempt
        \App\Models\PdfAccessLog::create([
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'session_token' => $isAdmin ? 'admin_bypass' : substr($token, 0, 32),
            'ip_address' => $request->ip(),
            'access_granted' => true,
            'failure_reason' => null,
            'accessed_at' => now(),
        ]);

        // Generate short-lived signed URL for PDF data
        $pdfDataUrl = $this->pdfService->generateSecureUrl($content, 5);
        
        // Log URL generation
        $this->logger->logUrlGeneration($content, $pdfDataUrl, 5, [
            'session_token' => $isAdmin ? 'admin_bypass' : (substr($token, 0, 16) . '...'),
            'viewer_opened' => true,
            'is_admin' => $isAdmin,
        ]);

        // Get watermark data
        $watermarkData = $this->pdfService->getWatermarkData(auth()->user(), $content);

        // Get user data for watermark display
        $user = auth()->user();
        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'ip' => $request->ip(),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];

        // Log access (for existing logging system)
        $this->pdfService->logAccess($content, auth()->user(), [
            'action' => 'viewer_opened',
            'session_token' => $isAdmin ? 'admin_bypass' : (substr($token, 0, 16) . '...'),
            'is_admin' => $isAdmin,
        ]);

        // Return view with X-Frame-Options: SAMEORIGIN to allow embedding in iframes
        // on the same domain (content builder preview, subpage show page).
        // Without this, PerformanceOptimizationMiddleware sets DENY, blocking the iframe.
        return response()
            ->view('secure-pdf.viewer', [
                'content' => $content,
                'pdfDataUrl' => $pdfDataUrl,
                'watermarkData' => $watermarkData,
                'sessionToken' => $token,
                'user' => $userData,
            ])
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Stream PDF data (called by PDF.js).
     * 
     * This is the actual PDF file stream endpoint.
     * Must be called with a valid signed URL.
     * 
     * The signed URL provides security, so we don't require session authentication.
     * This allows PDF.js to load the PDF even if cookies aren't sent properly.
     * 
     * @param Request $request
     * @param Content $content
     * @return Response
     */
    public function stream(Request $request, Content $content)
    {
        // CRITICAL FIX: Check if signature validation should be enforced
        // For development/testing, we may want to bypass signature validation
        // In production, always validate signatures
        $requireSignature = config('secure-pdf.require_signed_urls', true);
        
        if ($requireSignature && !$request->hasValidSignature()) {
            $correlationId = $this->logger->logStreamError(
                $content,
                $request,
                'invalid_signature',
                'Signed URL signature is invalid or expired',
                [
                    'url' => $request->fullUrl(),
                    'has_signature' => $request->has('signature'),
                    'has_expires' => $request->has('expires'),
                    'expires_value' => $request->get('expires'),
                    'current_time' => now()->timestamp,
                ]
            );
            
            Log::warning('SecurePDF: Invalid or expired signed URL', [
                'content_id' => $content->id,
                'url' => $request->fullUrl(),
                'has_signature' => $request->has('signature'),
                'has_expires' => $request->has('expires'),
                'expires_value' => $request->get('expires'),
                'current_time' => now()->timestamp,
                'correlation_id' => $correlationId,
            ]);
            
            return $this->errorResponse(
                403,
                'Access Denied',
                'The PDF access link has expired or is invalid. Please refresh the page to get a new link.',
                $correlationId
            );
        }
        
        // Get user (may be null if accessed via signed URL without session)
        $user = auth()->user();
        $userId = $user ? $user->id : null;
        
        // Log stream attempt for debugging
        Log::info('SecurePDF: Stream request received', [
            'content_id' => $content->id,
            'user_id' => $userId,
            'has_signature' => $request->has('signature'),
            'has_expires' => $request->has('expires'),
            'url' => $request->fullUrl(),
        ]);
        
        // Verify content type
        if ($content->type !== 'pdf') {
            // Log stream error with PdfStreamLogger
            $correlationId = $this->logger->logStreamError(
                $content,
                $request,
                'invalid_content_type',
                'Content type is not PDF',
                [
                    'expected_type' => 'pdf',
                    'actual_type' => $content->type,
                ]
            );
            
            // Log failed access attempt to PdfAccessLog table
            \App\Models\PdfAccessLog::create([
                'user_id' => $userId,
                'content_id' => $content->id,
                'session_token' => 'signed_url',
                'ip_address' => $request->ip(),
                'access_granted' => false,
                'failure_reason' => 'invalid_content_type',
                'accessed_at' => now(),
            ]);
            
            Log::warning('SecurePDF: Invalid content type', [
                'content_id' => $content->id,
                'expected_type' => 'pdf',
                'actual_type' => $content->type,
                'user_id' => $userId,
                'correlation_id' => $correlationId,
            ]);
            
            // Return proper error response for PDF.js (Requirement 6.3)
            return $this->errorResponse(
                404,
                'Document Not Found',
                'The requested document is not available.',
                $correlationId
            );
        }

        // For signed URLs, the signature itself provides security (Requirement 4.2)
        // The signed URL generation already validates permissions, so we trust the signature
        // This allows PDF.js to fetch PDFs without session-based permission checks

        // Check if content is active
        if (!$content->is_active) {
            // Log access denied with PdfStreamLogger
            $correlationId = $this->logger->logAccessDenied(
                $content,
                $request,
                'inactive_content',
                ['is_active' => false]
            );
            
            // Log failed access attempt to PdfAccessLog table
            \App\Models\PdfAccessLog::create([
                'user_id' => $userId,
                'content_id' => $content->id,
                'session_token' => 'signed_url',
                'ip_address' => $request->ip(),
                'access_granted' => false,
                'failure_reason' => 'inactive_content',
                'accessed_at' => now(),
            ]);
            
            Log::warning('SecurePDF: Inactive content access attempt', [
                'content_id' => $content->id,
                'user_id' => $userId,
                'correlation_id' => $correlationId,
            ]);
            
            // Return proper error response for PDF.js (Requirement 6.3)
            return $this->errorResponse(
                403,
                'Access Denied',
                'This document is not currently available.',
                $correlationId
            );
        }

        // Find file on storage
        $storageDisk = $content->storage_disk ?? 'protected';
        $disk = Storage::disk($storageDisk);
        
        if (!$disk->exists($content->file_path)) {
            // Try other disks as fallback
            $disksToTry = ['protected', 'private', 'public'];
            $fileFound = false;
            
            foreach ($disksToTry as $tryDisk) {
                if (Storage::disk($tryDisk)->exists($content->file_path)) {
                    $storageDisk = $tryDisk;
                    $disk = Storage::disk($tryDisk);
                    $fileFound = true;
                    break;
                }
            }
            
            if (!$fileFound) {
                // Log stream error with PdfStreamLogger
                $correlationId = $this->logger->logStreamError(
                    $content,
                    $request,
                    'file_not_found',
                    'PDF file not found in storage',
                    [
                        'file_path' => $content->file_path,
                        'tried_disks' => $disksToTry,
                    ]
                );
                
                // Log failed access attempt to PdfAccessLog table
                \App\Models\PdfAccessLog::create([
                    'user_id' => $userId,
                    'content_id' => $content->id,
                    'session_token' => 'signed_url',
                    'ip_address' => $request->ip(),
                    'access_granted' => false,
                    'failure_reason' => 'file_not_found',
                    'accessed_at' => now(),
                ]);
                
                Log::error('SecurePDF: File not found', [
                    'content_id' => $content->id,
                    'file_path' => $content->file_path,
                    'tried_disks' => $disksToTry,
                    'correlation_id' => $correlationId,
                ]);
                
                // Return proper error response for PDF.js (Requirement 6.3)
                return $this->errorResponse(
                    404,
                    'Document Not Found',
                    'The requested document file could not be found.',
                    $correlationId
                );
            }
        }

        $path = $disk->path($content->file_path);
        $fileSize = filesize($path);

        // Log successful stream with PdfStreamLogger (Requirement 5.4)
        $correlationId = $this->logger->logSuccessfulStream($content, $request, [
            'file_size' => $fileSize,
            'storage_disk' => $storageDisk,
            'is_range_request' => $request->hasHeader('Range'),
        ]);

        // Log successful access attempt to PdfAccessLog table
        \App\Models\PdfAccessLog::create([
            'user_id' => $userId,
            'content_id' => $content->id,
            'session_token' => 'signed_url',
            'ip_address' => $request->ip(),
            'access_granted' => true,
            'failure_reason' => null,
            'accessed_at' => now(),
        ]);

        // Log access (for existing logging system) - only if user is authenticated
        if ($user) {
            $this->pdfService->logAccess($content, $user, [
                'action' => 'pdf_streamed',
                'file_size' => $fileSize,
                'storage_disk' => $storageDisk,
                'correlation_id' => $correlationId,
            ]);
        }

        Log::info('SecurePDF: Stream access granted', [
            'user_id' => $userId,
            'content_id' => $content->id,
            'file_size' => $fileSize,
            'ip' => $request->ip(),
            'authenticated' => $user ? 'yes' : 'no',
            'correlation_id' => $correlationId,
        ]);

        // Handle range requests for efficient streaming (Requirement 3.4)
        $rangeHeader = $request->header('Range');
        
        if ($rangeHeader) {
            return $this->streamRangeResponse($path, $fileSize, $rangeHeader, $content, $request);
        }

        // Return full PDF with strict security headers
        $headers = [
            // Content headers (Requirement 3.1)
            'Content-Type' => 'application/pdf',
            'Content-Length' => $fileSize,
            'Content-Disposition' => 'inline; filename="' . basename($content->file_name ?? 'document.pdf') . '"',
            
            // Range request support (Requirement 3.4)
            'Accept-Ranges' => 'bytes',
            
            // CORS headers (Requirement 3.2) - Allow same-origin requests
            'Access-Control-Allow-Origin' => $request->header('Origin') ?? config('app.url'),
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Range, Content-Type',
            'Access-Control-Expose-Headers' => 'Content-Length, Content-Range, Accept-Ranges',
            
            // Strict caching headers - no caching
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            
            // Security headers
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN', // Allow iframe on same domain
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            
            // Content Security Policy - restrict what PDF can do
            'Content-Security-Policy' => "default-src 'none'; object-src 'self'; plugin-types application/pdf; frame-ancestors 'self'; script-src 'none';",
        ];

        return response()->file($path, $headers);
    }

    /**
     * Stream a range response for partial content requests.
     * 
     * Supports HTTP range requests for efficient PDF streaming.
     * PDF.js uses range requests to load only the parts of the PDF it needs.
     * 
     * @param string $path Full path to the PDF file
     * @param int $fileSize Total size of the file in bytes
     * @param string $rangeHeader The Range header value (e.g., "bytes=0-1023")
     * @param Content $content The content model
     * @param Request $request The HTTP request
     * @return Response
     */
    protected function streamRangeResponse(string $path, int $fileSize, string $rangeHeader, Content $content, Request $request): Response
    {
        // Parse range header (format: "bytes=start-end")
        if (!preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches)) {
            // Invalid range format - log error
            $correlationId = $this->logger->logStreamError(
                $content,
                $request,
                'invalid_range_format',
                'Invalid Range header format',
                [
                    'range_header' => $rangeHeader,
                    'file_size' => $fileSize,
                ]
            );
            
            Log::warning('SecurePDF: Invalid range header format', [
                'content_id' => $content->id,
                'range_header' => $rangeHeader,
                'correlation_id' => $correlationId,
            ]);
            
            // Return proper error response for PDF.js (Requirement 6.3)
            return response('Invalid Range header', 416)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Range', "bytes */{$fileSize}")
                ->header('X-Error-Type', 'invalid_range_format')
                ->header('X-Correlation-ID', $correlationId);
        }

        $start = (int) $matches[1];
        $end = !empty($matches[2]) ? (int) $matches[2] : $fileSize - 1;

        // Validate range
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            // Range not satisfiable - log error
            $correlationId = $this->logger->logStreamError(
                $content,
                $request,
                'range_not_satisfiable',
                'Requested range is not satisfiable',
                [
                    'range_header' => $rangeHeader,
                    'start' => $start,
                    'end' => $end,
                    'file_size' => $fileSize,
                ]
            );
            
            Log::warning('SecurePDF: Range not satisfiable', [
                'content_id' => $content->id,
                'range_header' => $rangeHeader,
                'file_size' => $fileSize,
                'start' => $start,
                'end' => $end,
                'correlation_id' => $correlationId,
            ]);
            
            // Return proper error response for PDF.js (Requirement 6.3)
            return response('Range Not Satisfiable', 416)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Range', "bytes */{$fileSize}")
                ->header('X-Error-Type', 'range_not_satisfiable')
                ->header('X-Correlation-ID', $correlationId);
        }

        $length = $end - $start + 1;

        // Log range request with PdfStreamLogger
        $correlationId = $this->logger->logRangeRequest($content, $request, $start, $end, $fileSize);

        // Read the requested byte range
        $handle = fopen($path, 'rb');
        fseek($handle, $start);
        $content_data = fread($handle, $length);
        fclose($handle);

        Log::info('SecurePDF: Range request served', [
            'content_id' => $content->id,
            'range' => "{$start}-{$end}",
            'length' => $length,
            'file_size' => $fileSize,
            'correlation_id' => $correlationId,
        ]);

        // Return partial content with 206 status
        $headers = [
            // Content headers
            'Content-Type' => 'application/pdf',
            'Content-Length' => $length,
            'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
            'Accept-Ranges' => 'bytes',
            
            // CORS headers - Allow same-origin requests
            'Access-Control-Allow-Origin' => request()->header('Origin') ?? config('app.url'),
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Range, Content-Type',
            'Access-Control-Expose-Headers' => 'Content-Length, Content-Range, Accept-Ranges',
            
            // Strict caching headers
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            
            // Security headers
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
        ];

        return response($content_data, 206, $headers);
    }

    /**
     * Log page view (called via AJAX from PDF.js viewer).
     * 
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Http\JsonResponse
     */
    public function logPageView(Request $request, Content $content)
    {
        $request->validate([
            'page_number' => 'required|integer|min:1',
            'total_pages' => 'required|integer|min:1',
            'session_token' => 'required|string',
        ]);

        // Validate session token
        $session = $this->pdfService->validateSessionToken($request->session_token);
        
        if (!$session || $session['content_id'] !== $content->id || $session['user_id'] !== auth()->id()) {
            return response()->json(['error' => 'Invalid session'], 403);
        }

        // Log page view
        $this->pdfService->logPageView(
            $content,
            auth()->user(),
            $request->page_number,
            $request->total_pages
        );

        return response()->json(['success' => true]);
    }

    /**
     * Log PDF viewer error (called via AJAX from PDF.js viewer).
     * Requirements 6.3, 6.4: Log errors with full context
     * 
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Http\JsonResponse
     */
    public function logError(Request $request, Content $content)
    {
        $request->validate([
            'error_type' => 'required|string',
            'error_message' => 'required|string',
            'error_details' => 'nullable|array',
            'session_token' => 'nullable|string',
        ]);

        // Get user (may be null if not authenticated)
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        // Log error with PdfStreamLogger
        $correlationId = $this->logger->logViewerError(
            $content,
            $request,
            $request->error_type,
            $request->error_message,
            array_merge($request->error_details ?? [], [
                'session_token' => $request->session_token ? substr($request->session_token, 0, 16) . '...' : null,
                'user_id' => $userId,
            ])
        );

        // Also log to Laravel log for monitoring
        Log::error('PDF Viewer Error', [
            'content_id' => $content->id,
            'error_type' => $request->error_type,
            'error_message' => $request->error_message,
            'error_details' => $request->error_details,
            'user_id' => $userId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'correlation_id' => $correlationId,
        ]);

        return response()->json([
            'success' => true,
            'correlation_id' => $correlationId,
        ]);
    }

    /**
     * Log DevTools detection (called via AJAX from PDF.js viewer).
     * Requirement 3.7: Detect and log developer tools usage
     * 
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Http\JsonResponse
     */
    public function logDevToolsDetection(Request $request, Content $content)
    {
        $request->validate([
            'session_token' => 'required|string',
            'timestamp' => 'required|string',
        ]);

        // Get user (may be null if not authenticated)
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        // Log DevTools detection
        Log::warning('PDF Viewer: DevTools Detected', [
            'content_id' => $content->id,
            'user_id' => $userId,
            'session_token' => substr($request->session_token, 0, 16) . '...',
            'timestamp' => $request->timestamp,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get viewing statistics for current user.
     * 
     * @param Request $request
     * @param Content $content
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request, Content $content)
    {
        if (!$this->pdfService->canView($content, auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = $this->pdfService->getViewingStats(auth()->user(), $content);

        return response()->json($stats);
    }

    /**
     * Test route for secure PDF viewer.
     * 
     * This route creates or uses a test PDF content entry and redirects
     * to the secure viewer with a valid session token.
     * 
     * For testing purposes only - should be disabled in production.
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function test()
    {
        // Find or create test content
        $testContent = Content::firstOrCreate(
            [
                'title' => 'Secure PDF Viewer Test Document',
                'type' => 'pdf',
            ],
            [
                'description' => 'Sample PDF for testing the secure viewer implementation',
                'file_path' => 'sample-test.pdf',
                'file_name' => 'sample-test.pdf',
                'original_filename' => 'sample-test.pdf',
                'file_size' => filesize(storage_path('app/protected/sample-test.pdf')),
                'mime_type' => 'application/pdf',
                'storage_disk' => 'protected',
                'is_active' => true,
                'visibility' => \App\Enums\ContentVisibility::STUDENT,
                'section' => \App\Enums\ContentSection::MAIN_CONTENT,
                'section_order' => 1,
                'order_index' => 1,
                'created_by' => auth()->id(),
            ]
        );

        // Generate secure viewer URL
        $viewerUrl = $this->pdfService->generateViewerUrl($testContent, auth()->user());

        // Log test access
        Log::info('Secure PDF Test Route Accessed', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'content_id' => $testContent->id,
            'viewer_url' => $viewerUrl,
            'ip' => request()->ip(),
        ]);

        // Redirect to viewer
        return redirect($viewerUrl);
    }

    /**
     * Format error response for PDF.js compatibility.
     * 
     * Returns a response with appropriate HTTP status code and descriptive
     * error message that PDF.js can interpret and display to users.
     * 
     * Requirement 6.3: Provide error responses that PDF.js can interpret
     * 
     * @param int $statusCode HTTP status code (403, 404, 500, etc.)
     * @param string $errorType User-friendly error type
     * @param string $errorMessage Descriptive error message
     * @param string|null $correlationId Optional correlation ID for tracking
     * @return Response
     */
    protected function errorResponse(
        int $statusCode,
        string $errorType,
        string $errorMessage,
        ?string $correlationId = null
    ): Response {
        // Determine if the request expects JSON (from PDF.js fetch or AJAX)
        $wantsJson = request()->wantsJson() || 
                     request()->expectsJson() || 
                     request()->header('Accept') === 'application/json' ||
                     request()->is('api/*');

        // Build error response data
        $errorData = [
            'error' => true,
            'error_type' => $errorType,
            'message' => $errorMessage,
            'status_code' => $statusCode,
        ];

        // Add correlation ID if provided
        if ($correlationId) {
            $errorData['correlation_id'] = $correlationId;
        }

        // Add timestamp for debugging
        $errorData['timestamp'] = now()->toISOString();

        // Return JSON response if requested, otherwise return plain text
        // PDF.js can handle both, but plain text is simpler for direct browser access
        if ($wantsJson) {
            return response()->json($errorData, $statusCode);
        }

        // Return plain text response with proper content type
        // This ensures PDF.js receives a clear error message
        return response($errorMessage, $statusCode)
            ->header('Content-Type', 'text/plain')
            ->header('X-Error-Type', $errorType)
            ->header('X-Correlation-ID', $correlationId ?? 'none');
    }
}
