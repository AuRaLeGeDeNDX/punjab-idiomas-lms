<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;
use App\Services\SecureFileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * **Validates: Requirements 6.4, 6.5**
 * 
 * Integration test for secure file storage functionality including:
 * - End-to-end file upload with security features
 * - Content block creation with secure file storage
 * - File access through secure endpoints
 * - Integration with existing content management system
 */
class SecureFileStorageIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $teacher;
    protected Course $course;
    protected Module $module;
    protected Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test data
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->course = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
        ]);
        
        $this->module = Module::factory()->create([
            'course_id' => $this->course->id,
        ]);
        
        $this->subpage = Subpage::factory()->create([
            'module_id' => $this->module->id,
        ]);
        
        // Set up storage
        Storage::fake('protected');
        Storage::fake('public');
    }

    /** @test */
    public function it_creates_content_block_with_secure_file_storage()
    {
        $this->actingAs($this->teacher);
        
        // Create test file
        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600)->size(500);
        
        // Create content block with file upload
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'image',
            'title' => 'Test Image Content',
            'description' => 'A test image uploaded securely',
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
            'alt_text' => 'Test image alt text',
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'type',
                'title',
                'file_path',
                'original_filename',
                'file_size',
                'mime_type',
                'storage_disk',
            ],
            'correlation_id',
        ]);
        
        $contentData = $response->json('data');
        
        // Assert content was created with secure storage
        $this->assertEquals('image', $contentData['type']);
        $this->assertEquals('test-image.jpg', $contentData['original_filename']);
        $this->assertEquals('protected', $contentData['storage_disk']);
        
        // Assert file is stored in protected disk (outside web root)
        $this->assertTrue(Storage::disk('protected')->exists($contentData['file_path']));
        $this->assertFalse(Storage::disk('public')->exists($contentData['file_path']));
        
        // Assert database record has secure storage fields
        $content = Content::find($contentData['id']);
        $this->assertNotNull($content->file_hash);
        $this->assertEquals('protected', $content->storage_disk);
        $this->assertEquals('test-image.jpg', $content->original_filename);
        $this->assertNotEquals('test-image.jpg', basename($content->file_path));
    }

    /** @test */
    public function it_downloads_content_file_through_secure_endpoint()
    {
        $this->actingAs($this->teacher);
        
        // Create content with file
        $file = UploadedFile::fake()->create('test-document.pdf', 100);
        $content = $this->createContentWithFile($file, 'pdf');
        
        // Download file through secure endpoint
        $response = $this->get("/secure-files/content/{$content->id}");
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-document.pdf"');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        
        // Assert file content is returned
        $this->assertNotEmpty($response->getContent());
    }

    /** @test */
    public function it_generates_secure_access_url_for_content_file()
    {
        $this->actingAs($this->teacher);
        
        // Create content with file
        $file = UploadedFile::fake()->image('secure-image.png', 400, 300);
        $content = $this->createContentWithFile($file, 'image');
        
        // Generate secure access URL
        $response = $this->postJson("/secure-files/content/{$content->id}/generate-url", [
            'expiration_minutes' => 30,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'secure_url',
            'expires_in_minutes',
            'expires_at',
        ]);
        
        $secureUrl = $response->json('secure_url');
        $this->assertStringContains('/secure-files/download/', $secureUrl);
        
        // Test accessing file through secure URL
        $urlParts = parse_url($secureUrl);
        $path = $urlParts['path'];
        
        $downloadResponse = $this->get($path);
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Type', 'image/png');
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_content_files()
    {
        // Create content as teacher
        $this->actingAs($this->teacher);
        $file = UploadedFile::fake()->create('private-document.pdf', 100);
        $content = $this->createContentWithFile($file, 'pdf');
        
        // Create another user (student)
        $student = User::factory()->create();
        $student->assignRole('Student');
        
        // Try to access file as unauthorized user
        $this->actingAs($student);
        $response = $this->get("/secure-files/content/{$content->id}");
        
        // Should be forbidden since student doesn't have access to this content
        $response->assertStatus(403);
    }

    /** @test */
    public function it_handles_missing_files_gracefully()
    {
        $this->actingAs($this->teacher);
        
        // Create content without file
        $content = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'type' => 'text',
            'content' => 'Text content without file',
            'file_path' => null,
        ]);
        
        // Try to download non-existent file
        $response = $this->get("/secure-files/content/{$content->id}");
        
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Content has no associated file'
        ]);
    }

    /** @test */
    public function it_validates_file_integrity_using_hash()
    {
        $this->actingAs($this->teacher);
        
        // Create content with file
        $file = UploadedFile::fake()->create('integrity-test.txt', 50);
        $content = $this->createContentWithFile($file, 'document');
        
        // Verify file hash matches stored content
        $storedContent = Storage::disk('protected')->get($content->file_path);
        $actualHash = hash('sha256', $storedContent);
        
        $this->assertEquals($actualHash, $content->file_hash);
        
        // Verify hash is properly formatted (64 hex characters)
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $content->file_hash);
    }

    /** @test */
    public function it_maintains_file_permissions_after_storage()
    {
        $this->actingAs($this->teacher);
        
        // Create content with file
        $file = UploadedFile::fake()->create('permissions-test.txt', 25);
        $content = $this->createContentWithFile($file, 'document');
        
        // Check file permissions
        $absolutePath = Storage::disk('protected')->path($content->file_path);
        $permissions = fileperms($absolutePath) & 0777;
        
        // Assert secure permissions (640 = rw-r-----)
        $this->assertEquals(0640, $permissions);
    }

    /** @test */
    public function it_creates_hierarchical_storage_structure()
    {
        $this->actingAs($this->teacher);
        
        // Create content with file
        $file = UploadedFile::fake()->create('structure-test.txt', 30);
        $content = $this->createContentWithFile($file, 'document');
        
        // Assert hierarchical path structure
        $expectedPattern = sprintf(
            '/^content_blocks\/document\/\d{4}\/\d{2}\/\d{2}\/courses\/%d\/users\/%d\//',
            $this->course->id,
            $this->teacher->id
        );
        
        $this->assertMatchesRegularExpression($expectedPattern, $content->file_path);
    }

    /** @test */
    public function it_logs_file_access_for_security_auditing()
    {
        $this->actingAs($this->teacher);
        
        // Create content with file
        $file = UploadedFile::fake()->create('audit-test.pdf', 75);
        $content = $this->createContentWithFile($file, 'pdf');
        
        // Clear previous logs
        \Illuminate\Support\Facades\Log::shouldReceive('channel')
            ->with('single')
            ->andReturnSelf();
            
        \Illuminate\Support\Facades\Log::shouldReceive('info')
            ->with('CONTENT_FILE_ACCESS_LOG', \Mockery::type('array'))
            ->once();
        
        // Access file (this should trigger logging)
        $response = $this->get("/secure-files/content/{$content->id}");
        
        $response->assertStatus(200);
        
        // Logging expectation is verified by Mockery
    }

    /** @test */
    public function it_supports_multiple_file_types_with_secure_storage()
    {
        $this->actingAs($this->teacher);
        
        $testFiles = [
            ['type' => 'image', 'file' => UploadedFile::fake()->image('test.jpg', 200, 200)],
            ['type' => 'pdf', 'file' => UploadedFile::fake()->create('test.pdf', 100)],
            ['type' => 'audio', 'file' => UploadedFile::fake()->create('test.mp3', 200)],
            ['type' => 'video', 'file' => UploadedFile::fake()->create('test.mp4', 500)],
        ];
        
        foreach ($testFiles as $testFile) {
            $content = $this->createContentWithFile($testFile['file'], $testFile['type']);
            
            // Assert secure storage for each file type
            $this->assertEquals('protected', $content->storage_disk);
            $this->assertTrue(Storage::disk('protected')->exists($content->file_path));
            $this->assertNotNull($content->file_hash);
            
            // Assert file can be accessed
            $response = $this->get("/secure-files/content/{$content->id}");
            $response->assertStatus(200);
        }
    }

    /**
     * Helper method to create content with file.
     */
    private function createContentWithFile(UploadedFile $file, string $type): Content
    {
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => $type,
            'title' => "Test {$type} Content",
            'description' => "A test {$type} file",
            'section' => 'main_content',
            'visibility' => 'student',
            'file' => $file,
        ]);
        
        $response->assertStatus(201);
        
        return Content::find($response->json('data.id'));
    }
}