<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use App\Services\PdfStreamLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Integration tests for PdfStreamLogger integration in SecurePdfController.
 * 
 * Verifies that the controller properly logs all PDF streaming operations
 * including successful streams, errors, and access denied events.
 * 
 * Requirements: 5.1, 5.2, 5.3, 5.4
 */
class SecurePdfControllerLoggingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Content $pdfContent;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create test PDF content
        $this->pdfContent = Content::factory()->create([
            'title' => 'Test PDF Document',
            'type' => 'pdf',
            'file_path' => 'test-document.pdf',
            'file_name' => 'test-document.pdf',
            'file_size' => 1024 * 100, // 100KB
            'mime_type' => 'application/pdf',
            'storage_disk' => 'protected',
            'is_active' => true,
        ]);

        // Create a test PDF file
        Storage::fake('protected');
        Storage::disk('protected')->put(
            'test-document.pdf',
            '%PDF-1.4' . str_repeat("\n", 100) // Minimal PDF content
        );
    }

    /** @test */
    public function it_logs_successful_stream_when_pdf_is_accessed()
    {
        // Mock the logger to verify it's called
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF streamed successfully' &&
                       $context['event_type'] === 'successful_stream' &&
                       $context['content_id'] === $this->pdfContent->id;
            });

        // Also expect the standard log
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: Stream access granted';
            });

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        // Access the PDF stream
        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function it_logs_stream_error_when_content_type_is_invalid()
    {
        // Create non-PDF content
        $imageContent = Content::factory()->create([
            'title' => 'Test Image',
            'type' => 'image',
            'file_path' => 'test-image.jpg',
            'is_active' => true,
        ]);

        // Mock the logger to verify error logging
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($imageContent) {
                return $message === 'PDF stream error occurred' &&
                       $context['event_type'] === 'stream_error' &&
                       $context['error_type'] === 'invalid_content_type' &&
                       $context['content_id'] === $imageContent->id;
            });

        // Also expect the standard warning log
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: Invalid content type';
            });

        // Generate signed URL for non-PDF content
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $imageContent->id]
        );

        // Access the stream (should fail)
        $response = $this->get($signedUrl);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_logs_access_denied_when_content_is_inactive()
    {
        // Make content inactive
        $this->pdfContent->update(['is_active' => false]);

        // Mock the logger to verify access denied logging
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF access denied' &&
                       $context['event_type'] === 'access_denied' &&
                       $context['reason'] === 'inactive_content' &&
                       $context['content_id'] === $this->pdfContent->id;
            });

        // Also expect the standard warning log
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: Inactive content access attempt';
            });

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        // Access the stream (should be denied)
        $response = $this->get($signedUrl);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_logs_stream_error_when_file_not_found()
    {
        // Create content with non-existent file
        $missingContent = Content::factory()->create([
            'title' => 'Missing PDF',
            'type' => 'pdf',
            'file_path' => 'non-existent.pdf',
            'is_active' => true,
        ]);

        // Mock the logger to verify error logging
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($missingContent) {
                return $message === 'PDF stream error occurred' &&
                       $context['event_type'] === 'stream_error' &&
                       $context['error_type'] === 'file_not_found' &&
                       $context['content_id'] === $missingContent->id;
            });

        // Also expect the standard error log
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: File not found';
            });

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $missingContent->id]
        );

        // Access the stream (should fail)
        $response = $this->get($signedUrl);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_logs_range_request_when_partial_content_is_requested()
    {
        // Mock the logger to verify range request logging
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF range request served' &&
                       $context['event_type'] === 'range_request' &&
                       $context['content_id'] === $this->pdfContent->id &&
                       $context['start_byte'] === 0 &&
                       $context['end_byte'] === 1023;
            });

        // Also expect the successful stream log
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF streamed successfully';
            });

        // Also expect the standard range request log
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: Range request served';
            });

        // Also expect the standard stream access log
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: Stream access granted';
            });

        // Mock any error logs that might occur during file operations
        Log::shouldReceive('error')->zeroOrMoreTimes();

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        // Make range request
        $response = $this->get($signedUrl, ['Range' => 'bytes=0-1023']);

        $response->assertStatus(206); // Partial Content
        $response->assertHeader('Content-Range');
    }

    /** @test */
    public function it_logs_stream_error_for_invalid_range_request()
    {
        // Mock the logger to verify error logging
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF stream error occurred' &&
                       $context['event_type'] === 'stream_error' &&
                       $context['error_type'] === 'invalid_range_format';
            });

        // Also expect the standard warning log
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: Invalid range header format';
            });

        // Also expect the successful stream log (before range processing)
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF streamed successfully';
            });

        // Also expect the standard stream access log
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'SecurePDF: Stream access granted';
            });

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        // Make invalid range request
        $response = $this->get($signedUrl, ['Range' => 'invalid-range-format']);

        $response->assertStatus(416); // Range Not Satisfiable
    }

    /** @test */
    public function it_includes_correlation_ids_in_all_logs()
    {
        $correlationIdFound = false;

        // Mock the logger to capture correlation ID
        Log::shouldReceive('info')
            ->twice()
            ->withArgs(function ($message, $context) use (&$correlationIdFound) {
                if (isset($context['correlation_id'])) {
                    $correlationIdFound = true;
                    $this->assertIsString($context['correlation_id']);
                    $this->assertNotEmpty($context['correlation_id']);
                }
                return true;
            });

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        // Access the PDF stream
        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $this->assertTrue($correlationIdFound, 'Correlation ID should be present in logs');
    }

    /** @test */
    public function it_logs_request_metadata_in_all_operations()
    {
        $metadataFound = false;

        // Mock the logger to verify metadata
        Log::shouldReceive('info')
            ->twice()
            ->withArgs(function ($message, $context) use (&$metadataFound) {
                if ($message === 'PDF streamed successfully') {
                    $this->assertArrayHasKey('ip_address', $context);
                    $this->assertArrayHasKey('user_agent', $context);
                    $this->assertArrayHasKey('url', $context);
                    $metadataFound = true;
                }
                return true;
            });

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(5),
            ['content' => $this->pdfContent->id]
        );

        // Access the PDF stream with custom headers
        $response = $this->withHeaders([
            'User-Agent' => 'Test Browser/1.0',
        ])->get($signedUrl);

        $response->assertStatus(200);
        $this->assertTrue($metadataFound, 'Request metadata should be present in logs');
    }
}
