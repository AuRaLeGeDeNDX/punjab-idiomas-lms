<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBlockJsonTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected Course $course;
    protected Module $module;
    protected Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');

        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
    }

    public function test_can_create_text_block_with_editorjs_json()
    {
        $editorJsContent = json_encode([
            'time' => time() * 1000,
            'blocks' => [
                [
                    'id' => 'test-id-1',
                    'type' => 'header',
                    'data' => [
                        'text' => 'Test Header',
                        'level' => 2
                    ]
                ],
                [
                    'id' => 'test-id-2',
                    'type' => 'paragraph',
                    'data' => [
                        'text' => 'Test paragraph content'
                    ]
                ]
            ],
            'version' => '2.28.0'
        ]);

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'text',
                'title' => 'Test Content Block',
                'content' => $editorJsContent,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Content block created successfully.'
                ]);

        // Verify content was stored
        $this->assertDatabaseHas('contents', [
            'subpage_id' => $this->subpage->id,
            'type' => 'text',
            'title' => 'Test Content Block'
        ]);
    }

    public function test_api_response_includes_content_format_for_editorjs()
    {
        $editorJsContent = json_encode([
            'time' => time() * 1000,
            'blocks' => [
                [
                    'id' => 'test-id',
                    'type' => 'paragraph',
                    'data' => [
                        'text' => 'Test content'
                    ]
                ]
            ],
            'version' => '2.28.0'
        ]);

        $content = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'type' => 'text',
            'content' => $editorJsContent,
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->getJson(route('api.content-blocks.show', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id,
                'content' => $content->id
            ]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'type',
                        'content',
                        'content_format',
                        'editable_content',
                        'rendered_content'
                    ]
                ])
                ->assertJson([
                    'data' => [
                        'content_format' => 'editorjs'
                    ]
                ]);
    }

    public function test_api_response_includes_content_format_for_html()
    {
        $htmlContent = '<p>This is legacy HTML content</p>';

        $content = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'type' => 'text',
            'content' => $htmlContent,
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->getJson(route('api.content-blocks.show', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id,
                'content' => $content->id
            ]));

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'content_format' => 'html'
                    ]
                ]);
    }

    public function test_validation_rejects_invalid_editorjs_json()
    {
        // Invalid JSON - blocks is not an array
        $invalidJson = json_encode([
            'blocks' => 'not-an-array'
        ]);

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'text',
                'title' => 'Test Content Block',
                'content' => $invalidJson,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['content']);
    }

    public function test_validation_rejects_editorjs_block_without_type()
    {
        // Invalid JSON - block missing 'type' field
        $invalidJson = json_encode([
            'blocks' => [
                [
                    'id' => 'test-id',
                    'data' => [
                        'text' => 'Test'
                    ]
                ]
            ]
        ]);

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'text',
                'title' => 'Test Content Block',
                'content' => $invalidJson,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['content']);
    }

    public function test_validation_accepts_legacy_html_content()
    {
        $htmlContent = '<p>This is <strong>legacy</strong> HTML content</p>';

        $response = $this->actingAs($this->teacher)
            ->postJson(route('api.content-blocks.store', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'type' => 'text',
                'title' => 'Test Content Block',
                'content' => $htmlContent,
                'visibility' => 'student',
                'section' => 'main_content'
            ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_can_render_list_with_mixed_item_types()
    {
        // Test list with mixed item types (strings, arrays, objects)
        $editorJsContent = json_encode([
            'time' => time() * 1000,
            'blocks' => [
                [
                    'id' => 'test-list',
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [
                            'Simple string item',
                            ['Array', 'item', 'with', 'parts'],
                            (object)['text' => 'Object with text property'],
                            (object)['content' => 'Object with content property'],
                            '', // Empty string should be skipped
                            null, // Null should be skipped
                        ]
                    ]
                ]
            ],
            'version' => '2.28.0'
        ]);

        $content = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'type' => 'text',
            'content' => $editorJsContent,
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->getJson(route('api.content-blocks.show', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id,
                'content' => $content->id
            ]));

        $response->assertStatus(200);
        
        // Verify the rendered content contains the list items
        $renderedContent = $response->json('data.rendered_content');
        $this->assertStringContainsString('<ul>', $renderedContent);
        $this->assertStringContainsString('Simple string item', $renderedContent);
        $this->assertStringContainsString('Array item with parts', $renderedContent);
        $this->assertStringContainsString('Object with text property', $renderedContent);
        $this->assertStringContainsString('Object with content property', $renderedContent);
        $this->assertStringContainsString('</ul>', $renderedContent);
    }
}
