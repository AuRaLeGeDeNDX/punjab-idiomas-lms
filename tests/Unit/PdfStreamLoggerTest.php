<?php

namespace Tests\Unit;

use App\Models\Content;
use App\Models\User;
use App\Services\PdfStreamLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit tests for PdfStreamLogger service.
 * 
 * Tests comprehensive logging functionality for PDF streaming operations
 * including signature failures, successful streams, and error logging.
 * 
 * Requirements: 5.1, 5.2, 5.3, 5.4
 */
class PdfStreamLoggerTest extends TestCase
{
    use RefreshDatabase;

    private PdfStreamLogger $logger;
    private User $user;
    private Content $content;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->logger = new PdfStreamLogger();
        
        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Create test PDF content
        $this->content = Content::factory()->create([
            'title' => 'Test PDF Document',
            'type' => 'pdf',
            'file_path' => 'test-document.pdf',
            'file_size' => 1024 * 1024, // 1MB
            'mime_type' => 'application/pdf',
            'storage_disk' => 'protected',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_logs_signature_validation_failure_with_all_required_details()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify log message
                $this->assertEquals('PDF stream signature validation failed', $message);
                
                // Verify required fields are present
                $this->assertArrayHasKey('correlation_id', $context);
                $this->assertArrayHasKey('timestamp', $context);
                $this->assertArrayHasKey('event_type', $context);
                $this->assertArrayHasKey('reason', $context);
                $this->assertArrayHasKey('url', $context);
                $this->assertArrayHasKey('signature', $context);
                $this->assertArrayHasKey('expires', $context);
                $this->assertArrayHasKey('ip_address', $context);
                $this->assertArrayHasKey('user_agent', $context);
                
                // Verify event type
                $this->assertEquals('signature_validation_failure', $context['event_type']);
                
                // Verify reason
                $this->assertEquals('invalid_signature', $context['reason']);
                
                return true;
            });

        $request = Request::create(
            'https://example.com/secure-pdf/stream/1?signature=invalid&expires=1234567890',
            'GET'
        );
        $request->headers->set('User-Agent', 'Mozilla/5.0');

        $correlationId = $this->logger->logSignatureFailure($request, 'invalid_signature');

        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    /** @test */
    public function it_logs_signature_failure_with_expiration_details()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify expiration details
                $this->assertArrayHasKey('expires', $context);
                $this->assertArrayHasKey('expires_formatted', $context);
                $this->assertArrayHasKey('current_time', $context);
                $this->assertArrayHasKey('is_expired', $context);
                
                // Verify expiration check
                $this->assertTrue($context['is_expired']);
                
                return true;
            });

        $expiredTime = time() - 3600; // 1 hour ago
        $request = Request::create(
            "https://example.com/secure-pdf/stream/1?signature=test&expires={$expiredTime}",
            'GET'
        );

        $this->logger->logSignatureFailure($request, 'expired_signature');
    }

    /** @test */
    public function it_logs_signature_failure_with_user_information_when_authenticated()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify user information
                $this->assertArrayHasKey('user_id', $context);
                $this->assertArrayHasKey('user_email', $context);
                $this->assertArrayHasKey('authenticated', $context);
                
                $this->assertEquals($this->user->id, $context['user_id']);
                $this->assertEquals($this->user->email, $context['user_email']);
                $this->assertTrue($context['authenticated']);
                
                return true;
            });

        $this->actingAs($this->user);

        $request = Request::create(
            'https://example.com/secure-pdf/stream/1?signature=invalid',
            'GET'
        );

        $this->logger->logSignatureFailure($request, 'invalid_signature');
    }

    /** @test */
    public function it_logs_signature_failure_with_request_headers()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify headers are logged
                $this->assertArrayHasKey('headers', $context);
                $this->assertArrayHasKey('Accept', $context['headers']);
                $this->assertArrayHasKey('Range', $context['headers']);
                $this->assertArrayHasKey('Cookie', $context['headers']);
                
                $this->assertEquals('application/pdf', $context['headers']['Accept']);
                $this->assertEquals('bytes=0-1023', $context['headers']['Range']);
                
                return true;
            });

        $request = Request::create(
            'https://example.com/secure-pdf/stream/1?signature=invalid',
            'GET'
        );
        $request->headers->set('Accept', 'application/pdf');
        $request->headers->set('Range', 'bytes=0-1023');

        $this->logger->logSignatureFailure($request, 'invalid_signature');
    }

    /** @test */
    public function it_logs_successful_stream_with_all_required_details()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify log message
                $this->assertEquals('PDF streamed successfully', $message);
                
                // Verify required fields
                $this->assertArrayHasKey('correlation_id', $context);
                $this->assertArrayHasKey('timestamp', $context);
                $this->assertArrayHasKey('event_type', $context);
                $this->assertArrayHasKey('content_id', $context);
                $this->assertArrayHasKey('content_title', $context);
                $this->assertArrayHasKey('file_size', $context);
                $this->assertArrayHasKey('file_size_formatted', $context);
                $this->assertArrayHasKey('url', $context);
                $this->assertArrayHasKey('ip_address', $context);
                
                // Verify event type
                $this->assertEquals('successful_stream', $context['event_type']);
                
                // Verify content details
                $this->assertEquals($this->content->id, $context['content_id']);
                $this->assertEquals($this->content->title, $context['content_title']);
                $this->assertEquals($this->content->file_size, $context['file_size']);
                
                return true;
            });

        $request = Request::create(
            'https://example.com/secure-pdf/stream/1?signature=valid&expires=9999999999',
            'GET'
        );
        $request->server->set('REMOTE_ADDR', '192.168.1.1');

        $correlationId = $this->logger->logSuccessfulStream($this->content, $request);

        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    /** @test */
    public function it_logs_successful_stream_with_formatted_file_size()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify file size formatting
                $this->assertArrayHasKey('file_size_formatted', $context);
                $this->assertEquals('1 MB', $context['file_size_formatted']);
                
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');

        $this->logger->logSuccessfulStream($this->content, $request);
    }

    /** @test */
    public function it_logs_successful_stream_with_range_request_information()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify range request details
                $this->assertArrayHasKey('is_range_request', $context);
                $this->assertArrayHasKey('range_header', $context);
                
                $this->assertTrue($context['is_range_request']);
                $this->assertEquals('bytes=0-1023', $context['range_header']);
                
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');
        $request->headers->set('Range', 'bytes=0-1023');

        $this->logger->logSuccessfulStream($this->content, $request);
    }

    /** @test */
    public function it_logs_successful_stream_with_user_information_when_authenticated()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify user information
                $this->assertArrayHasKey('user_id', $context);
                $this->assertArrayHasKey('user_email', $context);
                $this->assertArrayHasKey('authenticated', $context);
                
                $this->assertEquals($this->user->id, $context['user_id']);
                $this->assertEquals($this->user->email, $context['user_email']);
                $this->assertTrue($context['authenticated']);
                
                return true;
            });

        $this->actingAs($this->user);

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');

        $this->logger->logSuccessfulStream($this->content, $request);
    }

    /** @test */
    public function it_logs_stream_error_with_all_required_details()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify log message
                $this->assertEquals('PDF stream error occurred', $message);
                
                // Verify required fields
                $this->assertArrayHasKey('correlation_id', $context);
                $this->assertArrayHasKey('event_type', $context);
                $this->assertArrayHasKey('error_type', $context);
                $this->assertArrayHasKey('error_message', $context);
                
                // Verify error details
                $this->assertEquals('stream_error', $context['event_type']);
                $this->assertEquals('file_not_found', $context['error_type']);
                $this->assertEquals('PDF file does not exist', $context['error_message']);
                
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');

        $correlationId = $this->logger->logStreamError(
            $this->content,
            $request,
            'file_not_found',
            'PDF file does not exist'
        );

        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    /** @test */
    public function it_logs_stream_error_without_content_when_content_is_null()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify content fields are null
                $this->assertNull($context['content_id']);
                $this->assertNull($context['content_title']);
                $this->assertNull($context['content_type']);
                
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/999', 'GET');

        $this->logger->logStreamError(
            null,
            $request,
            'content_not_found',
            'Content does not exist'
        );
    }

    /** @test */
    public function it_logs_range_request_with_all_required_details()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify log message
                $this->assertEquals('PDF range request served', $message);
                
                // Verify required fields
                $this->assertArrayHasKey('correlation_id', $context);
                $this->assertArrayHasKey('event_type', $context);
                $this->assertArrayHasKey('content_id', $context);
                $this->assertArrayHasKey('range_header', $context);
                $this->assertArrayHasKey('start_byte', $context);
                $this->assertArrayHasKey('end_byte', $context);
                $this->assertArrayHasKey('length_bytes', $context);
                $this->assertArrayHasKey('file_size', $context);
                $this->assertArrayHasKey('percentage_of_file', $context);
                
                // Verify range details
                $this->assertEquals('range_request', $context['event_type']);
                $this->assertEquals(0, $context['start_byte']);
                $this->assertEquals(1023, $context['end_byte']);
                $this->assertEquals(1024, $context['length_bytes']);
                
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');
        $request->headers->set('Range', 'bytes=0-1023');

        $fileSize = 1024 * 1024; // 1MB
        $correlationId = $this->logger->logRangeRequest(
            $this->content,
            $request,
            0,
            1023,
            $fileSize
        );

        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    /** @test */
    public function it_logs_range_request_with_formatted_sizes()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify formatted sizes
                $this->assertArrayHasKey('length_formatted', $context);
                $this->assertArrayHasKey('file_size_formatted', $context);
                
                $this->assertEquals('1 KB', $context['length_formatted']);
                $this->assertEquals('1 MB', $context['file_size_formatted']);
                
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');
        $request->headers->set('Range', 'bytes=0-1023');

        $this->logger->logRangeRequest($this->content, $request, 0, 1023, 1024 * 1024);
    }

    /** @test */
    public function it_logs_url_generation_with_all_required_details()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify log message
                $this->assertEquals('PDF signed URL generated', $message);
                
                // Verify required fields
                $this->assertArrayHasKey('correlation_id', $context);
                $this->assertArrayHasKey('event_type', $context);
                $this->assertArrayHasKey('content_id', $context);
                $this->assertArrayHasKey('url', $context);
                $this->assertArrayHasKey('has_signature', $context);
                $this->assertArrayHasKey('has_expiration', $context);
                $this->assertArrayHasKey('expiration_minutes', $context);
                
                // Verify event type
                $this->assertEquals('url_generation', $context['event_type']);
                
                // Verify URL details
                $this->assertTrue($context['has_signature']);
                $this->assertTrue($context['has_expiration']);
                $this->assertEquals(5, $context['expiration_minutes']);
                
                return true;
            });

        $this->actingAs($this->user);

        $signedUrl = 'https://example.com/secure-pdf/stream/1?signature=abc123&expires=9999999999';
        
        $correlationId = $this->logger->logUrlGeneration($this->content, $signedUrl, 5);

        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    /** @test */
    public function it_logs_access_denied_with_all_required_details()
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                // Verify log message
                $this->assertEquals('PDF access denied', $message);
                
                // Verify required fields
                $this->assertArrayHasKey('correlation_id', $context);
                $this->assertArrayHasKey('event_type', $context);
                $this->assertArrayHasKey('reason', $context);
                $this->assertArrayHasKey('content_id', $context);
                $this->assertArrayHasKey('is_active', $context);
                
                // Verify event type and reason
                $this->assertEquals('access_denied', $context['event_type']);
                $this->assertEquals('insufficient_permissions', $context['reason']);
                
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');

        $correlationId = $this->logger->logAccessDenied(
            $this->content,
            $request,
            'insufficient_permissions'
        );

        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    /** @test */
    public function it_includes_additional_context_in_all_log_methods()
    {
        $additionalContext = [
            'custom_field' => 'custom_value',
            'debug_info' => 'test debug information',
        ];

        // Test signature failure
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($additionalContext) {
                $this->assertArrayHasKey('additional_context', $context);
                $this->assertEquals($additionalContext, $context['additional_context']);
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');
        $this->logger->logSignatureFailure($request, 'test', $additionalContext);

        // Test successful stream
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) use ($additionalContext) {
                $this->assertArrayHasKey('additional_context', $context);
                $this->assertEquals($additionalContext, $context['additional_context']);
                return true;
            });

        $this->logger->logSuccessfulStream($this->content, $request, $additionalContext);
    }

    /** @test */
    public function it_returns_unique_correlation_ids_for_each_log_call()
    {
        Log::shouldReceive('error')->times(3);

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');

        $id1 = $this->logger->logSignatureFailure($request, 'test1');
        $id2 = $this->logger->logSignatureFailure($request, 'test2');
        $id3 = $this->logger->logSignatureFailure($request, 'test3');

        $this->assertNotEquals($id1, $id2);
        $this->assertNotEquals($id2, $id3);
        $this->assertNotEquals($id1, $id3);
    }

    /** @test */
    public function it_formats_bytes_correctly_in_logs()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // Test various file sizes
                $this->assertEquals('1 MB', $context['file_size_formatted']);
                return true;
            });

        $request = Request::create('https://example.com/secure-pdf/stream/1', 'GET');
        $this->logger->logSuccessfulStream($this->content, $request);
    }
}
