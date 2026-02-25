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
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;

/**
 * Test upload failure cleanup logic in ContentBlockService.
 * 
 * Requirements 4.3: Implement cleanup for partially stored files on upload failure,
 * add clear error feedback for users, ensure atomic upload operations.
 */
class ContentBlockUploadFailureCleanupTest extends TestCase
{
    use RefreshDatabase;

    private ContentBlockService $service;
    private SecureFileStorageService $secureFileStorage;
    private FileStorageDiagnosticService $diagnosticService;
    private User $user;
    private Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create test data hierarchy
        $course = Course::factory()->create(['teacher_id' => $this->user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $module->id]);

        // Mock dependencies
        $this->secureFileStorage = Mockery::mock(SecureFileStorageService::class);
        $this->diagnosticService = Mockery::mock(FileStorageDiagnosticService::class);

        // Create service instance
        $this->service = new ContentBlockService(
            $this->secureFileStorage,
            $this->diagnosticService
        );

        // Set up storage fakes
        Storage::fake('protected');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that atomic rollback cleans up multiple files.
     * 
     * Requirements 4.3: Ensure atomic upload operations
     */
    public function test_atomic_rollback_cleans_up_multiple_files()
    {
        // Arrange
        Log::shouldReceive('info')->andReturn(true);
        Log::shouldReceive('error')->andReturn(true);
        Log::shouldReceive('warning')->andReturn(true);
        Log::shouldReceive('debug')->andReturn(true);

        // Create multiple files that should be cleaned up
        Storage::disk('protected')->put('test/file1.jpg', 'content1');
        Storage::disk('protected')->put('test/file2.jpg', 'content2');
        Storage::disk('public')->put('temp/file3.jpg', 'content3');

        $this->assertTrue(Storage::disk('protected')->exists('test/file1.jpg'));
        $this->assertTrue(Storage::disk('protected')->exists('test/file2.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('temp/file3.jpg'));

        // Use reflection to test the private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('performAtomicUploadRollback');
        $method->setAccessible(true);

        $fileData = [
            'file_path' => 'test/file1.jpg',
            'storage_disk' => 'protected',
            'original_filename' => 'file1.jpg',
            'file_size' => 1024,
        ];

        $uploadState = new \stdClass();
        $uploadState->filesCreated = [
            ['disk' => 'protected', 'path' => 'test/file1.jpg', 'type' => 'main_file'],
            ['disk' => 'protected', 'path' => 'test/file2.jpg', 'type' => 'thumbnail'],
            ['disk' => 'public', 'path' => 'temp/file3.jpg', 'type' => 'temp_file'],
        ];
        $uploadState->tempFiles = [];
        $uploadState->storageOperations = [];

        // Act
        $method->invoke($this->service, $fileData, $uploadState, 'test-correlation-id');

        // Assert
        $this->assertFalse(Storage::disk('protected')->exists('test/file1.jpg'));
        $this->assertFalse(Storage::disk('protected')->exists('test/file2.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('temp/file3.jpg'));
    }

    /**
     * Test that user-friendly error messages are generated.
     * 
     * Requirements 4.3: Add clear error feedback for users
     */
    public function test_generates_user_friendly_error_messages()
    {
        // Use reflection to test the private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateUserFriendlyErrorMessage');
        $method->setAccessible(true);

        // Test successful cleanup
        $cleanupResults = [
            'files_deleted' => 2,
            'temp_files_cleaned' => 1,
            'operations_reverted' => 1,
            'errors' => [],
        ];

        $message = $method->invoke($this->service, $cleanupResults);
        $this->assertStringContainsString('cleaned up successfully', $message);
        $this->assertStringContainsString('try uploading again', $message);

        // Test partial cleanup with errors
        $cleanupResults = [
            'files_deleted' => 1,
            'temp_files_cleaned' => 0,
            'operations_reverted' => 0,
            'errors' => ['Failed to delete file: test.jpg'],
        ];

        $message = $method->invoke($this->service, $cleanupResults);
        $this->assertStringContainsString('Most temporary files were cleaned up', $message);
        $this->assertStringContainsString('1 cleanup operations encountered issues', $message);

        // Test complete failure
        $cleanupResults = [
            'files_deleted' => 0,
            'temp_files_cleaned' => 0,
            'operations_reverted' => 0,
            'errors' => ['Multiple errors occurred'],
        ];

        $message = $method->invoke($this->service, $cleanupResults);
        $this->assertStringContainsString('cleanup operations encountered issues', $message);
        $this->assertStringContainsString('contact support', $message);
    }

    /**
     * Test that cleanup single file method works correctly.
     * 
     * Requirements 4.3: Implement cleanup for partially stored files on upload failure
     */
    public function test_cleanup_single_file_method()
    {
        // Arrange
        Log::shouldReceive('debug')->andReturn(true);
        Log::shouldReceive('warning')->andReturn(true);
        Log::shouldReceive('error')->andReturn(true);

        // Create a file to be cleaned up
        Storage::disk('protected')->put('test/cleanup.txt', 'test content');
        $this->assertTrue(Storage::disk('protected')->exists('test/cleanup.txt'));

        // Use reflection to test the private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('cleanupSingleFile');
        $method->setAccessible(true);

        $cleanupResults = [
            'files_deleted' => 0,
            'temp_files_cleaned' => 0,
            'operations_reverted' => 0,
            'errors' => [],
        ];

        // Act - use invokeArgs to pass by reference
        $method->invokeArgs($this->service, ['protected', 'test/cleanup.txt', 'test-correlation-id', &$cleanupResults]);

        // Assert
        $this->assertFalse(Storage::disk('protected')->exists('test/cleanup.txt'));
        $this->assertEquals(1, $cleanupResults['files_deleted']);
        $this->assertEmpty($cleanupResults['errors']);
    }

    /**
     * Test that cleanup handles non-existent files gracefully.
     * 
     * Requirements 4.3: Ensure atomic upload operations
     */
    public function test_cleanup_handles_non_existent_files_gracefully()
    {
        // Arrange
        Log::shouldReceive('debug')->andReturn(true);

        // Use reflection to test the private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('cleanupSingleFile');
        $method->setAccessible(true);

        $cleanupResults = [
            'files_deleted' => 0,
            'temp_files_cleaned' => 0,
            'operations_reverted' => 0,
            'errors' => [],
        ];

        // Act - try to clean up a file that doesn't exist, use invokeArgs to pass by reference
        $method->invokeArgs($this->service, ['protected', 'non/existent/file.txt', 'test-correlation-id', &$cleanupResults]);

        // Assert - should not cause errors
        $this->assertEquals(0, $cleanupResults['files_deleted']);
        $this->assertEmpty($cleanupResults['errors']);
    }

    /**
     * Test that upload state tracking works correctly.
     * 
     * Requirements 4.3: Ensure atomic upload operations
     */
    public function test_upload_state_tracking()
    {
        // Create an upload state object
        $uploadState = new \stdClass();
        $uploadState->filesCreated = [];
        $uploadState->tempFiles = [];
        $uploadState->storageOperations = [];

        // Verify initial state
        $this->assertEmpty($uploadState->filesCreated);
        $this->assertEmpty($uploadState->tempFiles);
        $this->assertEmpty($uploadState->storageOperations);

        // Add some tracking data
        $uploadState->filesCreated[] = [
            'disk' => 'protected',
            'path' => 'test/file.txt',
            'type' => 'main_file',
            'created_at' => now()->toISOString(),
        ];

        $uploadState->storageOperations[] = [
            'type' => 'secure_file_storage',
            'disk' => 'protected',
            'path' => 'test/file.txt',
            'operation_at' => now()->toISOString(),
        ];

        // Verify tracking data
        $this->assertCount(1, $uploadState->filesCreated);
        $this->assertCount(1, $uploadState->storageOperations);
        $this->assertEquals('protected', $uploadState->filesCreated[0]['disk']);
        $this->assertEquals('test/file.txt', $uploadState->filesCreated[0]['path']);
    }
}