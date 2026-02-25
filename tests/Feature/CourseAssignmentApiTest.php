<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class CourseAssignmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected User $student;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);

        // Create test users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');

        $this->student = User::factory()->create();
        $this->student->assignRole('Student');

        // Create test course
        $this->course = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
            'is_published' => true,
            'max_students' => 10,
        ]);
    }

    public function test_admin_can_get_available_students()
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/courses/{$this->course->id}/available-students");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'course' => ['id', 'title', 'max_students', 'current_enrollment_count'],
                    'available_students',
                    'total_count'
                ]
            ]);
    }

    public function test_teacher_can_get_available_students_for_own_course()
    {
        $response = $this->actingAs($this->teacher)
            ->getJson("/api/courses/{$this->course->id}/available-students");

        $response->assertStatus(200);
    }

    public function test_teacher_cannot_get_available_students_for_other_course()
    {
        $otherTeacher = User::factory()->create();
        $otherTeacher->assignRole('Teacher');
        
        $otherCourse = Course::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->teacher)
            ->getJson("/api/courses/{$otherCourse->id}/available-students");

        $response->assertStatus(403);
    }

    public function test_admin_can_assign_students_to_course()
    {
        $students = User::factory()->count(2)->create();
        foreach ($students as $student) {
            $student->assignRole('Student');
        }

        $response = $this->actingAs($this->admin)
            ->postJson("/api/courses/{$this->course->id}/assign-students", [
                'student_ids' => $students->pluck('id')->toArray(),
                'notes' => 'Test assignment'
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'successful',
                    'failed'
                ],
                'summary' => [
                    'successful_count',
                    'failed_count',
                    'total_count'
                ]
            ]);

        // Verify enrollments were created
        foreach ($students as $student) {
            $this->assertDatabaseHas('enrollments', [
                'user_id' => $student->id,
                'course_id' => $this->course->id,
                'assigned_by' => $this->admin->id,
                'assignment_notes' => 'Test assignment',
                'status' => 'active',
            ]);
        }
    }

    public function test_teacher_can_assign_students_to_own_course()
    {
        $response = $this->actingAs($this->teacher)
            ->postJson("/api/courses/{$this->course->id}/assign-students", [
                'student_ids' => [$this->student->id],
                'notes' => 'Teacher assignment'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assigned_by' => $this->teacher->id,
        ]);
    }

    public function test_student_cannot_assign_students()
    {
        $response = $this->actingAs($this->student)
            ->postJson("/api/courses/{$this->course->id}/assign-students", [
                'student_ids' => [$this->student->id]
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_remove_student_from_course()
    {
        // First assign the student
        $this->actingAs($this->admin)
            ->postJson("/api/courses/{$this->course->id}/assign-students", [
                'student_ids' => [$this->student->id]
            ]);

        // Then remove the student
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/courses/{$this->course->id}/students/{$this->student->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'dropped',
        ]);
    }

    public function test_can_get_enrolled_students()
    {
        // Assign a student first
        $this->actingAs($this->admin)
            ->postJson("/api/courses/{$this->course->id}/assign-students", [
                'student_ids' => [$this->student->id],
                'notes' => 'Test enrollment'
            ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/courses/{$this->course->id}/enrolled-students");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'course' => ['id', 'title', 'teacher_name'],
                    'enrolled_students',
                    'total_count'
                ]
            ]);

        $enrolledStudents = $response->json('data.enrolled_students');
        $this->assertCount(1, $enrolledStudents);
        $this->assertEquals($this->student->id, $enrolledStudents[0]['student']['id']);
        $this->assertTrue($enrolledStudents[0]['was_assigned']);
        $this->assertEquals('Test enrollment', $enrolledStudents[0]['assignment_notes']);
    }

    public function test_can_get_student_courses()
    {
        // Assign the student to the course
        $this->actingAs($this->admin)
            ->postJson("/api/courses/{$this->course->id}/assign-students", [
                'student_ids' => [$this->student->id],
                'notes' => 'Test assignment'
            ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/students/{$this->student->id}/courses");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'student' => ['id', 'name', 'email'],
                    'courses',
                    'total_count',
                    'active_count',
                    'completed_count'
                ]
            ]);

        $courses = $response->json('data.courses');
        $this->assertCount(1, $courses);
        $this->assertEquals($this->course->id, $courses[0]['course']['id']);
        $this->assertTrue($courses[0]['was_assigned']);
        $this->assertEquals('Test assignment', $courses[0]['assignment_notes']);
    }

    public function test_validation_errors_for_invalid_assignment()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/courses/{$this->course->id}/assign-students", [
                'student_ids' => [], // Empty array should fail validation
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['student_ids']);
    }
}