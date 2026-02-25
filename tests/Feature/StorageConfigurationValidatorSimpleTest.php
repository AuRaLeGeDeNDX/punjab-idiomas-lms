<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\StorageConfigurationValidatorSimple;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Integration tests for StorageConfigurationValidatorSimple service.
 * 
 * Tests the complete functionality of storage configuration validation
 * using a simplified implementation that doesn't depend on external classes.
 * 
 * Requirements: 8.1, 8.2, 8.4
 */
class StorageConfigurationValidatorSimpleTest extends TestCase
{
    private StorageConfigurationValidatorSimple $validator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->validator = new StorageConfigurationValidatorSimple();
        
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
        $this->assertIsArray($result);
        $this->assertEquals($correlationId, $result['correlation_id']);
        $this->assertEquals('healthy', $result['overall_status']);
        $this->assertCount(2, $result['disk_statuses']);
        $this->assertContains('public', $result['functional_disks']);
        $this->assertContains('protected', $result['functional_disks']);
        $this->assertEmpty($result['failed_disks']);
        
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
        $this->assertIsArray($result);
        $this->assertEquals($correlationId, $result['correlation_id']);
        $this->assertEmpty($result['configuration_errors']);
    }

    /**
     * Test configuration change validation with path changes.
     */
    public function test_configuration_change_validation_with_path_changes(): void
    {
        // Arrange
        $correlationId = 'test-path-change-' . time();
        
        $oldConfig = [
            'disks' => [
                'public' => ['root' => '/old/public/path'],
                'protected' => ['root' => '/old/protected/path'],
            ],
        ];
        
        $newConfig = [
            'disks' => [
                'public' => ['root' => '/new/public/path'], // Changed path
                'protected' => ['root' => '/old/protected/path'], // Same path
            ],
        ];
        
        // Act
        $result = $this->validator->validateConfigurationChange($oldConfig, $newConfig, $correlationId);
        
        // Assert
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['configuration_errors']);
        $this->assertArrayHasKey('disk_path_changed_public', $result['configuration_errors']);
        $this->assertEquals('warning', $result['configuration_errors']['disk_path_changed_public']['severity']);
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

    /**
     * Test disk accessibility validation scenarios.
     */
    public function test_disk_accessibility_validation_scenarios(): void
    {
        // Test with healthy disk (using fake storage)
        $correlationId = 'test-disk-validation-' . time();
        
        $result = $this->validator->performStartupValidation($correlationId);
        
        $diskStatuses = $result['disk_statuses'];
        
        // Assert public disk status
        $this->assertArrayHasKey('public', $diskStatuses);
        $publicStatus = $diskStatuses['public'];
        $this->assertTrue($publicStatus['accessible']);
        $this->assertTrue($publicStatus['writable']);
        $this->assertTrue($publicStatus['readable']);
        $this->assertEmpty($publicStatus['errors']);
        
        // Assert protected disk status
        $this->assertArrayHasKey('protected', $diskStatuses);
        $protectedStatus = $diskStatuses['protected'];
        $this->assertTrue($protectedStatus['accessible']);
        $this->assertTrue($protectedStatus['writable']);
        $this->assertTrue($protectedStatus['readable']);
        $this->assertEmpty($protectedStatus['errors']);
    }

    /**
     * Test validation status retrieval.
     */
    public function test_get_validation_status(): void
    {
        // Arrange
        $correlationId = 'test-status-' . time();
        
        // Perform startup validation
        $this->validator->performStartupValidation($correlationId);
        
        // Act
        $status = $this->validator->getValidationStatus($correlationId);
        
        // Assert
        $this->assertIsArray($status);
        $this->assertArrayHasKey('validation_info', $status);
        $this->assertArrayHasKey('disk_cache', $status);
        $this->assertArrayHasKey('configuration_errors', $status);
        $this->assertArrayHasKey('file_operations_allowed', $status);
        $this->assertArrayHasKey('functional_disks', $status);
        $this->assertArrayHasKey('required_disks', $status);
        $this->assertArrayHasKey('recommendations', $status);
        
        $this->assertEquals($correlationId, $status['validation_info']['correlation_id']);
        $this->assertTrue($status['file_operations_allowed']);
        $this->assertContains('public', $status['functional_disks']);
        $this->assertContains('protected', $status['functional_disks']);
    }

    /**
     * Test validation recommendations.
     */
    public function test_validation_recommendations(): void
    {
        // Arrange
        $correlationId = 'test-recommendations-' . time();
        
        // Test with fresh validator (no validation performed)
        $status = $this->validator->getValidationStatus($correlationId);
        $recommendations = $status['recommendations'];
        
        // Assert
        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);
        
        // Should have recommendations for no validation cache
        $cacheRecommendation = collect($recommendations)->firstWhere('type', 'no_validation_cache');
        $this->assertNotNull($cacheRecommendation);
        $this->assertEquals('warning', $cacheRecommendation['priority']);
    }
}