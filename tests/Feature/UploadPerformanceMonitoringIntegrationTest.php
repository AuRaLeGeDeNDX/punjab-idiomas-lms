<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Services\FileUploadLogger;
use App\Services\UploadPerformanceMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;

/**
 * Integration tests for upload performance monitoring with ContentBlockController.
 * 
 * Tests the complete performance monitoring flow including upload attempts,
 * performance tracking, metrics collection, and alert generation.
 * 
 * Requirements: 4.4
 */
class UploadPerformanceMonitoringIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    private User $teacher;
    private Course $course;
    private Module $module;
    private Subpage $subpage;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        \Spatie\Permission\Models\Role::create(['name' => 'teacher']);
        \Spatie\Permission\Models\Role::create(['name' => 'student']);
        
        // Create test data
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('teacher');
        
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
        
        // Set up storage
        Storage::fake('public');
        
        // Clear cache
        Cache::flush();
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test performance monitoring during successful file upload.
     */
    public function test_performance_monitoring_during_successful_upload(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        
        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600)->size(500); // 500KB
        
        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Image',
            'description' => 'Test image description',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'file' => $file,
        ]);
        
        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'correlation_id',
        ]);
        
        $correlationId = $response->json('correlation_id');
        $this->assertNotEmpty($correlationId);
        
        // Check that performance monitoring was completed (cache should be cleaned up)
        $monitoringContext = cache()->get("upload_monitoring_{$correlationId}");
        $this->assertNull($monitoringContext, 'Performance monitoring context should be cleaned up after completion');
        
        // Check that performance metrics were updated
        $totalUploads = cache()->get('total_uploads_day', 0);
        $successfulUploads = cache()->get('successful_uploads_day', 0);
        
        $this->assertGreaterThan(0, $totalUploads);
        $this->assertGreaterThan(0, $successfulUploads);
        $this->assertEquals($totalUploads, $successfulUploads);
    }
    
    /**
     * Test performance monitoring during failed file upload.
     */
    public function test_performance_monitoring_during_failed_upload(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        
        // Create a file that will fail validation (too large)
        $file = UploadedFile::fake()->image('large-image.jpg', 2000, 2000)->size(15000); // 15MB (exceeds typical limits)
        
        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Large Image',
            'description' => 'Test large image description',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'file' => $file,
        ]);
        
        // Assert
        $response->assertStatus(422); // Validation error expected
        
        $correlationId = $response->json('correlation_id');
        $this->assertNotEmpty($correlationId);
        
        // Check that performance monitoring was completed even for failed uploads
        $monitoringContext = cache()->get("upload_monitoring_{$correlationId}");
        $this->assertNull($monitoringContext, 'Performance monitoring context should be cleaned up after failure');
        
        // Check that failure metrics were updated
        $totalUploads = cache()->get('total_uploads_day', 0);
        $failedUploads = cache()->get('failed_uploads_day', 0);
        
        $this->assertGreaterThan(0, $totalUploads);
        $this->assertGreaterThan(0, $failedUploads);
    }
    
    /**
     * Test performance monitoring with update operation.
     */
    public function test_performance_monitoring_during_content_update(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        
        // First create a content block
        $initialFile = UploadedFile::fake()->image('initial.jpg', 400, 300)->size(200);
        
        $createResponse = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Initial Image',
            'description' => 'Initial description',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'file' => $initialFile,
        ]);
        
        $createResponse->assertStatus(201);
        $contentId = $createResponse->json('data.id');
        
        // Now update with a new file
        $updateFile = UploadedFile::fake()->image('updated.jpg', 600, 400)->size(300);
        
        // Act
        $response = $this->putJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks/{$contentId}", [
            'type' => 'image',
            'title' => 'Updated Image',
            'description' => 'Updated description',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'file' => $updateFile,
        ]);
        
        // Assert
        $response->assertStatus(200);
        
        $correlationId = $response->json('correlation_id');
        $this->assertNotEmpty($correlationId);
        
        // Check that performance monitoring was completed
        $monitoringContext = cache()->get("upload_monitoring_{$correlationId}");
        $this->assertNull($monitoringContext);
        
        // Check that metrics include both create and update operations
        $totalUploads = cache()->get('total_uploads_day', 0);
        $this->assertGreaterThanOrEqual(2, $totalUploads); // At least create + update
    }
    
    /**
     * Test dashboard data generation with real upload data.
     */
    public function test_dashboard_data_generation_with_real_data(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        $monitor = new UploadPerformanceMonitor();
        
        // Perform multiple uploads to generate data
        $files = [
            UploadedFile::fake()->image('image1.jpg', 400, 300)->size(200),
            UploadedFile::fake()->image('image2.jpg', 600, 400)->size(400),
            UploadedFile::fake()->create('document.pdf', 800, 'application/pdf'),
        ];
        
        foreach ($files as $index => $file) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => $index < 2 ? 'image' : 'pdf',
                'title' => "Test Content {$index}",
                'description' => "Test description {$index}",
                'section' => 'main_content',
                'visibility' => 'student',
                'is_active' => true,
                'file' => $file,
            ]);
            
            $response->assertStatus(201);
        }
        
        // Act
        $dashboardData = $monitor->getDashboardData('day');
        
        // Assert
        $this->assertIsArray($dashboardData);
        $this->assertEquals('day', $dashboardData['period']);
        
        // Check success rates
        $successRates = $dashboardData['success_rates'];
        $this->assertGreaterThan(0, $successRates['total_uploads']);
        $this->assertGreaterThan(0, $successRates['successful_uploads']);
        $this->assertEquals(100.0, $successRates['success_rate_percentage']); // All should succeed
        
        // Check performance metrics
        $performanceMetrics = $dashboardData['performance_metrics'];
        $this->assertArrayHasKey('average_upload_duration', $performanceMetrics);
        $this->assertArrayHasKey('average_memory_usage', $performanceMetrics);
        $this->assertArrayHasKey('average_upload_speed', $performanceMetrics);
        
        // Check system health
        $systemHealth = $dashboardData['system_health'];
        $this->assertArrayHasKey('overall_status', $systemHealth);
        $this->assertArrayHasKey('health_checks', $systemHealth);
        
        // Check that recommendations are provided
        $this->assertArrayHasKey('recommendations', $dashboardData);
    }
    
    /**
     * Test alert generation for slow uploads.
     */
    public function test_alert_generation_for_slow_uploads(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        
        // Mock FileUploadLogger to simulate slow upload
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);
        
        $correlationId = 'test-slow-upload';
        
        // Set up mock expectations
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);
            
        $mockLogger->shouldReceive('startPerformanceMonitoring')
            ->once()
            ->andReturn([
                'correlation_id' => $correlationId,
                'start_time' => microtime(true),
                'start_memory' => memory_get_usage(true),
                'file_size' => 1024 * 1024,
                'file_type' => 'jpg',
            ]);
            
        $mockLogger->shouldReceive('logUploadSuccess')
            ->once();
            
        $mockLogger->shouldReceive('endPerformanceMonitoring')
            ->once()
            ->with($correlationId, true, Mockery::any());
            
        // Simulate slow upload by setting high duration in cache
        Cache::put('avg_duration_day', 45.0); // 45 seconds (above 30s threshold)
        
        $file = UploadedFile::fake()->image('test.jpg', 400, 300)->size(200);
        
        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Image',
            'description' => 'Test description',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'file' => $file,
        ]);
        
        // Assert
        $response->assertStatus(201);
        
        // Check that performance recommendations include slow upload warning
        $monitor = new UploadPerformanceMonitor();
        $recommendations = $monitor->getPerformanceRecommendations('day');
        
        $this->assertNotEmpty($recommendations);
        $slowUploadRecommendation = collect($recommendations)->firstWhere('title', 'Slow Upload Performance');
        $this->assertNotNull($slowUploadRecommendation);
        $this->assertEquals('high', $slowUploadRecommendation['priority']);
    }
    
    /**
     * Test performance report generation.
     */
    public function test_performance_report_generation(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        $monitor = new UploadPerformanceMonitor();
        
        // Set up some test metrics
        Cache::put('total_uploads_day', 50);
        Cache::put('successful_uploads_day', 45);
        Cache::put('avg_duration_day', 15.5);
        Cache::put('avg_memory_day', 30 * 1024 * 1024);
        Cache::put('avg_speed_day', 150 * 1024);
        
        // Act
        $report = $monitor->generatePerformanceReport('day');
        
        // Assert
        $this->assertIsArray($report);
        $this->assertArrayHasKey('report_id', $report);
        $this->assertArrayHasKey('generated_at', $report);
        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('summary', $report);
        
        $summary = $report['summary'];
        $this->assertEquals(50, $summary['total_uploads']);
        $this->assertEquals(90.0, $summary['success_rate']); // 45/50 = 90%
        $this->assertEquals('15.5s', $summary['average_duration']);
        
        $this->assertArrayHasKey('detailed_metrics', $report);
        $this->assertArrayHasKey('resource_usage', $report);
        $this->assertArrayHasKey('failure_analysis', $report);
        $this->assertArrayHasKey('recommendations', $report);
        $this->assertArrayHasKey('trends', $report);
    }
    
    /**
     * Test concurrent upload monitoring.
     */
    public function test_concurrent_upload_monitoring(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        
        // Simulate concurrent uploads by making multiple requests
        $files = [
            UploadedFile::fake()->image('concurrent1.jpg', 400, 300)->size(200),
            UploadedFile::fake()->image('concurrent2.jpg', 400, 300)->size(200),
            UploadedFile::fake()->image('concurrent3.jpg', 400, 300)->size(200),
        ];
        
        $responses = [];
        
        // Act - Make concurrent requests
        foreach ($files as $index => $file) {
            $responses[] = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => "Concurrent Upload {$index}",
                'description' => "Concurrent description {$index}",
                'section' => 'main_content',
                'visibility' => 'student',
                'is_active' => true,
                'file' => $file,
            ]);
        }
        
        // Assert
        foreach ($responses as $response) {
            $response->assertStatus(201);
            $this->assertNotEmpty($response->json('correlation_id'));
        }
        
        // Check that all uploads were tracked
        $totalUploads = cache()->get('total_uploads_day', 0);
        $this->assertGreaterThanOrEqual(3, $totalUploads);
        
        $successfulUploads = cache()->get('successful_uploads_day', 0);
        $this->assertGreaterThanOrEqual(3, $successfulUploads);
    }
    
    /**
     * Test memory usage tracking during large file uploads.
     */
    public function test_memory_usage_tracking_during_large_uploads(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        
        // Create a larger file (within limits but substantial)
        $file = UploadedFile::fake()->image('large-test.jpg', 1200, 800)->size(2000); // 2MB
        
        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Large Test Image',
            'description' => 'Large test image description',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'file' => $file,
        ]);
        
        // Assert
        $response->assertStatus(201);
        
        // Check that memory metrics were updated
        $avgMemory = cache()->get('avg_memory_day', 0);
        $this->assertGreaterThan(0, $avgMemory);
        
        // Get dashboard data to check memory usage reporting
        $monitor = new UploadPerformanceMonitor();
        $dashboardData = $monitor->getDashboardData('day');
        
        $memoryMetric = $dashboardData['performance_metrics']['average_memory_usage'];
        $this->assertGreaterThan(0, $memoryMetric['value']);
        $this->assertNotEmpty($memoryMetric['formatted']);
        $this->assertContains($memoryMetric['status'], ['good', 'warning', 'critical']);
    }
    
    /**
     * Test failure pattern tracking.
     */
    public function test_failure_pattern_tracking(): void
    {
        // Arrange
        $this->actingAs($this->teacher);
        
        // Create files that will trigger different types of failures
        $oversizedFile = UploadedFile::fake()->image('oversized.jpg', 3000, 3000)->size(20000); // Too large
        $invalidFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-executable'); // Invalid type
        
        // Act - Attempt uploads that should fail
        $response1 = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Oversized Image',
            'file' => $oversizedFile,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
        ]);
        
        $response2 = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Invalid File',
            'file' => $invalidFile,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
        ]);
        
        // Assert
        $response1->assertStatus(422);
        $response2->assertStatus(422);
        
        // Check that failure metrics were updated
        $failedUploads = cache()->get('failed_uploads_day', 0);
        $this->assertGreaterThanOrEqual(2, $failedUploads);
        
        // Check failure patterns
        $monitor = new UploadPerformanceMonitor();
        $failurePatterns = $monitor->getCommonFailurePatterns('day');
        
        $this->assertIsArray($failurePatterns);
        $this->assertArrayHasKey('total_failures', $failurePatterns);
        $this->assertArrayHasKey('patterns', $failurePatterns);
        $this->assertGreaterThan(0, $failurePatterns['total_failures']);
    }
}