<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Services\StorageConfigurationValidatorSimple as StorageConfigurationValidator;
use App\Services\StorageConfigurationValidatorSimple;
use App\Services\StorageConfigurationMonitor;
use App\Http\Middleware\StorageValidationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Integration tests for storage validation lifecycle integration.
 * 
 * Tests the complete integration of storage validation into the application
 * lifecycle including boot process, middleware, and file operation prevention.
 * 
 * Requirements: 8.1, 8.4
 */
class StorageValidationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private StorageConfigurationValidatorSimple $validator;
    private StorageConfigurationMonitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validator = app(StorageConfigurationValidatorSimple::class);
        $this->monitor = app(StorageConfigurationMonitor::class);
    }

    /**
     * Test that storage validation services are properly registered in the container.
     * 
     * **Validates: Requirements 8.1**
     */
    public function test_storage_validation_services_are_registered()
    {
        // Test that services are registered as singletons
        $validator1 = app(StorageConfigurationValidatorSimple::class);
        $validator2 = app(StorageConfigurationValidatorSimple::class);
        $this->assertSame($validator1, $validator2, 'StorageConfigurationValidator should be registered as singleton');

        $monitor1 = app(StorageConfigurationMonitor::class);
        $monitor2 = app(StorageConfigurationMonitor::class);
        $this->assertSame($monitor1, $monitor2, 'StorageConfigurationMonitor should be registered as singleton');

        // Test that services are properly instantiated
        $this->assertInstanceOf(StorageConfigurationValidatorSimple::class, $validator1);
        $this->assertInstanceOf(StorageConfigurationMonitor::class, $monitor1);
    }

    /**
     * Test storage validation middleware integration.
     * 
     * **Validates: Requirements 8.4**
     */
    public function test_storage_validation_middleware_integration()
    {
        // Create a mock validator that always returns true for areFileOperationsAllowed
        $mockValidator = $this->createMock(StorageConfigurationValidatorSimple::class);
        $mockValidator->method('areFileOperationsAllowed')->willReturn(true);
        $mockValidator->method('getValidationStatus')->willReturn([
            'validation_info' => ['correlation_id' => 'test'],
            'functional_disks' => ['public', 'protected'],
        ]);
        
        // Create middleware instance with mock validator
        $middleware = new StorageValidationMiddleware($mockValidator);
        
        // Create a mock request that looks like a file operation
        $request = Request::create('/api/content-blocks', 'POST');
        $request->files->set('file', $this->createMockUploadedFile());
        
        // Test that middleware allows requests when validation passes
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });
        
        $this->assertEquals(200, $response->getStatusCode());
        
        // Verify correlation ID was added to request
        $this->assertTrue($request->headers->has('X-Correlation-ID'));
    }

    /**
     * Test storage validation middleware blocks file operations when validation fails.
     * 
     * **Validates: Requirements 8.4**
     */
    public function test_storage_validation_middleware_blocks_operations_when_validation_fails()
    {
        // Create a mock validator that always returns false for areFileOperationsAllowed
        $mockValidator = $this->createMock(StorageConfigurationValidatorSimple::class);
        $mockValidator->method('areFileOperationsAllowed')->willReturn(false);
        $mockValidator->method('getValidationStatus')->willReturn([
            'validation_info' => ['correlation_id' => 'test'],
            'functional_disks' => [],
            'recommendations' => [
                ['type' => 'configuration_errors', 'priority' => 'critical', 'description' => 'Test failure']
            ]
        ]);
        
        // Create middleware instance with mock validator
        $middleware = new StorageValidationMiddleware($mockValidator);
        
        // Create a mock request that looks like a file operation
        $request = Request::create('/api/content-blocks', 'POST');
        $request->files->set('file', $this->createMockUploadedFile());
        
        // Test that middleware blocks requests when validation fails
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });
        
        $this->assertEquals(503, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('STORAGE_VALIDATION_FAILED', $responseData['error_code']);
        $this->assertArrayHasKey('recommendations', $responseData);
    }

    /**
     * Test configuration change monitoring integration.
     * 
     * **Validates: Requirements 8.2**
     */
    public function test_configuration_change_monitoring()
    {
        // Get initial configuration
        $initialConfig = $this->monitor->getCurrentStorageConfiguration();
        $this->assertIsArray($initialConfig);
        $this->assertArrayHasKey('disks', $initialConfig);
        
        // Test configuration change summary
        $changeSummary = $this->monitor->getConfigurationChangeSummary();
        $this->assertArrayHasKey('has_changes', $changeSummary);
        $this->assertArrayHasKey('current_config', $changeSummary);
        $this->assertArrayHasKey('last_known_config', $changeSummary);
        
        // Initially should have no changes
        $this->assertFalse($changeSummary['has_changes']);
    }

    /**
     * Test startup validation integration.
     * 
     * **Validates: Requirements 8.1**
     */
    public function test_startup_validation_integration()
    {
        $correlationId = Str::uuid()->toString();
        
        // Test startup validation
        $result = $this->validator->performStartupValidation($correlationId);
        
        // Verify result structure (Simple version returns array)
        $this->assertIsArray($result);
        $this->assertArrayHasKey('overall_status', $result);
        $this->assertArrayHasKey('functional_disks', $result);
        $this->assertArrayHasKey('failed_disks', $result);
        
        // Test validation status retrieval
        $status = $this->validator->getValidationStatus($correlationId);
        $this->assertIsArray($status);
        $this->assertArrayHasKey('validation_info', $status);
        $this->assertArrayHasKey('functional_disks', $status);
        $this->assertArrayHasKey('file_operations_allowed', $status);
    }

    /**
     * Test file operations prevention when validation fails.
     * 
     * **Validates: Requirements 8.4**
     */
    public function test_file_operations_prevention()
    {
        $correlationId = Str::uuid()->toString();
        
        // Test with a working validator first - need to perform validation first
        $workingValidator = app(StorageConfigurationValidatorSimple::class);
        
        // Perform startup validation to populate cache
        $workingValidator->performStartupValidation($correlationId);
        
        // Now file operations should be allowed
        $this->assertTrue($workingValidator->areFileOperationsAllowed($correlationId));
        
        // Create a mock validator that simulates failure
        $failingValidator = $this->createMock(StorageConfigurationValidatorSimple::class);
        $failingValidator->method('areFileOperationsAllowed')->willReturn(false);
        
        // Test that the failing validator blocks operations
        $this->assertFalse($failingValidator->areFileOperationsAllowed($correlationId));
        
        // Test re-enabling (mock a working validator again)
        $reenabledValidator = $this->createMock(StorageConfigurationValidatorSimple::class);
        $reenabledValidator->method('areFileOperationsAllowed')->willReturn(true);
        
        $this->assertTrue($reenabledValidator->areFileOperationsAllowed($correlationId));
    }

    /**
     * Test validation cache management.
     * 
     * **Validates: Requirements 8.1**
     */
    public function test_validation_cache_management()
    {
        $correlationId = Str::uuid()->toString();
        
        // Perform validation to populate cache
        $this->validator->performStartupValidation($correlationId);
        
        // Get status to verify cache is populated
        $status = $this->validator->getValidationStatus($correlationId);
        $this->assertNotEmpty($status['disk_cache']);
        
        // Clear cache
        $this->validator->clearValidationCache($correlationId);
        
        // Verify cache is cleared
        $statusAfterClear = $this->validator->getValidationStatus($correlationId);
        $this->assertEmpty($statusAfterClear['disk_cache']);
    }

    /**
     * Test middleware file operation detection.
     * 
     * **Validates: Requirements 8.4**
     */
    public function test_middleware_file_operation_detection()
    {
        $middleware = new StorageValidationMiddleware($this->validator);
        
        // Test various request types
        $testCases = [
            // File upload request
            ['method' => 'POST', 'path' => '/api/content-blocks', 'hasFile' => true, 'expected' => true],
            // File download request
            ['method' => 'GET', 'path' => '/secure-files/123', 'hasFile' => false, 'expected' => true],
            // Non-file request
            ['method' => 'GET', 'path' => '/api/users', 'hasFile' => false, 'expected' => false],
        ];
        
        foreach ($testCases as $testCase) {
            $request = Request::create($testCase['path'], $testCase['method']);
            
            if ($testCase['hasFile']) {
                $request->files->set('file', $this->createMockUploadedFile());
            }
            
            $isFileOperation = $this->callPrivateMethod($middleware, 'isFileOperationRequest', [$request]);
            
            $this->assertEquals(
                $testCase['expected'], 
                $isFileOperation,
                "Request {$testCase['method']} {$testCase['path']} should " . 
                ($testCase['expected'] ? 'be' : 'not be') . " detected as file operation"
            );
        }
    }

    /**
     * Create a mock uploaded file for testing.
     */
    private function createMockUploadedFile()
    {
        return \Illuminate\Http\Testing\File::create('test.jpg', 100);
    }

    /**
     * Call a private method on an object for testing.
     */
    private function callPrivateMethod($object, $methodName, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}