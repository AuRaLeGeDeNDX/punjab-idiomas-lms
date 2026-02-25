<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Content;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Services\FileStorageDiagnosticService;
use App\Services\FileOperationLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class DiagnosticServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up storage fakes
        Storage::fake('public');
        Storage::fake('protected');
        
        // Create roles
        \Spatie\Permission\Models\Role::create(['name' => 'Teacher']);
        \Spatie\Permission\Models\Role::create(['name' => 'Student']);
        \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
    }

    /** @test */
    public function content_block_controller_uses_diagnostic_service_for_file_urls()
    {
        // Create test data
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Create content with file
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'image',
            'file_path' => 'test-files/test-image.jpg',
            'storage_disk' => 'public',
            'original_filename' => 'test-image.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
        ]);
        
        // Create the actual file in storage
        Storage::disk('public')->put('test-files/test-image.jpg', 'fake image content');
        
        // Act as teacher and make request
        $this->actingAs($teacher);
        
        $response = $this->getJson("/api/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'content_blocks' => [
                    '*' => [
                        'id',
                        'signed_url',
                        'file_info'
                    ]
                ]
            ]
        ]);
        
        // Verify that the signed URL is present (diagnostic service found the file)
        $contentBlocks = $response->json('data.content_blocks');
        $this->assertNotEmpty($contentBlocks);
        $this->assertNotNull($contentBlocks[0]['signed_url']);
    }

    /** @test */
    public function media_controller_uses_diagnostic_service_for_file_serving()
    {
        // Create test data
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Create content with file in protected storage
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'file_path' => 'test-files/test-document.pdf',
            'storage_disk' => 'protected',
            'original_filename' => 'test-document.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
        ]);
        
        // Create the actual file in storage
        Storage::disk('protected')->put('test-files/test-document.pdf', 'fake pdf content');
        
        // Act as teacher and make request
        $this->actingAs($teacher);
        
        $response = $this->get("/media/content/{$content->id}");
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function secure_file_controller_uses_diagnostic_service_for_downloads()
    {
        // Create test data
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Create content with file in protected storage
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'image',
            'file_path' => 'test-files/secure-image.jpg',
            'storage_disk' => 'protected',
            'original_filename' => 'secure-image.jpg',
            'file_size' => 1536,
            'mime_type' => 'image/jpeg',
        ]);
        
        // Create the actual file in storage
        Storage::disk('protected')->put('test-files/secure-image.jpg', 'fake secure image content');
        
        // Act as teacher and make request
        $this->actingAs($teacher);
        
        $response = $this->get("/secure-files/content/{$content->id}");
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    /** @test */
    public function controllers_handle_missing_files_with_diagnostic_logging()
    {
        // Create test data
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Create content with file that doesn't exist
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'image',
            'file_path' => 'test-files/missing-image.jpg',
            'storage_disk' => 'public',
            'original_filename' => 'missing-image.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
        ]);
        
        // Don't create the actual file - it should be missing
        
        // Act as teacher and test ContentBlockController
        $this->actingAs($teacher);
        
        $response = $this->getJson("/api/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks");
        
        $response->assertStatus(200);
        
        // The response should still be successful, but the signed_url should be null
        $contentBlocks = $response->json('data.content_blocks');
        $this->assertNotEmpty($contentBlocks);
        $this->assertNull($contentBlocks[0]['signed_url']);
        
        // Test MediaController
        $response = $this->get("/media/content/{$content->id}");
        $response->assertStatus(404); // Should return 404 for missing file
        
        // Test SecureFileController
        $response = $this->get("/secure-files/content/{$content->id}");
        $response->assertStatus(404); // Should return 404 for missing file
    }

    /** @test */
    public function controllers_handle_storage_disk_inconsistencies()
    {
        // Create test data
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Create content that says it's on public disk but file is actually on protected disk
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'image',
            'file_path' => 'test-files/inconsistent-image.jpg',
            'storage_disk' => 'public', // Says it's on public disk
            'original_filename' => 'inconsistent-image.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
        ]);
        
        // Create the actual file on protected disk (inconsistency)
        Storage::disk('protected')->put('test-files/inconsistent-image.jpg', 'fake image content');
        
        // Act as teacher and test
        $this->actingAs($teacher);
        
        // ContentBlockController should still work (diagnostic service finds the file)
        $response = $this->getJson("/api/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks");
        
        $response->assertStatus(200);
        $contentBlocks = $response->json('data.content_blocks');
        $this->assertNotEmpty($contentBlocks);
        // The diagnostic service should find the file and generate a URL
        $this->assertNotNull($contentBlocks[0]['signed_url']);
        
        // MediaController should also work (diagnostic service finds the file)
        $response = $this->get("/media/content/{$content->id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }
}