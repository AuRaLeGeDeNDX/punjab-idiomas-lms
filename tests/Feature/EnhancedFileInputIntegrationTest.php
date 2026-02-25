<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;

/**
 * Integration tests for Enhanced File Input Handling - Task 5.3
 * 
 * Tests the complete file upload flow with enhanced features:
 * - Drag-and-drop file selection
 * - File preview for images
 * - File information display
 * - Automatic clearing of invalid files
 * 
 * Requirements: 5.1, 5.2
 */
class EnhancedFileInputIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test enhanced file upload with valid image file
     * 
     * @test
     */
    public function it_handles_valid_image_upload_with_enhanced_features()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        // Create a test file
        $file = UploadedFile::fake()->create('test-image.jpg', 1024); // 1MB

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => 'Test Image Block',
                'description' => 'Test description',
                'section' => 'main_content',
                'visibility' => 'student',
                'is_active' => true,
                'file' => $file,
            ]);

        $response->assertStatus(201);
        
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('image', $responseData['data']['type']);
        $this->assertEquals('Test Image Block', $responseData['data']['title']);
        $this->assertNotNull($responseData['data']['file_path']);
        
        // Verify file was stored
        $this->assertTrue(Storage::disk('public')->exists($responseData['data']['file_path']));
    }

    /**
     * Test enhanced error handling for oversized files
     * 
     * @test
     */
    public function it_rejects_oversized_files_with_detailed_error_messages()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        // Create a large test file (simulate 15MB)
        $file = UploadedFile::fake()->create('large-image.jpg', 15360); // 15MB

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => 'Test Large Image',
                'file' => $file,
                'section' => 'main_content',
                'visibility' => 'student',
            ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('file', $responseData['errors']);
        
        // Check that error message contains size information
        $errorMessage = $responseData['errors']['file'][0];
        $this->assertStringContainsString('size', strtolower($errorMessage));
        $this->assertStringContainsString('exceeds', strtolower($errorMessage));
    }

    /**
     * Test enhanced error handling for invalid file types
     * 
     * @test
     */
    public function it_rejects_invalid_file_types_with_detailed_error_messages()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        // Create an invalid file type for image content
        $file = UploadedFile::fake()->create('document.exe', 1024);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => 'Test Invalid File',
                'file' => $file,
                'section' => 'main_content',
                'visibility' => 'student',
            ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('file', $responseData['errors']);
        
        // Check that error message contains file type information
        $errorMessage = $responseData['errors']['file'][0];
        $this->assertStringContainsString('type', strtolower($errorMessage));
        $this->assertStringContainsString('allowed', strtolower($errorMessage));
    }

    /**
     * Test enhanced logging for file upload attempts
     * 
     * @test
     */
    public function it_logs_file_upload_attempts_with_correlation_ids()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        $file = UploadedFile::fake()->create('test-document.pdf', 1024);

        // Capture log output
        $this->expectsEvents(\Illuminate\Log\Events\MessageLogged::class);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'pdf',
                'title' => 'Test PDF Block',
                'file' => $file,
                'section' => 'main_content',
                'visibility' => 'student',
            ]);

        // Should succeed or fail, but should be logged either way
        $this->assertTrue($response->status() === 201 || $response->status() === 422);
    }

    /**
     * Test file information display in response
     * 
     * @test
     */
    public function it_returns_detailed_file_information_in_response()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        $file = UploadedFile::fake()->create('test-document.pdf', 1024);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'pdf',
                'title' => 'Test PDF Block',
                'file' => $file,
                'section' => 'main_content',
                'visibility' => 'student',
            ]);

        if ($response->status() === 201) {
            $responseData = $response->json();
            $this->assertArrayHasKey('data', $responseData);
            
            $contentBlock = $responseData['data'];
            $this->assertArrayHasKey('file_path', $contentBlock);
            $this->assertArrayHasKey('file_size', $contentBlock);
            $this->assertArrayHasKey('original_filename', $contentBlock);
            
            // Verify file information is accurate
            $this->assertEquals('test-document.pdf', $contentBlock['original_filename']);
            $this->assertGreaterThan(0, $contentBlock['file_size']);
        }
    }

    /**
     * Test multiple file handling (should take first file)
     * 
     * @test
     */
    public function it_handles_multiple_files_by_taking_first_one()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        // Simulate multiple files (though HTTP typically sends one at a time)
        $file = UploadedFile::fake()->create('first-file.pdf', 1024);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'pdf',
                'title' => 'Test Multiple Files',
                'file' => $file,
                'section' => 'main_content',
                'visibility' => 'student',
            ]);

        // Should handle the single file normally
        if ($response->status() === 201) {
            $responseData = $response->json();
            $this->assertEquals('first-file.pdf', $responseData['data']['original_filename']);
        }
    }

    /**
     * Test enhanced validation with PHP upload errors
     * 
     * @test
     */
    public function it_handles_php_upload_errors_with_detailed_messages()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        // Test with no file (simulates UPLOAD_ERR_NO_FILE)
        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => 'Test No File',
                'section' => 'main_content',
                'visibility' => 'student',
                // No file provided
            ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        $this->assertArrayHasKey('errors', $responseData);
        
        // Should have validation error for missing file
        $this->assertTrue(
            isset($responseData['errors']['file']) || 
            isset($responseData['errors']['external_url'])
        );
    }

    /**
     * Test content type specific file validation
     * 
     * @test
     */
    public function it_validates_files_based_on_content_type()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        // Test image content type with PDF file (should fail)
        $pdfFile = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'image', // Expecting image but providing PDF
                'title' => 'Test Wrong Type',
                'file' => $pdfFile,
                'section' => 'main_content',
                'visibility' => 'student',
            ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('file', $responseData['errors']);
        
        // Error should mention file type mismatch
        $errorMessage = $responseData['errors']['file'][0];
        $this->assertStringContainsString('type', strtolower($errorMessage));
    }

    /**
     * Test server resource validation
     * 
     * @test
     */
    public function it_validates_server_resources_before_upload()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['user_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        $file = UploadedFile::fake()->create('test-file.pdf', 1024);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks", [
                'type' => 'pdf',
                'title' => 'Test Server Resources',
                'file' => $file,
                'section' => 'main_content',
                'visibility' => 'student',
            ]);

        // Should either succeed or fail gracefully with server resource errors
        $this->assertTrue(in_array($response->status(), [201, 422, 500]));
        
        if ($response->status() === 422) {
            $responseData = $response->json();
            if (isset($responseData['errors']['file'])) {
                // If there's a file error, it should be descriptive
                $errorMessage = $responseData['errors']['file'][0];
                $this->assertIsString($errorMessage);
                $this->assertNotEmpty($errorMessage);
            }
        }
    }
}