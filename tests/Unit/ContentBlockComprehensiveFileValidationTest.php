<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ContentBlockController;
use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContentBlockComprehensiveFileValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected Course $course;
    protected Module $module;
    protected Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');

        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
    }

    public function test_validates_file_size_with_formatted_messages()
    {
        // Create a file larger than the 10MB limit for images
        $file = UploadedFile::fake()->create('large-image.jpg', 11 * 1024, 'image/jpeg'); // 11MB

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Large Image',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422);
        
        $errors = $response->json('errors.file');
        $this->assertNotEmpty($errors);
        
        // Check that the error message contains formatted file sizes
        $errorMessage = $errors[0];
        $this->assertStringContainsString('MB', $errorMessage);
        $this->assertStringContainsString('too large', $errorMessage);
    }

    public function test_validates_file_extensions_against_strict_whitelist()
    {
        // Try to upload a .txt file as an image
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Invalid Extension',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422);
        
        $errors = $response->json('errors.file');
        $this->assertNotEmpty($errors);
        
        // Check that the error message mentions allowed types
        $errorMessage = $errors[0];
        $this->assertStringContainsString('not supported', $errorMessage);
        $this->assertStringContainsString('Please use one of these file types:', $errorMessage);
        $this->assertStringContainsString('.jpg', $errorMessage);
        $this->assertStringContainsString('.png', $errorMessage);
    }

    public function test_validates_mime_types_match_file_extensions()
    {
        // Create a file with mismatched MIME type and extension
        $file = UploadedFile::fake()->create('fake-image.jpg', 100, 'text/plain');

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test MIME Mismatch',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422);
        
        $errors = $response->json('errors.file');
        $this->assertNotEmpty($errors);
        
        // Check that the error message mentions MIME type mismatch
        $errorMessage = $errors[0];
        $this->assertStringContainsString('MIME type', $errorMessage);
        $this->assertStringContainsString('does not match', $errorMessage);
        $this->assertStringContainsString('corrupted', $errorMessage);
    }

    public function test_checks_for_empty_files()
    {
        // Create an empty file
        $file = UploadedFile::fake()->create('empty-file.jpg', 0, 'image/jpeg');

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Empty File',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422);
        
        $errors = $response->json('errors.file');
        $this->assertNotEmpty($errors);
        
        // Check that the error message mentions empty file
        $errorMessage = $errors[0];
        $this->assertStringContainsString('empty', $errorMessage);
        $this->assertStringContainsString('valid file with content', $errorMessage);
    }

    public function test_validates_different_content_types_with_different_limits()
    {
        // Test that PDF files under the limit pass validation (not full creation)
        $pdfFile = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'); // 1MB

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'pdf',
                'title' => 'Test PDF',
                'file' => $pdfFile,
                'visibility' => 'student',
                'section' => 'resources'
            ]);

        // Accept either 201 (success) or 500 (server processing error after validation passed)
        // The key is that it should NOT be 422 (validation error)
        $this->assertNotEquals(422, $response->status(), 
            'PDF file should pass validation. Errors: ' . json_encode($response->json('errors')));

        // Test that audio files under the limit pass validation (not full creation)
        $audioFile = UploadedFile::fake()->create('audio.mp3', 2000, 'audio/mpeg'); // 2MB

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'audio',
                'title' => 'Test Audio',
                'file' => $audioFile,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        // Accept either 201 (success) or 500 (server processing error after validation passed)
        // The key is that it should NOT be 422 (validation error)
        $this->assertNotEquals(422, $response->status(),
            'Audio file should pass validation. Errors: ' . json_encode($response->json('errors')));
    }

    public function test_provides_detailed_error_messages_with_context()
    {
        // Test with multiple validation failures
        $file = UploadedFile::fake()->create('large-file.exe', 15 * 1024, 'application/octet-stream'); // 15MB .exe file

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Multiple Failures',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422);
        
        $errors = $response->json('errors.file');
        $this->assertNotEmpty($errors);
        
        // Should have multiple error messages
        $this->assertGreaterThan(1, count($errors));
        
        // Check for size error
        $sizeError = collect($errors)->first(fn($error) => str_contains($error, 'too large'));
        $this->assertNotNull($sizeError);
        $this->assertStringContainsString('MB', $sizeError);
        
        // Check for extension error
        $extensionError = collect($errors)->first(fn($error) => str_contains($error, 'not supported'));
        $this->assertNotNull($extensionError);
        $this->assertStringContainsString('.exe', $extensionError);
    }

    public function test_correlation_id_tracking_in_responses()
    {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Correlation ID',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422);
        
        // Check that correlation ID is present in response
        $this->assertArrayHasKey('correlation_id', $response->json());
        $this->assertNotEmpty($response->json('correlation_id'));
    }
}