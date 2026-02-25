<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Course;
use App\Models\User;
use App\Models\Module;
use App\Services\CourseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class CourseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CourseService $courseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->courseService = app(CourseService::class);
        
        // Create roles for testing
        \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'Teacher']);
        \Spatie\Permission\Models\Role::create(['name' => 'Student']);
    }

    public function test_teacher_can_create_course()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $courseData = [
            'title' => 'Test Course',
            'description' => 'A test course description',
            'category' => 'Programming',
            'difficulty_level' => 'beginner',
        ];

        $course = $this->courseService->createCourse($courseData, $teacher);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('Test Course', $course->title);
        $this->assertEquals($teacher->id, $course->teacher_id);
        $this->assertFalse($course->is_published);
    }

    public function test_non_teacher_cannot_create_course()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $courseData = [
            'title' => 'Test Course',
            'description' => 'A test course description',
        ];

        $this->expectException(ValidationException::class);
        $this->courseService->createCourse($courseData, $student);
    }

    public function test_can_update_course()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $updateData = [
            'title' => 'Updated Course Title',
            'description' => 'Updated description',
        ];

        $updatedCourse = $this->courseService->updateCourse($course, $updateData);

        $this->assertEquals('Updated Course Title', $updatedCourse->title);
        $this->assertEquals('Updated description', $updatedCourse->description);
    }

    public function test_can_add_module_to_course()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $moduleData = [
            'title' => 'Test Module',
            'description' => 'A test module',
            'is_published' => true,
        ];

        $module = $this->courseService->addModule($course, $moduleData);

        $this->assertInstanceOf(Module::class, $module);
        $this->assertEquals('Test Module', $module->title);
        $this->assertEquals($course->id, $module->course_id);
        $this->assertEquals(1, $module->order_index);
    }

    public function test_can_publish_course_with_published_modules()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => false,
        ]);

        // Add a published module
        Module::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $this->courseService->publishCourse($course);

        $course->refresh();
        $this->assertTrue($course->is_published);
    }

    public function test_cannot_publish_course_without_published_modules()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => false,
        ]);

        // Add an unpublished module
        Module::factory()->create([
            'course_id' => $course->id,
            'is_published' => false,
        ]);

        $this->expectException(ValidationException::class);
        $this->courseService->publishCourse($course);
    }

    public function test_can_reorder_modules()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $module1 = Module::factory()->create(['course_id' => $course->id, 'order_index' => 1]);
        $module2 = Module::factory()->create(['course_id' => $course->id, 'order_index' => 2]);
        $module3 = Module::factory()->create(['course_id' => $course->id, 'order_index' => 3]);

        // Reorder: module3, module1, module2
        $newOrder = [$module3->id, $module1->id, $module2->id];
        
        $this->courseService->reorderModules($course, $newOrder);

        $module1->refresh();
        $module2->refresh();
        $module3->refresh();

        $this->assertEquals(2, $module1->order_index);
        $this->assertEquals(3, $module2->order_index);
        $this->assertEquals(1, $module3->order_index);
    }

    public function test_check_prerequisites_with_no_prerequisites()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');
        
        $course = Course::factory()->create(['prerequisites' => null]);

        $result = $this->courseService->checkPrerequisites($course, $student);

        $this->assertTrue($result);
    }

    public function test_check_prerequisites_with_completed_prerequisites()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');
        
        $prerequisiteCourse = Course::factory()->create();
        $course = Course::factory()->create(['prerequisites' => [$prerequisiteCourse->id]]);

        // Create completed enrollment for prerequisite
        $student->enrollments()->create([
            'course_id' => $prerequisiteCourse->id,
            'status' => 'completed',
        ]);

        $result = $this->courseService->checkPrerequisites($course, $student);

        $this->assertTrue($result);
    }

    public function test_check_prerequisites_with_incomplete_prerequisites()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');
        
        $prerequisiteCourse = Course::factory()->create();
        $course = Course::factory()->create(['prerequisites' => [$prerequisiteCourse->id]]);

        $result = $this->courseService->checkPrerequisites($course, $student);

        $this->assertFalse($result);
    }
}