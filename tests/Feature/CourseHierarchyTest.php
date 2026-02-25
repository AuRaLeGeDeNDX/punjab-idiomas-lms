<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_teacher_can_get_course_hierarchy()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($teacher)
            ->getJson("/api/courses/{$course->id}/hierarchy");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'course',
                    'modules' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'order_index',
                            'is_published',
                            'subpages_count',
                            'subpages' => [
                                '*' => [
                                    'id',
                                    'title',
                                    'description',
                                    'order_index',
                                    'is_active'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_teacher_can_create_module()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $moduleData = [
            'title' => 'Test Module',
            'description' => 'Test module description'
        ];

        $response = $this->actingAs($teacher)
            ->postJson("/api/courses/{$course->id}/modules", $moduleData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Module created successfully'
            ]);

        $this->assertDatabaseHas('modules', [
            'course_id' => $course->id,
            'title' => 'Test Module',
            'description' => 'Test module description'
        ]);
    }

    public function test_admin_can_access_any_course_hierarchy()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($admin)
            ->getJson("/api/courses/{$course->id}/hierarchy");

        $response->assertStatus(200);
    }
}