<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\FileOperationLogger;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FileOperationLoggerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private FileOperationLogger $logger;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->logger = app(FileOperationLogger::class);
        
        // Create test user with roles
        $this->user = User::factory()->create([
            'email' => 'integration@example.com'
        ]);
        
        // Set up storage disks
        Storage::fake('public');
        Storage::fake('protected');
        
        // Clear log expectations
        Log::spy();
    }

    public function test_complete_file_serving_error_workflow()
    {
        Auth::login($this->user);
        
        // Create content with file that doesn't exist
        $content = Content::factory()->create([
            'file_path' => 'missing/file.jpg',
            'storage_disk' => 'public',
            'original_filename' => 'file.jpg',
            'file_size' => 2048,
            'mime_type' => 'image/jpeg'
        ]);
        
        // Simulate a file serving error
        $error = 'The file does not exist or is not readable';
        $context = [
            'controller' => 'ContentBlockController',
            'action' => 'getSignedUrl',
            'request_id' => 'req_123456'
        ];
        
        $correlationId = $this->logger->logFileServingError($content, $error, $context);
        
        // Verify comprehensive logging occurred
        Log::shouldHaveReceived('error')
            ->once()
            ->with('File serving error occurred', \Mockery::on(function ($logData) use ($correlationId, $content, $error, $context) {
                // Verify all required fields are present
                $requiredFields = [
                    'correlation_id', 'event_type', 'timestamp', 'user_id', 'user_email',
                    'content_id', 'content_type', 'file_info', 'error_details',
                    'diagnostic_info', 'context', 'server_info', 'request_info'
                ];
                
                foreach ($requiredFields as $field) {
                    if (!isset($logData[$field])) {
                        return false;
                    }
                }
                
                // Verify specific values
                return $logData['correlation_id'] === $correlationId
                    && $logData['content_id'] === $content->id
                    && $logData['error_details']['error_message'] === $error
                    && $logData['error_details']['error_category'] === 'file_not_found'
                    && $logData['context'] === $context
                    && $logData['diagnostic_info']['file_existence_check']['public_disk'] === false
                    && $logData['diagnostic_info']['file_existence_check']['protected_disk'] === false;
            }));
        
        // Verify debug metric logging
        Log::shouldHaveReceived('debug')
            ->once()
            ->with('File operation error metric', \Mockery::on(function ($metricData) use ($correlationId) {
                return $metricData['correlation_id'] === $correlationId
                    && $metricData['metric_type'] === 'file_operation_error'
                    && $metricData['operation_type'] === 'file_serving_error'
                    && $metricData['error_category'] === 'file_not_found';
            }));
    }

    public function test_file_access_audit_trail_with_security_context()
    {
        Auth::login($this->user);
        
        // Create content with existing file
        $content = Content::factory()->create([
            'file_path' => 'secure/document.pdf',
            'storage_disk' => 'protected',
            'original_filename' => 'document.pdf',
            'file_size' => 5120,
            'mime_type' => 'application/pdf'
        ]);
        
        // Create the file in storage
        Storage::disk('protected')->put($content->file_path, 'PDF content here');
        
        // Simulate secure file access
        $accessMethod = 'secure_route';
        $context = [
            'controller' => 'SecureFileController',
            'permission_check' => 'passed',
            'access_level' => 'read'
        ];
        
        $correlationId = $this->logger->logFileAccess($content, $accessMethod, $context);
        
        // Verify security audit logging
        Log::shouldHaveReceived('info')
            ->once()
            ->with('File access granted', \Mockery::on(function ($logData) use ($correlationId, $content, $accessMethod) {
                return $logData['correlation_id'] === $correlationId
                    && $logData['event_type'] === 'file_access'
                    && $logData['content_id'] === $content->id
                    && $logData['access_details']['access_method'] === $accessMethod
                    && $logData['access_details']['access_granted'] === true
                    && $logData['security_audit']['user_authenticated'] === true
                    && $logData['security_audit']['secure_access'] === true
                    && isset($logData['security_audit']['ip_address'])
                    && isset($logData['user_roles']);
            }));
        
        // Verify success metric tracking
        Log::shouldHaveReceived('debug')
            ->once()
            ->with('File operation success metric', \Mockery::on(function ($metricData) use ($correlationId, $accessMethod) {
                return $metricData['correlation_id'] === $correlationId
                    && $metricData['operation_type'] === 'file_access'
                    && $metricData['details'] === $accessMethod;
            }));
    }

    public function test_storage_inconsistency_detection_and_repair_recommendations()
    {
        // Create content with inconsistent storage information
        $content = Content::factory()->create([
            'file_path' => 'images/photo.jpg',
            'storage_disk' => 'public',  // Recorded as public
            'original_filename' => 'photo.jpg',
            'file_size' => 3072,
            'mime_type' => 'image/jpeg'
        ]);
        
        // But actually store file in protected storage
        Storage::disk('protected')->put($content->file_path, 'Image data');
        
        $inconsistencyDetails = [
            'recorded_disk' => 'public',
            'actual_disk' => 'protected',
            'file_not_found' => false,
            'path_mismatch' => false
        ];
        
        $correlationId = $this->logger->logStorageInconsistency($content, $inconsistencyDetails);
        
        // Verify inconsistency logging with repair recommendations
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('File storage inconsistency detected', \Mockery::on(function ($logData) use ($correlationId, $inconsistencyDetails) {
                return $logData['correlation_id'] === $correlationId
                    && $logData['event_type'] === 'storage_inconsistency'
                    && $logData['inconsistency_details'] === $inconsistencyDetails
                    && $logData['severity'] === 'high'
                    && in_array("Update storage_disk field from 'public' to 'protected'", $logData['repair_recommendations'])
                    && $logData['diagnostic_info']['file_existence_check']['public_disk'] === false
                    && $logData['diagnostic_info']['file_existence_check']['protected_disk'] === true;
            }));
    }

    public function test_url_generation_failure_with_actionable_suggestions()
    {
        Auth::login($this->user);
        
        $content = Content::factory()->create([
            'file_path' => 'documents/report.pdf',
            'storage_disk' => 'protected',
            'original_filename' => 'report.pdf'
        ]);
        
        $failureReason = 'Route [secure-files.download-content] not defined';
        $context = [
            'attempted_method' => 'route',
            'route_name' => 'secure-files.download-content',
            'route_parameters' => ['content' => $content->id]
        ];
        
        $correlationId = $this->logger->logUrlGenerationFailure($content, $failureReason, $context);
        
        // Verify failure logging with suggestions
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('URL generation failed', \Mockery::on(function ($logData) use ($correlationId, $failureReason) {
                return $logData['correlation_id'] === $correlationId
                    && $logData['failure_details']['failure_reason'] === $failureReason
                    && $logData['failure_details']['failure_category'] === 'routing_error'
                    && $logData['failure_details']['is_retryable'] === false
                    && in_array('Verify secure file routes are properly configured', $logData['failure_details']['suggested_actions'])
                    && in_array('Check route caching and clear if necessary', $logData['failure_details']['suggested_actions']);
            }));
    }

    public function test_suspicious_activity_detection_and_logging()
    {
        Auth::login($this->user);
        
        $content = Content::factory()->create([
            'file_path' => 'admin/config.json',
            'storage_disk' => 'protected'
        ]);
        
        $denialReason = 'Brute force attempt detected - multiple failed access attempts';
        $context = [
            'access_method' => 'direct',
            'rapid_requests' => 25,
            'unusual_user_agent' => true,
            'ip_reputation' => 'suspicious',
            'failed_attempts_count' => 15
        ];
        
        $correlationId = $this->logger->logFileAccessDenial($content, $denialReason, $context);
        
        // Verify high-risk security logging
        Log::shouldHaveReceived('warning')
            ->once()
            ->with('File access denied', \Mockery::on(function ($logData) use ($correlationId, $denialReason) {
                return $logData['correlation_id'] === $correlationId
                    && $logData['denial_details']['denial_reason'] === $denialReason
                    && $logData['denial_details']['security_risk_level'] === 'high'
                    && $logData['security_audit']['suspicious_activity'] === true
                    && $logData['security_audit']['user_authorized'] === false
                    && isset($logData['security_audit']['ip_address'])
                    && isset($logData['security_audit']['user_agent']);
            }));
    }

    public function test_file_operation_logging_with_different_statuses()
    {
        Auth::login($this->user);
        
        $content = Content::factory()->create([
            'file_path' => 'uploads/test.txt',
            'storage_disk' => 'public'
        ]);
        
        // Test successful operation
        $successCorrelationId = $this->logger->logFileOperation(
            'file_upload',
            $content,
            'success',
            ['upload_size' => 1024, 'upload_time' => 0.5],
            ['source' => 'content_builder']
        );
        
        Log::shouldHaveReceived('info')
            ->with('File operation completed: file_upload', \Mockery::on(function ($logData) use ($successCorrelationId) {
                return $logData['correlation_id'] === $successCorrelationId
                    && $logData['status'] === 'success'
                    && $logData['operation'] === 'file_upload';
            }));
        
        // Test failed operation
        $failureCorrelationId = $this->logger->logFileOperation(
            'file_delete',
            $content,
            'failure',
            ['error' => 'Permission denied', 'error_code' => 403],
            ['source' => 'admin_panel']
        );
        
        Log::shouldHaveReceived('error')
            ->with('File operation failed: file_delete', \Mockery::on(function ($logData) use ($failureCorrelationId) {
                return $logData['correlation_id'] === $failureCorrelationId
                    && $logData['status'] === 'failure'
                    && $logData['operation'] === 'file_delete'
                    && $logData['operation_details']['error'] === 'Permission denied';
            }));
        
        // Test warning operation
        $warningCorrelationId = $this->logger->logFileOperation(
            'file_validation',
            $content,
            'warning',
            ['warning' => 'File size exceeds recommended limit'],
            ['source' => 'validator']
        );
        
        Log::shouldHaveReceived('warning')
            ->with('File operation warning: file_validation', \Mockery::on(function ($logData) use ($warningCorrelationId) {
                return $logData['correlation_id'] === $warningCorrelationId
                    && $logData['status'] === 'warning'
                    && $logData['operation'] === 'file_validation';
            }));
    }

    public function test_diagnostic_info_includes_file_metadata()
    {
        $content = Content::factory()->create([
            'file_path' => 'test/metadata.jpg',
            'storage_disk' => 'public',
            'original_filename' => 'metadata.jpg',
            'file_size' => 4096,
            'mime_type' => 'image/jpeg'
        ]);
        
        // Create file with specific size and timestamp
        Storage::disk('public')->put($content->file_path, str_repeat('x', 4096));
        
        $correlationId = $this->logger->logFileServingError($content, 'Test error');
        
        Log::shouldHaveReceived('error')
            ->once()
            ->with('File serving error occurred', \Mockery::on(function ($logData) use ($content) {
                $diagnostic = $logData['diagnostic_info'];
                
                return $diagnostic['file_existence_check']['public_disk'] === true
                    && $diagnostic['file_existence_check']['protected_disk'] === false
                    && isset($diagnostic['file_existence_check']['public_size'])
                    && isset($diagnostic['file_existence_check']['public_last_modified'])
                    && $diagnostic['recorded_storage_info']['storage_disk'] === 'public'
                    && $diagnostic['recorded_storage_info']['file_path'] === $content->file_path
                    && $diagnostic['url_generation_method'] === 'asset_url'
                    && isset($diagnostic['storage_configuration']);
            }));
    }

    public function test_correlation_id_tracking_across_operations()
    {
        Auth::login($this->user);
        
        $content = Content::factory()->create([
            'file_path' => 'tracking/test.pdf',
            'storage_disk' => 'protected'
        ]);
        
        // Log multiple related operations
        $errorCorrelationId = $this->logger->logFileServingError($content, 'Initial error');
        $urlFailureCorrelationId = $this->logger->logUrlGenerationFailure($content, 'URL generation failed');
        $operationCorrelationId = $this->logger->logFileOperation('repair_attempt', $content, 'success');
        
        // Verify each operation has unique correlation ID
        $this->assertNotEquals($errorCorrelationId, $urlFailureCorrelationId);
        $this->assertNotEquals($urlFailureCorrelationId, $operationCorrelationId);
        $this->assertNotEquals($errorCorrelationId, $operationCorrelationId);
        
        // Verify all correlation IDs are valid UUIDs
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $errorCorrelationId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $urlFailureCorrelationId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $operationCorrelationId);
    }

    public function test_console_environment_logging()
    {
        // Simulate console command execution
        $_SERVER['argv'] = ['artisan', 'files:repair', '--dry-run'];
        
        $content = Content::factory()->create();
        
        $correlationId = $this->logger->logFileOperation('console_repair', $content, 'success');
        
        Log::shouldHaveReceived('info')
            ->with('File operation completed: console_repair', \Mockery::on(function ($logData) {
                return isset($logData['request_info']['context'])
                    && $logData['request_info']['context'] === 'console'
                    && isset($logData['request_info']['command'])
                    && isset($logData['request_info']['arguments']);
            }));
    }
}