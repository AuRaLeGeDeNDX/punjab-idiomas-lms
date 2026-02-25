<?php

namespace Tests\Unit;

use App\Services\FileUploadErrorFormatter;
use Tests\TestCase;

class FileUploadErrorFormatterTest extends TestCase
{
    /**
     * Test file size formatting with various byte values.
     */
    public function test_format_bytes_with_various_sizes(): void
    {
        // Test bytes
        $this->assertEquals('0 Bytes', FileUploadErrorFormatter::formatBytes(0));
        $this->assertEquals('512 Bytes', FileUploadErrorFormatter::formatBytes(512));
        $this->assertEquals('1,023 Bytes', FileUploadErrorFormatter::formatBytes(1023));
        
        // Test kilobytes
        $this->assertEquals('1 KB', FileUploadErrorFormatter::formatBytes(1024));
        $this->assertEquals('1.5 KB', FileUploadErrorFormatter::formatBytes(1536));
        $this->assertEquals('100 KB', FileUploadErrorFormatter::formatBytes(102400));
        
        // Test megabytes
        $this->assertEquals('1 MB', FileUploadErrorFormatter::formatBytes(1048576));
        $this->assertEquals('2.5 MB', FileUploadErrorFormatter::formatBytes(2621440));
        $this->assertEquals('100 MB', FileUploadErrorFormatter::formatBytes(104857600));
        
        // Test gigabytes
        $this->assertEquals('1 GB', FileUploadErrorFormatter::formatBytes(1073741824));
        $this->assertEquals('2.5 GB', FileUploadErrorFormatter::formatBytes(2684354560));
    }
    
    /**
     * Test file size formatting with custom precision.
     */
    public function test_format_bytes_with_custom_precision(): void
    {
        // Test with 2 decimal places
        $this->assertEquals('1.50 KB', FileUploadErrorFormatter::formatBytes(1536, 2));
        $this->assertEquals('2.50 MB', FileUploadErrorFormatter::formatBytes(2621440, 2));
        
        // Test with 0 decimal places
        $this->assertEquals('2 KB', FileUploadErrorFormatter::formatBytes(1536, 0));
        $this->assertEquals('3 MB', FileUploadErrorFormatter::formatBytes(2621440, 0));
    }
    
    /**
     * Test size exceeded error formatting.
     */
    public function test_format_size_exceeded_error(): void
    {
        $context = [
            'actual_size' => 5242880, // 5MB
            'max_size' => 2097152,    // 2MB
            'content_type' => 'image'
        ];
        
        $result = FileUploadErrorFormatter::formatError('size_exceeded', $context);
        
        $this->assertStringContainsString('File is too large (5 MB)', $result);
        $this->assertStringContainsString('Maximum allowed size for image files is 2 MB', $result);
        $this->assertStringContainsString('Try compressing the file', $result);
    }
    
    /**
     * Test invalid extension error formatting.
     */
    public function test_format_invalid_extension_error(): void
    {
        $context = [
            'extension' => 'exe',
            'allowed_types' => '.jpg, .png, .gif',
            'content_type' => 'image'
        ];
        
        $result = FileUploadErrorFormatter::formatError('invalid_extension', $context);
        
        $this->assertStringContainsString("File type '.exe' is not supported", $result);
        $this->assertStringContainsString('Please use one of these file types: .jpg, .png, .gif', $result);
    }
    
    /**
     * Test MIME type mismatch error formatting.
     */
    public function test_format_mime_mismatch_error(): void
    {
        $context = [
            'file_mime' => 'application/octet-stream',
            'extension' => 'jpg'
        ];
        
        $result = FileUploadErrorFormatter::formatError('mime_mismatch', $context);
        
        $this->assertStringContainsString('File appears to be corrupted', $result);
        $this->assertStringContainsString('MIME type: application/octet-stream', $result);
        $this->assertStringContainsString("doesn't match the '.jpg' extension", $result);
    }
    
    /**
     * Test PHP upload error formatting for different error codes.
     */
    public function test_format_php_upload_errors(): void
    {
        // Test UPLOAD_ERR_INI_SIZE
        $context = [
            'error_code' => UPLOAD_ERR_INI_SIZE,
            'upload_max_filesize' => '2M'
        ];
        
        $result = FileUploadErrorFormatter::formatError('php_upload_error', $context);
        $this->assertStringContainsString('File exceeds server upload limit', $result);
        $this->assertStringContainsString('Current limit: 2M', $result);
        
        // Test UPLOAD_ERR_PARTIAL
        $context = ['error_code' => UPLOAD_ERR_PARTIAL];
        $result = FileUploadErrorFormatter::formatError('php_upload_error', $context);
        $this->assertStringContainsString('File upload was interrupted', $result);
        $this->assertStringContainsString('network interruption', $result);
        
        // Test UPLOAD_ERR_NO_FILE
        $context = ['error_code' => UPLOAD_ERR_NO_FILE];
        $result = FileUploadErrorFormatter::formatError('php_upload_error', $context);
        $this->assertStringContainsString('No file was selected', $result);
    }
    
    /**
     * Test server configuration error formatting.
     */
    public function test_format_server_config_error(): void
    {
        $context = [
            'config_issue' => 'File uploads are disabled',
            'recommended_action' => 'Enable file_uploads in PHP configuration'
        ];
        
        $result = FileUploadErrorFormatter::formatError('server_config', $context);
        
        $this->assertStringContainsString('File uploads are disabled', $result);
        $this->assertStringContainsString('Enable file_uploads in PHP configuration', $result);
    }
    
    /**
     * Test insufficient space error formatting.
     */
    public function test_format_insufficient_space_error(): void
    {
        $context = [
            'available_space' => 1048576,  // 1MB
            'required_space' => 5242880,   // 5MB
            'location' => 'temporary directory'
        ];
        
        $result = FileUploadErrorFormatter::formatError('insufficient_space', $context);
        
        $this->assertStringContainsString('Insufficient storage space on temporary directory', $result);
        $this->assertStringContainsString('Available: 1 MB', $result);
        $this->assertStringContainsString('Required: 5 MB', $result);
    }
    
    /**
     * Test empty file error formatting.
     */
    public function test_format_empty_file_error(): void
    {
        $context = ['file_name' => 'test.jpg'];
        
        $result = FileUploadErrorFormatter::formatError('empty_file', $context);
        
        $this->assertStringContainsString("File 'test.jpg' is empty (0 bytes)", $result);
        $this->assertStringContainsString('Please select a valid file with content', $result);
    }
    
    /**
     * Test memory limit error formatting.
     */
    public function test_format_memory_limit_error(): void
    {
        $context = [
            'file_size' => 52428800,      // 50MB
            'available_memory' => 10485760, // 10MB
            'required_memory' => 157286400, // 150MB
            'memory_limit' => 134217728    // 128MB
        ];
        
        $result = FileUploadErrorFormatter::formatError('memory_limit', $context);
        
        $this->assertStringContainsString('Insufficient memory for processing', $result);
        $this->assertStringContainsString('File size: 50 MB', $result);
        $this->assertStringContainsString('Available memory: 10 MB', $result);
        $this->assertStringContainsString('Required memory: 150 MB', $result);
        $this->assertStringContainsString('current: 128 MB', $result);
    }
    
    /**
     * Test image validation error formatting.
     */
    public function test_format_image_validation_error(): void
    {
        // Test general image validation error
        $context = [
            'file_name' => 'test.jpg',
            'issue' => 'has invalid image headers'
        ];
        
        $result = FileUploadErrorFormatter::formatError('image_validation', $context);
        
        $this->assertStringContainsString("File 'test.jpg' has invalid image headers", $result);
        $this->assertStringContainsString('valid image format', $result);
        
        // Test large dimensions error
        $context = [
            'file_name' => 'huge.jpg',
            'dimensions' => [
                'too_large' => true,
                'width' => 15000,
                'height' => 12000
            ]
        ];
        
        $result = FileUploadErrorFormatter::formatError('image_validation', $context);
        
        $this->assertStringContainsString('Image dimensions (15000x12000) are unusually large', $result);
        $this->assertStringContainsString('Consider resizing the image', $result);
    }
    
    /**
     * Test PDF validation error formatting.
     */
    public function test_format_pdf_validation_error(): void
    {
        $context = [
            'file_name' => 'document.pdf',
            'issue' => 'has invalid PDF headers'
        ];
        
        $result = FileUploadErrorFormatter::formatError('pdf_validation', $context);
        
        $this->assertStringContainsString("File 'document.pdf' has invalid PDF headers", $result);
        $this->assertStringContainsString('valid PDF document', $result);
        $this->assertStringContainsString('PDF viewer', $result);
    }
    
    /**
     * Test generic error formatting.
     */
    public function test_format_generic_error(): void
    {
        $context = [
            'message' => 'Unexpected error occurred',
            'suggestion' => 'Try again later'
        ];
        
        $result = FileUploadErrorFormatter::formatError('generic', $context);
        
        $this->assertStringContainsString('Unexpected error occurred', $result);
        $this->assertStringContainsString('Try again later', $result);
        $this->assertStringContainsString('contact your administrator', $result);
    }
    
    /**
     * Test unknown error type defaults to generic error.
     */
    public function test_unknown_error_type_defaults_to_generic(): void
    {
        $result = FileUploadErrorFormatter::formatError('unknown_error_type');
        
        $this->assertStringContainsString('Upload failed', $result);
        $this->assertStringContainsString('Please check your file and try again', $result);
    }
    
    /**
     * Test combining multiple errors.
     */
    public function test_combine_errors(): void
    {
        $errors = [
            'File is too large',
            'Invalid file type',
            'Server configuration error'
        ];
        
        $result = FileUploadErrorFormatter::combineErrors($errors);
        $this->assertEquals('File is too large Invalid file type Server configuration error', $result);
        
        // Test with custom separator
        $result = FileUploadErrorFormatter::combineErrors($errors, '; ');
        $this->assertEquals('File is too large; Invalid file type; Server configuration error', $result);
        
        // Test with single error
        $result = FileUploadErrorFormatter::combineErrors(['Single error']);
        $this->assertEquals('Single error', $result);
        
        // Test with empty array
        $result = FileUploadErrorFormatter::combineErrors([]);
        $this->assertEquals('Unknown error occurred.', $result);
    }
    
    /**
     * Test correlation ID generation.
     */
    public function test_generate_correlation_id(): void
    {
        $id1 = FileUploadErrorFormatter::generateCorrelationId();
        $id2 = FileUploadErrorFormatter::generateCorrelationId();
        
        // Should be valid UUIDs
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id1);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id2);
        
        // Should be unique
        $this->assertNotEquals($id1, $id2);
    }
    
    /**
     * Test creating standardized error context.
     */
    public function test_create_error_context(): void
    {
        $correlationId = 'test-correlation-id';
        $fileInfo = ['name' => 'test.jpg', 'size' => 1024];
        $serverConfig = ['upload_max_filesize' => '2M'];
        
        $context = FileUploadErrorFormatter::createErrorContext($correlationId, $fileInfo, $serverConfig);
        
        $this->assertEquals($correlationId, $context['correlation_id']);
        $this->assertEquals($fileInfo, $context['file_info']);
        $this->assertEquals($serverConfig, $context['server_config']);
        $this->assertArrayHasKey('timestamp', $context);
        $this->assertArrayHasKey('user_id', $context);
        // In unit tests, user_id should be null since no user is authenticated
        $this->assertNull($context['user_id']);
    }
    
    /**
     * Test timeout risk error formatting.
     */
    public function test_format_timeout_risk_error(): void
    {
        $context = [
            'file_size' => 104857600, // 100MB
            'max_execution_time' => 30
        ];
        
        $result = FileUploadErrorFormatter::formatError('timeout_risk', $context);
        
        $this->assertStringContainsString('Large file upload may timeout', $result);
        $this->assertStringContainsString('30 seconds', $result);
        $this->assertStringContainsString('File size: 100 MB', $result);
        $this->assertStringContainsString('increase max_execution_time', $result);
    }
}