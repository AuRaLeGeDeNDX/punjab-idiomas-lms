<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FileUploadLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetailedValidationLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected FileUploadLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new FileUploadLogger();
    }

    public function test_detailed_validation_failure_logging_method_exists()
    {
        $this->assertTrue(
            method_exists($this->logger, 'logDetailedValidationFailure'),
            'FileUploadLogger should have logDetailedValidationFailure method'
        );
    }

    public function test_detailed_validation_failure_logging_with_failed_rules()
    {
        // Mock the Log facade to capture log entries
        Log::shouldReceive('error')
            ->once()
            ->with('Detailed file upload validation failure', \Mockery::type('array'));

        Log::shouldReceive('channel')
            ->with('metrics')
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->once()
            ->with('Detailed validation failure tracked', \Mockery::type('array'));

        // Create test failed rules
        $failedRules = [
            [
                'rule_name' => 'file_size_limit',
                'rule_type' => 'file_size',
                'severity' => 'high',
                'is_retryable' => false,
                'rule_details' => [
                    'actual_size' => 11 * 1024 * 1024,
                    'max_allowed_size' => 10 * 1024 * 1024,
                    'content_type' => 'image',
                ],
                'error_message' => 'File is too large',
            ],
        ];

        // Test the detailed logging method
        $this->logger->logDetailedValidationFailure(
            'test-correlation-id-123',
            $failedRules,
            null, // No file for this test
            ['test_server_limits' => 'test_value'],
            ['test_context' => 'test_validation']
        );

        // If we get here without exceptions, the method works
        $this->assertTrue(true);
    }

    public function test_detailed_file_properties_method_exists()
    {
        // Test that the private method exists by checking if the class has the expected functionality
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getDetailedFileProperties');
        
        $this->assertTrue($method->isPrivate(), 'getDetailedFileProperties should be a private method');
    }

    public function test_failed_rules_categorization_methods_exist()
    {
        $reflection = new \ReflectionClass($this->logger);
        
        $this->assertTrue(
            $reflection->hasMethod('summarizeFailedRules'),
            'FileUploadLogger should have summarizeFailedRules method'
        );
        
        $this->assertTrue(
            $reflection->hasMethod('categorizeFailedRules'),
            'FileUploadLogger should have categorizeFailedRules method'
        );
        
        $this->assertTrue(
            $reflection->hasMethod('assessValidationSeverity'),
            'FileUploadLogger should have assessValidationSeverity method'
        );
        
        $this->assertTrue(
            $reflection->hasMethod('assessRetryLikelihood'),
            'FileUploadLogger should have assessRetryLikelihood method'
        );
    }

    public function test_enhanced_logging_includes_correlation_id_tracking()
    {
        // Mock the Log facade to capture the correlation ID
        Log::shouldReceive('error')
            ->once()
            ->with('Detailed file upload validation failure', \Mockery::on(function ($data) {
                return isset($data['correlation_id']) && 
                       $data['correlation_id'] === 'test-correlation-id-456' &&
                       isset($data['failed_validation_rules']) &&
                       isset($data['validation_rule_summary']) &&
                       isset($data['validation_context']);
            }));

        Log::shouldReceive('channel')
            ->with('metrics')
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->once()
            ->with('Detailed validation failure tracked', \Mockery::type('array'));

        $failedRules = [
            [
                'rule_name' => 'test_rule',
                'rule_type' => 'test_type',
                'severity' => 'medium',
                'is_retryable' => true,
                'error_message' => 'Test error',
            ],
        ];

        $this->logger->logDetailedValidationFailure(
            'test-correlation-id-456',
            $failedRules
        );

        $this->assertTrue(true);
    }

    public function test_enhanced_logging_includes_server_limits()
    {
        // Mock the Log facade to verify server limits are included
        Log::shouldReceive('error')
            ->once()
            ->with('Detailed file upload validation failure', \Mockery::on(function ($data) {
                return isset($data['server_limits']) &&
                       isset($data['server_limits']['php_version']) &&
                       isset($data['server_limits']['upload_max_filesize']) &&
                       isset($data['server_limits']['post_max_size']);
            }));

        Log::shouldReceive('channel')
            ->with('metrics')
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->once()
            ->with('Detailed validation failure tracked', \Mockery::type('array'));

        $failedRules = [
            [
                'rule_name' => 'test_rule',
                'rule_type' => 'test_type',
                'severity' => 'low',
                'error_message' => 'Test error',
            ],
        ];

        $this->logger->logDetailedValidationFailure(
            'test-correlation-id-789',
            $failedRules,
            null,
            ['custom_limit' => 'test_value']
        );

        $this->assertTrue(true);
    }
}