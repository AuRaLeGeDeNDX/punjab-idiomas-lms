<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * FileBackupService
 *
 * Called at upload time for PDFs and other non-video files.
 * Copies the file to both R2 backup bucket and Google Drive.
 * Files are NEVER deleted from backups.
 */
class FileBackupService
{
    protected GoogleDriveService $drive;

    public function __construct(GoogleDriveService $drive)
    {
        $this->drive = $drive;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Public API
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Backup an already-uploaded file from a source disk/path.
     *
     * @param  string  $sourceDisk  Laravel disk name ('local', 'public', 'r2', …)
     * @param  string  $sourcePath  Path within that disk
     * @param  string  $type        'videos' | 'pdfs' | 'content' — used to sort on Drive/R2
     * @return array{r2_path:string, drive_id:string}
     */
    public function backupFromDisk(string $sourceDisk, string $sourcePath, string $type = 'content'): array
    {
        // Build timestamped filename
        $original  = basename($sourcePath);
        $stamp     = now()->format('Y-m-d');
        $filename  = "{$stamp}_{$original}";

        // 1. Copy from source disk to r2-backup disk
        $r2Path = $this->uploadToR2Backup($sourceDisk, $sourcePath, $type, $filename);

        // 2. Get local temp copy → upload to Google Drive
        $tmpPath  = $this->toTempFile($sourceDisk, $sourcePath);
        $driveId  = $this->uploadToDrive($tmpPath, $type, $filename);
        @unlink($tmpPath);

        Log::info('[Backup] File backed up', [
            'type'    => $type,
            'file'    => $filename,
            'r2'      => $r2Path,
            'drive'   => $driveId,
        ]);

        return [
            'r2_path'  => $r2Path,
            'drive_id' => $driveId,
        ];
    }

    /**
     * Backup directly from an UploadedFile (before it's stored anywhere else).
     */
    public function backupUploadedFile(UploadedFile $file, string $type = 'content'): array
    {
        $stamp     = now()->format('Y-m-d');
        $filename  = "{$stamp}_{$file->getClientOriginalName()}";
        $tmpPath   = $file->getRealPath();

        // 1. Upload to R2 backup
        $r2Path = "backups/files/{$type}/{$filename}";
        Storage::disk('r2')->put($r2Path, file_get_contents($tmpPath));

        // 2. Upload to Google Drive
        $driveId = $this->uploadToDrive($tmpPath, $type, $filename);

        Log::info('[Backup] UploadedFile backed up', [
            'type'  => $type,
            'file'  => $filename,
            'drive' => $driveId,
        ]);

        return [
            'r2_path'  => $r2Path,
            'drive_id' => $driveId,
        ];
    }

    // ────────────────────────────────────────────────────────────────────────
    // Internal
    // ────────────────────────────────────────────────────────────────────────

    protected function uploadToR2Backup(string $disk, string $path, string $type, string $filename): string
    {
        $content   = Storage::disk($disk)->get($path);
        $r2Path    = "{$type}/{$filename}";  // r2-backup root is already 'backups/files'
        Storage::disk('r2-backup')->put($r2Path, $content);
        return $r2Path;
    }

    protected function toTempFile(string $disk, string $path): string
    {
        $tmp     = tempnam(sys_get_temp_dir(), 'pib_backup_');
        $content = Storage::disk($disk)->get($path);
        file_put_contents($tmp, $content);
        return $tmp;
    }

    protected function uploadToDrive(string $localPath, string $type, string $filename): string
    {
        $folderMap = config('backup.google_drive.folder_structure');
        $drivePath = $folderMap[$type] ?? "Backups/Files/{$type}";

        return $this->drive->upload($localPath, $drivePath, $filename);
    }
}
