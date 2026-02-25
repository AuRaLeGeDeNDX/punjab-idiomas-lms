<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Services\FileUploadErrorFormatter;
use App\Helpers\FileSecurityHelper;

/**
 * Simple test for file content security scanning functionality.
 * 
 * **Validates: Requirements 6.3**
 */
class FileContentSecurityScanningSimpleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Suppress logs during testing
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
        Log::shouldReceive('critical')->andReturn(null);
    }

    /**
     * Test FileSecurityHelper integration.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_file_security_helper_integration()
    {
        // Test with safe file
        $safeFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $scanResults = FileSecurityHelper::scanFile($safeFile);
        
        $this->assertIsArray($scanResults, 'FileSecurityHelper should return array results');
        $this->assertArrayHasKey('safe', $scanResults, 'Results should have safe key');
        $this->assertArrayHasKey('threats', $scanResults, 'Results should have threats key');
        $this->assertArrayHasKey('warnings', $scanResults, 'Results should have warnings key');
        $this->assertArrayHasKey('info', $scanResults, 'Results should have info key');
        
        $this->assertTrue($scanResults['safe'], 'Safe file should be marked as safe');
        $this->assertEmpty($scanResults['threats'], 'Safe file should have no threats');
    }

    /**
     * Test error message formatting for security scanning.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_error_message_formatting()
    {
        // Test header mismatch error
        $headerError = FileUploadErrorFormatter::formatError('header_mismatch', [
            'file_extension' => 'jpg',
            'header_signature' => '89504e47',
            'expected_headers' => 'ffd8ffe0, ffd8ffe1',
            'security_reason' => 'File header does not match extension'
        ]);
        
        $this->assertStringContainsString('File header validation failed', $headerError);
        $this->assertStringContainsString('.jpg', $headerError);
        $this->assertStringContainsString('89504e47', $headerError);

        // Test executable detected error
        $executableError = FileUploadErrorFormatter::formatError('executable_detected', [
            'file_name' => 'malware.exe',
            'signature_type' => 'Windows PE executable',
            'security_reason' => 'Executable files are not allowed for security'
        ]);
        
        $this->assertStringContainsString('Security violation', $executableError);
        $this->assertStringContainsString('malware.exe', $executableError);
        $this->assertStringContainsString('Windows PE executable', $executableError);

        // Test virus detected error
        $virusError = FileUploadErrorFormatter::formatError('virus_detected', [
            'file_name' => 'infected.doc',
            'scanner' => 'ClamAV',
            'security_reason' => 'Virus detected by antivirus scanner'
        ]);
        
        $this->assertStringContainsString('VIRUS DETECTED', $virusError);
        $this->assertStringContainsString('infected.doc', $virusError);
        $this->assertStringContainsString('ClamAV', $virusError);
    }

    /**
     * Test security scanning with different file types.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_different_file_types()
    {
        $fileTypes = [
            ['name' => 'document.pdf', 'mime' => 'application/pdf'],
            ['name' => 'image.jpg', 'mime' => 'image/jpeg'],
            ['name' => 'image.png', 'mime' => 'image/png'],
            ['name' => 'image.gif', 'mime' => 'image/gif'],
            ['name' => 'image.webp', 'mime' => 'image/webp'],
            ['name' => 'audio.mp3', 'mime' => 'audio/mpeg'],
            ['name' => 'video.mp4', 'mime' => 'video/mp4'],
            ['name' => 'archive.zip', 'mime' => 'application/zip'],
        ];

        foreach ($fileTypes as $fileType) {
            $file = UploadedFile::fake()->create($fileType['name'], 100, $fileType['mime']);
            $scanResults = FileSecurityHelper::scanFile($file);
            
            $this->assertIsArray($scanResults, "FileSecurityHelper should return array for {$fileType['name']}");
            $this->assertTrue($scanResults['safe'], "Safe {$fileType['name']} file should pass security scan");
        }
    }

    /**
     * Test that security scanning methods exist in ContentBlockController.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_methods_exist()
    {
        $controller = new \App\Http\Controllers\Api\ContentBlockController(
            app(\App\Services\ContentBlockService::class),
            app(\App\Services\FileUploadLogger::class)
        );
        
        $reflection = new \ReflectionClass($controller);
        
        // Check that security scanning methods exist
        $expectedMethods = [
            'performComprehensiveSecurityScan',
            'validateBasicFileHeaders',
            'detectExecutableFileSignatures',
            'validateEnhancedImageHeaders',
            'performAdvancedSecurityScan',
            'integrateVirusScanningHooks',
        ];
        
        foreach ($expectedMethods as $methodName) {
            $this->assertTrue($reflection->hasMethod($methodName), 
                "ContentBlockController should have method {$methodName}");
        }
    }

    /**
     * Test error message consistency.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_error_message_consistency()
    {
        $errorTypes = [
            'header_mismatch',
            'executable_detected',
            'script_content_detected',
            'security_threat',
            'security_warning',
            'virus_detected',
            'test_virus_detected',
        ];

        foreach ($errorTypes as $errorType) {
            $context = [
                'file_name' => 'test-file.ext',
                'file_extension' => 'ext',
                'header_signature' => 'test-signature',
                'expected_headers' => 'expected-headers',
                'signature_type' => 'test signature',
                'script_type' => 'test script',
                'threat_description' => 'test threat',
                'warning_description' => 'test warning',
                'scanner' => 'test scanner',
                'test_type' => 'test type',
                'security_reason' => 'test security reason',
            ];
            
            $errorMessage = FileUploadErrorFormatter::formatError($errorType, $context);
            
            // Error message should be non-empty
            $this->assertNotEmpty($errorMessage, "Error message for {$errorType} should not be empty");
            
            // Error message should be reasonably long (more than just a few words)
            $this->assertGreaterThan(20, strlen($errorMessage), 
                "Error message for {$errorType} should be informative: {$errorMessage}");
            
            // Error message should contain the file name (except for header_mismatch which uses file_extension)
            if ($errorType !== 'header_mismatch') {
                $this->assertStringContainsString('test-file.ext', $errorMessage, 
                    "Error message for {$errorType} should contain file name");
            } else {
                $this->assertStringContainsString('.ext', $errorMessage, 
                    "Error message for {$errorType} should contain file extension");
            }
        }
    }
}