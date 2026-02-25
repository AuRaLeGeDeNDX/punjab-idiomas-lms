<?php

namespace App\Services;

use App\Services\ConfigurationStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * StorageConfigurationValidator service for validating storage configuration.
 * 
 * This service provides comprehensive validation of storage disk accessibility,
 * configuration change validation, and detailed error logging for validation failures.
 * It focuses on startup validation and preventing file operations when validation fails.
 * 
 * Requirements: 8.1, 8.2, 8.4
 */
class StorageConfigurationValidator
{
    private array $validationCache = [];
    private Carbon $lastValidation;
    private bool $validationEnabled = true;
    private array $requiredDisks = ['public', 'protected'];
    private array $configurationErrors = [];

    /**
     * Create a new StorageConfigurationValidator instance.
     */
    public function __construct()
    {
        $this->lastValidation = now()->subHours(24); // Force initial validation
    }

    /**
     * Perform startup validation for storage disk accessibility.
     * 
     * This method validates all configured storage disks during application startup
     * to ensure they are accessible and writable before allowing file operations.
     * 
     * @param string|null $correlationId Correlation ID for tracking
     * @return \App\Services\ConfigurationStatus Storage configuration status
     * @throws \RuntimeException When critical storage validation fails
     */
    public function performStartupValidation(?string $correlationId = null): \App\Services\ConfigurationStatus
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('StorageConfigurationValidator: Starting startup validation', [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'required_disks' => $this->requiredDisks,
        ]);

        $configStatus = new \App\Services\ConfigurationStatus($correlationId);
        
        try {
            // Validate each required storage disk
            foreach ($this->requiredDisks as $disk) {
                $diskStatus = $this->validateDiskAccessibility($disk, $correlationId);
                $configStatus->addDiskStatus($disk, $diskStatus);
                
                // Cache successful validations
                if ($diskStatus['accessible'] && $diskStatus['writable'] && $diskStatus['readable']) {
                    $this->validationCache[$disk] = [
                        'status' => 'healthy',
                        'validated_at' => now(),
                        'correlation_id' => $correlationId,
                    ];
                }
            }
            
            // Validate configuration consistency
            $this->validateConfigurationConsistency($configStatus, $correlationId);
            
            // Check for critical failures
            if ($configStatus->isCritical()) {
                $this->handleCriticalValidationFailure($configStatus, $correlationId);
            }
            
            $this->lastValidation = now();
            
            Log::info('StorageConfigurationValidator: Startup validation completed', [
                'correlation_id' => $correlationId,
                'overall_status' => $configStatus->getOverallStatus(),
                'functional_disks' => count($configStatus->getFunctionalDisks()),
                'failed_disks' => count($configStatus->getFailedDisks()),
                'validation_duration' => now()->diffInMilliseconds($this->lastValidation),
            ]);
            
        } catch (\Exception $e) {
            Log::error('StorageConfigurationValidator: Startup validation failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $configStatus->addConfigurationError('startup_validation_exception', [
                'severity' => 'critical',
                'message' => 'Startup validation failed with exception: ' . $e->getMessage(),
                'exception_class' => get_class($e),
            ]);
            
            throw new \RuntimeException(
                "Storage configuration startup validation failed: " . $e->getMessage(),
                0,
                $e
            );
        }
        
        return $configStatus;
    }

    /**
     * Validate configuration changes to ensure existing files remain accessible.
     * 
     * This method is called when storage configuration changes to verify that
     * existing files remain accessible and the new configuration is valid.
     * 
     * @param array $oldConfig Previous configuration
     * @param array $newConfig New configuration
     * @param string|null $correlationId Correlation ID for tracking
     * @return \App\Services\ConfigurationStatus Validation status
     */
    public function validateConfigurationChange(array $oldConfig, array $newConfig, ?string $correlationId = null): \App\Services\ConfigurationStatus
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('StorageConfigurationValidator: Starting configuration change validation', [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'old_config_hash' => md5(serialize($oldConfig)),
            'new_config_hash' => md5(serialize($newConfig)),
        ]);

        $configStatus = new \App\Services\ConfigurationStatus($correlationId);
        
        try {
            // Validate new configuration
            $this->validateNewConfiguration($newConfig, $configStatus, $correlationId);
            
            // Check backward compatibility
            $this->validateBackwardCompatibility($oldConfig, $newConfig, $configStatus, $correlationId);
            
            // Verify existing files remain accessible
            $this->verifyExistingFileAccessibility($oldConfig, $newConfig, $configStatus, $correlationId);
            
            // Update validation cache if successful
            if (!$configStatus->isCritical()) {
                $this->updateValidationCache($configStatus, $correlationId);
            }
            
            Log::info('StorageConfigurationValidator: Configuration change validation completed', [
                'correlation_id' => $correlationId,
                'overall_status' => $configStatus->getOverallStatus(),
                'backward_compatible' => !$configStatus->hasConfigurationErrors(),
                'validation_duration' => now()->diffInMilliseconds(Carbon::parse($configStatus->getTimestamp())),
            ]);
            
        } catch (\Exception $e) {
            Log::error('StorageConfigurationValidator: Configuration change validation failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $configStatus->addConfigurationError('configuration_change_exception', [
                'severity' => 'critical',
                'message' => 'Configuration change validation failed: ' . $e->getMessage(),
                'exception_class' => get_class($e),
            ]);
        }
        
        return $configStatus;
    }

    /**
     * Check if file operations should be allowed based on current validation status.
     * 
     * This method prevents file operations when storage validation has failed
     * to ensure data integrity and prevent corruption.
     * 
     * @param string|null $correlationId Correlation ID for tracking
     * @return bool True if file operations are allowed
     */
    public function areFileOperationsAllowed(?string $correlationId = null): bool
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        // Check if validation is disabled (for emergency situations)
        if (!$this->validationEnabled) {
            Log::warning('StorageConfigurationValidator: File operations allowed - validation disabled', [
                'correlation_id' => $correlationId,
                'reason' => 'validation_disabled',
            ]);
            return true;
        }
        
        // Check if recent validation exists and was successful
        $recentValidation = $this->getRecentValidationStatus();
        if (!$recentValidation) {
            // Auto-trigger validation if there is no recent validation
            try {
                $this->performStartupValidation($correlationId);
                $recentValidation = $this->getRecentValidationStatus();
            } catch (\Exception $e) {
                // If validation throws, let it fail below
                Log::error('StorageConfigurationValidator: Auto-validation failed', [
                    'correlation_id' => $correlationId,
                    'error' => $e->getMessage()
                ]);
            }
            
            if (!$recentValidation) {
                Log::warning('StorageConfigurationValidator: File operations blocked - no recent validation (even after auto-check)', [
                    'correlation_id' => $correlationId,
                    'last_validation' => $this->lastValidation->toISOString(),
                    'validation_age_hours' => $this->lastValidation->diffInHours(now()),
                ]);
                return false;
            }
        }
        
        // Check for critical configuration errors
        if (!empty($this->configurationErrors)) {
            $criticalErrors = array_filter($this->configurationErrors, function($error) {
                return ($error['severity'] ?? 'warning') === 'critical';
            });
            
            if (!empty($criticalErrors)) {
                Log::warning('StorageConfigurationValidator: File operations blocked - critical errors', [
                    'correlation_id' => $correlationId,
                    'critical_errors' => count($criticalErrors),
                    'error_types' => array_keys($criticalErrors),
                ]);
                return false;
            }
        }
        
        // Check if at least one disk is functional
        $functionalDisks = $this->getFunctionalDisksFromCache();
        if (empty($functionalDisks)) {
            Log::warning('StorageConfigurationValidator: File operations blocked - no functional disks', [
                'correlation_id' => $correlationId,
                'cached_disks' => array_keys($this->validationCache),
            ]);
            return false;
        }
        
        Log::debug('StorageConfigurationValidator: File operations allowed', [
            'correlation_id' => $correlationId,
            'functional_disks' => $functionalDisks,
            'last_validation' => $this->lastValidation->toISOString(),
        ]);
        
        return true;
    }

    /**
     * Get detailed validation status for monitoring and debugging.
     * 
     * @param string|null $correlationId Correlation ID for tracking
     * @return array Detailed validation status
     */
    public function getValidationStatus(?string $correlationId = null): array
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        return [
            'validation_info' => [
                'correlation_id' => $correlationId,
                'timestamp' => now()->toISOString(),
                'last_validation' => $this->lastValidation->toISOString(),
                'validation_age_minutes' => $this->lastValidation->diffInMinutes(now()),
                'validation_enabled' => $this->validationEnabled,
            ],
            'disk_cache' => $this->validationCache,
            'configuration_errors' => $this->configurationErrors,
            'file_operations_allowed' => $this->areFileOperationsAllowed($correlationId),
            'functional_disks' => $this->getFunctionalDisksFromCache(),
            'required_disks' => $this->requiredDisks,
            'recommendations' => $this->getValidationRecommendations(),
        ];
    }

    /**
     * Enable or disable validation (for emergency situations).
     * 
     * @param bool $enabled Whether validation should be enabled
     * @param string $reason Reason for enabling/disabling
     * @param string|null $correlationId Correlation ID for tracking
     */
    public function setValidationEnabled(bool $enabled, string $reason, ?string $correlationId = null): void
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        $this->validationEnabled = $enabled;
        
        Log::warning('StorageConfigurationValidator: Validation status changed', [
            'correlation_id' => $correlationId,
            'enabled' => $enabled,
            'reason' => $reason,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Clear validation cache to force re-validation.
     * 
     * @param string|null $correlationId Correlation ID for tracking
     */
    public function clearValidationCache(?string $correlationId = null): void
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('StorageConfigurationValidator: Clearing validation cache', [
            'correlation_id' => $correlationId,
            'cached_disks' => array_keys($this->validationCache),
        ]);
        
        $this->validationCache = [];
        $this->configurationErrors = [];
        $this->lastValidation = now()->subHours(24);
    }

    /**
     * Validate accessibility of a specific storage disk.
     * 
     * @param string $disk Storage disk name
     * @param string $correlationId Correlation ID for tracking
     * @return array Disk validation status
     */
    private function validateDiskAccessibility(string $disk, string $correlationId): array
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
            'validation_details' => [
                'validated_at' => now()->toISOString(),
                'correlation_id' => $correlationId,
            ],
        ];
        
        try {
            // Check if disk configuration exists
            $diskConfig = config("filesystems.disks.{$disk}");
            if (!$diskConfig) {
                $status['errors'][] = "Storage disk '{$disk}' is not configured in filesystems.disks";
                Log::error('StorageConfigurationValidator: Disk not configured', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                ]);
                return $status;
            }
            
            // Check if disk is accessible
            $storage = Storage::disk($disk);
            $status['accessible'] = true;
            
            // Get disk path
            $status['path'] = $storage->path('');
            
            // Validate disk path exists and is directory
            if (!is_dir($status['path'])) {
                $status['errors'][] = "Storage path '{$status['path']}' is not a valid directory";
                Log::error('StorageConfigurationValidator: Invalid storage path', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'path' => $status['path'],
                ]);
                return $status;
            }
            
            // Test write capability
            $testFile = 'storage_validation_' . time() . '_' . Str::random(8) . '.txt';
            $testContent = 'Storage validation test - ' . now()->toISOString();
            
            try {
                $storage->put($testFile, $testContent);
                $status['writable'] = true;
                
                // Test read capability
                $readContent = $storage->get($testFile);
                if ($readContent === $testContent) {
                    $status['readable'] = true;
                } else {
                    $status['errors'][] = 'Read test failed - content mismatch';
                }
                
                // Clean up test file
                $storage->delete($testFile);
                
                Log::debug('StorageConfigurationValidator: Disk read/write test successful', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'test_file' => $testFile,
                ]);
                
            } catch (\Exception $e) {
                $status['errors'][] = 'Write/read test failed: ' . $e->getMessage();
                Log::error('StorageConfigurationValidator: Disk read/write test failed', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'error' => $e->getMessage(),
                ]);
            }
            
            // Get disk space information
            try {
                $path = $status['path'];
                if ($path && is_dir($path)) {
                    $freeSpace = disk_free_space($path);
                    $totalSpace = disk_total_space($path);
                    
                    if ($freeSpace !== false && $totalSpace !== false) {
                        $status['free_space'] = $freeSpace;
                        $status['total_space'] = $totalSpace;
                        $status['usage_percentage'] = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
                        
                        // Check for low disk space
                        $freePercentage = ($freeSpace / $totalSpace) * 100;
                        if ($freePercentage < 5) {
                            $status['errors'][] = 'Critical: Less than 5% disk space remaining';
                        } elseif ($freePercentage < 15) {
                            $status['errors'][] = 'Warning: Less than 15% disk space remaining';
                        }
                    }
                }
            } catch (\Exception $e) {
                $status['errors'][] = 'Disk space check failed: ' . $e->getMessage();
                Log::warning('StorageConfigurationValidator: Disk space check failed', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'error' => $e->getMessage(),
                ]);
            }
            
        } catch (\Exception $e) {
            $status['errors'][] = 'Disk access failed: ' . $e->getMessage();
            Log::error('StorageConfigurationValidator: Disk access failed', [
                'correlation_id' => $correlationId,
                'disk' => $disk,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        return $status;
    }

    /**
     * Validate overall configuration consistency.
     * 
     * @param ConfigurationStatus $configStatus Configuration status to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function validateConfigurationConsistency(\App\Services\ConfigurationStatus $configStatus, string $correlationId): void
    {
        $diskStatuses = $configStatus->getDiskStatuses();
        
        // Check if at least one disk is fully functional
        $functionalDisks = array_filter($diskStatuses, function($status) {
            return $status['accessible'] && $status['writable'] && $status['readable'];
        });
        
        if (empty($functionalDisks)) {
            $configStatus->addConfigurationError('no_functional_disks', [
                'severity' => 'critical',
                'message' => 'No fully functional storage disks available',
                'impact' => 'File operations will fail',
                'required_action' => 'Fix storage disk configuration immediately',
            ]);
            
            Log::critical('StorageConfigurationValidator: No functional storage disks', [
                'correlation_id' => $correlationId,
                'total_disks' => count($diskStatuses),
                'accessible_disks' => count($configStatus->getAccessibleDisks()),
            ]);
        }
        
        // Check if required disks are available
        foreach ($this->requiredDisks as $requiredDisk) {
            if (!isset($diskStatuses[$requiredDisk])) {
                $configStatus->addConfigurationError("missing_required_disk_{$requiredDisk}", [
                    'severity' => 'critical',
                    'message' => "Required storage disk '{$requiredDisk}' is not configured",
                    'impact' => 'File operations for this disk will fail',
                    'required_action' => "Configure '{$requiredDisk}' storage disk",
                ]);
            } elseif (!$diskStatuses[$requiredDisk]['accessible']) {
                $configStatus->addConfigurationError("inaccessible_required_disk_{$requiredDisk}", [
                    'severity' => 'critical',
                    'message' => "Required storage disk '{$requiredDisk}' is not accessible",
                    'impact' => 'File operations for this disk will fail',
                    'required_action' => "Fix '{$requiredDisk}' storage disk accessibility",
                ]);
            }
        }
        
        // Check for disk space warnings
        foreach ($diskStatuses as $disk => $status) {
            if (isset($status['free_space']) && isset($status['total_space'])) {
                $freePercentage = ($status['free_space'] / $status['total_space']) * 100;
                
                if ($freePercentage < 5) {
                    $configStatus->addConfigurationError("critical_disk_space_{$disk}", [
                        'severity' => 'critical',
                        'message' => "Storage disk '{$disk}' has less than 5% free space",
                        'impact' => 'File uploads may fail',
                        'required_action' => 'Free up disk space immediately',
                        'free_percentage' => round($freePercentage, 2),
                    ]);
                } elseif ($freePercentage < 15) {
                    $configStatus->addConfigurationError("low_disk_space_{$disk}", [
                        'severity' => 'warning',
                        'message' => "Storage disk '{$disk}' has less than 15% free space",
                        'impact' => 'File uploads may fail soon',
                        'required_action' => 'Monitor and free up disk space',
                        'free_percentage' => round($freePercentage, 2),
                    ]);
                }
            }
        }
    }

    /**
     * Handle critical validation failures.
     * 
     * @param ConfigurationStatus $configStatus Configuration status
     * @param string $correlationId Correlation ID for tracking
     * @throws \RuntimeException When critical failures cannot be handled
     */
    private function handleCriticalValidationFailure(\App\Services\ConfigurationStatus $configStatus, string $correlationId): void
    {
        $criticalErrors = $configStatus->getConfigurationErrorsBySeverity('critical');
        
        Log::critical('StorageConfigurationValidator: Critical validation failure detected', [
            'correlation_id' => $correlationId,
            'critical_errors' => count($criticalErrors),
            'error_types' => array_keys($criticalErrors),
            'functional_disks' => count($configStatus->getFunctionalDisks()),
            'total_disks' => count($configStatus->getDiskStatuses()),
        ]);
        
        // Store critical errors for blocking file operations
        $this->configurationErrors = array_merge($this->configurationErrors, $criticalErrors);
        
        // If no functional disks, this is a critical failure
        if (empty($configStatus->getFunctionalDisks())) {
            throw new \RuntimeException(
                'Critical storage configuration failure: No functional storage disks available. ' .
                'File operations are disabled until storage configuration is fixed.'
            );
        }
    }

    /**
     * Validate new configuration.
     * 
     * @param array $newConfig New configuration
     * @param ConfigurationStatus $configStatus Configuration status to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function validateNewConfiguration(array $newConfig, \App\Services\ConfigurationStatus $configStatus, string $correlationId): void
    {
        // Validate each disk in new configuration
        foreach ($this->requiredDisks as $disk) {
            if (isset($newConfig['disks'][$disk])) {
                $diskStatus = $this->validateDiskAccessibility($disk, $correlationId);
                $configStatus->addDiskStatus($disk, $diskStatus);
            } else {
                $configStatus->addConfigurationError("missing_disk_config_{$disk}", [
                    'severity' => 'critical',
                    'message' => "Required disk '{$disk}' missing from new configuration",
                    'impact' => 'File operations will fail',
                    'required_action' => 'Add disk configuration',
                ]);
            }
        }
    }

    /**
     * Validate backward compatibility.
     * 
     * @param array $oldConfig Old configuration
     * @param array $newConfig New configuration
     * @param ConfigurationStatus $configStatus Configuration status to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function validateBackwardCompatibility(array $oldConfig, array $newConfig, \App\Services\ConfigurationStatus $configStatus, string $correlationId): void
    {
        // Check if disk paths have changed
        foreach ($this->requiredDisks as $disk) {
            $oldPath = $oldConfig['disks'][$disk]['root'] ?? null;
            $newPath = $newConfig['disks'][$disk]['root'] ?? null;
            
            if ($oldPath && $newPath && $oldPath !== $newPath) {
                $configStatus->addConfigurationError("disk_path_changed_{$disk}", [
                    'severity' => 'warning',
                    'message' => "Storage path changed for disk '{$disk}'",
                    'impact' => 'Existing files may become inaccessible',
                    'required_action' => 'Verify file accessibility and consider migration',
                    'old_path' => $oldPath,
                    'new_path' => $newPath,
                ]);
                
                Log::warning('StorageConfigurationValidator: Disk path changed', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'old_path' => $oldPath,
                    'new_path' => $newPath,
                ]);
            }
        }
    }

    /**
     * Verify existing files remain accessible after configuration change.
     * 
     * @param array $oldConfig Old configuration
     * @param array $newConfig New configuration
     * @param ConfigurationStatus $configStatus Configuration status to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function verifyExistingFileAccessibility(array $oldConfig, array $newConfig, \App\Services\ConfigurationStatus $configStatus, string $correlationId): void
    {
        // This is a simplified check - in a full implementation, you might want to
        // sample existing files and verify they're still accessible
        
        foreach ($this->requiredDisks as $disk) {
            try {
                $storage = Storage::disk($disk);
                
                // Try to list some files to verify accessibility
                $files = $storage->files('', false); // Get files in root, non-recursive
                
                Log::debug('StorageConfigurationValidator: File accessibility check', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'accessible_files' => count($files),
                ]);
                
            } catch (\Exception $e) {
                $configStatus->addConfigurationError("file_accessibility_check_failed_{$disk}", [
                    'severity' => 'warning',
                    'message' => "Could not verify file accessibility for disk '{$disk}'",
                    'impact' => 'Existing files may be inaccessible',
                    'required_action' => 'Manually verify file accessibility',
                    'error' => $e->getMessage(),
                ]);
                
                Log::warning('StorageConfigurationValidator: File accessibility check failed', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Update validation cache with successful results.
     * 
     * @param ConfigurationStatus $configStatus Configuration status
     * @param string $correlationId Correlation ID for tracking
     */
    private function updateValidationCache(\App\Services\ConfigurationStatus $configStatus, string $correlationId): void
    {
        foreach ($configStatus->getDiskStatuses() as $disk => $status) {
            if ($status['accessible'] && $status['writable'] && $status['readable']) {
                $this->validationCache[$disk] = [
                    'status' => 'healthy',
                    'validated_at' => now(),
                    'correlation_id' => $correlationId,
                    'disk_info' => [
                        'path' => $status['path'],
                        'free_space' => $status['free_space'],
                        'total_space' => $status['total_space'],
                    ],
                ];
            }
        }
    }

    /**
     * Get recent validation status.
     * 
     * @return bool True if recent validation exists and was successful
     */
    private function getRecentValidationStatus(): bool
    {
        // Consider validation recent if it's less than 1 hour old
        $maxAge = now()->subHour();
        
        return $this->lastValidation->isAfter($maxAge) && !empty($this->validationCache);
    }

    /**
     * Get functional disks from cache.
     * 
     * @return array List of functional disk names
     */
    private function getFunctionalDisksFromCache(): array
    {
        return array_keys(array_filter($this->validationCache, function($cache) {
            return $cache['status'] === 'healthy';
        }));
    }

    /**
     * Get validation recommendations.
     * 
     * @return array List of recommendations
     */
    private function getValidationRecommendations(): array
    {
        $recommendations = [];
        
        // Check validation age
        if ($this->lastValidation->diffInHours(now()) > 24) {
            $recommendations[] = [
                'type' => 'outdated_validation',
                'priority' => 'warning',
                'description' => 'Storage validation is more than 24 hours old',
                'action' => 'Run fresh storage validation',
            ];
        }
        
        // Check for missing cache
        if (empty($this->validationCache)) {
            $recommendations[] = [
                'type' => 'no_validation_cache',
                'priority' => 'warning',
                'description' => 'No validation cache available',
                'action' => 'Perform storage validation',
            ];
        }
        
        // Check for configuration errors
        if (!empty($this->configurationErrors)) {
            $recommendations[] = [
                'type' => 'configuration_errors',
                'priority' => 'critical',
                'description' => 'Configuration errors detected',
                'action' => 'Fix storage configuration errors',
                'error_count' => count($this->configurationErrors),
            ];
        }
        
        return $recommendations;
    }
}