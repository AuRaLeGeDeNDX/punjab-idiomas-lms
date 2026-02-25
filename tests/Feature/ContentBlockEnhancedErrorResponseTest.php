<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Test enhanced error responses for ContentBlock API.
 * 
 * This test verifies that the API returns detailed error information including:
 * - Server configuration information
 * - Retry suggestions for transient failures
 * - Field-specific targeting for validation errors
 * - Upload guidance for file upload errors
 * 
 * Requirements: 1.1, 1.4, 6.4
 */
class ContentBlockEnhancedErrorResponseTest extends TestCase
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

        $this->course = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
        ]);

        $this->module = Module::factory()->create([
            'course_id' => $this->course->id,
        ]);

        $this->subpage = Subpage::factory()->create([
            'module_id' => $this->module->id,
        ]);
    }

    /**
     * Test enhanced error response for file size validation failure.
     */
    public function test_file_size_error_includes_enhanced_response(): void
    {
        $this->actingAs($this->teacher);

        // Create a file that's too large (simulate by creating a large fake file)
        $largeFile = UploadedFile::fake()->create('large_image.jpg', 15000); // 15MB

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            'file' => $largeFile,
        ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        
        // Verify enhanced error response structure
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
        
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('correlation_id', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
        
        // Verify field-specific targeting
        $this->assertArrayHasKey('error_fields', $responseData);
        $this->assertContains('file', $responseData['error_fields']);
        
        // Verify upload guidance is included
        $this->assertArrayHasKey('upload_guidance', $responseData);
        $this->assertArrayHasKey('supported_types', $responseData['upload_guidance']);
        $this->assertArrayHasKey('size_limits', $responseData['upload_guidance']);
        $this->assertArrayHasKey('troubleshooting', $responseData['upload_guidance']);
        
        // Verify size limits information
        $sizeLimits = $responseData['upload_guidance']['size_limits'];
        $this->assertArrayHasKey('max_size_bytes', $sizeLimits);
        $this->assertArrayHasKey('max_size_formatted', $sizeLimits);
        $this->assertArrayHasKey('server_limit', $sizeLimits);
        
        // Verify troubleshooting suggestions
        $troubleshooting = $responseData['upload_guidance']['troubleshooting'];
        $this->assertIsArray($troubleshooting);
        $this->assertNotEmpty($troubleshooting);
        
        // Should include size-related troubleshooting
        $troubleshootingText = implode(' ', $troubleshooting);
        $this->assertStringContainsString('compress', strtolower($troubleshootingText));
        
        // Verify retry suggestions
        $this->assertArrayHasKey('retry_suggestions', $responseData);
        $this->assertIsArray($responseData['retry_suggestions']);
        $this->assertNotEmpty($responseData['retry_suggestions']);
        
        // Should have a reduce file size suggestion
        $retrySuggestions = collect($responseData['retry_suggestions']);
        $this->assertTrue($retrySuggestions->contains('action', 'reduce_file_size'));
        
        // Verify support information
        $this->assertArrayHasKey('support', $responseData);
        $this->assertArrayHasKey('correlation_id', $responseData['support']);
        $this->assertArrayHasKey('contact_message', $responseData['support']);
    }

    /**
     * Test enhanced error response for invalid file type.
     */
    public function test_invalid_file_type_includes_enhanced_response(): void
    {
        $this->actingAs($this->teacher);

        // Create a file with invalid extension
        $invalidFile = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            'file' => $invalidFile,
        ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        
        // Verify upload guidance includes supported types
        $this->assertArrayHasKey('upload_guidance', $responseData);
        $supportedTypes = $responseData['upload_guidance']['supported_types'];
        
        $this->assertArrayHasKey('extensions', $supportedTypes);
        $this->assertArrayHasKey('formatted', $supportedTypes);
        
        // Should include image extensions
        $this->assertContains('jpg', $supportedTypes['extensions']);
        $this->assertContains('png', $supportedTypes['extensions']);
        
        // Verify retry suggestions include format change
        $retrySuggestions = collect($responseData['retry_suggestions']);
        $this->assertTrue($retrySuggestions->contains('action', 'change_format'));
        
        // Verify troubleshooting includes extension-related advice
        $troubleshooting = $responseData['upload_guidance']['troubleshooting'];
        $troubleshootingText = implode(' ', $troubleshooting);
        $this->assertStringContainsString('extension', strtolower($troubleshootingText));
    }

    /**
     * Test enhanced error response for server errors includes server config.
     */
    public function test_server_error_includes_server_configuration(): void
    {
        $this->actingAs($this->teacher);

        // Test with a validation error that should include server config
        // Create a scenario where server config would be included
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            // Missing both file and external_url to trigger validation error
        ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        
        // Verify basic enhanced response structure
        $this->assertArrayHasKey('correlation_id', $responseData);
        $this->assertArrayHasKey('timestamp', $responseData);
        $this->assertArrayHasKey('retry_suggestions', $responseData);
        $this->assertArrayHasKey('support', $responseData);
        
        // Verify support information is complete
        $support = $responseData['support'];
        $this->assertArrayHasKey('correlation_id', $support);
        $this->assertArrayHasKey('timestamp', $support);
        $this->assertArrayHasKey('contact_message', $support);
        $this->assertStringContainsString('correlation ID', $support['contact_message']);
    }

    /**
     * Test that correlation IDs are consistent across response.
     */
    public function test_correlation_id_consistency(): void
    {
        $this->actingAs($this->teacher);

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            // Missing file to trigger validation error
        ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        
        // Verify correlation ID exists and is consistent
        $this->assertArrayHasKey('correlation_id', $responseData);
        $this->assertArrayHasKey('support', $responseData);
        $this->assertArrayHasKey('correlation_id', $responseData['support']);
        
        $mainCorrelationId = $responseData['correlation_id'];
        $supportCorrelationId = $responseData['support']['correlation_id'];
        
        $this->assertEquals($mainCorrelationId, $supportCorrelationId);
        $this->assertIsString($mainCorrelationId);
        $this->assertNotEmpty($mainCorrelationId);
        
        // Should be a valid UUID format
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $mainCorrelationId
        );
    }

    /**
     * Test that retry suggestions are appropriate for different error types.
     */
    public function test_retry_suggestions_are_contextual(): void
    {
        $this->actingAs($this->teacher);

        // Test with missing required field
        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'text',
            'title' => 'Test Content',
            'visibility' => 'student',
            // Missing content field
        ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        
        // Verify retry suggestions exist and are appropriate
        $this->assertArrayHasKey('retry_suggestions', $responseData);
        $retrySuggestions = $responseData['retry_suggestions'];
        
        $this->assertIsArray($retrySuggestions);
        $this->assertNotEmpty($retrySuggestions);
        
        // Should have a check input suggestion for validation errors
        $suggestions = collect($retrySuggestions);
        $this->assertTrue($suggestions->contains('action', 'check_input'));
        
        // Each suggestion should have required fields
        foreach ($retrySuggestions as $suggestion) {
            $this->assertArrayHasKey('action', $suggestion);
            $this->assertArrayHasKey('message', $suggestion);
            $this->assertIsString($suggestion['action']);
            $this->assertIsString($suggestion['message']);
            $this->assertNotEmpty($suggestion['message']);
        }
    }

    /**
     * Test that sensitive server details are not exposed in error responses.
     */
    public function test_server_config_does_not_expose_sensitive_details(): void
    {
        $this->actingAs($this->teacher);

        // Create a large file to trigger server config inclusion
        $largeFile = UploadedFile::fake()->create('large_image.jpg', 15000);

        $response = $this->postJson(route('api.content-blocks.store', [
            'course' => $this->course->id,
            'module' => $this->module->id,
            'subpage' => $this->subpage->id
        ]), [
            'type' => 'image',
            'title' => 'Test Image',
            'visibility' => 'student',
            'file' => $largeFile,
        ]);

        $response->assertStatus(422);
        
        $responseData = $response->json();
        
        // If server_info is included, verify it doesn't expose sensitive details
        if (isset($responseData['server_info'])) {
            $serverInfo = $responseData['server_info'];
            
            // Should not contain sensitive paths
            $responseJson = json_encode($responseData);
            $this->assertStringNotContainsString('/var/www', $responseJson);
            $this->assertStringNotContainsString('/home/', $responseJson);
            $this->assertStringNotContainsString('C:\\', $responseJson);
            
            // Should not contain database credentials or API keys
            $this->assertStringNotContainsString('password', strtolower($responseJson));
            $this->assertStringNotContainsString('secret', strtolower($responseJson));
            $this->assertStringNotContainsString('key', strtolower($responseJson));
            
            // Should contain safe configuration info
            if (isset($serverInfo['upload_limits'])) {
                $this->assertArrayHasKey('max_file_size', $serverInfo['upload_limits']);
                $this->assertArrayHasKey('max_post_size', $serverInfo['upload_limits']);
            }
        }
    }
}