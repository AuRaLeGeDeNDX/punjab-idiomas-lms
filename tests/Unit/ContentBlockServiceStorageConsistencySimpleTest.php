<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ContentBlockService;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionMethod;

/**
 * Simple unit tests for ContentBlockService storage consistency improvements.
 * 
 * Tests the key methods for:
 * - Consistent storage strategy determination
 * - File path normalization
 * - Audit logging functionality
 * 
 * Requirements: 2.1, 2.2, 2.4
 */
class ContentBlockServiceStorageConsistencySimpleTest extends TestCase
{
    protected ContentBlockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create service with mocked dependencies
        $this->service = $this->app->make(ContentBlockService::class);
        
        // Mock Log facade
        Log::spy();
    }

    /**
     * Test that consistent storage strategy is always 'secure' for new uploads.
     * 
     * Requirements: 2.1 - Apply clear rules for choosing between 'public' and 'protected' storage disks
     */
    public function test_determines_consistent_storage_strategy()
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('determineConsistentStorageStrategy');
        $method->setAccessible(true);
        
        // Create mock subpage
        $subpage = new class {
            public $id = 1;
            public $module;
            
            public function __construct() {
                $this->module = new class {
                    public $course_id = 1;
                };
            }
        };
        
        // Test different content types - all should return 'secure'
        $contentTypes = ['image', 'pdf', 'audio', 'video'];
        
        foreach ($contentTypes as $contentType) {
            $strategy = $method->invoke($this->service, $contentType, $subpage, 'test-correlation-id');
            
            $this->assertEquals('secure', $strategy, "Storage strategy should be 'secure' for content type: {$contentType}");
        }
        
        // Verify logging occurred
        Log::shouldHaveReceived('info')
            ->with('ContentBlockService: Storage strategy determined', \Mockery::subset([
                'strategy' => 'secure',
                'storage_disk' => 'protected',
                'reasoning' => 'All new uploads use protected storage for security and consistency',
            ]))
            ->times(count($contentTypes));
    }

    /**
     * Test that file paths are normalized consistently.
     * 
     * Requirements: 2.4 - Ensure file paths are normalized consistently
     */
    public function test_normalizes_file_paths_consistently()
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizeFilePathsConsistently');
        $method->setAccessible(true);
        
        // Test cases with various path inconsistencies
        $testCases = [
            // Input path => Expected normalized path
            '\\content_blocks\\image\\test.jpg' => 'content_blocks/image/test.jpg',
            '//content_blocks//image//test.jpg' => 'content_blocks/image/test.jpg',
            '/content_blocks/image/test.jpg' => 'content_blocks/image/test.jpg',
            'content_blocks\\image\\test.jpg\\' => 'content_blocks/image/test.jpg',
            '\\\\content_blocks\\\\image\\\\test.jpg' => 'content_blocks/image/test.jpg',
            'content_blocks/image/test.jpg' => 'content_blocks/image/test.jpg', // Already normalized
        ];
        
        foreach ($testCases as $inputPath => $expectedPath) {
            $storageResult = [
                'file_path' => $inputPath,
                'storage_disk' => 'protected',
            ];
            
            $normalizedResult = $method->invoke($this->service, $storageResult, 'test-correlation-id');
            
            $this->assertEquals($expectedPath, $normalizedResult['file_path'], 
                "Path normalization failed for input: {$inputPath}");
        }
        
        // Verify logging occurred for paths that needed normalization
        Log::shouldHaveReceived('info')
            ->with('ContentBlockService: File path normalized consistently', \Mockery::subset([
                'normalization_applied' => true,
            ]))
            ->atLeast()->times(1);
    }

    /**
     * Test that comprehensive audit logging methods exist and work.
     * 
     * Requirements: 2.4 - Add comprehensive audit logging
     */
    public function test_audit_logging_methods_exist_and_work()
    {
        // Use reflection to access private methods
        $reflection = new ReflectionClass($this->service);
        
        // Test logUploadOperationStart method
        $startMethod = $reflection->getMethod('logUploadOperationStart');
        $startMethod->setAccessible(true);
        
        // Create mock objects
        $file = new class {
            public function getClientOriginalName() { return 'test.jpg'; }
            public function getSize() { return 1024; }
            public function getMimeType() { return 'image/jpeg'; }
            public function getClientOriginalExtension() { return 'jpg'; }
        };
        
        $subpage = new class {
            public $id = 1;
            public $title = 'Test Subpage';
            public $module_id = 1;
            public $module;
            
            public function __construct() {
                $this->module = new class {
                    public $id = 1;
                    public $title = 'Test Module';
                    public $course_id = 1;
                    public $course;
                    
                    public function __construct() {
                        $this->course = new class {
                            public $id = 1;
                            public $title = 'Test Course';
                            public $teacher_id = 1;
                        };
                    }
                };
            }
        };
        
        // Mock auth user
        $this->actingAs(\App\Models\User::factory()->create(['name' => 'Test User']));
        
        // Test the method doesn't throw exceptions
        $startMethod->invoke($this->service, $file, 'image', $subpage, 'test-correlation-id');
        
        // Verify audit log was written
        Log::shouldHaveReceived('info')
            ->with('AUDIT_LOG: Upload operation started', \Mockery::subset([
                'event_type' => 'upload_operation_start',
                'operation' => 'file_upload',
                'file_context' => \Mockery::subset([
                    'original_filename' => 'test.jpg',
                    'file_size' => 1024,
                    'content_type' => 'image',
                ]),
            ]))
            ->once();
    }

    /**
     * Test that upload failure logging works correctly.
     * 
     * Requirements: 2.4 - Add comprehensive audit logging
     */
    public function test_logs_upload_failures_with_audit_information()
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('logUploadOperationFailure');
        $method->setAccessible(true);
        
        // Mock auth user
        $this->actingAs(\App\Models\User::factory()->create(['name' => 'Test User']));
        
        // Test failure logging
        $method->invoke($this->service, 'test-correlation-id', 'file_type_not_allowed', [
            'file_extension' => 'exe',
            'allowed_extensions' => ['jpg', 'png', 'gif'],
        ]);
        
        // Verify failure audit log was written
        Log::shouldHaveReceived('error')
            ->with('AUDIT_LOG: Upload operation failed', \Mockery::subset([
                'event_type' => 'upload_operation_failure',
                'result' => 'failure',
                'failure_details' => \Mockery::subset([
                    'failure_reason' => 'file_type_not_allowed',
                    'failure_context' => \Mockery::subset([
                        'file_extension' => 'exe',
                        'allowed_extensions' => ['jpg', 'png', 'gif'],
                    ]),
                ]),
            ]))
            ->once();
    }

    /**
     * Test that file size formatting works correctly.
     * 
     * Requirements: 2.4 - Add comprehensive audit logging (includes formatted file sizes)
     */
    public function test_formats_file_sizes_correctly()
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('formatBytes');
        $method->setAccessible(true);
        
        // Test various file sizes
        $testCases = [
            0 => '0 B',
            1024 => '1 KB',
            1048576 => '1 MB', // 1024 * 1024
            1073741824 => '1 GB', // 1024 * 1024 * 1024
            1536 => '1.5 KB', // 1024 + 512
            2097152 => '2 MB', // 2 * 1024 * 1024
        ];
        
        foreach ($testCases as $bytes => $expected) {
            $result = $method->invoke($this->service, $bytes);
            $this->assertEquals($expected, $result, "File size formatting failed for {$bytes} bytes");
        }
    }

    /**
     * Test that consistent filename generation works.
     * 
     * Requirements: 2.1, 2.2 - Consistent storage strategy and normalized file paths
     */
    public function test_generates_consistent_filenames()
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('generateConsistentFilename');
        $method->setAccessible(true);
        
        // Create mock file
        $file = new class {
            public function getClientOriginalName() { return 'Test File With Spaces & Special!@#.jpg'; }
            public function getClientOriginalExtension() { return 'jpg'; }
        };
        
        $filename = $method->invoke($this->service, $file, 'test-correlation-id');
        
        // Verify filename characteristics
        $this->assertStringContains('_', $filename, 'Filename should contain underscores');
        $this->assertStringEndsWith('.jpg', $filename, 'Filename should preserve extension');
        $this->assertStringStartsWith('Test_File_With_Spaces', $filename, 'Filename should sanitize special characters');
        $this->assertLessThanOrEqual(100, strlen($filename), 'Filename should not be excessively long');
        
        // Verify no special characters remain (except underscore, dash, and dot)
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_.-]+$/', $filename, 'Filename should only contain safe characters');
    }
}