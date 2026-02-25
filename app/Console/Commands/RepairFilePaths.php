<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FileRepairService;
use App\Services\BatchRepairResult;
use App\Models\Content;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * RepairFilePaths command provides batch file path repair functionality.
 * 
 * This command identifies and repairs file path inconsistencies by locating actual files
 * and updating database records when inconsistencies are found. It supports both single
 * content repair and batch processing operations with comprehensive reporting.
 * 
 * Requirements: 7.4
 */
class RepairFilePaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:repair-paths 
                            {--content-id= : Repair specific content ID only}
                            {--content-type= : Repair only specific content type (image, document, etc.)}
                            {--batch-size=100 : Number of records to process in each batch}
                            {--limit= : Maximum number of records to process}
                            {--dry-run : Show what would be done without making changes}
                            {--detailed : Show detailed output for each repair}
                            {--report-only : Generate report without performing repairs}
                            {--export-report= : Export detailed report to file (json, csv, txt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair file path inconsistencies in content records by locating actual files and updating database records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = now();
        $correlationId = Str::uuid()->toString();
        
        if (!$this->option('quiet')) {
            $this->displayHeader($correlationId, $startTime);
        }
        
        try {
            // Validate options
            if (!$this->validateOptions()) {
                return 1;
            }
            
            // Get the repair service from the container
            $repairService = app(FileRepairService::class);
            
            // Handle specific content ID repair
            if ($this->option('content-id')) {
                return $this->handleSingleContentRepair($repairService, $correlationId);
            }
            
            // Handle batch repair
            return $this->handleBatchRepair($repairService, $correlationId);
            
        } catch (\Exception $e) {
            $this->error('File path repair failed: ' . $e->getMessage());
            
            Log::error('File path repair command failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'options' => $this->options(),
            ]);
            
            return 1;
        }
    }

    /**
     * Display command header.
     */
    private function displayHeader(string $correlationId, Carbon $startTime): void
    {
        $this->info('=== File Path Repair Tool ===');
        $this->line('Correlation ID: ' . $correlationId);
        $this->line('Started: ' . $startTime->format('Y-m-d H:i:s'));
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE: No changes will be made to the database');
        }
        
        if ($this->option('report-only')) {
            $this->info('REPORT ONLY MODE: Only generating diagnostic report');
        }
        
        $this->newLine();
    }

    /**
     * Validate command options.
     * 
     * @return bool True if options are valid
     */
    private function validateOptions(): bool
    {
        // Validate content ID if provided
        if ($contentId = $this->option('content-id')) {
            if (!is_numeric($contentId) || $contentId <= 0) {
                $this->error('Content ID must be a positive integer');
                return false;
            }
            
            $content = Content::find($contentId);
            if (!$content) {
                $this->error("Content with ID {$contentId} not found");
                return false;
            }
        }
        
        // Validate batch size
        $batchSize = $this->option('batch-size');
        if (!is_numeric($batchSize) || $batchSize <= 0 || $batchSize > 1000) {
            $this->error('Batch size must be between 1 and 1000');
            return false;
        }
        
        // Validate limit
        if ($limit = $this->option('limit')) {
            if (!is_numeric($limit) || $limit <= 0) {
                $this->error('Limit must be a positive integer');
                return false;
            }
        }
        
        // Validate export format
        if ($exportFormat = $this->option('export-report')) {
            $validFormats = ['json', 'csv', 'txt'];
            if (!in_array($exportFormat, $validFormats)) {
                $this->error('Export format must be one of: ' . implode(', ', $validFormats));
                return false;
            }
        }
        
        return true;
    }

    /**
     * Handle single content repair.
     * 
     * @param FilePathRepairService $repairService Repair service
     * @param string $correlationId Correlation ID
     * @return int Exit code
     */
    private function handleSingleContentRepair(FileRepairService $repairService, string $correlationId): int
    {
        $contentId = $this->option('content-id');
        $content = Content::find($contentId);
        
        if (!$this->option('quiet')) {
            $this->info("Repairing content ID: {$contentId}");
            $this->displayContentInfo($content);
        }
        
        $dryRun = $this->option('dry-run');
        $reportOnly = $this->option('report-only');
        
        if ($reportOnly || $dryRun) {
            // Just diagnose, don't repair
            $diagnosticResult = $repairService->diagnoseContent($content, $correlationId);
            $this->displayDiagnosticResult($content, $diagnosticResult);
            
            if ($reportOnly) {
                $this->info('Report completed - no repairs attempted');
                return 0;
            }
        }
        
        if (!$dryRun) {
            // Perform actual repair
            $repairResult = $repairService->repairSingleContent($content, $correlationId);
            $this->displayRepairResult($repairResult);
            
            // Export report if requested
            if ($exportFormat = $this->option('export-report')) {
                $this->exportSingleRepairReport($repairResult, $exportFormat);
            }
        } else {
            $this->warn('DRY RUN: Would attempt repair for this content');
        }
        
        return 0;
    }

    /**
     * Handle batch repair operation.
     * 
     * @param FilePathRepairService $repairService Repair service
     * @param string $correlationId Correlation ID
     * @return int Exit code
     */
    private function handleBatchRepair(FileRepairService $repairService, string $correlationId): int
    {
        $batchSize = (int) $this->option('batch-size');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $contentType = $this->option('content-type');
        $dryRun = $this->option('dry-run');
        $reportOnly = $this->option('report-only');
        $detailed = $this->option('detailed');
        $quiet = $this->option('quiet');
        
        // Build query
        $query = Content::whereNotNull('file_path');
        
        if ($contentType) {
            $query->where('type', $contentType);
        }
        
        if ($limit) {
            $query->limit($limit);
        }
        
        $totalRecords = $query->count();
        
        if (!$quiet) {
            $this->info("Found {$totalRecords} content records to process");
            
            if ($contentType) {
                $this->line("Content type filter: {$contentType}");
            }
            
            if ($limit) {
                $this->line("Processing limit: {$limit} records");
            }
            
            $this->line("Batch size: {$batchSize}");
            $this->newLine();
        }
        
        if ($totalRecords === 0) {
            $this->info('No content records found to process');
            return 0;
        }
        
        // Initialize batch result
        $batchResult = new BatchRepairResult($correlationId);
        
        // Process in batches
        $processed = 0;
        $progressBar = null;
        
        if (!$quiet) {
            $progressBar = $this->output->createProgressBar($totalRecords);
            $progressBar->setFormat('Processing: %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
            $progressBar->start();
        }
        
        $query->chunk($batchSize, function ($contents) use (
            &$processed, 
            $limit, 
            $dryRun, 
            $reportOnly, 
            $detailed, 
            $quiet, 
            $batchResult, 
            $progressBar,
            $repairService,
            $correlationId
        ) {
            foreach ($contents as $content) {
                if ($limit && $processed >= $limit) {
                    return false; // Stop processing
                }
                
                try {
                    if ($reportOnly) {
                        // Just diagnose
                        $diagnosticResult = $repairService->diagnoseContent($content, $correlationId);
                        
                        if ($detailed && !$quiet) {
                            $this->newLine();
                            $this->displayDiagnosticResult($content, $diagnosticResult);
                        }
                    } else {
                        // Repair or simulate repair
                        if ($dryRun) {
                            // Simulate repair
                            $repairResult = $repairService->simulateRepair($content, $correlationId);
                        } else {
                            // Actual repair
                            $repairResult = $repairService->repairSingleContent($content, $correlationId);
                        }
                        
                        $batchResult->addRepairResult($repairResult);
                        
                        if ($detailed && !$quiet) {
                            $this->newLine();
                            $this->displayRepairResult($repairResult);
                        }
                    }
                    
                } catch (\Exception $e) {
                    if (!$quiet) {
                        $this->newLine();
                        $this->error("Error processing content {$content->id}: " . $e->getMessage());
                    }
                    
                    Log::error('Error during batch repair', [
                        'correlation_id' => $correlationId,
                        'content_id' => $content->id,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                $processed++;
                
                if ($progressBar) {
                    $progressBar->advance();
                }
            }
        });
        
        if ($progressBar) {
            $progressBar->finish();
            $this->newLine(2);
        }
        
        // Complete batch result
        $batchResult->complete();
        
        // Display results
        if (!$reportOnly) {
            $this->displayBatchResults($batchResult, $dryRun);
        }
        
        // Export report if requested
        if ($exportFormat = $this->option('export-report')) {
            $this->exportBatchReport($batchResult, $exportFormat);
        }
        
        return 0;
    }

    /**
     * Display content information.
     * 
     * @param Content $content Content record
     */
    private function displayContentInfo(Content $content): void
    {
        $this->line("Content Type: {$content->type}");
        $this->line("File Path: {$content->file_path}");
        $this->line("Storage Disk: " . ($content->storage_disk ?? 'not set'));
        $this->line("Created: " . $content->created_at->format('Y-m-d H:i:s'));
        $this->newLine();
    }

    /**
     * Display diagnostic result.
     * 
     * @param Content $content Content record
     * @param mixed $diagnosticResult Diagnostic result
     */
    private function displayDiagnosticResult(Content $content, $diagnosticResult): void
    {
        $this->line("=== Diagnostic Result for Content {$content->id} ===");
        
        // This would depend on the actual DiagnosticResult implementation
        $this->line("File exists at recorded location: " . ($diagnosticResult->fileExists() ? 'Yes' : 'No'));
        
        if ($diagnosticResult->hasInconsistencies()) {
            $this->warn("Inconsistencies detected:");
            foreach ($diagnosticResult->getInconsistencies() as $inconsistency) {
                $this->line("  - {$inconsistency}");
            }
        } else {
            $this->info("No inconsistencies detected");
        }
        
        if ($actualLocation = $diagnosticResult->getActualLocation()) {
            $this->line("Actual file location: {$actualLocation->getDisk()}:{$actualLocation->getPath()}");
        }
        
        $this->newLine();
    }

    /**
     * Display repair result.
     * 
     * @param \App\Services\RepairResult $repairResult Repair result
     */
    private function displayRepairResult(\App\Services\RepairResult $repairResult): void
    {
        $content = $repairResult->getContent();
        $status = $repairResult->isSuccess() ? 'SUCCESS' : 'FAILURE';
        $statusColor = $repairResult->isSuccess() ? 'info' : 'error';
        
        $this->line("Content {$content->id}: <{$statusColor}>[{$status}]</{$statusColor}> {$repairResult->getDescription()}");
        
        if ($repairResult->hasChanges()) {
            $this->line("Changes made:");
            foreach ($repairResult->getChanges() as $field => $change) {
                $this->line("  {$field}: '{$change['old_value']}' → '{$change['new_value']}'");
            }
        }
        
        if ($repairResult->hasError()) {
            $this->error("Error: " . $repairResult->getError());
        }
        
        $this->newLine();
    }

    /**
     * Display batch repair results.
     * 
     * @param BatchRepairResult $batchResult Batch repair result
     * @param bool $dryRun Whether this was a dry run
     */
    private function displayBatchResults(BatchRepairResult $batchResult, bool $dryRun): void
    {
        $this->info('=== Batch Repair Results ===');
        
        $summary = $batchResult->getSummary();
        $processingStats = $summary['processing_stats'];
        
        $this->line("Total Processed: {$processingStats['total_processed']}");
        $this->line("Successful Repairs: {$processingStats['successful_repairs']}");
        $this->line("Failed Repairs: {$processingStats['failed_repairs']}");
        $this->line("Database Updates: {$processingStats['database_updates']}");
        $this->line("No Action Needed: {$processingStats['no_action_needed']}");
        $this->line("Files Not Found: {$processingStats['files_not_found']}");
        $this->line("Success Rate: {$processingStats['success_rate']}%");
        $this->line("Repair Rate: {$processingStats['repair_rate']}%");
        
        $duration = $batchResult->getDuration();
        if ($duration) {
            $this->line("Duration: " . $this->formatDuration($duration));
            $recordsPerSecond = round($processingStats['total_processed'] / $duration, 2);
            $this->line("Processing Rate: {$recordsPerSecond} records/second");
        }
        
        $overallStatus = $batchResult->getOverallStatus();
        $statusColor = $this->getStatusColor($overallStatus);
        $this->line("Overall Status: <{$statusColor}>" . strtoupper($overallStatus) . "</{$statusColor}>");
        
        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run - no actual changes were made to the database');
        }
        
        $this->newLine();
    }

    /**
     * Export single repair report.
     * 
     * @param \App\Services\RepairResult $repairResult Repair result
     * @param string $format Export format
     */
    private function exportSingleRepairReport(\App\Services\RepairResult $repairResult, string $format): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $contentId = $repairResult->getContent()->id;
        $filename = "file_repair_content_{$contentId}_{$timestamp}.{$format}";
        
        try {
            switch ($format) {
                case 'json':
                    $content = $repairResult->toJson();
                    break;
                    
                case 'txt':
                    $content = $this->generateTextReport($repairResult);
                    break;
                    
                case 'csv':
                    $content = $this->generateCsvReport([$repairResult]);
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Unsupported format: {$format}");
            }
            
            file_put_contents($filename, $content);
            $this->info("Report exported to: {$filename}");
            
        } catch (\Exception $e) {
            $this->error("Failed to export report: " . $e->getMessage());
        }
    }

    /**
     * Export batch repair report.
     * 
     * @param BatchRepairResult $batchResult Batch repair result
     * @param string $format Export format
     */
    private function exportBatchReport(BatchRepairResult $batchResult, string $format): void
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "file_repair_batch_{$timestamp}.{$format}";
        
        try {
            switch ($format) {
                case 'json':
                    $content = $batchResult->toJson(true); // Include detailed results
                    break;
                    
                case 'txt':
                    $content = $batchResult->generateTextReport();
                    break;
                    
                case 'csv':
                    $content = $this->generateCsvReport($batchResult->getRepairResults());
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Unsupported format: {$format}");
            }
            
            file_put_contents($filename, $content);
            $this->info("Report exported to: {$filename}");
            
        } catch (\Exception $e) {
            $this->error("Failed to export report: " . $e->getMessage());
        }
    }

    /**
     * Generate text report for single repair result.
     * 
     * @param \App\Services\RepairResult $repairResult Repair result
     * @return string Text report
     */
    private function generateTextReport(\App\Services\RepairResult $repairResult): string
    {
        $content = $repairResult->getContent();
        $report = [];
        
        $report[] = "=== File Path Repair Report ===";
        $report[] = "Correlation ID: {$repairResult->getCorrelationId()}";
        $report[] = "Timestamp: " . $repairResult->getTimestamp()->format('Y-m-d H:i:s');
        $report[] = "";
        
        $report[] = "=== Content Information ===";
        $report[] = "Content ID: {$content->id}";
        $report[] = "Content Type: {$content->type}";
        $report[] = "File Path: {$content->file_path}";
        $report[] = "Storage Disk: " . ($content->storage_disk ?? 'not set');
        $report[] = "Created: " . $content->created_at->format('Y-m-d H:i:s');
        $report[] = "";
        
        $report[] = "=== Repair Result ===";
        $report[] = "Success: " . ($repairResult->isSuccess() ? 'Yes' : 'No');
        $report[] = "Action: {$repairResult->getAction()}";
        $report[] = "Description: {$repairResult->getDescription()}";
        
        if ($repairResult->hasChanges()) {
            $report[] = "";
            $report[] = "=== Changes Made ===";
            foreach ($repairResult->getChanges() as $field => $change) {
                $report[] = "{$field}: '{$change['old_value']}' → '{$change['new_value']}'";
            }
        }
        
        if ($repairResult->hasError()) {
            $report[] = "";
            $report[] = "=== Error ===";
            $report[] = $repairResult->getError();
        }
        
        return implode("\n", $report);
    }

    /**
     * Generate CSV report for repair results.
     * 
     * @param array $repairResults Array of RepairResult objects
     * @return string CSV content
     */
    private function generateCsvReport(array $repairResults): string
    {
        $csv = [];
        
        // Header
        $csv[] = [
            'Content ID',
            'Content Type',
            'File Path',
            'Storage Disk',
            'Success',
            'Action',
            'Description',
            'Changes Made',
            'Error',
            'Timestamp',
            'Correlation ID'
        ];
        
        // Data rows
        foreach ($repairResults as $result) {
            $content = $result->getContent();
            $changes = [];
            
            foreach ($result->getChanges() as $field => $change) {
                $changes[] = "{$field}: '{$change['old_value']}' → '{$change['new_value']}'";
            }
            
            $csv[] = [
                $content->id,
                $content->type,
                $content->file_path,
                $content->storage_disk ?? '',
                $result->isSuccess() ? 'Yes' : 'No',
                $result->getAction(),
                $result->getDescription(),
                implode('; ', $changes),
                $result->getError() ?? '',
                $result->getTimestamp()->format('Y-m-d H:i:s'),
                $result->getCorrelationId()
            ];
        }
        
        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csv as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Format duration in human-readable format.
     * 
     * @param float $seconds Duration in seconds
     * @return string Formatted duration
     */
    private function formatDuration(float $seconds): string
    {
        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        } elseif ($seconds < 60) {
            return round($seconds, 2) . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . round($remainingSeconds) . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    /**
     * Get color for status display.
     * 
     * @param string $status Status value
     * @return string Color name
     */
    private function getStatusColor(string $status): string
    {
        switch ($status) {
            case 'success':
                return 'info';
            case 'partial_success':
                return 'comment';
            case 'failure':
                return 'error';
            default:
                return 'comment';
        }
    }
}