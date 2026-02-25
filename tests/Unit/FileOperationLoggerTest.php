<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FileOperationLogger;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileOperationLoggerTest extends TestCase
{
    use RefreshDatabase;

    private FileOperationLogger $logger;
    private User $user;
    private Content $content;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->logger = new \App\Services\FileOperationLogger();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com'
        ]);
        
        // Create test content
        $this->content = Content::factory()->create([
            'file_path' => 'test/sample.jpg',
            'storage_disk' => 'public',
            'original_filename' => 'sample.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image'
        ]);
        
        // Mock storage disks
        Storage::fake('public');
        Storage::fake('protected');
        
        // Clear log expectations
        Log::spy();
    }

    public function test_log_file_serving_error_creates_comprehensive_log()
    {
        Auth::login($this->user);
        
        $error = 'File not found at specified location';
        $context = ['additional_info' => 'test context'];
        
        $correlationId = $this->logger->logFileServingError($this->content, $error, $context);
        
        // Verify correlation ID is returned
        $this->assertIsString($correlationId);
        $this->assertNotEmpty($correlationId);
        
        // Verify log was created with correct structure
        Log::shouldHaveReceived('error')
            ->once()
            ->with('File serving error occurred', \Mockery::on(function ($logData) use ($correlationId, $error, $context) {
                return $logData['correlation_id'] === $correlationId
                    && $logData['event_type'] === 'file_serving_error'
                    && $logData['user_id'] === $this->user->id
                    && $logData['user_email'] === $this->user->email
                    && $logData['content_id'] === $this->content->id
                    && $logData['error_details']['error_message'] === $error
                    && $logData['error_details']['error_category'] === 'file_not_found'
                    && $logData['context'] === $context
                    && isset($logData['diagnostic_info'])
                    && isset($logData['server_info'])
                    && isset($logData['request_info']);
            }));
    }

    public function test_log_url_generation_failure_categorizes_failures_correctly()
    {
        Auth::login($this->user);
        
        $failureReason = 'Route not found for secure file access';
        
        $correlationId = $this->logger->logUrlGenerationFailure($this->content, $failureReason);
        
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('URL generation failed', \Mockery::on(function ($logData) use ($failureReason) {
                return $logData['failure_details']['failure_category'] === 'routing_error'
                    && $logData['failure_details']['is_retryable'] === false
                    && in_array('Verify secure file routes are properly configured', $logData['failure_details']['suggested_actions']);
            }));
    }

    public function test_log_file_access_records_security_audit_information()
    {
        Auth::login($this->user);
        
        $accessMethod = 'signed_url';
        $context = ['ip_address' => '192.168.1.1'];
        
        $correlationId = $this->logger->logFileAccess($this->content, $accessMethod, $context);
        
        Log::shouldHaveReceived('info')
            ->once()
            ->with('File access granted', \Mockery::on(function ($logData) use ($accessMethod) {
                return $logData['access_details']['access_method'] === $accessMethod
                    && $logData['access_details']['access_granted'] === true
                    && $logData['security_audit']['user_authenticated'] === true
                    && $logData['security_audit']['secure_access'] === true
                    && isset($logData['security_audit']['ip_address'])
                    && isset($logData['security_audit']['user_agent']);
            }));
    }

    public function test_log_file_access_denial_assesses_security_risk()
    {
        Auth::login($this->user);
        
        $denialReason = 'Brute force attempt detected';
        $context = ['access_method' => 'direct', 'rapid_requests' => 15];
        
        $correlationId = $this->logger->logFileAccessDenial($this->content, $denialReason, $context);
        
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('File access denied', \Mockery::on(function ($logData) use ($denialReason) {
                return $logData['denial_details']['denial_reason'] === $denialReason
                    && $logData['denial_details']['security_risk_level'] === 'high'
                    && $logData['security_audit']['suspicious_activity'] === true
                    && $logData['security_audit']['user_authorized'] === false;
            }));
    }

    public function test_log_file_operation_uses_appropriate_log_levels()
    {
        $operation = 'file_upload';
        
        // Test success logging
        $correlationId = $this->logger->logFileOperation($operation, $this->content, 'success');
        Log::shouldHaveReceived('info')->with("File operation completed: {$operation}", \Mockery::any());
        
        // Test failure logging
        $details = ['error' => 'Upload failed'];
        $correlationId = $this->logger->logFileOperation($operation, $this->content, 'failure', $details);
        Log::shouldHaveReceived('error')->with("File operation failed: {$operation}", \Mockery::any());
        
        // Test warning logging
        $correlationId = $this->logger->logFileOperation($operation, $this->content, 'warning');
        Log::shouldHaveReceived('warning')->with("File operation warning: {$operation}", \Mockery::any());
    }

    public function test_log_storage_inconsistency_provides_repair_recommendations()
    {
        $inconsistencyDetails = [
            'recorded_disk' => 'public',
            'actual_disk' => 'protected',
            'file_not_found' => false
        ];
        
        $correlationId = $this->logger->logStorageInconsistency($this->content, $inconsistencyDetails);
        
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('File storage inconsistency detected', \Mockery::on(function ($logData) use ($inconsistencyDetails) {
                return $logData['inconsistency_details'] === $inconsistencyDetails
                    && $logData['severity'] === 'high'
                    && in_array("Update storage_disk field from 'public' to 'protected'", $logData['repair_recommendations']);
            }));
    }

    public function test_gather_diagnostic_info_checks_file_existence()
    {
        // Create a test file in public storage
        Storage::disk('public')->put($this->content->file_path, 'test content');
        
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('gatherDiagnosticInfo');
        $method->setAccessible(true);
        
        $diagnosticInfo = $method->invoke($this->logger, $this->content);
        
        $this->assertTrue($diagnosticInfo['file_existence_check']['public_disk']);
        $this->assertFalse($diagnosticInfo['file_existence_check']['protected_disk']);
        $this->assertEquals('public', $diagnosticInfo['recorded_storage_info']['storage_disk']);
        $this->assertEquals('asset_url', $diagnosticInfo['url_generation_method']);
        $this->assertArrayHasKey('storage_configuration', $diagnosticInfo);
    }

    public function test_categorize_error_identifies_error_types_correctly()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('categorizeError');
        $method->setAccessible(true);
        
        $this->assertEquals('file_not_found', $method->invoke($this->logger, 'File does not exist'));
        $this->assertEquals('permission_error', $method->invoke($this->logger, 'Access denied due to permissions'));
        $this->assertEquals('storage_error', $method->invoke($this->logger, 'Storage disk unavailable'));
        $this->assertEquals('url_generation_error', $method->invoke($this->logger, 'URL route not found'));
        $this->assertEquals('configuration_error', $method->invoke($this->logger, 'Configuration invalid'));
        $this->assertEquals('unknown_error', $method->invoke($this->logger, 'Some random error'));
    }

    public function test_assess_error_severity_returns_appropriate_levels()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('assessErrorSeverity');
        $method->setAccessible(true);
        
        $this->assertEquals('critical', $method->invoke($this->logger, 'Critical system failure'));
        $this->assertEquals('high', $method->invoke($this->logger, 'Security breach detected'));
        $this->assertEquals('medium', $method->invoke($this->logger, 'File not found'));
        $this->assertEquals('low', $method->invoke($this->logger, 'Minor warning'));
    }

    public function test_url_failure_suggestions_provide_actionable_advice()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getUrlFailureSuggestions');
        $method->setAccessible(true);
        
        $suggestions = $method->invoke($this->logger, 'File not found');
        $this->assertContains('Check if file exists in expected storage location', $suggestions);
        $this->assertContains('Run file path repair service to locate actual file', $suggestions);
        
        $suggestions = $method->invoke($this->logger, 'Route configuration error');
        $this->assertContains('Verify secure file routes are properly configured', $suggestions);
        $this->assertContains('Check route caching and clear if necessary', $suggestions);
    }

    public function test_is_suspicious_activity_detects_threats()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('isSuspiciousActivity');
        $method->setAccessible(true);
        
        $this->assertTrue($method->invoke($this->logger, 'Brute force attempt', []));
        $this->assertTrue($method->invoke($this->logger, 'Access denied', ['rapid_requests' => 15]));
        $this->assertTrue($method->invoke($this->logger, 'Unauthorized', ['unusual_user_agent' => true]));
        $this->assertTrue($method->invoke($this->logger, 'Access denied', ['ip_reputation' => 'suspicious']));
        $this->assertFalse($method->invoke($this->logger, 'Session expired', []));
    }

    public function test_repair_recommendations_address_specific_issues()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getRepairRecommendations');
        $method->setAccessible(true);
        
        $inconsistency = [
            'recorded_disk' => 'public',
            'actual_disk' => 'protected'
        ];
        $recommendations = $method->invoke($this->logger, $inconsistency);
        $this->assertContains("Update storage_disk field from 'public' to 'protected'", $recommendations);
        
        $inconsistency = ['file_not_found' => true];
        $recommendations = $method->invoke($this->logger, $inconsistency);
        $this->assertContains('Search for file in alternative storage locations', $recommendations);
        $this->assertContains('Check if file was moved or deleted', $recommendations);
    }

    public function test_assess_inconsistency_severity_prioritizes_correctly()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('assessInconsistencySeverity');
        $method->setAccessible(true);
        
        $this->assertEquals('critical', $method->invoke($this->logger, ['file_not_found' => true]));
        $this->assertEquals('high', $method->invoke($this->logger, ['recorded_disk' => 'public', 'actual_disk' => 'protected']));
        $this->assertEquals('medium', $method->invoke($this->logger, ['path_mismatch' => true]));
        $this->assertEquals('low', $method->invoke($this->logger, []));
    }

    public function test_console_context_logging()
    {
        // Simulate console environment
        $this->app['env'] = 'testing';
        
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getRequestInfo');
        $method->setAccessible(true);
        
        // Mock console environment
        $_SERVER['argv'] = ['artisan', 'repair:files'];
        
        $requestInfo = $method->invoke($this->logger);
        
        $this->assertEquals('console', $requestInfo['context']);
        $this->assertArrayHasKey('command', $requestInfo);
        $this->assertArrayHasKey('arguments', $requestInfo);
    }

    public function test_storage_configuration_gathering()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getStorageConfiguration');
        $method->setAccessible(true);
        
        $config = $method->invoke($this->logger);
        
        $this->assertArrayHasKey('default_disk', $config);
        $this->assertArrayHasKey('public_disk_root', $config);
        $this->assertArrayHasKey('protected_disk_root', $config);
        $this->assertArrayHasKey('available_disks', $config);
    }

    public function test_server_info_gathering()
    {
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('getServerInfo');
        $method->setAccessible(true);
        
        $serverInfo = $method->invoke($this->logger);
        
        $this->assertArrayHasKey('php_version', $serverInfo);
        $this->assertArrayHasKey('laravel_version', $serverInfo);
        $this->assertArrayHasKey('environment', $serverInfo);
        $this->assertArrayHasKey('memory_usage', $serverInfo);
        $this->assertArrayHasKey('memory_peak', $serverInfo);
    }
}