<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;
use App\Services\ContentBlockService;
use App\Services\SecureFileStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

/**
 * EndToEndFileWorkflowIntegrationTest tests complete upload and display workflows.
 * 
 * This test suite covers:
 * - Complete file upload workflows from UI to storage
 * - File display and serving workflows
 * - Cross-storage-type compatibility
 * - Permission-based access control
 * - End-to-end security validation
 * 
 * Requirements: 6.1, 6.2, 6.4, 6.5
 */
class EndToEndFileWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected User $student;
    protected Course $course;
    protected Module $module;
    protected Subpage $subpage;
    protected ContentBlockService $contentBlockService;
    protected SecureFileStorageService $secureFileService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);
        
        // Create users with roles
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
        
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->student = User::factory()->create();
        $this->student->assignRole('Student');
        
        // Create course structure
        $this->course = Course::factory()->create(['created_by' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
        
        // Get services
        $this->contentBlockService = app(ContentBlockService::class);
        $this->secureFileService = app(SecureFileStorageService::class);
        
        // Ensure storage directories exist
        Storage::disk('public')->makeDirectory('test-uploads');
        Storage::disk('protected')->makeDirectory('test-uploads');
    }

    /** @test */
    public function it_can_complete_end_to_end_image_upload_workflow()
    {
        // Requirements: 6.1 - File type validation and security scanning
        $this->actingAs($this->teacher);
        
        // Create test image file
        $imageFile = UploadedFile::fake()->image('test-image.jpg', 800, 600)->size(500);
        
        // Step 1: Upload via API endpoint
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Test Image Upload',
            'file' => $imageFile,
            'section' => 'main_content',
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'content' => [
                'id',
                'type',
                'title',
                'file_path',
                'storage_disk',
                'file_size',
                'file_hash',
                'original_filename',
            ],
            'correlation_id',
        ]);
        
        $contentData = $response->json('content');
        $content = Content::find($contentData['id']);
        
        // Step 2: Verify file was stored correctly
        $this->assertNotNull($content);
        $this->assertEquals('image', $content->type);
        $this->assertEquals('test-image.jpg', $content->original_filename);
        $this->assertNotNull($content->file_path);
        $this->assertNotNull($content->storage_disk);
        $this->assertNotNull($content->file_hash);
        
        // Step 3: Verify file exists in storage
        $this->assertTrue(Storage::disk($content->storage_disk)->exists($content->file_path));
        
        // Step 4: Test file serving via secure endpoint
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        
        // Step 5: Test file display in content view
        $response = $this->get("/teacher/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}");
        $response->assertStatus(200);
        $response->assertSee($content->title);
    }

    /** @test */
    public function it_can_complete_end_to_end_document_upload_workflow()
    {
        // Requirements: 6.1 - File type validation for documents
        $this->actingAs($this->teacher);
        
        // Create test PDF file
        $pdfFile = UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');
        
        // Step 1: Upload document
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'document',
            'title' => 'Test Document Upload',
            'file' => $pdfFile,
            'section' => 'main_content',
        ]);
        
        $response->assertStatus(201);
        $contentData = $response->json('content');
        $content = Content::find($contentData['id']);
        
        // Step 2: Verify document-specific storage (should use protected storage)
        $this->assertEquals('protected', $content->storage_disk);
        $this->assertTrue(Storage::disk('protected')->exists($content->file_path));
        
        // Step 3: Test secure document serving
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
        
        // Step 4: Verify download functionality
        $response = $this->get("/secure-files/{$content->id}?download=1");
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="test-document.pdf"');
    }

    /** @test */
    public function it_enforces_permission_based_access_control_for_file_uploads()
    {
        // Requirements: 6.4, 6.5 - Permission-based access control
        
        // Test 1: Student cannot upload files to teacher's subpage
        $this->actingAs($this->student);
        
        $imageFile = UploadedFile::fake()->image('student-upload.jpg');
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Unauthorized Upload',
            'file' => $imageFile,
        ]);
        
        $response->assertStatus(403); // Forbidden
        
        // Test 2: Teacher can upload to their own subpage
        $this->actingAs($this->teacher);
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Authorized Upload',
            'file' => $imageFile,
        ]);
        
        $response->assertStatus(201);
        
        // Test 3: Admin can upload to any subpage
        $this->actingAs($this->admin);
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Admin Upload',
            'file' => $imageFile,
        ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function it_enforces_permission_based_access_control_for_file_access()
    {
        // Requirements: 6.4, 6.5 - File access permissions
        
        // Create content as teacher
        $this->actingAs($this->teacher);
        $imageFile = UploadedFile::fake()->image('protected-image.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'image',
            'title' => 'Protected Image',
            'file' => $imageFile,
            'section' => 'main_content',
        ]);
        
        // Test 1: Teacher can access their own content
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(200);
        
        // Test 2: Student enrolled in course can access content
        $this->course->enrollments()->create(['user_id' => $this->student->id]);
        
        $this->actingAs($this->student);
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(200);
        
        // Test 3: Unenrolled student cannot access content
        $unenrolledStudent = User::factory()->create();
        $unenrolledStudent->assignRole('Student');
        
        $this->actingAs($unenrolledStudent);
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(403);
        
        // Test 4: Admin can access any content
        $this->actingAs($this->admin);
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_cross_storage_type_compatibility()
    {
        // Requirements: 6.2 - Cross-storage compatibility
        $this->actingAs($this->teacher);
        
        // Test 1: Upload image to public storage
        $publicImage = UploadedFile::fake()->image('public-image.jpg');
        
        $publicContent = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'image',
            'title' => 'Public Image',
            'file' => $publicImage,
            'section' => 'main_content',
        ]);
        
        // Test 2: Upload document to protected storage
        $protectedDoc = UploadedFile::fake()->create('protected-doc.pdf', 1024, 'application/pdf');
        
        $protectedContent = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'document',
            'title' => 'Protected Document',
            'file' => $protectedDoc,
            'section' => 'main_content',
        ]);
        
        // Test 3: Verify both files are accessible through unified interface
        $response1 = $this->get("/secure-files/{$publicContent->id}");
        $response1->assertStatus(200);
        
        $response2 = $this->get("/secure-files/{$protectedContent->id}");
        $response2->assertStatus(200);
        
        // Test 4: Verify storage disk assignment is correct
        $this->assertEquals('public', $publicContent->storage_disk);
        $this->assertEquals('protected', $protectedContent->storage_disk);
        
        // Test 5: Test file migration between storage types
        $originalDisk = $publicContent->storage_disk;
        $originalPath = $publicContent->file_path;
        
        // Simulate migration to protected storage
        $newPath = 'migrated/' . basename($originalPath);
        Storage::disk('protected')->put($newPath, Storage::disk($originalDisk)->get($originalPath));
        
        $publicContent->update([
            'storage_disk' => 'protected',
            'file_path' => $newPath,
        ]);
        
        // Verify file is still accessible after migration
        $response = $this->get("/secure-files/{$publicContent->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_validates_file_security_throughout_workflow()
    {
        // Requirements: 6.1, 6.2 - Security validation
        $this->actingAs($this->teacher);
        
        // Test 1: Reject malicious file types
        $maliciousFile = UploadedFile::fake()->create('malicious.exe', 1024, 'application/x-executable');
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'document',
            'title' => 'Malicious File',
            'file' => $maliciousFile,
        ]);
        
        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['file']);
        
        // Test 2: Validate file size limits
        $oversizedFile = UploadedFile::fake()->create('huge-file.pdf', 50 * 1024, 'application/pdf'); // 50MB
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'document',
            'title' => 'Oversized File',
            'file' => $oversizedFile,
        ]);
        
        $response->assertStatus(422);
        
        // Test 3: Validate MIME type consistency
        $fakeImage = UploadedFile::fake()->create('fake-image.jpg', 1024, 'text/plain');
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Fake Image',
            'file' => $fakeImage,
        ]);
        
        $response->assertStatus(422);
    }

    /** @test */
    public function it_handles_concurrent_upload_operations()
    {
        // Requirements: 6.2 - Concurrent operation handling
        $this->actingAs($this->teacher);
        
        $promises = [];
        $files = [];
        
        // Create multiple files for concurrent upload
        for ($i = 0; $i < 5; $i++) {
            $files[] = UploadedFile::fake()->image("concurrent-image-{$i}.jpg");
        }
        
        // Simulate concurrent uploads
        foreach ($files as $index => $file) {
            $response = $this->postJson('/api/content-blocks', [
                'subpage_id' => $this->subpage->id,
                'type' => 'image',
                'title' => "Concurrent Upload {$index}",
                'file' => $file,
                'section' => 'main_content',
            ]);
            
            $response->assertStatus(201);
            $promises[] = $response->json('content.id');
        }
        
        // Verify all uploads completed successfully
        $this->assertCount(5, $promises);
        
        foreach ($promises as $contentId) {
            $content = Content::find($contentId);
            $this->assertNotNull($content);
            $this->assertTrue(Storage::disk($content->storage_disk)->exists($content->file_path));
        }
    }

    /** @test */
    public function it_maintains_file_integrity_throughout_workflow()
    {
        // Requirements: 6.1, 6.2 - File integrity validation
        $this->actingAs($this->teacher);
        
        // Create test file with known content
        $testContent = 'This is test file content for integrity validation.';
        $testFile = UploadedFile::fake()->createWithContent('test-integrity.txt', $testContent);
        
        // Upload file
        $content = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'document',
            'title' => 'Integrity Test File',
            'file' => $testFile,
            'section' => 'main_content',
        ]);
        
        // Verify file hash was calculated and stored
        $this->assertNotNull($content->file_hash);
        
        // Verify stored file content matches original
        $storedContent = Storage::disk($content->storage_disk)->get($content->file_path);
        $this->assertEquals($testContent, $storedContent);
        
        // Verify hash matches stored content
        $calculatedHash = hash('sha256', $storedContent);
        $this->assertEquals($content->file_hash, $calculatedHash);
        
        // Test file serving maintains integrity
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(200);
        $this->assertEquals($testContent, $response->getContent());
    }

    /** @test */
    public function it_handles_file_update_workflows()
    {
        // Requirements: 6.2 - File update operations
        $this->actingAs($this->teacher);
        
        // Step 1: Create initial content with file
        $originalFile = UploadedFile::fake()->image('original-image.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'image',
            'title' => 'Original Image',
            'file' => $originalFile,
            'section' => 'main_content',
        ]);
        
        $originalPath = $content->file_path;
        $originalHash = $content->file_hash;
        
        // Step 2: Update content with new file
        $newFile = UploadedFile::fake()->image('updated-image.jpg');
        
        $updatedContent = $this->contentBlockService->updateContentBlock($content, [
            'title' => 'Updated Image',
            'file' => $newFile,
        ]);
        
        // Step 3: Verify old file was cleaned up
        $this->assertFalse(Storage::disk($content->storage_disk)->exists($originalPath));
        
        // Step 4: Verify new file exists and is accessible
        $this->assertTrue(Storage::disk($updatedContent->storage_disk)->exists($updatedContent->file_path));
        $this->assertNotEquals($originalHash, $updatedContent->file_hash);
        
        // Step 5: Test updated file serving
        $response = $this->get("/secure-files/{$updatedContent->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_file_deletion_workflows()
    {
        // Requirements: 6.2 - File deletion operations
        $this->actingAs($this->teacher);
        
        // Step 1: Create content with file
        $testFile = UploadedFile::fake()->image('delete-test.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'image',
            'title' => 'Delete Test Image',
            'file' => $testFile,
            'section' => 'main_content',
        ]);
        
        $filePath = $content->file_path;
        $storageDisk = $content->storage_disk;
        
        // Verify file exists
        $this->assertTrue(Storage::disk($storageDisk)->exists($filePath));
        
        // Step 2: Delete content
        $this->contentBlockService->deleteContentBlock($content);
        
        // Step 3: Verify file was cleaned up
        $this->assertFalse(Storage::disk($storageDisk)->exists($filePath));
        
        // Step 4: Verify content record was deleted
        $this->assertNull(Content::find($content->id));
        
        // Step 5: Verify file serving returns 404
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(404);
    }

    /** @test */
    public function it_handles_error_recovery_in_upload_workflows()
    {
        // Requirements: 6.2 - Error handling and recovery
        $this->actingAs($this->teacher);
        
        // Test 1: Simulate storage failure during upload
        Storage::shouldReceive('disk->put')->andThrow(new \Exception('Storage failure'));
        
        $testFile = UploadedFile::fake()->image('error-test.jpg');
        
        try {
            $this->contentBlockService->createContentBlock($this->subpage, [
                'type' => 'image',
                'title' => 'Error Test Image',
                'file' => $testFile,
                'section' => 'main_content',
            ]);
            
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Verify no orphaned database records
            $orphanedContent = Content::where('title', 'Error Test Image')->first();
            $this->assertNull($orphanedContent);
        }
        
        // Reset storage mock
        Storage::clearResolvedInstances();
        
        // Test 2: Verify system recovers and can handle subsequent uploads
        $recoveryFile = UploadedFile::fake()->image('recovery-test.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'image',
            'title' => 'Recovery Test Image',
            'file' => $recoveryFile,
            'section' => 'main_content',
        ]);
        
        $this->assertNotNull($content);
        $this->assertTrue(Storage::disk($content->storage_disk)->exists($content->file_path));
    }

    protected function tearDown(): void
    {
        // Clean up test files
        Storage::disk('public')->deleteDirectory('test-uploads');
        Storage::disk('protected')->deleteDirectory('test-uploads');
        Storage::disk('public')->deleteDirectory('content_blocks');
        Storage::disk('protected')->deleteDirectory('content_blocks');
        
        parent::tearDown();
    }
}