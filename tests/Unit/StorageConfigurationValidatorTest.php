<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\StorageConfigurationValidator;
use App\Services\ConfigurationStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

/**
 * Unit tests for StorageConfigurationValidator service.
 * 
 * Tests startup validation, configuration change validation, and error handling
 * for storage configuration validation functionality.
 * 
 * Requirements: 8.1, 8.2, 8.4
 */
class StorageConfigurationValidatorTest extends TestCase
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
     * Test startup validation with healthy storage configuration.
     * 
     * @test
     */
    public function test_startup_validation_with_healthy_configuration(): void
    {
        // Arrange
        $correlationId = 'test-startup-' . time();
        
        // Act
        $result = $this->validator->performStartupValidation($correlationId);
        
        // Assert
        $this->assertInstanceOf(ConfigurationStatus::class, $result);
        $this->assertEquals($correlationId, $result->getCorrelationId());
        $this->assertTrue($result->isHealthy());
        $this->assertCount(2, $result->getDiskStatuses());
        $this->assertContains('public', $result->getAccessibleDisks());
        $this->assertContains('protected', $result->getAccessibleDisks());
        $this->assertEmpty($result->getFailedDisks());
        
        // Verify logging
        Log::shouldHaveReceived('info')
            ->with('StorageConfigurationValidator: Starting startup validation', \Mockery::type('array'))
            ->once();
        
        Log::shouldHaveReceived('info')
            ->with('StorageConfigurationValidator: Startup validation completed', \Mockery::type('array'))
            ->once();
    }

    /**
     * Test startup validation with inaccessible storage disk.
     * 
     * @test
     */
    public function test_startup_validation_with_inaccessible_disk(): void
    {
        // Arrange
        $correlationId = 'test-inaccessible-' . time();
        
        // Mock Storage to throw exception for protected disk
        Storage::shouldReceive('disk')
            ->with('protected')
            ->andThrow(new \Exception('Disk not accessible'));
        
        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Storage configuration startup validation failed');
        
        $this->validator->performStartupValidation($correlationId);
    }

    /**
     * Test configuration change validation with compatible changes.
     * 
     * @test
     */
    public function test_configuration_change_validation_compatible(): void
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
        $this->assertInstanceOf(ConfigurationStatus::class, $result);
        $this->assertEquals($correlationId, $result->getCorrelationId());
        $this->assertFalse($result->hasConfigurationErrors());
        
        // Verify logging
        Log::shouldHaveReceived('info')
            ->with('StorageConfigurationValidator: Starting configuration change validation', \Mockery::type('array'))
            ->once();
    }

    /**
     * Test configuration change validation with path changes.
     * 
     * @test
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
        $this->assertInstanceOf(ConfigurationStatus::class, $result);
        $this->assertTrue($result->hasConfigurationErrors());
        
        $errors = $result->getConfigurationErrors();
        $this->assertArrayHasKey('disk_path_changed_public', $errors);
        $this->assertEquals('warning', $errors['disk_path_changed_public']['severity']);
        
        // Verify warning logging
        Log::shouldHaveReceived('warning')
            ->with('StorageConfigurationValidator: Disk path changed', \Mockery::type('array'))
            ->once();
    }

    /**
     * Test file operations allowed with healthy configuration.
     * 
     * @test
     */
    public function test_file_operations_allowed_with_healthy_configuration(): void
    {
        // Arrange
        $correlationId = 'test-operations-allowed-' . time();
        
        // Perform startup validation to populate cache
        $this->validator->performStartupValidation($correlationId);
        
        // Act
        $allowed = $this->validator->areFileOperationsAllowed($correlationId);
        
        // Assert
        $this->assertTrue($allowed);
        
        // Verify debug logging
        Log::shouldHaveReceived('debug')
            ->with('StorageConfigurationValidator: File operations allowed', \Mockery::type('array'))
            ->once();
    }

    /**
     * Test file operations blocked with no recent validation.
     * 
     * @test
     */
    public function test_file_operations_blocked_no_recent_validation(): void
    {
        // Arrange
        $correlationId = 'test-operations-blocked-' . time();
        
        // Don't perform startup validation, so no recent validation exists
        
        // Act
        $allowed = $this->validator->areFileOperationsAllowed($correlationId);
        
        // Assert
        $this->assertFalse($allowed);
        
        // Verify warning logging
        Log::shouldHaveReceived('warning')
            ->with('StorageConfigurationValidator: File operations blocked - no recent validation', \Mockery::type('array'))
            ->once();
    }

    /**
     * Test file operations blocked with critical errors.
     * 
     * @test
     */
    public function test_file_operations_blocked_with_critical_errors(): void
    {
        // Arrange
        $correlationId = 'test-critical-errors-' . time();
        
        // Force critical error by mocking disk failure
        Storage::shouldReceive('disk')
            ->with('public')
            ->andThrow(new \Exception('Critical disk failure'));
        
        Storage::shouldReceive('disk')
            ->with('protected')
            ->andThrow(new \Exception('Critical disk failure'));
        
        // Act & Assert - startup validation should throw exception
        $this->expectException(\RuntimeException::class);
        $this->validator->performStartupValidation($correlationId);
    }

    /**
     * Test validation status retrieval.
     * 
     * @test
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
     * Test enabling and disabling validation.
     * 
     * @test
     */
    public function test_set_validation_enabled(): void
    {
        // Arrange
        $correlationId = 'test-enable-disable-' . time();
        $reason = 'Emergency maintenance';
        
        // Act - Disable validation
        $this->validator->setValidationEnabled(false, $reason, $correlationId);
        
        // Assert - File operations should be allowed even without validation
        $allowed = $this->validator->areFileOperationsAllowed($correlationId);
        $this->assertTrue($allowed);
        
        // Verify warning logging
        Log::shouldHaveReceived('warning')
            ->with('StorageConfigurationValidator: Validation status changed', \Mockery::type('array'))
            ->once();
        
        Log::shouldHaveReceived('warning')
            ->with('StorageConfigurationValidator: File operations allowed - validation disabled', \Mockery::type('array'))
            ->once();
        
        // Act - Re-enable validation
        $this->validator->setValidationEnabled(true, 'Maintenance complete', $correlationId);
        
        // Assert - File operations should now be blocked (no recent validation)
        $allowed = $this->validator->areFileOperationsAllowed($correlationId);
        $this->assertFalse($allowed);
    }

    /**
     * Test clearing validation cache.
     * 
     * @test
     */
    public function test_clear_validation_cache(): void
    {
        // Arrange
        $correlationId = 'test-clear-cache-' . time();
        
        // Perform startup validation to populate cache
        $this->validator->performStartupValidation($correlationId);
        
        // Verify cache is populated
        $status = $this->validator->getValidationStatus($correlationId);
        $this->assertNotEmpty($status['disk_cache']);
        
        // Act
        $this->validator->clearValidationCache($correlationId);
        
        // Assert
        $status = $this->validator->getValidationStatus($correlationId);
        $this->assertEmpty($status['disk_cache']);
        $this->assertEmpty($status['configuration_errors']);
        $this->assertFalse($status['file_operations_allowed']);
        
        // Verify info logging
        Log::shouldHaveReceived('info')
            ->with('StorageConfigurationValidator: Clearing validation cache', \Mockery::type('array'))
            ->once();
    }

    /**
     * Test disk accessibility validation with various scenarios.
     * 
     * @test
     */
    public function test_disk_accessibility_validation_scenarios(): void
    {
        // Test with healthy disk (using fake storage)
        $correlationId = 'test-disk-validation-' . time();
        
        $result = $this->validator->performStartupValidation($correlationId);
        
        $diskStatuses = $result->getDiskStatuses();
        
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
     * Test configuration consistency validation.
     * 
     * @test
     */
    public function test_configuration_consistency_validation(): void
    {
        // Arrange
        $correlationId = 'test-consistency-' . time();
        
        // Act
        $result = $this->validator->performStartupValidation($correlationId);
        
        // Assert - Should be healthy with fake storage
        $this->assertTrue($result->isHealthy());
        $this->assertFalse($result->hasConfigurationErrors());
        $this->assertCount(2, $result->getFunctionalDisks());
    }

    /**
     * Test validation recommendations.
     * 
     * @test
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

    /**
     * Test error handling during startup validation.
     * 
     * @test
     */
    public function test_startup_validation_error_handling(): void
    {
        // Arrange
        $correlationId = 'test-error-handling-' . time();
        
        // Mock Config to return null for disk configuration
        Config::shouldReceive('get')
            ->with('filesystems.disks.public')
            ->andReturn(null);
        
        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Storage configuration startup validation failed');
        
        $this->validator->performStartupValidation($correlationId);
        
        // Verify error logging
        Log::shouldHaveReceived('error')
            ->with('StorageConfigurationValidator: Startup validation failed', \Mockery::type('array'))
            ->once();
    }

    /**
     * Test configuration change validation error handling.
     * 
     * @test
     */
    public function test_configuration_change_validation_error_handling(): void
    {
        // Arrange
        $correlationId = 'test-config-error-' . time();
        
        $oldConfig = ['disks' => ['public' => ['root' => '/old/path']]];
        $newConfig = ['disks' => []]; // Missing required disks
        
        // Act
        $result = $this->validator->validateConfigurationChange($oldConfig, $newConfig, $correlationId);
        
        // Assert
        $this->assertTrue($result->hasConfigurationErrors());
        $this->assertTrue($result->isCritical());
        
        $errors = $result->getConfigurationErrors();
        $this->assertArrayHasKey('missing_disk_config_public', $errors);
        $this->assertArrayHasKey('missing_disk_config_protected', $errors);
    }

    /**
     * Test health score calculation.
     * 
     * @test
     */
    public function test_health_score_calculation(): void
    {
        // Arrange
        $correlationId = 'test-health-score-' . time();
        
        // Act
        $result = $this->validator->performStartupValidation($correlationId);
        $healthScore = $result->getHealthScore();
        
        // Assert
        $this->assertIsArray($healthScore);
        $this->assertArrayHasKey('score', $healthScore);
        $this->assertArrayHasKey('grade', $healthScore);
        $this->assertArrayHasKey('description', $healthScore);
        $this->assertArrayHasKey('breakdown', $healthScore);
        
        // With fake storage, should have excellent score
        $this->assertGreaterThanOrEqual(90, $healthScore['score']);
        $this->assertEquals('A', $healthScore['grade']);
    }

    /**
     * Test disk space summary.
     * 
     * @test
     */
    public function test_disk_space_summary(): void
    {
        // Arrange
        $correlationId = 'test-disk-space-' . time();
        
        // Act
        $result = $this->validator->performStartupValidation($correlationId);
        $diskSpaceSummary = $result->getDiskSpaceSummary();
        
        // Assert
        $this->assertIsArray($diskSpaceSummary);
        $this->assertArrayHasKey('total_disks', $diskSpaceSummary);
        $this->assertArrayHasKey('accessible_disks', $diskSpaceSummary);
        $this->assertArrayHasKey('total_space', $diskSpaceSummary);
        $this->assertArrayHasKey('free_space', $diskSpaceSummary);
        $this->assertArrayHasKey('used_space', $diskSpaceSummary);
        $this->assertArrayHasKey('usage_percentage', $diskSpaceSummary);
        $this->assertArrayHasKey('low_space_warnings', $diskSpaceSummary);
        $this->assertArrayHasKey('critical_space_warnings', $diskSpaceSummary);
        
        $this->assertEquals(2, $diskSpaceSummary['total_disks']);
        $this->assertEquals(2, $diskSpaceSummary['accessible_disks']);
    }

    /**
     * Test array and JSON conversion.
     * 
     * @test
     */
    public function test_array_and_json_conversion(): void
    {
        // Arrange
        $correlationId = 'test-conversion-' . time();
        
        // Act
        $result = $this->validator->performStartupValidation($correlationId);
        $array = $result->toArray();
        $json = $result->toJson();
        
        // Assert
        $this->assertIsArray($array);
        $this->assertIsString($json);
        $this->assertJson($json);
        
        $decodedJson = json_decode($json, true);
        $this->assertEquals($array, $decodedJson);
        
        // Verify required array keys
        $this->assertArrayHasKey('validation_info', $array);
        $this->assertArrayHasKey('disk_statuses', $array);
        $this->assertArrayHasKey('configuration_errors', $array);
        $this->assertArrayHasKey('summary', $array);
        $this->assertArrayHasKey('disk_space_summary', $array);
        $this->assertArrayHasKey('health_score', $array);
        $this->assertArrayHasKey('recommendations', $array);
    }
}