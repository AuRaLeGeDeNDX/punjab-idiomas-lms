<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FileStorageDiagnosticService;
// use App\Services\FilePathRepairService; // Temporarily disabled due to autoloader issues
use App\Services\FileUploadLogger;

class FileServicesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FileUploadLogger::class, function ($app) {
            return new FileUploadLogger();
        });

        $this->app->singleton(FileStorageDiagnosticService::class, function ($app) {
            return new FileStorageDiagnosticService($app->make(FileUploadLogger::class));
        });

        // FilePathRepairService temporarily disabled
        // $this->app->singleton(FilePathRepairService::class, function ($app) {
        //     return new FilePathRepairService($app->make(FileStorageDiagnosticService::class));
        // });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}