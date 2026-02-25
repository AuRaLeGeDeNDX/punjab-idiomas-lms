<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class EnrollmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EnrollmentService $enrollmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enrollmentService = new EnrollmentService();
        
        // Create roles for testing
        \Spatie\Permission\Models\Role::create(['name' => 'Student']);
        \Spatie\Permission\Models\Role::create(['name' => 'Teacher']);
        \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
    }

    public function test_student_cannot_enroll_themselves()
    {
        // Create a student
        $student = User::factory()->create();
        $student->assignRole('Student');

        // Create a teacher and published course
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
            'enrollment_start_date' => now()->subDay(),
            'enrollment_end_date' => now()->addMonth(),
        ]);

        // Test that student cannot enroll themselves (no assignedBy parameter)
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Students cannot enroll themselves');
        $this->enrollmentService->enrollStudent($course, $student);
    }

    public function test_admin_can_assign_student_to_course()
    {
        // Create users
        $student = User::factory()->create();
        $student->assignRole('Student');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
            'enrollment_start_date' => now()->subDay(),
            'enrollment_end_date' => now()->addMonth(),
        ]);

        // Test that admin can assign student
        $enrollment = $this->enrollmentService->enrollStudent($course, $student, $admin);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals($student->id, $enrollment->user_id);
        $this->assertEquals($course->id, $enrollment->course_id);
        $this->assertEquals('active', $enrollment->status);
        $this->assertEquals($admin->id, $enrollment->assigned_by);
        $this->assertNotNull($enrollment->assigned_at);
    }

    public function test_teacher_can_assign_student_to_own_course()
    {
        // Create users
        $student = User::factory()->create();
        $student->assignRole('Student');

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
            'enrollment_start_date' => now()->subDay(),
            'enrollment_end_date' => now()->addMonth(),
        ]);

        // Test that teacher can assign student to their own course
        $enrollment = $this->enrollmentService->enrollStudent($course, $student, $teacher);

        $this->assertInstanceOf(Enrollment::class, $enrollment);
        $this->assertEquals($student->id, $enrollment->user_id);
        $this->assertEquals($course->id, $enrollment->course_id);
        $this->assertEquals('active', $enrollment->status);
        $this->assertEquals($teacher->id, $enrollment->assigned_by);
    }

    public function test_teacher_cannot_assign_student_to_other_course()
    {
        // Create users
        $student = User::factory()->create();
        $student->assignRole('Student');

        $teacher1 = User::factory()->create();
        $teacher1->assignRole('Teacher');

        $teacher2 = User::factory()->create();
        $teacher2->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher1->id,
            'is_published' => true,
        ]);

        // Test that teacher2 cannot assign student to teacher1's course
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Teachers can only assign students to their own courses');
        $this->enrollmentService->enrollStudent($course, $student, $teacher2);
    }

    public function test_cannot_enroll_in_unpublished_course()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => false,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot enroll in unpublished courses');
        $this->enrollmentService->enrollStudent($course, $student, $admin);
    }

    public function test_cannot_enroll_twice_in_same_course()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
        ]);

        // First enrollment should succeed
        $this->enrollmentService->enrollStudent($course, $student, $admin);

        // Second enrollment should fail
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Student is already enrolled in this course');
        $this->enrollmentService->enrollStudent($course, $student, $admin);
    }

    public function test_can_track_progress()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
        ]);

        $enrollment = $this->enrollmentService->enrollStudent($course, $student, $admin);
        $progress = $this->enrollmentService->trackProgress($enrollment);

        $this->assertIsArray($progress);
        $this->assertArrayHasKey('overall_progress', $progress);
        $this->assertArrayHasKey('modules_completed', $progress);
        $this->assertArrayHasKey('total_modules', $progress);
        $this->assertArrayHasKey('module_progress', $progress);
    }

    public function test_can_get_learning_record()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $learningRecord = $this->enrollmentService->getLearningRecord($student);

        $this->assertIsArray($learningRecord);
        $this->assertArrayHasKey('student_id', $learningRecord);
        $this->assertArrayHasKey('total_courses', $learningRecord);
        $this->assertArrayHasKey('completed_courses', $learningRecord);
        $this->assertArrayHasKey('active_courses', $learningRecord);
        $this->assertArrayHasKey('course_records', $learningRecord);
        $this->assertEquals($student->id, $learningRecord['student_id']);
    }

    public function test_student_cannot_unenroll_themselves()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
        ]);

        // First enroll the student
        $this->enrollmentService->enrollStudent($course, $student, $admin);

        // Test that student cannot unenroll themselves
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Students cannot unenroll themselves');
        $this->enrollmentService->unenrollStudent($course, $student);
    }

    public function test_admin_can_remove_student_from_course()
    {
        $student = User::factory()->create();
        $student->assignRole('Student');

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
            'is_published' => true,
        ]);

        // First enroll the student
        $this->enrollmentService->enrollStudent($course, $student, $admin);

        // Test that admin can remove student
        $this->enrollmentService->unenrollStudent($course, $student, $admin);

        // Verify enrollment status changed to dropped
        $enrollment = $student->enrollments()->where('course_id', $course->id)->first();
        $this->assertEquals('dropped', $enrollment->status);
    }
}