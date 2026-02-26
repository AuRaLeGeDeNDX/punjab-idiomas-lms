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
        // Simply fake the disks and put a test file in R2 so the Service finds it!
        \Illuminate\Support\Facades\Storage::fake('r2');
        \Illuminate\Support\Facades\Storage::fake('public');

        // Pre-populate the fake R2 disk with the files the test expects
        \Illuminate\Support\Facades\Storage::disk('r2')->put('content-blocks/1/2/3/uuid_test-image.jpg', 'Fake Image Content');
        \Illuminate\Support\Facades\Storage::disk('r2')->put('content-blocks/1/2/3/uuid_document.pdf', 'Fake PDF Content');
        \Illuminate\Support\Facades\Storage::disk('r2')->put('content-blocks/1/2/3/uuid_audio.mp3', 'Fake Audio Content');
        \Illuminate\Support\Facades\Storage::disk('r2')->put('content-blocks/1/2/3/uuid_video.mp4', 'Fake Video Content');
    }

    public function test_successful_image_upload()
    {
        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'image',
                'title' => 'Test Image Block',
                'r2_path' => 'content-blocks/1/2/3/uuid_test-image.jpg',
                'original_filename' => 'test-image.jpg',
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
            'title' => 'Test Image Block',
            'file_path' => 'content-blocks/1/2/3/uuid_test-image.jpg'
        ]);
    }

    public function test_pdf_upload_validation()
    {
        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'pdf',
                'title' => 'Test PDF Document',
                'r2_path' => 'content-blocks/1/2/3/uuid_document.pdf',
                'original_filename' => 'document.pdf',
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
        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'audio',
                'title' => 'Test Audio File',
                'r2_path' => 'content-blocks/1/2/3/uuid_audio.mp3',
                'original_filename' => 'audio.mp3',
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
        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'video',
                'title' => 'Test Video File',
                'r2_path' => 'content-blocks/1/2/3/uuid_video.mp4',
                'original_filename' => 'video.mp4',
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
                'original_filename' => 'external_link',
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

    public function test_requires_either_file_or_external_url_or_r2()
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
                // No file, r2_path, or external_url provided
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