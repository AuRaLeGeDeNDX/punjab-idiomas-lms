<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ContentBlockService;
use App\Services\SecureFileStorageService;
use App\Services\FileStorageDiagnosticService;
use App\Services\DiagnosticResult;
use App\Services\FileLocation;
use App\Models\Content;
use App\Models\Subpage;
use App\Models\Module;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Mockery;

/**
 * Test post-upload verification functionality in ContentBlockService.
 * 
 * Tests Requirements 1.2 and 2.3:
 * - Verify file exists at stored path immediately after upload
 * - Validate database record matches actual file location
 * - Add rollback logic for failed verifications
 */
class ContentBlockPostUploadVerificationTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private ContentBlockService $service;
    private SecureFileStorageService $mockSecureStorage;
    private FileStorageDiagnosticService $mockDiagnosticService;
    private User $user;
    private Course $course;
    private Module $module;
    private Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and course structure
        $this->user = User::factory()->create();
        $this->course = Course::factory()->create();
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);

        Auth::login($this->user);

        // Mock dependencies
        $this->mockSecureStorage = Mockery::mock(SecureFileStorageService::class);
        $this->mockDiagnosticService = Mockery::mock(FileStorageDiagnosticService::class);

        // Create service with mocked dependencies
        $this->service = new ContentBlockService(
            $this->mockSecureStorage,
            $this->mockDiagnosticService
        );

        // Set up storage fake
        Storage::fake('protected');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful file upload with verification.
     */
    public function test_successful_file_upload_with_verification()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        
        $secureStorageResult = [
            'file_path' => 'content/test-file.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'file_hash' => null, // Skip hash verification for this test
            'storage_disk' => 'protected',
            'secure_filename' => 'test-file.jpg',
        ];

        // Mock secure storage service
        $this->mockSecureStorage
            ->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn($secureStorageResult);

        // Create actual file for verification with correct size
        Storage::disk('protected')->put($secureStorageResult['file_path'], str_repeat('x', 1024));

        // Mock diagnostic service for database record verification
        $mockDiagnosticResult = Mockery::mock(DiagnosticResult::class);
        $mockDiagnosticResult->shouldReceive('fileExists')->andReturn(true);
        $mockDiagnosticResult->shouldReceive('hasInconsistencies')->andReturn(false);

        $this->mockDiagnosticService
            ->shouldReceive('diagnoseFileStorageIssues')
            ->once()
            ->andReturn($mockDiagnosticResult);

        $data = [
            'title' => 'Test Content',
            'type' => 'image',
            'file' => $file,
        ];

        // Act
        $content = $this->service->createContentBlock($this->subpage, $data);

        // Assert
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('Test Content', $content->title);
        $this->assertEquals('image', $content->type);
        $this->assertEquals($secureStorageResult['file_path'], $content->file_path);
        $this->assertEquals($secureStorageResult['storage_disk'], $content->storage_disk);
        $this->assertEquals($secureStorageResult['file_size'], $content->file_size);
    }

    /**
     * Test file upload with verification failure triggers rollback.
     */
    public function test_file_upload_verification_failure_triggers_rollback()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        
        $secureStorageResult = [
            'file_path' => 'content/test-file.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'file_hash' => null,
            'storage_disk' => 'protected',
            'secure_filename' => 'test-file.jpg',
        ];

        // Mock secure storage service
        $this->mockSecureStorage
            ->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn($secureStorageResult);

        // Don't create the file - this will cause verification to fail
        // Storage::disk('protected')->put($secureStorageResult['file_path'], 'test content');

        $data = [
            'title' => 'Test Content',
            'type' => 'image',
            'file' => $file,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File upload verification failed');

        $this->service->createContentBlock($this->subpage, $data);

        // Verify no content was created
        $this->assertDatabaseMissing('contents', [
            'title' => 'Test Content',
            'type' => 'image',
        ]);
    }

    /**
     * Test file upload with size mismatch triggers rollback.
     */
    public function test_file_upload_size_mismatch_triggers_rollback()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        
        $secureStorageResult = [
            'file_path' => 'content/test-file.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'file_hash' => null,
            'storage_disk' => 'protected',
            'secure_filename' => 'test-file.jpg',
        ];

        // Mock secure storage service
        $this->mockSecureStorage
            ->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn($secureStorageResult);

        // Create file with different size than expected
        Storage::disk('protected')->put($secureStorageResult['file_path'], 'different content with different size');

        $data = [
            'title' => 'Test Content',
            'type' => 'image',
            'file' => $file,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File upload verification failed');

        $this->service->createContentBlock($this->subpage, $data);

        // Verify file was cleaned up during rollback
        $this->assertFalse(Storage::disk('protected')->exists($secureStorageResult['file_path']));
    }

    /**
     * Test database record consistency verification with inconsistencies.
     */
    public function test_database_record_consistency_verification_with_inconsistencies()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        
        $secureStorageResult = [
            'file_path' => 'content/test-file.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'file_hash' => null,
            'storage_disk' => 'protected',
            'secure_filename' => 'test-file.jpg',
        ];

        // Mock secure storage service
        $this->mockSecureStorage
            ->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn($secureStorageResult);

        // Create actual file for verification
        Storage::disk('protected')->put($secureStorageResult['file_path'], str_repeat('x', 1024));

        // Mock diagnostic service to return critical inconsistencies
        $mockDiagnosticResult = Mockery::mock(DiagnosticResult::class);
        $mockDiagnosticResult->shouldReceive('fileExists')->andReturn(true);
        $mockDiagnosticResult->shouldReceive('hasInconsistencies')->andReturn(true);
        $mockDiagnosticResult->shouldReceive('getInconsistencies')->andReturn([
            'storage_disk_mismatch' => [
                'recorded_disk' => 'protected',
                'actual_disk' => 'public',
                'severity' => 'medium',
            ]
        ]);

        $this->mockDiagnosticService
            ->shouldReceive('diagnoseFileStorageIssues')
            ->once()
            ->andReturn($mockDiagnosticResult);

        $data = [
            'title' => 'Test Content',
            'type' => 'image',
            'file' => $file,
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Database record verification failed');

        $this->service->createContentBlock($this->subpage, $data);
    }

    /**
     * Test successful database record consistency verification with minor inconsistencies.
     */
    public function test_database_record_consistency_verification_with_minor_inconsistencies()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        
        $secureStorageResult = [
            'file_path' => 'content/test-file.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'file_hash' => null,
            'storage_disk' => 'protected',
            'secure_filename' => 'test-file.jpg',
        ];

        // Mock secure storage service
        $this->mockSecureStorage
            ->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn($secureStorageResult);

        // Create actual file for verification
        Storage::disk('protected')->put($secureStorageResult['file_path'], str_repeat('x', 1024));

        // Mock diagnostic service to return non-critical inconsistencies
        $mockDiagnosticResult = Mockery::mock(DiagnosticResult::class);
        $mockDiagnosticResult->shouldReceive('fileExists')->andReturn(true);
        $mockDiagnosticResult->shouldReceive('hasInconsistencies')->andReturn(true);
        $mockDiagnosticResult->shouldReceive('getInconsistencies')->andReturn([
            'file_not_at_recorded_location' => [
                'recorded_disk' => 'protected',
                'file_path' => 'content/test-file.jpg',
                'severity' => 'high',
            ]
        ]);

        $this->mockDiagnosticService
            ->shouldReceive('diagnoseFileStorageIssues')
            ->once()
            ->andReturn($mockDiagnosticResult);

        $data = [
            'title' => 'Test Content',
            'type' => 'image',
            'file' => $file,
        ];

        // Act & Assert - should succeed despite non-critical inconsistencies
        $content = $this->service->createContentBlock($this->subpage, $data);

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('Test Content', $content->title);
    }

    /**
     * Test rollback functionality cleans up uploaded files.
     */
    public function test_rollback_cleans_up_uploaded_files()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        
        $secureStorageResult = [
            'file_path' => 'content/test-file.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'file_hash' => null,
            'storage_disk' => 'protected',
            'secure_filename' => 'test-file.jpg',
        ];

        // Mock secure storage service
        $this->mockSecureStorage
            ->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn($secureStorageResult);

        // Create file that will be uploaded
        Storage::disk('protected')->put($secureStorageResult['file_path'], 'test content');
        
        // Verify file exists before test
        $this->assertTrue(Storage::disk('protected')->exists($secureStorageResult['file_path']));

        // Create file with wrong size to trigger verification failure
        Storage::disk('protected')->put($secureStorageResult['file_path'], 'wrong size content');

        $data = [
            'title' => 'Test Content',
            'type' => 'image',
            'file' => $file,
        ];

        // Act
        try {
            $this->service->createContentBlock($this->subpage, $data);
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            // Expected exception
        }

        // Assert - file should be cleaned up
        $this->assertFalse(Storage::disk('protected')->exists($secureStorageResult['file_path']));
    }

    /**
     * Test file hash verification works correctly.
     */
    public function test_file_hash_verification()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        $testContent = str_repeat('x', 1024);
        $expectedHash = hash('sha256', $testContent);
        
        $secureStorageResult = [
            'file_path' => 'content/test-file.jpg',
            'original_filename' => 'test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'file_hash' => $expectedHash, // Set the expected hash
            'storage_disk' => 'protected',
            'secure_filename' => 'test-file.jpg',
        ];

        // Mock secure storage service
        $this->mockSecureStorage
            ->shouldReceive('storeFileSecurely')
            ->once()
            ->andReturn($secureStorageResult);

        // Create file with matching content and hash
        Storage::disk('protected')->put($secureStorageResult['file_path'], $testContent);

        // Mock diagnostic service
        $mockDiagnosticResult = Mockery::mock(DiagnosticResult::class);
        $mockDiagnosticResult->shouldReceive('fileExists')->andReturn(true);
        $mockDiagnosticResult->shouldReceive('hasInconsistencies')->andReturn(false);

        $this->mockDiagnosticService
            ->shouldReceive('diagnoseFileStorageIssues')
            ->once()
            ->andReturn($mockDiagnosticResult);

        $data = [
            'title' => 'Test Content',
            'type' => 'image',
            'file' => $file,
        ];

        // Act
        $content = $this->service->createContentBlock($this->subpage, $data);

        // Assert
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($expectedHash, $content->file_hash);
    }
}