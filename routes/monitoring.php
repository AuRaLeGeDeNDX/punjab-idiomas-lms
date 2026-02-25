<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MonitoringController;

/*
|--------------------------------------------------------------------------
| Monitoring Routes
|--------------------------------------------------------------------------
|
| These routes handle the monitoring dashboard, alerts management,
| and performance monitoring for file uploads.
|
*/

Route::middleware(['auth', 'can:view-monitoring-dashboard'])->group(function () {
    
    // Main monitoring dashboard
    Route::get('/monitoring', [MonitoringController::class, 'dashboard'])
        ->name('monitoring.dashboard');
    
    // Dashboard data API (for AJAX updates)
    Route::get('/monitoring/api/dashboard', [MonitoringController::class, 'dashboardData'])
        ->name('monitoring.api.dashboard');
    
    // Alerts management
    Route::get('/monitoring/alerts', [MonitoringController::class, 'alerts'])
        ->name('monitoring.alerts');
    
    Route::get('/monitoring/api/alerts', [MonitoringController::class, 'getAlerts'])
        ->name('monitoring.api.alerts');
    
    // Alert actions
    Route::post('/monitoring/api/alerts/{alertId}/acknowledge', [MonitoringController::class, 'acknowledgeAlert'])
        ->name('monitoring.api.alerts.acknowledge');
    
    Route::post('/monitoring/api/alerts/{alertId}/resolve', [MonitoringController::class, 'resolveAlert'])
        ->name('monitoring.api.alerts.resolve');
    
    // System health
    Route::get('/monitoring/health', [MonitoringController::class, 'health'])
        ->name('monitoring.health');
    
    Route::get('/monitoring/api/health', [MonitoringController::class, 'healthStatus'])
        ->name('monitoring.api.health');
    
    // Performance reports
    Route::get('/monitoring/reports', [MonitoringController::class, 'reports'])
        ->name('monitoring.reports');
    
    // Export functionality
    Route::get('/monitoring/export/{format}', [MonitoringController::class, 'export'])
        ->name('monitoring.export')
        ->where('format', 'pdf|csv|json');
    
});

// Administrative routes (require higher permissions)
Route::middleware(['auth', 'can:manage-monitoring-system'])->group(function () {
    
    // Cleanup old monitoring data
    Route::post('/monitoring/api/cleanup', [MonitoringController::class, 'cleanup'])
        ->name('monitoring.api.cleanup');
    
});

// Public health check endpoint (no authentication required)
Route::get('/health-check', [MonitoringController::class, 'healthStatus'])
    ->name('public.health-check');