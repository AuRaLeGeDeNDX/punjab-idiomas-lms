<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use Spatie\Permission\Models\Role;

class ModuleTrashEmptyingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        
        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
        
        // Create teacher user
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        // Create course assigned to teacher
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
    }

    public function test_admin_can_empty_module_trash()
    {
        $modules = Module::factory()->count(3)->create(['course_id' => $this->course->id]);
        $modules->each->delete();

        $this->assertEquals(3, Module::onlyTrashed()->where('course_id', $this->course->id)->count());

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('teacher.modules.empty-trash', $this->course));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals(0, Module::onlyTrashed()->where('course_id', $this->course->id)->count());
    }

    public function test_teacher_can_empty_module_trash_for_their_course()
    {
        $modules = Module::factory()->count(3)->create(['course_id' => $this->course->id]);
        $modules->each->delete();

        $this->assertEquals(3, Module::onlyTrashed()->where('course_id', $this->course->id)->count());

        $response = $this->actingAs($this->teacher)
            ->deleteJson(route('teacher.modules.empty-trash', $this->course));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals(0, Module::onlyTrashed()->where('course_id', $this->course->id)->count());
    }

    public function test_teacher_cannot_empty_trash_for_other_teacher_course()
    {
        $otherTeacher = User::factory()->create();
        $otherTeacher->assignRole('Teacher');
        $otherCourse = Course::factory()->create(['teacher_id' => $otherTeacher->id]);
        
        $modules = Module::factory()->count(1)->create(['course_id' => $otherCourse->id]);
        $modules->each->delete();

        $response = $this->actingAs($this->teacher)
            ->deleteJson(route('teacher.modules.empty-trash', $otherCourse));

        $response->assertStatus(403);
    }

    public function test_admin_can_restore_module()
    {
        $module = Module::factory()->create(['course_id' => $this->course->id]);
        $module->delete();

        $this->assertSoftDeleted('modules', ['id' => $module->id]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('teacher.modules.restore', [$this->course, $module->id]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotSoftDeleted('modules', ['id' => $module->id]);
    }

    public function test_restore_diagnostic()
    {
        $module = Module::factory()->create(['course_id' => $this->course->id]);
        $module->delete();

        $response = $this->actingAs($this->teacher)
            ->postJson(route('teacher.modules.restore', [$this->course, $module->id]));

        dump($response->status());
        dump($response->getContent());
        $response->assertStatus(200);
    }
}
