<?php

namespace App\Jobs;

use App\Services\FileBackupService;
use App\Services\GoogleDriveService;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Media\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * VideoProcessingJob
 *
 * Queued job that:
 *   1. Opens the temp-stored video with FFmpeg
 *   2. Converts it to multi-rendition HLS (360p / 720p / 1080p)
 *   3. Uploads every HLS file to Cloudflare R2 under videos/{course}/{lesson}/
 *   4. Backs up the original MP4 to R2 backup bucket + Google Drive
 *   5. Optionally updates an Eloquent model with the stream URL
 *   6. Cleans up local temp files
 */
class VideoProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;    // 1 hour — large videos take time
    public int $tries   = 2;

    protected string $jobId;

    public function __construct(
        public readonly string $tempPath,
        public readonly int    $courseId,
        public readonly int    $lessonId,
        public readonly string $originalName = 'video.mp4',
        public readonly mixed  $model = null,
    ) {
        $this->jobId = Str::uuid()->toString();
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Handle
    // ────────────────────────────────────────────────────────────────────────

    public function handle(FileBackupService $backup): void
    {
        $localAbsPath = storage_path("app/private/{$this->tempPath}");

        Log::info('[VideoJob] Starting', [
            'job'    => $this->jobId,
            'file'   => $localAbsPath,
            'course' => $this->courseId,
            'lesson' => $this->lessonId,
        ]);

        try {
            // 1. Backup original BEFORE conversion (so even a corrupt source is saved)
            $this->backupOriginal($backup, $localAbsPath);

            // 2. Convert to HLS renditions
            $hlsDir = $this->convert($localAbsPath);

            // 3. Upload all HLS files to R2
            $this->uploadHlsToR2($hlsDir);

            // 4. Update model if provided
            $this->updateModel();

            Log::info('[VideoJob] Completed', ['job' => $this->jobId]);

        } finally {
            // Always clean up temp files
            $this->cleanup($localAbsPath);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Step 1: Backup original
    // ────────────────────────────────────────────────────────────────────────

    protected function backupOriginal(FileBackupService $backup, string $localPath): void
    {
        try {
            $backup->backupFromDisk('local', $this->tempPath, 'videos');
        } catch (\Throwable $e) {
            // Backup failure should NOT abort the main processing
            Log::warning('[VideoJob] Backup of original failed', [
                'job'   => $this->jobId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Step 2: FFmpeg HLS conversion
    // ────────────────────────────────────────────────────────────────────────

    protected function convert(string $inputPath): string
    {
        $outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hls_' . $this->jobId;
        @mkdir($outDir, 0755, true);

        $ffmpegBin  = config('backup.ffmpeg.binaries', 'ffmpeg');
        $ffprobeBin = config('backup.ffmpeg.ffprobe', 'ffprobe');
        $renditions  = config('backup.ffmpeg.hls_renditions');
        $hlsTime    = config('backup.ffmpeg.hls_time', 6);

        // Build a single FFmpeg pass with multiple outputs (most efficient)
        // We use raw process instead of php-ffmpeg's limited HLS API
        $cmd = [$ffmpegBin, '-i', $inputPath, '-y'];

        $streamMaps = [];
        $i = 0;
        foreach ($renditions as $label => [$width, $height, $vBitrate, $aBitrate]) {
            $rendDir = $outDir . DIRECTORY_SEPARATOR . $label;
            @mkdir($rendDir, 0755, true);

            $cmd = array_merge($cmd, [
                "-vf",          "scale={$width}:{$height}",
                "-c:v",         "libx264",
                "-b:v",         $vBitrate,
                "-c:a",         "aac",
                "-b:a",         $aBitrate,
                "-ar",          "48000",
                "-hls_time",    (string) $hlsTime,
                "-hls_list_size", "0",
                "-hls_segment_filename", $rendDir . DIRECTORY_SEPARATOR . "seg_%03d.ts",
                "-f",           "hls",
                $rendDir . DIRECTORY_SEPARATOR . "playlist.m3u8",
            ]);

            $streamMaps[] = $label;
            $i++;
        }

        $process = new Process($cmd);
        $process->setTimeout((int) config('backup.ffmpeg.timeout', 3600));
        $process->run(function ($type, $buffer) {
            Log::debug('[FFmpeg] ' . trim($buffer));
        });

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('[VideoJob] FFmpeg failed: ' . $process->getErrorOutput());
        }

        // Generate master playlist
        $this->generateMasterPlaylist($outDir, $renditions);

        return $outDir;
    }

    protected function generateMasterPlaylist(string $outDir, array $renditions): void
    {
        $m3u8 = "#EXTM3U\n#EXT-X-VERSION:3\n\n";
        foreach ($renditions as $label => [$width, $height, $vBitrate]) {
            $bandwidth = (int) (rtrim($vBitrate, 'k') * 1000);
            $m3u8 .= "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION={$width}x{$height}\n";
            $m3u8 .= "{$label}/playlist.m3u8\n\n";
        }
        file_put_contents($outDir . DIRECTORY_SEPARATOR . 'master.m3u8', $m3u8);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Step 3: Upload HLS to R2
    // ────────────────────────────────────────────────────────────────────────

    protected function uploadHlsToR2(string $hlsDir): void
    {
        $r2Prefix = "videos/{$this->courseId}/{$this->lessonId}";
        $disk     = Storage::disk('r2');

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($hlsDir, \FilesystemIterator::SKIP_DOTS)
        );

        $count = 0;
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;

            $relative = ltrim(str_replace($hlsDir, '', $file->getPathname()), DIRECTORY_SEPARATOR . '/');
            $r2Key    = $r2Prefix . '/' . str_replace('\\', '/', $relative);

            $disk->put($r2Key, file_get_contents($file->getPathname()), 'public');
            $count++;
        }

        Log::info('[VideoJob] HLS files uploaded to R2', [
            'prefix' => $r2Prefix,
            'files'  => $count,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Step 4: Update model
    // ────────────────────────────────────────────────────────────────────────

    protected function updateModel(): void
    {
        if (! $this->model) {
            return;
        }

        $publicUrl = rtrim(config('filesystems.disks.r2.url', ''), '/');
        $streamUrl = "{$publicUrl}/videos/{$this->courseId}/{$this->lessonId}/master.m3u8";

        try {
            $this->model->update([
                'video_url'    => $streamUrl,
                'video_status' => 'ready',
            ]);
        } catch (\Throwable $e) {
            Log::warning('[VideoJob] Could not update model', ['error' => $e->getMessage()]);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Cleanup
    // ────────────────────────────────────────────────────────────────────────

    protected function cleanup(string $localPath): void
    {
        @unlink($localPath);

        $hlsDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hls_' . $this->jobId;
        if (is_dir($hlsDir)) {
            $this->deleteDir($hlsDir);
        }
    }

    protected function deleteDir(string $dir): void
    {
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        ) as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Failure handling
    // ────────────────────────────────────────────────────────────────────────

    public function failed(\Throwable $e): void
    {
        Log::error('[VideoJob] Failed permanently', [
            'job'    => $this->jobId,
            'course' => $this->courseId,
            'lesson' => $this->lessonId,
            'error'  => $e->getMessage(),
        ]);
    }
}
