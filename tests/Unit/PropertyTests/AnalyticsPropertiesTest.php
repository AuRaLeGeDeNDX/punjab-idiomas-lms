<?php

namespace Tests\Unit\PropertyTests;

use Tests\TestCase;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\Grade;
use App\Models\Enrollment;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

/**
 * Property-Based Tests for Analytics Service
 * 
 * Feature: complete-assignment-system
 * Tests Properties 12-20 from the design document
 * 
 * These tests verify universal properties that should hold true
 * across all valid inputs and scenarios.
 */
class AnalyticsPropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new AnalyticsService();
        
        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'teacher']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    /**
     * Helper method to create a user with a specific role
     */
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Property 12: Analytics Calculation Accuracy
     * 
     * For any course with assignments and submissions, the calculated completion rate
     * should equal (total submissions / (total assignments × total enrolled students)) × 100,
     * and the average score should equal the mean of all graded submissions.
     * 
     * Validates: Requirements 3.1, 3.2, 3.6, 4.1, 4.2
     */
    public function test_property_12_analytics_calculation_accuracy(): void
    {
        // Test with various combinations of assignments, students, and submissions
        $testCases = [
            ['assignments' => 5, 'students' => 10, 'submission_rate' => 0.8],
            ['assignments' => 3, 'students' => 20, 'submission_rate' => 0.5],
            ['assignments' => 10, 'students' => 5, 'submission_rate' => 1.0],
            ['assignments' => 1, 'students' => 50, 'submission_rate' => 0.3],
            ['assignments' => 8, 'students' => 15, 'submission_rate' => 0.9],
        ];

        foreach ($testCases as $case) {
            // Create course with teacher
            $teacher = $this->createUserWithRole('teacher');
            $course = Course::factory()->create(['teacher_id' => $teacher->id]);

            // Create and enroll students
            $students = [];
            for ($i = 0; $i < $case['students']; $i++) {
                $student = $this->createUserWithRole('student');
                $students[] = $student;
                Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => 'active'
                ]);
            }

            // Create assignments
            $assignments = Assignment::factory()->count($case['assignments'])->create([
                'course_id' => $course->id,
                'is_published' => true,
                'max_score' => 100
            ]);

            // Create submissions based on submission rate
            $totalPossibleSubmissions = $case['assignments'] * $case['students'];
            $targetSubmissions = (int) round($totalPossibleSubmissions * $case['submission_rate']);
            
            $submissionCount = 0;
            $totalScore = 0;
            $gradedCount = 0;

            foreach ($assignments as $assignment) {
                foreach ($students as $student) {
                    if ($submissionCount >= $targetSubmissions) break 2;
                    
                    $submission = Submission::create([
                        'assignment_id' => $assignment->id,
                        'user_id' => $student->id,
                        'content' => 'Test submission',
                        'submitted_at' => now()
                    ]);

                    // Grade 80% of submissions
                    if (rand(1, 100) <= 80) {
                        $score = rand(60, 100);
                        Grade::create([
                            'submission_id' => $submission->id,
                            'score' => $score,
                            'grader_id' => $teacher->id,
                            'is_published' => true
                        ]);
                        $totalScore += $score;
                        $gradedCount++;
                    }

                    $submissionCount++;
                }
            }

            // Calculate expected values
            $expectedCompletionRate = round(($submissionCount / $totalPossibleSubmissions) * 100, 2);
            $expectedAverageScore = $gradedCount > 0 ? round($totalScore / $gradedCount, 2) : 0.0;

            // Get analytics data
            $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

            // Verify completion rate
            $this->assertEquals(
                $expectedCompletionRate,
                $data['overall_completion_rate'],
                "Completion rate mismatch for case: " . json_encode($case)
            );

            // Verify average score
            $this->assertEquals(
                $expectedAverageScore,
                $data['average_score'],
                "Average score mismatch for case: " . json_encode($case)
            );
        }
    }

    /**
     * Property 13: Submission Timeline Aggregation
     * 
     * For any time period, the submission timeline should accurately count
     * submissions per day with no missing or duplicate entries.
     * 
     * Validates: Requirements 3.3
     */
    public function test_property_13_submission_timeline_aggregation(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Create submissions on specific dates
        $submissionDates = [
            5 => 3,  // 5 days ago: 3 submissions
            3 => 5,  // 3 days ago: 5 submissions
            1 => 2,  // 1 day ago: 2 submissions
            0 => 4   // today: 4 submissions
        ];

        foreach ($submissionDates as $daysAgo => $count) {
            $date = now()->subDays($daysAgo);
            for ($i = 0; $i < $count; $i++) {
                // Create a unique student for each submission to avoid unique constraint violation
                $student = $this->createUserWithRole('student');
                Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'status' => 'active'
                ]);
                
                Submission::create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id,
                    'content' => "Submission $i",
                    'submitted_at' => $date
                ]);
            }
        }

        // Get timeline data
        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);
        $timeline = $data['submission_timeline'];

        // Verify each date has correct count
        foreach ($submissionDates as $daysAgo => $expectedCount) {
            $dateStr = now()->subDays($daysAgo)->format('Y-m-d');
            $found = false;
            
            foreach ($timeline as $entry) {
                if ($entry['date'] === $dateStr) {
                    $this->assertEquals($expectedCount, $entry['count'], 
                        "Count mismatch for date $dateStr");
                    $found = true;
                    break;
                }
            }
            
            $this->assertTrue($found, "Date $dateStr not found in timeline");
        }

        // Verify no duplicate dates
        $dates = array_column($timeline, 'date');
        $this->assertEquals(count($dates), count(array_unique($dates)), 
            "Timeline contains duplicate dates");
    }

    /**
     * Property 14: Student Ranking Accuracy
     * 
     * For any set of students, the top performers should be correctly identified
     * by highest average score, and at-risk students should be correctly identified
     * by lowest average score or missing submissions.
     * 
     * Validates: Requirements 3.4, 3.5
     */
    public function test_property_14_student_ranking_accuracy(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        // Create students with known average scores
        $studentScores = [
            'Student A' => [95, 98, 92, 96],  // Avg: 95.25 (top)
            'Student B' => [88, 90, 85, 87],  // Avg: 87.5
            'Student C' => [75, 78, 72, 76],  // Avg: 75.25
            'Student D' => [55, 58, 52, 56],  // Avg: 55.25 (at-risk)
            'Student E' => [45, 48, 42, 46],  // Avg: 45.25 (at-risk)
        ];

        $students = [];
        foreach ($studentScores as $name => $scores) {
            $student = $this->createUserWithRole('student', ['name' => $name]);
            $students[$name] = $student;
            
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
        }

        // Create assignments and submissions
        foreach ($studentScores as $name => $scores) {
            foreach ($scores as $index => $score) {
                $assignment = Assignment::factory()->create([
                    'course_id' => $course->id,
                    'is_published' => true,
                    'max_score' => 100,
                    'title' => "Assignment " . ($index + 1)
                ]);

                $submission = Submission::create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $students[$name]->id,
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
        }

        // Get analytics data
        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);

        // Verify top performers
        $topPerformers = $data['top_performers'];
        $this->assertNotEmpty($topPerformers, "Top performers list should not be empty");
        
        // First top performer should be Student A
        $this->assertEquals('Student A', $topPerformers[0]['student_name']);
        $this->assertEquals(95.25, $topPerformers[0]['average_score']);

        // Verify at-risk students
        $atRiskStudents = $data['at_risk_students'];
        $this->assertNotEmpty($atRiskStudents, "At-risk students list should not be empty");
        
        // At-risk students should have scores below 60
        foreach ($atRiskStudents as $student) {
            $this->assertLessThan(60, $student['average_score'], 
                "At-risk student {$student['student_name']} should have score < 60");
        }

        // Verify sorting (top performers descending, at-risk ascending)
        for ($i = 0; $i < count($topPerformers) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                $topPerformers[$i + 1]['average_score'],
                $topPerformers[$i]['average_score'],
                "Top performers should be sorted by score descending"
            );
        }
    }

    /**
     * Property 15: Grade Distribution Categorization
     * 
     * For any set of grades, the distribution should correctly categorize scores
     * into ranges (A: 90-100, B: 80-89, C: 70-79, D: 60-69, F: 0-59) with accurate counts.
     * 
     * Validates: Requirements 3.7
     */
    public function test_property_15_grade_distribution_categorization(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        // Create grades with known distribution
        $gradeScores = [
            95, 92, 98, 91,      // 4 A's (90-100)
            85, 88, 82, 87, 89,  // 5 B's (80-89)
            75, 78, 72,          // 3 C's (70-79)
            65, 68,              // 2 D's (60-69)
            55, 52, 48           // 3 F's (0-59)
        ];

        $expectedDistribution = [
            'A' => 4,
            'B' => 5,
            'C' => 3,
            'D' => 2,
            'F' => 3
        ];

        foreach ($gradeScores as $score) {
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

        // Get assignment analytics
        $analytics = $this->analyticsService->getAssignmentAnalytics($assignment);
        $distribution = $analytics['grade_distribution'];

        // Verify each grade category
        foreach ($expectedDistribution as $grade => $expectedCount) {
            $this->assertEquals(
                $expectedCount,
                $distribution[$grade],
                "Grade $grade count mismatch"
            );
        }

        // Verify total count
        $totalGrades = array_sum($distribution);
        $this->assertEquals(count($gradeScores), $totalGrades, 
            "Total grade count should match number of submissions");
    }

    /**
     * Property 16: Student Data Completeness
     * 
     * For any student or assignment, all related data (submissions, grades, assignments)
     * should be included in analytics views with no missing records.
     * 
     * Validates: Requirements 3.8, 3.9, 3.10, 4.9
     */
    public function test_property_16_student_data_completeness(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        // Create multiple assignments with submissions and grades
        $assignmentCount = 5;
        $assignments = [];
        
        for ($i = 0; $i < $assignmentCount; $i++) {
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
                'title' => "Assignment " . ($i + 1)
            ]);
            $assignments[] = $assignment;

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now()->subDays($i)
            ]);

            Grade::create([
                'submission_id' => $submission->id,
                'score' => 80 + $i,
                'grader_id' => $teacher->id,
                'is_published' => true,
                'created_at' => now()->subDays($i)
            ]);
        }

        // Get student dashboard data
        $data = $this->analyticsService->getStudentDashboardData($student);

        // Verify all assignments are counted
        $this->assertEquals($assignmentCount, $data['completion_progress']['completed_assignments'],
            "All submitted assignments should be counted");

        // Verify recent grades includes all grades (up to limit of 5)
        $recentGrades = $data['recent_grades'];
        $this->assertCount(min(5, $assignmentCount), $recentGrades,
            "Recent grades should include up to 5 grades");

        // Verify performance trend includes all grades
        $performanceTrend = $data['performance_trend'];
        $this->assertCount($assignmentCount, $performanceTrend,
            "Performance trend should include all grades");

        // Verify no duplicate entries
        $gradeIds = array_column($recentGrades, 'id');
        $this->assertEquals(count($gradeIds), count(array_unique($gradeIds)),
            "Recent grades should not contain duplicates");
    }

    /**
     * Property 17: Date-Based Filtering
     * 
     * For any student, upcoming assignments should include only those due within 7 days,
     * overdue assignments should include only those past due with late submission allowed
     * and not submitted, and missed assignments should include only those past due with
     * late submission not allowed and not submitted.
     * 
     * Validates: Requirements 4.3, 4.4, 4.5
     */
    public function test_property_17_date_based_filtering(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        // Create upcoming assignment (due in 3 days)
        $upcomingAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->addDays(3),
            'title' => 'Upcoming Assignment'
        ]);

        // Create overdue assignment (past due, allows late submission, not submitted)
        $overdueAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->subDays(2),
            'allow_late_submission' => true,
            'title' => 'Overdue Assignment'
        ]);

        // Create missed assignment (past due, no late submission, not submitted)
        $missedAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->subDays(5),
            'allow_late_submission' => false,
            'title' => 'Missed Assignment'
        ]);

        // Create assignment due in 10 days (should not appear in upcoming)
        $futureAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->addDays(10),
            'title' => 'Future Assignment'
        ]);

        // Create submitted overdue assignment (should not appear in overdue)
        $submittedOverdueAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->subDays(3),
            'allow_late_submission' => true,
            'title' => 'Submitted Overdue'
        ]);
        Submission::create([
            'assignment_id' => $submittedOverdueAssignment->id,
            'user_id' => $student->id,
            'content' => 'Late submission',
            'submitted_at' => now()
        ]);

        // Get student dashboard data
        $data = $this->analyticsService->getStudentDashboardData($student);

        // Verify upcoming assignments
        $upcomingAssignments = $data['upcoming_assignments'];
        $upcomingIds = array_column($upcomingAssignments, 'id');
        $this->assertContains($upcomingAssignment->id, $upcomingIds,
            "Upcoming assignments should include assignment due in 3 days");
        $this->assertNotContains($futureAssignment->id, $upcomingIds,
            "Upcoming assignments should not include assignment due in 10 days");

        // Verify all upcoming assignments are due within 7 days
        foreach ($upcomingAssignments as $assignment) {
            $daysUntilDue = $assignment['days_until_due'];
            $this->assertLessThanOrEqual(7, $daysUntilDue,
                "Upcoming assignment should be due within 7 days");
            $this->assertGreaterThan(0, $daysUntilDue,
                "Upcoming assignment should not be past due");
        }

        // Verify overdue assignments
        $overdueAssignments = $data['overdue_assignments'];
        $overdueIds = array_column($overdueAssignments, 'id');
        $this->assertContains($overdueAssignment->id, $overdueIds,
            "Overdue assignments should include past due assignment with late submission allowed");
        $this->assertNotContains($submittedOverdueAssignment->id, $overdueIds,
            "Overdue assignments should not include submitted assignments");

        // Verify all overdue assignments allow late submission
        foreach ($overdueAssignments as $assignment) {
            $this->assertTrue($assignment['allows_late_submission'],
                "Overdue assignments should allow late submission");
        }

        // Verify missed assignments
        $missedAssignments = $data['missed_assignments'];
        $missedIds = array_column($missedAssignments, 'id');
        $this->assertContains($missedAssignment->id, $missedIds,
            "Missed assignments should include past due assignment without late submission");

        // Verify all missed assignments do not allow late submission
        foreach ($missedAssignments as $assignment) {
            $this->assertFalse($assignment['allows_late_submission'],
                "Missed assignments should not allow late submission");
        }
    }

    /**
     * Property 18: Recent Grades Limiting
     * 
     * For any student, the recent grades view should return exactly the last 5 graded
     * assignments (or fewer if less than 5 exist), ordered by graded_at descending.
     * 
     * Validates: Requirements 4.6
     */
    public function test_property_18_recent_grades_limiting_with_more_than_5(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        // Test with 8 graded assignments
        $gradeCount = 8;
        $grades = [];
        
        for ($i = 0; $i < $gradeCount; $i++) {
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
                'title' => "Assignment " . ($i + 1)
            ]);

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now()->subDays($gradeCount - $i)
            ]);

            $grade = Grade::create([
                'submission_id' => $submission->id,
                'score' => 80 + $i,
                'grader_id' => $teacher->id,
                'is_published' => true,
                'created_at' => now()->subDays($gradeCount - $i)
            ]);
            
            $grades[] = $grade;
        }

        // Get student dashboard data
        $data = $this->analyticsService->getStudentDashboardData($student);
        $recentGrades = $data['recent_grades'];

        // Verify exactly 5 grades returned (limit)
        $this->assertCount(5, $recentGrades,
            "Recent grades should return exactly 5 grades when more than 5 exist");

        // Verify grades are ordered by graded_at descending (most recent first)
        for ($i = 0; $i < count($recentGrades) - 1; $i++) {
            $currentDate = Carbon::parse($recentGrades[$i]['graded_at']);
            $nextDate = Carbon::parse($recentGrades[$i + 1]['graded_at']);
            
            $this->assertGreaterThanOrEqual(
                $nextDate->timestamp,
                $currentDate->timestamp,
                "Recent grades should be ordered by graded_at descending"
            );
        }
    }

    /**
     * Property 18b: Recent Grades Limiting with fewer than 5
     */
    public function test_property_18_recent_grades_limiting_with_fewer_than_5(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        // Create only 3 graded assignments
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true
            ]);

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now()
            ]);

            Grade::create([
                'submission_id' => $submission->id,
                'score' => 85,
                'grader_id' => $teacher->id,
                'is_published' => true
            ]);
        }

        $data = $this->analyticsService->getStudentDashboardData($student);
        $recentGrades = $data['recent_grades'];

        // Verify exactly 3 grades returned (all available)
        $this->assertCount(3, $recentGrades,
            "Recent grades should return all grades when fewer than 5 exist");
    }

    /**
     * Property 19: Performance Trend Accuracy
     * 
     * For any student, the performance chart should accurately represent score trends
     * over time with correct chronological ordering.
     * 
     * Validates: Requirements 4.7
     */
    public function test_property_19_performance_trend_accuracy(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        // Create grades with known scores and dates
        $gradeData = [
            ['date' => now()->subDays(10), 'score' => 75, 'max_score' => 100],
            ['date' => now()->subDays(8), 'score' => 80, 'max_score' => 100],
            ['date' => now()->subDays(5), 'score' => 85, 'max_score' => 100],
            ['date' => now()->subDays(3), 'score' => 90, 'max_score' => 100],
            ['date' => now()->subDays(1), 'score' => 95, 'max_score' => 100],
        ];

        foreach ($gradeData as $data) {
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
                'max_score' => $data['max_score']
            ]);

            $submission = Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => $data['date']
            ]);

            Grade::create([
                'submission_id' => $submission->id,
                'score' => $data['score'],
                'grader_id' => $teacher->id,
                'is_published' => true,
                'created_at' => $data['date']
            ]);
        }

        // Get student dashboard data
        $dashboardData = $this->analyticsService->getStudentDashboardData($student);
        $performanceTrend = $dashboardData['performance_trend'];

        // Verify all grades are included
        $this->assertCount(count($gradeData), $performanceTrend,
            "Performance trend should include all grades");

        // Verify chronological ordering (oldest to newest)
        for ($i = 0; $i < count($performanceTrend) - 1; $i++) {
            $currentDate = Carbon::parse($performanceTrend[$i]['date']);
            $nextDate = Carbon::parse($performanceTrend[$i + 1]['date']);
            
            $this->assertLessThanOrEqual(
                $nextDate->timestamp,
                $currentDate->timestamp,
                "Performance trend should be ordered chronologically"
            );
        }

        // Verify score accuracy
        foreach ($performanceTrend as $index => $trendPoint) {
            $expectedScore = $gradeData[$index]['score'];
            $expectedPercentage = round(($expectedScore / $gradeData[$index]['max_score']) * 100, 2);
            
            $this->assertEquals($expectedScore, $trendPoint['score'],
                "Score should match for trend point $index");
            $this->assertEquals($expectedPercentage, $trendPoint['percentage'],
                "Percentage should be correctly calculated for trend point $index");
        }
    }

    /**
     * Property 20: Assignment History Sorting
     * 
     * For any student, assignment history should display all assignments in chronological
     * order with correct status badges (not submitted, submitted, late, graded).
     * 
     * Validates: Requirements 4.8
     */
    public function test_property_20_assignment_history_sorting(): void
    {
        $student = $this->createUserWithRole('student');
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        // Create assignment with no submission (not submitted)
        $notSubmittedAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->addDays(5),
            'title' => 'Not Submitted Assignment'
        ]);

        // Create assignment with submission but no grade (submitted)
        $submittedAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->addDays(3),
            'title' => 'Submitted Assignment'
        ]);
        Submission::create([
            'assignment_id' => $submittedAssignment->id,
            'user_id' => $student->id,
            'content' => 'Test submission',
            'submitted_at' => now(),
            'is_late' => false
        ]);

        // Create assignment with late submission (late)
        $lateAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->subDays(2),
            'allow_late_submission' => true,
            'title' => 'Late Assignment'
        ]);
        Submission::create([
            'assignment_id' => $lateAssignment->id,
            'user_id' => $student->id,
            'content' => 'Late submission',
            'submitted_at' => now(),
            'is_late' => true
        ]);

        // Create assignment with grade (graded)
        $gradedAssignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'due_date' => now()->subDays(5),
            'title' => 'Graded Assignment'
        ]);
        $gradedSubmission = Submission::create([
            'assignment_id' => $gradedAssignment->id,
            'user_id' => $student->id,
            'content' => 'Graded submission',
            'submitted_at' => now()->subDays(4),
            'is_late' => false
        ]);
        Grade::create([
            'submission_id' => $gradedSubmission->id,
            'score' => 85,
            'grader_id' => $teacher->id,
            'is_published' => true
        ]);

        // Get assignment analytics for each assignment
        $assignments = [
            $notSubmittedAssignment,
            $submittedAssignment,
            $lateAssignment,
            $gradedAssignment
        ];

        $statuses = [];
        foreach ($assignments as $assignment) {
            $analytics = $this->analyticsService->getAssignmentAnalytics($assignment);
            $studentList = $analytics['student_list']['data'];
            
            // Find the student in the list
            foreach ($studentList as $studentData) {
                if ($studentData['student_id'] === $student->id) {
                    $statuses[$assignment->title] = $studentData['status'];
                    break;
                }
            }
        }

        // Verify status badges
        $this->assertEquals('not_submitted', $statuses['Not Submitted Assignment'],
            "Assignment with no submission should have 'not_submitted' status");
        $this->assertEquals('submitted', $statuses['Submitted Assignment'],
            "Assignment with submission but no grade should have 'submitted' status");
        $this->assertEquals('late', $statuses['Late Assignment'],
            "Assignment with late submission should have 'late' status");
        $this->assertEquals('graded', $statuses['Graded Assignment'],
            "Assignment with grade should have 'graded' status");

        // Verify chronological ordering in student dashboard
        $dashboardData = $this->analyticsService->getStudentDashboardData($student);
        
        // Upcoming assignments should be ordered by due date
        $upcomingAssignments = $dashboardData['upcoming_assignments'];
        for ($i = 0; $i < count($upcomingAssignments) - 1; $i++) {
            $currentDueDate = Carbon::parse($upcomingAssignments[$i]['due_date']);
            $nextDueDate = Carbon::parse($upcomingAssignments[$i + 1]['due_date']);
            
            $this->assertLessThanOrEqual(
                $nextDueDate->timestamp,
                $currentDueDate->timestamp,
                "Upcoming assignments should be ordered by due date ascending"
            );
        }
    }
}
