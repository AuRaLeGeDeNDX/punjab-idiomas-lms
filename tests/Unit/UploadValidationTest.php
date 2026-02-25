<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ContentBlockController;
use App\Models\Content;
use App\Services\ContentBlockService;
use App\Services\FileUploadLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Comprehensive unit tests for upload validation functionality.
 * 
 * Tests cover:
 * - PHP upload error handling for each error code
 * - File size validation with various file sizes
 * - Extension validation with allowed and disallowed types
 * - MIME type validation and spoofing prevention
 * 
 * **Validates: Requirements All**
 */
class UploadValidationTest extends TestCase
{
    use RefreshDatabase;
    private ContentBlockController $controller;
    private ReflectionClass $reflection;
    private ReflectionMethod $getDetailedPhpUploadErrorMethod;
    private ReflectionMethod $convertToBytesMethod;
    private ReflectionMethod $formatBytesMethod;
    private ReflectionMethod $validateUploadedFileMethod;
    private ReflectionMethod $getMimeTypesForExtensionsMethod;
    private ReflectionMethod $validateServerConfigurationMethod;
    private ReflectionMethod $validateServerResourcesMethod;
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Log facade to prevent actual logging during tests
        Log::spy();
        
        // Create controller with mocked dependencies
        $this->controller = new ContentBlockController(
            $this->createMock(ContentBlockService::class),
            $this->createMock(FileUploadLogger::class)
        );
        
        // Set up reflection to access private methods
        $this->reflection = new ReflectionClass($this->controller);
        
        $this->getDetailedPhpUploadErrorMethod = $this->reflection->getMethod('getDetailedPhpUploadError');
        $this->getDetailedPhpUploadErrorMethod->setAccessible(true);
        
        $this->convertToBytesMethod = $this->reflection->getMethod('convertToBytes');
        $this->convertToBytesMethod->setAccessible(true);
        
        $this->formatBytesMethod = $this->reflection->getMethod('formatBytes');
        $this->formatBytesMethod->setAccessible(true);
        
        $this->validateUploadedFileMethod = $this->reflection->getMethod('validateUploadedFile');
        $this->validateUploadedFileMethod->setAccessible(true);
        
        $this->getMimeTypesForExtensionsMethod = $this->reflection->getMethod('getMimeTypesForExtensions');
        $this->getMimeTypesForExtensionsMethod->setAccessible(true);
        
        $this->validateServerConfigurationMethod = $this->reflection->getMethod('validateServerConfiguration');
        $this->validateServerConfigurationMethod->setAccessible(true);
        
        $this->validateServerResourcesMethod = $this->reflection->getMethod('validateServerResources');
        $this->validateServerResourcesMethod->setAccessible(true);
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        foreach ($this->tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        
        parent::tearDown();
    }

    // ========================================
    // PHP Upload Error Handling Tests
    // ========================================

    /**
     * Test PHP upload error handling for UPLOAD_ERR_INI_SIZE.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2**
     */
    public function test_php_upload_error_ini_size()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_INI_SIZE);
        
        $this->assertStringContainsString('File exceeds server upload limit', $message);
        $this->assertStringContainsString('Current limit:', $message);
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
        $this->assertGreaterThan(50, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test PHP upload error handling for UPLOAD_ERR_FORM_SIZE.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2**
     */
    public function test_php_upload_error_form_size()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_FORM_SIZE);
        
        $this->assertStringContainsString('File exceeds form upload limit', $message);
        $this->assertStringContainsString('form specified a smaller limit', $message);
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('post_max_size', $message);
        $this->assertGreaterThan(50, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test PHP upload error handling for UPLOAD_ERR_PARTIAL.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2**
     */
    public function test_php_upload_error_partial()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_PARTIAL);
        
        $this->assertStringContainsString('File upload was interrupted', $message);
        $this->assertStringContainsString('partially completed', $message);
        $this->assertStringContainsString('network interruption', $message);
        $this->assertStringContainsString('check your internet connection', $message);
        $this->assertGreaterThan(50, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test PHP upload error handling for UPLOAD_ERR_NO_FILE.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2**
     */
    public function test_php_upload_error_no_file()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_NO_FILE);
        
        $this->assertStringContainsString('No file was selected', $message);
        $this->assertStringContainsString('choose a file', $message);
        $this->assertGreaterThan(30, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test PHP upload error handling for UPLOAD_ERR_NO_TMP_DIR.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2, 3.4**
     */
    public function test_php_upload_error_no_tmp_dir()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_NO_TMP_DIR);
        
        $this->assertStringContainsString('temporary directory is missing', $message);
        $this->assertStringContainsString('upload_tmp_dir', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
        $this->assertGreaterThan(50, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test PHP upload error handling for UPLOAD_ERR_CANT_WRITE.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2, 3.3**
     */
    public function test_php_upload_error_cant_write()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_CANT_WRITE);
        
        $this->assertStringContainsString('cannot write uploaded file', $message);
        $this->assertStringContainsString('disk space or permission issues', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
        $this->assertGreaterThan(50, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test PHP upload error handling for UPLOAD_ERR_EXTENSION.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2**
     */
    public function test_php_upload_error_extension()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, UPLOAD_ERR_EXTENSION);
        
        $this->assertStringContainsString('Upload blocked by server extension', $message);
        $this->assertStringContainsString('PHP extension has prevented', $message);
        $this->assertStringContainsString('Contact your administrator', $message);
        $this->assertGreaterThan(50, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test PHP upload error handling for unknown error codes.
     * 
     * **Validates: Requirements 1.1, 2.1, 2.2**
     */
    public function test_php_upload_error_unknown()
    {
        $message = $this->getDetailedPhpUploadErrorMethod->invoke($this->controller, 999);
        
        $this->assertStringContainsString('Unknown upload error', $message);
        $this->assertStringContainsString('code: 999', $message);
        $this->assertStringContainsString('upload_max_filesize', $message);
        $this->assertStringContainsString('post_max_size', $message);
        $this->assertStringContainsString('memory_limit', $message);
        $this->assertGreaterThan(50, strlen($message), 'Error message should be detailed');
    }

    /**
     * Test that all PHP error codes return helpful messages.
     * 
     * **Validates: Requirements 1.1, 1.4**
     */
    public function test_all_php_error_codes_return_helpful_messages()
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
            $this->assertGreaterThan(30, strlen($message), "Error message for code {$errorCode} should be detailed");
            
            // Messages should not contain generic phrases
            $this->assertStringNotContainsString('generic error', strtolower($message), 
                "Error message for code {$errorCode} should be specific");
        }
    }

    /**
     * Test that configuration guidance is included for server errors.
     * 
     * **Validates: Requirements 1.4, 3.5**
     */
    public function test_configuration_guidance_included_for_server_errors()
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

    // ========================================
    // File Size Validation Tests
    // ========================================

    /**
     * Test file size validation with various sizes.
     * 
     * **Validates: Requirements 1.2, 2.4**
     */
    public function test_file_size_validation_with_various_sizes()
    {
        $config = [
            'max_file_size' => 5 * 1024, // 5KB for testing
            'allowed_extensions' => ['jpg', 'png']
        ];

        // Test file within size limit (should not throw exception)
        $validFile = $this->createMockUploadedFile('test.jpg', 3 * 1024, 'image/jpeg'); // 3KB
        
        try {
            $this->validateUploadedFileMethod->invoke($this->controller, $validFile, 'image', $config, 'test-correlation-id');
            $this->assertTrue(true, 'Valid file size should not throw exception');
        } catch (ValidationException $e) {
            // If it throws for other reasons (like MIME type), that's okay for this test
            $errors = $e->errors()['file'] ?? [];
            $sizeErrors = array_filter($errors, fn($error) => str_contains($error, 'exceeds maximum'));
            $this->assertEmpty($sizeErrors, 'Valid file size should not have size validation errors');
        }

        // Test file exceeding size limit (should throw exception)
        $oversizedFile = $this->createMockUploadedFile('large.jpg', 7 * 1024, 'image/jpeg'); // 7KB
        
        $this->expectException(ValidationException::class);
        $this->validateUploadedFileMethod->invoke($this->controller, $oversizedFile, 'image', $config, 'test-correlation-id');
    }

    /**
     * Test file size validation error messages include formatted sizes.
     * 
     * **Validates: Requirements 1.2**
     */
    public function test_file_size_error_messages_include_formatted_sizes()
    {
        $config = [
            'max_file_size' => 2 * 1024, // 2KB
            'allowed_extensions' => ['jpg']
        ];

        $oversizedFile = $this->createMockUploadedFile('large.jpg', 5 * 1024, 'image/jpeg'); // 5KB
        
        try {
            $this->validateUploadedFileMethod->invoke($this->controller, $oversizedFile, 'image', $config, 'test-correlation-id');
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errors = $e->errors()['file'] ?? [];
            
            // Debug: Let's see what errors we actually get
            $this->assertNotEmpty($errors, 'Should have validation errors');
            
            // Look for any size-related error
            $sizeError = collect($errors)->first(fn($error) => 
                str_contains($error, 'exceeds') || 
                str_contains($error, 'size') || 
                str_contains($error, 'large') ||
                str_contains($error, 'maximum')
            );
            
            $this->assertNotNull($sizeError, 'Should have size validation error. Actual errors: ' . implode('; ', $errors));
            $this->assertStringContainsString('KB', $sizeError, 'Error should include formatted file size');
            $this->assertStringContainsString('5', $sizeError, 'Error should include actual file size');
            $this->assertStringContainsString('2', $sizeError, 'Error should include maximum allowed size');
        }
    }

    /**
     * Test file size validation at exact boundaries.
     * 
     * **Validates: Requirements 1.2, 2.4**
     */
    public function test_file_size_validation_at_boundaries()
    {
        $config = [
            'max_file_size' => 1024, // 1KB exactly
            'allowed_extensions' => ['txt']
        ];

        // Test file at exact limit (should pass)
        $exactSizeFile = $this->createMockUploadedFile('exact.txt', 1024, 'text/plain');
        
        try {
            $this->validateUploadedFileMethod->invoke($this->controller, $exactSizeFile, 'pdf', $config, 'test-correlation-id');
            $this->assertTrue(true, 'File at exact size limit should not throw size validation error');
        } catch (ValidationException $e) {
            $errors = $e->errors()['file'] ?? [];
            $sizeErrors = array_filter($errors, fn($error) => str_contains($error, 'exceeds maximum'));
            $this->assertEmpty($sizeErrors, 'File at exact size limit should not have size validation errors');
        }

        // Test file one byte over limit (should fail)
        $overLimitFile = $this->createMockUploadedFile('over.txt', 1025, 'text/plain');
        
        $this->expectException(ValidationException::class);
        $this->validateUploadedFileMethod->invoke($this->controller, $overLimitFile, 'pdf', $config, 'test-correlation-id');
    }

    // ========================================
    // Extension Validation Tests
    // ========================================

    /**
     * Test extension validation with allowed types.
     * 
     * **Validates: Requirements 2.4, 6.1**
     */
    public function test_extension_validation_with_allowed_types()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['jpg', 'png', 'gif']
        ];

        // Test allowed extensions (should not throw extension errors)
        $allowedExtensions = ['jpg', 'png', 'gif'];
        foreach ($allowedExtensions as $ext) {
            $file = $this->createMockUploadedFile("test.{$ext}", 1024, "image/{$ext}");
            
            try {
                $this->validateUploadedFileMethod->invoke($this->controller, $file, 'image', $config, 'test-correlation-id');
                $this->assertTrue(true, "Extension .{$ext} should be allowed");
            } catch (ValidationException $e) {
                $errors = $e->errors()['file'] ?? [];
                $extensionErrors = array_filter($errors, fn($error) => str_contains($error, 'not allowed'));
                $this->assertEmpty($extensionErrors, "Extension .{$ext} should not have extension validation errors");
            }
        }
    }

    /**
     * Test extension validation with disallowed types.
     * 
     * **Validates: Requirements 2.4, 6.1**
     */
    public function test_extension_validation_with_disallowed_types()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['jpg', 'png']
        ];

        // Test disallowed extensions (should throw exception)
        $disallowedExtensions = ['exe', 'bat', 'php', 'js', 'html'];
        foreach ($disallowedExtensions as $ext) {
            $file = $this->createMockUploadedFile("malicious.{$ext}", 1024, 'application/octet-stream');
            
            try {
                $this->validateUploadedFileMethod->invoke($this->controller, $file, 'image', $config, 'test-correlation-id');
                $this->fail("Extension .{$ext} should not be allowed");
            } catch (ValidationException $e) {
                $errors = $e->errors()['file'] ?? [];
                
                // Debug: Let's see what errors we actually get
                $this->assertNotEmpty($errors, "Should have validation errors for .{$ext}. No errors found.");
                
                // Look for any extension-related error
                $extensionError = collect($errors)->first(fn($error) => 
                    str_contains($error, 'not allowed') || 
                    str_contains($error, 'not supported') ||
                    str_contains($error, 'invalid') ||
                    str_contains($error, 'extension')
                );
                
                $this->assertNotNull($extensionError, "Should have extension validation error for .{$ext}. Actual errors: " . implode('; ', $errors));
                $this->assertStringContainsString($ext, $extensionError, "Error should mention the disallowed extension");
            }
        }
    }

    /**
     * Test extension validation is case insensitive.
     * 
     * **Validates: Requirements 2.4, 6.1**
     */
    public function test_extension_validation_case_insensitive()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['jpg', 'png']
        ];

        // Test uppercase extensions (should be allowed)
        $uppercaseExtensions = ['JPG', 'PNG', 'Jpg', 'pNg'];
        foreach ($uppercaseExtensions as $ext) {
            $file = $this->createMockUploadedFile("test.{$ext}", 1024, 'image/jpeg');
            
            try {
                $this->validateUploadedFileMethod->invoke($this->controller, $file, 'image', $config, 'test-correlation-id');
                $this->assertTrue(true, "Extension .{$ext} should be allowed (case insensitive)");
            } catch (ValidationException $e) {
                $errors = $e->errors()['file'] ?? [];
                $extensionErrors = array_filter($errors, fn($error) => str_contains($error, 'not allowed'));
                $this->assertEmpty($extensionErrors, "Extension .{$ext} should not have extension validation errors (case insensitive)");
            }
        }
    }

    /**
     * Test extension validation error messages include allowed types.
     * 
     * **Validates: Requirements 1.3, 2.4**
     */
    public function test_extension_validation_error_messages_include_allowed_types()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['jpg', 'png', 'gif', 'webp']
        ];

        $disallowedFile = $this->createMockUploadedFile('document.pdf', 1024, 'application/pdf');
        
        try {
            $this->validateUploadedFileMethod->invoke($this->controller, $disallowedFile, 'image', $config, 'test-correlation-id');
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errors = $e->errors()['file'] ?? [];
            $extensionError = collect($errors)->first(fn($error) => str_contains($error, 'not allowed'));
            
            $this->assertNotNull($extensionError, 'Should have extension validation error');
            $this->assertStringContainsString('pdf', $extensionError, 'Error should mention the disallowed extension');
            $this->assertStringContainsString('Allowed types:', $extensionError, 'Error should list allowed types');
            $this->assertStringContainsString('.jpg', $extensionError, 'Error should include .jpg');
            $this->assertStringContainsString('.png', $extensionError, 'Error should include .png');
            $this->assertStringContainsString('.gif', $extensionError, 'Error should include .gif');
            $this->assertStringContainsString('.webp', $extensionError, 'Error should include .webp');
        }
    }

    // ========================================
    // MIME Type Validation Tests
    // ========================================

    /**
     * Test MIME type validation for valid combinations.
     * 
     * **Validates: Requirements 2.5, 6.2**
     */
    public function test_mime_type_validation_valid_combinations()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['jpg', 'png', 'pdf']
        ];

        // Test valid MIME type combinations
        $validCombinations = [
            ['jpg', 'image/jpeg'],
            ['png', 'image/png'],
            ['pdf', 'application/pdf']
        ];

        foreach ($validCombinations as [$extension, $mimeType]) {
            $file = $this->createMockUploadedFile("test.{$extension}", 1024, $mimeType);
            
            try {
                $this->validateUploadedFileMethod->invoke($this->controller, $file, 'image', $config, 'test-correlation-id');
                $this->assertTrue(true, "Valid MIME type {$mimeType} for .{$extension} should be allowed");
            } catch (ValidationException $e) {
                $errors = $e->errors()['file'] ?? [];
                $mimeErrors = array_filter($errors, fn($error) => str_contains($error, 'MIME type'));
                $this->assertEmpty($mimeErrors, "Valid MIME type {$mimeType} for .{$extension} should not have MIME validation errors");
            }
        }
    }

    /**
     * Test MIME type validation prevents spoofing.
     * 
     * **Validates: Requirements 2.5, 6.2**
     */
    public function test_mime_type_validation_prevents_spoofing()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['jpg', 'png']
        ];

        // Test MIME type spoofing attempts
        $spoofingAttempts = [
            ['jpg', 'text/plain'],           // Text file with .jpg extension
            ['jpg', 'application/pdf'],      // PDF file with .jpg extension
            ['png', 'text/html'],           // HTML file with .png extension
            ['jpg', 'application/octet-stream'], // Binary file with .jpg extension
        ];

        foreach ($spoofingAttempts as [$extension, $mimeType]) {
            $file = $this->createMockUploadedFile("spoofed.{$extension}", 1024, $mimeType);
            
            try {
                $this->validateUploadedFileMethod->invoke($this->controller, $file, 'image', $config, 'test-correlation-id');
                $this->fail("MIME type spoofing should be detected for .{$extension} with {$mimeType}");
            } catch (ValidationException $e) {
                $errors = $e->errors()['file'] ?? [];
                $mimeError = collect($errors)->first(fn($error) => str_contains($error, 'MIME type'));
                
                $this->assertNotNull($mimeError, "Should detect MIME type spoofing for .{$extension} with {$mimeType}");
                $this->assertStringContainsString('does not match', $mimeError, 'Error should indicate MIME type mismatch');
                $this->assertStringContainsString('corrupted', $mimeError, 'Error should suggest file corruption');
            }
        }
    }

    /**
     * Test MIME type validation with multiple valid MIME types per extension.
     * 
     * **Validates: Requirements 2.5, 6.2**
     */
    public function test_mime_type_validation_multiple_valid_types()
    {
        // Get MIME types for extensions
        $extensions = ['jpg', 'jpeg'];
        $mimeTypes = $this->getMimeTypesForExtensionsMethod->invoke($this->controller, $extensions);
        
        $this->assertIsArray($mimeTypes, 'Should return array of MIME types');
        $this->assertContains('image/jpeg', $mimeTypes, 'Should include image/jpeg for jpg/jpeg extensions');
        
        // Test that both jpg and jpeg extensions work with image/jpeg MIME type
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['jpg', 'jpeg']
        ];

        $validFiles = [
            $this->createMockUploadedFile('test.jpg', 1024, 'image/jpeg'),
            $this->createMockUploadedFile('test.jpeg', 1024, 'image/jpeg')
        ];

        foreach ($validFiles as $file) {
            try {
                $this->validateUploadedFileMethod->invoke($this->controller, $file, 'image', $config, 'test-correlation-id');
                $this->assertTrue(true, 'Valid MIME type should be allowed for both jpg and jpeg extensions');
            } catch (ValidationException $e) {
                $errors = $e->errors()['file'] ?? [];
                $mimeErrors = array_filter($errors, fn($error) => str_contains($error, 'MIME type'));
                $this->assertEmpty($mimeErrors, 'Valid MIME type should not have MIME validation errors');
            }
        }
    }

    /**
     * Test comprehensive MIME type mapping exists.
     * 
     * **Validates: Requirements 6.1, 6.2**
     */
    public function test_comprehensive_mime_type_mapping_exists()
    {
        // Test that MIME type mapping exists for common file types
        $commonExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp',  // Images
            'mp4', 'webm', 'ogg', 'mov', 'avi',          // Videos
            'mp3', 'wav', 'ogg', 'm4a', 'aac',           // Audio
            'pdf'                                         // Documents
        ];

        foreach ($commonExtensions as $extension) {
            $mimeTypes = $this->getMimeTypesForExtensionsMethod->invoke($this->controller, [$extension]);
            
            $this->assertIsArray($mimeTypes, "Should return MIME types array for .{$extension}");
            $this->assertNotEmpty($mimeTypes, "Should have at least one MIME type for .{$extension}");
            
            // Each MIME type should be a valid format
            foreach ($mimeTypes as $mimeType) {
                $this->assertStringContainsString('/', $mimeType, "MIME type '{$mimeType}' should contain '/'");
                $this->assertGreaterThan(3, strlen($mimeType), "MIME type '{$mimeType}' should be meaningful");
            }
        }
    }

    // ========================================
    // Utility Method Tests
    // ========================================

    /**
     * Test byte conversion utility handles different units.
     * 
     * **Validates: Requirements 1.2, 3.2**
     */
    public function test_convert_to_bytes_handles_different_units()
    {
        // Test bytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '1024');
        $this->assertEquals(1024, $result);

        // Test kilobytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '2K');
        $this->assertEquals(2048, $result);

        $result = $this->convertToBytesMethod->invoke($this->controller, '2k');
        $this->assertEquals(2048, $result);

        // Test megabytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '5M');
        $this->assertEquals(5242880, $result);

        $result = $this->convertToBytesMethod->invoke($this->controller, '5m');
        $this->assertEquals(5242880, $result);

        // Test gigabytes
        $result = $this->convertToBytesMethod->invoke($this->controller, '1G');
        $this->assertEquals(1073741824, $result);

        $result = $this->convertToBytesMethod->invoke($this->controller, '1g');
        $this->assertEquals(1073741824, $result);
    }

    /**
     * Test byte formatting utility returns human-readable format.
     * 
     * **Validates: Requirements 1.2**
     */
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

        $result = $this->formatBytesMethod->invoke($this->controller, 2147483648);
        $this->assertEquals('2 GB', $result);
    }

    // ========================================
    // Edge Case Tests
    // ========================================

    /**
     * Test validation handles empty file detection.
     * 
     * **Validates: Requirements 2.3**
     */
    public function test_validation_detects_empty_files()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => ['txt']
        ];

        $emptyFile = $this->createMockUploadedFile('empty.txt', 0, 'text/plain');
        
        try {
            $this->validateUploadedFileMethod->invoke($this->controller, $emptyFile, 'pdf', $config, 'test-correlation-id');
            $this->fail('Empty file should be rejected');
        } catch (ValidationException $e) {
            $errors = $e->errors()['file'] ?? [];
            
            // Empty files might be detected as MIME type mismatch or empty file error
            $hasEmptyError = collect($errors)->contains(fn($error) => str_contains($error, 'empty'));
            $hasMimeError = collect($errors)->contains(fn($error) => str_contains($error, 'MIME type') || str_contains($error, 'corrupted'));
            
            $this->assertTrue($hasEmptyError || $hasMimeError, 'Should detect empty file or MIME type issue');
            
            if ($hasEmptyError) {
                $emptyError = collect($errors)->first(fn($error) => str_contains($error, 'empty'));
                $this->assertStringContainsString('empty', $emptyError, 'Error should mention empty file');
            }
        }
    }

    /**
     * Test validation handles missing extension configuration.
     * 
     * **Validates: Requirements 2.4, 6.1**
     */
    public function test_validation_handles_missing_extension_configuration()
    {
        $config = [
            'max_file_size' => 10 * 1024 * 1024,
            'allowed_extensions' => [] // Empty extensions array
        ];

        $file = $this->createMockUploadedFile('test.jpg', 1024, 'image/jpeg');
        
        try {
            $this->validateUploadedFileMethod->invoke($this->controller, $file, 'image', $config, 'test-correlation-id');
            $this->fail('Missing extension configuration should be handled');
        } catch (ValidationException $e) {
            $errors = $e->errors()['file'] ?? [];
            $configError = collect($errors)->first(fn($error) => str_contains($error, 'configured'));
            
            $this->assertNotNull($configError, 'Should detect missing extension configuration');
            $this->assertStringContainsString('No file extensions are configured', $configError, 'Error should mention missing configuration');
            $this->assertStringContainsString('Contact your administrator', $configError, 'Error should suggest contacting administrator');
        }
    }

    /**
     * Test validation provides multiple error messages for multiple failures.
     * 
     * **Validates: Requirements 1.1, 1.2, 1.3, 2.3**
     */
    public function test_validation_provides_multiple_error_messages()
    {
        $config = [
            'max_file_size' => 1024, // 1KB limit
            'allowed_extensions' => ['jpg', 'png']
        ];

        // Create file that fails multiple validations: wrong extension, too large, wrong MIME type
        $multiFailFile = $this->createMockUploadedFile('large.exe', 2048, 'application/octet-stream');
        
        try {
            $this->validateUploadedFileMethod->invoke($this->controller, $multiFailFile, 'image', $config, 'test-correlation-id');
            $this->fail('Multiple validation failures should throw exception');
        } catch (ValidationException $e) {
            $errors = $e->errors()['file'] ?? [];
            
            $this->assertGreaterThan(1, count($errors), 'Should have multiple error messages');
            
            // Check for size error
            $sizeError = collect($errors)->first(fn($error) => str_contains($error, 'exceeds maximum'));
            $this->assertNotNull($sizeError, 'Should have size validation error');
            
            // Check for extension error
            $extensionError = collect($errors)->first(fn($error) => str_contains($error, 'not allowed'));
            $this->assertNotNull($extensionError, 'Should have extension validation error');
            
            // Check for MIME type error
            $mimeError = collect($errors)->first(fn($error) => str_contains($error, 'MIME type'));
            $this->assertNotNull($mimeError, 'Should have MIME type validation error');
        }
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Create a mock UploadedFile for testing.
     */
    private function createMockUploadedFile(string $name, int $size, string $mimeType): UploadedFile
    {
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test_upload_');
        file_put_contents($tempFile, str_repeat('x', $size)); // Fill with dummy data
        
        // Track temp file for cleanup
        $this->tempFiles[] = $tempFile;
        
        $file = new UploadedFile(
            $tempFile,
            $name,
            $mimeType,
            UPLOAD_ERR_OK,
            true // test mode
        );
        
        return $file;
    }
}