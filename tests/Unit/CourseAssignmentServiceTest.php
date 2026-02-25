<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CourseAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CourseAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CourseAssignmentService $assignmentService;
    protected User $admin;
    protected User $teacher;
    protected User $student;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->assignmentService = new CourseAssignmentService();

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

    public function test_admin_can_assign_student_to_course()
    {
        $enrollment = $this->assignmentService->assignStudentToCourse(
            $this->student,
            $this->course,
            $this->admin,
            'Test assignment by admin'
        );

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals($this->student->id, $enrollment->user_id);
        $this->assertEquals($this->course->id, $enrollment->course_id);
        $this->assertEquals($this->admin->id, $enrollment->assigned_by);
        $this->assertEquals('Test assignment by admin', $enrollment->assignment_notes);
        $this->assertEquals('active', $enrollment->status);
        $this->assertTrue($enrollment->wasAssigned());
    }

    public function test_teacher_can_assign_student_to_own_course()
    {
        $enrollment = $this->assignmentService->assignStudentToCourse(
            $this->student,
            $this->course,
            $this->teacher,
            'Test assignment by teacher'
        );

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals($this->teacher->id, $enrollment->assigned_by);
        $this->assertTrue($enrollment->wasAssigned());
    }

    public function test_teacher_cannot_assign_student_to_other_course()
    {
        $otherTeacher = User::factory()->create();
        $otherTeacher->assignRole('Teacher');
        
        $otherCourse = Course::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'is_published' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Teachers can only assign students to courses they are assigned to teach.');

        $this->assignmentService->assignStudentToCourse(
            $this->student,
            $otherCourse,
            $this->teacher
        );
    }

    public function test_student_cannot_assign_other_students()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only Admins and Teachers can assign students to courses.');

        $otherStudent = User::factory()->create();
        $otherStudent->assignRole('Student');

        $this->assignmentService->assignStudentToCourse(
            $otherStudent,
            $this->course,
            $this->student
        );
    }

    public function test_cannot_assign_duplicate_enrollment()
    {
        // First assignment should succeed
        $this->assignmentService->assignStudentToCourse(
            $this->student,
            $this->course,
            $this->admin
        );

        // Second assignment should fail
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Student is already enrolled in this course.');

        $this->assignmentService->assignStudentToCourse(
            $this->student,
            $this->course,
            $this->admin
        );
    }

    public function test_can_remove_student_from_course()
    {
        // First assign the student
        $this->assignmentService->assignStudentToCourse(
            $this->student,
            $this->course,
            $this->admin
        );

        // Verify enrollment exists
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);

        // Remove the student
        $this->assignmentService->removeStudentFromCourse($this->student, $this->course);

        // Verify enrollment is marked as dropped
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'dropped',
        ]);
    }

    public function test_bulk_assign_students()
    {
        $students = User::factory()->count(3)->create();
        foreach ($students as $student) {
            $student->assignRole('Student');
        }

        $studentIds = $students->pluck('id')->toArray();

        $results = $this->assignmentService->bulkAssignStudents(
            $studentIds,
            $this->course,
            $this->admin,
            'Bulk assignment test'
        );

        $this->assertCount(3, $results['successful']);
        $this->assertCount(0, $results['failed']);

        // Verify all enrollments were created
        foreach ($studentIds as $studentId) {
            $this->assertDatabaseHas('enrollments', [
                'user_id' => $studentId,
                'course_id' => $this->course->id,
                'assigned_by' => $this->admin->id,
                'assignment_notes' => 'Bulk assignment test',
                'status' => 'active',
            ]);
        }
    }

    public function test_get_available_students()
    {
        // Create additional students
        $availableStudents = User::factory()->count(3)->create();
        foreach ($availableStudents as $student) {
            $student->assignRole('Student');
        }

        // Assign one student to the course
        $this->assignmentService->assignStudentToCourse(
            $this->student,
            $this->course,
            $this->admin
        );

        // Clear cache to ensure fresh data
        \Illuminate\Support\Facades\Cache::flush();

        $available = $this->assignmentService->getAvailableStudents($this->course);

        // Should return 3 available students (not including the already enrolled one)
        $this->assertCount(3, $available);
        $this->assertFalse($available->contains('id', $this->student->id));
    }

    public function test_get_enrolled_students()
    {
        // Assign students to the course
        $this->assignmentService->assignStudentToCourse(
            $this->student,
            $this->course,
            $this->admin,
            'Test enrollment'
        );

        $enrolled = $this->assignmentService->getEnrolledStudents($this->course);

        $this->assertCount(1, $enrolled);
        $enrolledStudent = $enrolled->first();
        $this->assertEquals($this->student->id, $enrolledStudent['student']['id']);
        $this->assertEquals($this->admin->id, $enrolledStudent['assignor']['id']);
        $this->assertTrue($enrolledStudent['was_assigned']);
        $this->assertEquals('Test enrollment', $enrolledStudent['assignment_notes']);
    }
}