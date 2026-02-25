<?php

namespace App\Http\Controllers;

use App\Services\MonitoringDashboardService;
use App\Services\AlertManagementService;
use App\Services\UploadPerformanceMonitor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * MonitoringController handles monitoring dashboard and alert management endpoints.
 * 
 * This controller provides web interfaces for monitoring file upload performance,
 * viewing dashboards, managing alerts, and exporting monitoring data.
 * 
 * Requirements: 4.4
 */
class MonitoringController extends Controller
{
    private MonitoringDashboardService $dashboardService;
    private AlertManagementService $alertService;
    private UploadPerformanceMonitor $performanceMonitor;

    public function __construct(
        MonitoringDashboardService $dashboardService,
        AlertManagementService $alertService,
        UploadPerformanceMonitor $performanceMonitor
    ) {
        $this->dashboardService = $dashboardService;
        $this->alertService = $alertService;
        $this->performanceMonitor = $performanceMonitor;
        
        // Ensure user has appropriate permissions
        $this->middleware('auth');
        $this->middleware('can:view-monitoring-dashboard');
    }

    /**
     * Display the main monitoring dashboard.
     * 
     * @param Request $request
     * @return View
     */
    public function dashboard(Request $request): View
    {
        $period = $request->get('period', 'day');
        
        // Validate period
        if (!in_array($period, ['hour', 'day', 'week', 'month'])) {
            $period = 'day';
        }

        try {
            $config = $this->dashboardService->generateDashboardConfig($period);
            
            return view('monitoring.dashboard', [
                'config' => $config,
                'period' => $period,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Monitoring dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'period' => $period,
            ]);
            
            // Return error view or redirect with error message
            return view('monitoring.dashboard-error', [
                'error' => 'Failed to load monitoring dashboard',
                'period' => $period,
            ]);
        }
    }

    /**
     * Get dashboard data as JSON (for AJAX updates).
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboardData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'day');
        
        if (!in_array($period, ['hour', 'day', 'week', 'month'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid period parameter',
            ], 400);
        }

        try {
            $config = $this->dashboardService->generateDashboardConfig($period);
            
            return response()->json([
                'success' => true,
                'data' => $config,
                'timestamp' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard data API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'period' => $period,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display alerts management page.
     * 
     * @param Request $request
     * @return View
     */
    public function alerts(Request $request): View
    {
        $severity = $request->get('severity');
        $limit = (int) $request->get('limit', 50);
        
        try {
            $alerts = $this->alertService->getActiveAlerts($severity, $limit);
            $statistics = $this->alertService->getAlertStatistics('day');
            
            return view('monitoring.alerts', [
                'alerts' => $alerts,
                'statistics' => $statistics,
                'severity_filter' => $severity,
                'limit' => $limit,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Alerts page error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return view('monitoring.alerts-error', [
                'error' => 'Failed to load alerts',
            ]);
        }
    }

    /**
     * Get active alerts as JSON.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getAlerts(Request $request): JsonResponse
    {
        $severity = $request->get('severity');
        $limit = (int) $request->get('limit', 50);
        
        try {
            $alerts = $this->alertService->getActiveAlerts($severity, $limit);
            $statistics = $this->alertService->getAlertStatistics('hour');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'alerts' => $alerts,
                    'statistics' => $statistics,
                    'total_count' => count($alerts),
                ],
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get alerts API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve alerts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Acknowledge an alert.
     * 
     * @param Request $request
     * @param string $alertId
     * @return JsonResponse
     */
    public function acknowledgeAlert(Request $request, string $alertId): JsonResponse
    {
        try {
            $success = $this->alertService->acknowledgeAlert(
                $alertId,
                auth()->user()->email ?? 'unknown'
            );
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alert acknowledged successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Alert not found or already acknowledged',
                ], 404);
            }
            
        } catch (\Exception $e) {
            \Log::error('Acknowledge alert error', [
                'alert_id' => $alertId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to acknowledge alert',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve an alert.
     * 
     * @param Request $request
     * @param string $alertId
     * @return JsonResponse
     */
    public function resolveAlert(Request $request, string $alertId): JsonResponse
    {
        $request->validate([
            'resolution' => 'nullable|string|max:1000',
        ]);
        
        try {
            $success = $this->alertService->resolveAlert(
                $alertId,
                auth()->user()->email ?? 'unknown',
                $request->input('resolution')
            );
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alert resolved successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Alert not found or already resolved',
                ], 404);
            }
            
        } catch (\Exception $e) {
            \Log::error('Resolve alert error', [
                'alert_id' => $alertId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve alert',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export dashboard data in various formats.
     * 
     * @param Request $request
     * @param string $format
     * @return mixed
     */
    public function export(Request $request, string $format)
    {
        $period = $request->get('period', 'day');
        
        if (!in_array($period, ['hour', 'day', 'week', 'month'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid period parameter',
            ], 400);
        }
        
        if (!in_array($format, ['pdf', 'csv', 'json'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid export format',
            ], 400);
        }

        try {
            $exportData = $this->dashboardService->exportDashboardData($format, $period);
            
            switch ($format) {
                case 'pdf':
                    return $this->generatePDFResponse($exportData);
                case 'csv':
                    return $this->generateCSVResponse($exportData);
                case 'json':
                    return $this->generateJSONResponse($exportData);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported export format',
                    ], 400);
            }
            
        } catch (\Exception $e) {
            \Log::error('Export dashboard error', [
                'format' => $format,
                'period' => $period,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to export dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display system health status page.
     * 
     * @return View
     */
    public function health(): View
    {
        try {
            $healthStatus = $this->performanceMonitor->getSystemHealthStatus();
            $resourceMetrics = $this->performanceMonitor->getResourceUsageMetrics('day');
            
            return view('monitoring.health', [
                'health_status' => $healthStatus,
                'resource_metrics' => $resourceMetrics,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Health status page error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return view('monitoring.health-error', [
                'error' => 'Failed to load health status',
            ]);
        }
    }

    /**
     * Get system health status as JSON.
     * 
     * @return JsonResponse
     */
    public function healthStatus(): JsonResponse
    {
        try {
            $healthStatus = $this->performanceMonitor->getSystemHealthStatus();
            
            return response()->json([
                'success' => true,
                'data' => $healthStatus,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Health status API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve health status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display performance reports page.
     * 
     * @param Request $request
     * @return View
     */
    public function reports(Request $request): View
    {
        $period = $request->get('period', 'day');
        
        try {
            $report = $this->performanceMonitor->generatePerformanceReport($period);
            
            return view('monitoring.reports', [
                'report' => $report,
                'period' => $period,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Performance reports error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'period' => $period,
            ]);
            
            return view('monitoring.reports-error', [
                'error' => 'Failed to generate performance report',
                'period' => $period,
            ]);
        }
    }

    /**
     * Clean up old monitoring data.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function cleanup(Request $request): JsonResponse
    {
        // Only allow administrators to perform cleanup
        if (!auth()->user()->can('manage-monitoring-system')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions',
            ], 403);
        }
        
        $daysToKeep = (int) $request->get('days', 7);
        
        if ($daysToKeep < 1 || $daysToKeep > 365) {
            return response()->json([
                'success' => false,
                'message' => 'Days to keep must be between 1 and 365',
            ], 400);
        }

        try {
            $this->alertService->cleanupOldAlerts($daysToKeep);
            
            return response()->json([
                'success' => true,
                'message' => "Old monitoring data cleaned up (kept last {$daysToKeep} days)",
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Monitoring cleanup error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'days_to_keep' => $daysToKeep,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup monitoring data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF response for export.
     * 
     * @param array $exportData Export data
     * @return \Illuminate\Http\Response
     */
    private function generatePDFResponse(array $exportData)
    {
        // This would typically use a PDF library like DomPDF or wkhtmltopdf
        // For now, return a simple response
        return response()->json([
            'success' => true,
            'message' => 'PDF export functionality not yet implemented',
            'data' => $exportData,
        ]);
    }

    /**
     * Generate CSV response for export.
     * 
     * @param array $exportData Export data
     * @return \Illuminate\Http\Response
     */
    private function generateCSVResponse(array $exportData)
    {
        $filename = $exportData['filename'];
        $data = $exportData['data'];
        $headers = $exportData['headers'];
        
        $csvContent = implode(',', $headers) . "\n";
        
        foreach ($data as $row) {
            $csvContent .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, array_values($row))) . "\n";
        }
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate JSON response for export.
     * 
     * @param array $exportData Export data
     * @return \Illuminate\Http\Response
     */
    private function generateJSONResponse(array $exportData)
    {
        $filename = $exportData['filename'];
        $data = $exportData['data'];
        $metadata = $exportData['metadata'] ?? [];
        
        $jsonData = [
            'metadata' => $metadata,
            'data' => $data,
        ];
        
        return response()->json($jsonData)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}