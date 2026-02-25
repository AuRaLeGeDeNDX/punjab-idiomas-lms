<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\Grade;
use App\Models\Enrollment;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

/**
 * Unit Tests for Analytics Calculation Accuracy
 * 
 * Feature: complete-assignment-system
 * Task 3.13: Write unit tests for calculation accuracy
 * 
 * These tests verify specific calculation scenarios and edge cases.
 */
class AnalyticsCalculationAccuracyTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
        
        // Create roles
        Role::firstOrCreate(['name' => 'teacher']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    /** @test */
    public function completion_rate_is_zero_when_no_assignments_exist(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

        $this->assertEquals(0.0, $data['overall_completion_rate']);
    }

    /** @test */
    public function completion_rate_is_zero_when_no_students_enrolled(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

        $this->assertEquals(0.0, $data['overall_completion_rate']);
    }

    /** @test */
    public function completion_rate_is_100_when_all_students_submitted_all_assignments(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        // Create 3 students
        $students = [];
        for ($i = 0; $i < 3; $i++) {
            $student = $this->createUserWithRole('student');
            $students[] = $student;
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
        }

        // Create 2 assignments
        $assignments = Assignment::factory()->count(2)->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // All students submit all assignments
        foreach ($assignments as $assignment) {
            foreach ($students as $student) {
                Submission::create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id,
                    'content' => 'Test submission',
                    'submitted_at' => now()
                ]);
            }
        }

        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

        $this->assertEquals(100.0, $data['overall_completion_rate']);
    }

    /** @test */
    public function average_score_is_zero_when_no_grades_exist(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

        $this->assertEquals(0.0, $data['average_score']);
    }

    /** @test */
    public function average_score_is_calculated_correctly_with_multiple_grades(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Create grades with known scores: 80, 90, 100
        // Expected average: (80 + 90 + 100) / 3 = 90
        $scores = [80, 90, 100];
        foreach ($scores as $score) {
            $student = $this->createUserWithRole('student');
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now()
            ]);

            Grade::create([
                'submission_id' => $submission->id,
                'score' => $score,
                'grader_id' => $teacher->id,
                'is_published' => true
            ]);
        }

        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

        $this->assertEquals(90.0, $data['average_score']);
    }

    /** @test */
    public function unpublished_grades_are_not_included_in_average(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Create published grade with score 80
        $student1 = $this->createUserWithRole('student');
        Enrollment::create([
            'user_id' => $student1->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        $submission1 = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student1->id,
            'content' => 'Test submission',
            'submitted_at' => now()
        ]);

        Grade::create([
            'submission_id' => $submission1->id,
            'score' => 80,
            'grader_id' => $teacher->id,
            'is_published' => true
        ]);

        // Create unpublished grade with score 100 (should not be included)
        $student2 = $this->createUserWithRole('student');
        Enrollment::create([
            'user_id' => $student2->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        $submission2 = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student2->id,
            'content' => 'Test submission',
            'submitted_at' => now()
        ]);

        Grade::create([
            'submission_id' => $submission2->id,
            'score' => 100,
            'grader_id' => $teacher->id,
            'is_published' => false
        ]);

        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

        // Average should be 80 (only published grade), not 90
        $this->assertEquals(80.0, $data['average_score']);
    }

    /** @test */
    public function grade_distribution_categorizes_scores_correctly(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        // Create grades at boundary values
        $boundaryScores = [
            100, // A
            90,  // A
            89,  // B
            80,  // B
            79,  // C
            70,  // C
            69,  // D
            60,  // D
            59,  // F
            0    // F
        ];

        foreach ($boundaryScores as $score) {
            $student = $this->createUserWithRole('student');
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now()
            ]);

            Grade::create([
                'submission_id' => $submission->id,
                'score' => $score,
                'grader_id' => $teacher->id,
                'is_published' => true
            ]);
        }

        $analytics = $this->analyticsService->getAssignmentAnalytics($assignment);
        $distribution = $analytics['grade_distribution'];

        $this->assertEquals(2, $distribution['A'], "Should have 2 A's (100, 90)");
        $this->assertEquals(2, $distribution['B'], "Should have 2 B's (89, 80)");
        $this->assertEquals(2, $distribution['C'], "Should have 2 C's (79, 70)");
        $this->assertEquals(2, $distribution['D'], "Should have 2 D's (69, 60)");
        $this->assertEquals(2, $distribution['F'], "Should have 2 F's (59, 0)");
    }

    /** @test */
    public function student_progress_calculates_correctly_with_multiple_courses(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');

        // Create 2 courses
        $course1 = Course::factory()->create(['teacher_id' => $teacher->id]);
        $course2 = Course::factory()->create(['teacher_id' => $teacher->id]);

        // Enroll student in both courses
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course1->id,
            'status' => 'active'
        ]);
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course2->id,
            'status' => 'active'
        ]);

        // Course 1: 3 assignments, student submits 2
        $course1Assignments = Assignment::factory()->count(3)->create([
            'course_id' => $course1->id,
            'is_published' => true
        ]);

        for ($i = 0; $i < 2; $i++) {
            Submission::create([
                'assignment_id' => $course1Assignments[$i]->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now()
            ]);
        }

        // Course 2: 2 assignments, student submits 1
        $course2Assignments = Assignment::factory()->count(2)->create([
            'course_id' => $course2->id,
            'is_published' => true
        ]);

        Submission::create([
            'assignment_id' => $course2Assignments[0]->id,
            'user_id' => $student->id,
            'content' => 'Test submission',
            'submitted_at' => now()
        ]);

        $data = $this->analyticsService->getStudentDashboardData($student);
        $progress = $data['completion_progress'];

        // Total: 5 assignments, Completed: 3 submissions
        // Expected rate: (3 / 5) * 100 = 60%
        $this->assertEquals(5, $progress['total_assignments']);
        $this->assertEquals(3, $progress['completed_assignments']);
        $this->assertEquals(60.0, $progress['completion_rate']);
    }

    /** @test */
    public function not_submitted_count_is_accurate(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Enroll 10 students
        $students = [];
        for ($i = 0; $i < 10; $i++) {
            $student = $this->createUserWithRole('student');
            $students[] = $student;
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
        }

        // Only 3 students submit
        for ($i = 0; $i < 3; $i++) {
            Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $students[$i]->id,
                'content' => 'Test submission',
                'submitted_at' => now()
            ]);
        }

        $analytics = $this->analyticsService->getAssignmentAnalytics($assignment);

        // 10 enrolled - 3 submitted = 7 not submitted
        $this->assertEquals(7, $analytics['not_submitted_count']);
    }

    /** @test */
    public function late_submission_count_is_accurate(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Create 5 on-time submissions
        for ($i = 0; $i < 5; $i++) {
            $student = $this->createUserWithRole('student');
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);

            Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now(),
                'is_late' => false
            ]);
        }

        // Create 3 late submissions
        for ($i = 0; $i < 3; $i++) {
            $student = $this->createUserWithRole('student');
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);

            Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Late submission',
                'submitted_at' => now(),
                'is_late' => true
            ]);
        }

        $analytics = $this->analyticsService->getAssignmentAnalytics($assignment);

        $this->assertEquals(3, $analytics['late_count']);
    }

    /** @test */
    public function recent_grades_returns_most_recent_first(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        // Create 3 grades with different timestamps
        $timestamps = [
            now()->subDays(5),
            now()->subDays(2),
            now()
        ];

        foreach ($timestamps as $timestamp) {
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true
            ]);

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => $timestamp
            ]);

            Grade::create([
                'submission_id' => $submission->id,
                'score' => 85,
                'grader_id' => $teacher->id,
                'is_published' => true,
                'created_at' => $timestamp
            ]);
        }

        $data = $this->analyticsService->getStudentDashboardData($student);
        $recentGrades = $data['recent_grades'];

        // Verify we have 3 grades
        $this->assertCount(3, $recentGrades);

        // Verify grades are ordered by date descending (most recent first)
        for ($i = 0; $i < count($recentGrades) - 1; $i++) {
            $currentDate = \Carbon\Carbon::parse($recentGrades[$i]['graded_at']);
            $nextDate = \Carbon\Carbon::parse($recentGrades[$i + 1]['graded_at']);
            
            $this->assertGreaterThanOrEqual(
                $nextDate->timestamp,
                $currentDate->timestamp,
                "Grades should be ordered by date descending (most recent first)"
            );
        }
    }
}
