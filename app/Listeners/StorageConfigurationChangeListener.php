<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use App\Services\StorageConfigurationValidatorSimple as StorageConfigurationValidator;
use Illuminate\Support\Str;

/**
 * StorageConfigurationChangeListener - Handles storage configuration changes.
 * 
 * This listener is triggered when storage configuration changes occur and
 * validates that existing files remain accessible with the new configuration.
 * 
 * Requirements: 8.2, 8.4
 */
class StorageConfigurationChangeListener
{
    protected StorageConfigurationValidator $storageValidator;

    public function __construct(StorageConfigurationValidator $storageValidator)
    {
        $this->storageValidator = $storageValidator;
    }

    /**
     * Handle storage configuration change events.
     * 
     * @param array $oldConfig Previous configuration
     * @param array $newConfig New configuration
     * @param string|null $correlationId Correlation ID for tracking
     */
    public function handle(array $oldConfig, array $newConfig, ?string $correlationId = null): void
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('StorageConfigurationChangeListener: Configuration change detected', [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'old_config_hash' => md5(serialize($oldConfig)),
            'new_config_hash' => md5(serialize($newConfig)),
        ]);

        try {
            // Validate the configuration change
            $validationResult = $this->storageValidator->validateConfigurationChange(
                $oldConfig, 
                $newConfig, 
                $correlationId
            );

            // Log validation results
            if ($validationResult->isCritical()) {
                Log::critical('StorageConfigurationChangeListener: Critical issues detected with configuration change', [
                    'correlation_id' => $correlationId,
                    'overall_status' => $validationResult->getOverallStatus(),
                    'configuration_errors' => count($validationResult->getConfigurationErrors()),
                    'functional_disks' => count($validationResult->getFunctionalDisks()),
                ]);

                // Clear validation cache to force re-validation
                $this->storageValidator->clearValidationCache($correlationId);
                
                // In a production system, you might want to:
                // - Send alerts to administrators
                // - Temporarily disable file operations
                // - Rollback configuration changes if possible
                
            } else {
                Log::info('StorageConfigurationChangeListener: Configuration change validated successfully', [
                    'correlation_id' => $correlationId,
                    'overall_status' => $validationResult->getOverallStatus(),
                    'functional_disks' => count($validationResult->getFunctionalDisks()),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('StorageConfigurationChangeListener: Configuration change validation failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clear validation cache to force re-validation on next request
            $this->storageValidator->clearValidationCache($correlationId);
        }
    }

    /**
     * Validate configuration change and handle results.
     * 
     * This method can be called directly when configuration changes are detected
     * outside of the event system.
     * 
     * @param array $oldConfig
     * @param array $newConfig
     * @param string|null $correlationId
     * @return bool True if validation passed, false if critical issues detected
     */
    public function validateConfigurationChange(array $oldConfig, array $newConfig, ?string $correlationId = null): bool
    {
        $this->handle($oldConfig, $newConfig, $correlationId);
        
        // Check if file operations are still allowed after validation
        return $this->storageValidator->areFileOperationsAllowed($correlationId);
    }
}