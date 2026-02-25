<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SecureVideoController
 * 
 * Production-grade secure video streaming controller with:
 * - Authentication & enrollment checks
 * - HTTP Range request support (seeking)
 * - Large file chunked streaming
 * - Comprehensive logging
 * - No direct downloads
 */
class SecureVideoController extends Controller
{
    /**
     * Stream video file with full security and range request support.
     * 
     * @param Request $request
     * @param int $content Content ID
     * @return StreamedResponse
     */
    public function stream(Request $request, $content)
    {
        // Store the original content ID for logging
        $contentId = $content;
        
        // Find the content by ID
        $content = Content::find($contentId);
        
        if (!$content) {
            Log::error('SecureVideo: Content not found', [
                'content_id' => $contentId,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
            abort(404, 'Content not found');
        }
        
        // STEP 1: Validate content type is video
        if ($content->type !== 'video') {
            Log::warning('SecureVideo: Attempt to access non-video content', [
                'user_id' => auth()->id(),
                'content_id' => $content->id,
                'content_type' => $content->type,
                'ip' => $request->ip(),
            ]);
            abort(404, 'Content not found');
        }

        // STEP 2: Check if content is published/active
        if (!$content->is_active) {
            Log::warning('SecureVideo: Attempt to access inactive video', [
                'user_id' => auth()->id(),
                'content_id' => $content->id,
                'ip' => $request->ip(),
            ]);
            abort(403, 'This video is not available');
        }

        // STEP 3: Get course through relationships
        $subpage = $content->subpage;
        if (!$subpage) {
            Log::error('SecureVideo: Content has no subpage', [
                'content_id' => $content->id,
            ]);
            abort(404, 'Content not found');
        }

        $module = $subpage->module;
        if (!$module) {
            Log::error('SecureVideo: Subpage has no module', [
                'content_id' => $content->id,
                'subpage_id' => $subpage->id,
            ]);
            abort(404, 'Content not found');
        }

        $course = $module->course;
        if (!$course) {
            Log::error('SecureVideo: Module has no course', [
                'content_id' => $content->id,
                'module_id' => $module->id,
            ]);
            abort(404, 'Content not found');
        }

        // STEP 4: Authorization checks
        $user = auth()->user();

        // Check if user is enrolled in the course OR is the teacher OR is admin
        $isEnrolled = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        $isTeacher = $course->teacher_id === $user->id;
        $isAdmin = $user->hasRole('Admin');

        if (!$isEnrolled && !$isTeacher && !$isAdmin) {
            Log::warning('SecureVideo: Unauthorized video access attempt', [
                'user_id' => $user->id,
                'content_id' => $content->id,
                'course_id' => $course->id,
                'ip' => $request->ip(),
            ]);
            abort(403, 'You are not enrolled in this course');
        }

        // STEP 5: Find actual file location (handles storage inconsistencies)
        $actualLocation = $content->findActualFileLocation();
        
        if (!$actualLocation) {
            Log::error('SecureVideo: Video file not found on any storage disk', [
                'content_id' => $content->id,
                'file_path' => $content->file_path,
                'recorded_storage_disk' => $content->storage_disk,
                'user_id' => $user->id,
            ]);
            abort(404, 'Video file not found');
        }

        // Get the actual disk where file exists
        $disk = Storage::disk($actualLocation->getDisk());
        
        // Log if file found on different disk than recorded
        if ($actualLocation->getDisk() !== ($content->storage_disk ?? 'private')) {
            Log::warning('SecureVideo: Storage inconsistency detected', [
                'content_id' => $content->id,
                'recorded_disk' => $content->storage_disk,
                'actual_disk' => $actualLocation->getDisk(),
                'file_path' => $content->file_path,
            ]);
        }

        // STEP 6: Get file information
        $path = $disk->path($actualLocation->getPath());
        $fileSize = filesize($path);
        $mimeType = $content->mime_type ?? 'video/mp4';

        // STEP 7: Log access
        Log::info('SecureVideo: Video access granted', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'content_id' => $content->id,
            'course_id' => $course->id,
            'file_size' => $fileSize,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // STEP 8: Handle Range Requests (for seeking)
        $rangeHeader = $request->header('Range');
        
        if ($rangeHeader) {
            return $this->streamWithRange($path, $fileSize, $mimeType, $rangeHeader);
        }

        // STEP 9: Stream entire file (no range request)
        return $this->streamFull($path, $fileSize, $mimeType);
    }

    /**
     * Stream file with HTTP Range support (for video seeking).
     * 
     * @param string $path Absolute file path
     * @param int $fileSize Total file size
     * @param string $mimeType MIME type
     * @param string $rangeHeader Range header value
     * @return StreamedResponse
     */
    private function streamWithRange(string $path, int $fileSize, string $mimeType, string $rangeHeader)
    {
        // Parse Range header (e.g., "bytes=0-1023" or "bytes=1000-")
        preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches);
        
        if (!$matches) {
            // Invalid range header, return full file
            return $this->streamFull($path, $fileSize, $mimeType);
        }
        
        $start = (int) $matches[1];
        $end = !empty($matches[2]) ? (int) $matches[2] : $fileSize - 1;
        
        // Validate range
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            return response('Requested Range Not Satisfiable', 416)
                ->header('Content-Range', "bytes */{$fileSize}");
        }

        $length = $end - $start + 1;

        // Create streamed response
        $response = new StreamedResponse(function() use ($path, $start, $length) {
            $stream = fopen($path, 'rb');
            
            if ($stream === false) {
                Log::error('SecureVideo: Cannot open file for streaming', [
                    'path' => $path,
                ]);
                return;
            }

            // Seek to start position
            fseek($stream, $start);

            // Stream in chunks (8KB chunks for optimal performance)
            $chunkSize = 8192;
            $bytesRemaining = $length;

            while (!feof($stream) && $bytesRemaining > 0) {
                $bytesToRead = min($chunkSize, $bytesRemaining);
                $data = fread($stream, $bytesToRead);
                
                if ($data === false) {
                    break;
                }

                echo $data;
                flush();

                $bytesRemaining -= strlen($data);
            }

            fclose($stream);
        }, 206); // 206 Partial Content

        // Set headers for range response
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', $length);
        $response->headers->set('Content-Range', "bytes {$start}-{$end}/{$fileSize}");
        $response->headers->set('Accept-Ranges', 'bytes');
        
        // Cache control - no caching for security
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }

    /**
     * Stream entire file without range support.
     * 
     * @param string $path Absolute file path
     * @param int $fileSize Total file size
     * @param string $mimeType MIME type
     * @return StreamedResponse
     */
    private function streamFull(string $path, int $fileSize, string $mimeType)
    {
        $response = new StreamedResponse(function() use ($path) {
            $stream = fopen($path, 'rb');
            
            if ($stream === false) {
                Log::error('SecureVideo: Cannot open file for streaming', [
                    'path' => $path,
                ]);
                return;
            }

            // Stream in chunks (8KB chunks)
            $chunkSize = 8192;

            while (!feof($stream)) {
                echo fread($stream, $chunkSize);
                flush();
            }

            fclose($stream);
        }, 200);

        // Set headers
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', $fileSize);
        $response->headers->set('Accept-Ranges', 'bytes');
        
        // Cache control - no caching for security
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
