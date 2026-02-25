<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Jobs\VideoProcessingJob;

/**
 * VideoService
 *
 * Entry point for all video operations:
 *   - Accepts an uploaded file and dispatches async FFmpeg + R2 processing
 *   - Returns streaming URLs for existing videos
 *   - Handles deletion from live R2 (backup copies are preserved)
 */
class VideoService
{
    // ────────────────────────────────────────────────────────────────────────
    // Upload & Processing
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Store the uploaded video to a temporary local path and dispatch the
     * async processing job (FFmpeg HLS conversion + R2 upload).
     *
     * @param  UploadedFile  $file
     * @param  int           $courseId   Used to organise R2 path
     * @param  int           $lessonId   Used to organise R2 path
     * @param  mixed         $model      Optional Eloquent model updated on completion
     * @return string  Job dispatch ID (for status tracking)
     */
    public function uploadAndProcess(
        UploadedFile $file,
        int $courseId,
        int $lessonId,
        mixed $model = null
    ): string {
        // Store original in local temp area — job will clean it up
        $tempPath = $file->store("temp/videos/{$courseId}/{$lessonId}", 'local');

        Log::info('[Video] Dispatched processing job', [
            'course'  => $courseId,
            'lesson'  => $lessonId,
            'temp'    => $tempPath,
            'size_mb' => round($file->getSize() / 1048576, 2),
        ]);

        $job = new VideoProcessingJob(
            tempPath: $tempPath,
            courseId: $courseId,
            lessonId: $lessonId,
            originalName: $file->getClientOriginalName(),
            model: $model,
        );

        dispatch($job)->onQueue('video-processing');

        return $job->getJobId();
    }

    // ────────────────────────────────────────────────────────────────────────
    // Streaming URL
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Build the public HLS master playlist URL for the given course/lesson.
     */
    public function getStreamUrl(int $courseId, int $lessonId): string
    {
        $publicBase = rtrim(config('filesystems.disks.r2.url', ''), '/');
        return "{$publicBase}/videos/{$courseId}/{$lessonId}/master.m3u8";
    }

    // ────────────────────────────────────────────────────────────────────────
    // Deletion (live only — backup copies are kept)
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Delete all live HLS files for a lesson from R2.
     * The original in r2-backup is NOT touched.
     */
    public function deleteVideo(int $courseId, int $lessonId): bool
    {
        $prefix = "videos/{$courseId}/{$lessonId}";

        try {
            $files = Storage::disk('r2')->allFiles($prefix);
            foreach ($files as $file) {
                Storage::disk('r2')->delete($file);
            }

            Log::info('[Video] Deleted live video from R2', [
                'course' => $courseId,
                'lesson' => $lessonId,
                'files'  => count($files),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('[Video] Failed to delete from R2', [
                'course' => $courseId,
                'lesson' => $lessonId,
                'error'  => $e->getMessage(),
            ]);
            return false;
        }
    }
}
