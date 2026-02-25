<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\GradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GradingService $gradingService;
    protected User $teacher;
    protected User $student;
    protected Course $course;
    protected Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        $this->gradingService = app(GradingService::class);
        
        // Create test users
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->student = User::factory()->create();
        $this->student->assignRole('Student');
        
        // Create test course
        $this->course = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
            'grading_scheme' => [
                'quiz' => 20,
                'assignment' => 40,
                'project' => 25,
                'exam' => 15,
            ],
            'passing_grade' => 60.00,
        ]);
        
        // Create test assignment
        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'module_id' => null, // Don't use modules in this test
            'max_score' => 100,
            'assignment_type' => 'assignment',
            'is_published' => true, // Make sure it's published
        ]);
        
        // Enroll student
        Enrollment::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);
    }

    public function test_can_grade_submission_manually()
    {
        // Create a submission
        $submission = Submission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        // Grade the submission
        $grade = $this->gradingService->gradeSubmission(
            $submission,
            85.0,
            $this->teacher,
            'Good work!',
            ['criteria1' => 90, 'criteria2' => 80]
        );

        // Assert grade was created correctly
        $this->assertInstanceOf(Grade::class, $grade);
        $this->assertEquals(85.0, $grade->score);
        $this->assertEquals('Good work!', $grade->feedback);
        $this->assertEquals($this->teacher->id, $grade->grader_id);
        $this->assertEquals('B', $grade->grade_letter);
        $this->assertFalse($grade->is_published);

        // Assert submission status was updated
        $submission->refresh();
        $this->assertEquals('graded', $submission->status);
    }

    public function test_cannot_grade_with_score_exceeding_max()
    {
        $submission = Submission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Score cannot exceed maximum score of 100');

        $this->gradingService->gradeSubmission(
            $submission,
            150.0, // Exceeds max score
            $this->teacher
        );
    }

    public function test_can_calculate_course_grade()
    {
        // Create multiple assignments with different types
        $quiz = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'module_id' => null,
            'assignment_type' => 'quiz',
            'max_score' => 50,
        ]);

        $project = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'module_id' => null,
            'assignment_type' => 'project',
            'max_score' => 200,
        ]);

        // Create submissions and grades
        $assignmentSubmission = Submission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
        ]);

        $quizSubmission = Submission::factory()->create([
            'assignment_id' => $quiz->id,
            'user_id' => $this->student->id,
        ]);

        $projectSubmission = Submission::factory()->create([
            'assignment_id' => $project->id,
            'user_id' => $this->student->id,
        ]);

        // Create published grades
        Grade::factory()->create([
            'submission_id' => $assignmentSubmission->id,
            'score' => 80, // 80/100 = 80%
            'is_published' => true,
        ]);

        Grade::factory()->create([
            'submission_id' => $quizSubmission->id,
            'score' => 40, // 40/50 = 80%
            'is_published' => true,
        ]);

        Grade::factory()->create([
            'submission_id' => $projectSubmission->id,
            'score' => 160, // 160/200 = 80%
            'is_published' => true,
        ]);

        // Calculate course grade
        $enrollment = $this->student->enrollments()->where('course_id', $this->course->id)->first();
        $courseGrade = $this->gradingService->calculateCourseGrade($enrollment);

        // Expected calculation:
        // Assignment: 80% * 40% = 32%
        // Quiz: 80% * 20% = 16%
        // Project: 80% * 25% = 20%
        // Total: 68% (exam weight not included as no exam grades)

        $this->assertEquals(68.0, $courseGrade['weighted_score']);
        $this->assertEquals('D', $courseGrade['letter_grade']);
        $this->assertArrayHasKey('grade_breakdown', $courseGrade);
    }

    public function test_can_publish_grades_for_assignment()
    {
        // Create multiple submissions for the assignment
        $submissions = Submission::factory()->count(3)->create([
            'assignment_id' => $this->assignment->id,
        ]);

        // Create unpublished grades
        foreach ($submissions as $submission) {
            Grade::factory()->create([
                'submission_id' => $submission->id,
                'is_published' => false,
            ]);
        }

        // Publish grades
        $publishedCount = $this->gradingService->publishGrades($this->assignment, $this->teacher);

        // Assert all grades were published
        $this->assertEquals(3, $publishedCount);
        
        $publishedGrades = Grade::whereHas('submission', function ($query) {
            $query->where('assignment_id', $this->assignment->id);
        })->where('is_published', true)->count();
        
        $this->assertEquals(3, $publishedGrades);
    }

    public function test_can_get_grade_book_data()
    {
        // Create additional students and assignments
        $student2 = User::factory()->create();
        $student2->assignRole('Student');
        
        Enrollment::factory()->create([
            'user_id' => $student2->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);

        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'module_id' => null,
            'max_score' => 50,
            'is_published' => true, // Make sure it's published
        ]);

        // Create submissions and grades
        $submissions = [
            Submission::factory()->create([
                'assignment_id' => $this->assignment->id,
                'user_id' => $this->student->id,
            ]),
            Submission::factory()->create([
                'assignment_id' => $assignment2->id,
                'user_id' => $this->student->id,
            ]),
            Submission::factory()->create([
                'assignment_id' => $this->assignment->id,
                'user_id' => $student2->id,
            ]),
        ];

        foreach ($submissions as $submission) {
            Grade::factory()->create([
                'submission_id' => $submission->id,
                'score' => 80,
                'is_published' => true,
            ]);
        }

        // Get grade book
        $gradeBook = $this->gradingService->getGradeBook($this->course);

        // Assert structure
        $this->assertArrayHasKey('course', $gradeBook);
        $this->assertArrayHasKey('assignments', $gradeBook);
        $this->assertArrayHasKey('students', $gradeBook);
        $this->assertArrayHasKey('grade_matrix', $gradeBook);
        $this->assertArrayHasKey('assignment_stats', $gradeBook);

        // Assert data
        $this->assertEquals($this->course->id, $gradeBook['course']->id);
        $this->assertCount(2, $gradeBook['assignments']);
        $this->assertCount(2, $gradeBook['students']);
        $this->assertCount(2, $gradeBook['grade_matrix']);
    }

    public function test_can_export_grades()
    {
        // Create a submission and grade
        $submission = Submission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
        ]);

        Grade::factory()->create([
            'submission_id' => $submission->id,
            'score' => 85,
            'is_published' => true,
        ]);

        // Export grades
        $exportData = $this->gradingService->exportGrades($this->course);

        // Assert export structure
        $this->assertArrayHasKey('data', $exportData);
        $this->assertArrayHasKey('filename', $exportData);
        $this->assertArrayHasKey('course', $exportData);

        // Assert data contains headers and student data
        $this->assertIsArray($exportData['data']);
        $this->assertGreaterThan(1, count($exportData['data'])); // Headers + at least one student row
        
        // Check filename format
        $this->assertStringContainsString('grades_', $exportData['filename']);
        $this->assertStringEndsWith('.csv', $exportData['filename']);
    }

    public function test_letter_grade_calculation()
    {
        $submission = Submission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
        ]);

        // Test different score ranges
        $testCases = [
            ['score' => 95, 'expected' => 'A'],
            ['score' => 85, 'expected' => 'B'],
            ['score' => 75, 'expected' => 'C'],
            ['score' => 65, 'expected' => 'D'],
            ['score' => 55, 'expected' => 'F'],
        ];

        foreach ($testCases as $case) {
            $grade = $this->gradingService->gradeSubmission(
                $submission,
                $case['score'],
                $this->teacher
            );

            $this->assertEquals($case['expected'], $grade->grade_letter, 
                "Score {$case['score']} should result in grade {$case['expected']}");
        }
    }
}