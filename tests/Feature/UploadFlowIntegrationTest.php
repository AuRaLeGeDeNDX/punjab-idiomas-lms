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
use Mockery;

/**
 * Integration tests for complete file upload flow from client to storage.
 * 
 * These tests validate the entire upload workflow including:
 * - Complete upload process from client to storage
 * - Error handling and response formatting
 * - Logging and correlation ID tracking
 * - Client-side validation integration
 * 
 * Requirements: All (comprehensive integration testing)
 */
class UploadFlowIntegrationTest extends TestCase
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
     * Test complete successful upload flow from client to storage.
     */
    public function test_it_completes_full_upload_flow_successfully()
    {
        $this->actingAs($this->teacher);

        // Create a valid test file (using create instead of image to avoid GD dependency)
        $file = UploadedFile::fake()->create('test-image.jpg', 1024, 'image/jpeg'); // 1MB

        // Make the upload request
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Test Image Upload',
            'description' => 'Integration test for complete upload flow',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'file' => $file,
            'alt_text' => 'Test image for integration testing',
        ]);

        // Assert successful response
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'type',
                'title',
                'description',
                'file_path',
                'file_size',
                'mime_type',
                'original_filename',
                'created_at',
                'updated_at',
            ],
            'correlation_id',
        ]);

        $responseData = $response->json();
        
        // Verify response data
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Content block created successfully.', $responseData['message']);
        $this->assertNotEmpty($responseData['correlation_id']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $responseData['correlation_id']
        );

        // Verify content was created in database
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Test Image Upload',
            'description' => 'Integration test for complete upload flow',
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true,
            'alt_text' => 'Test image for integration testing',
        ]);

        // Verify file was stored
        $content = Content::where('subpage_id', $this->subpage->id)->first();
        $this->assertNotNull($content);
        $this->assertNotNull($content->file_path);
        $this->assertNotNull($content->file_size);
        $this->assertNotNull($content->mime_type);
        $this->assertNotNull($content->original_filename);
        
        // Verify file exists in storage
        Storage::disk('public')->assertExists($content->file_path);
        
        // Verify file properties
        $this->assertEquals('image/jpeg', $content->mime_type);
        $this->assertEquals('test-image.jpg', $content->original_filename);
        $this->assertGreaterThan(0, $content->file_size);
    }

    /**
     * Test complete upload flow with different file types.
     */
    public function test_it_handles_different_file_types_in_complete_flow()
    {
        $this->actingAs($this->teacher);

        $testCases = [
            [
                'type' => 'image',
                'file' => UploadedFile::fake()->create('test.jpg', 512, 'image/jpeg'),
                'expected_mime' => 'image/jpeg',
                'additional_fields' => ['alt_text' => 'Test JPEG image'],
            ],
            [
                'type' => 'pdf',
                'file' => UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf'),
                'expected_mime' => 'application/pdf',
                'additional_fields' => [],
            ],
            [
                'type' => 'audio',
                'file' => UploadedFile::fake()->create('audio.mp3', 2048, 'audio/mpeg'),
                'expected_mime' => 'audio/mpeg',
                'additional_fields' => [],
            ],
            [
                'type' => 'video',
                'file' => UploadedFile::fake()->create('video.mp4', 5120, 'video/mp4'),
                'expected_mime' => 'video/mp4',
                'additional_fields' => [],
            ],
        ];

        foreach ($testCases as $index => $testCase) {
            $requestData = array_merge([
                'type' => $testCase['type'],
                'title' => "Test {$testCase['type']} upload {$index}",
                'section' => 'main_content',
                'visibility' => 'student',
                'is_active' => true,
                'file' => $testCase['file'],
            ], $testCase['additional_fields']);

            $response = $this->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id,
            ]), $requestData);

            // Assert successful response for each file type
            $response->assertStatus(201);
            $response->assertJson(['success' => true]);

            // Verify content was created with correct properties
            $content = Content::where('title', "Test {$testCase['type']} upload {$index}")->first();
            $this->assertNotNull($content);
            $this->assertEquals($testCase['type'], $content->type);
            $this->assertEquals($testCase['expected_mime'], $content->mime_type);
            
            // Verify file exists in storage
            Storage::disk('public')->assertExists($content->file_path);
        }
    }

    /**
     * Test error handling throughout the complete upload flow.
     */
    public function test_it_handles_errors_throughout_upload_flow()
    {
        $this->actingAs($this->teacher);

        $errorTestCases = [
            [
                'name' => 'oversized_file',
                'file' => UploadedFile::fake()->create('huge.jpg', 50 * 1024 * 1024, 'image/jpeg'), // 50MB
                'expected_status' => 422,
                'expected_error_field' => 'file',
                'expected_error_contains' => 'size',
            ],
            [
                'name' => 'invalid_extension',
                'file' => UploadedFile::fake()->create('malware.exe', 1024, 'application/x-executable'),
                'expected_status' => 422,
                'expected_error_field' => 'file',
                'expected_error_contains' => 'not allowed',
            ],
            [
                'name' => 'empty_file',
                'file' => UploadedFile::fake()->create('empty.txt', 0),
                'expected_status' => 422,
                'expected_error_field' => 'file',
                'expected_error_contains' => 'empty',
            ],
            [
                'name' => 'wrong_mime_type',
                'file' => UploadedFile::fake()->create('fake-image.jpg', 1024, 'text/plain'),
                'expected_status' => 422,
                'expected_error_field' => 'file',
                'expected_error_contains' => 'type',
            ],
        ];

        foreach ($errorTestCases as $testCase) {
            $response = $this->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id,
            ]), [
                'type' => 'image',
                'title' => "Test error case: {$testCase['name']}",
                'section' => 'main_content',
                'visibility' => 'student',
                'file' => $testCase['file'],
            ]);

            // Assert error response
            $response->assertStatus($testCase['expected_status']);
            $response->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    $testCase['expected_error_field']
                ],
                'correlation_id',
            ]);

            $responseData = $response->json();
            
            // Verify error response structure
            $this->assertFalse($responseData['success']);
            $this->assertNotEmpty($responseData['correlation_id']);
            $this->assertArrayHasKey($testCase['expected_error_field'], $responseData['errors']);
            
            // Verify error message contains expected content
            $errorMessage = $responseData['errors'][$testCase['expected_error_field']][0];
            $this->assertStringContainsString(
                $testCase['expected_error_contains'], 
                strtolower($errorMessage),
                "Error case '{$testCase['name']}' should contain '{$testCase['expected_error_contains']}' in error message"
            );

            // Verify no content was created for failed uploads
            $this->assertDatabaseMissing('contents', [
                'title' => "Test error case: {$testCase['name']}",
            ]);
        }
    }

    /**
     * Test correlation ID tracking throughout the upload flow.
     */
    public function test_it_tracks_correlation_ids_throughout_upload_flow()
    {
        $this->actingAs($this->teacher);

        // Mock the logger to capture correlation ID usage
        $mockLogger = Mockery::mock(FileUploadLogger::class);
        $this->app->instance(FileUploadLogger::class, $mockLogger);

        $correlationId = 'test-correlation-id-12345';
        $file = UploadedFile::fake()->create('test.jpg', 500, 'image/jpeg');

        // Expect correlation ID to be used consistently
        $mockLogger->shouldReceive('logUploadAttempt')
            ->once()
            ->andReturn($correlationId);

        $mockLogger->shouldReceive('startPerformanceMonitoring')
            ->once()
            ->with($correlationId, Mockery::type('Illuminate\Http\UploadedFile'))
            ->andReturn(['correlation_id' => $correlationId]);

        $mockLogger->shouldReceive('logUploadSuccess')
            ->once()
            ->with($correlationId, Mockery::type('App\Models\Content'), Mockery::type('array'));

        $mockLogger->shouldReceive('endPerformanceMonitoring')
            ->once()
            ->with($correlationId, true, Mockery::type('array'));

        // Make upload request
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Correlation ID Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);

        // Verify correlation ID is returned in response
        $response->assertStatus(201);
        $response->assertJson(['correlation_id' => $correlationId]);
    }

    /**
     * Test external URL alternative to file upload.
     */
    public function test_it_handles_external_url_alternative_in_upload_flow()
    {
        $this->actingAs($this->teacher);

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'video',
            'title' => 'External Video',
            'description' => 'Video hosted externally',
            'section' => 'main_content',
            'visibility' => 'student',
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'type',
                'title',
                'external_url',
            ],
            'correlation_id',
        ]);

        // Verify content was created with external URL
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'video',
            'title' => 'External Video',
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $content = Content::where('title', 'External Video')->first();
        $this->assertNull($content->file_path);
        $this->assertNotNull($content->external_url);
    }

    /**
     * Test that upload flow requires either file or external URL.
     */
    public function test_it_requires_either_file_or_external_url_in_upload_flow()
    {
        $this->actingAs($this->teacher);

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Missing File and URL',
            'section' => 'main_content',
            'visibility' => 'student',
            // No file or external_url provided
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'file'
            ],
            'correlation_id',
        ]);

        $responseData = $response->json();
        $this->assertFalse($responseData['success']);
        $this->assertStringContainsString('file', strtolower($responseData['errors']['file'][0]));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}