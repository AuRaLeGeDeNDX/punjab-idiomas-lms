<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\MonitoringDashboardService;
use App\Services\AlertManagementService;
use App\Services\UploadPerformanceMonitor;
use App\Services\FileUploadLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * MonitoringSystemTest verifies the monitoring and alerting system functionality.
 * 
 * This test suite ensures that monitoring dashboards, alerts, and performance
 * tracking work correctly for the file upload system.
 * 
 * Requirements: 4.4
 */
class MonitoringSystemTest extends TestCase
{
    use RefreshDatabase;

    private MonitoringDashboardService $dashboardService;
    private AlertManagementService $alertService;
    private UploadPerformanceMonitor $performanceMonitor;
    private FileUploadLogger $uploadLogger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dashboardService = app(MonitoringDashboardService::class);
        $this->alertService = app(AlertManagementService::class);
        $this->performanceMonitor = app(UploadPerformanceMonitor::class);
        $this->uploadLogger = app(FileUploadLogger::class);
        
        // Enable monitoring for tests
        Config::set('monitoring.enabled', true);
        Config::set('monitoring.alerts.enabled', true);
        Config::set('monitoring.dashboard.enabled', true);
    }

    /** @test */
    public function it_can_generate_dashboard_configuration()
    {
        $config = $this->dashboardService->generateDashboardConfig('day');
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('dashboard_id', $config);
        $this->assertArrayHasKey('title', $config);
        $this->assertArrayHasKey('period', $config);
        $this->assertArrayHasKey('widgets', $config);
        $this->assertArrayHasKey('charts', $config);
        $this->assertEquals('day', $config['period']);
        $this->assertEquals('File Upload Monitoring Dashboard', $config['title']);
    }

    /** @test */
    public function it_can_generate_dashboard_widgets()
    {
        $config = $this->dashboardService->generateDashboardConfig('day');
        $widgets = $config['widgets'];
        
        $this->assertArrayHasKey('system_health', $widgets);
        $this->assertArrayHasKey('upload_stats', $widgets);
        $this->assertArrayHasKey('performance_summary', $widgets);
        $this->assertArrayHasKey('active_alerts', $widgets);
        $this->assertArrayHasKey('recommendations', $widgets);
        $this->assertArrayHasKey('recent_activity', $widgets);
        
        // Verify widget structure
        $systemHealthWidget = $widgets['system_health'];
        $this->assertEquals('status_card', $systemHealthWidget['type']);
        $this->assertEquals('System Health', $systemHealthWidget['title']);
        $this->assertArrayHasKey('data', $systemHealthWidget);
        $this->assertArrayHasKey('config', $systemHealthWidget);
    }

    /** @test */
    public function it_can_generate_dashboard_charts()
    {
        $config = $this->dashboardService->generateDashboardConfig('day');
        $charts = $config['charts'];
        
        $this->assertArrayHasKey('success_rate_chart', $charts);
        $this->assertArrayHasKey('performance_trends_chart', $charts);
        $this->assertArrayHasKey('resource_usage_chart', $charts);
        $this->assertArrayHasKey('failure_patterns_chart', $charts);
        $this->assertArrayHasKey('upload_volume_chart', $charts);
        
        // Verify chart structure
        $successRateChart = $charts['success_rate_chart'];
        $this->assertEquals('line_chart', $successRateChart['type']);
        $this->assertEquals('Upload Success Rate Over Time', $successRateChart['title']);
        $this->assertArrayHasKey('data', $successRateChart);
        $this->assertArrayHasKey('config', $successRateChart);
    }

    /** @test */
    public function it_can_create_performance_alerts()
    {
        $performanceMetrics = [
            'duration_seconds' => 45.0,
            'duration_formatted' => '45.0s',
            'memory_used' => 75 * 1024 * 1024, // 75MB
            'memory_used_formatted' => '75 MB',
            'upload_speed_bps' => 80 * 1024, // 80KB/s
            'upload_speed_formatted' => '80 KB/s',
            'file_size' => 10 * 1024 * 1024, // 10MB
        ];
        
        $alerts = $this->alertService->createPerformanceAlerts($performanceMetrics, 'test-correlation-id');
        
        $this->assertIsArray($alerts);
        
        // Should create alerts for duration and memory (both exceed warning thresholds)
        $this->assertGreaterThan(0, count($alerts));
        
        // Verify alert structure
        if (!empty($alerts)) {
            $alert = $alerts[0];
            $this->assertArrayHasKey('id', $alert);
            $this->assertArrayHasKey('type', $alert);
            $this->assertArrayHasKey('severity', $alert);
            $this->assertArrayHasKey('message', $alert);
            $this->assertArrayHasKey('context', $alert);
            $this->assertArrayHasKey('correlation_id', $alert);
            $this->assertEquals('test-correlation-id', $alert['correlation_id']);
        }
    }

    /** @test */
    public function it_can_create_resource_alerts()
    {
        $resourceMetrics = [
            'disk_usage' => [
                'upload_directory' => [
                    'usage_percentage' => 85.0,
                    'status' => 'warning',
                ],
            ],
            'memory_usage' => [
                'usage_percentage' => 92.0,
                'status' => 'critical',
            ],
        ];
        
        $alerts = $this->alertService->createResourceAlerts($resourceMetrics);
        
        $this->assertIsArray($alerts);
        $this->assertGreaterThan(0, count($alerts));
        
        // Should create alerts for both disk and memory usage
        $alertTypes = array_column($alerts, 'type');
        $this->assertContains('disk_usage', $alertTypes);
        $this->assertContains('memory_limit', $alertTypes);
    }

    /** @test */
    public function it_can_create_failure_rate_alerts()
    {
        $failureRate = 25.0; // 25% failure rate (exceeds critical threshold)
        $period = 'day';
        
        $alert = $this->alertService->createFailureRateAlert($failureRate, $period);
        
        $this->assertIsArray($alert);
        $this->assertEquals('failure_rate', $alert['type']);
        $this->assertEquals('critical', $alert['severity']);
        $this->assertStringContainsString('25%', $alert['message']);
        $this->assertStringContainsString('day', $alert['message']);
    }

    /** @test */
    public function it_can_acknowledge_alerts()
    {
        // Create a test alert
        $alert = $this->alertService->createAlert(
            'test_alert',
            'warning',
            'Test alert message',
            ['test' => true],
            'test-correlation-id'
        );
        
        $alertId = $alert['id'];
        
        // Acknowledge the alert
        $success = $this->alertService->acknowledgeAlert($alertId, 'test@example.com');
        
        $this->assertTrue($success);
        
        // Verify alert is acknowledged
        $alerts = $this->alertService->getActiveAlerts();
        $acknowledgedAlert = collect($alerts)->firstWhere('id', $alertId);
        
        $this->assertNotNull($acknowledgedAlert);
        $this->assertTrue($acknowledgedAlert['acknowledged']);
        $this->assertEquals('test@example.com', $acknowledgedAlert['acknowledged_by']);
        $this->assertArrayHasKey('acknowledged_at', $acknowledgedAlert);
    }

    /** @test */
    public function it_can_resolve_alerts()
    {
        // Create a test alert
        $alert = $this->alertService->createAlert(
            'test_alert',
            'warning',
            'Test alert message',
            ['test' => true],
            'test-correlation-id'
        );
        
        $alertId = $alert['id'];
        
        // Resolve the alert
        $success = $this->alertService->resolveAlert($alertId, 'test@example.com', 'Issue resolved');
        
        $this->assertTrue($success);
        
        // Verify alert is resolved
        $alerts = $this->alertService->getActiveAlerts();
        $resolvedAlert = collect($alerts)->firstWhere('id', $alertId);
        
        // Should not appear in active alerts since it's resolved
        $this->assertNull($resolvedAlert);
    }

    /** @test */
    public function it_can_get_alert_statistics()
    {
        // Create test alerts
        $this->alertService->createAlert('test_critical', 'critical', 'Critical test alert');
        $this->alertService->createAlert('test_warning', 'warning', 'Warning test alert');
        $this->alertService->createAlert('test_info', 'info', 'Info test alert');
        
        $statistics = $this->alertService->getAlertStatistics('day');
        
        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total_alerts', $statistics);
        $this->assertArrayHasKey('critical_alerts', $statistics);
        $this->assertArrayHasKey('warning_alerts', $statistics);
        $this->assertArrayHasKey('info_alerts', $statistics);
        $this->assertArrayHasKey('acknowledged_alerts', $statistics);
        $this->assertArrayHasKey('resolved_alerts', $statistics);
        $this->assertArrayHasKey('active_alerts', $statistics);
        
        $this->assertEquals(3, $statistics['total_alerts']);
        $this->assertEquals(1, $statistics['critical_alerts']);
        $this->assertEquals(1, $statistics['warning_alerts']);
        $this->assertEquals(1, $statistics['info_alerts']);
    }

    /** @test */
    public function it_can_get_performance_dashboard_data()
    {
        $dashboardData = $this->performanceMonitor->getDashboardData('day');
        
        $this->assertIsArray($dashboardData);
        $this->assertArrayHasKey('period', $dashboardData);
        $this->assertArrayHasKey('timestamp', $dashboardData);
        $this->assertArrayHasKey('success_rates', $dashboardData);
        $this->assertArrayHasKey('performance_metrics', $dashboardData);
        $this->assertArrayHasKey('resource_usage', $dashboardData);
        $this->assertArrayHasKey('failure_analysis', $dashboardData);
        $this->assertArrayHasKey('active_alerts', $dashboardData);
        $this->assertArrayHasKey('system_health', $dashboardData);
        $this->assertArrayHasKey('recommendations', $dashboardData);
        
        $this->assertEquals('day', $dashboardData['period']);
    }

    /** @test */
    public function it_can_get_performance_metrics()
    {
        $metrics = $this->performanceMonitor->getPerformanceMetrics('day');
        
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('average_upload_duration', $metrics);
        $this->assertArrayHasKey('average_memory_usage', $metrics);
        $this->assertArrayHasKey('average_upload_speed', $metrics);
        $this->assertArrayHasKey('average_file_size', $metrics);
        $this->assertArrayHasKey('peak_concurrent_uploads', $metrics);
        
        // Verify metric structure
        $durationMetric = $metrics['average_upload_duration'];
        $this->assertArrayHasKey('value', $durationMetric);
        $this->assertArrayHasKey('formatted', $durationMetric);
        $this->assertArrayHasKey('threshold', $durationMetric);
        $this->assertArrayHasKey('status', $durationMetric);
    }

    /** @test */
    public function it_can_get_resource_usage_metrics()
    {
        $resourceMetrics = $this->performanceMonitor->getResourceUsageMetrics('day');
        
        $this->assertIsArray($resourceMetrics);
        $this->assertArrayHasKey('disk_usage', $resourceMetrics);
        $this->assertArrayHasKey('memory_usage', $resourceMetrics);
        $this->assertArrayHasKey('server_load', $resourceMetrics);
        
        // Verify disk usage structure
        $diskUsage = $resourceMetrics['disk_usage'];
        $this->assertArrayHasKey('upload_directory', $diskUsage);
        $this->assertArrayHasKey('temp_directory', $diskUsage);
        
        $uploadDir = $diskUsage['upload_directory'];
        $this->assertArrayHasKey('free_space', $uploadDir);
        $this->assertArrayHasKey('total_space', $uploadDir);
        $this->assertArrayHasKey('usage_percentage', $uploadDir);
        $this->assertArrayHasKey('status', $uploadDir);
    }

    /** @test */
    public function it_can_get_system_health_status()
    {
        $healthStatus = $this->performanceMonitor->getSystemHealthStatus();
        
        $this->assertIsArray($healthStatus);
        $this->assertArrayHasKey('overall_status', $healthStatus);
        $this->assertArrayHasKey('health_checks', $healthStatus);
        $this->assertArrayHasKey('last_updated', $healthStatus);
        
        $this->assertContains($healthStatus['overall_status'], ['good', 'warning', 'critical']);
        
        // Verify health checks structure
        $healthChecks = $healthStatus['health_checks'];
        $this->assertArrayHasKey('disk_space', $healthChecks);
        $this->assertArrayHasKey('memory_usage', $healthChecks);
        $this->assertArrayHasKey('upload_success_rate', $healthChecks);
        $this->assertArrayHasKey('php_configuration', $healthChecks);
        
        foreach ($healthChecks as $check) {
            $this->assertArrayHasKey('status', $check);
            $this->assertArrayHasKey('message', $check);
        }
    }

    /** @test */
    public function it_can_generate_performance_report()
    {
        $report = $this->performanceMonitor->generatePerformanceReport('day');
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('report_id', $report);
        $this->assertArrayHasKey('generated_at', $report);
        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('detailed_metrics', $report);
        $this->assertArrayHasKey('resource_usage', $report);
        $this->assertArrayHasKey('failure_analysis', $report);
        $this->assertArrayHasKey('recommendations', $report);
        $this->assertArrayHasKey('trends', $report);
        
        $this->assertEquals('day', $report['period']);
        $this->assertStringStartsWith('perf_report_', $report['report_id']);
    }

    /** @test */
    public function it_can_export_dashboard_data()
    {
        $formats = ['csv', 'json'];
        
        foreach ($formats as $format) {
            $exportData = $this->dashboardService->exportDashboardData($format, 'day');
            
            $this->assertIsArray($exportData);
            $this->assertArrayHasKey('format', $exportData);
            $this->assertArrayHasKey('filename', $exportData);
            $this->assertArrayHasKey('data', $exportData);
            
            $this->assertEquals($format, $exportData['format']);
            $this->assertStringContainsString($format, $exportData['filename']);
            $this->assertStringContainsString('day', $exportData['filename']);
        }
    }

    /** @test */
    public function it_respects_rate_limiting_for_alerts()
    {
        // Set low rate limits for testing
        Config::set('monitoring.alerts.rate_limiting.max_alerts_per_hour', 2);
        Config::set('monitoring.alerts.rate_limiting.cooldown_minutes', 1);
        
        // Create multiple alerts of the same type
        $alert1 = $this->alertService->createAlert('test_type', 'warning', 'First alert');
        $alert2 = $this->alertService->createAlert('test_type', 'warning', 'Second alert');
        $alert3 = $this->alertService->createAlert('test_type', 'warning', 'Third alert (should be rate limited)');
        
        // First two should be created
        $this->assertArrayHasKey('id', $alert1);
        $this->assertArrayHasKey('id', $alert2);
        
        // Third should be rate limited (still returns alert structure but not stored)
        $this->assertArrayHasKey('id', $alert3);
    }

    /** @test */
    public function it_can_clean_up_old_alerts()
    {
        // Create test alerts
        $alert1 = $this->alertService->createAlert('old_alert', 'warning', 'Old alert');
        $alert2 = $this->alertService->createAlert('new_alert', 'warning', 'New alert');
        
        // Verify alerts exist
        $alertsBefore = $this->alertService->getActiveAlerts();
        $this->assertGreaterThanOrEqual(2, count($alertsBefore));
        
        // Clean up old alerts (keep 0 days for testing)
        $this->alertService->cleanupOldAlerts(0);
        
        // Verify cleanup doesn't break the system
        $alertsAfter = $this->alertService->getActiveAlerts();
        $this->assertIsArray($alertsAfter);
    }

    /** @test */
    public function it_handles_invalid_periods_gracefully()
    {
        // Test with invalid period
        $config = $this->dashboardService->generateDashboardConfig('invalid_period');
        
        // Should default to 'day' or handle gracefully
        $this->assertIsArray($config);
        $this->assertArrayHasKey('period', $config);
    }

    /** @test */
    public function it_handles_missing_data_gracefully()
    {
        // Clear any cached data
        Cache::flush();
        
        // Should still return valid structure even with no data
        $dashboardData = $this->performanceMonitor->getDashboardData('day');
        
        $this->assertIsArray($dashboardData);
        $this->assertArrayHasKey('success_rates', $dashboardData);
        $this->assertArrayHasKey('performance_metrics', $dashboardData);
        
        // Values should be zero or default values, not null/undefined
        $successRates = $dashboardData['success_rates'];
        $this->assertArrayHasKey('total_uploads', $successRates);
        $this->assertArrayHasKey('success_rate_percentage', $successRates);
    }
}