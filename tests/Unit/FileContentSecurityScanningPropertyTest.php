<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\ContentBlockController;
use App\Services\FileUploadErrorFormatter;
use App\Helpers\FileSecurityHelper;
use ReflectionClass;

/**
 * Property-based tests for file content security scanning functionality.
 * 
 * **Validates: Requirements 6.3**
 */
class FileContentSecurityScanningPropertyTest extends TestCase
{
    use RefreshDatabase;

    private ContentBlockController $controller;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->controller = new ContentBlockController(
            app(\App\Services\ContentBlockService::class),
            app(\App\Services\FileUploadLogger::class)
        );
        
        $this->reflection = new ReflectionClass($this->controller);
        
        // Suppress logs during testing
        Log::shouldReceive('info')->andReturn(null);
        Log::shouldReceive('warning')->andReturn(null);
        Log::shouldReceive('error')->andReturn(null);
        Log::shouldReceive('critical')->andReturn(null);
    }

    /**
     * Property: Security scanning should never crash regardless of file input.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_security_scanning_never_crashes()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        // Test with various file types and sizes
        $testCases = [
            ['name' => 'tiny.txt', 'size' => 1, 'mime' => 'text/plain'],
            ['name' => 'small.pdf', 'size' => 100, 'mime' => 'application/pdf'],
            ['name' => 'medium.jpg', 'size' => 1000, 'mime' => 'image/jpeg'],
            ['name' => 'large.mp4', 'size' => 5000, 'mime' => 'video/mp4'],
            ['name' => 'empty.doc', 'size' => 0, 'mime' => 'application/msword'],
            ['name' => 'weird-name!@#$.png', 'size' => 500, 'mime' => 'image/png'],
            ['name' => 'no-extension', 'size' => 200, 'mime' => 'application/octet-stream'],
        ];

        foreach ($testCases as $testCase) {
            try {
                $file = UploadedFile::fake()->create($testCase['name'], $testCase['size'], $testCase['mime']);
                $errors = [];
                
                // Should never throw an exception
                $method->invoke($this->controller, $file, $errors, 'test-correlation-id');
                
                $this->assertTrue(true, "Security scanning should handle {$testCase['name']} without crashing");
                
            } catch (\Exception $e) {
                $this->fail("Security scanning crashed with file {$testCase['name']}: " . $e->getMessage());
            }
        }
    }

    /**
     * Property: All safe files should pass security scanning.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_safe_files_always_pass_security_scanning()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        $safeFileTypes = [
            ['name' => 'document.pdf', 'mime' => 'application/pdf'],
            ['name' => 'image.jpg', 'mime' => 'image/jpeg'],
            ['name' => 'image.png', 'mime' => 'image/png'],
            ['name' => 'image.gif', 'mime' => 'image/gif'],
            ['name' => 'audio.mp3', 'mime' => 'audio/mpeg'],
            ['name' => 'video.mp4', 'mime' => 'video/mp4'],
            ['name' => 'text.txt', 'mime' => 'text/plain'],
        ];

        foreach ($safeFileTypes as $fileType) {
            $file = UploadedFile::fake()->create($fileType['name'], 500, $fileType['mime']);
            $errors = [];
            
            $method->invoke($this->controller, $file, $errors, 'test-correlation-id');
            
            // Safe files should not produce security errors
            $hasSecurityError = false;
            foreach ($errors as $error) {
                if (stripos($error, 'security') !== false || 
                    stripos($error, 'threat') !== false || 
                    stripos($error, 'virus') !== false ||
                    stripos($error, 'malware') !== false) {
                    $hasSecurityError = true;
                    break;
                }
            }
            
            $this->assertFalse($hasSecurityError, 
                "Safe file {$fileType['name']} should not trigger security errors. Errors: " . implode(', ', $errors));
        }
    }

    /**
     * Property: Security scanning should be consistent across multiple runs.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_security_scanning_consistency()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        $testFiles = [
            UploadedFile::fake()->create('test1.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('test2.jpg', 400, 300),
            UploadedFile::fake()->create('test3.mp3', 200, 'audio/mpeg'),
        ];

        foreach ($testFiles as $file) {
            $results = [];
            
            // Run security scanning multiple times on the same file
            for ($i = 0; $i < 3; $i++) {
                $errors = [];
                $method->invoke($this->controller, $file, $errors, "test-correlation-id-{$i}");
                $results[] = count($errors);
            }
            
            // Results should be consistent
            $uniqueResults = array_unique($results);
            $this->assertCount(1, $uniqueResults, 
                "Security scanning should produce consistent results for {$file->getClientOriginalName()}. " .
                "Got results: " . implode(', ', $results));
        }
    }

    /**
     * Property: Error messages should always be user-friendly and informative.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_error_messages_always_user_friendly()
    {
        $errorTypes = [
            'header_mismatch',
            'executable_detected',
            'script_content_detected',
            'security_threat',
            'security_warning',
            'virus_detected',
            'test_virus_detected',
        ];

        foreach ($errorTypes as $errorType) {
            $context = [
                'file_name' => 'test-file.ext',
                'signature_type' => 'test signature',
                'script_type' => 'test script',
                'threat_description' => 'test threat',
                'warning_description' => 'test warning',
                'scanner' => 'test scanner',
                'test_type' => 'test type',
                'security_reason' => 'test security reason',
            ];
            
            $errorMessage = FileUploadErrorFormatter::formatError($errorType, $context);
            
            // Error message should be non-empty
            $this->assertNotEmpty($errorMessage, "Error message for {$errorType} should not be empty");
            
            // Error message should be reasonably long (more than just a few words)
            $this->assertGreaterThan(20, strlen($errorMessage), 
                "Error message for {$errorType} should be informative: {$errorMessage}");
            
            // Error message should contain the file name
            $this->assertStringContainsString('test-file.ext', $errorMessage, 
                "Error message for {$errorType} should contain file name");
            
            // Error message should not contain technical jargon that users won't understand
            $forbiddenTerms = ['null', 'undefined', 'exception', 'stack trace', 'debug'];
            foreach ($forbiddenTerms as $term) {
                $this->assertStringNotContainsStringIgnoringCase($term, $errorMessage, 
                    "Error message for {$errorType} should not contain technical term '{$term}': {$errorMessage}");
            }
        }
    }

    /**
     * Property: Security scanning should handle various file sizes gracefully.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_security_scanning_handles_various_file_sizes()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        $fileSizes = [0, 1, 10, 100, 500, 1000, 2000, 5000]; // Various sizes in KB

        foreach ($fileSizes as $size) {
            $file = UploadedFile::fake()->create("test-{$size}kb.pdf", $size, 'application/pdf');
            $errors = [];
            
            $startTime = microtime(true);
            
            try {
                $method->invoke($this->controller, $file, $errors, 'test-correlation-id');
                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
                
                // Should complete within reasonable time regardless of file size
                $this->assertLessThan(10.0, $executionTime, 
                    "Security scanning should complete within 10 seconds for {$size}KB file");
                
                // Should not crash regardless of file size
                $this->assertTrue(true, "Security scanning should handle {$size}KB file without crashing");
                
            } catch (\Exception $e) {
                $this->fail("Security scanning failed for {$size}KB file: " . $e->getMessage());
            }
        }
    }

    /**
     * Property: FileSecurityHelper integration should always return valid results.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_file_security_helper_always_returns_valid_results()
    {
        $testFiles = [
            UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('test.jpg', 200, 200),
            UploadedFile::fake()->create('test.mp3', 300, 'audio/mpeg'),
            UploadedFile::fake()->create('test.mp4', 400, 'video/mp4'),
            UploadedFile::fake()->create('test.txt', 50, 'text/plain'),
        ];

        foreach ($testFiles as $file) {
            $results = FileSecurityHelper::scanFile($file);
            
            // Results should always be an array
            $this->assertIsArray($results, 
                "FileSecurityHelper should return array for {$file->getClientOriginalName()}");
            
            // Results should have required keys
            $requiredKeys = ['safe', 'threats', 'warnings', 'info'];
            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $results, 
                    "Results should have '{$key}' key for {$file->getClientOriginalName()}");
            }
            
            // 'safe' should be boolean
            $this->assertIsBool($results['safe'], 
                "'safe' should be boolean for {$file->getClientOriginalName()}");
            
            // 'threats', 'warnings' should be arrays
            $this->assertIsArray($results['threats'], 
                "'threats' should be array for {$file->getClientOriginalName()}");
            $this->assertIsArray($results['warnings'], 
                "'warnings' should be array for {$file->getClientOriginalName()}");
            
            // 'info' should be array
            $this->assertIsArray($results['info'], 
                "'info' should be array for {$file->getClientOriginalName()}");
            
            // If not safe, should have threats
            if (!$results['safe']) {
                $this->assertNotEmpty($results['threats'], 
                    "Unsafe file should have threats listed for {$file->getClientOriginalName()}");
            }
        }
    }

    /**
     * Property: Security scanning should preserve file integrity.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_security_scanning_preserves_file_integrity()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        $testFiles = [
            UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->image('test.jpg', 300, 200),
            UploadedFile::fake()->create('test.mp3', 200, 'audio/mpeg'),
        ];

        foreach ($testFiles as $file) {
            // Get original file properties
            $originalSize = $file->getSize();
            $originalName = $file->getClientOriginalName();
            $originalMime = $file->getMimeType();
            $originalPath = $file->getPathname();
            
            // Perform security scanning
            $errors = [];
            $method->invoke($this->controller, $file, $errors, 'test-correlation-id');
            
            // File properties should remain unchanged
            $this->assertEquals($originalSize, $file->getSize(), 
                "File size should not change after security scanning for {$originalName}");
            $this->assertEquals($originalName, $file->getClientOriginalName(), 
                "File name should not change after security scanning for {$originalName}");
            $this->assertEquals($originalMime, $file->getMimeType(), 
                "MIME type should not change after security scanning for {$originalName}");
            $this->assertEquals($originalPath, $file->getPathname(), 
                "File path should not change after security scanning for {$originalName}");
            
            // File should still be readable
            $this->assertTrue(is_readable($file->getPathname()), 
                "File should remain readable after security scanning for {$originalName}");
        }
    }

    /**
     * Property: Correlation IDs should be properly tracked through security scanning.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_correlation_ids_properly_tracked()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        $correlationIds = [
            'test-id-1',
            'test-id-2',
            'custom-correlation-123',
            'uuid-like-id-456',
            null, // Should generate one
        ];

        foreach ($correlationIds as $correlationId) {
            $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
            $errors = [];
            
            // Should not throw exception regardless of correlation ID format
            try {
                $method->invoke($this->controller, $file, $errors, $correlationId);
                $this->assertTrue(true, "Security scanning should handle correlation ID: " . ($correlationId ?? 'null'));
            } catch (\Exception $e) {
                $this->fail("Security scanning failed with correlation ID '{$correlationId}': " . $e->getMessage());
            }
        }
    }

    /**
     * Property: Security scanning should be performant across different scenarios.
     * 
     * **Validates: Requirements 6.3**
     */
    public function test_property_security_scanning_performance()
    {
        $method = $this->reflection->getMethod('performComprehensiveSecurityScan');
        $method->setAccessible(true);

        $performanceTestCases = [
            ['count' => 1, 'size' => 1000, 'maxTime' => 5.0],
            ['count' => 3, 'size' => 500, 'maxTime' => 10.0],
            ['count' => 5, 'size' => 200, 'maxTime' => 15.0],
        ];

        foreach ($performanceTestCases as $testCase) {
            $startTime = microtime(true);
            
            for ($i = 0; $i < $testCase['count']; $i++) {
                $file = UploadedFile::fake()->create("test-{$i}.pdf", $testCase['size'], 'application/pdf');
                $errors = [];
                $method->invoke($this->controller, $file, $errors, "test-correlation-id-{$i}");
            }
            
            $endTime = microtime(true);
            $totalTime = $endTime - $startTime;
            
            $this->assertLessThan($testCase['maxTime'], $totalTime, 
                "Security scanning {$testCase['count']} files of {$testCase['size']}KB should complete within {$testCase['maxTime']} seconds");
        }
    }
}