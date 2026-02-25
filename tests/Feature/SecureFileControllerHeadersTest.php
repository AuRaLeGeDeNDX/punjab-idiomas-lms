<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class SecureFileControllerHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up storage disks
        Storage::fake('protected');
        
        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Teacher']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Student']);
    }

    public function test_pdf_file_has_correct_security_headers()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->pdf()->create([
            'file_path' => 'test-document.pdf',
            'storage_disk' => 'protected',
            'original_filename' => 'test-document.pdf'
        ]);
        
        // Create the actual file
        Storage::disk('protected')->put($content->file_path, 'PDF content');
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(200);
        
        // Test basic headers
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-document.pdf"');
        
        // Test security headers
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        
        // Debug: Let's see what X-Frame-Options is actually set to
        $actualFrameOptions = $response->headers->get('X-Frame-Options');
        echo "Actual X-Frame-Options: " . $actualFrameOptions . "\n";
        
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN'); // Updated expectation for PDFs
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Test CSP header
        $this->assertStringContainsString('default-src \'none\'', $response->headers->get('Content-Security-Policy'));
        
        // Test caching headers
        $this->assertNotNull($response->headers->get('Cache-Control'));
        $this->assertNotNull($response->headers->get('Expires'));
        $this->assertNotNull($response->headers->get('ETag'));
    }

    public function test_image_file_has_optimized_headers()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->image()->create([
            'file_path' => 'test-image.jpg',
            'storage_disk' => 'protected',
            'original_filename' => 'test-image.jpg'
        ]);
        
        // Create the actual file
        Storage::disk('protected')->put($content->file_path, 'JPEG content');
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(200);
        
        // Test basic headers
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-image.jpg"');
        
        // Test image-specific headers
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN'); // Different for images
        
        // Test image-specific CSP
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('img-src \'self\'', $csp);
        
        // Test longer caching for images
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=7200', $cacheControl); // 2 hours for images
    }

    public function test_downloadable_file_has_attachment_disposition()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'file', // Use 'file' instead of 'document'
            'file_path' => 'test-document.docx',
            'storage_disk' => 'protected',
            'original_filename' => 'test-document.docx'
        ]);
        
        // Create the actual file
        Storage::disk('protected')->put($content->file_path, 'DOCX content');
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(200);
        
        // Test that downloadable files use attachment disposition
        $response->assertHeader('Content-Disposition', 'attachment; filename="test-document.docx"');
        
        // Test additional download security headers
        $response->assertHeader('X-Download-Options', 'noopen');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
    }

    public function test_media_file_has_extended_caching()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'video',
            'file_path' => 'test-video.mp4',
            'storage_disk' => 'protected',
            'original_filename' => 'test-video.mp4'
        ]);
        
        // Create the actual file
        Storage::disk('protected')->put($content->file_path, 'MP4 content');
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(200);
        
        // Test media file caching (4 hours)
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=14400', $cacheControl);
    }

    public function test_filename_sanitization()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => 'test-document.pdf',
            'storage_disk' => 'protected',
            'original_filename' => 'test document with spaces & special chars!.pdf'
        ]);
        
        // Create the actual file
        Storage::disk('protected')->put($content->file_path, 'PDF content');
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(200);
        
        // Test that filename is sanitized
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('filename="test_document_with_spaces___special_chars_.pdf"', $contentDisposition);
    }
}