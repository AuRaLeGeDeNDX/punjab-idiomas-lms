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

class SecureMediaStreamingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create storage disks if they don't exist
        Storage::fake('private');
        Storage::fake('protected');
    }

    /**
     * Helper method to create a complete content structure with course enrollment.
     */
    private function createContentWithEnrollment(string $type, array $contentAttributes = [])
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Generate a file path based on type
        $filePath = $contentAttributes['file_path'] ?? "test_{$type}/" . uniqid() . ".{$type}";
        
        $content = Content::factory()->create(array_merge([
            'subpage_id' => $subpage->id,
            'type' => $type,
            'is_active' => true,
            'storage_disk' => 'private',
            'file_path' => $filePath,
        ], $contentAttributes));

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        return [$user, $content];
    }

    /** @test */
    public function enrolled_user_can_access_pdf()
    {
        [$user, $content] = $this->createContentWithEnrollment('pdf');

        Storage::disk('private')->put($content->file_path, 'fake pdf content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    /** @test */
    public function enrolled_user_can_access_audio()
    {
        [$user, $content] = $this->createContentWithEnrollment('audio', [
            'mime_type' => 'audio/mpeg',
        ]);

        Storage::disk('private')->put($content->file_path, str_repeat('x', 1000000));

        $signedUrl = URL::temporarySignedRoute(
            'secure.audio.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertOk();
        $response->assertHeader('Accept-Ranges', 'bytes');
    }

    /** @test */
    public function audio_supports_range_requests()
    {
        [$user, $content] = $this->createContentWithEnrollment('audio', [
            'file_size' => 1000000,
            'mime_type' => 'audio/mpeg',
        ]);

        Storage::disk('private')->put($content->file_path, str_repeat('x', 1000000));

        $signedUrl = URL::temporarySignedRoute(
            'secure.audio.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=0-999'])
            ->get($signedUrl);

        $response->assertStatus(206); // Partial Content
        $response->assertHeader('Content-Range');
        $response->assertHeader('Content-Length', '1000');
    }

    /** @test */
    public function enrolled_user_can_access_image()
    {
        [$user, $content] = $this->createContentWithEnrollment('image', [
            'mime_type' => 'image/jpeg',
        ]);

        Storage::disk('private')->put($content->file_path, 'fake image content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.image.serve',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    /** @test */
    public function non_enrolled_user_cannot_access_pdf()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $filePath = 'test_pdf/' . uniqid() . '.pdf';
        
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'is_active' => true,
            'file_path' => $filePath,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertForbidden();
    }

    /** @test */
    public function non_enrolled_user_cannot_access_audio()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $filePath = 'test_audio/' . uniqid() . '.mp3';
        
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'audio',
            'is_active' => true,
            'file_path' => $filePath,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.audio.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertForbidden();
    }

    /** @test */
    public function non_enrolled_user_cannot_access_image()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $filePath = 'test_image/' . uniqid() . '.jpg';
        
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'image',
            'is_active' => true,
            'file_path' => $filePath,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.image.serve',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_media()
    {
        $filePath = 'test_pdf/' . uniqid() . '.pdf';
        
        $content = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => $filePath,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->get($signedUrl);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function expired_url_returns_forbidden()
    {
        [$user, $content] = $this->createContentWithEnrollment('pdf');

        Storage::disk('private')->put($content->file_path, 'fake pdf content');

        $expiredUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->subMinutes(10), // Expired 10 minutes ago
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($expiredUrl);

        $response->assertForbidden();
    }

    /** @test */
    public function inactive_content_returns_forbidden()
    {
        [$user, $content] = $this->createContentWithEnrollment('pdf', [
            'is_active' => false,
        ]);

        Storage::disk('private')->put($content->file_path, 'fake pdf content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertForbidden();
    }

    /** @test */
    public function missing_file_returns_not_found()
    {
        [$user, $content] = $this->createContentWithEnrollment('pdf');

        // Don't create the file

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertNotFound();
    }

    /** @test */
    public function teacher_can_access_course_media()
    {
        $teacher = User::factory()->create();
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $filePath = 'test_pdf/' . uniqid() . '.pdf';
        
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'is_active' => true,
            'storage_disk' => 'private',
            'file_path' => $filePath,
        ]);

        Storage::disk('private')->put($content->file_path, 'fake pdf content');

        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($teacher)->get($signedUrl);

        $response->assertOk();
    }

    /** @test */
    public function wrong_content_type_returns_not_found()
    {
        [$user, $content] = $this->createContentWithEnrollment('image', [
            'mime_type' => 'image/jpeg',
        ]);

        Storage::disk('private')->put($content->file_path, 'fake image content');

        // Try to access image content via PDF route
        $signedUrl = URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $content->id]
        );

        $response = $this->actingAs($user)->get($signedUrl);

        $response->assertNotFound();
    }

    /** @test */
    public function content_model_generates_correct_signed_urls()
    {
        $pdfContent = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => 'test_pdf/' . uniqid() . '.pdf',
        ]);
        $audioContent = Content::factory()->create([
            'type' => 'audio',
            'file_path' => 'test_audio/' . uniqid() . '.mp3',
        ]);
        $imageContent = Content::factory()->create([
            'type' => 'image',
            'file_path' => 'test_image/' . uniqid() . '.jpg',
        ]);

        $this->assertStringContainsString('/secure/pdf/', $pdfContent->getSecurePdfUrl());
        $this->assertStringContainsString('/secure/audio/', $audioContent->getSecureAudioUrl());
        $this->assertStringContainsString('/secure/image/', $imageContent->getSecureImageUrl());
        
        $this->assertStringContainsString('signature=', $pdfContent->getSecurePdfUrl());
        $this->assertStringContainsString('expires=', $pdfContent->getSecurePdfUrl());
    }

    /** @test */
    public function get_secure_media_url_returns_correct_url_for_type()
    {
        $pdfContent = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => 'test_pdf/' . uniqid() . '.pdf',
        ]);
        $audioContent = Content::factory()->create([
            'type' => 'audio',
            'file_path' => 'test_audio/' . uniqid() . '.mp3',
        ]);
        $imageContent = Content::factory()->create([
            'type' => 'image',
            'file_path' => 'test_image/' . uniqid() . '.jpg',
        ]);
        $textContent = Content::factory()->create([
            'type' => 'text',
            'file_path' => null,
        ]);

        $this->assertStringContainsString('/secure/pdf/', $pdfContent->getSecureMediaUrl());
        $this->assertStringContainsString('/secure/audio/', $audioContent->getSecureMediaUrl());
        $this->assertStringContainsString('/secure/image/', $imageContent->getSecureMediaUrl());
        $this->assertNull($textContent->getSecureMediaUrl());
    }
}
