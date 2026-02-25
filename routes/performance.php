<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UploadPerformanceController;

/*
|--------------------------------------------------------------------------
| Upload Performance Monitoring Routes
|--------------------------------------------------------------------------
|
| These routes provide API endpoints for upload performance monitoring,
| including dashboard data, metrics, alerts, and system health status.
|
*/

Route::middleware(['auth:sanctum'])->prefix('api/performance')->group(function () {
    
    // Dashboard data endpoint
    Route::get('/dashboard', [UploadPerformanceController::class, 'dashboard'])
        ->name('performance.dashboard');
    
    // Performance metrics endpoint
    Route::get('/metrics', [UploadPerformanceController::class, 'metrics'])
        ->name('performance.metrics');
    
    // Active alerts endpoint
    Route::get('/alerts', [UploadPerformanceController::class, 'alerts'])
        ->name('performance.alerts');
    
    // System health status endpoint
    Route::get('/health', [UploadPerformanceController::class, 'health'])
        ->name('performance.health');
    
    // Performance report generation endpoint
    Route::get('/report', [UploadPerformanceController::class, 'report'])
        ->name('performance.report');
    
    // Resource usage metrics endpoint
    Route::get('/resources', [UploadPerformanceController::class, 'resources'])
        ->name('performance.resources');
});

// Public health check endpoint (no authentication required)
Route::get('/api/health-check', [UploadPerformanceController::class, 'health'])
    ->name('public.health-check');