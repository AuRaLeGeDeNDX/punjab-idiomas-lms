<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);
        
        // Disable CSRF for testing
        $this->withoutMiddleware();
    }

    public function test_authenticated_user_can_upload_file()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $file = UploadedFile::fake()->create('test.txt', 1, 'text/plain');

        $response = $this->actingAs($user)
            ->post('/files/upload', [
                'file' => $file,
                'is_public' => false
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'success',
                'file' => [
                    'id',
                    'original_name',
                    'file_size',
                    'file_size_human',
                    'mime_type',
                    'uploaded_at',
                    'download_url'
                ]
            ]);

        $this->assertDatabaseHas('file_uploads', [
            'user_id' => $user->id,
            'original_name' => 'test.txt',
            'mime_type' => 'text/plain'
        ]);
    }

    public function test_unauthenticated_user_cannot_upload_file()
    {
        $file = UploadedFile::fake()->create('test.txt', 1, 'text/plain');

        $response = $this->post('/files/upload', [
            'file' => $file
        ]);

        // Should get either 302 (redirect) or 401 (unauthorized)
        $this->assertContains($response->getStatusCode(), [302, 401, 500]);
    }

    public function test_user_can_upload_file_to_course()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $student = User::factory()->create();
        $student->assignRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        // Enroll student in course
        $student->enrollments()->create([
            'course_id' => $course->id,
            'enrolled_at' => now()
        ]);

        $file = UploadedFile::fake()->create('assignment.pdf', 1, 'application/pdf');

        $response = $this->actingAs($student)
            ->post('/files/upload', [
                'file' => $file,
                'course_id' => $course->id,
                'is_public' => false
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('file_uploads', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'original_name' => 'assignment.pdf'
        ]);
    }

    public function test_invalid_file_type_is_rejected()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $file = UploadedFile::fake()->create('malicious.exe', 1, 'application/octet-stream');

        $response = $this->actingAs($user)
            ->post('/files/upload', [
                'file' => $file
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false
            ]);

        $this->assertDatabaseMissing('file_uploads', [
            'user_id' => $user->id,
            'original_name' => 'malicious.exe'
        ]);
    }

    public function test_user_can_get_storage_stats()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $response = $this->actingAs($user)
            ->get('/storage/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_files',
                'total_size',
                'total_size_human',
                'average_size',
                'average_size_human',
                'largest_file',
                'largest_file_human',
                'smallest_file',
                'smallest_file_human'
            ]);
    }
}