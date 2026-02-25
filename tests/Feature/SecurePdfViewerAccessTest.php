<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\PdfAccessLog;
use App\Models\Subpage;
use App\Models\User;
use App\Services\SecurePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurePdfViewerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected SecurePdfService $pdfService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdfService = app(SecurePdfService::class);
        
        // Setup storage
        Storage::fake('protected');
    }

    /** @test */
    public function it_logs_successful_viewer_access()
    {
        // Create test data
        $teacher = User::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Create PDF content
        Storage::disk('protected')->put('test.pdf', 'fake pdf content');
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'file_path' => 'test.pdf',
            'storage_disk' => 'protected',
        ]);

        // Generate valid token
        $token = $this->pdfService->generateViewerUrl($content, $teacher);
        $tokenPart = last(explode('/', $token));

        // Access viewer
        $response = $this->actingAs($teacher)->get(route('secure.pdf.viewer', [
            'content' => $content->id,
            'token' => $tokenPart,
        ]));

        // Assert successful response
        $response->assertStatus(200);
        $response->assertViewIs('secure-pdf.viewer');
        $response->assertViewHas('content');
        $response->assertViewHas('user');

        // Assert access was logged
        $this->assertDatabaseHas('pdf_access_logs', [
            'user_id' => $teacher->id,
            'content_id' => $content->id,
            'access_granted' => true,
            'failure_reason' => null,
        ]);
    }

    /** @test */
    public function it_logs_failed_access_with_invalid_token()
    {
        // Create test data
        $teacher = User::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);

        // Use invalid token
        $invalidToken = 'invalid_token_string';

        // Access viewer
        $response = $this->actingAs($teacher)->get(route('secure.pdf.viewer', [
            'content' => $content->id,
            'token' => $invalidToken,
        ]));

        // Assert forbidden response
        $response->assertStatus(403);

        // Assert failed access was logged
        $this->assertDatabaseHas('pdf_access_logs', [
            'user_id' => $teacher->id,
            'content_id' => $content->id,
            'access_granted' => false,
            'failure_reason' => 'invalid_or_expired_token',
        ]);
    }

    /** @test */
    public function it_logs_failed_access_with_insufficient_permissions()
    {
        // Create test data
        $teacher = User::factory()->create();
        $unauthorizedUser = User::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        Storage::disk('protected')->put('test.pdf', 'fake pdf content');
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'file_path' => 'test.pdf',
            'storage_disk' => 'protected',
        ]);

        // Generate token for unauthorized user
        $token = $this->pdfService->generateViewerUrl($content, $unauthorizedUser);
        $tokenPart = last(explode('/', $token));

        // Access viewer as unauthorized user
        $response = $this->actingAs($unauthorizedUser)->get(route('secure.pdf.viewer', [
            'content' => $content->id,
            'token' => $tokenPart,
        ]));

        // Assert forbidden response
        $response->assertStatus(403);

        // Assert failed access was logged
        $this->assertDatabaseHas('pdf_access_logs', [
            'user_id' => $unauthorizedUser->id,
            'content_id' => $content->id,
            'access_granted' => false,
            'failure_reason' => 'insufficient_permissions',
        ]);
    }

    /** @test */
    public function it_passes_user_data_to_view()
    {
        // Create test data
        $teacher = User::factory()->create([
            'name' => 'Test Teacher',
            'email' => 'teacher@example.com',
        ]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        Storage::disk('protected')->put('test.pdf', 'fake pdf content');
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'file_path' => 'test.pdf',
            'storage_disk' => 'protected',
        ]);

        // Generate valid token
        $token = $this->pdfService->generateViewerUrl($content, $teacher);
        $tokenPart = last(explode('/', $token));

        // Access viewer
        $response = $this->actingAs($teacher)->get(route('secure.pdf.viewer', [
            'content' => $content->id,
            'token' => $tokenPart,
        ]));

        // Assert user data is passed to view
        $response->assertStatus(200);
        $response->assertViewHas('user', function ($userData) use ($teacher) {
            return $userData['name'] === $teacher->name
                && $userData['email'] === $teacher->email
                && isset($userData['ip']);
        });
    }
}
