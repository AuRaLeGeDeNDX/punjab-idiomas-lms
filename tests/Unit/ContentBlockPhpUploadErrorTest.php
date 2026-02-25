<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ContentBlockController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class ContentBlockPhpUploadErrorTest extends TestCase
{
    private ContentBlockController $controller;
    private ReflectionMethod $getDetailedPhpUploadErrorMethod;
    private ReflectionMethod $convertToBytesMethod;
    private ReflectionMethod $formatBytesMethod;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new ContentBlockController(
            $this->createMock(\App\Services\ContentBlockService::class)
        );
        
        // Use reflection to access private methods
        $reflection = new ReflectionClass($this->controller);
        
        $this->getDetailedPhpUploadErrorMethod = $reflection->getMethod('getDetailedPhpUploadError');
        $this->getDetailedPhpUploadErrorMethod->setAccessible(true);
        
        $this->convertToBytesMethod = $reflection->getMethod('convertToBytes');
        $this->convertToBytesMethod->setAccessible(true);
        
        $this->formatBytesMethod = $reflection->getMethod('formatBytes');
        $this->formatBytesMethod->setAccessible(true);
    }

    public function test_upload_err_ini_size_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_INI_SIZE);
        
        $this->assertStringContainsString('File exceeds server upload limit', $message);
        $this->assertStringContainsString('Current limit:', $message);
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
    }

    public function test_upload_err_form_size_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_FORM_SIZE);
        
        $this->assertStringContainsString('File exceeds form upload limit', $message);
        $this->assertStringContainsString('form specified a smaller limit', $message);
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('post_max_size', $message);
    }

    public function test_upload_err_partial_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_PARTIAL);
        
        $this->assertStringContainsString('File upload was interrupted', $message);
        $this->assertStringContainsString('partially completed', $message);
        $this->assertStringContainsString('network interruption', $message);
        $this->assertStringContainsString('check your internet connection', $message);
    }

    public function test_upload_err_no_file_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_NO_FILE);
        
        $this->assertStringContainsString('No file was selected', $message);
        $this->assertStringContainsString('choose a file', $message);
    }

    public function test_upload_err_no_tmp_dir_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_NO_TMP_DIR);
        
        $this->assertStringContainsString('temporary directory is missing', $message);
        $this->assertStringContainsString('upload_tmp_dir', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
    }

    public function test_upload_err_cant_write_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_CANT_WRITE);
        
        $this->assertStringContainsString('cannot write uploaded file', $message);
        $this->assertStringContainsString('disk space or permission issues', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
    }

    public function test_upload_err_extension_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_EXTENSION);
        
        $this->assertStringContainsString('Upload blocked by server extension', $message);
        $this->assertStringContainsString('PHP extension has prevented', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
    }

    public function test_unknown_error_code_returns_detailed_message()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, 999);
        
        $this->assertStringContainsString('Unknown upload error', $message);
        $this->assertStringContainsString('code: 999', $message);
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('post_max_size', $message);
        $this->assertStringContainsString('memory_limit', $message);
    }

    public function test_convert_to_bytes_handles_different_units()
    {
        // Test bytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '1024');
        $this->assertEquals(1024, $result);

        // Test kilobytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '2K');
        $this->assertEquals(2048, $result);

        // Test megabytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '5M');
        $this->assertEquals(5242880, $result);

        // Test gigabytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '1G');
        $this->assertEquals(1073741824, $result);

        // Test lowercase
        $result = $this->convertToBytesMethod->invoke($this->controller, '10m');
        $this->assertEquals(10485760, $result);
    }

    public function test_format_bytes_returns_human_readable_format()
    {
        // Test bytes
        $result = $this->formatBytesMethod->invoke($this->controller, 0);
        $this->assertEquals('0 Bytes', $result);

        $result = $this->formatBytesMethod->invoke($this->controller, 512);
        $this->assertEquals('512 Bytes', $result);

        // Test kilobytes
        $result = $this->formatBytesMethod->invoke($this->controller, 1024);
        $this->assertEquals('1 KB', $result);

        $result = $this->formatBytesMethod->invoke($this->controller, 1536);
        $this->assertEquals('1.5 KB', $result);

        // Test megabytes
        $result = $this->formatBytesMethod->invoke($this->controller, 1048576);
        $this->assertEquals('1 MB', $result);

        $result = $this->formatBytesMethod->invoke($this->controller, 2621440);
        $this->assertEquals('2.5 MB', $result);

        // Test gigabytes
        $result = $this->formatBytesMethod->invoke($this->controller, 1073741824);
        $this->assertEquals('1 GB', $result);
    }

    /**
     * Test that all error messages include helpful information
     */
    public function test_all_error_messages_are_helpful()
    {
        $errorCodes = [
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION,
            999 // Unknown error
        ];

        foreach ($errorCodes as $errorCode) {
            $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, $errorCode);
            
            // All messages should be non-empty and contain useful information
            $this->assertNotEmpty($message, "Error message for code {$errorCode} should not be empty");
            $this->assertGreaterThan(50, strlen($message), "Error message for code {$errorCode} should be detailed");
            
            // Messages should not contain generic phrases
            $this->assertStringNotContainsString('unknown error', strtolower($message), "Error message for code {$errorCode} should be specific");
        }
    }

    /**
     * Test that configuration guidance is included where appropriate
     */
    public function test_configuration_guidance_included()
    {
        $configurationErrorCodes = [
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION
        ];

        foreach ($configurationErrorCodes as $errorCode) {
            $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, $errorCode);
            
            $this->assertStringContainsString('administrator', $message, 
                "Error message for code {$errorCode} should include administrator guidance");
        }
    }
}