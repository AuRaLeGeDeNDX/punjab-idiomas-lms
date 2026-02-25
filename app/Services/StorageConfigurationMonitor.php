<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Listeners\StorageConfigurationChangeListener;
use Illuminate\Support\Str;

/**
 * StorageConfigurationMonitor - Monitors and validates storage configuration changes.
 * 
 * This service provides methods to detect configuration changes and trigger
 * validation to ensure existing files remain accessible.
 * 
 * Requirements: 8.2, 8.4
 */
class StorageConfigurationMonitor
{
    protected StorageConfigurationChangeListener $changeListener;
    protected array $lastKnownConfig = [];
    protected string $configHash = '';

    public function __construct(StorageConfigurationChangeListener $changeListener)
    {
        $this->changeListener = $changeListener;
        $this->captureCurrentConfiguration();
    }

    /**
     * Check for configuration changes and validate if changes are detected.
     * 
     * @param string|null $correlationId Correlation ID for tracking
     * @return bool True if no changes or changes are valid, false if critical issues detected
     */
    public function checkForConfigurationChanges(?string $correlationId = null): bool
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        $currentConfig = $this->getCurrentStorageConfiguration();
        $currentHash = md5(serialize($currentConfig));
        
        // Check if configuration has changed
        if ($currentHash !== $this->configHash) {
            Log::info('StorageConfigurationMonitor: Configuration change detected', [
                'correlation_id' => $correlationId,
                'old_hash' => $this->configHash,
                'new_hash' => $currentHash,
                'timestamp' => now()->toISOString(),
            ]);

            // Validate the configuration change
            $validationResult = $this->changeListener->validateConfigurationChange(
                $this->lastKnownConfig,
                $currentConfig,
                $correlationId
            );

            // Update stored configuration
            $this->lastKnownConfig = $currentConfig;
            $this->configHash = $currentHash;

            return $validationResult;
        }

        return true; // No changes detected
    }

    /**
     * Manually trigger configuration change validation.
     * 
     * This method can be called when you know configuration has changed
     * and want to validate the changes immediately.
     * 
     * @param array|null $newConfig New configuration (if null, current config is used)
     * @param string|null $correlationId Correlation ID for tracking
     * @return bool True if validation passed, false if critical issues detected
     */
    public function validateConfigurationChange(?array $newConfig = null, ?string $correlationId = null): bool
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        $oldConfig = $this->lastKnownConfig;
        $newConfig = $newConfig ?? $this->getCurrentStorageConfiguration();
        
        Log::info('StorageConfigurationMonitor: Manual configuration validation triggered', [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
        ]);

        $validationResult = $this->changeListener->validateConfigurationChange(
            $oldConfig,
            $newConfig,
            $correlationId
        );

        // Update stored configuration
        $this->lastKnownConfig = $newConfig;
        $this->configHash = md5(serialize($newConfig));

        return $validationResult;
    }

    /**
     * Get the current storage configuration.
     * 
     * @return array
     */
    public function getCurrentStorageConfiguration(): array
    {
        return [
            'default' => Config::get('filesystems.default'),
            'disks' => Config::get('filesystems.disks', []),
            'links' => Config::get('filesystems.links', []),
        ];
    }

    /**
     * Get the last known configuration.
     * 
     * @return array
     */
    public function getLastKnownConfiguration(): array
    {
        return $this->lastKnownConfig;
    }

    /**
     * Get configuration change summary.
     * 
     * @return array
     */
    public function getConfigurationChangeSummary(): array
    {
        $currentConfig = $this->getCurrentStorageConfiguration();
        $currentHash = md5(serialize($currentConfig));
        
        return [
            'has_changes' => $currentHash !== $this->configHash,
            'last_known_hash' => $this->configHash,
            'current_hash' => $currentHash,
            'last_known_config' => $this->lastKnownConfig,
            'current_config' => $currentConfig,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Reset configuration monitoring.
     * 
     * This method captures the current configuration as the baseline
     * for future change detection.
     * 
     * @param string|null $correlationId Correlation ID for tracking
     */
    public function resetConfigurationBaseline(?string $correlationId = null): void
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        Log::info('StorageConfigurationMonitor: Resetting configuration baseline', [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
        ]);

        $this->captureCurrentConfiguration();
    }

    /**
     * Capture the current configuration as baseline.
     */
    private function captureCurrentConfiguration(): void
    {
        $this->lastKnownConfig = $this->getCurrentStorageConfiguration();
        $this->configHash = md5(serialize($this->lastKnownConfig));
    }
}