<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;

/**
 * Integration tests for FileUploadValidator with Content Builder
 * 
 * These tests verify that the client-side FileUploadValidator integrates
 * properly with the server-side content builder functionality.
 * 
 * Requirements: 5.1, 5.2
 */
class FileUploadValidatorIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    private $teacher;
    private $course;
    private $module;
    private $subpage;
    
    protected function setUp(): void
    {
        parent::setUp();
        
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
     * Test that content builder page includes FileUploadValidator JavaScript
     */
    public function test_content_builder_includes_file_upload_validator()
    {
        $this->actingAs($this->teacher);
        
        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));
        
        $response->assertStatus(200);
        
        // Check that the page includes the FileUploadValidator class
        $response->assertSee('class FileUploadValidator');
        $response->assertSee('validateFile(file)');
        $response->assertSee('formatFileSize');
    }
    
    /**
     * Test that content type configurations are properly passed to JavaScript
     */
    public function test_content_type_configurations_available_to_javascript()
    {
        $this->actingAs($this->teacher);
        
        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));
        
        $response->assertStatus(200);
        
        // Check that content type configurations are available
        $response->assertSee('contentTypes');
        $response->assertSee('max_file_size');
        $response->assertSee('allowed_extensions');
        
        // Check for specific content types
        $response->assertSee('image');
        $response->assertSee('pdf');
        $response->assertSee('audio');
        $response->assertSee('video');
    }
    
    /**
     * Test client-side validation integration with file upload form
     */
    public function test_client_side_validation_integration()
    {
        $this->actingAs($this->teacher);
        
        $response = $this->get(route('teacher.courses.modules.subpages.content-builder', [
            $this->course,
            $this->module,
            $this->subpage
        ]));
        
        $response->assertStatus(200);
        
        // Check that file input handling is set up
        $response->assertSee('handleFileSelection');
        $response->assertSee('setupFileInputHandling');
        $response->assertSee('validateFile');
        
        // Check that error display functions are available
        $response->assertSee('displayEnhancedErrorMessages');
        $response->assertSee('showFilePreview');
    }
    
    /**
     * Test that server-side validation still works when client-side validation passes
     */
    public function test_server_side_validation_after_client_side_validation()
    {
        $this->actingAs($this->teacher);
        
        // Create a valid image file that should pass client-side validation
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(2048); // 2MB
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        // Should succeed since both client and server validation pass
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        
        // Verify file was stored
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'image',
            'title' => 'Test Image'
        ]);
    }
    
    /**
     * Test that server-side validation catches files that bypass client-side validation
     */
    public function test_server_side_validation_catches_bypassed_client_validation()
    {
        $this->actingAs($this->teacher);
        
        // Create a file that would fail client-side validation (too large)
        $file = UploadedFile::fake()->image('large.jpg', 2000, 2000)->size(50 * 1024); // 50MB
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Large Image',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        // Should fail at server-side validation
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'file'
            ]
        ]);
        
        // Should include detailed error information
        $responseData = $response->json();
        $this->assertStringContainsString('size', strtolower($responseData['errors']['file'][0]));
    }
    
    /**
     * Test that invalid file extensions are properly handled
     */
    public function test_invalid_file_extensions_handled_properly()
    {
        $this->actingAs($this->teacher);
        
        // Create a file with invalid extension for image content type
        $file = UploadedFile::fake()->create('malware.exe', 1024, 'application/x-executable');
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Invalid File',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        // Should fail validation
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'file'
            ]
        ]);
        
        // Should include specific error about file type
        $responseData = $response->json();
        $fileError = $responseData['errors']['file'][0];
        $this->assertStringContainsString('exe', strtolower($fileError));
        $this->assertStringContainsString('not allowed', strtolower($fileError));
    }
    
    /**
     * Test that empty files are properly rejected
     */
    public function test_empty_files_properly_rejected()
    {
        $this->actingAs($this->teacher);
        
        // Create an empty file
        $file = UploadedFile::fake()->create('empty.txt', 0);
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'pdf',
            'title' => 'Empty File',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        // Should fail validation
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'file'
            ]
        ]);
        
        // Should include specific error about empty file
        $responseData = $response->json();
        $fileError = $responseData['errors']['file'][0];
        $this->assertStringContainsString('empty', strtolower($fileError));
    }
    
    /**
     * Test that file size limits are properly enforced
     */
    public function test_file_size_limits_properly_enforced()
    {
        $this->actingAs($this->teacher);
        
        // Create a file that exceeds the typical image size limit
        $file = UploadedFile::fake()->image('huge.jpg', 5000, 5000)->size(20 * 1024); // 20MB
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Huge Image',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        // Should fail validation
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'file'
            ]
        ]);
        
        // Should include specific error about file size
        $responseData = $response->json();
        $fileError = $responseData['errors']['file'][0];
        $this->assertStringContainsString('size', strtolower($fileError));
        $this->assertStringContainsString('exceed', strtolower($fileError));
    }
    
    /**
     * Test that different content types have appropriate validation rules
     */
    public function test_different_content_types_have_appropriate_validation()
    {
        $this->actingAs($this->teacher);
        
        $testCases = [
            [
                'type' => 'image',
                'file' => UploadedFile::fake()->image('test.jpg', 800, 600)->size(1024),
                'shouldPass' => true
            ],
            [
                'type' => 'image',
                'file' => UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf'),
                'shouldPass' => false // PDF file for image content type
            ],
            [
                'type' => 'pdf',
                'file' => UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf'),
                'shouldPass' => true
            ],
            [
                'type' => 'pdf',
                'file' => UploadedFile::fake()->image('image.jpg', 100, 100)->size(1024),
                'shouldPass' => false // Image file for PDF content type
            ]
        ];
        
        foreach ($testCases as $case) {
            $response = $this->postJson(route('api.content-blocks.store', [
                $this->course,
                $this->module,
                $this->subpage
            ]), [
                'type' => $case['type'],
                'title' => 'Test Content',
                'file' => $case['file'],
                'section' => 'main_content',
                'visibility' => 'student',
                'is_active' => true
            ]);
            
            if ($case['shouldPass']) {
                $response->assertStatus(201);
            } else {
                $response->assertStatus(422);
                $response->assertJsonStructure([
                    'errors' => [
                        'file'
                    ]
                ]);
            }
        }
    }
    
    /**
     * Test that validation error messages are user-friendly and actionable
     */
    public function test_validation_error_messages_are_user_friendly()
    {
        $this->actingAs($this->teacher);
        
        // Test with oversized file
        $file = UploadedFile::fake()->image('large.jpg', 2000, 2000)->size(50 * 1024); // 50MB
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Large Image',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        $response->assertStatus(422);
        $responseData = $response->json();
        $fileError = $responseData['errors']['file'][0];
        
        // Error message should be descriptive and helpful
        $this->assertStringContainsString('size', strtolower($fileError));
        $this->assertStringContainsString('MB', $fileError); // Should include formatted sizes
        
        // Should not contain technical jargon or error codes
        $this->assertStringNotContainsString('UPLOAD_ERR_', $fileError);
        $this->assertStringNotContainsString('validation.', $fileError);
    }
    
    /**
     * Test that correlation IDs are included for debugging
     */
    public function test_correlation_ids_included_for_debugging()
    {
        $this->actingAs($this->teacher);
        
        // Create an invalid file to trigger validation error
        $file = UploadedFile::fake()->create('invalid.exe', 1024, 'application/x-executable');
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Invalid File',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        $response->assertStatus(422);
        $responseData = $response->json();
        
        // Should include correlation ID for debugging
        $this->assertArrayHasKey('correlation_id', $responseData);
        $this->assertNotEmpty($responseData['correlation_id']);
        
        // Correlation ID should be a valid UUID format
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $responseData['correlation_id']
        );
    }
    
    /**
     * Test that upload guidance is provided in error responses
     */
    public function test_upload_guidance_provided_in_error_responses()
    {
        $this->actingAs($this->teacher);
        
        // Create a file with wrong extension
        $file = UploadedFile::fake()->create('document.txt', 1024, 'text/plain');
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Wrong Type',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        $response->assertStatus(422);
        $responseData = $response->json();
        
        // Should include upload guidance
        if (isset($responseData['upload_guidance'])) {
            $this->assertArrayHasKey('supported_types', $responseData['upload_guidance']);
            $this->assertArrayHasKey('size_limits', $responseData['upload_guidance']);
        }
        
        // Error message should include allowed types
        $fileError = $responseData['errors']['file'][0];
        $this->assertStringContainsString('allowed', strtolower($fileError));
    }
    
    /**
     * Test that multiple validation errors are properly aggregated
     */
    public function test_multiple_validation_errors_properly_aggregated()
    {
        $this->actingAs($this->teacher);
        
        // Create a file that fails multiple validation rules
        $file = UploadedFile::fake()->create('huge.exe', 100 * 1024 * 1024, 'application/x-executable'); // Large executable
        
        $response = $this->postJson(route('api.content-blocks.store', [
            $this->course,
            $this->module,
            $this->subpage
        ]), [
            'type' => 'image',
            'title' => 'Invalid File',
            'file' => $file,
            'section' => 'main_content',
            'visibility' => 'student',
            'is_active' => true
        ]);
        
        $response->assertStatus(422);
        $responseData = $response->json();
        
        // Should have file validation errors
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('file', $responseData['errors']);
        
        // File errors should be an array (multiple errors possible)
        $this->assertIsArray($responseData['errors']['file']);
        
        // Should have at least one error (could be size, type, or security)
        $this->assertNotEmpty($responseData['errors']['file']);
    }
}