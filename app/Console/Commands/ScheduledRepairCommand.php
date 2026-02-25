<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FileRepairService;
use App\Services\RepairTriggerService;
use App\Models\Content;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ScheduledRepairCommand handles scheduled automatic repair operations.
 * 
 * This command can be run via cron or Laravel's task scheduler to perform
 * automatic repairs on a schedule. It includes safety limits and monitoring.
 * 
 * Requirements: 7.1, 7.2
 */
class ScheduledRepairCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:scheduled 
                            {--limit=50 : Maximum number of records to process}
                            {--content-types=* : Content types to include (e.g., image,document)}
                            {--storage-disk= : Storage disk to target (public,protected)}
                            {--created-after= : Only process files created after this date}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled file path repair operations with safety limits and monitoring';

    protected FileRepairService $repairService;
    protected RepairTriggerService $triggerService;

    public function __construct(
        FileRepairService $repairService,
        RepairTriggerService $triggerService
    ) {
        parent::__construct();
        $this->repairService = $repairService;
        $this->triggerService = $triggerService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $correlationId = Str::uuid()->toString();
        
        try {
            $this->info("Starting scheduled repair operation (ID: {$correlationId})");
            
            // Get command options
            $options = $this->getRepairOptions();
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');
            
            // Log the start of the operation
            Log::info('ScheduledRepair: Starting scheduled repair operation', [
                'correlation_id' => $correlationId,
                'options' => $options,
                'dry_run' => $dryRun,
                'command' => 'repair:scheduled',
            ]);
            
            // Show configuration
            $this->displayConfiguration($options, $dryRun);
            
            // Confirm operation unless forced
            if (!$force && !$dryRun && !$this->confirm('Proceed with repair operation?')) {
                $this->info('Operation cancelled by user.');
                return self::SUCCESS;
            }
            
            // Check system health before proceeding
            if (!$this->checkSystemHealth()) {
                $this->error('System health check failed. Aborting repair operation.');
                return self::FAILURE;
            }
            
            // Monitor current repair load
            $repairStats = $this->triggerService->monitorRepairOperations();
            if ($repairStats['hourly_repairs'] > 80) { // 80% of hourly limit
                $this->warn('High repair activity detected. Consider reducing load.');
                if (!$force && !$this->confirm('Continue despite high repair activity?')) {
                    return self::SUCCESS;
                }
            }
            
            // Execute the repair operation
            if ($dryRun) {
                $result = $this->performDryRun($options, $correlationId);
            } else {
                $result = $this->performRepair($options, $correlationId);
            }
            
            // Display results
            $this->displayResults($result, $dryRun);
            
            // Log completion
            Log::info('ScheduledRepair: Scheduled repair operation completed', [
                'correlation_id' => $correlationId,
                'success' => $result->getOverallStatus() === 'success',
                'total_processed' => $result->getTotalProcessed(),
                'successful_repairs' => $result->getSuccessfulRepairs(),
                'failed_repairs' => $result->getFailedRepairs(),
                'duration' => $result->getDuration(),
            ]);
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Scheduled repair operation failed: {$e->getMessage()}");
            
            Log::error('ScheduledRepair: Scheduled repair operation failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return self::FAILURE;
        }
    }

    /**
     * Get repair options from command arguments.
     * 
     * @return array
     */
    protected function getRepairOptions(): array
    {
        $options = [];
        
        if ($this->option('limit')) {
            $options['limit'] = (int) $this->option('limit');
        }
        
        if ($contentTypes = $this->option('content-types')) {
            $options['content_types'] = $contentTypes;
        }
        
        if ($storageDisk = $this->option('storage-disk')) {
            $options['storage_disk'] = $storageDisk;
        }
        
        if ($createdAfter = $this->option('created-after')) {
            $options['created_after'] = $createdAfter;
        }
        
        return $options;
    }

    /**
     * Display repair configuration.
     * 
     * @param array $options
     * @param bool $dryRun
     */
    protected function displayConfiguration(array $options, bool $dryRun): void
    {
        $this->info('Repair Configuration:');
        $this->line("  Mode: " . ($dryRun ? 'Dry Run (Preview)' : 'Live Repair'));
        $this->line("  Limit: " . ($options['limit'] ?? 'No limit'));
        
        if (isset($options['content_types'])) {
            $this->line("  Content Types: " . implode(', ', $options['content_types']));
        }
        
        if (isset($options['storage_disk'])) {
            $this->line("  Storage Disk: " . $options['storage_disk']);
        }
        
        if (isset($options['created_after'])) {
            $this->line("  Created After: " . $options['created_after']);
        }
        
        $this->newLine();
    }

    /**
     * Check system health before repair operation.
     * 
     * @return bool
     */
    protected function checkSystemHealth(): bool
    {
        try {
            // Check database connectivity
            Content::count();
            
            // Check storage accessibility
            $publicDisk = \Storage::disk('public');
            $protectedDisk = \Storage::disk('protected');
            
            if (!$publicDisk->exists('.')) {
                $this->error('Public storage disk is not accessible');
                return false;
            }
            
            if (!$protectedDisk->exists('.')) {
                $this->error('Protected storage disk is not accessible');
                return false;
            }
            
            // Check available disk space (warn if less than 1GB)
            $freeSpace = disk_free_space(storage_path());
            if ($freeSpace < 1024 * 1024 * 1024) { // 1GB
                $this->warn('Low disk space detected: ' . $this->formatBytes($freeSpace) . ' remaining');
            }
            
            return true;
            
        } catch (\Exception $e) {
            $this->error("System health check failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Perform dry run repair operation.
     * 
     * @param array $options
     * @param string $correlationId
     * @return \App\Services\BatchRepairResult
     */
    protected function performDryRun(array $options, string $correlationId): \App\Services\BatchRepairResult
    {
        $this->info('Performing dry run...');
        
        // Limit dry run to smaller sample
        $dryRunOptions = array_merge($options, ['limit' => min($options['limit'] ?? 10, 10)]);
        
        $progressBar = $this->output->createProgressBar($dryRunOptions['limit'] ?? 10);
        $progressBar->start();
        
        // Get sample content records
        $query = Content::whereNotNull('file_path');
        
        if (isset($dryRunOptions['content_types'])) {
            $query->whereIn('type', $dryRunOptions['content_types']);
        }
        
        if (isset($dryRunOptions['storage_disk'])) {
            $query->where('storage_disk', $dryRunOptions['storage_disk']);
        }
        
        if (isset($dryRunOptions['created_after'])) {
            $query->where('created_at', '>=', $dryRunOptions['created_after']);
        }
        
        $contents = $query->limit($dryRunOptions['limit'] ?? 10)->get();
        
        $batchResult = new \App\Services\BatchRepairResult($correlationId);
        
        foreach ($contents as $content) {
            $simulatedResult = $this->repairService->simulateRepair($content, $correlationId);
            $batchResult->addRepairResult($simulatedResult);
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        $batchResult->complete();
        return $batchResult;
    }

    /**
     * Perform actual repair operation.
     * 
     * @param array $options
     * @param string $correlationId
     * @return \App\Services\BatchRepairResult
     */
    protected function performRepair(array $options, string $correlationId): \App\Services\BatchRepairResult
    {
        $this->info('Performing repair operation...');
        
        // Estimate total records for progress bar
        $query = Content::whereNotNull('file_path');
        
        if (isset($options['content_types'])) {
            $query->whereIn('type', $options['content_types']);
        }
        
        if (isset($options['storage_disk'])) {
            $query->where('storage_disk', $options['storage_disk']);
        }
        
        if (isset($options['created_after'])) {
            $query->where('created_at', '>=', $options['created_after']);
        }
        
        $totalEstimate = min($query->count(), $options['limit'] ?? 1000);
        
        $progressBar = $this->output->createProgressBar($totalEstimate);
        $progressBar->start();
        
        // Use the repair service with progress callback
        $result = $this->repairService->repairAllContent($options, $correlationId, function () use ($progressBar) {
            $progressBar->advance();
        });
        
        $progressBar->finish();
        $this->newLine();
        
        return $result;
    }

    /**
     * Display repair results.
     * 
     * @param \App\Services\BatchRepairResult $result
     * @param bool $dryRun
     */
    protected function displayResults(\App\Services\BatchRepairResult $result, bool $dryRun): void
    {
        $this->newLine();
        $this->info($dryRun ? 'Dry Run Results:' : 'Repair Results:');
        
        $headers = ['Metric', 'Value'];
        $rows = [
            ['Total Processed', number_format($result->getTotalProcessed())],
            ['Successful Repairs', number_format($result->getSuccessfulRepairs())],
            ['Failed Repairs', number_format($result->getFailedRepairs())],
            ['Database Updates', number_format($result->getDatabaseUpdates())],
            ['Files Not Found', number_format($result->getFilesNotFound())],
            ['Success Rate', number_format($result->getSuccessRate(), 1) . '%'],
            ['Duration', number_format($result->getDuration(), 2) . ' seconds'],
            ['Overall Status', $result->getOverallStatus()],
        ];
        
        $this->table($headers, $rows);
        
        if ($dryRun) {
            $this->info('This was a dry run. No changes were made to the database or files.');
        } else {
            $statusColor = $result->getOverallStatus() === 'success' ? 'info' : 'warn';
            $this->$statusColor("Repair operation completed with status: {$result->getOverallStatus()}");
        }
        
        if ($result->getFailedRepairs() > 0) {
            $this->warn("There were {$result->getFailedRepairs()} failed repairs. Check logs for details.");
        }
    }

    /**
     * Format bytes for human-readable display.
     * 
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}