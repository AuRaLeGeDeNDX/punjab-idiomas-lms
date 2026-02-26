<?php

namespace Tests\Feature;

use App\Http\Controllers\SecurePdfController;
use App\Models\Content;
use App\Models\User;
use App\Services\PdfStreamLogger;
use App\Services\SecurePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Unit tests for SecurePdfController error response formatting.
 * 
 * Verifies that error responses are properly formatted for PDF.js compatibility.
 * 
 * Requirement 6.3: Provide error responses that PDF.js can interpret
 */
class SecurePdfErrorResponseFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Content $pdfContent;
    protected SecurePdfController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test PDF content
        $this->pdfContent = Content::factory()->create([
            'type' => 'pdf',
            'title' => 'Test PDF',
            'file_path' => 'test.pdf',
            'file_name' => 'test.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'storage_disk' => 'protected',
            'is_active' => true,
        ]);

        // Setup storage
        Storage::fake('protected');
        Storage::disk('protected')->put('test.pdf', 'fake pdf content');

        // Create controller instance
        $pdfService = app(SecurePdfService::class);
        $logger = app(PdfStreamLogger::class);
        $this->controller = new SecurePdfController($pdfService, $logger);
    }

    /**
     * Test that error responses return appropriate HTTP status codes.
     * Requirement 6.3: Return appropriate HTTP status codes
     */
    public function test_error_responses_return_correct_status_codes(): void
    {
        // Test 404 for invalid content type
        $imageContent = Content::factory()->create([
            'type' => 'image',
            'title' => 'Test Image',
            'file_path' => 'test.jpg',
            'is_active' => true,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $imageContent->id]
        );

        $response = $this->get($signedUrl);
        $response->assertStatus(404);

        // Test 403 for inactive content
        $this->pdfContent->update(['is_active' => false]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->get($signedUrl);
        $response->assertStatus(403);
    }

    /**
     * Test that error responses include descriptive error messages.
     * Requirement 6.3: Provide descriptive error messages
     */
    public function test_error_responses_include_descriptive_messages(): void
    {
        // Test error message for invalid content type
        $imageContent = Content::factory()->create([
            'type' => 'image',
            'title' => 'Test Image',
            'file_path' => 'test.jpg',
            'is_active' => true,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $imageContent->id]
        );

        $response = $this->get($signedUrl);
        $response->assertStatus(404);
        
        // Response should contain a descriptive error message
        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('document', strtolower($content));
    }

    /**
     * Test that error responses are compatible with PDF.js.
     * Requirement 6.3: Format errors for PDF.js compatibility
     */
    public function test_error_responses_are_pdfjs_compatible(): void
    {
        // PDF.js expects plain text or JSON error responses with proper status codes
        $imageContent = Content::factory()->create([
            'type' => 'image',
            'title' => 'Test Image',
            'file_path' => 'test.jpg',
            'is_active' => true,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $imageContent->id]
        );

        // Test plain text response (default)
        $response = $this->get($signedUrl);
        $response->assertStatus(404);
        $this->assertTrue(
            str_starts_with($response->headers->get('Content-Type'), 'text/plain'),
            'Content-Type should be text/plain'
        );
        $response->assertHeader('X-Error-Type');
        $response->assertHeader('X-Correlation-ID');

        // Test JSON response (when Accept: application/json)
        $response = $this->get($signedUrl, ['Accept' => 'application/json']);
        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'error',
            'error_type',
            'message',
            'status_code',
            'correlation_id',
            'timestamp',
        ]);
    }

    /**
     * Test that error responses include correlation IDs for tracking.
     * Requirement 6.3: Include correlation IDs for debugging
     */
    public function test_error_responses_include_correlation_ids(): void
    {
        $imageContent = Content::factory()->create([
            'type' => 'image',
            'title' => 'Test Image',
            'file_path' => 'test.jpg',
            'is_active' => true,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $imageContent->id]
        );

        // Test plain text response
        $response = $this->get($signedUrl);
        $response->assertStatus(404);
        $correlationId = $response->headers->get('X-Correlation-ID');
        $this->assertNotNull($correlationId);
        $this->assertNotEmpty($correlationId);
        $this->assertNotEquals('none', $correlationId);

        // Test JSON response
        $response = $this->get($signedUrl, ['Accept' => 'application/json']);
        $response->assertStatus(404);
        $responseData = $response->json();
        $this->assertArrayHasKey('correlation_id', $responseData);
        $this->assertNotEmpty($responseData['correlation_id']);
    }

    /**
     * Test that range request errors return proper headers.
     * Requirement 6.3: Support range request error responses
     */
    public function test_range_request_errors_return_proper_headers(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        // Test invalid range format
        $response = $this->get($signedUrl, ['Range' => 'invalid-format']);
        $response->assertStatus(416);
        $this->assertTrue(
            str_starts_with($response->headers->get('Content-Type'), 'text/plain'),
            'Content-Type should be text/plain'
        );
        $response->assertHeader('Content-Range');
        $response->assertHeader('X-Error-Type', 'invalid_range_format');
        $response->assertHeader('X-Correlation-ID');
    }

    /**
     * Test that file not found errors return 404.
     * Requirement 6.3: Return appropriate status codes for missing files
     */
    public function test_file_not_found_returns_404(): void
    {
        $missingContent = Content::factory()->create([
            'type' => 'pdf',
            'title' => 'Missing PDF',
            'file_path' => 'non-existent.pdf',
            'is_active' => true,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $missingContent->id]
        );

        $response = $this->get($signedUrl);
        $response->assertStatus(404);
        
        // Should include descriptive error message
        $content = $response->getContent();
        $this->assertStringContainsString('document', strtolower($content));
        $this->assertStringContainsString('could not be found', strtolower($content));
    }

    /**
     * Test that inactive content returns 403.
     * Requirement 6.3: Return appropriate status codes for access denied
     */
    public function test_inactive_content_returns_403(): void
    {
        $this->pdfContent->update(['is_active' => false]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->get($signedUrl);
        $response->assertStatus(403);
        
        // Should include descriptive error message
        $content = $response->getContent();
        $this->assertStringContainsString('not', strtolower($content));
        $this->assertStringContainsString('available', strtolower($content));
    }
}
