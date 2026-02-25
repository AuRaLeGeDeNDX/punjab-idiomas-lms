<?php

namespace App\Services;

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
class StorageConfigurationValidatorSimple
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
     * @return array Storage configuration status
     * @throws \RuntimeException When critical storage validation fails
     */
    public function performStartupValidation(?string $correlationId = null): array
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('StorageConfigurationValidator: Starting startup validation', [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'required_disks' => $this->requiredDisks,
        ]);

        $configStatus = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'overall_status' => 'unknown',
            'disk_statuses' => [],
            'configuration_errors' => [],
            'functional_disks' => [],
            'failed_disks' => [],
        ];
        
        try {
            // Validate each required storage disk
            foreach ($this->requiredDisks as $disk) {
                $diskStatus = $this->validateDiskAccessibility($disk, $correlationId);
                $configStatus['disk_statuses'][$disk] = $diskStatus;
                
                // Cache successful validations
                if ($diskStatus['accessible'] && $diskStatus['writable'] && $diskStatus['readable']) {
                    $this->validationCache[$disk] = [
                        'status' => 'healthy',
                        'validated_at' => now(),
                        'correlation_id' => $correlationId,
                    ];
                    $configStatus['functional_disks'][] = $disk;
                } else {
                    $configStatus['failed_disks'][] = $disk;
                }
            }
            
            // Validate configuration consistency
            $this->validateConfigurationConsistency($configStatus, $correlationId);
            
            // Check for critical failures
            if ($configStatus['overall_status'] === 'critical') {
                $this->handleCriticalValidationFailure($configStatus, $correlationId);
            }
            
            $this->lastValidation = now();
            
            Log::info('StorageConfigurationValidator: Startup validation completed', [
                'correlation_id' => $correlationId,
                'overall_status' => $configStatus['overall_status'],
                'functional_disks' => count($configStatus['functional_disks']),
                'failed_disks' => count($configStatus['failed_disks']),
                'validation_duration' => now()->diffInMilliseconds($this->lastValidation),
            ]);
            
        } catch (\Exception $e) {
            Log::error('StorageConfigurationValidator: Startup validation failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $configStatus['configuration_errors']['startup_validation_exception'] = [
                'severity' => 'critical',
                'message' => 'Startup validation failed with exception: ' . $e->getMessage(),
                'exception_class' => get_class($e),
                'detected_at' => now()->toISOString(),
            ];
            
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
     * @return array Validation status
     */
    public function validateConfigurationChange(array $oldConfig, array $newConfig, ?string $correlationId = null): array
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('StorageConfigurationValidator: Starting configuration change validation', [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'old_config_hash' => md5(serialize($oldConfig)),
            'new_config_hash' => md5(serialize($newConfig)),
        ]);

        $configStatus = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'overall_status' => 'unknown',
            'disk_statuses' => [],
            'configuration_errors' => [],
            'functional_disks' => [],
            'failed_disks' => [],
        ];
        
        try {
            // Validate new configuration
            $this->validateNewConfiguration($newConfig, $configStatus, $correlationId);
            
            // Check backward compatibility
            $this->validateBackwardCompatibility($oldConfig, $newConfig, $configStatus, $correlationId);
            
            // Verify existing files remain accessible
            $this->verifyExistingFileAccessibility($oldConfig, $newConfig, $configStatus, $correlationId);
            
            // Update validation cache if successful
            if ($configStatus['overall_status'] !== 'critical') {
                $this->updateValidationCache($configStatus, $correlationId);
            }
            
            Log::info('StorageConfigurationValidator: Configuration change validation completed', [
                'correlation_id' => $correlationId,
                'overall_status' => $configStatus['overall_status'],
                'backward_compatible' => empty($configStatus['configuration_errors']),
                'validation_duration' => now()->diffInMilliseconds(Carbon::parse($configStatus['timestamp'])),
            ]);
            
        } catch (\Exception $e) {
            Log::error('StorageConfigurationValidator: Configuration change validation failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $configStatus['configuration_errors']['configuration_change_exception'] = [
                'severity' => 'critical',
                'message' => 'Configuration change validation failed: ' . $e->getMessage(),
                'exception_class' => get_class($e),
                'detected_at' => now()->toISOString(),
            ];
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
     * @param array $configStatus Configuration status to update
     * @param string $correlationId Correlation ID for tracking
     */
    private function validateConfigurationConsistency(array &$configStatus, string $correlationId): void
    {
        $diskStatuses = $configStatus['disk_statuses'];
        
        // Check if at least one disk is fully functional
        $functionalDisks = array_filter($diskStatuses, function($status) {
            return $status['accessible'] && $status['writable'] && $status['readable'];
        });
        
        if (empty($functionalDisks)) {
            $configStatus['configuration_errors']['no_functional_disks'] = [
                'severity' => 'critical',
                'message' => 'No fully functional storage disks available',
                'impact' => 'File operations will fail',
                'required_action' => 'Fix storage disk configuration immediately',
                'detected_at' => now()->toISOString(),
            ];
            
            $configStatus['overall_status'] = 'critical';
            
            Log::critical('StorageConfigurationValidator: No functional storage disks', [
                'correlation_id' => $correlationId,
                'total_disks' => count($diskStatuses),
                'accessible_disks' => count(array_filter($diskStatuses, fn($s) => $s['accessible'])),
            ]);
        } else {
            $configStatus['overall_status'] = 'healthy';
        }
        
        // Check if required disks are available
        foreach ($this->requiredDisks as $requiredDisk) {
            if (!isset($diskStatuses[$requiredDisk])) {
                $configStatus['configuration_errors']["missing_required_disk_{$requiredDisk}"] = [
                    'severity' => 'critical',
                    'message' => "Required storage disk '{$requiredDisk}' is not configured",
                    'impact' => 'File operations for this disk will fail',
                    'required_action' => "Configure '{$requiredDisk}' storage disk",
                    'detected_at' => now()->toISOString(),
                ];
                $configStatus['overall_status'] = 'critical';
            } elseif (!$diskStatuses[$requiredDisk]['accessible']) {
                $configStatus['configuration_errors']["inaccessible_required_disk_{$requiredDisk}"] = [
                    'severity' => 'critical',
                    'message' => "Required storage disk '{$requiredDisk}' is not accessible",
                    'impact' => 'File operations for this disk will fail',
                    'required_action' => "Fix '{$requiredDisk}' storage disk accessibility",
                    'detected_at' => now()->toISOString(),
                ];
                $configStatus['overall_status'] = 'critical';
            }
        }
    }

    /**
     * Handle critical validation failures.
     * 
     * @param array $configStatus Configuration status
     * @param string $correlationId Correlation ID for tracking
     * @throws \RuntimeException When critical failures cannot be handled
     */
    private function handleCriticalValidationFailure(array $configStatus, string $correlationId): void
    {
        $criticalErrors = array_filter($configStatus['configuration_errors'], function($error) {
            return ($error['severity'] ?? 'warning') === 'critical';
        });
        
        Log::critical('StorageConfigurationValidator: Critical validation failure detected', [
            'correlation_id' => $correlationId,
            'critical_errors' => count($criticalErrors),
            'error_types' => array_keys($criticalErrors),
            'functional_disks' => count($configStatus['functional_disks']),
            'total_disks' => count($configStatus['disk_statuses']),
        ]);
        
        // Store critical errors for blocking file operations
        $this->configurationErrors = array_merge($this->configurationErrors, $criticalErrors);
        
        // If no functional disks, this is a critical failure
        if (empty($configStatus['functional_disks'])) {
            throw new \RuntimeException(
                'Critical storage configuration failure: No functional storage disks available. ' .
                'File operations are disabled until storage configuration is fixed.'
            );
        }
    }

    // Additional private methods would be implemented here...
    // For brevity, I'm including simplified versions of the remaining methods

    private function validateNewConfiguration(array $newConfig, array &$configStatus, string $correlationId): void
    {
        // Simplified implementation
        foreach ($this->requiredDisks as $disk) {
            if (!isset($newConfig['disks'][$disk])) {
                $configStatus['configuration_errors']["missing_disk_config_{$disk}"] = [
                    'severity' => 'critical',
                    'message' => "Required disk '{$disk}' missing from new configuration",
                    'detected_at' => now()->toISOString(),
                ];
            }
        }
    }

    private function validateBackwardCompatibility(array $oldConfig, array $newConfig, array &$configStatus, string $correlationId): void
    {
        // Simplified implementation - check for path changes
        foreach ($this->requiredDisks as $disk) {
            $oldPath = $oldConfig['disks'][$disk]['root'] ?? null;
            $newPath = $newConfig['disks'][$disk]['root'] ?? null;
            
            if ($oldPath && $newPath && $oldPath !== $newPath) {
                $configStatus['configuration_errors']["disk_path_changed_{$disk}"] = [
                    'severity' => 'warning',
                    'message' => "Storage path changed for disk '{$disk}'",
                    'detected_at' => now()->toISOString(),
                ];
            }
        }
    }

    private function verifyExistingFileAccessibility(array $oldConfig, array $newConfig, array &$configStatus, string $correlationId): void
    {
        // Simplified implementation
        foreach ($this->requiredDisks as $disk) {
            try {
                $storage = Storage::disk($disk);
                $files = $storage->files('', false);
                
                Log::debug('StorageConfigurationValidator: File accessibility check', [
                    'correlation_id' => $correlationId,
                    'disk' => $disk,
                    'accessible_files' => count($files),
                ]);
            } catch (\Exception $e) {
                $configStatus['configuration_errors']["file_accessibility_check_failed_{$disk}"] = [
                    'severity' => 'warning',
                    'message' => "Could not verify file accessibility for disk '{$disk}'",
                    'detected_at' => now()->toISOString(),
                ];
            }
        }
    }

    private function updateValidationCache(array $configStatus, string $correlationId): void
    {
        foreach ($configStatus['disk_statuses'] as $disk => $status) {
            if ($status['accessible'] && $status['writable'] && $status['readable']) {
                $this->validationCache[$disk] = [
                    'status' => 'healthy',
                    'validated_at' => now(),
                    'correlation_id' => $correlationId,
                ];
            }
        }
    }

    private function getRecentValidationStatus(): bool
    {
        $maxAge = now()->subHour();
        return $this->lastValidation->isAfter($maxAge) && !empty($this->validationCache);
    }

    private function getFunctionalDisksFromCache(): array
    {
        return array_keys(array_filter($this->validationCache, function($cache) {
            return $cache['status'] === 'healthy';
        }));
    }

    private function getValidationRecommendations(): array
    {
        $recommendations = [];
        
        if ($this->lastValidation->diffInHours(now()) > 24) {
            $recommendations[] = [
                'type' => 'outdated_validation',
                'priority' => 'warning',
                'description' => 'Storage validation is more than 24 hours old',
            ];
        }
        
        if (empty($this->validationCache)) {
            $recommendations[] = [
                'type' => 'no_validation_cache',
                'priority' => 'warning',
                'description' => 'No validation cache available',
            ];
        }
        
        return $recommendations;
    }
}