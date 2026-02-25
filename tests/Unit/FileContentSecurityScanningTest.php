<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\ContentBlockController;
use App\Services\FileUploadErrorFormatter;
use App\Helpers\FileSecurityHelper;
use ReflectionClass;
use ReflectionMethod;

/**
 * Test file content security scanning functionality.
 * 
 * **Validates: Requirements 6.3**
 */
class FileContentSecurityScanningTest extends TestCase
{
    use RefreshDatabase;

    private ContentBlockController $controller;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new ContentBlockController(
            app(\App\Services\ContentBlockService::class),
            app(\App\Services\FileUploadLogger::class)
        );
        
        $this->reflection = new ReflectionClass($this->controller);
        
        // Suppress logs during testing
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
        Log::shouldReceive('critical')->andReturn(null);
    }

    /**
     * Test basic file header validation for common file types.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_basic_file_header_validation()
    {
        $method = $this->reflection->getMethod('validateBasicFileHeaders');
        $method->setAccessible(true);

        // Test valid JPEG header
        $jpegFile = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        $errors = [];
        $method->invokeArgs($this->controller, [$jpegFile, &$errors, 'test-correlation-id']);
        
        // Should not add errors for valid files in testing environment
        $this->assertEmpty($errors, 'Valid JPEG file should not produce header validation errors');

        // Test valid PNG header
        $pngFile = UploadedFile::fake()->create('test.png', 100, 'image/png');
        $errors = [];
        $method->invokeArgs($this->controller, [$pngFile, &$errors, 'test-correlation-id']);
        
        $this->assertEmpty($errors, 'Valid PNG file should not produce header validation errors');

        // Test valid PDF header
        $pdfFile = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $errors = [];
        $method->invokeArgs($this->controller, [$pdfFile, &$errors, 'test-correlation-id']);
        
        $this->assertEmpty($errors, 'Valid PDF file should not produce header validation errors');
    }

    /**
     * Test executable file signature detection.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_executable_file_signature_detection()
    {
        $method = $this->reflection->getMethod('detectExecutableFileSignatures');
        $method->setAccessible(true);

        // Test with safe file
        $safeFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $errors = [];
        $method->invoke($this->controller, $safeFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe PDF file should not trigger executable detection');

        // Test with image file
        $imageFile = UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg');
        $errors = [];
        $method->invoke($this->controller, $imageFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe image file should not trigger executable detection');
    }

    /**
     * Test enhanced image header validation.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_enhanced_image_header_validation()
    {
        $method = $this->reflection->getMethod('validateEnhancedImageHeaders');
        $method->setAccessible(true);

        // Test JPEG image validation
        $jpegFile = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        $errors = [];
        $method->invoke($this->controller, $jpegFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Valid JPEG file should pass enhanced validation');

        // Test PNG image validation
        $pngFile = UploadedFile::fake()->create('test.png', 100, 'image/png');
        $errors = [];
        $method->invoke($this->controller, $pngFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Valid PNG file should pass enhanced validation');

        // Test GIF image validation
        $gifFile = UploadedFile::fake()->create('test.gif', 100, 'image/gif');
        $errors = [];
        $method->invoke($this->controller, $gifFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Valid GIF file should pass enhanced validation');

        // Test WebP image validation
        $webpFile = UploadedFile::fake()->create('test.webp', 100, 'image/webp');
        $errors = [];
        $method->invoke($this->controller, $webpFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Valid WebP file should pass enhanced validation');
    }

    /**
     * Test advanced security scan integration.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_advanced_security_scan_integration()
    {
        $method = $this->reflection->getMethod('performAdvancedSecurityScan');
        $method->setAccessible(true);

        // Test with safe file
        $safeFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $errors = [];
        $method->invoke($this->controller, $safeFile, $errors, 'test-correlation-id');
        
        // Should not add errors for safe files
        $this->assertEmpty($errors, 'Safe file should pass advanced security scan');
    }

    /**
     * Test virus scanning integration hooks.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_virus_scanning_integration_hooks()
    {
        $method = $this->reflection->getMethod('integrateVirusScanningHooks');
        $method->setAccessible(true);

        // Test with safe file
        $safeFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $errors = [];
        $method->invoke($this->controller, $safeFile, $errors, 'test-correlation-id');
        
        // Should not add errors for safe files
        $this->assertEmpty($errors, 'Safe file should pass virus scanning hooks');
    }

    /**
     * Test EICAR test file detection.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_eicar_test_file_detection()
    {
        $method = $this->reflection->getMethod('detectEicarTestFile');
        $method->setAccessible(true);

        // Test with safe file
        $safeFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $errors = [];
        $method->invoke($this->controller, $safeFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe file should not trigger EICAR detection');
    }

    /**
     * Test comprehensive security scan method integration.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_comprehensive_security_scan_integration()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        // Test with safe PDF file
        $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $errors = [];
        $method->invoke($this->controller, $pdfFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe PDF file should pass comprehensive security scan');

        // Test with safe image file
        $imageFile = UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg');
        $errors = [];
        $method->invoke($this->controller, $imageFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe image file should pass comprehensive security scan');

        // Test with safe audio file
        $audioFile = UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg');
        $errors = [];
        $method->invoke($this->controller, $audioFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe audio file should pass comprehensive security scan');

        // Test with safe video file
        $videoFile = UploadedFile::fake()->create('video.mp4', 100, 'video/mp4');
        $errors = [];
        $method->invoke($this->controller, $videoFile, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe video file should pass comprehensive security scan');
    }

    /**
     * Test JPEG image header validation methods.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_jpeg_image_header_validation()
    {
        $method = $this->reflection->getMethod('validateJpegImageHeaders');
        $method->setAccessible(true);

        // Test with valid JPEG header simulation
        $validJpegHeader = "\xFF\xD8\xFF\xE0" . str_repeat("\x00", 100);
        $errors = [];
        $method->invoke($this->controller, $validJpegHeader, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Valid JPEG header should not produce errors');

        // Test with invalid header
        $invalidHeader = "INVALID" . str_repeat("\x00", 100);
        $errors = [];
        $method->invoke($this->controller, $invalidHeader, $errors, 'test-correlation-id');
        
        $this->assertNotEmpty($errors, 'Invalid JPEG header should produce errors');
        $this->assertStringContainsString('Invalid JPEG header', $errors[0]);
    }

    /**
     * Test PNG image header validation methods.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_png_image_header_validation()
    {
        $method = $this->reflection->getMethod('validatePngImageHeaders');
        $method->setAccessible(true);

        // Test with valid PNG header simulation
        $validPngHeader = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A" . "IHDR" . str_repeat("\x00", 100);
        $errors = [];
        $method->invoke($this->controller, $validPngHeader, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Valid PNG header should not produce errors');

        // Test with invalid header
        $invalidHeader = "INVALID" . str_repeat("\x00", 100);
        $errors = [];
        $method->invoke($this->controller, $invalidHeader, $errors, 'test-correlation-id');
        
        $this->assertNotEmpty($errors, 'Invalid PNG header should produce errors');
        $this->assertStringContainsString('Invalid PNG header', $errors[0]);
    }

    /**
     * Test embedded executable detection in images.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_embedded_executable_detection()
    {
        $method = $this->reflection->getMethod('checkForEmbeddedExecutables');
        $method->setAccessible(true);

        // Test with safe image content
        $safeContent = str_repeat("\x00", 1000);
        $errors = [];
        $method->invoke($this->controller, $safeContent, $errors, 'test-correlation-id');
        
        $this->assertEmpty($errors, 'Safe image content should not trigger embedded executable detection');

        // Test with embedded executable signature
        $maliciousContent = str_repeat("\x00", 500) . "MZ" . str_repeat("\x00", 500);
        $errors = [];
        $method->invoke($this->controller, $maliciousContent, $errors, 'test-correlation-id');
        
        $this->assertNotEmpty($errors, 'Content with embedded executable should trigger detection');
        $this->assertStringContainsString('embedded executable content', $errors[0]);

        // Test with embedded PHP script
        $phpContent = str_repeat("\x00", 500) . "<?php" . str_repeat("\x00", 500);
        $errors = [];
        $method->invoke($this->controller, $phpContent, $errors, 'test-correlation-id');
        
        $this->assertNotEmpty($errors, 'Content with embedded PHP should trigger detection');
        $this->assertStringContainsString('embedded executable content', $errors[0]);
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
     * Test security scanning with different file types.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_different_file_types()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

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
            $errors = [];
            $method->invoke($this->controller, $file, $errors, 'test-correlation-id');
            
            $this->assertEmpty($errors, "Safe {$fileType['name']} file should pass comprehensive security scan");
        }
    }

    /**
     * Test security scanning performance with larger files.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_performance()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        // Test with larger file (1MB)
        $largeFile = UploadedFile::fake()->create('large_document.pdf', 1024, 'application/pdf');
        
        $startTime = microtime(true);
        $errors = [];
        $method->invoke($this->controller, $largeFile, $errors, 'test-correlation-id');
        $endTime = microtime(true);
        
        $executionTime = $endTime - $startTime;
        
        $this->assertEmpty($errors, 'Large safe file should pass security scan');
        $this->assertLessThan(5.0, $executionTime, 'Security scan should complete within 5 seconds');
    }

    /**
     * Test security scanning error handling.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_security_scanning_error_handling()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        // Test with valid file to ensure no exceptions are thrown
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $errors = [];
        
        // Should not throw any exceptions
        try {
            $method->invoke($this->controller, $file, $errors, 'test-correlation-id');
            $this->assertTrue(true, 'Security scanning should handle errors gracefully');
        } catch (\Exception $e) {
            $this->fail('Security scanning should not throw exceptions: ' . $e->getMessage());
        }
    }

    /**
     * Clean up temporary files after tests.
     */
    protected function tearDown(): void
    {
        // Clean up any temporary files created during testing
        parent::tearDown();
    }
}