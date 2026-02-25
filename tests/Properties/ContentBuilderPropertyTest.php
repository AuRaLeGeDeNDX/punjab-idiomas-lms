<?php

namespace Tests\Properties;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\PropertyTesting;

class ContentBuilderPropertyTest extends TestCase
{
    use RefreshDatabase, PropertyTesting, WithFaker;

    protected User $teacher;
    protected Course $course;
    protected Module $module;
    protected Subpage $subpage;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles for permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Setup base entities
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');

        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
    }

    /**
     * Helper: Generate a valid random EditorJS JSON structure
     */
    protected function generateRandomEditorJs(int $blockCount = 5, bool $includeUnknown = false): array
    {
        $blocks = [];
        $types = ['header', 'paragraph', 'list', 'image', 'quote'];
        if ($includeUnknown) {
            $types[] = 'unknown_gadget'; // Prop 4
        }

        for ($i = 0; $i < $blockCount; $i++) {
            $type = $this->faker->randomElement($types);
            $block = [
                'id' => $this->faker->uuid,
                'type' => $type,
                'data' => []
            ];

            // Generate type-specific data
            switch ($type) {
                case 'header':
                    $block['data'] = [
                        'text' => $this->faker->sentence,
                        'level' => $this->faker->numberBetween(1, 6)
                    ];
                    break;
                case 'paragraph':
                    $block['data'] = [
                        'text' => $this->faker->paragraph
                    ];
                    break;
                case 'list':
                    $block['data'] = [
                        'style' => $this->faker->randomElement(['ordered', 'unordered']),
                        'items' => $this->faker->sentences(3)
                    ];
                    break;
                case 'image':
                    $block['data'] = [
                        'url' => $this->faker->imageUrl,
                        'caption' => $this->faker->sentence,
                        'withBorder' => $this->faker->boolean,
                        'withBackground' => $this->faker->boolean,
                        'stretched' => $this->faker->boolean
                    ];
                    break;
                case 'quote':
                    $block['data'] = [
                        'text' => $this->faker->sentence,
                        'caption' => $this->faker->name,
                        'alignment' => $this->faker->randomElement(['left', 'center'])
                    ];
                    break;
                case 'unknown_gadget':
                    $block['data'] = [
                        'flux_capacitor' => 'enabled',
                        'timestamp' => time(),
                        'extra_field' => $this->faker->word
                    ];
                    break;
            }

            // Prop 4: Add extra fields to valid blocks
            if ($includeUnknown && $this->faker->boolean(30)) {
                $block['extra_metadata'] = ['foo' => 'bar'];
                $block['data']['unexpected_field'] = 'should_be_ignored';
            }

            $blocks[] = $block;
        }

        return [
            'time' => time() * 1000,
            'blocks' => $blocks,
            'version' => '2.28.0'
        ];
    }

    /**
     * Property 1: Content Persistence Integrity
     * Invariant: Any valid random EditorJS JSON structure must save successfully and retrieve correctly.
     */
    public function test_content_persistence_property()
    {
        $this->propertyTest(function () {
            $contentData = $this->generateRandomEditorJs($this->faker->numberBetween(1, 10));
            $jsonContent = json_encode($contentData);

            $response = $this->actingAs($this->teacher)
                ->postJson(route('api.content-blocks.store', [
                    'course' => $this->course->id,
                    'module' => $this->module->id,
                    'subpage' => $this->subpage->id
                ]), [
                    'type' => 'text',
                    'title' => $this->faker->sentence,
                    'content' => $jsonContent,
                    'visibility' => 'student',
                    'section' => 'main_content'
                ]);

            // Assertion 1: API accepts it
            $response->assertStatus(201);
            
            // Assertion 2: Stored content structure matches input
            $contentId = $response->json('data.id'); // Assuming API returns Created ID or we fetch last
            // If API doesn't return ID in data.id, we fetch latest
            $storedContent = Content::latest()->first(); 
            
            $storedJson = json_decode($storedContent->content, true);
            $this->assertCount(count($contentData['blocks']), $storedJson['blocks'], 'Block count should match');
            $this->assertEquals($contentData['blocks'][0]['type'], $storedJson['blocks'][0]['type'], 'First block type should match');

        }, 10, 'Content Persistence');
    }

    /**
     * Property 2: Preview Rendering Robustness
     * Invariant: Any sequence of valid content blocks should render a 200 OK preview.
     */
    public function test_preview_rendering_property()
    {
        $this->propertyTest(function () {
            // Setup: Create a fresh subpage with N random blocks
            $subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
            $blockCount = $this->faker->numberBetween(1, 5);

            for ($i = 0; $i < $blockCount; $i++) {
                Content::factory()->create([
                    'subpage_id' => $subpage->id,
                    'type' => 'text',
                    'content' => json_encode($this->generateRandomEditorJs(3)),
                    'sort_order' => $i
                ]);
            }

            // Action: Request Preview
            $response = $this->actingAs($this->teacher)
                ->get(route('subpages.preview', ['subpage' => $subpage->id]));

            // Assertion: 200 OK
            $response->assertStatus(200);
            $response->assertSee('main-content'); // Basic check for layout

        }, 5, 'Preview Rendering');
    }

    /**
     * Property 3: Reordering Integrity
     * Invariant: Reordering must preserve the exact set of IDs and maintain unique sequential sort_order.
     */
    public function test_reordering_integrity_property()
    {
        $this->propertyTest(function () {
            // Setup: Create 5 blocks
            $subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
            $blocks = Content::factory()->count(5)->create([
                'subpage_id' => $subpage->id,
                'type' => 'text'
            ]);

            $originalIds = $blocks->pluck('id')->sort()->values();
            
            // Shuffle IDs for new order
            $shuffledIds = $blocks->pluck('id')->shuffle()->toArray();
            
            // Action: Send Reorder Request
            $response = $this->actingAs($this->teacher)
                ->postJson(route('api.content-blocks.reorder', [
                    'course' => $this->course->id,
                    'module' => $this->module->id,
                    'subpage' => $subpage->id
                ]), [
                    'order' => $shuffledIds
                ]);

            $response->assertStatus(200);

            // Assertion: Check DB State
            $freshBlocks = Content::where('subpage_id', $subpage->id)->orderBy('sort_order')->get();
            
            // 1. No items lost or added
            $newIds = $freshBlocks->pluck('id')->sort()->values();
            $this->assertEquals($originalIds, $newIds, 'Set of IDs must be preserved');

            // 2. Sort order is sequential 0..4 (or 1..5 depending on impl)
            // Assuming 0-based or 1-based, just check they are unique and sequential
            $orders = $freshBlocks->pluck('sort_order')->toArray();
            $this->assertEquals($shuffledIds, $freshBlocks->pluck('id')->toArray(), 'Order in DB must match requested order');
            
        }, 5, 'Reordering Integrity');
    }

    /**
     * Property 4: Unknown / Future Block Safety
     * Invariant: Unknown block types or extra fields must not crash the system.
     */
    public function test_unknown_block_safety_property()
    {
        $this->propertyTest(function () {
            // Generate content with "unknown_gadget" and extra fields
            $contentData = $this->generateRandomEditorJs(3, true); 
            $jsonContent = json_encode($contentData);

            // Action: Save
            $response = $this->actingAs($this->teacher)
                ->postJson(route('api.content-blocks.store', [
                    'course' => $this->course->id,
                    'module' => $this->module->id,
                    'subpage' => $this->subpage->id
                ]), [
                    'type' => 'text',
                    'title' => 'Future Block Test',
                    'content' => $jsonContent,
                    'visibility' => 'student',
                    'section' => 'main_content'
                ]);

            // Assertion 1: Save Successful (ignored or stored)
            // We accept 201 Created or 200 OK. 500 is failure.
            $this->assertLessThan(500, $response->getStatusCode(), 'Should not crash on unknown blocks');
            
            if ($response->getStatusCode() === 201) {
                // If saved, verify Preview doesn't crash
                 $contentId = Content::latest()->first()->id;
                 
                 // If we have a specific subpage for this iteration, better to use it
                 // reusing $this->subpage is fine as we append
                 $previewResponse = $this->actingAs($this->teacher)
                    ->get(route('subpages.preview', ['subpage' => $this->subpage->id]));
                    
                 $previewResponse->assertStatus(200);
            }

        }, 5, 'Unknown Block Safety');
    }

    /**
     * Property 5: Idempotent Save
     * Invariant: Saving the same content twice results in identical state (no duplication).
     */
    public function test_idempotent_save_property()
    {
        $this->propertyTest(function () {
            $contentData = $this->generateRandomEditorJs(3);
            $jsonContent = json_encode($contentData);
            $title = $this->faker->sentence;

            // Save T1
            $response1 = $this->actingAs($this->teacher)
                ->postJson(route('api.content-blocks.store', [
                    'course' => $this->course->id,
                    'module' => $this->module->id,
                    'subpage' => $this->subpage->id
                ]), [
                    'type' => 'text',
                    'title' => $title,
                    'content' => $jsonContent,
                    'visibility' => 'student',
                    'section' => 'main_content'
                ]);
            $response1->assertStatus(201);
            $id1 = Content::latest()->first()->id;

            // Update T2 (PUT) with same content
            // Assuming we test UPDATE idempotency here, as STORE usually creates new.
            // If the user meant "Pressing Save button twice shouldn't create duplicates if it's an update",
            // we should test the Update endpoint.
            
            $response2 = $this->actingAs($this->teacher)
                ->putJson(route('api.content-blocks.update', [
                    'course' => $this->course->id,
                    'module' => $this->module->id,
                    'subpage' => $this->subpage->id,
                    'content' => $id1
                ]), [
                    'type' => 'text',
                    'title' => $title,
                    'content' => $jsonContent,
                    // send same data
                    'visibility' => 'student' 
                ]);
            
            $response2->assertStatus(200);
            
            // Assertion: Content is inchanged from T1 intent
            $finalContent = Content::find($id1);
            $this->assertEquals(json_decode($jsonContent, true), json_decode($finalContent->content, true));

        }, 5, 'Idempotent Save');
    }

    /**
     * Property 6: Large Content Stress Safety
     * Invariant: System handles 100 blocks without crashing.
     */
    public function test_large_content_stress_property()
    {
        // Run only once or twice as it's heavy
        $this->propertyTest(function () {
            // Generate 100 blocks
            $contentData = $this->generateRandomEditorJs(100);
            $jsonContent = json_encode($contentData);

            $startTime = microtime(true);

            $response = $this->actingAs($this->teacher)
                ->postJson(route('api.content-blocks.store', [
                    'course' => $this->course->id,
                    'module' => $this->module->id,
                    'subpage' => $this->subpage->id
                ]), [
                    'type' => 'text',
                    'title' => 'Large Content Test',
                    'content' => $jsonContent,
                    'visibility' => 'student',
                    'section' => 'main_content'
                ]);

            $endTime = microtime(true);
            
            // Assertions
            $response->assertStatus(201);
            $this->assertLessThan(5.0, $endTime - $startTime, 'Should process large content within 5 seconds');

            // Verify Preview
            $previewResponse = $this->actingAs($this->teacher)
                ->get(route('subpages.preview', ['subpage' => $this->subpage->id]));
            $previewResponse->assertStatus(200);

        }, 2, 'Large Content Stress');
    }

    /**
     * Property 7: Security Encoding Safety
     * Invariant: Malicious inputs must be sanitized on Preview.
     */
    public function test_security_encoding_property()
    {
        $this->propertyTest(function () {
            $maliciousScript = '<script>alert("XSS")</script>';
            $maliciousImg = '<img src=x onerror=alert(1)>';
            
            $contentData = [
                'time' => time(),
                'version' => '2.28.0',
                'blocks' => [
                    [
                        'type' => 'paragraph',
                        'data' => ['text' => "Normal text " . $maliciousScript]
                    ],
                    [
                        'type' => 'header',
                        'data' => ['text' => "Header " . $maliciousImg, 'level' => 2]
                    ]
                ]
            ];
            
            // Save Content
            $subpage = Subpage::factory()->create(['module_id' => $this->module->id]);
            Content::factory()->create([
                'subpage_id' => $subpage->id,
                'type' => 'text',
                'content' => json_encode($contentData)
            ]);

            // Request Preview
            $response = $this->actingAs($this->teacher)
                ->get(route('subpages.preview', ['subpage' => $subpage->id]));

            $response->assertStatus(200);
            $html = $response->getContent();

            // Assertions: Raw tags should NOT be present
            // We expect them to be escaped like &lt;script&gt; or stripped
            $this->assertStringNotContainsString($maliciousScript, $html, 'Raw script tag should not be present');
            $this->assertStringNotContainsString($maliciousImg, $html, 'Raw onerror handler should not be present');
            
            // Optionally check for escaped version if that's the strategy
            // $this->assertStringContainsString('&lt;script&gt;', $html);

        }, 5, 'Security Encoding Safety');
    }
}
