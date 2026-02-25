<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ContentBlockFileUploadTest extends TestCase
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

    public function test_successful_image_upload()
    {
        $file = UploadedFile::fake()->create('test-image.jpg', 500, 'image/jpeg'); // 500KB

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Image Block',
                'file' => $file,
                'alt_text' => 'Test image',
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Content block created successfully.'
                ]);

        // Verify content was stored
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Test Image Block'
        ]);
    }

    public function test_file_upload_with_oversized_file()
    {
        // Create a file that's larger than typical limits (50MB)
        $file = UploadedFile::fake()->create('large-file.jpg', 51200, 'image/jpeg'); // 50MB

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

        // Should fail validation due to file size
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors' => [
                        'file'
                    ]
                ]);
    }

    public function test_file_upload_with_invalid_extension()
    {
        $file = UploadedFile::fake()->create('malicious.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Invalid File',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        // Should fail validation due to invalid file type
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors' => [
                        'file'
                    ]
                ]);
    }

    public function test_pdf_upload_validation()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf'); // 1MB

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'pdf',
                'title' => 'Test PDF Document',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'resources'
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Content block created successfully.'
                ]);

        // Verify content was stored
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'pdf',
            'title' => 'Test PDF Document'
        ]);
    }

    public function test_audio_upload_validation()
    {
        $file = UploadedFile::fake()->create('audio.mp3', 2000, 'audio/mpeg'); // 2MB

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'audio',
                'title' => 'Test Audio File',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Content block created successfully.'
                ]);

        // Verify content was stored
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'audio',
            'title' => 'Test Audio File'
        ]);
    }

    public function test_video_upload_validation()
    {
        $file = UploadedFile::fake()->create('video.mp4', 5000, 'video/mp4'); // 5MB

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'video',
                'title' => 'Test Video File',
                'file' => $file,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Content block created successfully.'
                ]);

        // Verify content was stored
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'video',
            'title' => 'Test Video File'
        ]);
    }

    public function test_external_url_as_alternative_to_file_upload()
    {
        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'video',
                'title' => 'External Video',
                'external_url' => 'https://www.youtube.com/watch?v=example',
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Content block created successfully.'
                ]);

        // Verify content was stored with external URL
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'video',
            'title' => 'External Video',
            'external_url' => 'https://www.youtube.com/watch?v=example'
        ]);
    }

    public function test_requires_either_file_or_external_url()
    {
        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Image Without File',
                'visibility' => 'student',
                'section' => 'main_content'
                // No file or external_url provided
            ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors' => [
                        'file'
                    ]
                ]);
    }
}