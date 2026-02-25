<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Content;
use App\Services\FileStorageDiagnosticService;
use App\Services\FileLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Mockery;

/**
 * Unit tests for the enhanced getSignedUrl() method in Content model.
 * 
 * Tests the file existence validation, fallback logic, and error logging
 * functionality added to the getSignedUrl() method.
 * 
 * Requirements: 3.3, 3.4
 */
class ContentEnhancedGetSignedUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up storage fakes
        Storage::fake('public');
        Storage::fake('protected');
    }

    /**
     * Test getSignedUrl returns null when no file_path is set.
     */
    public function test_getSignedUrl_returns_null_when_no_file_path()
    {
        $content = new Content([
            'type' => 'image',
            'file_path' => null,
        ]);
        $content->id = 1;

        $result = $content->getSignedUrl();

        $this->assertNull($result);
    }

    /**
     * Test getSignedUrl returns null when file doesn't exist anywhere.
     */
    public function test_getSignedUrl_returns_null_when_file_not_found()
    {
        $content = new Content([
            'type' => 'image',
            'file_path' => 'nonexistent/test.jpg',
            'storage_disk' => 'public',
        ]);
        $content->id = 1;

        // Expect debug and error logs to be called
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $result = $content->getSignedUrl();

        $this->assertNull($result);
    }

    /**
     * Test getSignedUrl generates asset URL for public storage files.
     */
    public function test_getSignedUrl_generates_asset_url_for_public_files()
    {
        // Create a test file in public storage
        Storage::disk('public')->put('test/image.jpg', 'test content');

        $content = new Content([
            'type' => 'image',
            'file_path' => 'test/image.jpg',
            'storage_disk' => 'public',
        ]);
        $content->id = 1;

        // Expect debug logs
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('info')->atLeast()->once();

        $result = $content->getSignedUrl();

        $this->assertNotNull($result);
        $this->assertStringContainsString('storage/test/image.jpg', $result);
    }

    /**
     * Test getSignedUrl generates secure route URL for protected storage files.
     */
    public function test_getSignedUrl_generates_secure_route_for_protected_files()
    {
        // Create a test file in protected storage
        Storage::disk('protected')->put('secure/document.pdf', 'secure content');

        $content = new Content([
            'type' => 'pdf',
            'file_path' => 'secure/document.pdf',
            'storage_disk' => 'protected',
        ]);
        $content->id = 1;

        // Expect debug logs
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('info')->atLeast()->once();

        $result = $content->getSignedUrl();

        $this->assertNotNull($result);
        $this->assertStringContainsString('secure-files/content/1', $result);
    }

    /**
     * Test getSignedUrl logs warning when file is found on different disk than recorded.
     */
    public function test_getSignedUrl_logs_warning_for_storage_disk_mismatch()
    {
        // Create a test file in protected storage but record it as public
        Storage::disk('protected')->put('misplaced/image.jpg', 'test content');

        $content = new Content([
            'type' => 'image',
            'file_path' => 'misplaced/image.jpg',
            'storage_disk' => 'public', // Recorded as public but actually on protected
        ]);
        $content->id = 1;

        // Expect debug logs and warning log for disk mismatch
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('warning')->atLeast()->once();

        $result = $content->getSignedUrl();

        $this->assertNotNull($result);
        $this->assertStringContainsString('secure-files/content/1', $result);
    }

    /**
     * Test getSignedUrl handles exceptions gracefully.
     */
    public function test_getSignedUrl_handles_exceptions_gracefully()
    {
        $content = new Content([
            'type' => 'image',
            'file_path' => 'test/image.jpg',
            'storage_disk' => 'public',
        ]);
        $content->id = 1;

        // Mock Storage to throw an exception
        Storage::shouldReceive('disk')
            ->andThrow(new \Exception('Storage service error'));

        // Expect debug and error logs
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();
        Log::shouldReceive('warning')->atLeast()->once();

        $result = $content->getSignedUrl();

        $this->assertNull($result);
    }

    /**
     * Test that comprehensive diagnostic information is logged when file is not found.
     */
    public function test_comprehensive_diagnostic_logging_when_file_not_found()
    {
        $content = new Content([
            'type' => 'image',
            'file_path' => 'missing/test.jpg',
            'storage_disk' => 'public',
            'file_size' => 1024,
            'file_hash' => 'abc123def456',
        ]);
        $content->id = 1;

        // Expect comprehensive error log
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $result = $content->getSignedUrl();

        $this->assertNull($result);
    }

    /**
     * Test URL type determination helper method.
     */
    public function test_url_type_determination()
    {
        $content = new Content();
        
        // Use reflection to test private method
        $reflection = new \ReflectionClass($content);
        $method = $reflection->getMethod('determineUrlType');
        $method->setAccessible(true);

        // Test secure route URL
        $secureUrl = 'https://example.com/secure-files/content/1';
        $this->assertEquals('secure_route', $method->invoke($content, $secureUrl));

        // Test asset URL
        $assetUrl = 'https://example.com/storage/test/image.jpg';
        $this->assertEquals('asset_url', $method->invoke($content, $assetUrl));

        // Test unknown URL
        $unknownUrl = 'https://example.com/unknown/path';
        $this->assertEquals('unknown', $method->invoke($content, $unknownUrl));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}