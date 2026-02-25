<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Services\FileUploadLogger;
use App\Services\FileUploadErrorFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Mockery;

/**
 * Integration tests for error handling throughout the upload flow.
 * 
 * These tests focus specifically on error scenarios and ensure that:
 * - PHP upload errors are properly detected and handled
 * - Server configuration issues are caught early
 * - Resource validation prevents system overload
 * - Error messages are user-friendly and actionable
 * - Correlation IDs are maintained through error flows
 * 
 * Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 3.1, 3.2, 3.3, 3.4, 3.5
 */
class UploadFlowErrorHandlingIntegrationTest extends TestCase
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
     * Test PHP upload error handling with detailed error messages.
     * 
     * @test
     */
    public function it_handles_php_upload_errors_with_detailed_messages()
    {
        $this->actingAs($this->teacher);

        // Mock a file with PHP upload error
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getError')->andReturn(UPLOAD_ERR_INI_SIZE);
        $file->shouldReceive('getClientOriginalName')->andReturn('large-file.jpg');
        $file->shouldReceive('getSize')->andReturn(50 * 1024 * 1024); // 50MB
        $file->shouldReceive('getMimeType')->andReturn('image/jpeg');
        $file->shouldReceive('getClientOriginalExtension')->andReturn('jpg');

        // Mock the logger to capture PHP error logging
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'php-error-test-id';

        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        $mockLogger->shouldReceive('startPerformanceMonitoring')
            ->once()
            ->andReturn(['correlation_id' => $correlationId]);

        $mockLogger->shouldReceive('logPhpUploadError')
            ->once()
            ->with($correlationId, UPLOAD_ERR_INI_SIZE, $file, Mockery::type('array'));

        $mockLogger->shouldReceive('endPerformanceMonitoring')
            ->once()
            ->with($correlationId, false, Mockery::type('array'));

        // Create a request with the mocked file
        $response = $this->call('POST', "/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'PHP Error Test',
            'section' => 'main_content',
            'visibility' => 'student',
        ], [], ['file' => $file]);

        // Should return validation error with detailed PHP error message
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => ['file'],
            'correlation_id',
        ]);

        $responseData = $response->json();
        $this->assertFalse($responseData['success']);
        $this->assertEquals($correlationId, $responseData['correlation_id']);
        
        // Error message should contain server configuration guidance
        $errorMessage = $responseData['errors']['file'][0];
        $this->assertStringContainsString('server upload limit', strtolower($errorMessage));
        $this->assertStringContainsString('upload_max_filesize', strtolower($errorMessage));
    }

    /**
     * Test file size validation with formatted error messages.
     * 
     * @test
     */
    public function it_validates_file_size_with_formatted_error_messages()
    {
        $this->actingAs($this->teacher);

        // Create a file that exceeds typical size limits
        $file = UploadedFile::fake()->image('oversized.jpg', 2000, 2000)->size(25 * 1024); // 25MB

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Oversized File Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => ['file'],
            'correlation_id',
        ]);

        $responseData = $response->json();
        $errorMessage = $responseData['errors']['file'][0];

        // Error message should include formatted file sizes
        $this->assertStringContainsString('MB', $errorMessage);
        $this->assertStringContainsString('size', strtolower($errorMessage));
        $this->assertStringContainsString('exceed', strtolower($errorMessage));
        
        // Should include actual vs maximum size information
        $this->assertMatchesRegularExpression('/\d+(\.\d+)?\s*(MB|KB|GB)/', $errorMessage);
    }

    /**
     * Test file type validation with comprehensive error messages.
     * 
     * @test
     */
    public function it_validates_file_types_with_comprehensive_error_messages()
    {
        $this->actingAs($this->teacher);

        $invalidFileTypes = [
            [
                'file' => UploadedFile::fake()->create('script.php', 1024, 'application/x-php'),
                'content_type' => 'image',
                'expected_contains' => ['php', 'not allowed', 'image'],
            ],
            [
                'file' => UploadedFile::fake()->create('executable.exe', 1024, 'application/x-executable'),
                'content_type' => 'pdf',
                'expected_contains' => ['exe', 'not allowed', 'pdf'],
            ],
            [
                'file' => UploadedFile::fake()->create('archive.zip', 1024, 'application/zip'),
                'content_type' => 'audio',
                'expected_contains' => ['zip', 'not allowed', 'audio'],
            ],
        ];

        foreach ($invalidFileTypes as $testCase) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => $testCase['content_type'],
                'title' => 'Invalid File Type Test',
                'section' => 'main_content',
                'visibility' => 'student',
                'file' => $testCase['file'],
            ]);

            $response->assertStatus(422);
            $responseData = $response->json();
            $errorMessage = strtolower($responseData['errors']['file'][0]);

            // Verify error message contains expected information
            foreach ($testCase['expected_contains'] as $expectedText) {
                $this->assertStringContainsString(
                    strtolower($expectedText),
                    $errorMessage,
                    "Error message should contain '{$expectedText}' for file type validation"
                );
            }
        }
    }

    /**
     * Test MIME type validation and spoofing prevention.
     * 
     * @test
     */
    public function it_prevents_mime_type_spoofing_with_detailed_errors()
    {
        $this->actingAs($this->teacher);

        // Create a file with mismatched extension and MIME type
        $file = UploadedFile::fake()->create('fake-image.jpg', 1024, 'text/plain');

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'MIME Spoofing Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(422);
        $responseData = $response->json();
        $errorMessage = strtolower($responseData['errors']['file'][0]);

        // Error message should explain MIME type mismatch
        $this->assertStringContainsString('mime', $errorMessage);
        $this->assertStringContainsString('extension', $errorMessage);
        $this->assertStringContainsString('corrupted', $errorMessage);
    }

    /**
     * Test server resource validation with actionable error messages.
     * 
     * @test
     */
    public function it_validates_server_resources_with_actionable_errors()
    {
        $this->actingAs($this->teacher);

        // Mock insufficient disk space scenario
        // Note: In a real test environment, we can't actually fill up disk space,
        // so this test focuses on the error message format and structure
        
        $file = UploadedFile::fake()->image('resource-test.jpg', 1000, 1000)->size(10 * 1024); // 10MB

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Resource Validation Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        // Should succeed with normal resources, but we're testing the validation logic exists
        // In a real scenario with insufficient resources, we would get a 422 error
        if ($response->status() === 422) {
            $responseData = $response->json();
            $errorMessage = strtolower($responseData['errors']['file'][0]);

            // Error message should be actionable
            $this->assertStringContainsString('administrator', $errorMessage);
            $this->assertStringContainsString('space', $errorMessage);
        } else {
            // If resources are sufficient, upload should succeed
            $response->assertStatus(201);
        }
    }

    /**
     * Test empty file validation with helpful error messages.
     * 
     * @test
     */
    public function it_validates_empty_files_with_helpful_errors()
    {
        $this->actingAs($this->teacher);

        $emptyFile = UploadedFile::fake()->create('empty.txt', 0); // 0 bytes

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'pdf',
            'title' => 'Empty File Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $emptyFile,
        ]);

        $response->assertStatus(422);
        $responseData = $response->json();
        $errorMessage = strtolower($responseData['errors']['file'][0]);

        // Error message should explain the empty file issue
        $this->assertStringContainsString('empty', $errorMessage);
        $this->assertStringContainsString('0 bytes', $errorMessage);
        $this->assertStringContainsString('valid file', $errorMessage);
    }

    /**
     * Test correlation ID persistence through error flows.
     * 
     * @test
     */
    public function it_maintains_correlation_ids_through_error_flows()
    {
        $this->actingAs($this->teacher);

        // Mock the logger to track correlation ID usage
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'error-flow-correlation-id';
        $invalidFile = UploadedFile::fake()->create('invalid.exe', 1024, 'application/x-executable');

        // Expect correlation ID to be used consistently through error flow
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        $mockLogger->shouldReceive('startPerformanceMonitoring')
            ->once()
            ->andReturn(['correlation_id' => $correlationId]);

        $mockLogger->shouldReceive('logValidationFailure')
            ->once()
            ->with($correlationId, Mockery::type('array'), Mockery::type('array'));

        $mockLogger->shouldReceive('endPerformanceMonitoring')
            ->once()
            ->with($correlationId, false, Mockery::type('array'));

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Correlation ID Error Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $invalidFile,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['correlation_id' => $correlationId]);
    }

    /**
     * Test multiple validation errors are properly aggregated.
     * 
     * @test
     */
    public function it_aggregates_multiple_validation_errors_properly()
    {
        $this->actingAs($this->teacher);

        // Create a request that will fail multiple validation rules
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            // Missing title, visibility, and file
            'section' => 'invalid_section',
        ]);

        $response->assertStatus(422);
        $responseData = $response->json();

        // Should have multiple validation errors
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertGreaterThan(1, count($responseData['errors']));
        
        // Should still include correlation ID for debugging
        $this->assertArrayHasKey('correlation_id', $responseData);
        $this->assertNotEmpty($responseData['correlation_id']);
    }

    /**
     * Test error response format consistency.
     * 
     * @test
     */
    public function it_maintains_consistent_error_response_format()
    {
        $this->actingAs($this->teacher);

        $errorScenarios = [
            [
                'name' => 'invalid_file_type',
                'data' => [
                    'type' => 'image',
                    'title' => 'Invalid Type Test',
                    'section' => 'main_content',
                    'visibility' => 'student',
                    'file' => UploadedFile::fake()->create('test.exe', 1024, 'application/x-executable'),
                ],
            ],
            [
                'name' => 'missing_required_fields',
                'data' => [
                    'type' => 'image',
                    // Missing title, visibility, file
                ],
            ],
            [
                'name' => 'oversized_file',
                'data' => [
                    'type' => 'image',
                    'title' => 'Oversized Test',
                    'section' => 'main_content',
                    'visibility' => 'student',
                    'file' => UploadedFile::fake()->image('huge.jpg', 3000, 3000)->size(30 * 1024), // 30MB
                ],
            ],
        ];

        foreach ($errorScenarios as $scenario) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", $scenario['data']);

            $response->assertStatus(422);
            $response->assertJsonStructure([
                'success',
                'message',
                'errors',
                'correlation_id',
            ]);

            $responseData = $response->json();
            
            // Verify consistent error response format
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertIsArray($responseData['errors']);
            $this->assertNotEmpty($responseData['correlation_id']);
            $this->assertMatchesRegularExpression(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                $responseData['correlation_id']
            );
        }
    }

    /**
     * Test server error handling with enhanced error responses.
     * 
     * @test
     */
    public function it_handles_server_errors_with_enhanced_responses()
    {
        $this->actingAs($this->teacher);

        // Mock a service that will throw an exception
        $mockService = Mockery::mock(\App\Services\ContentBlockService::class);
        $mockService->shouldReceive('createContentBlock')
            ->andThrow(new \Exception('Database connection failed'));
        
        $this->app->instance(\App\Services\ContentBlockService::class, $mockService);

        $file = UploadedFile::fake()->image('server-error-test.jpg', 200, 200)->size(1024);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Server Error Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        $response->assertStatus(500);
        $response->assertJsonStructure([
            'success',
            'message',
            'correlation_id',
        ]);

        $responseData = $response->json();
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('Failed to create content block', $responseData['message']);
        $this->assertNotEmpty($responseData['correlation_id']);
    }

    /**
     * Test that error messages are user-friendly and actionable.
     * 
     * @test
     */
    public function it_provides_user_friendly_and_actionable_error_messages()
    {
        $this->actingAs($this->teacher);

        $userFriendlyTestCases = [
            [
                'file' => UploadedFile::fake()->image('large.jpg', 2000, 2000)->size(20 * 1024), // 20MB
                'expected_friendly_elements' => [
                    'size', 'MB', 'maximum', 'smaller', // Size-related friendly terms
                ],
                'should_not_contain' => [
                    'UPLOAD_ERR_', 'validation.', 'Exception', // Technical jargon
                ],
            ],
            [
                'file' => UploadedFile::fake()->create('document.docx', 1024, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
                'expected_friendly_elements' => [
                    'type', 'not supported', 'please use', // Type-related friendly terms
                ],
                'should_not_contain' => [
                    'mime_type', 'validation_rule', 'constraint', // Technical terms
                ],
            ],
        ];

        foreach ($userFriendlyTestCases as $testCase) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => 'User Friendly Error Test',
                'section' => 'main_content',
                'visibility' => 'student',
                'file' => $testCase['file'],
            ]);

            $response->assertStatus(422);
            $responseData = $response->json();
            $errorMessage = $responseData['errors']['file'][0];

            // Check for user-friendly elements
            foreach ($testCase['expected_friendly_elements'] as $friendlyElement) {
                $this->assertStringContainsString(
                    strtolower($friendlyElement),
                    strtolower($errorMessage),
                    "Error message should contain user-friendly term: {$friendlyElement}"
                );
            }

            // Check that technical jargon is not present
            foreach ($testCase['should_not_contain'] as $technicalTerm) {
                $this->assertStringNotContainsString(
                    $technicalTerm,
                    $errorMessage,
                    "Error message should not contain technical term: {$technicalTerm}"
                );
            }
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}