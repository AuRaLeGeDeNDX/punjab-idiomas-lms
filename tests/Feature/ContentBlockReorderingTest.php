<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBlockReorderingTest extends TestCase
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

    public function test_teacher_can_reorder_content_blocks_with_simple_array()
    {
        // Create content blocks
        $content1 = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'order_index' => 1,
            'title' => 'First Block',
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);
        $content2 = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'order_index' => 2,
            'title' => 'Second Block',
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);
        $content3 = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'order_index' => 3,
            'title' => 'Third Block',
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);

        // Reorder: move third block to first position
        $newOrder = [$content3->id, $content1->id, $content2->id];

        $response = $this->actingAs($this->teacher)
            ->withSession(['_token' => 'test-token'])
            ->postJson(route('api.content-blocks.reorder', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'content_ids' => $newOrder,
                '_token' => 'test-token'
            ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Content blocks reordered successfully.'
                ]);

        // Verify new order in database
        $this->assertEquals(1, $content3->fresh()->order_index);
        $this->assertEquals(2, $content1->fresh()->order_index);
        $this->assertEquals(3, $content2->fresh()->order_index);
    }

    public function test_teacher_can_reorder_content_blocks_with_object_array()
    {
        // Create content blocks
        $content1 = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'order_index' => 1,
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);
        $content2 = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'order_index' => 2,
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->withSession(['_token' => 'test-token'])
            ->postJson(route('api.content-blocks.reorder', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'blocks' => [
                    ['id' => $content2->id, 'order' => 1],
                    ['id' => $content1->id, 'order' => 2]
                ],
                '_token' => 'test-token'
            ]);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        // Verify new order
        $this->assertEquals(1, $content2->fresh()->order_index);
        $this->assertEquals(2, $content1->fresh()->order_index);
    }

    public function test_student_cannot_reorder_content_blocks()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $content = Content::factory()->create([
            'subpage_id' => $this->subpage->id,
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($student)
            ->withSession(['_token' => 'test-token'])
            ->postJson(route('api.content-blocks.reorder', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'content_ids' => [$content->id],
                '_token' => 'test-token'
            ]);

        $response->assertStatus(403);
    }

    public function test_reordering_validates_content_belongs_to_subpage()
    {
        $otherSubpage = Subpage::factory()->create(['module_id' => $this->module->id]);
        $otherContent = Content::factory()->create([
            'subpage_id' => $otherSubpage->id,
            'created_by' => $this->teacher->id,
            'updated_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->withSession(['_token' => 'test-token'])
            ->postJson(route('api.content-blocks.reorder', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                'content_ids' => [$otherContent->id],
                '_token' => 'test-token'
            ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Some content blocks do not belong to this subpage.'
                ]);
    }

    public function test_reordering_requires_valid_payload()
    {
        $response = $this->actingAs($this->teacher)
            ->withSession(['_token' => 'test-token'])
            ->postJson(route('api.content-blocks.reorder', [
                'course' => $this->course->id,
                'module' => $this->module->id,
                'subpage' => $this->subpage->id
            ]), [
                '_token' => 'test-token'
            ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Either content_ids or blocks array is required.'
                ]);
    }
}