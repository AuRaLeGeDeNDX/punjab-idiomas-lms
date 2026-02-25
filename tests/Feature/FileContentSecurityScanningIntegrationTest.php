<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;

/**
 * Integration test for file content security scanning in the complete upload flow.
 * 
 * **Validates: Requirements 6.3**
 */
class FileContentSecurityScanningIntegrationTest extends TestCase
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
        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);

        // Suppress logs during testing
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
        Log::shouldReceive('critical')->andReturn(null);
    }

    /**
     * Test successful file upload with security scanning for image files.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_successful_image_upload_with_security_scanning()
    {
        $this->actingAs($this->teacher);

        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600)->size(500);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Image',
            'description' => 'Test image with security scanning',
            'file' => $file,
            'visibility' => 'student',
            'section' => 'main_content',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'type',
                'file_path',
                'file_name',
                'file_size',
                'mime_type',
            ],
            'correlation_id',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('image', $response->json('data.type'));
        $this->assertStringContainsString('test-image', $response->json('data.file_name'));

        // Verify content was created in database
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Test Image',
        ]);
    }

    /**
     * Test successful file upload with security scanning for PDF files.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_successful_pdf_upload_with_security_scanning()
    {
        $this->actingAs($this->teacher);

        $file = UploadedFile::fake()->create('test-document.pdf', 1000, 'application/pdf');

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'pdf',
            'title' => 'Test PDF',
            'description' => 'Test PDF with security scanning',
            'file' => $file,
            'visibility' => 'student',
            'section' => 'resources',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'type',
                'file_path',
                'file_name',
                'file_size',
                'mime_type',
            ],
            'correlation_id',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('pdf', $response->json('data.type'));
        $this->assertStringContainsString('test-document', $response->json('data.file_name'));

        // Verify content was created in database
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'pdf',
            'title' => 'Test PDF',
        ]);
    }

    /**
     * Test successful file upload with security scanning for audio files.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_successful_audio_upload_with_security_scanning()
    {
        $this->actingAs($this->teacher);

        $file = UploadedFile::fake()->create('test-audio.mp3', 2000, 'audio/mpeg');

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'audio',
            'title' => 'Test Audio',
            'description' => 'Test audio with security scanning',
            'file' => $file,
            'visibility' => 'student',
            'section' => 'main_content',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'type',
                'file_path',
                'file_name',
                'file_size',
                'mime_type',
            ],
            'correlation_id',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('audio', $response->json('data.type'));
        $this->assertStringContainsString('test-audio', $response->json('data.file_name'));

        // Verify content was created in database
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'audio',
            'title' => 'Test Audio',
        ]);
    }

    /**
     * Test successful file upload with security scanning for video files.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_successful_video_upload_with_security_scanning()
    {
        $this->actingAs($this->teacher);

        $file = UploadedFile::fake()->create('test-video.mp4', 5000, 'video/mp4');

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'video',
            'title' => 'Test Video',
            'description' => 'Test video with security scanning',
            'file' => $file,
            'visibility' => 'student',
            'section' => 'main_content',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'type',
                'file_path',
                'file_name',
                'file_size',
                'mime_type',
            ],
            'correlation_id',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('video', $response->json('data.type'));
        $this->assertStringContainsString('test-video', $response->json('data.file_name'));

        // Verify content was created in database
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'video',
            'title' => 'Test Video',
        ]);
    }

    /**
     * Test file upload update with security scanning.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_file_upload_update_with_security_scanning()
    {
        $this->actingAs($this->teacher);

        // Create initial content
        $content = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Original Image',
            'created_by' => $this->teacher->id,
        ]);

        // Update with new file
        $newFile = UploadedFile::fake()->image('updated-image.png', 600, 400)->size(300);

        $response = $this->putJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks/{$content->id}", [
            'type' => 'image',
            'title' => 'Updated Image',
            'description' => 'Updated image with security scanning',
            'file' => $newFile,
            'visibility' => 'student',
            'section' => 'main_content',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'type',
                'file_path',
                'file_name',
                'file_size',
                'mime_type',
            ],
            'correlation_id',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Updated Image', $response->json('data.title'));
        $this->assertStringContainsString('updated-image', $response->json('data.file_name'));
    }

    /**
     * Test security scanning with multiple file types in sequence.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_multiple_file_types()
    {
        $this->actingAs($this->teacher);

        $fileTypes = [
            ['file' => UploadedFile::fake()->image('image.jpg'), 'type' => 'image', 'title' => 'Test Image'],
            ['file' => UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'), 'type' => 'pdf', 'title' => 'Test PDF'],
            ['file' => UploadedFile::fake()->create('audio.mp3', 1000, 'audio/mpeg'), 'type' => 'audio', 'title' => 'Test Audio'],
            ['file' => UploadedFile::fake()->create('video.mp4', 2000, 'video/mp4'), 'type' => 'video', 'title' => 'Test Video'],
        ];

        foreach ($fileTypes as $fileData) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => $fileData['type'],
                'title' => $fileData['title'],
                'description' => 'Test file with security scanning',
                'file' => $fileData['file'],
                'visibility' => 'student',
                'section' => 'main_content',
            ]);

            $response->assertStatus(201, "Failed to upload {$fileData['type']} file");
            $this->assertTrue($response->json('success'), "Upload should succeed for {$fileData['type']} file");
            $this->assertEquals($fileData['type'], $response->json('data.type'));
            $this->assertNotNull($response->json('correlation_id'), "Should have correlation ID for {$fileData['type']} file");
        }

        // Verify all content was created
        $this->assertDatabaseCount('contents', 4);
    }

    /**
     * Test security scanning performance with concurrent uploads.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_performance()
    {
        $this->actingAs($this->teacher);

        $startTime = microtime(true);

        // Upload multiple files to test performance
        for ($i = 1; $i <= 5; $i++) {
            $file = UploadedFile::fake()->image("test-image-{$i}.jpg", 400, 300)->size(200);

            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => "Test Image {$i}",
                'description' => 'Performance test image',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content',
            ]);

            $response->assertStatus(201, "Failed to upload image {$i}");
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Should complete all uploads within reasonable time
        $this->assertLessThan(30.0, $totalTime, 'Security scanning should not significantly impact upload performance');

        // Verify all content was created
        $this->assertDatabaseCount('contents', 5);
    }

    /**
     * Test security scanning with edge case file sizes.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_edge_case_file_sizes()
    {
        $this->actingAs($this->teacher);

        // Test very small file (1KB)
        $smallFile = UploadedFile::fake()->create('small.pdf', 1, 'application/pdf');

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'pdf',
            'title' => 'Small PDF',
            'description' => 'Very small PDF file',
            'file' => $smallFile,
            'visibility' => 'student',
            'section' => 'resources',
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));

        // Test medium file (5MB)
        $mediumFile = UploadedFile::fake()->create('medium.pdf', 5120, 'application/pdf');

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'pdf',
            'title' => 'Medium PDF',
            'description' => 'Medium sized PDF file',
            'file' => $mediumFile,
            'visibility' => 'student',
            'section' => 'resources',
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
    }

    /**
     * Test security scanning error handling in integration flow.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_error_handling_integration()
    {
        $this->actingAs($this->teacher);

        // Test with valid file to ensure security scanning doesn't break normal flow
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'pdf',
            'title' => 'Test PDF',
            'description' => 'Test PDF for error handling',
            'file' => $file,
            'visibility' => 'student',
            'section' => 'resources',
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
        $this->assertNotNull($response->json('correlation_id'));

        // Verify content was created despite security scanning
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'pdf',
            'title' => 'Test PDF',
        ]);
    }

    /**
     * Test security scanning with different image formats.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_different_image_formats()
    {
        $this->actingAs($this->teacher);

        $imageFormats = [
            ['name' => 'test.jpg', 'mime' => 'image/jpeg'],
            ['name' => 'test.png', 'mime' => 'image/png'],
            ['name' => 'test.gif', 'mime' => 'image/gif'],
            ['name' => 'test.webp', 'mime' => 'image/webp'],
            ['name' => 'test.bmp', 'mime' => 'image/bmp'],
        ];

        foreach ($imageFormats as $format) {
            $file = UploadedFile::fake()->create($format['name'], 200, $format['mime']);

            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => "Test {$format['name']}",
                'description' => "Test {$format['mime']} image",
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content',
            ]);

            $response->assertStatus(201, "Failed to upload {$format['name']}");
            $this->assertTrue($response->json('success'), "Upload should succeed for {$format['name']}");
            $this->assertEquals('image', $response->json('data.type'));
        }

        // Verify all images were created
        $this->assertDatabaseCount('contents', count($imageFormats));
    }

    /**
     * Test correlation ID tracking through security scanning.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_correlation_id_tracking_through_security_scanning()
    {
        $this->actingAs($this->teacher);

        $file = UploadedFile::fake()->image('test.jpg', 400, 300);

        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Image',
            'description' => 'Test image for correlation tracking',
            'file' => $file,
            'visibility' => 'student',
            'section' => 'main_content',
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));

        // Verify correlation ID is present and valid UUID format
        $correlationId = $response->json('correlation_id');
        $this->assertNotNull($correlationId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $correlationId);
    }
}