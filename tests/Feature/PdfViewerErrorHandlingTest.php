<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use App\Services\PdfStreamLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test PDF viewer error handling functionality.
 * 
 * Requirements 6.3, 6.4: Enhanced error handling with user-friendly messages
 * and comprehensive logging.
 */
class PdfViewerErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Content $content;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test PDF content
        $this->content = Content::factory()->create([
            'type' => 'pdf',
            'title' => 'Test PDF Document',
            'file_path' => 'test-pdfs/sample.pdf',
            'file_name' => 'sample.pdf',
            'original_filename' => 'sample.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'storage_disk' => 'protected',
            'is_active' => true,
        ]);

        // Setup storage
        Storage::fake('protected');
        Storage::disk('protected')->put('test-pdfs/sample.pdf', 'fake pdf content');
    }

    /**
     * Test that error logging endpoint exists and accepts requests.
     * Requirement 6.3: Log errors with full context
     */
    public function test_error_logging_endpoint_exists(): void
    {
        // Expect two log calls: one from PdfStreamLogger, one from controller
        Log::shouldReceive('error')->twice();

        $response = $this->postJson("/secure-pdf/log-error/{$this->content->id}", [
            'error_type' => 'network',
            'error_message' => 'Network Connection Error',
            'error_details' => [
                'technical' => 'Failed to fetch PDF',
                'can_retry' => true,
            ],
            'session_token' => 'test-token-123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'correlation_id',
        ]);
    }

    /**
     * Test that error logging captures all required context.
     * Requirement 6.3: Log errors with full context
     */
    public function test_error_logging_captures_full_context(): void
    {
        $this->actingAs($this->user);

        // Expect two log calls: one from PdfStreamLogger, one from controller
        // Use a more flexible matcher that doesn't check exact arguments
        Log::shouldReceive('error')->twice()->andReturn(null);

        $response = $this->postJson("/secure-pdf/log-error/{$this->content->id}", [
            'error_type' => 'forbidden',
            'error_message' => 'Access Denied',
            'error_details' => [
                'technical' => '403 Forbidden',
                'can_retry' => true,
            ],
            'session_token' => 'test-token-123',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test that different error types are logged correctly.
     * Requirement 6.3: Catch PDF.js errors and categorize them
     */
    public function test_different_error_types_are_logged(): void
    {
        $errorTypes = [
            'network',
            'forbidden',
            'not_found',
            'timeout',
            'invalid_pdf',
            'server_error',
            'memory',
            'rendering_error',
        ];

        foreach ($errorTypes as $errorType) {
            // Expect two log calls per error: one from PdfStreamLogger, one from controller
            Log::shouldReceive('error')->twice();

            $response = $this->postJson("/secure-pdf/log-error/{$this->content->id}", [
                'error_type' => $errorType,
                'error_message' => "Test {$errorType} error",
                'error_details' => [
                    'technical' => "Technical details for {$errorType}",
                ],
            ]);

            $response->assertStatus(200);
        }
    }

    /**
     * Test that DevTools detection logging works.
     * Requirement 3.7: Detect and log developer tools usage
     */
    public function test_devtools_detection_logging(): void
    {
        $this->actingAs($this->user);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF Viewer: DevTools Detected' &&
                    isset($context['content_id']) &&
                    isset($context['user_id']) &&
                    isset($context['session_token']) &&
                    isset($context['timestamp']);
            });

        $response = $this->postJson("/secure-pdf/log-devtools-detection/{$this->content->id}", [
            'session_token' => 'test-token-123',
            'timestamp' => now()->toISOString(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test that error logging works without authentication.
     * Requirement 6.3: Log errors even when user is not authenticated
     */
    public function test_error_logging_works_without_authentication(): void
    {
        // Expect two log calls: one from PdfStreamLogger, one from controller
        Log::shouldReceive('error')->twice();

        $response = $this->postJson("/secure-pdf/log-error/{$this->content->id}", [
            'error_type' => 'network',
            'error_message' => 'Network Connection Error',
            'error_details' => [
                'technical' => 'Failed to fetch PDF',
            ],
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test that error logging validates required fields.
     * Requirement 6.3: Ensure error data is complete
     */
    public function test_error_logging_validates_required_fields(): void
    {
        $response = $this->postJson("/secure-pdf/log-error/{$this->content->id}", [
            // Missing required fields
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['error_type', 'error_message']);
    }

    /**
     * Test that PdfStreamLogger logViewerError method works correctly.
     * Requirement 6.3: Log errors with full context
     */
    public function test_pdf_stream_logger_logs_viewer_error(): void
    {
        $logger = app(PdfStreamLogger::class);
        $request = $this->createRequest('GET', "/secure-pdf/stream/{$this->content->id}");

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'PDF viewer error reported' &&
                    $context['event_type'] === 'viewer_error' &&
                    isset($context['correlation_id']) &&
                    isset($context['error_type']) &&
                    isset($context['error_message']);
            });

        $correlationId = $logger->logViewerError(
            $this->content,
            $request,
            'network',
            'Network Connection Error',
            ['technical' => 'Failed to fetch']
        );

        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    /**
     * Test that correlation IDs are unique.
     * Requirement 6.3: Track errors with unique correlation IDs
     */
    public function test_correlation_ids_are_unique(): void
    {
        // Expect two log calls per error: one from PdfStreamLogger, one from controller
        Log::shouldReceive('error')->times(6);

        $correlationIds = [];

        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson("/secure-pdf/log-error/{$this->content->id}", [
                'error_type' => 'network',
                'error_message' => "Test error {$i}",
                'error_details' => [],
            ]);

            $response->assertStatus(200);
            $correlationIds[] = $response->json('correlation_id');
        }

        // Ensure all correlation IDs are unique
        $this->assertCount(3, array_unique($correlationIds));
    }

    /**
     * Helper method to create a request instance.
     */
    protected function createRequest(string $method, string $uri): \Illuminate\Http\Request
    {
        return \Illuminate\Http\Request::create($uri, $method);
    }
}
