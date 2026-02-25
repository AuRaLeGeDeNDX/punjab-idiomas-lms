<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UploadPerformanceMonitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * UploadPerformanceController provides API endpoints for upload performance monitoring.
 * 
 * This controller exposes performance metrics, dashboard data, and alerts
 * for monitoring file upload performance and system health.
 * 
 * Requirements: 4.4
 */
class UploadPerformanceController extends Controller
{
    private UploadPerformanceMonitor $performanceMonitor;
    
    public function __construct(UploadPerformanceMonitor $performanceMonitor)
    {
        $this->performanceMonitor = $performanceMonitor;
    }
    
    /**
     * Get upload performance dashboard data.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboard(Request $request): JsonResponse
    {
        $period = $request->get('period', 'day');
        
        // Validate period
        if (!in_array($period, ['hour', 'day', 'week', 'month'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid period. Must be one of: hour, day, week, month',
            ], 400);
        }
        
        try {
            $dashboardData = $this->performanceMonitor->getDashboardData($period);
            
            return response()->json([
                'success' => true,
                'data' => $dashboardData,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Upload performance dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get performance metrics for a specific period.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function metrics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'day');
        
        if (!in_array($period, ['hour', 'day', 'week', 'month'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid period. Must be one of: hour, day, week, month',
            ], 400);
        }
        
        try {
            $metrics = $this->performanceMonitor->getPerformanceMetrics($period);
            
            return response()->json([
                'success' => true,
                'period' => $period,
                'data' => $metrics,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Upload performance metrics error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get active performance alerts.
     * 
     * @return JsonResponse
     */
    public function alerts(): JsonResponse
    {
        try {
            $alerts = $this->performanceMonitor->getActiveAlerts();
            
            return response()->json([
                'success' => true,
                'data' => $alerts,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Upload performance alerts error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance alerts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get system health status.
     * 
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        try {
            $healthStatus = $this->performanceMonitor->getSystemHealthStatus();
            
            return response()->json([
                'success' => true,
                'data' => $healthStatus,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('System health status error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system health status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate performance report.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        $period = $request->get('period', 'day');
        
        if (!in_array($period, ['hour', 'day', 'week', 'month'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid period. Must be one of: hour, day, week, month',
            ], 400);
        }
        
        try {
            $report = $this->performanceMonitor->generatePerformanceReport($period);
            
            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Performance report generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate performance report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get resource usage metrics.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resources(Request $request): JsonResponse
    {
        $period = $request->get('period', 'day');
        
        if (!in_array($period, ['hour', 'day', 'week', 'month'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid period. Must be one of: hour, day, week, month',
            ], 400);
        }
        
        try {
            $resourceMetrics = $this->performanceMonitor->getResourceUsageMetrics($period);
            
            return response()->json([
                'success' => true,
                'period' => $period,
                'data' => $resourceMetrics,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Resource usage metrics error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve resource usage metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}