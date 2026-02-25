<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupListCommand extends Command
{
    protected $signature   = 'backup:list
                                {--type=all : Filter by type: database | videos | pdfs | content | all}';
    protected $description = 'List all available backups from Google Drive and local storage, sorted newest first';

    public function handle(): int
    {
        $type = $this->option('type');
        $this->newLine();

        // ── Local DB backups ────────────────────────────────────────────────
        if (in_array($type, ['all', 'database'])) {
            $this->info('📦 Local Database Backups:');
            $dir  = storage_path('app/backups/db');
            $rows = [];

            if (is_dir($dir)) {
                $files = glob($dir . DIRECTORY_SEPARATOR . '*.{sql,gz}', GLOB_BRACE);
                usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));

                foreach ($files as $f) {
                    $rows[] = [
                        basename($f),
                        date('Y-m-d H:i:s', filemtime($f)),
                        $this->humanSize(filesize($f)),
                    ];
                }
            }

            $rows
                ? $this->table(['Filename', 'Date', 'Size'], $rows)
                : $this->line('  (no local database backups found)');

            $this->newLine();
        }

        // ── Google Drive backups ────────────────────────────────────────────
        try {
            $drive = app(GoogleDriveService::class);

            $typePaths = config('backup.google_drive.folder_structure');
            $toList    = $type === 'all' ? $typePaths : [$type => $typePaths[$type] ?? "Backups/Files/{$type}"];

            foreach ($toList as $label => $drivePath) {
                $this->info("☁  Google Drive — {$drivePath}:");
                $rows  = [];
                $files = $drive->listBackups($drivePath);

                foreach ($files as $f) {
                    $rows[] = [
                        $f['name'],
                        $f['modified'],
                        $this->humanSize($f['size']),
                        $f['id'],
                    ];
                }

                $rows
                    ? $this->table(['Filename', 'Modified', 'Size', 'Drive ID'], $rows)
                    : $this->line('  (no backups found in this folder)');

                $this->newLine();
            }

        } catch (\Throwable $e) {
            $this->warn('⚠  Google Drive not available: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    protected function humanSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
        return $bytes . ' B';
    }
}
