<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UploadPerformanceMonitor;
use App\Services\FileUploadLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Unit tests for UploadPerformanceMonitor service.
 * 
 * Tests comprehensive performance monitoring functionality including metrics tracking,
 * alerting, dashboard data generation, and performance analysis.
 * 
 * Requirements: 4.4
 */
class UploadPerformanceMonitorTest extends TestCase
{
    use RefreshDatabase;
    
    private UploadPerformanceMonitor $monitor;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->monitor = new UploadPerformanceMonitor();
        
        // Clear cache before each test
        Cache::flush();
    }
    
    /**
     * Test that UploadPerformanceMonitor can be instantiated.
     */
    public function test_can_instantiate_upload_performance_monitor(): void
    {
        $this->assertInstanceOf(UploadPerformanceMonitor::class, $this->monitor);
    }
    
    /**
     * Test getting dashboard data with default period.
     */
    public function test_get_dashboard_data_with_default_period(): void
    {
        // Arrange - Set up some test data in cache
        Cache::put('total_uploads_day', 100);
        Cache::put('successful_uploads_day', 90);
        Cache::put('avg_duration_day', 15.5);
        Cache::put('avg_memory_day', 25 * 1024 * 1024); // 25MB
        Cache::put('avg_speed_day', 200 * 1024); // 200KB/s
        
        // Act
        $dashboardData = $this->monitor->getDashboardData();
        
        // Assert
        $this->assertIsArray($dashboardData);
        $this->assertEquals('day', $dashboardData['period']);
        $this->assertArrayHasKey('success_rates', $dashboardData);
        $this->assertArrayHasKey('performance_metrics', $dashboardData);
        $this->assertArrayHasKey('resource_usage', $dashboardData);
        $this->assertArrayHasKey('failure_analysis', $dashboardData);
        $this->assertArrayHasKey('active_alerts', $dashboardData);
        $this->assertArrayHasKey('system_health', $dashboardData);
        $this->assertArrayHasKey('recommendations', $dashboardData);
        $this->assertArrayHasKey('timestamp', $dashboardData);
    }
    
    /**
     * Test getting dashboard data with specific period.
     */
    public function test_get_dashboard_data_with_specific_period(): void
    {
        // Arrange
        $period = 'week';
        Cache::put('total_uploads_week', 500);
        Cache::put('successful_uploads_week', 450);
        
        // Act
        $dashboardData = $this->monitor->getDashboardData($period);
        
        // Assert
        $this->assertEquals($period, $dashboardData['period']);
        $this->assertArrayHasKey('success_rates', $dashboardData);
    }
    
    /**
     * Test getting performance metrics.
     */
    public function test_get_performance_metrics(): void
    {
        // Arrange
        $period = 'day';
        Cache::put('avg_duration_day', 25.0);
        Cache::put('avg_memory_day', 75 * 1024 * 1024); // 75MB
        Cache::put('avg_speed_day', 50 * 1024); // 50KB/s
        Cache::put('avg_file_size_day', 5 * 1024 * 1024); // 5MB
        Cache::put('peak_concurrent_day', 15);
        
        // Act
        $metrics = $this->monitor->getPerformanceMetrics($period);
        
        // Assert
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('average_upload_duration', $metrics);
        $this->assertArrayHasKey('average_memory_usage', $metrics);
        $this->assertArrayHasKey('average_upload_speed', $metrics);
        $this->assertArrayHasKey('average_file_size', $metrics);
        $this->assertArrayHasKey('peak_concurrent_uploads', $metrics);
        
        // Check duration metric
        $durationMetric = $metrics['average_upload_duration'];
        $this->assertEquals(25.0, $durationMetric['value']);
        $this->assertEquals('25s', $durationMetric['formatted']);
        $this->assertEquals(30, $durationMetric['threshold']);
        $this->assertEquals('good', $durationMetric['status']);
        
        // Check memory metric (should be warning since 75MB > 50MB threshold)
        $memoryMetric = $metrics['average_memory_usage'];
        $this->assertEquals(75 * 1024 * 1024, $memoryMetric['value']);
        $this->assertEquals('warning', $memoryMetric['status']);
        
        // Check speed metric (should be warning since 50KB/s < 100KB/s threshold)
        $speedMetric = $metrics['average_upload_speed'];
        $this->assertEquals('warning', $speedMetric['status']);
    }
    
    /**
     * Test getting resource usage metrics.
     */
    public function test_get_resource_usage_metrics(): void
    {
        // Act
        $resourceMetrics = $this->monitor->getResourceUsageMetrics('day');
        
        // Assert
        $this->assertIsArray($resourceMetrics);
        $this->assertArrayHasKey('disk_usage', $resourceMetrics);
        $this->assertArrayHasKey('memory_usage', $resourceMetrics);
        $this->assertArrayHasKey('server_load', $resourceMetrics);
        
        // Check disk usage structure
        $diskUsage = $resourceMetrics['disk_usage'];
        $this->assertArrayHasKey('upload_directory', $diskUsage);
        $this->assertArrayHasKey('temp_directory', $diskUsage);
        
        // Check upload directory structure
        $uploadDir = $diskUsage['upload_directory'];
        $this->assertArrayHasKey('free_space', $uploadDir);
        $this->assertArrayHasKey('free_space_formatted', $uploadDir);
        $this->assertArrayHasKey('total_space', $uploadDir);
        $this->assertArrayHasKey('total_space_formatted', $uploadDir);
        $this->assertArrayHasKey('usage_percentage', $uploadDir);
        $this->assertArrayHasKey('status', $uploadDir);
        
        // Check memory usage structure
        $memoryUsage = $resourceMetrics['memory_usage'];
        $this->assertArrayHasKey('current', $memoryUsage);
        $this->assertArrayHasKey('current_formatted', $memoryUsage);
        $this->assertArrayHasKey('peak', $memoryUsage);
        $this->assertArrayHasKey('peak_formatted', $memoryUsage);
        $this->assertArrayHasKey('limit', $memoryUsage);
        $this->assertArrayHasKey('limit_formatted', $memoryUsage);
        $this->assertArrayHasKey('usage_percentage', $memoryUsage);
        $this->assertArrayHasKey('status', $memoryUsage);
    }
    
    /**
     * Test getting active alerts.
     */
    public function test_get_active_alerts(): void
    {
        // Arrange - Set up some test alerts
        $testAlerts = [
            [
                'correlation_id' => 'test-1',
                'timestamp' => now()->toISOString(),
                'max_severity' => 'critical',
                'alerts' => [
                    ['type' => 'slow_upload', 'severity' => 'critical'],
                ],
            ],
            [
                'correlation_id' => 'test-2',
                'timestamp' => now()->subMinutes(30)->toISOString(),
                'max_severity' => 'warning',
                'alerts' => [
                    ['type' => 'high_memory_usage', 'severity' => 'warning'],
                ],
            ],
        ];
        
        $currentHour = now()->format('Y-m-d-H');
        Cache::put("performance_alerts_{$currentHour}", $testAlerts);
        
        // Act
        $activeAlerts = $this->monitor->getActiveAlerts();
        
        // Assert
        $this->assertIsArray($activeAlerts);
        $this->assertArrayHasKey('total_alerts', $activeAlerts);
        $this->assertArrayHasKey('critical_alerts', $activeAlerts);
        $this->assertArrayHasKey('warning_alerts', $activeAlerts);
        $this->assertArrayHasKey('recent_alerts', $activeAlerts);
        $this->assertArrayHasKey('alert_trends', $activeAlerts);
        
        $this->assertEquals(2, $activeAlerts['total_alerts']);
        $this->assertEquals(1, $activeAlerts['critical_alerts']);
        $this->assertEquals(1, $activeAlerts['warning_alerts']);
        $this->assertCount(2, $activeAlerts['recent_alerts']);
    }
    
    /**
     * Test getting system health status.
     */
    public function test_get_system_health_status(): void
    {
        // Act
        $healthStatus = $this->monitor->getSystemHealthStatus();
        
        // Assert
        $this->assertIsArray($healthStatus);
        $this->assertArrayHasKey('overall_status', $healthStatus);
        $this->assertArrayHasKey('health_checks', $healthStatus);
        $this->assertArrayHasKey('last_updated', $healthStatus);
        
        $healthChecks = $healthStatus['health_checks'];
        $this->assertArrayHasKey('disk_space', $healthChecks);
        $this->assertArrayHasKey('temp_directory', $healthChecks);
        $this->assertArrayHasKey('memory_usage', $healthChecks);
        $this->assertArrayHasKey('upload_success_rate', $healthChecks);
        $this->assertArrayHasKey('php_configuration', $healthChecks);
        
        // Check that each health check has status and message
        foreach ($healthChecks as $check) {
            $this->assertArrayHasKey('status', $check);
            $this->assertArrayHasKey('message', $check);
            $this->assertContains($check['status'], ['good', 'warning', 'critical']);
        }
    }
    
    /**
     * Test getting performance recommendations.
     */
    public function test_get_performance_recommendations(): void
    {
        // Arrange - Set up metrics that should trigger recommendations
        Cache::put('avg_duration_day', 45.0); // Should trigger slow upload recommendation
        Cache::put('avg_memory_day', 80 * 1024 * 1024); // Should trigger high memory recommendation
        Cache::put('total_uploads_day', 100);
        Cache::put('successful_uploads_day', 85); // 85% success rate should trigger recommendation
        
        // Act
        $recommendations = $this->monitor->getPerformanceRecommendations('day');
        
        // Assert
        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
        
        // Check that recommendations have required structure
        foreach ($recommendations as $recommendation) {
            $this->assertArrayHasKey('type', $recommendation);
            $this->assertArrayHasKey('priority', $recommendation);
            $this->assertArrayHasKey('title', $recommendation);
            $this->assertArrayHasKey('description', $recommendation);
            $this->assertArrayHasKey('actions', $recommendation);
            $this->assertIsArray($recommendation['actions']);
            $this->assertContains($recommendation['priority'], ['low', 'medium', 'high', 'critical']);
        }
        
        // Should have recommendations for slow upload, high memory, and low success rate
        $recommendationTypes = array_column($recommendations, 'type');
        $this->assertContains('performance', $recommendationTypes);
        $this->assertContains('resource', $recommendationTypes);
        $this->assertContains('reliability', $recommendationTypes);
    }
    
    /**
     * Test generating performance report.
     */
    public function test_generate_performance_report(): void
    {
        // Arrange
        $period = 'day';
        Cache::put('total_uploads_day', 150);
        Cache::put('successful_uploads_day', 135);
        Cache::put('avg_duration_day', 20.0);
        
        // Act
        $report = $this->monitor->generatePerformanceReport($period);
        
        // Assert
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
        
        $this->assertEquals($period, $report['period']);
        $this->assertStringStartsWith('perf_report_', $report['report_id']);
        
        // Check summary structure
        $summary = $report['summary'];
        $this->assertArrayHasKey('total_uploads', $summary);
        $this->assertArrayHasKey('success_rate', $summary);
        $this->assertArrayHasKey('average_duration', $summary);
        $this->assertArrayHasKey('total_alerts', $summary);
        $this->assertArrayHasKey('system_health', $summary);
    }
    
    /**
     * Test performance monitoring with FileUploadLogger integration.
     */
    public function test_performance_monitoring_integration(): void
    {
        // Arrange
        $logger = new FileUploadLogger();
        $correlationId = 'test-correlation-id';
        
        // Mock file for testing
        $mockFile = $this->createMock(\Illuminate\Http\UploadedFile::class);
        $mockFile->method('getSize')->willReturn(1024 * 1024); // 1MB
        $mockFile->method('getClientOriginalExtension')->willReturn('jpg');
        
        // Act - Start performance monitoring
        $context = $logger->startPerformanceMonitoring($correlationId, $mockFile);
        
        // Assert - Check monitoring context
        $this->assertIsArray($context);
        $this->assertArrayHasKey('correlation_id', $context);
        $this->assertArrayHasKey('start_time', $context);
        $this->assertArrayHasKey('start_memory', $context);
        $this->assertArrayHasKey('file_size', $context);
        $this->assertArrayHasKey('file_type', $context);
        $this->assertEquals($correlationId, $context['correlation_id']);
        $this->assertEquals(1024 * 1024, $context['file_size']);
        $this->assertEquals('jpg', $context['file_type']);
        
        // Check that context is stored in cache
        $cachedContext = cache()->get("upload_monitoring_{$correlationId}");
        $this->assertNotNull($cachedContext);
        $this->assertEquals($context, $cachedContext);
        
        // Simulate some processing time
        usleep(100000); // 100ms
        
        // Act - End performance monitoring
        $logger->endPerformanceMonitoring($correlationId, true, [
            'processing_stage' => 'completed',
        ]);
        
        // Assert - Check that context is cleaned up
        $cachedContext = cache()->get("upload_monitoring_{$correlationId}");
        $this->assertNull($cachedContext);
    }
    
    /**
     * Test metric status calculation.
     */
    public function test_metric_status_calculation(): void
    {
        // Test duration metrics
        $metrics = $this->monitor->getPerformanceMetrics('day');
        
        // Test with good duration (below threshold)
        Cache::put('avg_duration_day', 15.0);
        $metrics = $this->monitor->getPerformanceMetrics('day');
        $this->assertEquals('good', $metrics['average_upload_duration']['status']);
        
        // Test with warning duration (above threshold)
        Cache::put('avg_duration_day', 45.0);
        $metrics = $this->monitor->getPerformanceMetrics('day');
        $this->assertEquals('warning', $metrics['average_upload_duration']['status']);
        
        // Test with critical duration (above 2x threshold)
        Cache::put('avg_duration_day', 75.0);
        $metrics = $this->monitor->getPerformanceMetrics('day');
        $this->assertEquals('critical', $metrics['average_upload_duration']['status']);
    }
    
    /**
     * Test alert generation and storage.
     */
    public function test_alert_generation_and_storage(): void
    {
        // This test would require access to private methods
        // For now, we test the public interface that uses these methods
        
        // Arrange - Set up conditions that should trigger alerts
        Cache::put('avg_duration_day', 45.0); // Should trigger slow upload alert
        Cache::put('failed_uploads_hour', 25);
        Cache::put('total_uploads_hour', 100); // 25% failure rate should trigger alert
        
        // Act
        $dashboardData = $this->monitor->getDashboardData('day');
        
        // Assert - Check that recommendations are generated (which indicates alert conditions)
        $this->assertNotEmpty($dashboardData['recommendations']);
        
        $recommendationTypes = array_column($dashboardData['recommendations'], 'type');
        $this->assertContains('performance', $recommendationTypes);
    }
    
    /**
     * Test caching behavior.
     */
    public function test_caching_behavior(): void
    {
        // Arrange
        Cache::put('total_uploads_day', 100);
        Cache::put('successful_uploads_day', 90);
        
        // Act - Get dashboard data twice
        $data1 = $this->monitor->getDashboardData('day');
        $data2 = $this->monitor->getDashboardData('day');
        
        // Assert - Should return consistent data (cached)
        $this->assertEquals($data1['success_rates'], $data2['success_rates']);
    }
    
    /**
     * Test error handling with missing cache data.
     */
    public function test_error_handling_with_missing_cache_data(): void
    {
        // Arrange - Clear all cache (no data available)
        Cache::flush();
        
        // Act
        $dashboardData = $this->monitor->getDashboardData('day');
        
        // Assert - Should handle missing data gracefully
        $this->assertIsArray($dashboardData);
        $this->assertEquals(0, $dashboardData['success_rates']['total_uploads']);
        $this->assertEquals(0, $dashboardData['success_rates']['success_rate_percentage']);
    }
}