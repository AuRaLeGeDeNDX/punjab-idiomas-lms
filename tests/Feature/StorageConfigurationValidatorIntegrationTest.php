<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\StorageConfigurationValidator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Integration tests for StorageConfigurationValidator service.
 * 
 * Tests the complete functionality of storage configuration validation
 * in a real Laravel application context.
 * 
 * Requirements: 8.1, 8.2, 8.4
 */
class StorageConfigurationValidatorIntegrationTest extends TestCase
{
    private StorageConfigurationValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validator = new StorageConfigurationValidator();
        
        // Set up fake storage for testing
        Storage::fake('public');
        Storage::fake('protected');
        
        // Clear any existing logs
        Log::spy();
    }

    /**
     * Test that the StorageConfigurationValidator can be instantiated and perform basic validation.
     */
    public function test_storage_configuration_validator_basic_functionality(): void
    {
        // Arrange
        $correlationId = 'test-integration-' . time();
        
        // Act
        $result = $this->validator->performStartupValidation($correlationId);
        
        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($correlationId, $result->getCorrelationId());
        $this->assertTrue($result->isHealthy());
        
        // Verify file operations are allowed
        $allowed = $this->validator->areFileOperationsAllowed($correlationId);
        $this->assertTrue($allowed);
        
        // Verify validation status
        $status = $this->validator->getValidationStatus($correlationId);
        $this->assertIsArray($status);
        $this->assertTrue($status['file_operations_allowed']);
    }

    /**
     * Test configuration change validation.
     */
    public function test_configuration_change_validation(): void
    {
        // Arrange
        $correlationId = 'test-config-change-' . time();
        
        $oldConfig = [
            'disks' => [
                'public' => ['root' => '/old/public/path'],
                'protected' => ['root' => '/old/protected/path'],
            ],
        ];
        
        $newConfig = [
            'disks' => [
                'public' => ['root' => '/old/public/path'], // Same path
                'protected' => ['root' => '/old/protected/path'], // Same path
            ],
        ];
        
        // Act
        $result = $this->validator->validateConfigurationChange($oldConfig, $newConfig, $correlationId);
        
        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($correlationId, $result->getCorrelationId());
        $this->assertFalse($result->hasConfigurationErrors());
    }

    /**
     * Test validation cache management.
     */
    public function test_validation_cache_management(): void
    {
        // Arrange
        $correlationId = 'test-cache-' . time();
        
        // Perform initial validation
        $this->validator->performStartupValidation($correlationId);
        
        // Verify cache is populated
        $status = $this->validator->getValidationStatus($correlationId);
        $this->assertNotEmpty($status['disk_cache']);
        
        // Clear cache
        $this->validator->clearValidationCache($correlationId);
        
        // Verify cache is cleared
        $status = $this->validator->getValidationStatus($correlationId);
        $this->assertEmpty($status['disk_cache']);
    }

    /**
     * Test validation enable/disable functionality.
     */
    public function test_validation_enable_disable(): void
    {
        // Arrange
        $correlationId = 'test-enable-disable-' . time();
        
        // Disable validation
        $this->validator->setValidationEnabled(false, 'Testing', $correlationId);
        
        // File operations should be allowed even without validation
        $allowed = $this->validator->areFileOperationsAllowed($correlationId);
        $this->assertTrue($allowed);
        
        // Re-enable validation
        $this->validator->setValidationEnabled(true, 'Testing complete', $correlationId);
        
        // File operations should now be blocked (no recent validation)
        $allowed = $this->validator->areFileOperationsAllowed($correlationId);
        $this->assertFalse($allowed);
    }
}