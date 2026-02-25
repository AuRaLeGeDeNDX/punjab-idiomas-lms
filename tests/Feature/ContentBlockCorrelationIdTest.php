<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentBlockCorrelationIdTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private Course $course;
    private Module $module;
    private Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create test data
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);

        // Set up storage
        Storage::fake('public');
    }

    public function test_correlation_id_included_in_successful_response()
    {
        $this->actingAs($this->teacher);

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'text',
            'title' => 'Test Content',
            'content' => 'This is test content',
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'correlation_id'
            ]);

        // Verify correlation ID is a valid UUID format
        $correlationId = $response->json('correlation_id');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $correlationId);
    }

    public function test_correlation_id_included_in_validation_error_response()
    {
        $this->actingAs($this->teacher);

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'text',
            // Missing required content field
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'correlation_id'
            ]);

        // Verify correlation ID is a valid UUID format
        $correlationId = $response->json('correlation_id');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $correlationId);
    }

    public function test_correlation_id_included_in_file_upload_error_response()
    {
        $this->actingAs($this->teacher);

        // Create a fake file that's too large (simulate by using a large file)
        $file = UploadedFile::fake()->create('test.jpg', 50000); // 50MB file

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'file' => $file,
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
                'correlation_id'
            ]);

        // Verify correlation ID is a valid UUID format
        $correlationId = $response->json('correlation_id');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $correlationId);
    }

    public function test_correlation_id_included_in_update_response()
    {
        $this->actingAs($this->teacher);

        // Create a content block first
        $content = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'type' => 'text',
            'created_by' => $this->teacher->id,
        ]);

        $response = $this->putJson(route('api.content-blocks.update', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
            'content' => $content->id
        ]), [
            'type' => 'text',
            'title' => 'Updated Content',
            'content' => 'This is updated content',
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'correlation_id'
            ]);

        // Verify correlation ID is a valid UUID format
        $correlationId = $response->json('correlation_id');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $correlationId);
    }

    public function test_correlation_id_logged_in_upload_attempts()
    {
        $this->actingAs($this->teacher);

        // Capture log entries
        Log::spy();

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'text',
            'title' => 'Test Content',
            'content' => 'This is test content',
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        $response->assertStatus(201);
        $correlationId = $response->json('correlation_id');

        // Verify that log entries contain the correlation ID
        Log::shouldHaveReceived('info')
            ->with('ContentBlock store attempt', \Mockery::on(function ($context) use ($correlationId) {
                return isset($context['correlation_id']) && $context['correlation_id'] === $correlationId;
            }));

        Log::shouldHaveReceived('info')
            ->with('ContentBlock store: Starting validation', \Mockery::on(function ($context) use ($correlationId) {
                return isset($context['correlation_id']) && $context['correlation_id'] === $correlationId;
            }));

        Log::shouldHaveReceived('info')
            ->with('ContentBlock created successfully', \Mockery::on(function ($context) use ($correlationId) {
                return isset($context['correlation_id']) && $context['correlation_id'] === $correlationId;
            }));
    }

    public function test_correlation_id_logged_in_validation_failures()
    {
        $this->actingAs($this->teacher);

        // Capture log entries
        Log::spy();

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'text',
            // Missing required content field
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        $response->assertStatus(422);
        $correlationId = $response->json('correlation_id');

        // Verify that validation failure log contains the correlation ID
        Log::shouldHaveReceived('warning')
            ->with('ContentBlock store: Validation failed', \Mockery::on(function ($context) use ($correlationId) {
                return isset($context['correlation_id']) && $context['correlation_id'] === $correlationId;
            }));
    }

    public function test_different_requests_get_different_correlation_ids()
    {
        $this->actingAs($this->teacher);

        // Make first request
        $response1 = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'text',
            'title' => 'Test Content 1',
            'content' => 'This is test content 1',
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        // Make second request
        $response2 = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'text',
            'title' => 'Test Content 2',
            'content' => 'This is test content 2',
            'visibility' => 'student',
            'section' => 'main_content'
        ]);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $correlationId1 = $response1->json('correlation_id');
        $correlationId2 = $response2->json('correlation_id');

        // Verify that different requests get different correlation IDs
        $this->assertNotEquals($correlationId1, $correlationId2);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $correlationId1);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $correlationId2);
    }
}