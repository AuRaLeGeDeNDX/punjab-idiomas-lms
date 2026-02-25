<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;
use App\Services\FileUploadLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Mockery;

/**
 * Integration tests for logging and correlation ID tracking throughout the upload flow.
 * 
 * These tests verify that:
 * - Comprehensive logging occurs at all stages of upload
 * - Correlation IDs are consistently tracked across all operations
 * - Performance monitoring captures relevant metrics
 * - Log entries contain sufficient context for debugging
 * - Upload success and failure rates are tracked
 * 
 * Requirements: 4.1, 4.2, 4.4, 4.5
 */
class UploadFlowLoggingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private Course $course;
    private Module $module;
    private Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create test data
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);

        // Set up storage for testing
        Storage::fake('public');
    }

    /**
     * Test comprehensive logging throughout successful upload flow.
     * 
     * @test
     */
    public function it_logs_comprehensive_information_throughout_successful_upload()
    {
        $this->actingAs($this->teacher);

        // Spy on the Log facade to capture all log entries
        Log::spy();

        $file = UploadedFile::fake()->image('logging-test.jpg', 400, 300)->size(1024);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Comprehensive Logging Test',
            'description' => 'Testing complete logging flow',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
            'alt_text' => 'Test image for logging',
        ]);

        $response->assertStatus(201);

        // Verify upload attempt logging
        Log::shouldHaveReceived('info')
            ->with('File upload attempt initiated', Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['user_id']) &&
                       isset($data['file_info']) &&
                       isset($data['server_config']) &&
                       isset($data['resource_availability']);
            }))
            ->once();

        // Verify validation logging
        Log::shouldHaveReceived('info')
            ->with('ContentBlock store: Starting validation', Mockery::type('array'))
            ->once();

        Log::shouldHaveReceived('info')
            ->with('ContentBlock store: Validation passed', Mockery::type('array'))
            ->once();

        // Verify content creation logging
        Log::shouldHaveReceived('info')
            ->with('ContentBlock store: Creating content block', Mockery::type('array'))
            ->once();

        // Verify success logging
        Log::shouldHaveReceived('info')
            ->with('File upload completed successfully', Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['content_id']) &&
                       isset($data['file_info']) &&
                       isset($data['upload_duration']);
            }))
            ->once();
    }

    /**
     * Test correlation ID consistency across all log entries.
     * 
     * @test
     */
    public function it_maintains_correlation_id_consistency_across_all_log_entries()
    {
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger to control correlation ID
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-consistency-12345';
        $file = UploadedFile::fake()->image('correlation-test.jpg', 200, 200)->size(512);

        // Set up expectations for consistent correlation ID usage
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->with(
                Mockery::type('Illuminate\Http\Request'),
                Mockery::type('Illuminate\Http\UploadedFile'),
                Mockery::on(function ($context) {
                    return isset($context['subpage_id']) && isset($context['action']);
                })
            )
            ->andReturn($correlationId);

        $mockLogger->shouldReceive('startPerformanceMonitoring')
            ->once()
            ->with($correlationId, Mockery::type('Illuminate\Http\UploadedFile'))
            ->andReturn(['correlation_id' => $correlationId, 'start_time' => microtime(true)]);

        $mockLogger->shouldReceive('logUploadSuccess')
            ->once()
            ->with($correlationId, Mockery::type('App\Models\Content'), Mockery::type('array'));

        $mockLogger->shouldReceive('endPerformanceMonitoring')
            ->once()
            ->with($correlationId, true, Mockery::type('array'));

        // Spy on Log to verify correlation ID in all entries
        Log::spy();

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Correlation ID Consistency Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['correlation_id' => $correlationId]);

        // Verify correlation ID appears in controller log entries
        Log::shouldHaveReceived('info')
            ->with('ContentBlock store: Starting validation', Mockery::on(function ($data) use ($correlationId) {
                return isset($data['correlation_id']) && $data['correlation_id'] === $correlationId;
            }))
            ->once();

        Log::shouldHaveReceived('info')
            ->with('ContentBlock store: Validation passed', Mockery::on(function ($data) use ($correlationId) {
                return isset($data['correlation_id']) && $data['correlation_id'] === $correlationId;
            }))
            ->once();
    }

    /**
     * Test detailed validation failure logging.
     * 
     * @test
     */
    public function it_logs_detailed_validation_failures_with_context()
    {
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger to capture detailed validation logging
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'validation-failure-test-id';
        $invalidFile = UploadedFile::fake()->create('invalid.exe', 1024, 'application/x-executable');

        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        $mockLogger->shouldReceive('startPerformanceMonitoring')
            ->once()
            ->andReturn(['correlation_id' => $correlationId]);

        // Expect detailed validation failure logging
        $mockLogger->shouldReceive('logValidationFailure')
            ->once()
            ->with(
                $correlationId,
                Mockery::on(function ($errors) {
                    return isset($errors['file']) && is_array($errors['file']);
                }),
                Mockery::on(function ($context) {
                    return isset($context['action']) &&
                           isset($context['subpage_id']) &&
                           isset($context['validation_rules_failed']) &&
                           $context['action'] === 'store';
                })
            );

        $mockLogger->shouldReceive('endPerformanceMonitoring')
            ->once()
            ->with($correlationId, false, Mockery::type('array'));

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Validation Failure Logging Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $invalidFile,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['correlation_id' => $correlationId]);
    }

    /**
     * Test performance monitoring throughout upload flow.
     * 
     * @test
     */
    public function it_monitors_performance_throughout_upload_flow()
    {
        $this->actingAs($this->teacher);

        // Use real FileUploadLogger to test performance monitoring
        $logger = new FileUploadLogger();
        $this->app->instance(FileUploadLogger::class, $logger);

        $file = UploadedFile::fake()->image('performance-test.jpg', 600, 400)->size(2048);

        // Spy on Log to capture performance monitoring
        Log::spy();

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Performance Monitoring Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(201);

        // Verify performance monitoring was started
        Log::shouldHaveReceived('info')
            ->with('Upload performance monitoring started', Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['monitoring_context']) &&
                       isset($data['monitoring_context']['start_time']) &&
                       isset($data['monitoring_context']['file_size']);
            }))
            ->once();

        // Verify performance metrics were logged
        Log::shouldHaveReceived('info')
            ->with('File upload performance metrics', Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['performance_metrics']) &&
                       isset($data['performance_metrics']['duration_seconds']) &&
                       isset($data['performance_metrics']['memory_used']);
            }))
            ->once();
    }

    /**
     * Test server configuration logging during upload attempts.
     * 
     * @test
     */
    public function it_logs_server_configuration_during_upload_attempts()
    {
        $this->actingAs($this->teacher);

        // Use real FileUploadLogger to capture server configuration
        $logger = new FileUploadLogger();
        $this->app->instance(FileUploadLogger::class, $logger);

        Log::spy();

        $file = UploadedFile::fake()->image('config-logging-test.jpg', 300, 300)->size(1024);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Server Config Logging Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(201);

        // Verify server configuration was logged
        Log::shouldHaveReceived('info')
            ->with('File upload attempt initiated', Mockery::on(function ($data) {
                return isset($data['server_config']) &&
                       isset($data['server_config']['upload_max_filesize']) &&
                       isset($data['server_config']['post_max_size']) &&
                       isset($data['server_config']['memory_limit']) &&
                       isset($data['resource_availability']) &&
                       isset($data['resource_availability']['free_space']);
            }))
            ->once();
    }

    /**
     * Test file information logging with comprehensive details.
     * 
     * @test
     */
    public function it_logs_comprehensive_file_information()
    {
        $this->actingAs($this->teacher);

        // Use real FileUploadLogger to capture file information
        $logger = new FileUploadLogger();
        $this->app->instance(FileUploadLogger::class, $logger);

        Log::spy();

        $file = UploadedFile::fake()->image('file-info-test.png', 500, 400)->size(1536);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'File Information Logging Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(201);

        // Verify comprehensive file information was logged
        Log::shouldHaveReceived('info')
            ->with('File upload attempt initiated', Mockery::on(function ($data) {
                return isset($data['file_info']) &&
                       isset($data['file_info']['original_name']) &&
                       isset($data['file_info']['size']) &&
                       isset($data['file_info']['size_formatted']) &&
                       isset($data['file_info']['mime_type']) &&
                       isset($data['file_info']['extension']) &&
                       isset($data['file_info']['php_error_code']) &&
                       isset($data['file_info']['is_valid']);
            }))
            ->once();
    }

    /**
     * Test user context logging throughout upload flow.
     * 
     * @test
     */
    public function it_logs_user_context_throughout_upload_flow()
    {
        $this->actingAs($this->teacher);

        // Use real FileUploadLogger to capture user context
        $logger = new FileUploadLogger();
        $this->app->instance(FileUploadLogger::class, $logger);

        Log::spy();

        $file = UploadedFile::fake()->image('user-context-test.jpg', 300, 300)->size(1024);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'User Context Logging Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(201);

        // Verify user context was logged
        Log::shouldHaveReceived('info')
            ->with('File upload attempt initiated', Mockery::on(function ($data) {
                return isset($data['user_id']) &&
                       isset($data['user_email']) &&
                       isset($data['user_roles']) &&
                       $data['user_id'] === $this->teacher->id &&
                       $data['user_email'] === $this->teacher->email &&
                       in_array('Teacher', $data['user_roles']);
            }))
            ->once();
    }

    /**
     * Test request information logging with comprehensive details.
     * 
     * @test
     */
    public function it_logs_comprehensive_request_information()
    {
        $this->actingAs($this->teacher);

        // Use real FileUploadLogger to capture request information
        $logger = new FileUploadLogger();
        $this->app->instance(FileUploadLogger::class, $logger);

        Log::spy();

        $file = UploadedFile::fake()->image('request-info-test.jpg', 400, 300)->size(1024);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Request Information Logging Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(201);

        // Verify request information was logged
        Log::shouldHaveReceived('info')
            ->with('File upload attempt initiated', Mockery::on(function ($data) {
                return isset($data['request_info']) &&
                       isset($data['request_info']['method']) &&
                       isset($data['request_info']['url']) &&
                       isset($data['request_info']['user_agent']) &&
                       isset($data['request_info']['ip_address']) &&
                       isset($data['request_info']['content_type']) &&
                       isset($data['request_info']['has_files']) &&
                       $data['request_info']['method'] === 'POST' &&
                       $data['request_info']['has_files'] === true;
            }))
            ->once();
    }

    /**
     * Test error logging with detailed context and correlation tracking.
     * 
     * @test
     */
    public function it_logs_errors_with_detailed_context_and_correlation_tracking()
    {
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger to capture error logging
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'error-logging-test-id';
        $invalidFile = UploadedFile::fake()->create('error-test.txt', 1024, 'text/plain');

        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        $mockLogger->shouldReceive('startPerformanceMonitoring')
            ->once()
            ->andReturn(['correlation_id' => $correlationId]);

        // Expect detailed error logging
        $mockLogger->shouldReceive('logValidationFailure')
            ->once()
            ->with(
                $correlationId,
                Mockery::type('array'),
                Mockery::on(function ($context) {
                    return isset($context['action']) &&
                           isset($context['subpage_id']) &&
                           isset($context['validation_rules_failed']);
                })
            );

        $mockLogger->shouldReceive('endPerformanceMonitoring')
            ->once()
            ->with($correlationId, false, Mockery::on(function ($metrics) {
                return isset($metrics['failure_reason']) &&
                       isset($metrics['failure_stage']) &&
                       $metrics['failure_reason'] === 'validation_failed';
            }));

        // Spy on Log to verify controller error logging
        Log::spy();

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Error Logging Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $invalidFile,
        ]);

        $response->assertStatus(422);

        // Verify controller logged validation failure with correlation ID
        Log::shouldHaveReceived('warning')
            ->with('ContentBlock validation: Rules validation failed', Mockery::on(function ($data) use ($correlationId) {
                return isset($data['correlation_id']) &&
                       isset($data['validation_errors']) &&
                       $data['correlation_id'] === $correlationId;
            }))
            ->once();
    }

    /**
     * Test upload success rate tracking and metrics.
     * 
     * @test
     */
    public function it_tracks_upload_success_rates_and_metrics()
    {
        $this->actingAs($this->teacher);

        // Use real FileUploadLogger to test metrics tracking
        $logger = new FileUploadLogger();
        $this->app->instance(FileUploadLogger::class, $logger);

        // Clear any existing metrics
        Cache::flush();

        // Perform successful upload
        $successFile = UploadedFile::fake()->image('success-metrics.jpg', 300, 300)->size(1024);

        $successResponse = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Success Metrics Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $successFile,
        ]);

        $successResponse->assertStatus(201);

        // Perform failed upload
        $failFile = UploadedFile::fake()->create('fail-metrics.exe', 1024, 'application/x-executable');

        $failResponse = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Fail Metrics Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $failFile,
        ]);

        $failResponse->assertStatus(422);

        // Test success rate retrieval
        $successRates = $logger->getUploadSuccessRates('day');

        $this->assertIsArray($successRates);
        $this->assertArrayHasKey('period', $successRates);
        $this->assertArrayHasKey('total_uploads', $successRates);
        $this->assertArrayHasKey('successful_uploads', $successRates);
        $this->assertArrayHasKey('failed_uploads', $successRates);
        $this->assertArrayHasKey('success_rate_percentage', $successRates);
        $this->assertEquals('day', $successRates['period']);
    }

    /**
     * Test that sensitive information is not logged.
     * 
     * @test
     */
    public function it_excludes_sensitive_information_from_logs()
    {
        $this->actingAs($this->teacher);

        // Use real FileUploadLogger
        $logger = new FileUploadLogger();
        $this->app->instance(FileUploadLogger::class, $logger);

        Log::spy();

        $file = UploadedFile::fake()->image('sensitive-test.jpg', 300, 300)->size(1024);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Sensitive Information Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
            '_token' => 'sensitive-csrf-token',
        ]);

        $response->assertStatus(201);

        // Verify sensitive information is excluded from logs
        Log::shouldHaveReceived('info')
            ->with('File upload attempt initiated', Mockery::on(function ($data) {
                // Should not contain sensitive form data like _token
                return isset($data['request_info']['form_data']) &&
                       !isset($data['request_info']['form_data']['_token']) &&
                       !isset($data['request_info']['form_data']['file']);
            }))
            ->once();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}