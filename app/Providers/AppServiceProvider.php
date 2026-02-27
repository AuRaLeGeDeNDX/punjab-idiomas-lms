<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\RepairFilePaths;
use App\Services\StorageConfigurationValidator;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register base file services first with explicit instantiation
        $this->app->singleton(\App\Services\FileUploadLogger::class, function ($app) {
            return new \App\Services\FileUploadLogger();
        });

        // Temporarily disable FileOperationLogger to focus on SecureFileStorageService issue
        // $this->app->singleton(\App\Services\FileOperationLogger::class, function ($app) {
        //     return new \App\Services\FileOperationLogger();
        // });

        // Register StorageConfigurationValidator service (use the Simple version that works)
        $this->app->singleton(\App\Services\StorageConfigurationValidator::class, function ($app) {
            return new \App\Services\StorageConfigurationValidatorSimple();
        });
        
        // Also register the Simple version directly for consistency
        $this->app->singleton(\App\Services\StorageConfigurationValidatorSimple::class, function ($app) {
            return $app->make(\App\Services\StorageConfigurationValidator::class);
        });

        // Register SecureFileStorageService with explicit dependency resolution
        $this->app->singleton(\App\Services\SecureFileStorageService::class, function ($app) {
            $fileUploadLogger = $app->make(\App\Services\FileUploadLogger::class);
            return new \App\Services\SecureFileStorageService($fileUploadLogger);
        });

        // Register FileStorageDiagnosticService
        $this->app->singleton(\App\Services\FileStorageDiagnosticService::class, function ($app) {
            return new \App\Services\FileStorageDiagnosticService($app->make(\App\Services\FileUploadLogger::class));
        });

        // Register FileRepairService (renamed from FilePathRepairService)
        $this->app->singleton(\App\Services\FileRepairService::class, function ($app) {
            return new \App\Services\FileRepairService(
                $app->make(\App\Services\FileStorageDiagnosticService::class)
            );
        });

        // Register RepairTriggerService (renamed from AutomaticRepairTriggerService)
        $this->app->singleton(\App\Services\RepairTriggerService::class, function ($app) {
            return new \App\Services\RepairTriggerService(
                $app->make(\App\Services\FileRepairService::class),
                $app->make(\App\Services\FileStorageDiagnosticService::class)
            );
        });

        // Register ContentBlockService with dependencies
        $this->app->singleton(\App\Services\ContentBlockService::class, function ($app) {
            return new \App\Services\ContentBlockService(
                $app->make(\App\Services\SecureFileStorageService::class),
                $app->make(\App\Services\FileStorageDiagnosticService::class),
                $app->make(\App\Services\StorageConfigurationValidator::class),
                $app->make(\App\Services\RepairTriggerService::class)
            );
        });

        // Register StorageConfigurationChangeListener
        $this->app->singleton(\App\Listeners\StorageConfigurationChangeListener::class, function ($app) {
            return new \App\Listeners\StorageConfigurationChangeListener(
                $app->make(\App\Services\StorageConfigurationValidator::class)
            );
        });

        // Register StorageConfigurationMonitor
        $this->app->singleton(\App\Services\StorageConfigurationMonitor::class, function ($app) {
            return new \App\Services\StorageConfigurationMonitor(
                $app->make(\App\Listeners\StorageConfigurationChangeListener::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

         if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Simple language switcher via query param
        if (request()->has('lang')) {
            $lang = request()->get('lang');
            if (in_array($lang, ['es', 'en', 'pa', 'hi', 'ca', 'ur'])) {
                app()->setLocale($lang);
                session()->put('locale', $lang);
            }
        } elseif (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\RepairFilePaths::class,
                \App\Console\Commands\ValidateStorageConfiguration::class,
                \App\Console\Commands\ScheduledRepairCommand::class,
            ]);
        }

        // Perform storage configuration validation during application boot
        // Skip validation during testing and specific console commands to avoid issues
        // OPTIMIZATION: Disabled automatic validation on boot as it adds 2-4s latency.
        if (false && !$this->app->runningUnitTests() && !$this->isMaintenanceCommand()) {
            $this->performStartupStorageValidation();
        }
    }

    /**
     * Perform storage configuration validation during application startup.
     * 
     * Requirements: 8.1, 8.4
     */
    private function performStartupStorageValidation(): void
    {
        try {
            $validator = $this->app->make(\App\Services\StorageConfigurationValidator::class);
            
            Log::info('AppServiceProvider: Starting storage configuration validation during boot');
            
            $validationResult = $validator->performStartupValidation();
            
            // Log validation results (Simple version returns array)
            $overallStatus = $validationResult['overall_status'] ?? 'unknown';
            $functionalDisks = $validationResult['functional_disks'] ?? [];
            $failedDisks = $validationResult['failed_disks'] ?? [];
            $configurationErrors = $validationResult['configuration_errors'] ?? [];
            
            if ($overallStatus === 'critical') {
                Log::critical('AppServiceProvider: Critical storage configuration issues detected during boot', [
                    'overall_status' => $overallStatus,
                    'functional_disks' => count($functionalDisks),
                    'failed_disks' => count($failedDisks),
                    'configuration_errors' => count($configurationErrors),
                ]);
                
                // For critical failures, we log but don't throw exceptions during boot
                // to prevent the application from failing to start completely
                Log::warning('AppServiceProvider: Application started with critical storage issues - file operations may be limited');
            } else {
                Log::info('AppServiceProvider: Storage configuration validation completed successfully during boot', [
                    'overall_status' => $overallStatus,
                    'functional_disks' => count($functionalDisks),
                ]);
            }
            
        } catch (\Exception $e) {
            // Log validation errors but don't prevent application boot
            Log::error('AppServiceProvider: Storage configuration validation failed during boot', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // In production, we might want to disable file operations or send alerts
            Log::warning('AppServiceProvider: Application started with storage validation failure - file operations may be unreliable');
        }
    }

    /**
     * Check if the current command is a maintenance command that should skip validation.
     * 
     * @return bool
     */
    private function isMaintenanceCommand(): bool
    {
        if (!$this->app->runningInConsole()) {
            return false;
        }

        $maintenanceCommands = [
            'migrate',
            'migrate:rollback',
            'migrate:reset',
            'migrate:refresh',
            'migrate:fresh',
            'migrate:install',
            'migrate:status',
            'db:seed',
            'cache:clear',
            'config:clear',
            'config:cache',
            'route:clear',
            'route:cache',
            'view:clear',
            'view:cache',
            'storage:link',
            'queue:work',
            'queue:listen',
            'schedule:run',
            'tinker',
        ];

        $currentCommand = $_SERVER['argv'][1] ?? '';
        
        foreach ($maintenanceCommands as $command) {
            if (str_starts_with($currentCommand, $command)) {
                return true;
            }
        }

        return false;
    }
}
