<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class RestoreBackupCommand extends Command
{
    protected $signature   = 'backup:restore
                                {type : What to restore: database | files}
                                {--date=latest : Date string to match (e.g. 2026-02-23) or "latest"}
                                {--drive-id= : Restore a specific Google Drive file by ID}
                                {--dry-run : Show what would be restored without actually doing it}';

    protected $description = 'Restore a backup from Google Drive. Lists options if not found automatically.';

    public function handle(GoogleDriveService $drive): int
    {
        $type   = $this->argument('type');
        $date   = $this->option('date');
        $fileId = $this->option('drive-id');
        $dry    = $this->option('dry-run');

        $this->newLine();
        $this->info("🔄 Restore type: {$type}");

        // ── Specific file by ID ─────────────────────────────────────────────
        if ($fileId) {
            return $this->restoreById($drive, $fileId, $type, $dry);
        }

        // ── Find by date / latest ──────────────────────────────────────────
        $drivePath = $this->getDrivePath($type);
        $files     = $drive->listBackups($drivePath);  // already sorted newest first

        if (empty($files)) {
            $this->error("No backups found at Drive path: {$drivePath}");
            return self::FAILURE;
        }

        // Filter by date if not 'latest'
        if ($date !== 'latest') {
            $files = array_values(array_filter($files, fn ($f) => str_contains($f['name'], $date)));
            if (empty($files)) {
                $this->error("No backups matching date: {$date}");
                $this->newLine();
                $this->warn('Available backups:');
                $allFiles = $drive->listBackups($drivePath);
                $this->table(
                    ['#', 'Filename', 'Modified', 'Drive ID'],
                    array_map(fn ($i, $f) => [$i + 1, $f['name'], $f['modified'], $f['id']], array_keys($allFiles), $allFiles)
                );
                $this->newLine();
                $this->line('Tip: use --drive-id=<ID> or --date=YYYY-MM-DD to select a specific backup.');
                return self::FAILURE;
            }
        }

        $chosen = $files[0]; // Most recent match

        $this->table(
            ['Field', 'Value'],
            [
                ['Filename', $chosen['name']],
                ['Modified', $chosen['modified']],
                ['Drive ID', $chosen['id']],
                ['Type',     $type],
            ]
        );

        if ($dry) {
            $this->warn('--dry-run: no actual restore performed.');
            return self::SUCCESS;
        }

        if (! $this->confirm("Restore this backup?")) {
            $this->line('Cancelled.');
            return self::SUCCESS;
        }

        return $this->restoreById($drive, $chosen['id'], $type, false);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Restore helpers
    // ────────────────────────────────────────────────────────────────────────

    protected function restoreById(GoogleDriveService $drive, string $fileId, string $type, bool $dry): int
    {
        if ($type === 'database') {
            return $this->restoreDatabase($drive, $fileId, $dry);
        }

        // Files restore: just download to a recovery folder
        $dest = storage_path('app/recovery/' . now()->format('Y-m-d_H-i-s'));
        if (! $dry) {
            @mkdir($dest, 0755, true);
            $this->info("⬇  Downloading to: {$dest}");
            $drive->download($fileId, $dest . '/restored_file');
            $this->info('✅ File downloaded. Place it manually in the correct location.');
        } else {
            $this->warn("--dry-run: would download Drive ID {$fileId} to {$dest}");
        }

        return self::SUCCESS;
    }

    protected function restoreDatabase(GoogleDriveService $drive, string $fileId, bool $dry): int
    {
        $dest = storage_path('app/recovery/' . now()->format('Y-m-d_H-i-s') . '.sql.gz');
        @mkdir(dirname($dest), 0755, true);

        if ($dry) {
            $this->warn("--dry-run: would download Drive ID {$fileId} to {$dest}");
            $this->warn('Then run:  php artisan backup:restore database --apply=' . $dest);
            return self::SUCCESS;
        }

        $this->info('⬇  Downloading backup from Google Drive...');
        $drive->download($fileId, $dest);
        $this->info("✅ Downloaded to: {$dest}");

        $this->newLine();
        $this->warn('⚠  DATABASE RESTORE INSTRUCTIONS:');
        $this->line('  1. Stop your application (maintenance mode):');
        $this->line('     php artisan down');
        $this->line('  2. Decompress and restore:');
        $this->line('     gunzip -c "' . $dest . '" | mysql -u root -p punjabidomas_lms');
        $this->line('  3. Re-run any new migrations:');
        $this->line('     php artisan migrate');
        $this->line('  4. Bring the app back online:');
        $this->line('     php artisan up');
        $this->newLine();
        $this->info('The backup file is at: ' . $dest);

        return self::SUCCESS;
    }

    protected function getDrivePath(string $type): string
    {
        $map = config('backup.google_drive.folder_structure', []);
        return $map[$type] ?? "Backups/{$type}";
    }
}
