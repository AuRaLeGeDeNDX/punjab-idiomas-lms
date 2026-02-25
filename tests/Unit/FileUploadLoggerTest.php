<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FileUploadLogger;
use App\Models\Content;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Mockery;

/**
 * Unit tests for FileUploadLogger service.
 * 
 * Tests comprehensive logging functionality for file upload attempts,
 * validation failures, success tracking, and performance monitoring.
 * 
 * Requirements: 4.1, 4.2, 4.4, 4.5
 */
class FileUploadLoggerTest extends TestCase
{
    private FileUploadLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->logger = new FileUploadLogger();
        
        // Mock authentication without database
        Auth::shouldReceive('id')->andReturn(1);
        Auth::shouldReceive('user')->andReturn((object) [
            'id' => 1,
            'email' => 'test@example.com',
            'roles' => collect([
                (object) ['name' => 'teacher']
            ])
        ]);
    }

    public function test_it_logs_upload_attempt_with_comprehensive_context()
    {
        // Arrange
        Log::shouldReceive('info')->once()->with(
            'File upload attempt initiated',
            Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['timestamp']) &&
                       isset($data['user_id']) &&
                       isset($data['request_info']) &&
                       isset($data['server_config']) &&
                       isset($data['resource_availability']) &&
                       $data['user_id'] === 1;
            })
        );

        $request = Request::create('/test', 'POST', ['type' => 'image']);

        // Act
        $correlationId = $this->logger->logUploadAttempt($request, null, ['test' => 'context']);

        // Assert
        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
    }

    public function test_it_logs_validation_failure_with_error_categorization()
    {
        // Arrange
        Log::shouldReceive('warning')->once()->with(
            'File upload validation failed',
            Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['validation_errors']) &&
                       isset($data['error_categories']) &&
                       isset($data['server_config_at_failure']) &&
                       isset($data['resource_availability_at_failure']);
            })
        );

        // Mock the trackUploadFailure method call
        Log::shouldReceive('channel')->with('metrics')->andReturnSelf();
        Log::shouldReceive('info')->with('Upload failure tracked', Mockery::any());

        $validationErrors = [
            'file' => ['File is too large (5MB). Maximum allowed size is 2MB.']
        ];

        // Act
        $this->logger->logValidationFailure('test-correlation-id', $validationErrors, ['test' => 'context']);

        // Assert - Mockery will verify the expectations
        $this->assertTrue(true);
    }

    public function test_it_categorizes_validation_errors_correctly()
    {
        // Arrange
        $validationErrors = [
            'file' => [
                'File is too large (5MB). Maximum allowed size is 2MB.',
                'File type .exe is not allowed.',
                'Server configuration error: missing temporary directory',
                'Insufficient memory for processing this upload.',
                'Security scan detected malicious content.',
                'Unknown validation error'
            ]
        ];

        Log::shouldReceive('warning')->once()->with(
            'File upload validation failed',
            Mockery::on(function ($data) {
                $categories = $data['error_categories'];
                return isset($categories['file_size']) &&
                       isset($categories['file_type']) &&
                       isset($categories['server_config']) &&
                       isset($categories['resource_limits']) &&
                       isset($categories['security']) &&
                       isset($categories['other']);
            })
        );

        // Mock metrics logging
        Log::shouldReceive('channel')->with('metrics')->andReturnSelf();
        Log::shouldReceive('info')->with('Upload failure tracked', Mockery::any());

        // Act
        $this->logger->logValidationFailure('test-correlation-id', $validationErrors);

        // Assert - Mockery will verify the expectations
        $this->assertTrue(true);
    }

    public function test_it_logs_php_upload_error_with_diagnostics()
    {
        // Arrange
        Log::shouldReceive('error')->once()->with(
            'PHP file upload error occurred',
            Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['php_error_code']) &&
                       isset($data['php_error_message']) &&
                       isset($data['diagnostic_info']) &&
                       isset($data['server_config']) &&
                       $data['php_error_code'] === UPLOAD_ERR_INI_SIZE;
            })
        );

        // Mock metrics logging
        Log::shouldReceive('channel')->with('metrics')->andReturnSelf();
        Log::shouldReceive('info')->with('Upload failure tracked', Mockery::any());

        // Act
        $this->logger->logPhpUploadError('test-correlation-id', UPLOAD_ERR_INI_SIZE, null, ['test' => 'context']);

        // Assert - Mockery will verify the expectations
        $this->assertTrue(true);
    }

    public function test_it_logs_upload_performance_metrics()
    {
        // Arrange
        $performanceMetrics = [
            'upload_duration' => 2.5,
            'file_size' => 1024000,
            'processing_time' => 1.2,
        ];

        Log::shouldReceive('info')->once()->with(
            'File upload performance metrics',
            Mockery::on(function ($data) {
                return isset($data['correlation_id']) &&
                       isset($data['performance_metrics']) &&
                       isset($data['server_load']) &&
                       isset($data['resource_usage']);
            })
        );

        // Act
        $this->logger->logUploadPerformance('test-correlation-id', $performanceMetrics);

        // Assert - Mockery will verify the expectations
        $this->assertTrue(true);
    }

    public function test_it_formats_bytes_correctly()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('formatBytes');
        $method->setAccessible(true);

        // Test various byte sizes
        $this->assertEquals('0 Bytes', $method->invoke($this->logger, 0));
        $this->assertEquals('1 Bytes', $method->invoke($this->logger, 1));
        $this->assertEquals('1 KB', $method->invoke($this->logger, 1024));
        $this->assertEquals('1 MB', $method->invoke($this->logger, 1024 * 1024));
        $this->assertEquals('1 GB', $method->invoke($this->logger, 1024 * 1024 * 1024));
        $this->assertEquals('2.5 KB', $method->invoke($this->logger, 2560));
    }

    public function test_it_converts_ini_sizes_to_bytes_correctly()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('convertToBytes');
        $method->setAccessible(true);

        // Test various ini size formats
        $this->assertEquals(-1, $method->invoke($this->logger, '-1'));
        $this->assertEquals(-1, $method->invoke($this->logger, ''));
        $this->assertEquals(1024, $method->invoke($this->logger, '1K'));
        $this->assertEquals(1024, $method->invoke($this->logger, '1k'));
        $this->assertEquals(1024 * 1024, $method->invoke($this->logger, '1M'));
        $this->assertEquals(1024 * 1024, $method->invoke($this->logger, '1m'));
        $this->assertEquals(1024 * 1024 * 1024, $method->invoke($this->logger, '1G'));
        $this->assertEquals(1024 * 1024 * 1024, $method->invoke($this->logger, '1g'));
        $this->assertEquals(2 * 1024 * 1024, $method->invoke($this->logger, '2M'));
    }

    public function test_it_gets_php_error_messages_correctly()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getPhpErrorMessage');
        $method->setAccessible(true);

        // Test all PHP upload error codes
        $this->assertEquals('No error', $method->invoke($this->logger, UPLOAD_ERR_OK));
        $this->assertEquals('File exceeds upload_max_filesize', $method->invoke($this->logger, UPLOAD_ERR_INI_SIZE));
        $this->assertEquals('File exceeds MAX_FILE_SIZE', $method->invoke($this->logger, UPLOAD_ERR_FORM_SIZE));
        $this->assertEquals('File was only partially uploaded', $method->invoke($this->logger, UPLOAD_ERR_PARTIAL));
        $this->assertEquals('No file was uploaded', $method->invoke($this->logger, UPLOAD_ERR_NO_FILE));
        $this->assertEquals('Missing temporary folder', $method->invoke($this->logger, UPLOAD_ERR_NO_TMP_DIR));
        $this->assertEquals('Failed to write file to disk', $method->invoke($this->logger, UPLOAD_ERR_CANT_WRITE));
        $this->assertEquals('Upload stopped by extension', $method->invoke($this->logger, UPLOAD_ERR_EXTENSION));
        $this->assertEquals('Unknown upload error', $method->invoke($this->logger, 999));
    }

    public function test_it_gets_server_configuration()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getServerConfiguration');
        $method->setAccessible(true);

        $config = $method->invoke($this->logger);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('php_version', $config);
        $this->assertArrayHasKey('file_uploads', $config);
        $this->assertArrayHasKey('upload_max_filesize', $config);
        $this->assertArrayHasKey('post_max_size', $config);
        $this->assertArrayHasKey('memory_limit', $config);
        $this->assertArrayHasKey('upload_tmp_dir', $config);
    }

    public function test_it_gets_resource_availability()
    {
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getResourceAvailability');
        $method->setAccessible(true);

        $resources = $method->invoke($this->logger);

        $this->assertIsArray($resources);
        $this->assertArrayHasKey('memory_usage', $resources);
        $this->assertArrayHasKey('disk_space', $resources);
        $this->assertArrayHasKey('server_load', $resources);
        
        // Check memory usage structure
        $this->assertArrayHasKey('current', $resources['memory_usage']);
        $this->assertArrayHasKey('peak', $resources['memory_usage']);
        $this->assertArrayHasKey('limit', $resources['memory_usage']);
        
        // Check disk space structure
        $this->assertArrayHasKey('upload_directory', $resources['disk_space']);
        $this->assertArrayHasKey('temp_directory', $resources['disk_space']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}