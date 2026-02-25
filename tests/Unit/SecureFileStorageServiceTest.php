<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SecureFileStorageService;
use App\Services\FileUploadLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

/**
 * **Validates: Requirements 6.4, 6.5**
 * 
 * Test secure file storage functionality including:
 * - Files stored outside web root
 * - Cryptographically secure unique file names
 * - Proper file permissions
 * - File access logging
 */
class SecureFileStorageServiceTest extends TestCase
{
    protected SecureFileStorageService $secureFileStorage;
    protected $mockFileUploadLogger;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the FileUploadLogger
        $this->mockFileUploadLogger = Mockery::mock(FileUploadLogger::class);
        $this->mockFileUploadLogger->shouldReceive('logSecureFileStorage')->andReturn(true);
        $this->mockFileUploadLogger->shouldReceive('logSecureFileDeletion')->andReturn(true);
        
        $this->secureFileStorage = new SecureFileStorageService($this->mockFileUploadLogger);
        
        // Set up test user without database
        $this->actingAs($this->createMockUser());
        
        // Ensure protected disk exists
        Storage::fake('protected');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_stores_files_outside_web_root_using_protected_disk()
    {
        // Create a test file (non-image to avoid GD dependency)
        $file = UploadedFile::fake()->create('test.pdf', 50);
        
        // Store file securely
        $result = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        
        // Assert file is stored on protected disk (outside web root)
        $this->assertEquals('protected', $result['storage_disk']);
        $this->assertTrue(Storage::disk('protected')->exists($result['file_path']));
        
        // Assert file is NOT on public disk (inside web root)
        $this->assertFalse(Storage::disk('public')->exists($result['file_path']));
    }

    /** @test */
    public function it_generates_cryptographically_secure_unique_filenames()
    {
        $file = UploadedFile::fake()->create('test.pdf', 50);
        
        // Store the same file multiple times
        $result1 = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        $result2 = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        
        // Assert filenames are different (unique)
        $this->assertNotEquals($result1['secure_filename'], $result2['secure_filename']);
        
        // Assert filenames contain cryptographic elements
        $this->assertMatchesRegularExpression('/^test_\d+_\d+_[a-f0-9]{32}\.pdf$/', $result1['secure_filename']);
        $this->assertMatchesRegularExpression('/^test_\d+_\d+_[a-f0-9]{32}\.pdf$/', $result2['secure_filename']);
        
        // Assert filenames have sufficient entropy (32 hex chars = 128 bits)
        preg_match('/_([a-f0-9]{32})\./', $result1['secure_filename'], $matches1);
        preg_match('/_([a-f0-9]{32})\./', $result2['secure_filename'], $matches2);
        
        $this->assertNotEquals($matches1[1], $matches2[1]);
        $this->assertEquals(32, strlen($matches1[1]));
        $this->assertEquals(32, strlen($matches2[1]));
    }

    /** @test */
    public function it_sets_proper_file_permissions_on_uploaded_files()
    {
        $file = UploadedFile::fake()->create('test.pdf', 50);
        
        $result = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        
        // Get the absolute path to verify permissions
        $absolutePath = Storage::disk('protected')->path($result['file_path']);
        
        // Assert file exists
        $this->assertFileExists($absolutePath);
        
        // Assert file permissions are secure (640 = rw-r-----)
        $permissions = fileperms($absolutePath) & 0777;
        $this->assertEquals(0640, $permissions, 'File permissions should be 640 (rw-r-----)');
    }

    /** @test */
    public function it_logs_file_storage_access_for_security_auditing()
    {
        Log::shouldReceive('info')
            ->with('SecureFileStorage: Starting secure file storage', Mockery::type('array'))
            ->once();
            
        Log::shouldReceive('info')
            ->with('SecureFileStorage: Generated secure filename', Mockery::type('array'))
            ->once();
            
        Log::shouldReceive('info')
            ->with('SecureFileStorage: Storing file outside web root', Mockery::type('array'))
            ->once();
            
        Log::shouldReceive('info')
            ->with('SecureFileStorage: File stored and verified successfully', Mockery::type('array'))
            ->once();
            
        Log::shouldReceive('info')
            ->with('SecureFileStorage: File stored securely', Mockery::type('array'))
            ->once();
            
        Log::shouldReceive('channel')
            ->with('single')
            ->andReturnSelf();
            
        Log::shouldReceive('info')
            ->with('FILE_ACCESS_LOG', Mockery::type('array'))
            ->once();

        $file = UploadedFile::fake()->create('test.pdf', 50);
        
        $result = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        
        // Assert logging was called (verified by shouldReceive expectations)
        $this->assertTrue(true);
    }

    /** @test */
    public function it_generates_file_hash_for_integrity_verification()
    {
        $file = UploadedFile::fake()->create('test.pdf', 50);
        
        $result = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        
        // Assert file hash is generated
        $this->assertArrayHasKey('file_hash', $result);
        $this->assertNotEmpty($result['file_hash']);
        
        // Assert hash is SHA256 (64 hex characters)
        $this->assertEquals(64, strlen($result['file_hash']));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result['file_hash']);
        
        // Verify hash matches file content
        $storedContent = Storage::disk('protected')->get($result['file_path']);
        $expectedHash = hash('sha256', $storedContent);
        $this->assertEquals($expectedHash, $result['file_hash']);
    }

    /** @test */
    public function it_creates_hierarchical_storage_path_structure()
    {
        $file = UploadedFile::fake()->create('test.pdf', 50);
        
        $context = [
            'user_id' => 123,
            'course_id' => 456,
        ];
        
        $result = $this->secureFileStorage->storeFileSecurely($file, 'pdf', $context);
        
        // Assert path follows hierarchical structure
        $expectedPathPattern = '/^content_blocks\/pdf\/\d{4}\/\d{2}\/\d{2}\/courses\/456\/users\/123\//';
        $this->assertMatchesRegularExpression($expectedPathPattern, $result['file_path']);
    }

    /** @test */
    public function it_generates_secure_access_tokens_for_file_download()
    {
        $filePath = 'test/path/file.jpg';
        $expirationMinutes = 60;
        $context = ['test' => 'data'];
        
        $token = $this->secureFileStorage->generateSecureAccessToken($filePath, $expirationMinutes, $context);
        
        // Assert token is generated
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 64-character token
        
        // Assert token data is stored in cache
        $tokenData = Cache::get("secure_file_token:{$token}");
        $this->assertNotNull($tokenData);
        $this->assertEquals($filePath, $tokenData['file_path']);
        $this->assertEquals($context, $tokenData['context']);
        $this->assertGreaterThan(time(), $tokenData['expires_at']);
    }

    /** @test */
    public function it_validates_secure_access_tokens_correctly()
    {
        $filePath = 'test/path/file.jpg';
        $context = ['test' => 'data'];
        
        // Generate token
        $token = $this->secureFileStorage->generateSecureAccessToken($filePath, 60, $context);
        
        // Validate token
        $tokenData = $this->secureFileStorage->validateSecureAccessToken($token);
        
        $this->assertNotNull($tokenData);
        $this->assertEquals($filePath, $tokenData['file_path']);
        $this->assertEquals($context, $tokenData['context']);
    }

    /** @test */
    public function it_rejects_invalid_or_expired_tokens()
    {
        // Test invalid token
        $invalidToken = str_repeat('a', 64);
        $result = $this->secureFileStorage->validateSecureAccessToken($invalidToken);
        $this->assertNull($result);
        
        // Test expired token
        $filePath = 'test/path/file.jpg';
        $token = $this->secureFileStorage->generateSecureAccessToken($filePath, -1); // Expired
        
        // Wait a moment to ensure expiration
        sleep(1);
        
        $result = $this->secureFileStorage->validateSecureAccessToken($token);
        $this->assertNull($result);
    }

    /** @test */
    public function it_deletes_files_securely_with_logging()
    {
        // Store a file first
        $file = UploadedFile::fake()->create('test.pdf', 50);
        $result = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        
        // Verify file exists
        $this->assertTrue(Storage::disk('protected')->exists($result['file_path']));
        
        // Mock logging expectations
        Log::shouldReceive('info')
            ->with('SecureFileStorage: Starting secure file deletion', Mockery::type('array'))
            ->once();
            
        Log::shouldReceive('info')
            ->with('SecureFileStorage: File deleted successfully', Mockery::type('array'))
            ->once();
            
        Log::shouldReceive('channel')
            ->with('single')
            ->andReturnSelf();
            
        Log::shouldReceive('info')
            ->with('FILE_DELETION_LOG', Mockery::type('array'))
            ->once();
        
        // Delete file
        $correlationId = 'test-correlation-id';
        $deleted = $this->secureFileStorage->deleteFileSecurely($result['file_path'], $correlationId);
        
        // Assert deletion was successful
        $this->assertTrue($deleted);
        $this->assertFalse(Storage::disk('protected')->exists($result['file_path']));
    }

    /** @test */
    public function it_handles_file_storage_errors_gracefully()
    {
        // Create a file that will cause storage to fail
        $file = UploadedFile::fake()->create('test.pdf', 50);
        
        // Mock storage to fail
        Storage::shouldReceive('disk')
            ->with('protected')
            ->andReturnSelf();
            
        Storage::shouldReceive('storeAs')
            ->andReturn(false);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to store file securely');
        
        $this->secureFileStorage->storeFileSecurely($file, 'pdf');
    }

    /** @test */
    public function it_preserves_original_filename_for_user_reference()
    {
        $originalFilename = 'my-important-document.pdf';
        $file = UploadedFile::fake()->create($originalFilename, 100);
        
        $result = $this->secureFileStorage->storeFileSecurely($file, 'pdf');
        
        // Assert original filename is preserved
        $this->assertEquals($originalFilename, $result['original_filename']);
        
        // Assert secure filename is different
        $this->assertNotEquals($originalFilename, $result['secure_filename']);
    }

    /**
     * Create a mock user for authentication.
     */
    private function createMockUser()
    {
        $user = Mockery::mock(\App\Models\User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('Test User');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('test@example.com');
        
        return $user;
    }
}