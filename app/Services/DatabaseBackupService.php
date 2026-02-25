<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * DatabaseBackupService
 *
 * Creates compressed MySQL dumps and uploads them to Google Drive.
 * Works on both Windows (with mysqldump in PATH) and Linux/macOS.
 */
class DatabaseBackupService
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
     * Run a full database backup.
     *
     * @param  array|null  $tables  If provided, only dump these tables.
     * @return array{local_path:string,drive_id:string,filename:string}
     */
    public function backup(?array $tables = null): array
    {
        $localPath = $this->dump($tables);
        $filename  = basename($localPath);
        $drivePath = $this->buildDrivePath();

        $driveId = $this->drive->upload($localPath, $drivePath, $filename);

        Log::info('[Backup] Database backup complete', [
            'local'    => $localPath,
            'drive_id' => $driveId,
            'tables'   => $tables ?? 'all',
        ]);

        return [
            'local_path' => $localPath,
            'drive_id'   => $driveId,
            'filename'   => $filename,
        ];
    }

    /**
     * Clean up local dumps older than the configured retention period.
     */
    public function cleanOldLocalBackups(): int
    {
        $days    = config('backup.local_retention_days', 30);
        $dir     = storage_path('app/' . config('backup.local_path', 'backups/db'));
        $removed = 0;

        if (! is_dir($dir)) {
            return 0;
        }

        foreach (new \DirectoryIterator($dir) as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }
            if (time() - $file->getMTime() > $days * 86400) {
                @unlink($file->getPathname());
                $removed++;
            }
        }

        return $removed;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Internal
    // ────────────────────────────────────────────────────────────────────────

    protected function dump(?array $tables): string
    {
        $dir = storage_path('app/' . config('backup.local_path', 'backups/db'));
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stamp    = now()->format('Y-m-d_H-i-s');
        $suffix   = $tables ? 'partial' : 'full';
        $sqlFile  = "{$dir}/{$stamp}_{$suffix}.sql";
        $gzFile   = "{$sqlFile}.gz";

        $binary  = config('backup.database.dump_binary', 'mysqldump');
        $host    = config('database.connections.mysql.host', '127.0.0.1');
        $port    = config('database.connections.mysql.port', 3306);
        $db      = config('database.connections.mysql.database');
        $user    = config('database.connections.mysql.username');
        $pass    = config('database.connections.mysql.password');

        // Build the mysqldump command
        $cmd = array_filter([
            $binary,
            "--host={$host}",
            "--port={$port}",
            "--user={$user}",
            $pass ? "--password={$pass}" : null,
            '--single-transaction',
            '--routines',
            '--triggers',
            $db,
        ]);

        if ($tables) {
            array_push($cmd, ...$tables);
        }

        // Run mysqldump and capture output
        $process = new Process($cmd);
        $process->setTimeout((int) config('backup.ffmpeg.timeout', 3600));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(
                "mysqldump failed: " . $process->getErrorOutput()
            );
        }

        file_put_contents($sqlFile, $process->getOutput());

        // Compress with gzip if available, else store plain SQL
        if (config('backup.database.compress', true) && function_exists('gzopen')) {
            $gz = gzopen($gzFile, 'wb9');
            gzwrite($gz, file_get_contents($sqlFile));
            gzclose($gz);
            @unlink($sqlFile);
            return $gzFile;
        }

        return $sqlFile;
    }

    protected function buildDrivePath(): string
    {
        $base = config('backup.google_drive.folder_structure.database', 'Backups/Database');
        return $base . '/' . now()->format('Y') . '/' . now()->format('m');
    }
}
