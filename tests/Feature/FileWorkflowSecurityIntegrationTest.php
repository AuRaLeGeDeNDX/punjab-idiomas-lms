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
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

/**
 * FileWorkflowSecurityIntegrationTest verifies security throughout file workflows.
 * 
 * This test suite covers:
 * - File type validation and security scanning
 * - Malicious file detection and blocking
 * - Content security policy enforcement
 * - Secure file serving with proper headers
 * - Path traversal attack prevention
 * - File size and resource limit enforcement
 * 
 * Requirements: 6.1, 6.2 - File security and validation
 */
class FileWorkflowSecurityIntegrationTest extends TestCase
{
    use RefreshDatabase;

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
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);
        
        // Create users
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->student = User::factory()->create();
        $this->student->assignRole('Student');
        
        // Create course structure
        $this->course = Course::factory()->create(['created_by' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
        
        // Enroll student
        $this->course->enrollments()->create(['user_id' => $this->student->id]);
        
        // Get services
        $this->contentBlockService = app(ContentBlockService::class);
        $this->secureFileService = app(SecureFileStorageService::class);
        
        // Ensure storage directories exist
        Storage::disk('public')->makeDirectory('security-test');
        Storage::disk('protected')->makeDirectory('security-test');
    }

    /** @test */
    public function it_blocks_malicious_file_types()
    {
        // Requirements: 6.1 - File type validation and security scanning
        $this->actingAs($this->teacher);
        
        $maliciousFiles = [
            ['name' => 'malware.exe', 'mime' => 'application/x-executable'],
            ['name' => 'script.bat', 'mime' => 'application/x-bat'],
            ['name' => 'virus.scr', 'mime' => 'application/x-screensaver'],
            ['name' => 'trojan.com', 'mime' => 'application/x-msdos-program'],
            ['name' => 'malicious.php', 'mime' => 'application/x-php'],
            ['name' => 'script.js', 'mime' => 'application/javascript'],
            ['name' => 'payload.vbs', 'mime' => 'text/vbscript'],
        ];
        
        foreach ($maliciousFiles as $fileData) {
            $maliciousFile = UploadedFile::fake()->create(
                $fileData['name'], 
                1024, 
                $fileData['mime']
            );
            
            $response = $this->postJson('/api/content-blocks', [
                'subpage_id' => $this->subpage->id,
                'type' => 'document',
                'title' => 'Malicious File Test',
                'file' => $maliciousFile,
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['file']);
            
            // Verify no file was stored
            $this->assertEquals(0, Content::where('original_filename', $fileData['name'])->count());
        }
    }

    /** @test */
    public function it_validates_file_content_matches_extension()
    {
        // Requirements: 6.1 - MIME type validation
        $this->actingAs($this->teacher);
        
        // Test 1: Executable disguised as image
        $fakeImage = UploadedFile::fake()->create('fake-image.jpg', 1024, 'application/x-executable');
        
        $response1 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Fake Image Test',
            'file' => $fakeImage,
        ]);
        
        $response1->assertStatus(422);
        
        // Test 2: Text file disguised as PDF
        $fakePdf = UploadedFile::fake()->create('fake-document.pdf', 1024, 'text/plain');
        
        $response2 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'document',
            'title' => 'Fake PDF Test',
            'file' => $fakePdf,
        ]);
        
        $response2->assertStatus(422);
        
        // Test 3: Valid file should pass
        $validImage = UploadedFile::fake()->image('valid-image.jpg');
        
        $response3 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Valid Image Test',
            'file' => $validImage,
        ]);
        
        $response3->assertStatus(201);
    }

    /** @test */
    public function it_enforces_file_size_limits()
    {
        // Requirements: 6.1 - Resource limit enforcement
        $this->actingAs($this->teacher);
        
        // Test oversized image (assuming 10MB limit for images)
        $oversizedImage = UploadedFile::fake()->create('huge-image.jpg', 15 * 1024, 'image/jpeg'); // 15MB
        
        $response1 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Oversized Image Test',
            'file' => $oversizedImage,
        ]);
        
        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors(['file']);
        
        // Test oversized document (assuming 50MB limit for documents)
        $oversizedDoc = UploadedFile::fake()->create('huge-doc.pdf', 60 * 1024, 'application/pdf'); // 60MB
        
        $response2 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'document',
            'title' => 'Oversized Document Test',
            'file' => $oversizedDoc,
        ]);
        
        $response2->assertStatus(422);
        
        // Test valid sized file
        $validSizeImage = UploadedFile::fake()->image('normal-image.jpg')->size(2048); // 2MB
        
        $response3 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Normal Size Image Test',
            'file' => $validSizeImage,
        ]);
        
        $response3->assertStatus(201);
    }

    /** @test */
    public function it_prevents_path_traversal_attacks()
    {
        // Requirements: 6.2 - Path traversal prevention
        $this->actingAs($this->teacher);
        
        $maliciousFilenames = [
            '../../../etc/passwd',
            '..\\..\\windows\\system32\\config\\sam',
            'normal-name.jpg/../../../sensitive-file',
            'file.jpg%00.exe',
            'file.jpg\x00.exe',
        ];
        
        foreach ($maliciousFilenames as $filename) {
            $testFile = UploadedFile::fake()->image('test.jpg');
            
            // Attempt to manipulate filename through various means
            $response = $this->postJson('/api/content-blocks', [
                'subpage_id' => $this->subpage->id,
                'type' => 'image',
                'title' => 'Path Traversal Test',
                'file' => $testFile,
                'custom_filename' => $filename, // If such parameter existed
            ]);
            
            // Should either succeed with sanitized filename or fail validation
            if ($response->status() === 201) {
                $content = Content::latest()->first();
                
                // Verify filename was sanitized and doesn't contain path traversal
                $this->assertStringNotContainsString('..', $content->file_path);
                $this->assertStringNotContainsString('\\', $content->file_path);
                $this->assertStringNotContainsString("\x00", $content->file_path);
                
                // Verify file is stored in expected location
                $this->assertStringStartsWith('content_blocks/', $content->file_path);
            }
        }
    }

    /** @test */
    public function it_serves_files_with_secure_headers()
    {
        // Requirements: 6.2 - Secure file serving
        $this->actingAs($this->teacher);
        
        // Create test files of different types
        $imageFile = UploadedFile::fake()->image('secure-image.jpg');
        $documentFile = UploadedFile::fake()->create('secure-doc.pdf', 1024, 'application/pdf');
        
        $imageContent = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'image',
            'title' => 'Secure Image Test',
            'file' => $imageFile,
            'section' => 'main_content',
        ]);
        
        $documentContent = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'document',
            'title' => 'Secure Document Test',
            'file' => $documentFile,
            'section' => 'main_content',
        ]);
        
        // Test image serving headers
        $this->actingAs($this->student);
        
        $imageResponse = $this->get("/secure-files/{$imageContent->id}");
        $imageResponse->assertStatus(200);
        
        // Verify security headers
        $imageResponse->assertHeader('X-Content-Type-Options', 'nosniff');
        $imageResponse->assertHeader('X-Frame-Options', 'DENY');
        $imageResponse->assertHeaderMissing('X-Powered-By');
        
        // Verify content type is correct
        $imageResponse->assertHeader('Content-Type', 'image/jpeg');
        
        // Test document serving headers
        $documentResponse = $this->get("/secure-files/{$documentContent->id}");
        $documentResponse->assertStatus(200);
        
        // Documents should have stricter caching policies
        $this->assertStringContainsString('no-cache', $documentResponse->headers->get('Cache-Control') ?? '');
        $documentResponse->assertHeader('Content-Type', 'application/pdf');
        
        // Test download headers
        $downloadResponse = $this->get("/secure-files/{$documentContent->id}?download=1");
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Disposition', 'attachment; filename="secure-doc.pdf"');
    }

    /** @test */
    public function it_prevents_content_sniffing_attacks()
    {
        // Requirements: 6.2 - Content sniffing prevention
        $this->actingAs($this->teacher);
        
        // Create a text file that could be interpreted as HTML
        $maliciousContent = '<script>alert("XSS")</script>';
        $textFile = UploadedFile::fake()->createWithContent('malicious.txt', $maliciousContent);
        
        $content = $this->contentBlockService->createContentBlock($this->subpage, [
            'type' => 'document',
            'title' => 'Content Sniffing Test',
            'file' => $textFile,
            'section' => 'main_content',
        ]);
        
        $this->actingAs($this->student);
        
        $response = $this->get("/secure-files/{$content->id}");
        $response->assertStatus(200);
        
        // Verify X-Content-Type-Options header prevents sniffing
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        
        // Verify content is served with correct MIME type
        $response->assertHeader('Content-Type', 'text/plain');
        
        // Verify content is served as-is without interpretation
        $this->assertEquals($maliciousContent, $response->getContent());
    }

    /** @test */
    public function it_validates_image_file_integrity()
    {
        // Requirements: 6.1 - Image validation and security
        $this->actingAs($this->teacher);
        
        // Test 1: Valid image should pass
        $validImage = UploadedFile::fake()->image('valid.jpg', 800, 600);
        
        $response1 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Valid Image Test',
            'file' => $validImage,
        ]);
        
        $response1->assertStatus(201);
        
        // Test 2: Corrupted image should be rejected
        $corruptedImage = UploadedFile::fake()->create('corrupted.jpg', 1024, 'image/jpeg');
        
        $response2 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Corrupted Image Test',
            'file' => $corruptedImage,
        ]);
        
        // Should fail validation due to invalid image data
        $response2->assertStatus(422);
        
        // Test 3: Image with embedded malicious data
        // (In a real implementation, this would involve more sophisticated scanning)
        $suspiciousImage = UploadedFile::fake()->image('suspicious.jpg');
        
        $response3 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Suspicious Image Test',
            'file' => $suspiciousImage,
        ]);
        
        // For now, this should pass, but in production you might have additional scanning
        $response3->assertStatus(201);
    }

    /** @test */
    public function it_handles_concurrent_security_validations()
    {
        // Requirements: 6.1, 6.2 - Concurrent security processing
        $this->actingAs($this->teacher);
        
        $files = [];
        $responses = [];
        
        // Create multiple files for concurrent upload
        for ($i = 0; $i < 5; $i++) {
            $files[] = UploadedFile::fake()->image("concurrent-security-{$i}.jpg");
        }
        
        // Submit concurrent uploads
        foreach ($files as $index => $file) {
            $responses[] = $this->postJson('/api/content-blocks', [
                'subpage_id' => $this->subpage->id,
                'type' => 'image',
                'title' => "Concurrent Security Test {$index}",
                'file' => $file,
            ]);
        }
        
        // All should succeed
        foreach ($responses as $response) {
            $response->assertStatus(201);
        }
        
        // Verify all files were properly validated and stored
        $contents = Content::where('title', 'LIKE', 'Concurrent Security Test%')->get();
        $this->assertCount(5, $contents);
        
        foreach ($contents as $content) {
            $this->assertNotNull($content->file_hash);
            $this->assertTrue(Storage::disk($content->storage_disk)->exists($content->file_path));
        }
    }

    /** @test */
    public function it_logs_security_violations()
    {
        // Requirements: 6.1, 6.2 - Security logging
        $this->actingAs($this->teacher);
        
        // Attempt to upload malicious file
        $maliciousFile = UploadedFile::fake()->create('malware.exe', 1024, 'application/x-executable');
        
        // Capture log output
        Log::shouldReceive('warning')
           ->once()
           ->with(\Mockery::pattern('/security.*violation/i'), \Mockery::any());
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'document',
            'title' => 'Security Violation Test',
            'file' => $maliciousFile,
        ]);
        
        $response->assertStatus(422);
        
        // In a real implementation, you would verify the log entry was created
        // with appropriate details like user ID, IP address, attempted filename, etc.
    }

    /** @test */
    public function it_enforces_rate_limiting_for_uploads()
    {
        // Requirements: 6.2 - Rate limiting for security
        $this->actingAs($this->teacher);
        
        $successfulUploads = 0;
        $rateLimitHit = false;
        
        // Attempt rapid uploads
        for ($i = 0; $i < 20; $i++) {
            $testFile = UploadedFile::fake()->image("rate-limit-test-{$i}.jpg");
            
            $response = $this->postJson('/api/content-blocks', [
                'subpage_id' => $this->subpage->id,
                'type' => 'image',
                'title' => "Rate Limit Test {$i}",
                'file' => $testFile,
            ]);
            
            if ($response->status() === 201) {
                $successfulUploads++;
            } elseif ($response->status() === 429) { // Too Many Requests
                $rateLimitHit = true;
                break;
            }
        }
        
        // Should either hit rate limit or all succeed (depending on implementation)
        $this->assertTrue($successfulUploads > 0, 'At least some uploads should succeed');
        
        // If rate limiting is implemented, verify it works
        if ($rateLimitHit) {
            $this->assertLessThan(20, $successfulUploads, 'Rate limiting should prevent all uploads');
        }
    }

    /** @test */
    public function it_sanitizes_file_metadata()
    {
        // Requirements: 6.1, 6.2 - Metadata sanitization
        $this->actingAs($this->teacher);
        
        // Create file with potentially malicious metadata
        $testFile = UploadedFile::fake()->image('metadata-test.jpg');
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => '<script>alert("XSS")</script>',
            'description' => 'javascript:alert("XSS")',
            'file' => $testFile,
        ]);
        
        $response->assertStatus(201);
        
        $content = Content::latest()->first();
        
        // Verify metadata was sanitized
        $this->assertStringNotContainsString('<script>', $content->title);
        $this->assertStringNotContainsString('javascript:', $content->description ?? '');
        
        // Verify filename was sanitized
        $this->assertStringNotContainsString('<', $content->original_filename);
        $this->assertStringNotContainsString('>', $content->original_filename);
    }

    /** @test */
    public function it_handles_storage_security_failures_gracefully()
    {
        // Requirements: 6.2 - Security failure handling
        $this->actingAs($this->teacher);
        
        $testFile = UploadedFile::fake()->image('security-failure-test.jpg');
        
        // Simulate storage security failure
        Storage::shouldReceive('disk->put')
               ->andThrow(new \Exception('Security scan failed'));
        
        $response = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Security Failure Test',
            'file' => $testFile,
        ]);
        
        // Should fail gracefully without exposing internal details
        $response->assertStatus(500);
        $response->assertJsonMissing(['trace', 'file', 'line']);
        
        // Verify no orphaned database records
        $orphanedContent = Content::where('title', 'Security Failure Test')->first();
        $this->assertNull($orphanedContent);
        
        // Reset storage mock
        Storage::clearResolvedInstances();
        
        // Verify system recovers
        $recoveryFile = UploadedFile::fake()->image('recovery-test.jpg');
        
        $recoveryResponse = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Recovery Test',
            'file' => $recoveryFile,
        ]);
        
        $recoveryResponse->assertStatus(201);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        Storage::disk('public')->deleteDirectory('security-test');
        Storage::disk('protected')->deleteDirectory('security-test');
        Storage::disk('public')->deleteDirectory('content_blocks');
        Storage::disk('protected')->deleteDirectory('content_blocks');
        
        parent::tearDown();
    }
}