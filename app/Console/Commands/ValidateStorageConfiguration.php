<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StorageConfigurationValidatorSimple as StorageConfigurationValidator;
use App\Services\StorageConfigurationMonitor;
use Illuminate\Support\Str;

/**
 * ValidateStorageConfiguration command - Manually validate storage configuration.
 * 
 * This command allows administrators to manually trigger storage configuration
 * validation and check for configuration changes.
 * 
 * Requirements: 8.1, 8.2, 8.4
 */
class ValidateStorageConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:validate 
                            {--force : Force validation even if recent validation exists}
                            {--check-changes : Check for configuration changes}
                            {--clear-cache : Clear validation cache before validation}
                            {--detailed : Show detailed validation information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate storage configuration and check for issues';

    protected StorageConfigurationValidator $storageValidator;
    protected StorageConfigurationMonitor $configMonitor;

    public function __construct(
        StorageConfigurationValidator $storageValidator,
        StorageConfigurationMonitor $configMonitor
    ) {
        parent::__construct();
        $this->storageValidator = $storageValidator;
        $this->configMonitor = $configMonitor;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $correlationId = Str::uuid()->toString();
        
        $this->info("Storage Configuration Validation");
        $this->info("Correlation ID: {$correlationId}");
        $this->newLine();

        try {
            // Clear cache if requested
            if ($this->option('clear-cache')) {
                $this->info('Clearing validation cache...');
                $this->storageValidator->clearValidationCache($correlationId);
                $this->info('✓ Validation cache cleared');
                $this->newLine();
            }

            // Check for configuration changes if requested
            if ($this->option('check-changes')) {
                $this->info('Checking for configuration changes...');
                $changeSummary = $this->configMonitor->getConfigurationChangeSummary();
                
                if ($changeSummary['has_changes']) {
                    $this->warn('⚠ Configuration changes detected');
                    $this->info("Last known hash: {$changeSummary['last_known_hash']}");
                    $this->info("Current hash: {$changeSummary['current_hash']}");
                    
                    $this->info('Validating configuration changes...');
                    $changeValidationResult = $this->configMonitor->checkForConfigurationChanges($correlationId);
                    
                    if ($changeValidationResult) {
                        $this->info('✓ Configuration changes validated successfully');
                    } else {
                        $this->error('✗ Configuration changes validation failed');
                    }
                } else {
                    $this->info('✓ No configuration changes detected');
                }
                $this->newLine();
            }

            // Perform storage validation
            $this->info('Performing storage configuration validation...');
            
            $validationResult = $this->storageValidator->performStartupValidation($correlationId);
            
            // Display results
            $this->displayValidationResults($validationResult);
            
            // Show detailed information if requested
            if ($this->option('detailed')) {
                $this->newLine();
                $this->displayDetailedValidationStatus($correlationId);
            }

            // Determine exit code based on validation results
            $overallStatus = $validationResult['overall_status'] ?? 'unknown';
            if ($overallStatus === 'critical') {
                $this->error('Storage configuration validation completed with critical issues');
                return 1; // Error exit code
            } else {
                $this->info('Storage configuration validation completed successfully');
                return 0; // Success exit code
            }

        } catch (\Exception $e) {
            $this->error('Storage configuration validation failed with exception:');
            $this->error($e->getMessage());
            
            if ($this->option('detailed')) {
                $this->newLine();
                $this->error('Exception trace:');
                $this->line($e->getTraceAsString());
            }
            
            return 1; // Error exit code
        }
    }

    /**
     * Display validation results in a formatted way.
     * 
     * @param array $validationResult
     */
    private function displayValidationResults(array $validationResult): void
    {
        $overallStatus = $validationResult['overall_status'] ?? 'unknown';
        $this->info("Overall Status: {$overallStatus}");
        
        $functionalDisks = $validationResult['functional_disks'] ?? [];
        $failedDisks = $validationResult['failed_disks'] ?? [];
        
        $this->info("Functional Disks: " . count($functionalDisks));
        $this->info("Failed Disks: " . count($failedDisks));
        
        // Display functional disks
        if (!empty($functionalDisks)) {
            $this->newLine();
            $this->info('✓ Functional Storage Disks:');
            foreach ($functionalDisks as $disk) {
                $this->info("  - {$disk}");
            }
        }
        
        // Display failed disks
        if (!empty($failedDisks)) {
            $this->newLine();
            $this->error('✗ Failed Storage Disks:');
            foreach ($failedDisks as $disk) {
                $this->error("  - {$disk}");
            }
        }
        
        // Display configuration errors
        $configErrors = $validationResult['configuration_errors'] ?? [];
        if (!empty($configErrors)) {
            $this->newLine();
            $this->warn('Configuration Issues:');
            foreach ($configErrors as $errorKey => $error) {
                $severity = $error['severity'] ?? 'unknown';
                $message = $error['message'] ?? 'Unknown error';
                
                $prefix = $severity === 'critical' ? '✗' : '⚠';
                $this->line("  {$prefix} [{$severity}] {$message}");
            }
        }
        
        // Show file operations status
        $fileOpsAllowed = $this->storageValidator->areFileOperationsAllowed();
        $status = $fileOpsAllowed ? '✓ Allowed' : '✗ Blocked';
        $this->info("File Operations: {$status}");
    }

    /**
     * Display detailed validation status information.
     * 
     * @param string $correlationId
     */
    private function displayDetailedValidationStatus(string $correlationId): void
    {
        $this->info('Detailed Validation Status:');
        $this->newLine();
        
        $status = $this->storageValidator->getValidationStatus($correlationId);
        
        // Validation info
        $validationInfo = $status['validation_info'];
        $this->info('Validation Information:');
        $this->info("  Last Validation: {$validationInfo['last_validation']}");
        $this->info("  Validation Age: {$validationInfo['validation_age_minutes']} minutes");
        $this->info("  Validation Enabled: " . ($validationInfo['validation_enabled'] ? 'Yes' : 'No'));
        $this->newLine();
        
        // Disk cache
        $diskCache = $status['disk_cache'];
        if (!empty($diskCache)) {
            $this->info('Disk Cache:');
            foreach ($diskCache as $disk => $cache) {
                $this->info("  {$disk}:");
                $this->info("    Status: {$cache['status']}");
                $this->info("    Validated At: {$cache['validated_at']}");
                if (isset($cache['disk_info'])) {
                    $diskInfo = $cache['disk_info'];
                    $this->info("    Path: {$diskInfo['path']}");
                    if (isset($diskInfo['free_space']) && isset($diskInfo['total_space'])) {
                        $freeGB = round($diskInfo['free_space'] / (1024**3), 2);
                        $totalGB = round($diskInfo['total_space'] / (1024**3), 2);
                        $this->info("    Free Space: {$freeGB} GB / {$totalGB} GB");
                    }
                }
            }
            $this->newLine();
        }
        
        // Recommendations
        $recommendations = $status['recommendations'];
        if (!empty($recommendations)) {
            $this->info('Recommendations:');
            foreach ($recommendations as $recommendation) {
                $priority = $recommendation['priority'] ?? 'info';
                $description = $recommendation['description'] ?? 'No description';
                $action = $recommendation['action'] ?? 'No action specified';
                
                $prefix = $priority === 'critical' ? '!' : ($priority === 'warning' ? '⚠' : 'ℹ');
                $this->line("  {$prefix} {$description}");
                $this->line("    Action: {$action}");
            }
        }
    }
}