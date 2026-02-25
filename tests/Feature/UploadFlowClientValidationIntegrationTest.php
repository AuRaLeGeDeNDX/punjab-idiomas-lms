<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Integration tests for client-side validation integration with server-side upload flow.
 * 
 * These tests verify that:
 * - Content builder pages include necessary JavaScript validation
 * - Content type configurations are available to client-side code
 * - Server-side validation catches what client-side validation might miss
 * - Client and server validation work together seamlessly
 * - Upload guidance is provided in responses
 * 
 * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5
 */
class UploadFlowClientValidationIntegrationTest extends TestCase
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

        // Set up storage for testing
        Storage::fake('public');
    }

    /**
     * Test that content builder page includes FileUploadValidator JavaScript.
     * 
     * @test
     */
    public function it_includes_file_upload_validator_javascript_in_content_builder()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check that the page includes the FileUploadValidator class
        $response->assertSee('class FileUploadValidator', false);
        $response->assertSee('validateFile(file)', false);
        $response->assertSee('formatFileSize', false);
        $response->assertSee('formatBytes', false);

        // Check for validation methods
        $response->assertSee('validateFileSize', false);
        $response->assertSee('validateFileExtension', false);
        $response->assertSee('validateFileType', false);
    }

    /**
     * Test that content type configurations are available to JavaScript.
     * 
     * @test
     */
    public function it_provides_content_type_configurations_to_javascript()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check that content type configurations are available
        $response->assertSee('contentTypes', false);
        $response->assertSee('max_file_size', false);
        $response->assertSee('allowed_extensions', false);

        // Check for specific content types
        $response->assertSee('image', false);
        $response->assertSee('pdf', false);
        $response->assertSee('audio', false);
        $response->assertSee('video', false);

        // Check for file size limits
        $response->assertSee('MB', false);
        $response->assertSee('KB', false);

        // Check for allowed extensions arrays
        $response->assertSee('jpg', false);
        $response->assertSee('png', false);
        $response->assertSee('pdf', false);
        $response->assertSee('mp3', false);
        $response->assertSee('mp4', false);
    }

    /**
     * Test client-side validation integration with file input handling.
     * 
     * @test
     */
    public function it_integrates_client_side_validation_with_file_input_handling()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check that file input handling is set up
        $response->assertSee('handleFileSelection', false);
        $response->assertSee('setupFileInputHandling', false);
        $response->assertSee('validateFile', false);

        // Check that error display functions are available
        $response->assertSee('displayEnhancedErrorMessages', false);
        $response->assertSee('showFilePreview', false);
        $response->assertSee('clearFileInput', false);

        // Check for file input event handlers
        $response->assertSee('change', false);
        $response->assertSee('addEventListener', false);
    }

    /**
     * Test that server-side validation catches files that bypass client-side validation.
     * 
     * @test
     */
    public function it_catches_files_that_bypass_client_side_validation()
    {
        $this->actingAs($this->teacher);

        $bypassTestCases = [
            [
                'name' => 'oversized_file_bypass',
                'file' => UploadedFile::fake()->image('bypass-size.jpg', 2000, 2000)->size(30 * 1024), // 30MB
                'expected_error_contains' => 'size',
                'description' => 'File that would fail client-side size validation',
            ],
            [
                'name' => 'invalid_extension_bypass',
                'file' => UploadedFile::fake()->create('bypass-ext.php', 1024, 'application/x-php'),
                'expected_error_contains' => 'not allowed',
                'description' => 'File with extension that should be blocked client-side',
            ],
            [
                'name' => 'mime_spoofing_bypass',
                'file' => UploadedFile::fake()->create('bypass-mime.jpg', 1024, 'text/plain'),
                'expected_error_contains' => 'mime',
                'description' => 'File with mismatched MIME type and extension',
            ],
            [
                'name' => 'empty_file_bypass',
                'file' => UploadedFile::fake()->create('bypass-empty.txt', 0),
                'expected_error_contains' => 'empty',
                'description' => 'Empty file that should be caught client-side',
            ],
        ];

        foreach ($bypassTestCases as $testCase) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => "Bypass test: {$testCase['name']}",
                'section' => 'main_content',
                'visibility' => 'student',
                'file' => $testCase['file'],
            ]);

            // Server-side validation should catch the issue
            $response->assertStatus(422);
            $response->assertJsonStructure([
                'success',
                'message',
                'errors' => ['file'],
                'correlation_id',
            ]);

            $responseData = $response->json();
            $errorMessage = strtolower($responseData['errors']['file'][0]);

            $this->assertStringContainsString(
                $testCase['expected_error_contains'],
                $errorMessage,
                "Server should catch {$testCase['description']}"
            );

            // Verify no content was created
            $this->assertDatabaseMissing('contents', [
                'title' => "Bypass test: {$testCase['name']}",
            ]);
        }
    }

    /**
     * Test that valid files pass both client and server validation.
     * 
     * @test
     */
    public function it_allows_valid_files_through_both_client_and_server_validation()
    {
        $this->actingAs($this->teacher);

        $validTestCases = [
            [
                'type' => 'image',
                'file' => UploadedFile::fake()->image('valid.jpg', 800, 600)->size(1024),
                'additional_fields' => ['alt_text' => 'Valid image'],
            ],
            [
                'type' => 'image',
                'file' => UploadedFile::fake()->image('valid.png', 400, 300)->size(512),
                'additional_fields' => ['alt_text' => 'Valid PNG'],
            ],
            [
                'type' => 'pdf',
                'file' => UploadedFile::fake()->create('valid.pdf', 2048, 'application/pdf'),
                'additional_fields' => [],
            ],
            [
                'type' => 'audio',
                'file' => UploadedFile::fake()->create('valid.mp3', 3072, 'audio/mpeg'),
                'additional_fields' => [],
            ],
            [
                'type' => 'video',
                'file' => UploadedFile::fake()->create('valid.mp4', 5120, 'video/mp4'),
                'additional_fields' => [],
            ],
        ];

        foreach ($validTestCases as $index => $testCase) {
            $requestData = array_merge([
                'type' => $testCase['type'],
                'title' => "Valid {$testCase['type']} test {$index}",
                'section' => 'main_content',
                'visibility' => 'student',
                'file' => $testCase['file'],
            ], $testCase['additional_fields']);

            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", $requestData);

            // Should succeed through both validations
            $response->assertStatus(201);
            $response->assertJson(['success' => true]);

            // Verify content was created
            $this->assertDatabaseHas('contents', [
                'title' => "Valid {$testCase['type']} test {$index}",
                'type' => $testCase['type'],
            ]);

            $content = Content::where('title', "Valid {$testCase['type']} test {$index}")->first();
            $this->assertNotNull($content);
            Storage::disk('public')->assertExists($content->file_path);
        }
    }

    /**
     * Test upload guidance is provided in error responses.
     * 
     * @test
     */
    public function it_provides_upload_guidance_in_error_responses()
    {
        $this->actingAs($this->teacher);

        $guidanceTestCases = [
            [
                'file' => UploadedFile::fake()->create('guidance-test.txt', 1024, 'text/plain'),
                'expected_guidance_elements' => [
                    'allowed types',
                    'jpg',
                    'png',
                    'gif',
                ],
            ],
            [
                'file' => UploadedFile::fake()->image('guidance-large.jpg', 3000, 3000)->size(25 * 1024), // 25MB
                'expected_guidance_elements' => [
                    'size',
                    'MB',
                    'maximum',
                    'compress',
                ],
            ],
        ];

        foreach ($guidanceTestCases as $testCase) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => 'Upload Guidance Test',
                'section' => 'main_content',
                'visibility' => 'student',
                'file' => $testCase['file'],
            ]);

            $response->assertStatus(422);
            $responseData = $response->json();

            // Check for upload guidance in response
            if (isset($responseData['upload_guidance'])) {
                $this->assertArrayHasKey('supported_types', $responseData['upload_guidance']);
                $this->assertArrayHasKey('size_limits', $responseData['upload_guidance']);
            }

            // Check that error message includes guidance elements
            $errorMessage = strtolower($responseData['errors']['file'][0]);
            foreach ($testCase['expected_guidance_elements'] as $guidanceElement) {
                $this->assertStringContainsString(
                    strtolower($guidanceElement),
                    $errorMessage,
                    "Error message should include guidance: {$guidanceElement}"
                );
            }
        }
    }

    /**
     * Test that validation error messages are user-friendly for client integration.
     * 
     * @test
     */
    public function it_provides_user_friendly_error_messages_for_client_integration()
    {
        $this->actingAs($this->teacher);

        $userFriendlyTestCases = [
            [
                'file' => UploadedFile::fake()->image('friendly-large.jpg', 2000, 2000)->size(20 * 1024),
                'user_friendly_elements' => [
                    'too large',
                    'MB',
                    'maximum allowed',
                    'try',
                ],
                'technical_elements_to_avoid' => [
                    'validation.file.max',
                    'UPLOAD_ERR_',
                    'constraint violation',
                ],
            ],
            [
                'file' => UploadedFile::fake()->create('friendly-wrong.exe', 1024, 'application/x-executable'),
                'user_friendly_elements' => [
                    'not supported',
                    'please use',
                    'allowed types',
                ],
                'technical_elements_to_avoid' => [
                    'mime_type',
                    'validation_rule',
                    'whitelist',
                ],
            ],
        ];

        foreach ($userFriendlyTestCases as $testCase) {
            $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
                'type' => 'image',
                'title' => 'User Friendly Error Test',
                'section' => 'main_content',
                'visibility' => 'student',
                'file' => $testCase['file'],
            ]);

            $response->assertStatus(422);
            $responseData = $response->json();
            $errorMessage = $responseData['errors']['file'][0];

            // Check for user-friendly elements
            foreach ($testCase['user_friendly_elements'] as $friendlyElement) {
                $this->assertStringContainsString(
                    strtolower($friendlyElement),
                    strtolower($errorMessage),
                    "Error message should be user-friendly and contain: {$friendlyElement}"
                );
            }

            // Check that technical jargon is avoided
            foreach ($testCase['technical_elements_to_avoid'] as $technicalElement) {
                $this->assertStringNotContainsString(
                    $technicalElement,
                    $errorMessage,
                    "Error message should avoid technical jargon: {$technicalElement}"
                );
            }
        }
    }

    /**
     * Test file preview functionality integration.
     * 
     * @test
     */
    public function it_supports_file_preview_functionality_integration()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check for file preview functionality
        $response->assertSee('showFilePreview', false);
        $response->assertSee('createImagePreview', false);
        $response->assertSee('showFileInfo', false);
        $response->assertSee('formatFileSize', false);

        // Check for preview container elements
        $response->assertSee('file-preview', false);
        $response->assertSee('file-info', false);
        $response->assertSee('preview-image', false);
    }

    /**
     * Test drag and drop file selection integration.
     * 
     * @test
     */
    public function it_supports_drag_and_drop_file_selection_integration()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check for drag and drop functionality
        $response->assertSee('dragover', false);
        $response->assertSee('dragleave', false);
        $response->assertSee('drop', false);
        $response->assertSee('preventDefault', false);

        // Check for drag and drop styling classes
        $response->assertSee('drag-over', false);
        $response->assertSee('drop-zone', false);
    }

    /**
     * Test upload progress indication integration.
     * 
     * @test
     */
    public function it_supports_upload_progress_indication_integration()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check for progress indication functionality
        $response->assertSee('showUploadProgress', false);
        $response->assertSee('updateProgress', false);
        $response->assertSee('hideProgress', false);

        // Check for progress elements
        $response->assertSee('progress', false);
        $response->assertSee('progress-bar', false);
        $response->assertSee('upload-status', false);
    }

    /**
     * Test retry mechanism integration for failed uploads.
     * 
     * @test
     */
    public function it_supports_retry_mechanism_for_failed_uploads()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check for retry functionality
        $response->assertSee('retryUpload', false);
        $response->assertSee('showRetryButton', false);
        $response->assertSee('resetUploadForm', false);

        // Check for retry UI elements
        $response->assertSee('retry-button', false);
        $response->assertSee('upload-failed', false);
    }

    /**
     * Test that content type switching updates validation rules dynamically.
     * 
     * @test
     */
    public function it_updates_validation_rules_dynamically_on_content_type_change()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check for dynamic validation rule updates
        $response->assertSee('updateValidationRules', false);
        $response->assertSee('onContentTypeChange', false);
        $response->assertSee('getContentTypeConfig', false);

        // Check for content type selector
        $response->assertSee('content-type-select', false);
        $response->assertSee('type', false);
        $response->assertSee('image', false);
        $response->assertSee('pdf', false);
        $response->assertSee('audio', false);
        $response->assertSee('video', false);
    }

    /**
     * Test external URL alternative integration with client-side validation.
     * 
     * @test
     */
    public function it_integrates_external_url_alternative_with_client_validation()
    {
        $this->actingAs($this->teacher);

        // Test external URL without file upload
        $response = $this->postJson("/api/courses/{$this->course->id}/modules/{$this->module->id}/subpages/{$this->subpage->id}/content-blocks", [
            'type' => 'video',
            'title' => 'External URL Integration Test',
            'section' => 'main_content',
            'visibility' => 'student',
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // Verify content was created with external URL
        $this->assertDatabaseHas('contents', [
            'title' => 'External URL Integration Test',
            'type' => 'video',
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $content = Content::where('title', 'External URL Integration Test')->first();
        $this->assertNull($content->file_path);
        $this->assertNotNull($content->external_url);
    }

    /**
     * Test that client-side validation provides immediate feedback.
     * 
     * @test
     */
    public function it_provides_immediate_client_side_feedback()
    {
        $this->actingAs($this->teacher);

        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));

        $response->assertStatus(200);

        // Check for immediate feedback functionality
        $response->assertSee('showImmediateError', false);
        $response->assertSee('clearErrors', false);
        $response->assertSee('validateOnChange', false);

        // Check for error display elements
        $response->assertSee('error-message', false);
        $response->assertSee('field-error', false);
        $response->assertSee('validation-error', false);

        // Check for success feedback
        $response->assertSee('showSuccess', false);
        $response->assertSee('success-message', false);
    }
}