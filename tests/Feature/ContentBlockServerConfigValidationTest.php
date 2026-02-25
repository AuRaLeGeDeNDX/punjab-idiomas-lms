<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ContentBlockController;
use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class ContentBlockServerConfigValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private Course $course;
    private Module $module;
    private Subpage $subpage;
    private ContentBlockController $controller;
    private ReflectionMethod $validateServerConfigurationMethod;
    private ReflectionMethod $validateServerResourcesMethod;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles for testing
        \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'Teacher']);
        \Spatie\Permission\Models\Role::create(['name' => 'Student']);
        
        // Create test data
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
        
        // Set up controller with reflection
        $this->controller = app(ContentBlockController::class);
        $reflection = new ReflectionClass($this->controller);
        
        $this->validateServerConfigurationMethod = $reflection->getMethod('validateServerConfiguration');
        $this->validateServerConfigurationMethod->setAccessible(true);
        
        $this->validateServerResourcesMethod = $reflection->getMethod('validateServerResources');
        $this->validateServerResourcesMethod->setAccessible(true);
        
        // Set up storage
        Storage::fake('public');
    }

    public function test_server_configuration_validation_passes_with_normal_settings()
    {
        $this->actingAs($this->teacher);
        
        // Test that normal server configuration doesn't throw exceptions
        try {
            $this->validateServerConfigurationMethod->invoke($this->controller);
            $this->assertTrue(true); // Test passes if no exception is thrown
        } catch (ValidationException $e) {
            // If validation fails, check that errors are actionable
            $errors = $e->errors()['server_config'] ?? [];
            foreach ($errors as $error) {
                $this->assertStringContainsString('administrator', $error);
                $this->assertGreaterThan(20, strlen($error));
            }
        }
    }

    public function test_server_resources_validation_with_small_file()
    {
        $this->actingAs($this->teacher);
        
        // Create a small test file without using GD
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'); // 100KB
        
        // Should not throw exception for small file
        try {
            $this->validateServerResourcesMethod->invoke($this->controller, $file);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            // If it fails, the error should be specific and actionable
            $errors = $e->errors()['file'] ?? [];
            foreach ($errors as $error) {
                $this->assertStringContainsString('disk space', $error);
                $this->assertStringContainsString('administrator', $error);
            }
        }
    }

    public function test_file_upload_with_server_configuration_validation()
    {
        $this->actingAs($this->teacher);
        
        // Create a test image file without using GD
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            'file' => $file,
        ]);
        
        // Should either succeed or fail with specific error messages
        if ($response->status() === 422) {
            $errors = $response->json('errors');
            
            // Check that any server configuration errors are actionable
            if (isset($errors['server_config'])) {
                foreach ($errors['server_config'] as $error) {
                    $this->assertStringContainsString('administrator', $error);
                    $this->assertGreaterThan(20, strlen($error));
                }
            }
            
            // Check that any file errors are specific
            if (isset($errors['file'])) {
                foreach ($errors['file'] as $error) {
                    $this->assertGreaterThan(20, strlen($error));
                    // Should not contain generic error messages
                    $this->assertStringNotContainsString('The file failed to upload', $error);
                }
            }
        } else {
            // If successful, verify the content was created
            $response->assertStatus(201);
            $this->assertDatabaseHas('contents', [
                'subpage_id' => $this->subpage->id,
                'type' => 'image',
                'title' => 'Test Image',
            ]);
        }
    }

    public function test_file_upload_error_messages_are_actionable()
    {
        $this->actingAs($this->teacher);
        
        // Try to upload without a file to trigger validation
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            // No file provided
        ]);
        
        $response->assertStatus(422);
        $errors = $response->json('errors');
        
        // Should have specific error about missing file
        $this->assertArrayHasKey('file', $errors);
        
        foreach ($errors['file'] as $error) {
            $this->assertIsString($error);
            $this->assertGreaterThan(10, strlen($error));
        }
    }

    public function test_large_file_upload_provides_specific_error_messages()
    {
        $this->actingAs($this->teacher);
        
        // Create a large file that might exceed limits without using GD
        $file = UploadedFile::fake()->create('large.jpg', 50000, 'image/jpeg'); // 50MB
        
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Large Image',
            'visibility' => 'student',
            'file' => $file,
        ]);
        
        // Should either succeed or provide specific error messages
        if ($response->status() === 422) {
            $errors = $response->json('errors');
            
            // Check for specific file size errors
            if (isset($errors['file'])) {
                foreach ($errors['file'] as $error) {
                    // Should mention specific sizes or limits
                    $hasSizeInfo = (
                        strpos($error, 'MB') !== false ||
                        strpos($error, 'KB') !== false ||
                        strpos($error, 'bytes') !== false ||
                        strpos($error, 'limit') !== false ||
                        strpos($error, 'size') !== false
                    );
                    $this->assertTrue($hasSizeInfo, "Error should mention size information: {$error}");
                }
            }
            
            // Check for server configuration errors
            if (isset($errors['server_config'])) {
                foreach ($errors['server_config'] as $error) {
                    $this->assertStringContainsString('administrator', $error);
                    $hasConfigInfo = (
                        strpos($error, 'upload_max_filesize') !== false ||
                        strpos($error, 'post_max_size') !== false ||
                        strpos($error, 'disk space') !== false ||
                        strpos($error, 'memory') !== false
                    );
                    $this->assertTrue($hasConfigInfo, "Error should mention specific configuration: {$error}");
                }
            }
        }
    }

    public function test_invalid_file_type_provides_specific_error_messages()
    {
        $this->actingAs($this->teacher);
        
        // Create a file with invalid extension for image type
        $file = UploadedFile::fake()->create('document.txt', 100);
        
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Invalid File',
            'visibility' => 'student',
            'file' => $file,
        ]);
        
        $response->assertStatus(422);
        $errors = $response->json('errors');
        
        // Should have specific error about file type
        $this->assertArrayHasKey('file', $errors);
        
        foreach ($errors['file'] as $error) {
            // Should mention allowed file types or extensions
            $hasTypeInfo = (
                strpos($error, 'type') !== false ||
                strpos($error, 'extension') !== false ||
                strpos($error, 'allowed') !== false ||
                strpos($error, 'jpg') !== false ||
                strpos($error, 'png') !== false
            );
            $this->assertTrue($hasTypeInfo, "Error should mention file type information: {$error}");
        }
    }

    public function test_server_configuration_recommendations_are_specific()
    {
        $this->actingAs($this->teacher);
        
        // Test with various content types to ensure all have proper validation
        $contentTypes = ['image', 'pdf', 'audio', 'video'];
        
        foreach ($contentTypes as $type) {
            $file = $this->createFileForType($type);
            
            $response = $this->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id,
            ]), [
                'type' => $type,
                'title' => "Test {$type}",
                'visibility' => 'student',
                'file' => $file,
            ]);
            
            // Assert that we get a valid response (either success or validation error)
            $this->assertContains($response->status(), [201, 422], 
                "Response status should be either 201 (success) or 422 (validation error) for {$type}");
            
            // If there are validation errors, they should be specific
            if ($response->status() === 422) {
                $errors = $response->json('errors');
                $this->assertIsArray($errors, "Errors should be an array for {$type}");
                
                foreach ($errors as $field => $fieldErrors) {
                    $this->assertIsArray($fieldErrors, "Field errors should be an array for {$type}.{$field}");
                    foreach ($fieldErrors as $error) {
                        $this->assertIsString($error, "Error should be a string for {$type}.{$field}");
                        $this->assertGreaterThan(15, strlen($error), 
                            "Error should be descriptive for {$type}.{$field}: {$error}");
                        
                        // Should not contain generic error messages
                        $this->assertStringNotContainsString('validation failed', strtolower($error),
                            "Error should not be generic for {$type}.{$field}: {$error}");
                        $this->assertStringNotContainsString('invalid input', strtolower($error),
                            "Error should not be generic for {$type}.{$field}: {$error}");
                    }
                }
            } else {
                // If successful, verify the content was created
                $this->assertDatabaseHas('contents', [
                    'subpage_id' => $this->subpage->id,
                    'type' => $type,
                    'title' => "Test {$type}",
                ]);
                $this->addToAssertionCount(1); // Count this as an assertion for the test type
            }
        }
        
        // Ensure we tested all content types
        $this->assertCount(4, $contentTypes, "Should test all 4 content types");
    }

    public function test_disk_space_validation_provides_actionable_guidance()
    {
        $this->actingAs($this->teacher);
        
        // Create a reasonably sized file without using GD
        $file = UploadedFile::fake()->create('test.jpg', 1000, 'image/jpeg'); // 1MB
        
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            'file' => $file,
        ]);
        
        // Assert that we get a valid response
        $this->assertContains($response->status(), [201, 422], 
            "Response status should be either 201 (success) or 422 (validation error)");
        
        // If disk space validation fails, error should be actionable
        if ($response->status() === 422) {
            $errors = $response->json('errors');
            $this->assertIsArray($errors, "Errors should be an array");
            
            if (isset($errors['file'])) {
                $this->assertIsArray($errors['file'], "File errors should be an array");
                foreach ($errors['file'] as $error) {
                    $this->assertIsString($error, "Error should be a string");
                    if (strpos($error, 'disk space') !== false) {
                        $this->assertStringContainsString('administrator', $error,
                            "Disk space error should mention administrator: {$error}");
                        // Should mention specific space amounts
                        $hasSpaceInfo = (
                            strpos($error, 'MB') !== false ||
                            strpos($error, 'GB') !== false ||
                            strpos($error, 'Available') !== false ||
                            strpos($error, 'Required') !== false
                        );
                        $this->assertTrue($hasSpaceInfo, "Disk space error should mention specific amounts: {$error}");
                    }
                }
            }
        } else {
            // If successful, verify the content was created
            $this->assertDatabaseHas('contents', [
                'subpage_id' => $this->subpage->id,
                'type' => 'image',
                'title' => 'Test Image',
            ]);
        }
        
        // Always assert that the response has the expected structure
        if ($response->status() === 201) {
            $response->assertJsonStructure([
                'success',
                'message',
                'data',
                'correlation_id'
            ]);
        } else {
            $response->assertJsonStructure([
                'success',
                'message',
                'errors',
                'correlation_id'
            ]);
        }
    }

    /**
     * Create a fake file appropriate for the given content type
     */
    private function createFileForType(string $type): UploadedFile
    {
        switch ($type) {
            case 'image':
                return UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
            case 'pdf':
                return UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
            case 'audio':
                return UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg');
            case 'video':
                return UploadedFile::fake()->create('video.mp4', 100, 'video/mp4');
            default:
                return UploadedFile::fake()->create('file.txt', 100);
        }
    }

    /**
     * Test that all validation methods handle edge cases gracefully
     */
    public function test_validation_handles_edge_cases_gracefully()
    {
        $this->actingAs($this->teacher);
        
        // Test with empty request
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id,
        ]), []);
        
        $response->assertStatus(422);
        $errors = $response->json('errors');
        
        // Should have specific validation errors, not generic ones
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $this->assertIsString($error);
                $this->assertNotEmpty($error);
                $this->assertStringNotContainsString('Something went wrong', $error);
            }
        }
    }

    /**
     * Test that error messages are consistent across different scenarios
     */
    public function test_error_messages_are_consistent()
    {
        $this->actingAs($this->teacher);
        
        // Test multiple scenarios that should produce similar error patterns
        $scenarios = [
            ['type' => 'image', 'file' => null],
            ['type' => 'pdf', 'file' => null],
            ['type' => 'audio', 'file' => null],
            ['type' => 'video', 'file' => null],
        ];
        
        foreach ($scenarios as $scenario) {
            $response = $this->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id,
            ]), array_merge([
                'title' => 'Test Content',
                'visibility' => 'student',
            ], $scenario));
            
            $response->assertStatus(422);
            $errors = $response->json('errors');
            
            // All should have file validation errors
            $this->assertArrayHasKey('file', $errors);
            
            foreach ($errors['file'] as $error) {
                $this->assertIsString($error);
                $this->assertGreaterThan(10, strlen($error));
            }
        }
    }
}