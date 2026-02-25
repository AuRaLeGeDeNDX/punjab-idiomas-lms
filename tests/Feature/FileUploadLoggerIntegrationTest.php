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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;

/**
 * Integration tests for FileUploadLogger with ContentBlockController.
 * 
 * Tests the complete file upload logging flow including upload attempts,
 * validation failures, and success tracking in real controller scenarios.
 * 
 * Requirements: 4.1, 4.2, 4.4, 4.5
 */
class FileUploadLoggerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private Course $course;
    private Module $module;
    private Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->teacher = User::factory()->create();
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);

        // Set up storage
        Storage::fake('public');
    }

    /** @test */
    public function it_logs_successful_file_upload_attempt()
    {
        // Arrange
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger to verify method calls
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-id';
        $file = UploadedFile::fake()->image('test.jpg', 100, 100)->size(500);

        // Expect logUploadAttempt to be called
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->with(
                Mockery::type('Illuminate\Http\Request'),
                Mockery::type('Illuminate\Http\UploadedFile'),
                Mockery::on(function ($context) {
                    return isset($context['subpage_id']) &&
                           isset($context['course_id']) &&
                           isset($context['teacher_id']) &&
                           $context['action'] === 'store';
                })
            )
            ->andReturn($correlationId);

        // Expect logUploadSuccess to be called
        $mockLogger->shouldReceive('logUploadSuccess')
            ->once()
            ->with(
                $correlationId,
                Mockery::type('App\Models\Content'),
                Mockery::on(function ($context) {
                    return isset($context['action']) &&
                           isset($context['subpage_id']) &&
                           $context['action'] === 'store';
                })
            );

        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            'file' => $file,
        ]);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'correlation_id'
        ]);
    }

    /** @test */
    public function it_logs_validation_failure_for_invalid_file_type()
    {
        // Arrange
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-id';
        $file = UploadedFile::fake()->create('test.exe', 500); // Invalid file type

        // Expect logUploadAttempt to be called
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        // Expect logValidationFailure to be called
        $mockLogger->shouldReceive('logValidationFailure')
            ->once()
            ->with(
                $correlationId,
                Mockery::type('array'),
                Mockery::on(function ($context) {
                    return isset($context['action']) &&
                           isset($context['subpage_id']) &&
                           isset($context['validation_rules_failed']) &&
                           $context['action'] === 'store';
                })
            );

        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            'file' => $file,
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
            'correlation_id'
        ]);
    }

    /** @test */
    public function it_logs_validation_failure_for_oversized_file()
    {
        // Arrange
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-id';
        // Create a file larger than typical limits (50MB)
        $file = UploadedFile::fake()->image('large.jpg')->size(50 * 1024);

        // Expect logUploadAttempt to be called
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        // Expect logValidationFailure to be called
        $mockLogger->shouldReceive('logValidationFailure')
            ->once()
            ->with(
                $correlationId,
                Mockery::on(function ($errors) {
                    // Check that file size error is present
                    return isset($errors['file']) && 
                           is_array($errors['file']) &&
                           count($errors['file']) > 0;
                }),
                Mockery::type('array')
            );

        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Large Image',
            'visibility' => 'student',
            'file' => $file,
        ]);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function it_logs_successful_file_update_attempt()
    {
        // Arrange
        $this->actingAs($this->teacher);

        // Create existing content
        $existingContent = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Original Image',
            'visibility' => 'student',
        ]);

        // Mock the FileUploadLogger
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-id';
        $file = UploadedFile::fake()->image('updated.jpg', 100, 100)->size(500);

        // Expect logUploadAttempt to be called
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->with(
                Mockery::type('Illuminate\Http\Request'),
                Mockery::type('Illuminate\Http\UploadedFile'),
                Mockery::on(function ($context) use ($existingContent) {
                    return isset($context['content_id']) &&
                           isset($context['subpage_id']) &&
                           isset($context['course_id']) &&
                           $context['action'] === 'update' &&
                           $context['content_id'] === $existingContent->id;
                })
            )
            ->andReturn($correlationId);

        // Expect logUploadSuccess to be called
        $mockLogger->shouldReceive('logUploadSuccess')
            ->once()
            ->with(
                $correlationId,
                Mockery::type('App\Models\Content'),
                Mockery::on(function ($context) use ($existingContent) {
                    return isset($context['action']) &&
                           isset($context['original_content_id']) &&
                           $context['action'] === 'update' &&
                           $context['original_content_id'] === $existingContent->id;
                })
            );

        // Act
        $response = $this->putJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks/{$existingContent->id}", [
            'type' => 'image',
            'title' => 'Updated Image',
            'visibility' => 'student',
            'file' => $file,
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'correlation_id'
        ]);
    }

    /** @test */
    public function it_logs_upload_attempt_without_file_for_text_content()
    {
        // Arrange
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-id';

        // Expect logUploadAttempt to be called with null file
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->with(
                Mockery::type('Illuminate\Http\Request'),
                null, // No file for text content
                Mockery::type('array')
            )
            ->andReturn($correlationId);

        // Expect logUploadSuccess to be called
        $mockLogger->shouldReceive('logUploadSuccess')
            ->once()
            ->with(
                $correlationId,
                Mockery::type('App\Models\Content'),
                Mockery::type('array')
            );

        // Act
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'text',
            'title' => 'Test Text',
            'content' => 'This is test content',
            'visibility' => 'student',
        ]);

        // Assert
        $response->assertStatus(201);
    }

    /** @test */
    public function it_includes_correlation_id_in_error_responses()
    {
        // Arrange
        $this->actingAs($this->teacher);

        // Mock the FileUploadLogger
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-id';

        // Expect logUploadAttempt to be called
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        // Expect logValidationFailure to be called
        $mockLogger->shouldReceive('logValidationFailure')
            ->once();

        // Act - Send invalid request (missing required fields)
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            // Missing required fields like visibility
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
            'correlation_id'
        ]);
        
        // Verify correlation ID is included in response
        $responseData = $response->json();
        $this->assertEquals($correlationId, $responseData['correlation_id']);
    }

    /** @test */
    public function it_logs_performance_metrics_for_large_files()
    {
        // This test would require more complex setup to actually trigger performance logging
        // For now, we'll test that the logger can handle performance metrics
        
        // Arrange
        $logger = new FileUploadLogger();
        
        // Mock Log facade
        Log::shouldReceive('info')->once()->with(
            'File upload performance metrics',
            Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['performance_metrics']) &&
                       isset($data['server_load']) &&
                       isset($data['resource_usage']);
            })
        );

        $performanceMetrics = [
            'upload_duration' => 3.5,
            'file_size' => 5 * 1024 * 1024, // 5MB
            'processing_time' => 2.1,
            'memory_peak' => 128 * 1024 * 1024, // 128MB
        ];

        // Act
        $logger->logUploadPerformance('test-correlation-id', $performanceMetrics);

        // Assert - Mockery will verify the expectations
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}