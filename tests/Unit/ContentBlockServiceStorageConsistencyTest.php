<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ContentBlockService;
use App\Services\SecureFileStorageService;
use App\Services\FileStorageDiagnosticService;
use App\Models\Content;
use App\Models\Subpage;
use App\Models\Module;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

/**
 * Unit tests for ContentBlockService storage consistency improvements.
 * 
 * Tests the enhanced file upload handling with:
 * - Consistent storage strategy across all upload operations
 * - Normalized file paths consistently
 * - Comprehensive audit logging
 * 
 * Requirements: 2.1, 2.2, 2.4
 */
class ContentBlockServiceStorageConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected ContentBlockService $service;
    protected SecureFileStorageService $secureFileStorage;
    protected FileStorageDiagnosticService $diagnosticService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the dependencies
        $this->secureFileStorage = Mockery::mock(SecureFileStorageService::class);
        $this->diagnosticService = Mockery::mock(FileStorageDiagnosticService::class);
        
        $this->service = new ContentBlockService(
            $this->secureFileStorage,
            $this->diagnosticService
        );
        
        // Mock Log facade
        Log::spy();
        
        // Set up storage fake
        Storage::fake('protected');
        Storage::fake('public');
    }

    /**
     * Test that consistent storage strategy is applied for all uploads.
     * 
     * Requirements: 2.1 - Apply clear rules for choosing between 'public' and 'protected' storage disks
     */
    public function test_applies_consistent_storage_strategy_for_all_uploads()
    {
        // Create test data
        $user = User::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $this->actingAs($user);
        
        // Create test file (simple file without GD dependency)
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        
        // Create actual file in storage for verification
        Storage::disk('protected')->put('content_blocks/image/2024/01/courses/1/users/1/test_123456_abcdef.jpg', 'fake content');
        
        // Mock SecureFileStorageService to return consistent result
        $this->secureFileStorage->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn([
                'file_path' => 'content_blocks/image/2024/01/courses/1/users/1/test_123456_abcdef.jpg',
                'secure_filename' => 'test_123456_abcdef.jpg',
                'original_filename' => 'test.jpg',
                'file_size' => 1024,
                'mime_type' => 'image/jpeg',
                'file_hash' => 'abc123def456',
                'storage_disk' => 'protected',
                'stored_at' => now()->toISOString(),
                'correlation_id' => 'test-correlation-id',
            ]);
        
        // Mock diagnostic service for verification
        $this->diagnosticService->shouldReceive('diagnoseFileStorageIssues')
            ->once()
            ->andReturn(new class {
                public function fileExists() { return true; }
                public function hasInconsistencies() { return false; }
                public function getInconsistencies() { return []; }
            });
        
        // Test upload
        $result = $this->service->createContentBlock($subpage, [
            'type' => 'image',
            'title' => 'Test Image',
            'file' => $file,
            'visibility' => 'student',
        ]);
        
        // Verify consistent storage strategy was applied
        $this->assertEquals('protected', $result->storage_disk);
        $this->assertStringContains('content_blocks/image/', $result->file_path);
        
        // Verify comprehensive audit logging
        Log::shouldHaveReceived('info')
            ->with('AUDIT_LOG: Upload operation started', Mockery::type('array'))
            ->once();
        
        Log::shouldHaveReceived('info')
            ->with('AUDIT_LOG: Upload operation completed successfully', Mockery::type('array'))
            ->once();
    }

    /**
     * Test that file paths are normalized consistently.
     * 
     * Requirements: 2.4 - Ensure file paths are normalized consistently
     */
    public function test_normalizes_file_paths_consistently()
    {
        // Create test data
        $user = User::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $this->actingAs($user);
        
        // Create test file
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        // Create actual file in storage for verification (normalized path)
        Storage::disk('protected')->put('content_blocks/pdf/2024/01/courses/1/users/1/test_123456_abcdef.pdf', 'fake content');
        
        // Mock SecureFileStorageService to return path with inconsistent separators
        $this->secureFileStorage->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn([
                'file_path' => '\\content_blocks\\pdf\\2024\\01\\//courses/1/users/1\\test_123456_abcdef.pdf',
                'secure_filename' => 'test_123456_abcdef.pdf',
                'original_filename' => 'test.pdf',
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'file_hash' => 'abc123def456',
                'storage_disk' => 'protected',
                'stored_at' => now()->toISOString(),
                'correlation_id' => 'test-correlation-id',
            ]);
        
        // Mock diagnostic service for verification
        $this->diagnosticService->shouldReceive('diagnoseFileStorageIssues')
            ->once()
            ->andReturn(new class {
                public function fileExists() { return true; }
                public function hasInconsistencies() { return false; }
                public function getInconsistencies() { return []; }
            });
        
        // Test upload
        $result = $this->service->createContentBlock($subpage, [
            'type' => 'pdf',
            'title' => 'Test PDF',
            'file' => $file,
            'visibility' => 'student',
        ]);
        
        // Verify path was normalized consistently
        $expectedPath = 'content_blocks/pdf/2024/01/courses/1/users/1/test_123456_abcdef.pdf';
        $this->assertEquals($expectedPath, $result->file_path);
        
        // Verify normalization was logged
        Log::shouldHaveReceived('info')
            ->with('ContentBlockService: File path normalized consistently', Mockery::subset([
                'normalization_applied' => true,
            ]))
            ->once();
    }

    /**
     * Test comprehensive audit logging for upload operations.
     * 
     * Requirements: 2.4 - Add comprehensive audit logging
     */
    public function test_provides_comprehensive_audit_logging()
    {
        // Create test data
        $user = User::factory()->create(['name' => 'Test User']);
        $course = Course::factory()->create(['teacher_id' => $user->id, 'title' => 'Test Course']);
        $module = Module::factory()->create(['course_id' => $course->id, 'title' => 'Test Module']);
        $subpage = Subpage::factory()->create(['module_id' => $module->id, 'title' => 'Test Subpage']);
        
        $this->actingAs($user);
        
        // Create test file
        $file = UploadedFile::fake()->create('test-image.jpg', 2048, 'image/jpeg');
        
        // Create actual file in storage for verification
        Storage::disk('protected')->put('content_blocks/image/2024/01/courses/1/users/1/test_image_123456_abcdef.jpg', 'fake content');
        
        // Mock SecureFileStorageService
        $this->secureFileStorage->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn([
                'file_path' => 'content_blocks/image/2024/01/courses/1/users/1/test_image_123456_abcdef.jpg',
                'secure_filename' => 'test_image_123456_abcdef.jpg',
                'original_filename' => 'test-image.jpg',
                'file_size' => 2048,
                'mime_type' => 'image/jpeg',
                'file_hash' => 'abc123def456',
                'storage_disk' => 'protected',
                'stored_at' => now()->toISOString(),
                'correlation_id' => 'test-correlation-id',
            ]);
        
        // Mock diagnostic service
        $this->diagnosticService->shouldReceive('diagnoseFileStorageIssues')
            ->once()
            ->andReturn(new class {
                public function fileExists() { return true; }
                public function hasInconsistencies() { return false; }
                public function getInconsistencies() { return []; }
            });
        
        // Test upload
        $result = $this->service->createContentBlock($subpage, [
            'type' => 'image',
            'title' => 'Test Image Upload',
            'file' => $file,
            'visibility' => 'student',
        ]);
        
        // Verify comprehensive audit logging for upload start
        Log::shouldHaveReceived('info')
            ->with('AUDIT_LOG: Upload operation started', Mockery::subset([
                'event_type' => 'upload_operation_start',
                'operation' => 'file_upload',
                'user_context' => Mockery::subset([
                    'user_id' => $user->id,
                    'user_name' => 'Test User',
                ]),
                'file_context' => Mockery::subset([
                    'original_filename' => 'test-image.jpg',
                    'file_size' => 2048,
                    'mime_type' => 'image/jpeg',
                    'content_type' => 'image',
                ]),
                'business_context' => Mockery::subset([
                    'subpage_id' => $subpage->id,
                    'subpage_title' => 'Test Subpage',
                    'course_title' => 'Test Course',
                ]),
            ]))
            ->once();
        
        // Verify comprehensive audit logging for upload success
        Log::shouldHaveReceived('info')
            ->with('AUDIT_LOG: Upload operation completed successfully', Mockery::subset([
                'event_type' => 'upload_operation_success',
                'result' => 'success',
                'storage_result' => Mockery::subset([
                    'storage_disk' => 'protected',
                    'storage_strategy' => 'secure',
                    'file_size' => 2048,
                ]),
                'consistency_checks' => Mockery::subset([
                    'path_normalized' => true,
                    'post_upload_verification' => 'passed',
                ]),
            ]))
            ->once();
    }

    /**
     * Test that upload failures are logged with comprehensive audit information.
     * 
     * Requirements: 2.4 - Add comprehensive audit logging
     */
    public function test_logs_upload_failures_comprehensively()
    {
        // Create test data
        $user = User::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $this->actingAs($user);
        
        // Create test file with invalid extension
        $file = UploadedFile::fake()->create('test.exe', 1024, 'application/octet-stream');
        
        // Test upload with invalid file type
        try {
            $this->service->createContentBlock($subpage, [
                'type' => 'image',
                'title' => 'Test Invalid Upload',
                'file' => $file,
                'visibility' => 'student',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            // Expected exception
        }
        
        // Verify failure was logged comprehensively
        Log::shouldHaveReceived('error')
            ->with('AUDIT_LOG: Upload operation failed', Mockery::subset([
                'event_type' => 'upload_operation_failure',
                'result' => 'failure',
                'failure_details' => Mockery::subset([
                    'failure_reason' => 'file_type_not_allowed',
                ]),
            ]))
            ->once();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}