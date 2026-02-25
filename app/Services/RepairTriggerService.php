<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RepairTriggerService
{
    protected FileRepairService $repairService;
    protected FileStorageDiagnosticService $diagnosticService;
    
    private const REPAIR_THROTTLE_MINUTES = 5;
    private const MAX_REPAIRS_PER_HOUR = 100;
    private const CACHE_PREFIX = 'auto_repair:';

    public function __construct(
        FileRepairService $repairService,
        FileStorageDiagnosticService $diagnosticService
    ) {
        $this->repairService = $repairService;
        $this->diagnosticService = $diagnosticService;
    }

    public function triggerRepairOnInconsistency(Content $content, string $inconsistencyType, array $context = []): bool
    {
        $correlationId = $context['correlation_id'] ?? Str::uuid()->toString();
        
        Log::info('RepairTrigger: Evaluating repair trigger', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'inconsistency_type' => $inconsistencyType,
        ]);

        try {
            if (!$this->shouldAttemptRepair($content, $inconsistencyType, $correlationId)) {
                Log::info('RepairTrigger: Repair throttled', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                ]);
                return false;
            }
            
            // Record the attempt before running diagnostic
            $this->recordRepairAttempt($content, $inconsistencyType, $correlationId);
            
            $diagnostic = $this->diagnosticService->diagnoseFileStorageIssues($content, $correlationId);
            
            if (!$diagnostic->hasInconsistencies()) {
                Log::info('RepairTrigger: No inconsistencies found', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                ]);
                // Still return true as the repair was attempted successfully (no issues found)
                return true;
            }
            
            $repairResult = $this->repairService->repairSingleContent($content, $correlationId);
            
            $this->logRepairResult($repairResult, $inconsistencyType, $correlationId);
            
            return $repairResult->isSuccess();
            
        } catch (\Exception $e) {
            Log::error('RepairTrigger: Error during repair trigger evaluation', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    private function shouldAttemptRepair(Content $content, string $inconsistencyType, string $correlationId): bool
    {
        $throttleKey = self::CACHE_PREFIX . "throttle:{$content->id}";
        if (Cache::has($throttleKey)) {
            return false;
        }
        
        $hourlyKey = self::CACHE_PREFIX . 'hourly_count:' . now()->format('Y-m-d-H');
        $hourlyCount = Cache::get($hourlyKey, 0);
        if ($hourlyCount >= self::MAX_REPAIRS_PER_HOUR) {
            return false;
        }
        
        return true;
    }

    private function recordRepairAttempt(Content $content, string $inconsistencyType, string $correlationId): void
    {
        $throttleKey = self::CACHE_PREFIX . "throttle:{$content->id}";
        Cache::put($throttleKey, true, now()->addMinutes(self::REPAIR_THROTTLE_MINUTES));
        
        $hourlyKey = self::CACHE_PREFIX . 'hourly_count:' . now()->format('Y-m-d-H');
        Cache::increment($hourlyKey, 1);
        Cache::put($hourlyKey, Cache::get($hourlyKey, 1), now()->addHour());
    }

    private function logRepairResult(RepairResult $repairResult, string $inconsistencyType, string $correlationId): void
    {
        if ($repairResult->isSuccess()) {
            Log::info('RepairTrigger: Repair completed successfully', [
                'correlation_id' => $correlationId,
                'content_id' => $repairResult->getContent()->id,
                'inconsistency_type' => $inconsistencyType,
                'action' => $repairResult->getAction(),
            ]);
        } else {
            Log::warning('RepairTrigger: Repair failed', [
                'correlation_id' => $correlationId,
                'content_id' => $repairResult->getContent()->id,
                'inconsistency_type' => $inconsistencyType,
                'error_message' => $repairResult->getError(),
            ]);
        }
    }

    public function scheduleBatchRepair(array $contentIds, string $batchName = null): string
    {
        $jobId = Str::uuid()->toString();
        $batchName = $batchName ?? 'batch_repair_' . now()->format('Y-m-d_H-i-s');
        
        Log::info('RepairTrigger: Scheduling batch repair', [
            'job_id' => $jobId,
            'batch_name' => $batchName,
            'content_count' => count($contentIds),
        ]);
        
        // Store batch job info in cache
        $batchKey = self::CACHE_PREFIX . "batch:{$jobId}";
        Cache::put($batchKey, [
            'job_id' => $jobId,
            'batch_name' => $batchName,
            'content_ids' => $contentIds,
            'status' => 'pending',
            'created_at' => now()->toIso8601String(),
        ], now()->addDay());
        
        // In a real implementation, this would dispatch a queued job
        // For now, we just return the job ID
        return $jobId;
    }

    public function monitorRepairOperations(): array
    {
        $hourlyKey = self::CACHE_PREFIX . 'hourly_count:' . now()->format('Y-m-d-H');
        $hourlyCount = Cache::get($hourlyKey, 0);
        
        // Count throttled content
        $throttledCount = 0;
        $failedCount = 0;
        $activeBatchJobs = 0;
        
        // Get all cache keys for throttled content
        // Note: This is a simplified implementation
        // In production, you'd want a more efficient way to track these
        
        return [
            'hourly_repairs' => $hourlyCount,
            'max_hourly_repairs' => self::MAX_REPAIRS_PER_HOUR,
            'hourly_percentage' => ($hourlyCount / self::MAX_REPAIRS_PER_HOUR) * 100,
            'throttle_minutes' => self::REPAIR_THROTTLE_MINUTES,
            'throttled_content' => $throttledCount,
            'failed_repairs_last_hour' => $failedCount,
            'batch_jobs_active' => $activeBatchJobs,
        ];
    }
}