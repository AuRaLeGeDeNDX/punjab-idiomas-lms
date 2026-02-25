<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class FileRepairService
{
    protected FileStorageDiagnosticService $diagnosticService;

    public function __construct(FileStorageDiagnosticService $diagnosticService)
    {
        $this->diagnosticService = $diagnosticService;
    }

    public function repairSingleContent(Content $content, ?string $correlationId = null): RepairResult
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('FileRepair: Starting single content repair', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
        ]);

        try {
            $diagnostic = $this->diagnosticService->diagnoseFileStorageIssues($content, $correlationId);
            
            if (!$diagnostic->fileExists()) {
                $searchedLocations = $this->getSearchedLocations($content);
                return RepairResult::fileNotFound($content, $searchedLocations, $correlationId);
            }
            
            if (!$diagnostic->hasInconsistencies()) {
                return RepairResult::noActionNeeded($content, $correlationId);
            }
            
            $actualLocation = $diagnostic->getActualLocation();
            if (!$actualLocation) {
                return RepairResult::error($content, 'Inconsistencies detected but no actual location found', $correlationId);
            }
            
            return $this->performDatabaseUpdate($content, $actualLocation, $diagnostic, $correlationId);
            
        } catch (Exception $e) {
            Log::error('FileRepair: Error during single content repair', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
            
            return RepairResult::error($content, 'Repair operation failed: ' . $e->getMessage(), $correlationId);
        }
    }

    public function diagnoseContent(Content $content, ?string $correlationId = null): DiagnosticResult
    {
        return $this->diagnosticService->diagnoseFileStorageIssues($content, $correlationId);
    }

    public function simulateRepair(Content $content, ?string $correlationId = null): RepairResult
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('FileRepair: Simulating repair for content', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
        ]);

        try {
            $diagnostic = $this->diagnosticService->diagnoseFileStorageIssues($content, $correlationId);
            
            if (!$diagnostic->fileExists()) {
                $searchedLocations = $this->getSearchedLocations($content);
                return RepairResult::fileNotFound($content, $searchedLocations, $correlationId);
            }
            
            if (!$diagnostic->hasInconsistencies()) {
                return RepairResult::noActionNeeded($content, $correlationId);
            }
            
            $actualLocation = $diagnostic->getActualLocation();
            if (!$actualLocation) {
                return RepairResult::error($content, 'Inconsistencies detected but no actual location found', $correlationId);
            }
            
            // For simulation, just return success without actually updating the database
            return RepairResult::success($content, $actualLocation, $correlationId);
            
        } catch (Exception $e) {
            Log::error('FileRepair: Error during repair simulation', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
            
            return RepairResult::error($content, 'Repair simulation failed: ' . $e->getMessage(), $correlationId);
        }
    }

    private function getSearchedLocations(Content $content): array
    {
        $recordedDisk = $content->storage_disk ?? 'public';
        $allDisks = ['public', 'protected'];
        
        $searchedLocations = [];
        foreach ($allDisks as $disk) {
            $searchedLocations[] = [
                'disk' => $disk,
                'path' => $content->file_path,
                'is_recorded_location' => $disk === $recordedDisk,
            ];
        }
        
        return $searchedLocations;
    }

    private function performDatabaseUpdate(Content $content, FileLocation $actualLocation, DiagnosticResult $diagnostic, string $correlationId): RepairResult
    {
        try {
            DB::beginTransaction();
            
            $updateData = [];
            
            if ($content->storage_disk !== $actualLocation->getDisk()) {
                $updateData['storage_disk'] = $actualLocation->getDisk();
            }
            
            if ($content->file_path !== $actualLocation->getPath()) {
                $updateData['file_path'] = $actualLocation->getPath();
            }
            
            if (!empty($updateData)) {
                $content->update($updateData);
            }
            
            DB::commit();
            
            return RepairResult::success($content, $actualLocation, $correlationId);
            
        } catch (Exception $e) {
            DB::rollBack();
            return RepairResult::error($content, 'Database update failed: ' . $e->getMessage(), $correlationId);
        }
    }

    public function repairAllContent(array $options = [], ?string $correlationId = null): BatchRepairResult
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('FileRepair: Starting batch repair operation', [
            'correlation_id' => $correlationId,
            'options' => $options,
        ]);

        $batchResult = new BatchRepairResult($correlationId);
        
        try {
            $query = Content::whereNotNull('file_path');
            
            // Apply filters
            if (isset($options['content_types'])) {
                $query->whereIn('type', $options['content_types']);
            }
            
            if (isset($options['storage_disk'])) {
                $query->where('storage_disk', $options['storage_disk']);
            }
            
            if (isset($options['created_after'])) {
                $query->where('created_at', '>=', $options['created_after']);
            }
            
            if (isset($options['created_before'])) {
                $query->where('created_at', '<=', $options['created_before']);
            }
            
            if (isset($options['limit'])) {
                $query->limit($options['limit']);
            }
            
            $contents = $query->get();
            
            foreach ($contents as $content) {
                $repairResult = $this->repairSingleContent($content, $correlationId);
                $batchResult->addRepairResult($repairResult);
            }
            
            $batchResult->complete();
            
            Log::info('FileRepair: Batch repair operation completed', [
                'correlation_id' => $correlationId,
                'total_processed' => $batchResult->getTotalProcessed(),
                'successful_repairs' => $batchResult->getSuccessfulRepairs(),
                'failed_repairs' => $batchResult->getFailedRepairs(),
            ]);
            
            return $batchResult;
            
        } catch (Exception $e) {
            Log::error('FileRepair: Error during batch repair operation', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
            ]);
            
            $batchResult->complete();
            return $batchResult;
        }
    }

    public function generateMissingFilesReport(array $options = [], ?string $correlationId = null): array
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('FileRepair: Generating missing files report', [
            'correlation_id' => $correlationId,
            'options' => $options,
        ]);

        $query = Content::whereNotNull('file_path');
        
        // Apply filters
        if (isset($options['content_types'])) {
            $query->whereIn('type', $options['content_types']);
        }
        
        if (isset($options['created_after'])) {
            $query->where('created_at', '>=', $options['created_after']);
        }
        
        $contents = $query->get();
        $missingFiles = [];
        $recoverableFiles = [];
        
        foreach ($contents as $content) {
            $diagnostic = $this->diagnosticService->diagnoseFileStorageIssues($content, $correlationId);
            
            if (!$diagnostic->fileExists()) {
                $missingFiles[] = [
                    'content_id' => $content->id,
                    'content_type' => $content->type,
                    'recorded_file_path' => $content->file_path,
                    'recorded_storage_disk' => $content->storage_disk,
                    'file_size' => $content->file_size,
                    'created_at' => $content->created_at->toISOString(),
                ];
            } elseif ($diagnostic->hasInconsistencies()) {
                $recoverableFiles[] = [
                    'content_id' => $content->id,
                    'content_type' => $content->type,
                    'recorded_file_path' => $content->file_path,
                    'actual_location' => $diagnostic->getActualLocation()?->toArray(),
                ];
            }
        }
        
        return [
            'correlation_id' => $correlationId,
            'generated_at' => now()->toISOString(),
            'summary' => [
                'total_checked' => $contents->count(),
                'missing_files' => count($missingFiles),
                'recoverable_files' => count($recoverableFiles),
                'unrecoverable_files' => count($missingFiles) - count($recoverableFiles),
            ],
            'missing_files' => $missingFiles,
            'recoverable_files' => $recoverableFiles,
        ];
    }
}