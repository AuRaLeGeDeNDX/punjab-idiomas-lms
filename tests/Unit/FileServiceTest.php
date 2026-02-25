<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FileService;
use App\Models\User;
use App\Models\Course;
use App\Models\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class FileServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileService $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileService = new FileService();
        
        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);
    }

    public function test_can_upload_valid_file()
    {
        // Create a test user
        $user = User::factory()->create();
        $user->assignRole('Student');

        // Create a fake file (Laravel fake files are actually 1KB minimum)
        $file = UploadedFile::fake()->create('test.txt', 1, 'text/plain');

        // Upload the file
        $fileUpload = $this->fileService->uploadFile($file, $user);

        // Assert file was created in database
        $this->assertInstanceOf(FileUpload::class, $fileUpload);
        $this->assertEquals($user->id, $fileUpload->user_id);
        $this->assertEquals('test.txt', $fileUpload->original_name);
        $this->assertEquals('text/plain', $fileUpload->mime_type);
        $this->assertGreaterThan(0, $fileUpload->file_size); // File size will be at least 1KB
        $this->assertNotNull($fileUpload->file_hash);
    }

    public function test_can_upload_file_to_course()
    {
        // Create test data
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $student = User::factory()->create();
        $student->assignRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        // Create a fake file
        $file = UploadedFile::fake()->create('course-material.pdf', 500, 'application/pdf');

        // Upload the file to the course
        $fileUpload = $this->fileService->uploadFile($file, $student, $course);

        // Assert file was created with course association
        $this->assertEquals($course->id, $fileUpload->course_id);
        $this->assertEquals($student->id, $fileUpload->user_id);
        $this->assertStringContainsString("courses/{$course->id}/users/{$student->id}", $fileUpload->file_path);
    }

    public function test_rejects_invalid_file_type()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        // Create a fake file with invalid extension
        $file = UploadedFile::fake()->create('malicious.exe', 100, 'application/octet-stream');

        // Expect exception when uploading invalid file type
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File upload blocked due to security threats');

        $this->fileService->uploadFile($file, $user);
    }

    public function test_can_generate_temporary_url()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');
        $fileUpload = $this->fileService->uploadFile($file, $user);

        // Generate temporary URL
        $tempUrl = $this->fileService->generateTemporaryUrl($fileUpload, 30);

        // Assert URL is generated and contains token
        $this->assertStringContainsString('/files/download/', $tempUrl);
        // The token is in the URL path, not as a query parameter
        $this->assertMatchesRegularExpression('/\/files\/download\/[a-zA-Z0-9]+/', $tempUrl);
    }

    public function test_can_create_file_version()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        // Upload original file
        $originalFile = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
        $fileUpload = $this->fileService->uploadFile($originalFile, $user);

        // Create new version
        $newVersionFile = UploadedFile::fake()->create('document.txt', 150, 'text/plain');
        $newVersion = $this->fileService->createFileVersion($fileUpload, $newVersionFile, $user);

        // Assert new version was created
        $this->assertInstanceOf(FileUpload::class, $newVersion);
        $this->assertNotEquals($fileUpload->id, $newVersion->id);
        $this->assertStringContainsString('_v1.txt', $newVersion->original_name);
        $this->assertGreaterThan($fileUpload->file_size, $newVersion->file_size);
    }

    public function test_can_get_storage_stats()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        // Upload a file
        $file1 = UploadedFile::fake()->create('test1.txt', 1, 'text/plain');
        $this->fileService->uploadFile($file1, $user);

        // Get storage stats
        $stats = $this->fileService->getStorageStats();

        // Assert stats are correct
        $this->assertEquals(1, $stats['total_files']);
        $this->assertGreaterThan(0, $stats['total_size']);
        $this->assertGreaterThan(0, $stats['average_size']);
        $this->assertGreaterThan(0, $stats['largest_file']);
        $this->assertGreaterThan(0, $stats['smallest_file']);
    }

    public function test_can_delete_file()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');
        $fileUpload = $this->fileService->uploadFile($file, $user);

        // Delete the file
        $result = $this->fileService->deleteFile($fileUpload);

        // Assert file was deleted
        $this->assertTrue($result);
        $this->assertDatabaseMissing('file_uploads', ['id' => $fileUpload->id]);
    }

    public function test_detects_duplicate_files()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        // Create identical files with same content
        Storage::fake('local');
        $content = 'This is test content';
        $file1 = UploadedFile::fake()->createWithContent('test.txt', $content);
        $file2 = UploadedFile::fake()->createWithContent('test.txt', $content);

        // Upload first file
        $fileUpload1 = $this->fileService->uploadFile($file1, $user);

        // Upload second identical file - should return existing file
        $fileUpload2 = $this->fileService->uploadFile($file2, $user);

        // Assert same file record is returned
        $this->assertEquals($fileUpload1->id, $fileUpload2->id);
        $this->assertEquals($fileUpload1->file_hash, $fileUpload2->file_hash);
    }
}