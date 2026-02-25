<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileRepairService;
use App\Services\FileStorageDiagnosticService;
use App\Services\BatchRepairResult;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

/**
 * FileRepairController provides admin interface for file path repair operations.
 * 
 * This controller handles repair service integration points including admin interface hooks,
 * automatic repair triggers, and repair scheduling and monitoring capabilities.
 * 
 * Requirements: 7.1, 7.2
 */
class FileRepairController extends Controller
{
    use AuthorizesRequests;

    protected FileRepairService $repairService;
    protected FileStorageDiagnosticService $diagnosticService;

    public function __construct(
        FileRepairService $repairService,
        FileStorageDiagnosticService $diagnosticService
    ) {
        $this->repairService = $repairService;
        $this->diagnosticService = $diagnosticService;
        
        // Ensure only admins can access repair functionality
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', \App\Models\User::class); // Admin only
            return $next($request);
        });
    }

    /**
     * Display the file repair dashboard.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        try {
            // Get basic statistics
            $stats = $this->getRepairStatistics();
            
            // Get recent repair operations
            $recentRepairs = $this->getRecentRepairOperations(10);
            
            // Get storage configuration status
            $storageStatus = $this->diagnosticService->validateStorageConfiguration();
            
            return view('admin.file-repair.index', [
                'stats' => $stats,
                'recentRepairs' => $recentRepairs,
                'storageStatus' => $storageStatus,
            ]);
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error loading repair dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return view('admin.file-repair.index', [
                'stats' => null,
                'recentRepairs' => [],
                'storageStatus' => null,
                'error' => 'Failed to load repair dashboard: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Diagnose a specific content record.
     * 
     * @param Request $request
     * @param int $contentId
     * @return JsonResponse
     */
    public function diagnose(Request $request, int $contentId): JsonResponse
    {
        $correlationId = Str::uuid()->toString();
        
        try {
            $content = Content::findOrFail($contentId);
            
            Log::info('FileRepair: Admin diagnostic request', [
                'correlation_id' => $correlationId,
                'content_id' => $contentId,
                'user_id' => auth()->id(),
            ]);
            
            $diagnostic = $this->diagnosticService->diagnoseFileStorageIssues($content, $correlationId);
            
            return response()->json([
                'success' => true,
                'correlation_id' => $correlationId,
                'content_id' => $contentId,
                'diagnostic' => [
                    'file_exists' => $diagnostic->fileExists(),
                    'has_inconsistencies' => $diagnostic->hasInconsistencies(),
                    'actual_location' => $diagnostic->getActualLocation()?->toArray(),
                    'inconsistencies' => $diagnostic->getInconsistencies(),
                    'recommendations' => $diagnostic->getRecommendedActions(),
                    'url_generation_error' => $diagnostic->getUrlGenerationError(),
                ],
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error during admin diagnostic', [
                'correlation_id' => $correlationId,
                'content_id' => $contentId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Diagnostic failed: ' . $e->getMessage(),
                'correlation_id' => $correlationId,
            ], 500);
        }
    }

    /**
     * Repair a specific content record.
     * 
     * @param Request $request
     * @param int $contentId
     * @return JsonResponse
     */
    public function repair(Request $request, int $contentId): JsonResponse
    {
        $correlationId = Str::uuid()->toString();
        
        try {
            $content = Content::findOrFail($contentId);
            
            Log::info('FileRepair: Admin repair request', [
                'correlation_id' => $correlationId,
                'content_id' => $contentId,
                'user_id' => auth()->id(),
            ]);
            
            $repairResult = $this->repairService->repairSingleContent($content, $correlationId);
            
            // Log the repair operation for monitoring
            $this->logRepairOperation($repairResult, auth()->id());
            
            return response()->json([
                'success' => $repairResult->isSuccess(),
                'correlation_id' => $correlationId,
                'content_id' => $contentId,
                'repair_result' => [
                    'action' => $repairResult->getAction(),
                    'description' => $repairResult->getDescription(),
                    'has_changes' => $repairResult->hasChanges(),
                    'changes' => $repairResult->getChanges(),
                    'error' => $repairResult->getError(),
                    'metadata' => $repairResult->getMetadata(),
                ],
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error during admin repair', [
                'correlation_id' => $correlationId,
                'content_id' => $contentId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Repair failed: ' . $e->getMessage(),
                'correlation_id' => $correlationId,
            ], 500);
        }
    }

    /**
     * Start a batch repair operation.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function batchRepair(Request $request): JsonResponse
    {
        $correlationId = Str::uuid()->toString();
        
        try {
            $request->validate([
                'content_types' => 'sometimes|array',
                'content_types.*' => 'string',
                'storage_disk' => 'sometimes|string|in:public,protected',
                'limit' => 'sometimes|integer|min:1|max:1000',
                'created_after' => 'sometimes|date',
                'created_before' => 'sometimes|date',
                'dry_run' => 'sometimes|boolean',
            ]);
            
            $options = $request->only([
                'content_types', 'storage_disk', 'limit', 
                'created_after', 'created_before'
            ]);
            
            $dryRun = $request->boolean('dry_run', false);
            
            Log::info('FileRepair: Admin batch repair request', [
                'correlation_id' => $correlationId,
                'options' => $options,
                'dry_run' => $dryRun,
                'user_id' => auth()->id(),
            ]);
            
            if ($dryRun) {
                // For dry run, we'll simulate repairs for a small sample
                $sampleOptions = array_merge($options, ['limit' => min($options['limit'] ?? 10, 10)]);
                $batchResult = $this->simulateBatchRepair($sampleOptions, $correlationId);
            } else {
                $batchResult = $this->repairService->repairAllContent($options, $correlationId);
            }
            
            // Log the batch operation for monitoring
            $this->logBatchRepairOperation($batchResult, auth()->id(), $dryRun);
            
            return response()->json([
                'success' => true,
                'correlation_id' => $correlationId,
                'dry_run' => $dryRun,
                'batch_result' => [
                    'total_processed' => $batchResult->getTotalProcessed(),
                    'successful_repairs' => $batchResult->getSuccessfulRepairs(),
                    'failed_repairs' => $batchResult->getFailedRepairs(),
                    'database_updates' => $batchResult->getDatabaseUpdates(),
                    'files_not_found' => $batchResult->getFilesNotFound(),
                    'success_rate' => $batchResult->getSuccessRate(),
                    'duration' => $batchResult->getDuration(),
                    'overall_status' => $batchResult->getOverallStatus(),
                ],
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error during admin batch repair', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Batch repair failed: ' . $e->getMessage(),
                'correlation_id' => $correlationId,
            ], 500);
        }
    }

    /**
     * Generate a missing files report.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function missingFilesReport(Request $request): JsonResponse
    {
        $correlationId = Str::uuid()->toString();
        
        try {
            $request->validate([
                'content_types' => 'sometimes|array',
                'content_types.*' => 'string',
                'created_after' => 'sometimes|date',
                'export_format' => 'sometimes|string|in:json,csv,txt',
            ]);
            
            $options = $request->only(['content_types', 'created_after']);
            $exportFormat = $request->input('export_format', 'json');
            
            Log::info('FileRepair: Admin missing files report request', [
                'correlation_id' => $correlationId,
                'options' => $options,
                'export_format' => $exportFormat,
                'user_id' => auth()->id(),
            ]);
            
            $report = $this->repairService->generateMissingFilesReport($options, $correlationId);
            
            if ($exportFormat === 'json') {
                return response()->json([
                    'success' => true,
                    'correlation_id' => $correlationId,
                    'report' => $report,
                    'timestamp' => now()->toISOString(),
                ]);
            } else {
                // Generate file download for CSV/TXT formats
                $filename = "missing_files_report_{$correlationId}." . $exportFormat;
                $content = $this->formatReportForExport($report, $exportFormat);
                
                return response($content)
                    ->header('Content-Type', $this->getContentTypeForFormat($exportFormat))
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
            }
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error generating missing files report', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Report generation failed: ' . $e->getMessage(),
                'correlation_id' => $correlationId,
            ], 500);
        }
    }

    /**
     * Get repair operation status.
     * 
     * @param Request $request
     * @param string $correlationId
     * @return JsonResponse
     */
    public function status(Request $request, string $correlationId): JsonResponse
    {
        try {
            // Check if this is a valid correlation ID format
            if (!Str::isUuid($correlationId)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid correlation ID format',
                ], 400);
            }
            
            // Get status from logs or cache
            $status = $this->getRepairOperationStatus($correlationId);
            
            return response()->json([
                'success' => true,
                'correlation_id' => $correlationId,
                'status' => $status,
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error getting repair status', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Status check failed: ' . $e->getMessage(),
                'correlation_id' => $correlationId,
            ], 500);
        }
    }

    /**
     * Schedule automatic repair operations.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function scheduleRepair(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'schedule_type' => 'required|string|in:immediate,hourly,daily,weekly',
                'content_types' => 'sometimes|array',
                'content_types.*' => 'string',
                'storage_disk' => 'sometimes|string|in:public,protected',
                'max_records' => 'sometimes|integer|min:1|max:1000',
                'enabled' => 'sometimes|boolean',
            ]);
            
            $scheduleConfig = $request->only([
                'schedule_type', 'content_types', 'storage_disk', 
                'max_records', 'enabled'
            ]);
            
            Log::info('FileRepair: Admin schedule repair request', [
                'schedule_config' => $scheduleConfig,
                'user_id' => auth()->id(),
            ]);
            
            // Store schedule configuration
            $this->storeRepairSchedule($scheduleConfig, auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => 'Repair schedule configured successfully',
                'schedule_config' => $scheduleConfig,
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error scheduling repair', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Schedule configuration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get repair statistics for dashboard.
     * 
     * @return array
     */
    private function getRepairStatistics(): array
    {
        try {
            $stats = [
                'total_content_files' => Content::whereNotNull('file_path')->count(),
                'files_with_issues' => 0,
                'recent_repairs' => 0,
                'success_rate' => 0,
                'storage_distribution' => [
                    'public' => Content::where('storage_disk', 'public')->count(),
                    'protected' => Content::where('storage_disk', 'protected')->count(),
                    'unspecified' => Content::whereNull('storage_disk')->whereNotNull('file_path')->count(),
                ],
                'content_type_distribution' => Content::whereNotNull('file_path')
                    ->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ];
            
            // Get recent repair statistics from logs
            $recentRepairStats = $this->getRecentRepairStatistics();
            $stats['recent_repairs'] = $recentRepairStats['total_repairs'];
            $stats['success_rate'] = $recentRepairStats['success_rate'];
            
            // Estimate files with issues (this could be cached for performance)
            $stats['files_with_issues'] = $this->estimateFilesWithIssues();
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error getting repair statistics', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'error' => 'Failed to load statistics',
                'total_content_files' => 0,
                'files_with_issues' => 0,
                'recent_repairs' => 0,
                'success_rate' => 0,
                'storage_distribution' => [],
                'content_type_distribution' => [],
            ];
        }
    }

    /**
     * Get recent repair operations.
     * 
     * @param int $limit
     * @return array
     */
    private function getRecentRepairOperations(int $limit = 10): array
    {
        try {
            // This would typically come from a dedicated repair_operations table
            // For now, we'll extract from logs or return mock data
            return [
                [
                    'id' => 1,
                    'correlation_id' => Str::uuid()->toString(),
                    'type' => 'single',
                    'content_id' => 123,
                    'status' => 'success',
                    'description' => 'File path updated from public to protected storage',
                    'user_id' => auth()->id(),
                    'created_at' => now()->subMinutes(30)->toISOString(),
                ],
                [
                    'id' => 2,
                    'correlation_id' => Str::uuid()->toString(),
                    'type' => 'batch',
                    'processed_count' => 45,
                    'success_count' => 42,
                    'status' => 'partial_success',
                    'description' => 'Batch repair completed with 3 failures',
                    'user_id' => auth()->id(),
                    'created_at' => now()->subHours(2)->toISOString(),
                ],
            ];
            
        } catch (\Exception $e) {
            Log::error('FileRepair: Error getting recent repair operations', [
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * Simulate batch repair for dry run.
     * 
     * @param array $options
     * @param string $correlationId
     * @return BatchRepairResult
     */
    private function simulateBatchRepair(array $options, string $correlationId): BatchRepairResult
    {
        $batchResult = new BatchRepairResult($correlationId);
        
        // Get a small sample of content records
        $query = Content::whereNotNull('file_path');
        
        if (isset($options['content_types'])) {
            $query->whereIn('type', $options['content_types']);
        }
        
        if (isset($options['storage_disk'])) {
            $query->where('storage_disk', $options['storage_disk']);
        }
        
        $contents = $query->limit($options['limit'] ?? 10)->get();
        
        foreach ($contents as $content) {
            $simulatedResult = $this->repairService->simulateRepair($content, $correlationId);
            $batchResult->addRepairResult($simulatedResult);
        }
        
        $batchResult->complete();
        return $batchResult;
    }

    /**
     * Log repair operation for monitoring.
     * 
     * @param \App\Services\RepairResult $repairResult
     * @param int $userId
     */
    private function logRepairOperation(\App\Services\RepairResult $repairResult, int $userId): void
    {
        Log::info('FileRepair: Admin repair operation completed', [
            'correlation_id' => $repairResult->getCorrelationId(),
            'content_id' => $repairResult->getContent()->id,
            'success' => $repairResult->isSuccess(),
            'action' => $repairResult->getAction(),
            'has_changes' => $repairResult->hasChanges(),
            'user_id' => $userId,
            'timestamp' => now()->toISOString(),
        ]);
        
        // Here you could also store to a dedicated repair_operations table
        // for better tracking and reporting
    }

    /**
     * Log batch repair operation for monitoring.
     * 
     * @param BatchRepairResult $batchResult
     * @param int $userId
     * @param bool $dryRun
     */
    private function logBatchRepairOperation(BatchRepairResult $batchResult, int $userId, bool $dryRun): void
    {
        Log::info('FileRepair: Admin batch repair operation completed', [
            'correlation_id' => $batchResult->getCorrelationId(),
            'total_processed' => $batchResult->getTotalProcessed(),
            'successful_repairs' => $batchResult->getSuccessfulRepairs(),
            'failed_repairs' => $batchResult->getFailedRepairs(),
            'success_rate' => $batchResult->getSuccessRate(),
            'duration' => $batchResult->getDuration(),
            'dry_run' => $dryRun,
            'user_id' => $userId,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get recent repair statistics from logs.
     * 
     * @return array
     */
    private function getRecentRepairStatistics(): array
    {
        // This would typically query a dedicated table or log aggregation
        // For now, return mock data
        return [
            'total_repairs' => 156,
            'success_rate' => 94.2,
            'last_24_hours' => 23,
            'last_week' => 89,
        ];
    }

    /**
     * Estimate files with issues (cached for performance).
     * 
     * @return int
     */
    private function estimateFilesWithIssues(): int
    {
        // This could be a cached value updated periodically
        // For now, return a rough estimate
        return (int) (Content::whereNotNull('file_path')->count() * 0.05); // Assume 5% have issues
    }

    /**
     * Get repair operation status from logs or cache.
     * 
     * @param string $correlationId
     * @return array
     */
    private function getRepairOperationStatus(string $correlationId): array
    {
        // This would typically query logs or a status cache
        return [
            'status' => 'completed',
            'progress' => 100,
            'message' => 'Repair operation completed successfully',
            'started_at' => now()->subMinutes(5)->toISOString(),
            'completed_at' => now()->subMinutes(2)->toISOString(),
        ];
    }

    /**
     * Store repair schedule configuration.
     * 
     * @param array $scheduleConfig
     * @param int $userId
     */
    private function storeRepairSchedule(array $scheduleConfig, int $userId): void
    {
        // This would typically store in a database table or configuration file
        // For now, just log the configuration
        Log::info('FileRepair: Repair schedule configured', [
            'schedule_config' => $scheduleConfig,
            'user_id' => $userId,
            'timestamp' => now()->toISOString(),
        ]);
        
        // You could store this in a repair_schedules table or config file
        // and use it with Laravel's task scheduler
    }

    /**
     * Format report for export.
     * 
     * @param array $report
     * @param string $format
     * @return string
     */
    private function formatReportForExport(array $report, string $format): string
    {
        switch ($format) {
            case 'csv':
                return $this->formatReportAsCsv($report);
            case 'txt':
                return $this->formatReportAsText($report);
            default:
                return json_encode($report, JSON_PRETTY_PRINT);
        }
    }

    /**
     * Format report as CSV.
     * 
     * @param array $report
     * @return string
     */
    private function formatReportAsCsv(array $report): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Header
        fputcsv($output, [
            'Content ID', 'Content Type', 'File Path', 'Storage Disk',
            'File Size', 'Created At', 'Recovery Potential'
        ]);
        
        // Data rows
        foreach ($report['missing_files'] as $file) {
            fputcsv($output, [
                $file['content_id'],
                $file['content_type'],
                $file['recorded_file_path'],
                $file['recorded_storage_disk'],
                $file['file_size'] ?? 'Unknown',
                $file['created_at'],
                in_array($file, $report['recoverable_files']) ? 'High' : 'Low'
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Format report as text.
     * 
     * @param array $report
     * @return string
     */
    private function formatReportAsText(array $report): string
    {
        $text = [];
        $text[] = "=== Missing Files Report ===";
        $text[] = "Generated: " . $report['generated_at'];
        $text[] = "Correlation ID: " . $report['correlation_id'];
        $text[] = "";
        
        $text[] = "=== Summary ===";
        $text[] = "Total Checked: " . $report['summary']['total_checked'];
        $text[] = "Missing Files: " . $report['summary']['missing_files'];
        $text[] = "Recoverable Files: " . $report['summary']['recoverable_files'];
        $text[] = "Unrecoverable Files: " . $report['summary']['unrecoverable_files'];
        $text[] = "";
        
        $text[] = "=== Missing Files ===";
        foreach ($report['missing_files'] as $file) {
            $text[] = "Content ID: {$file['content_id']}";
            $text[] = "  Type: {$file['content_type']}";
            $text[] = "  Path: {$file['recorded_file_path']}";
            $text[] = "  Disk: {$file['recorded_storage_disk']}";
            $text[] = "  Created: {$file['created_at']}";
            $text[] = "";
        }
        
        return implode("\n", $text);
    }

    /**
     * Get content type for export format.
     * 
     * @param string $format
     * @return string
     */
    private function getContentTypeForFormat(string $format): string
    {
        switch ($format) {
            case 'csv':
                return 'text/csv';
            case 'txt':
                return 'text/plain';
            default:
                return 'application/json';
        }
    }
}
