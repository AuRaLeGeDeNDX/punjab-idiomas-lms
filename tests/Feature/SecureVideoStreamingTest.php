<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SecureVideoStreamingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure private disk exists
        Storage::fake('private');
    }

    /**
     * Test that unauthenticated users cannot access videos.
     */
    public function test_unauthenticated_user_cannot_access_video()
    {
        $content = $this->createVideoContent();

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->get($signedUrl);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that enrolled students can access videos.
     */
    public function test_enrolled_student_can_access_video()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        $content = $this->createVideoContent();
        $course = $content->subpage->module->course;

        // Enroll student in course
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Create fake video file
        Storage::disk('private')->put($content->file_path, 'fake video content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'video/mp4');
    }

    /**
     * Test that non-enrolled students cannot access videos.
     */
    public function test_non_enrolled_student_cannot_access_video()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        $content = $this->createVideoContent();

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertForbidden();
    }

    /**
     * Test that course teachers can access videos.
     */
    public function test_course_teacher_can_access_video()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $content = $this->createVideoContent();
        $course = $content->subpage->module->course;
        
        // Set teacher as course teacher
        $course->teacher_id = $teacher->id;
        $course->save();

        // Create fake video file
        Storage::disk('private')->put($content->file_path, 'fake video content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($teacher)->get($signedUrl);

        $response->assertOk();
    }

    /**
     * Test that admins can access all videos.
     */
    public function test_admin_can_access_any_video()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = $this->createVideoContent();

        // Create fake video file
        Storage::disk('private')->put($content->file_path, 'fake video content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($admin)->get($signedUrl);

        $response->assertOk();
    }

    /**
     * Test that expired URLs are rejected.
     */
    public function test_expired_url_returns_forbidden()
    {
        $user = User::factory()->create();
        $content = $this->createVideoContent();

        // Create expired URL
        $expiredUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->subMinutes(10), // Expired 10 minutes ago
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($expiredUrl);

        $response->assertForbidden();
    }

    /**
     * Test that invalid signatures are rejected.
     */
    public function test_invalid_signature_returns_forbidden()
    {
        $user = User::factory()->create();
        $content = $this->createVideoContent();

        // Create valid URL then tamper with signature
        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        // Tamper with signature
        $tamperedUrl = str_replace('signature=', 'signature=invalid', $signedUrl);

        $response = $this->actingAs($user)->get($tamperedUrl);

        $response->assertForbidden();
    }

    /**
     * Test that inactive content cannot be accessed.
     */
    public function test_inactive_content_cannot_be_accessed()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        $content = $this->createVideoContent(['is_active' => false]);
        $course = $content->subpage->module->course;

        // Enroll student
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertForbidden();
    }

    /**
     * Test that non-video content types are rejected.
     */
    public function test_non_video_content_type_returns_404()
    {
        $user = User::factory()->create();
        
        $content = $this->createVideoContent(['type' => 'pdf']);

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertNotFound();
    }

    /**
     * Test that missing files return 404.
     */
    public function test_missing_file_returns_404()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        $content = $this->createVideoContent();
        $course = $content->subpage->module->course;

        // Enroll student
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Don't create the file - it should be missing

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertNotFound();
    }

    /**
     * Test HTTP Range requests for video seeking.
     */
    public function test_range_request_returns_partial_content()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        $content = $this->createVideoContent();
        $course = $content->subpage->module->course;

        // Enroll student
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Create fake video file with known size
        $videoData = str_repeat('x', 1000000); // 1MB
        Storage::disk('private')->put($content->file_path, $videoData);

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        // Request specific byte range
        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=0-999'])
            ->get($signedUrl);

        $response->assertStatus(206); // Partial Content
        $response->assertHeader('Content-Range');
        $response->assertHeader('Accept-Ranges', 'bytes');
    }

    /**
     * Test that security headers are present.
     */
    public function test_security_headers_are_present()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        $content = $this->createVideoContent();
        $course = $content->subpage->module->course;

        // Enroll student
        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        // Create fake video file
        Storage::disk('private')->put($content->file_path, 'fake video content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Test getSecureVideoUrl() method returns signed URL.
     */
    public function test_get_secure_video_url_returns_signed_url()
    {
        $content = $this->createVideoContent();

        $url = $content->getSecureVideoUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    /**
     * Test getSecureVideoUrl() returns null for non-video content.
     */
    public function test_get_secure_video_url_returns_null_for_non_video()
    {
        $content = $this->createVideoContent(['type' => 'pdf']);

        $url = $content->getSecureVideoUrl();

        $this->assertNull($url);
    }

    /**
     * Helper method to create video content with full hierarchy.
     */
    protected function createVideoContent(array $attributes = []): Content
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        $module = Module::factory()->create([
            'course_id' => $course->id,
        ]);

        $subpage = Subpage::factory()->create([
            'module_id' => $module->id,
        ]);

        return Content::factory()->create(array_merge([
            'subpage_id' => $subpage->id,
            'type' => 'video',
            'file_path' => 'videos/test_video.mp4',
            'mime_type' => 'video/mp4',
            'storage_disk' => 'private',
            'is_active' => true,
        ], $attributes));
    }
}
