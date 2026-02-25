<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

/**
 * Test PDF Stream Range Request Support
 * 
 * Validates that the stream method properly handles:
 * - HTTP range requests for partial content
 * - CORS headers for cross-origin requests
 * - Content-Type header is set correctly
 * - Accept-Ranges header is present
 * 
 * Requirements 3.1, 3.2, 3.4
 */
class PdfStreamRangeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Content $pdfContent;
    protected string $testPdfContent;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test PDF content (1000 bytes for range testing)
        $this->testPdfContent = '%PDF-1.4' . str_repeat('X', 992);
        
        // Create test PDF file
        Storage::fake('protected');
        $pdfPath = 'test-pdfs/range-test.pdf';
        Storage::disk('protected')->put($pdfPath, $this->testPdfContent);

        // Create PDF content
        $this->pdfContent = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => $pdfPath,
            'file_name' => 'range-test.pdf',
            'original_filename' => 'range-test.pdf',
            'file_size' => strlen($this->testPdfContent),
            'mime_type' => 'application/pdf',
            'storage_disk' => 'protected',
            'is_active' => true,
        ]);
    }

    /**
     * Test that Content-Type header is set to application/pdf.
     * 
     * Requirement 3.1: Content-Type must be application/pdf
     * 
     * @test
     */
    public function test_content_type_header_is_application_pdf()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that Accept-Ranges header is present.
     * 
     * Requirement 3.4: Support range requests
     * 
     * @test
     */
    public function test_accept_ranges_header_is_present()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Accept-Ranges', 'bytes');
    }

    /**
     * Test that CORS headers are present.
     * 
     * Requirement 3.2: Add CORS headers if needed
     * 
     * @test
     */
    public function test_cors_headers_are_present()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->withHeaders([
            'Origin' => 'http://localhost:8000',
        ])->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Expose-Headers');
    }

    /**
     * Test basic range request (first 100 bytes).
     * 
     * Requirement 3.4: Support HTTP range requests
     * 
     * @test
     */
    public function test_range_request_first_100_bytes()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->withHeaders([
            'Range' => 'bytes=0-99',
        ])->get($signedUrl);

        $response->assertStatus(206); // Partial Content
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Range', 'bytes 0-99/1000');
        $response->assertHeader('Content-Length', '100');
        
        // Verify content is correct
        $this->assertEquals(
            substr($this->testPdfContent, 0, 100),
            $response->getContent()
        );
    }

    /**
     * Test range request for middle bytes.
     * 
     * Requirement 3.4: Support HTTP range requests
     * 
     * @test
     */
    public function test_range_request_middle_bytes()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->withHeaders([
            'Range' => 'bytes=100-199',
        ])->get($signedUrl);

        $response->assertStatus(206);
        $response->assertHeader('Content-Range', 'bytes 100-199/1000');
        $response->assertHeader('Content-Length', '100');
        
        // Verify content is correct
        $this->assertEquals(
            substr($this->testPdfContent, 100, 100),
            $response->getContent()
        );
    }

    /**
     * Test range request to end of file.
     * 
     * Requirement 3.4: Support HTTP range requests
     * 
     * @test
     */
    public function test_range_request_to_end_of_file()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Request from byte 900 to end (no end specified)
        $response = $this->withHeaders([
            'Range' => 'bytes=900-',
        ])->get($signedUrl);

        $response->assertStatus(206);
        $response->assertHeader('Content-Range', 'bytes 900-999/1000');
        $response->assertHeader('Content-Length', '100');
        
        // Verify content is correct
        $this->assertEquals(
            substr($this->testPdfContent, 900),
            $response->getContent()
        );
    }

    /**
     * Test invalid range request (start > end).
     * 
     * Requirement 3.4: Handle invalid range requests gracefully
     * 
     * @test
     */
    public function test_invalid_range_request_start_greater_than_end()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->withHeaders([
            'Range' => 'bytes=500-100',
        ])->get($signedUrl);

        $response->assertStatus(416); // Range Not Satisfiable
        $response->assertHeader('Content-Range', 'bytes */1000');
    }

    /**
     * Test range request beyond file size.
     * 
     * Requirement 3.4: Handle invalid range requests gracefully
     * 
     * @test
     */
    public function test_range_request_beyond_file_size()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->withHeaders([
            'Range' => 'bytes=1000-2000',
        ])->get($signedUrl);

        $response->assertStatus(416); // Range Not Satisfiable
        $response->assertHeader('Content-Range', 'bytes */1000');
    }

    /**
     * Test invalid range header format.
     * 
     * Requirement 3.4: Handle invalid range requests gracefully
     * 
     * @test
     */
    public function test_invalid_range_header_format()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->withHeaders([
            'Range' => 'invalid-format',
        ])->get($signedUrl);

        $response->assertStatus(416); // Range Not Satisfiable
        $response->assertHeader('Content-Range', 'bytes */1000');
    }

    /**
     * Test multiple range requests with same signed URL.
     * 
     * PDF.js makes multiple range requests for the same PDF.
     * All requests should succeed.
     * 
     * Requirement 3.4: Support multiple range requests
     * 
     * @test
     */
    public function test_multiple_range_requests_with_same_signed_url()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // First range request
        $response1 = $this->withHeaders([
            'Range' => 'bytes=0-99',
        ])->get($signedUrl);
        $response1->assertStatus(206);

        // Second range request
        $response2 = $this->withHeaders([
            'Range' => 'bytes=100-199',
        ])->get($signedUrl);
        $response2->assertStatus(206);

        // Third range request
        $response3 = $this->withHeaders([
            'Range' => 'bytes=200-299',
        ])->get($signedUrl);
        $response3->assertStatus(206);

        // All requests should succeed
        $this->assertTrue(true, 'All three range requests succeeded');
    }

    /**
     * Test full file request without Range header.
     * 
     * When no Range header is present, return the full file.
     * 
     * @test
     */
    public function test_full_file_request_without_range_header()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200); // OK, not 206
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Accept-Ranges', 'bytes');
        $response->assertHeader('Content-Length', '1000');
    }

    /**
     * Test that range requests work without authentication.
     * 
     * Range requests should work with signed URLs without session.
     * 
     * @test
     */
    public function test_range_request_works_without_authentication()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Make range request without authentication
        $response = $this->withHeaders([
            'Range' => 'bytes=0-99',
        ])->get($signedUrl);

        $response->assertStatus(206);
        $response->assertHeader('Content-Range', 'bytes 0-99/1000');
    }

    /**
     * Test CORS headers are present in range responses.
     * 
     * Requirement 3.2: CORS headers should be present in all responses
     * 
     * @test
     */
    public function test_cors_headers_present_in_range_response()
    {
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        $response = $this->withHeaders([
            'Range' => 'bytes=0-99',
            'Origin' => 'http://localhost:8000',
        ])->get($signedUrl);

        $response->assertStatus(206);
        $response->assertHeader('Access-Control-Allow-Origin');
        $response->assertHeader('Access-Control-Expose-Headers');
    }
}
