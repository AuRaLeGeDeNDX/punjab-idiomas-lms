<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature   = 'backup:database
                                {--tables= : Comma-separated list of specific tables to backup (default: all)}';
    protected $description = 'Create a compressed MySQL backup and upload it to Google Drive';

    public function handle(DatabaseBackupService $service): int
    {
        $tables = null;
        if ($this->option('tables')) {
            $tables = array_map('trim', explode(',', $this->option('tables')));
        }

        $this->info('📦 Starting database backup...');
        if ($tables) {
            $this->line('  Tables: ' . implode(', ', $tables));
        } else {
            $this->line('  Tables: ALL');
        }

        try {
            $result = $service->backup($tables);

            $this->newLine();
            $this->components->twoColumnDetail('Local file', basename($result['local_path']));
            $this->components->twoColumnDetail('Saved to',   storage_path('app/backups/db'));
            $this->components->twoColumnDetail('Drive ID',   $result['drive_id']);
            $this->newLine();

            // Clean old local backups
            $removed = $service->cleanOldLocalBackups();
            if ($removed > 0) {
                $this->line("🗑  Removed {$removed} old local backup(s) past retention window.");
            }

            $this->info('✅ Database backup complete!');
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('❌ Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
