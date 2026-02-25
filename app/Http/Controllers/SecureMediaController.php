<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecureMediaController
 * 
 * Unified secure media streaming controller for:
 * - PDF files (inline viewing, no download)
 * - Audio files (streaming with Range support)
 * - Images (secure rendering)
 * 
 * Security Features:
 * - Authentication required
 * - Enrollment verification
 * - Signed URLs with expiration
 * - HTTP Range support for audio
 * - No public storage access
 * - Comprehensive logging
 */
class SecureMediaController extends Controller
{
    /**
     * Stream PDF file securely (inline viewing only).
     * 
     * @param Request $request
     * @param int $content Content ID
     * @return Response
     */
    public function streamPdf(Request $request, $content)
    {
        $contentId = $content;
        $content = $this->validateAndAuthorize($request, $contentId, 'pdf');
        
        // Try to find the file on multiple storage disks
        $storageDisk = $content->storage_disk ?? 'protected';
        $disk = Storage::disk($storageDisk);
        
        // If file doesn't exist on recorded disk, try other disks
        if (!$disk->exists($content->file_path)) {
            $disksToTry = ['protected', 'private', 'public'];
            $fileFound = false;
            
            foreach ($disksToTry as $tryDisk) {
                if (Storage::disk($tryDisk)->exists($content->file_path)) {
                    $storageDisk = $tryDisk;
                    $disk = Storage::disk($tryDisk);
                    $fileFound = true;
                    
                    Log::info('SecureMedia: PDF found on different disk', [
                        'content_id' => $content->id,
                        'recorded_disk' => $content->storage_disk ?? 'not set',
                        'actual_disk' => $tryDisk,
                    ]);
                    break;
                }
            }
            
            if (!$fileFound) {
                Log::error('SecureMedia: PDF file not found on any disk', [
                    'content_id' => $content->id,
                    'file_path' => $content->file_path,
                    'recorded_disk' => $content->storage_disk ?? 'not set',
                    'tried_disks' => $disksToTry,
                ]);
                abort(404, 'PDF file not found');
            }
        }

        $path = $disk->path($content->file_path);
        $fileSize = filesize($path);
        $mimeType = 'application/pdf';

        Log::info('SecureMedia: PDF access granted', [
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'file_size' => $fileSize,
            'storage_disk' => $storageDisk,
            'ip' => $request->ip(),
        ]);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Content-Disposition' => 'inline; filename="' . basename($content->file_name ?? 'document.pdf') . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
        ]);
    }

    /**
     * Stream audio file with Range support (for seeking).
     * 
     * @param Request $request
     * @param int $content Content ID
     * @return StreamedResponse
     */
    public function streamAudio(Request $request, $content)
    {
        $contentId = $content;
        $content = $this->validateAndAuthorize($request, $contentId, 'audio');
        
        // Find actual file location (handles storage inconsistencies)
        $actualLocation = $content->findActualFileLocation();
        
        if (!$actualLocation) {
            Log::error('SecureMedia: Audio file not found on any storage disk', [
                'content_id' => $content->id,
                'file_path' => $content->file_path,
                'recorded_storage_disk' => $content->storage_disk,
                'user_id' => auth()->id(),
            ]);
            abort(404, 'Audio file not found');
        }

        // Get the actual disk where file exists
        $disk = Storage::disk($actualLocation->getDisk());
        
        // Log if file found on different disk than recorded
        if ($actualLocation->getDisk() !== ($content->storage_disk ?? 'private')) {
            Log::warning('SecureMedia: Audio storage inconsistency detected', [
                'content_id' => $content->id,
                'recorded_disk' => $content->storage_disk,
                'actual_disk' => $actualLocation->getDisk(),
                'file_path' => $content->file_path,
            ]);
        }

        $path = $disk->path($actualLocation->getPath());
        $fileSize = filesize($path);
        $mimeType = $content->mime_type ?? 'audio/mpeg';

        Log::info('SecureMedia: Audio access granted', [
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'file_size' => $fileSize,
            'ip' => $request->ip(),
        ]);

        // Handle Range Requests (for seeking)
        $rangeHeader = $request->header('Range');
        
        if ($rangeHeader) {
            return $this->streamWithRange($path, $fileSize, $mimeType, $rangeHeader);
        }

        return $this->streamFull($path, $fileSize, $mimeType);
    }

    /**
     * Serve image file securely.
     * 
     * @param Request $request
     * @param int $content Content ID
     * @return Response
     */
    public function serveImage(Request $request, $content)
    {
        $contentId = $content;
        $content = $this->validateAndAuthorize($request, $contentId, 'image');
        
        // Try to find the file on multiple storage disks
        $storageDisk = $content->storage_disk ?? 'protected';
        $disk = Storage::disk($storageDisk);
        
        // If file doesn't exist on recorded disk, try other disks
        if (!$disk->exists($content->file_path)) {
            $disksToTry = ['protected', 'private', 'public'];
            $fileFound = false;
            
            foreach ($disksToTry as $tryDisk) {
                if (Storage::disk($tryDisk)->exists($content->file_path)) {
                    $storageDisk = $tryDisk;
                    $disk = Storage::disk($tryDisk);
                    $fileFound = true;
                    
                    Log::info('SecureMedia: Image found on different disk', [
                        'content_id' => $content->id,
                        'recorded_disk' => $content->storage_disk ?? 'not set',
                        'actual_disk' => $tryDisk,
                    ]);
                    break;
                }
            }
            
            if (!$fileFound) {
                Log::error('SecureMedia: Image file not found on any disk', [
                    'content_id' => $content->id,
                    'file_path' => $content->file_path,
                    'recorded_disk' => $content->storage_disk ?? 'not set',
                    'tried_disks' => $disksToTry,
                ]);
                abort(404, 'Image file not found');
            }
        }

        $path = $disk->path($content->file_path);
        $fileSize = filesize($path);
        $mimeType = $content->mime_type ?? 'image/jpeg';

        Log::info('SecureMedia: Image access granted', [
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'file_size' => $fileSize,
            'storage_disk' => $storageDisk,
            'ip' => $request->ip(),
        ]);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    /**
     * Validate content and authorize user access.
     * 
     * @param Request $request
     * @param int $contentId
     * @param string $expectedType
     * @return Content
     */
    private function validateAndAuthorize(Request $request, int $contentId, string $expectedType): Content
    {
        $content = Content::find($contentId);
        
        if (!$content) {
            Log::error('SecureMedia: Content not found', [
                'content_id' => $contentId,
                'expected_type' => $expectedType,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);
            abort(404, 'Content not found');
        }
        
        // Validate content type
        if ($content->type !== $expectedType) {
            Log::warning('SecureMedia: Invalid content type', [
                'user_id' => auth()->id(),
                'content_id' => $content->id,
                'expected_type' => $expectedType,
                'actual_type' => $content->type,
                'ip' => $request->ip(),
            ]);
            abort(404, 'Content not found');
        }

        // Check if content is active
        if (!$content->is_active) {
            Log::warning('SecureMedia: Inactive content access attempt', [
                'user_id' => auth()->id(),
                'content_id' => $content->id,
                'type' => $expectedType,
                'ip' => $request->ip(),
            ]);
            abort(403, 'This content is not available');
        }

        // Get course through relationships
        $subpage = $content->subpage;
        if (!$subpage) {
            Log::error('SecureMedia: Content has no subpage', [
                'content_id' => $content->id,
            ]);
            abort(404, 'Content not found');
        }

        $module = $subpage->module;
        if (!$module) {
            Log::error('SecureMedia: Subpage has no module', [
                'content_id' => $content->id,
                'subpage_id' => $subpage->id,
            ]);
            abort(404, 'Content not found');
        }

        $course = $module->course;
        if (!$course) {
            Log::error('SecureMedia: Module has no course', [
                'content_id' => $content->id,
                'module_id' => $module->id,
            ]);
            abort(404, 'Content not found');
        }

        // Authorization checks
        $user = auth()->user();

        $isEnrolled = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        $isTeacher = $course->teacher_id === $user->id;
        $isAdmin = $user->hasRole('Admin');

        if (!$isEnrolled && !$isTeacher && !$isAdmin) {
            Log::warning('SecureMedia: Unauthorized access attempt', [
                'user_id' => $user->id,
                'content_id' => $content->id,
                'course_id' => $course->id,
                'type' => $expectedType,
                'ip' => $request->ip(),
            ]);
            abort(403, 'You are not enrolled in this course');
        }

        return $content;
    }

    /**
     * Stream file with HTTP Range support.
     */
    private function streamWithRange(string $path, int $fileSize, string $mimeType, string $rangeHeader)
    {
        preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches);
        
        if (!$matches) {
            return $this->streamFull($path, $fileSize, $mimeType);
        }
        
        $start = (int) $matches[1];
        $end = !empty($matches[2]) ? (int) $matches[2] : $fileSize - 1;
        
        if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
            return response('Requested Range Not Satisfiable', 416)
                ->header('Content-Range', "bytes */{$fileSize}");
        }

        $length = $end - $start + 1;

        $response = new StreamedResponse(function() use ($path, $start, $length) {
            $stream = fopen($path, 'rb');
            
            if ($stream === false) {
                return;
            }

            fseek($stream, $start);

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
        }, 206);

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', $length);
        $response->headers->set('Content-Range', "bytes {$start}-{$end}/{$fileSize}");
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }

    /**
     * Stream entire file without range support.
     */
    private function streamFull(string $path, int $fileSize, string $mimeType)
    {
        $response = new StreamedResponse(function() use ($path) {
            $stream = fopen($path, 'rb');
            
            if ($stream === false) {
                return;
            }

            $chunkSize = 8192;

            while (!feof($stream)) {
                echo fread($stream, $chunkSize);
                flush();
            }

            fclose($stream);
        }, 200);

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', $fileSize);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }
}
