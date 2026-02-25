<?php

namespace App\Services;

use App\Models\Content;
use App\Services\FileUploadLogger;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * FileStorageDiagnosticService provides comprehensive file storage diagnostics and repair capabilities.
 * 
 * This service diagnoses file storage inconsistencies, locates files across multiple storage disks,
 * and provides detailed logging for diagnostic operations with correlation IDs.
 * 
 * Requirements: 1.1, 1.2, 1.3, 1.4
 */
class FileStorageDiagnosticService
{
    protected FileUploadLogger $fileUploadLogger;

    public function __construct(FileUploadLogger $fileUploadLogger)
    {
        $this->fileUploadLogger = $fileUploadLogger;
    }

    /**
     * Diagnose file storage issues for a specific content record.
     * 
     * @param Content $content The content record to diagnose
     * @param string|null $correlationId Correlation ID for tracking
     * @return DiagnosticResult Comprehensive diagnostic result
     */
    public function diagnoseFileStorageIssues(Content $content, ?string $correlationId = null): DiagnosticResult
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('FileStorageDiagnostic: Starting file storage diagnosis', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'recorded_file_path' => $content->file_path,
            'recorded_storage_disk' => $content->storage_disk,
            'content_type' => $content->type,
        ]);

        $diagnosticResult = new DiagnosticResult($content, $correlationId);
        
        // 1. Check if file exists at recorded location
        $this->checkRecordedLocation($content, $diagnosticResult, $correlationId);
        
        // 2. Search for file in alternative storage locations
        $this->searchAlternativeLocations($content, $diagnosticResult, $correlationId);
        
        // 3. Validate URL generation
        $this->validateUrlGeneration($content, $diagnosticResult, $correlationId);
        
        // 4. Check file integrity if found
        $this->checkFileIntegrity($content, $diagnosticResult, $correlationId);
        
        // 5. Generate recommendations
        $this->generateRecommendations($diagnosticResult, $correlationId);
        
        // 6. Log comprehensive diagnostic results
        $this->logDiagnosticResults($diagnosticResult, $correlationId);
        
        Log::info('FileStorageDiagnostic: File storage diagnosis completed', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'file_exists' => $diagnosticResult->fileExists(),
            'has_inconsistencies' => $diagnosticResult->hasInconsistencies(),
            'actual_location' => $diagnosticResult->getActualLocation()?->toArray(),
        ]);
        
        return $diagnosticResult;
    }

    /**
     * Audit all content files for storage inconsistencies.
     * 
     * @param array $options Audit options (limit, offset, content_types, etc.)
     * @param string|null $correlationId Correlation ID for tracking
     * @return AuditReport Comprehensive audit report
     */
    public function auditAllContentFiles(array $options = [], ?string $correlationId = null): AuditReport
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('FileStorageDiagnostic: Starting comprehensive file audit', [
            'correlation_id' => $correlationId,
            'options' => $options,
        ]);

        $auditReport = new AuditReport($correlationId);
        
        // Build query with options
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
        
        // Apply pagination
        $limit = $options['limit'] ?? 100;
        $offset = $options['offset'] ?? 0;
        
        $totalRecords = $query->count();
        $contentRecords = $query->offset($offset)->limit($limit)->get();
        
        Log::info('FileStorageDiagnostic: Processing content records for audit', [
            'correlation_id' => $correlationId,
            'total_records' => $totalRecords,
            'processing_count' => $contentRecords->count(),
            'offset' => $offset,
            'limit' => $limit,
        ]);

        // Process each content record
        foreach ($contentRecords as $content) {
            $diagnosticResult = $this->diagnoseFileStorageIssues($content, $correlationId);
            $auditReport->addDiagnosticResult($diagnosticResult);
            
            // Add small delay to prevent overwhelming the system
            if ($contentRecords->count() > 50) {
                usleep(10000); // 10ms delay
            }
        }
        
        // Generate audit summary
        $auditReport->generateSummary();
        
        Log::info('FileStorageDiagnostic: File audit completed', [
            'correlation_id' => $correlationId,
            'total_processed' => $auditReport->getTotalProcessed(),
            'files_found' => $auditReport->getFilesFound(),
            'files_missing' => $auditReport->getFilesMissing(),
            'inconsistencies_found' => $auditReport->getInconsistenciesFound(),
        ]);
        
        return $auditReport;
    }

    /**
     * Find the actual location of a file across multiple storage disks.
     * 
     * @param Content $content The content record
     * @param string|null $correlationId Correlation ID for tracking
     * @return FileLocation|null The actual file location or null if not found
     */
    public function findActualFileLocation(Content $content, ?string $correlationId = null): ?FileLocation
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        if (!$content->file_path) {
            Log::debug('FileStorageDiagnostic: No file path recorded for content', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
            ]);
            return null;
        }

        Log::debug('FileStorageDiagnostic: Searching for file across storage disks', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'file_path' => $content->file_path,
            'recorded_disk' => $content->storage_disk,
        ]);

        // Define storage disks to check in priority order
        $disksToCheck = $this->getStorageDisksToCheck($content);
        
        foreach ($disksToCheck as $disk) {
            try {
                if (Storage::disk($disk)->exists($content->file_path)) {
                    $fileLocation = new FileLocation($disk, $content->file_path);
                    
                    Log::info('FileStorageDiagnostic: File found on storage disk', [
                        'correlation_id' => $correlationId,
                        'content_id' => $content->id,
                        'file_path' => $content->file_path,
                        'found_on_disk' => $disk,
                        'recorded_disk' => $content->storage_disk,
                        'is_inconsistent' => $disk !== $content->storage_disk,
                    ]);
                    
                    return $fileLocation;
                }
            } catch (\Exception $e) {
                Log::warning('FileStorageDiagnostic: Error checking storage disk', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'disk' => $disk,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::warning('FileStorageDiagnostic: File not found on any storage disk', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'file_path' => $content->file_path,
            'disks_checked' => $disksToCheck,
        ]);
        
        return null;
    }

    /**
     * Validate storage configuration and accessibility.
     * 
     * @param string|null $correlationId Correlation ID for tracking
     * @return ConfigurationStatus Storage configuration status
     */
    public function validateStorageConfiguration(?string $correlationId = null): ConfigurationStatus
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('FileStorageDiagnostic: Starting storage configuration validation', [
            'correlation_id' => $correlationId,
        ]);

        $configStatus = new ConfigurationStatus($correlationId);
        
        // Get configured storage disks
        $disks = ['public', 'protected'];
        
        foreach ($disks as $disk) {
            $diskStatus = $this->validateStorageDisk($disk, $correlationId);
            $configStatus->addDiskStatus($disk, $diskStatus);
        }
        
        // Check overall configuration consistency
        $this->checkConfigurationConsistency($configStatus, $correlationId);
        
        Log::info('FileStorageDiagnostic: Storage configuration validation completed', [
            'correlation_id' => $correlationId,
            'overall_status' => $configStatus->getOverallStatus(),
            'accessible_disks' => $configStatus->getAccessibleDisks(),
            'failed_disks' => $configStatus->getFailedDisks(),
        ]);
        
        return $configStatus;
    }

    /**
     * Check if file exists at the recorded location.
     * 
     * @param Content $content The content record
     * @param DiagnosticResult $diagnosticResult The diagnostic result to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function checkRecordedLocation(Content $content, DiagnosticResult $diagnosticResult, string $correlationId): void
    {
        $recordedDisk = $content->storage_disk ?? 'public';
        
        try {
            $exists = Storage::disk($recordedDisk)->exists($content->file_path);
            
            if ($exists) {
                $fileLocation = new FileLocation($recordedDisk, $content->file_path);
                $diagnosticResult->setActualLocation($fileLocation);
                $diagnosticResult->setFileExists(true);
                
                Log::debug('FileStorageDiagnostic: File found at recorded location', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'storage_disk' => $recordedDisk,
                    'file_path' => $content->file_path,
                ]);
            } else {
                $diagnosticResult->addInconsistency('file_not_at_recorded_location', [
                    'recorded_disk' => $recordedDisk,
                    'file_path' => $content->file_path,
                    'message' => 'File does not exist at the recorded storage location',
                ]);
                
                Log::warning('FileStorageDiagnostic: File not found at recorded location', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'recorded_disk' => $recordedDisk,
                    'file_path' => $content->file_path,
                ]);
            }
        } catch (\Exception $e) {
            $diagnosticResult->addInconsistency('storage_disk_error', [
                'recorded_disk' => $recordedDisk,
                'error' => $e->getMessage(),
                'message' => 'Error accessing recorded storage disk',
            ]);
            
            Log::error('FileStorageDiagnostic: Error checking recorded location', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'recorded_disk' => $recordedDisk,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Search for file in alternative storage locations.
     * 
     * @param Content $content The content record
     * @param DiagnosticResult $diagnosticResult The diagnostic result to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function searchAlternativeLocations(Content $content, DiagnosticResult $diagnosticResult, string $correlationId): void
    {
        // Skip if file already found at recorded location
        if ($diagnosticResult->fileExists()) {
            return;
        }
        
        $recordedDisk = $content->storage_disk ?? 'public';
        $alternativeDisks = $this->getAlternativeStorageDisks($recordedDisk);
        
        foreach ($alternativeDisks as $disk) {
            try {
                if (Storage::disk($disk)->exists($content->file_path)) {
                    $fileLocation = new FileLocation($disk, $content->file_path);
                    $diagnosticResult->setActualLocation($fileLocation);
                    $diagnosticResult->setFileExists(true);
                    
                    $diagnosticResult->addInconsistency('storage_disk_mismatch', [
                        'recorded_disk' => $recordedDisk,
                        'actual_disk' => $disk,
                        'file_path' => $content->file_path,
                        'message' => 'File found on different storage disk than recorded',
                    ]);
                    
                    Log::warning('FileStorageDiagnostic: File found on alternative storage disk', [
                        'correlation_id' => $correlationId,
                        'content_id' => $content->id,
                        'recorded_disk' => $recordedDisk,
                        'actual_disk' => $disk,
                        'file_path' => $content->file_path,
                    ]);
                    
                    return; // Found the file, stop searching
                }
            } catch (\Exception $e) {
                Log::warning('FileStorageDiagnostic: Error checking alternative storage disk', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'disk' => $disk,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // File not found anywhere
        $diagnosticResult->setFileExists(false);
        $diagnosticResult->addInconsistency('file_not_found', [
            'recorded_disk' => $recordedDisk,
            'file_path' => $content->file_path,
            'disks_checked' => array_merge([$recordedDisk], $alternativeDisks),
            'message' => 'File not found on any storage disk',
        ]);
    }

    /**
     * Validate URL generation for the content.
     * 
     * @param Content $content The content record
     * @param DiagnosticResult $diagnosticResult The diagnostic result to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function validateUrlGeneration(Content $content, DiagnosticResult $diagnosticResult, string $correlationId): void
    {
        try {
            $url = $content->getSignedUrl();
            
            if ($url) {
                $diagnosticResult->setGeneratedUrl($url);
                
                Log::debug('FileStorageDiagnostic: URL generated successfully', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'url_type' => $this->determineUrlType($url),
                ]);
            } else {
                $diagnosticResult->setUrlGenerationError('URL generation returned null');
                
                Log::warning('FileStorageDiagnostic: URL generation returned null', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'file_path' => $content->file_path,
                    'storage_disk' => $content->storage_disk,
                ]);
            }
        } catch (\Exception $e) {
            $diagnosticResult->setUrlGenerationError($e->getMessage());
            
            Log::error('FileStorageDiagnostic: URL generation failed', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check file integrity if the file exists.
     * 
     * @param Content $content The content record
     * @param DiagnosticResult $diagnosticResult The diagnostic result to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function checkFileIntegrity(Content $content, DiagnosticResult $diagnosticResult, string $correlationId): void
    {
        $actualLocation = $diagnosticResult->getActualLocation();
        
        if (!$actualLocation || !$diagnosticResult->fileExists()) {
            return;
        }
        
        try {
            $disk = $actualLocation->getDisk();
            $path = $actualLocation->getPath();
            
            // Check file size
            $actualSize = Storage::disk($disk)->size($path);
            $recordedSize = $content->file_size;
            
            if ($recordedSize && $actualSize !== $recordedSize) {
                $diagnosticResult->addInconsistency('file_size_mismatch', [
                    'recorded_size' => $recordedSize,
                    'actual_size' => $actualSize,
                    'message' => 'File size does not match recorded size',
                ]);
                
                Log::warning('FileStorageDiagnostic: File size mismatch detected', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'recorded_size' => $recordedSize,
                    'actual_size' => $actualSize,
                ]);
            }
            
            // Check file hash if available and file is not too large
            if ($content->file_hash && $actualSize < 50 * 1024 * 1024) { // Only for files < 50MB
                $actualHash = hash_file('sha256', Storage::disk($disk)->path($path));
                
                if ($actualHash !== $content->file_hash) {
                    $diagnosticResult->addInconsistency('file_hash_mismatch', [
                        'recorded_hash' => $content->file_hash,
                        'actual_hash' => $actualHash,
                        'message' => 'File content hash does not match recorded hash',
                    ]);
                    
                    Log::warning('FileStorageDiagnostic: File hash mismatch detected', [
                        'correlation_id' => $correlationId,
                        'content_id' => $content->id,
                        'file_path' => $path,
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::warning('FileStorageDiagnostic: Error checking file integrity', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate recommendations based on diagnostic results.
     * 
     * @param DiagnosticResult $diagnosticResult The diagnostic result to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function generateRecommendations(DiagnosticResult $diagnosticResult, string $correlationId): void
    {
        $recommendations = [];
        
        if (!$diagnosticResult->fileExists()) {
            $recommendations[] = [
                'type' => 'file_missing',
                'priority' => 'high',
                'action' => 'investigate_file_deletion',
                'description' => 'File not found - investigate if file was deleted or moved',
                'automated_fix_available' => false,
            ];
        }
        
        foreach ($diagnosticResult->getInconsistencies() as $type => $inconsistency) {
            switch ($type) {
                case 'storage_disk_mismatch':
                    $recommendations[] = [
                        'type' => 'update_database_record',
                        'priority' => 'medium',
                        'action' => 'update_storage_disk_field',
                        'description' => 'Update storage_disk field to match actual file location',
                        'automated_fix_available' => true,
                        'fix_data' => [
                            'new_storage_disk' => $inconsistency['actual_disk'],
                        ],
                    ];
                    break;
                    
                case 'file_size_mismatch':
                    $recommendations[] = [
                        'type' => 'update_file_metadata',
                        'priority' => 'low',
                        'action' => 'update_file_size',
                        'description' => 'Update file_size field to match actual file size',
                        'automated_fix_available' => true,
                        'fix_data' => [
                            'new_file_size' => $inconsistency['actual_size'],
                        ],
                    ];
                    break;
                    
                case 'file_hash_mismatch':
                    $recommendations[] = [
                        'type' => 'investigate_file_corruption',
                        'priority' => 'high',
                        'action' => 'verify_file_integrity',
                        'description' => 'File content has changed - investigate potential corruption or unauthorized modification',
                        'automated_fix_available' => false,
                    ];
                    break;
            }
        }
        
        $diagnosticResult->setRecommendations($recommendations);
        
        Log::debug('FileStorageDiagnostic: Generated recommendations', [
            'correlation_id' => $correlationId,
            'content_id' => $diagnosticResult->getContent()->id,
            'recommendation_count' => count($recommendations),
            'high_priority_count' => count(array_filter($recommendations, fn($r) => $r['priority'] === 'high')),
        ]);
    }

    /**
     * Log comprehensive diagnostic results.
     * 
     * @param DiagnosticResult $diagnosticResult The diagnostic result
     * @param string $correlationId Correlation ID for tracking
     */
    private function logDiagnosticResults(DiagnosticResult $diagnosticResult, string $correlationId): void
    {
        $content = $diagnosticResult->getContent();
        
        $logData = [
            'correlation_id' => $correlationId,
            'event_type' => 'file_storage_diagnosis',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'content_details' => [
                'content_id' => $content->id,
                'content_type' => $content->type,
                'recorded_file_path' => $content->file_path,
                'recorded_storage_disk' => $content->storage_disk,
                'recorded_file_size' => $content->file_size,
                'recorded_file_hash' => $content->file_hash ? substr($content->file_hash, 0, 16) . '...' : null,
            ],
            'diagnostic_results' => [
                'file_exists' => $diagnosticResult->fileExists(),
                'actual_location' => $diagnosticResult->getActualLocation()?->toArray(),
                'inconsistencies_count' => count($diagnosticResult->getInconsistencies()),
                'inconsistencies' => $diagnosticResult->getInconsistencies(),
                'url_generated' => $diagnosticResult->getGeneratedUrl() !== null,
                'url_generation_error' => $diagnosticResult->getUrlGenerationError(),
                'recommendations_count' => count($diagnosticResult->getRecommendations()),
                'high_priority_recommendations' => count(array_filter(
                    $diagnosticResult->getRecommendations(),
                    fn($r) => $r['priority'] === 'high'
                )),
            ],
            'server_info' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_uri' => request()->getRequestUri() ?? 'cli',
            ],
        ];
        
        // Use appropriate log level based on findings
        if (!$diagnosticResult->fileExists()) {
            Log::error('FILE_STORAGE_DIAGNOSIS: File not found', $logData);
        } elseif ($diagnosticResult->hasInconsistencies()) {
            Log::warning('FILE_STORAGE_DIAGNOSIS: Inconsistencies detected', $logData);
        } else {
            Log::info('FILE_STORAGE_DIAGNOSIS: File storage healthy', $logData);
        }
        
        // Also log through FileUploadLogger for consistency
        $this->fileUploadLogger->logUploadAttempt(
            request(),
            null,
            [
                'diagnostic_operation' => true,
                'diagnostic_results' => $logData['diagnostic_results'],
            ]
        );
    }

    /**
     * Get storage disks to check in priority order.
     * 
     * @param Content $content The content record
     * @return array Storage disks to check
     */
    private function getStorageDisksToCheck(Content $content): array
    {
        $recordedDisk = $content->storage_disk ?? 'public';
        $allDisks = ['public', 'protected'];
        
        // Put recorded disk first, then others
        $disksToCheck = [$recordedDisk];
        foreach ($allDisks as $disk) {
            if ($disk !== $recordedDisk) {
                $disksToCheck[] = $disk;
            }
        }
        
        return $disksToCheck;
    }

    /**
     * Get alternative storage disks to check.
     * 
     * @param string $recordedDisk The recorded storage disk
     * @return array Alternative storage disks
     */
    private function getAlternativeStorageDisks(string $recordedDisk): array
    {
        $allDisks = ['public', 'protected'];
        
        return array_filter($allDisks, fn($disk) => $disk !== $recordedDisk);
    }

    /**
     * Determine the type of URL generated.
     * 
     * @param string $url The generated URL
     * @return string URL type
     */
    private function determineUrlType(string $url): string
    {
        if (str_contains($url, '/secure-files/')) {
            return 'secure_route';
        } elseif (str_contains($url, '/storage/')) {
            return 'asset_url';
        } else {
            return 'unknown';
        }
    }

    /**
     * Validate a specific storage disk.
     * 
     * @param string $disk Storage disk name
     * @param string $correlationId Correlation ID for tracking
     * @return array Disk status information
     */
    private function validateStorageDisk(string $disk, string $correlationId): array
    {
        $status = [
            'disk' => $disk,
            'accessible' => false,
            'writable' => false,
            'readable' => false,
            'path' => null,
            'free_space' => null,
            'total_space' => null,
            'errors' => [],
        ];
        
        try {
            // Check if disk is accessible
            $storage = Storage::disk($disk);
            $status['accessible'] = true;
            
            // Get disk path
            $status['path'] = $storage->path('');
            
            // Check if writable
            $testFile = 'diagnostic_test_' . time() . '.txt';
            $testContent = 'diagnostic test';
            
            try {
                $storage->put($testFile, $testContent);
                $status['writable'] = true;
                
                // Check if readable
                $readContent = $storage->get($testFile);
                if ($readContent === $testContent) {
                    $status['readable'] = true;
                }
                
                // Clean up test file
                $storage->delete($testFile);
                
            } catch (\Exception $e) {
                $status['errors'][] = 'Write/read test failed: ' . $e->getMessage();
            }
            
            // Get disk space information
            try {
                $path = $status['path'];
                if ($path && is_dir($path)) {
                    $status['free_space'] = disk_free_space($path);
                    $status['total_space'] = disk_total_space($path);
                }
            } catch (\Exception $e) {
                $status['errors'][] = 'Disk space check failed: ' . $e->getMessage();
            }
            
        } catch (\Exception $e) {
            $status['errors'][] = 'Disk access failed: ' . $e->getMessage();
        }
        
        Log::debug('FileStorageDiagnostic: Storage disk validation completed', [
            'correlation_id' => $correlationId,
            'disk' => $disk,
            'status' => $status,
        ]);
        
        return $status;
    }

    /**
     * Check overall configuration consistency.
     * 
     * @param ConfigurationStatus $configStatus Configuration status to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function checkConfigurationConsistency(ConfigurationStatus $configStatus, string $correlationId): void
    {
        $diskStatuses = $configStatus->getDiskStatuses();
        
        // Check if at least one disk is fully functional
        $functionalDisks = array_filter($diskStatuses, function($status) {
            return $status['accessible'] && $status['writable'] && $status['readable'];
        });
        
        if (empty($functionalDisks)) {
            $configStatus->addConfigurationError('no_functional_disks', [
                'message' => 'No storage disks are fully functional',
                'severity' => 'critical',
            ]);
        }
        
        // Check disk space warnings
        foreach ($diskStatuses as $disk => $status) {
            if ($status['free_space'] && $status['total_space']) {
                $freePercentage = ($status['free_space'] / $status['total_space']) * 100;
                
                if ($freePercentage < 5) {
                    $configStatus->addConfigurationError('low_disk_space_critical', [
                        'disk' => $disk,
                        'free_percentage' => round($freePercentage, 2),
                        'message' => "Critical: Less than 5% free space on {$disk} disk",
                        'severity' => 'critical',
                    ]);
                } elseif ($freePercentage < 15) {
                    $configStatus->addConfigurationError('low_disk_space_warning', [
                        'disk' => $disk,
                        'free_percentage' => round($freePercentage, 2),
                        'message' => "Warning: Less than 15% free space on {$disk} disk",
                        'severity' => 'warning',
                    ]);
                }
            }
        }
        
        Log::debug('FileStorageDiagnostic: Configuration consistency check completed', [
            'correlation_id' => $correlationId,
            'functional_disks' => array_keys($functionalDisks),
            'configuration_errors' => count($configStatus->getConfigurationErrors()),
        ]);
    }
}