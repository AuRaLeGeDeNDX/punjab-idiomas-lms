<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

/**
 * Test PDF Stream Session Independence
 * 
 * Validates that signed URLs work without requiring active sessions:
 * - Signed URLs work without cookies
 * - Signed URLs work without authenticated sessions
 * - Signature validation is independent of session state
 * 
 * Requirement 4.1: Signed URLs should work without active session authentication
 */
class PdfStreamSessionIndependenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Content $pdfContent;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test PDF file
        Storage::fake('protected');
        $pdfPath = 'test-pdfs/test-document.pdf';
        Storage::disk('protected')->put($pdfPath, '%PDF-1.4 test content');

        // Create PDF content using factory
        $this->pdfContent = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => $pdfPath,
            'file_name' => 'test-document.pdf',
            'original_filename' => 'test-document.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
            'storage_disk' => 'protected',
            'is_active' => true,
        ]);
    }

    /**
     * Test that signed URLs work without an authenticated session.
     * 
     * This is the core requirement: PDF.js should be able to fetch PDFs
     * using only the signed URL, without needing session cookies.
     * 
     * @test
     */
    public function test_signed_url_works_without_authenticated_session()
    {
        // Generate a signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Make request WITHOUT authentication (no actingAs)
        // This simulates PDF.js making a request without session cookies
        $response = $this->withoutMiddleware(\App\Http\Middleware\Authenticate::class)
            ->get($signedUrl);

        // Should succeed with 200 OK
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that signed URLs work without cookies.
     * 
     * PDF.js may not send cookies with fetch requests,
     * so the signed URL must work without any cookie data.
     * 
     * @test
     */
    public function test_signed_url_works_without_cookies()
    {
        // Generate a signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Make request without cookies by not calling actingAs
        // This simulates PDF.js making a request without session cookies
        $response = $this->get($signedUrl);

        // Should succeed with 200 OK
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that signed URLs work when session is started but user is not authenticated.
     * 
     * This tests the scenario where the session middleware runs
     * but there's no authenticated user in the session.
     * 
     * @test
     */
    public function test_signed_url_works_with_session_but_no_auth()
    {
        // Generate a signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Start a session but don't authenticate
        // This simulates having session cookies but no logged-in user
        $response = $this->withSession([])
            ->get($signedUrl);

        // Should succeed with 200 OK
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that expired signed URLs are rejected regardless of session state.
     * 
     * Even without authentication, expired signatures should be rejected.
     * 
     * @test
     */
    public function test_expired_signed_url_rejected_without_session()
    {
        // Generate an expired signed URL
        $expiredUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->subMinutes(10), // Expired 10 minutes ago
            ['content' => $this->pdfContent->id]
        );

        // Make request without authentication
        $response = $this->get($expiredUrl);

        // Should be rejected with 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that tampered signed URLs are rejected without session.
     * 
     * Signature validation should work independently of session state.
     * 
     * @test
     */
    public function test_tampered_signed_url_rejected_without_session()
    {
        // Generate a valid signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Parse the URL and tamper with the signature parameter
        $parsedUrl = parse_url($signedUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        
        // Tamper with the signature by changing a few characters
        $originalSignature = $queryParams['signature'];
        $tamperedSignature = substr($originalSignature, 0, -5) . 'XXXXX';
        
        // Reconstruct the URL with tampered signature
        $tamperedUrl = $parsedUrl['scheme'] . '://' . 
                      $parsedUrl['host'] . 
                      ($parsedUrl['port'] ?? null ? ':' . $parsedUrl['port'] : '') .
                      $parsedUrl['path'] . '?' . 
                      'signature=' . $tamperedSignature . 
                      '&expires=' . $queryParams['expires'] . 
                      '&content=' . $this->pdfContent->id;

        // Make request without authentication
        $response = $this->get($tamperedUrl);

        // Should be rejected with 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that signed URLs work with authenticated user.
     * 
     * Signed URLs should also work when the user IS authenticated,
     * providing flexibility for both scenarios.
     * 
     * @test
     */
    public function test_signed_url_works_with_authenticated_user()
    {
        // Generate a signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Make request WITH authentication
        $response = $this->actingAs($this->user)
            ->get($signedUrl);

        // Should succeed with 200 OK
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test that multiple requests with the same signed URL work without session.
     * 
     * PDF.js makes multiple range requests for the same PDF.
     * All requests should succeed with the same signed URL.
     * 
     * Requirement 1.4: Multiple requests should succeed
     * 
     * @test
     */
    public function test_multiple_requests_with_same_signed_url_without_session()
    {
        // Generate a signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Make first request without authentication
        $response1 = $this->get($signedUrl);
        $response1->assertStatus(200);

        // Make second request without authentication
        $response2 = $this->get($signedUrl);
        $response2->assertStatus(200);

        // Make third request without authentication
        $response3 = $this->get($signedUrl);
        $response3->assertStatus(200);

        // All requests should succeed
        $this->assertTrue(true, 'All three requests succeeded');
    }

    /**
     * Test that CSRF token is not required for signed URL requests.
     * 
     * The route should be excluded from CSRF verification.
     * 
     * @test
     */
    public function test_signed_url_does_not_require_csrf_token()
    {
        // Generate a signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $this->pdfContent->id]
        );

        // Make request without CSRF token (no session means no CSRF token)
        $response = $this->get($signedUrl);

        // Should succeed with 200 OK
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
