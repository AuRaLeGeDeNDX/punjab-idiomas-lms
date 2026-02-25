<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupFullCommand extends Command
{
    protected $signature   = 'backup:full';
    protected $description = 'Run a full database backup and display an R2 backup file summary';

    public function handle(DatabaseBackupService $dbService): int
    {
        $this->info('🚀 Running full backup...');
        $this->newLine();

        // 1. Database backup
        $this->components->task('Database dump + Google Drive upload', function () use ($dbService) {
            $dbService->backup();
            return true;
        });

        // 2. Summarise R2 backup file counts
        $this->newLine();
        $this->info('📂 R2 Backup File Summary:');

        $types = ['videos', 'pdfs', 'content'];
        $rows  = [];

        foreach ($types as $type) {
            try {
                $files  = Storage::disk('r2-backup')->allFiles($type);
                $rows[] = [$type, count($files) . ' file(s)'];
            } catch (\Throwable) {
                $rows[] = [$type, '(R2 not configured)'];
            }
        }

        $this->table(['Type', 'Count'], $rows);
        $this->newLine();
        $this->info('✅ Full backup complete!');

        return self::SUCCESS;
    }
}
