<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AnalyticsService;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Grade;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Test suite for AnalyticsService student dashboard data aggregation
 * 
 * Task 3.3: Implement student dashboard data aggregation
 * Requirements: 4 (Student Progress), 14 (Performance Optimization)
 */
class AnalyticsServiceStudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $analyticsService;
    private User $student;
    private Course $course;
    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->analyticsService = new AnalyticsService();
        
        // Create test student, teacher, and course
        $this->student = User::factory()->create(['name' => 'Test Student']);
        $this->teacher = User::factory()->create(['name' => 'Test Teacher']);
        $this->course = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Test Course'
        ]);
        
        // Enroll student in course
        Enrollment::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);
    }

    /**
     * Test that student dashboard data is returned with all required fields
     */
    public function test_student_dashboard_returns_all_required_fields(): void
    {
        // Arrange: Create test data
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'submitted_at' => now()
        ]);

        Grade::factory()->create([
            'submission_id' => $submission->id,
            'grader_id' => $this->teacher->id,
            'score' => 85,
            'is_published' => true
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Verify all required fields are present
        $this->assertIsArray($data);
        $this->assertArrayHasKey('completion_progress', $data);
        $this->assertArrayHasKey('average_score', $data);
        $this->assertArrayHasKey('upcoming_assignments', $data);
        $this->assertArrayHasKey('overdue_assignments', $data);
        $this->assertArrayHasKey('missed_assignments', $data);
        $this->assertArrayHasKey('recent_grades', $data);
        $this->assertArrayHasKey('performance_trend', $data);
    }

    /**
     * Test student completion progress calculation
     */
    public function test_completion_progress_calculation_is_accurate(): void
    {
        // Arrange: Create 3 assignments, student completes 2
        $assignment1 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);
        
        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);
        
        $assignment3 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);

        // Student submits 2 out of 3
        Submission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $this->student->id,
            'submitted_at' => now()
        ]);
        
        Submission::factory()->create([
            'assignment_id' => $assignment2->id,
            'user_id' => $this->student->id,
            'submitted_at' => now()
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Completion rate should be 66.67% (2 out of 3)
        $this->assertEquals(3, $data['completion_progress']['total_assignments']);
        $this->assertEquals(2, $data['completion_progress']['completed_assignments']);
        $this->assertEquals(66.67, $data['completion_progress']['completion_rate']);
    }

    /**
     * Test student average score calculation
     */
    public function test_average_score_calculation_is_accurate(): void
    {
        // Arrange: Create assignments with different scores
        $scores = [80, 90, 85];
        
        foreach ($scores as $score) {
            $assignment = Assignment::factory()->create([
                'course_id' => $this->course->id,
                'is_published' => true,
                'max_score' => 100
            ]);

            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $this->student->id,
                'submitted_at' => now()
            ]);

            Grade::factory()->create([
                'submission_id' => $submission->id,
                'grader_id' => $this->teacher->id,
                'score' => $score,
                'is_published' => true
            ]);
        }

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Average score should be 85 ((80 + 90 + 85) / 3)
        $this->assertEquals(85.0, $data['average_score']);
    }

    /**
     * Test upcoming assignments (due within 7 days)
     */
    public function test_upcoming_assignments_within_7_days(): void
    {
        // Arrange: Create assignments with different due dates
        $upcomingAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Upcoming Assignment',
            'due_date' => Carbon::now()->addDays(3)
        ]);
        
        $farFutureAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Far Future Assignment',
            'due_date' => Carbon::now()->addDays(10)
        ]);
        
        $pastAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Past Assignment',
            'due_date' => Carbon::now()->subDays(2)
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Only upcoming assignment within 7 days should be included
        $this->assertCount(1, $data['upcoming_assignments']);
        $this->assertEquals('Upcoming Assignment', $data['upcoming_assignments'][0]['title']);
        $this->assertEquals(3, $data['upcoming_assignments'][0]['days_until_due']);
    }

    /**
     * Test overdue assignments (past due, late submission allowed)
     */
    public function test_overdue_assignments_with_late_submission_allowed(): void
    {
        // Arrange: Create overdue assignment that allows late submission
        $overdueAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Overdue Assignment',
            'due_date' => Carbon::now()->subDays(2),
            'allow_late_submission' => true
        ]);
        
        // Create assignment that doesn't allow late submission (should not appear in overdue)
        $missedAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Missed Assignment',
            'due_date' => Carbon::now()->subDays(3),
            'allow_late_submission' => false
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Only overdue assignment with late submission allowed should be included
        $this->assertCount(1, $data['overdue_assignments']);
        $this->assertEquals('Overdue Assignment', $data['overdue_assignments'][0]['title']);
        $this->assertTrue($data['overdue_assignments'][0]['allows_late_submission']);
        $this->assertEquals(2, $data['overdue_assignments'][0]['days_overdue']);
    }

    /**
     * Test missed assignments (past due, late submission NOT allowed)
     */
    public function test_missed_assignments_without_late_submission(): void
    {
        // Arrange: Create missed assignment that doesn't allow late submission
        $missedAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Missed Assignment',
            'due_date' => Carbon::now()->subDays(3),
            'allow_late_submission' => false
        ]);
        
        // Create assignment that allows late submission (should not appear in missed)
        $overdueAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Overdue Assignment',
            'due_date' => Carbon::now()->subDays(2),
            'allow_late_submission' => true
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Only missed assignment without late submission should be included
        $this->assertCount(1, $data['missed_assignments']);
        $this->assertEquals('Missed Assignment', $data['missed_assignments'][0]['title']);
        $this->assertFalse($data['missed_assignments'][0]['allows_late_submission']);
        $this->assertEquals(3, $data['missed_assignments'][0]['days_overdue']);
    }

    /**
     * Test that submitted assignments don't appear in upcoming/overdue/missed
     */
    public function test_submitted_assignments_excluded_from_pending_lists(): void
    {
        // Arrange: Create assignment and submit it
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Submitted Assignment',
            'due_date' => Carbon::now()->addDays(2),
            'allow_late_submission' => true
        ]);
        
        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'submitted_at' => now()
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Submitted assignment should not appear in any pending lists
        $this->assertCount(0, $data['upcoming_assignments']);
        $this->assertCount(0, $data['overdue_assignments']);
        $this->assertCount(0, $data['missed_assignments']);
    }

    /**
     * Test recent grades (last 5 graded assignments)
     */
    public function test_recent_grades_returns_last_5_grades(): void
    {
        // Arrange: Create 7 graded assignments
        for ($i = 1; $i <= 7; $i++) {
            $assignment = Assignment::factory()->create([
                'course_id' => $this->course->id,
                'is_published' => true,
                'title' => "Assignment $i",
                'max_score' => 100
            ]);

            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $this->student->id,
                'submitted_at' => now()->subDays(7 - $i)
            ]);

            Grade::factory()->create([
                'submission_id' => $submission->id,
                'grader_id' => $this->teacher->id,
                'score' => 80 + $i,
                'is_published' => true,
                'created_at' => now()->subDays(7 - $i)
            ]);
        }

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Only last 5 grades should be returned
        $this->assertCount(5, $data['recent_grades']);
        
        // Verify they are in descending order (most recent first)
        $this->assertEquals('Assignment 7', $data['recent_grades'][0]['assignment_title']);
        $this->assertEquals(87, $data['recent_grades'][0]['score']);
    }

    /**
     * Test performance trend data
     */
    public function test_performance_trend_shows_scores_over_time(): void
    {
        // Arrange: Create grades over time
        $scores = [70, 75, 80, 85, 90];
        
        foreach ($scores as $index => $score) {
            $assignment = Assignment::factory()->create([
                'course_id' => $this->course->id,
                'is_published' => true,
                'title' => "Assignment " . ($index + 1),
                'max_score' => 100
            ]);

            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $this->student->id,
                'submitted_at' => now()->subDays(5 - $index)
            ]);

            Grade::factory()->create([
                'submission_id' => $submission->id,
                'grader_id' => $this->teacher->id,
                'score' => $score,
                'is_published' => true,
                'created_at' => now()->subDays(5 - $index)
            ]);
        }

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Performance trend should show all grades in chronological order
        $this->assertCount(5, $data['performance_trend']);
        $this->assertEquals(70, $data['performance_trend'][0]['score']);
        $this->assertEquals(90, $data['performance_trend'][4]['score']);
        
        // Verify percentages are calculated correctly
        $this->assertEquals(70.0, $data['performance_trend'][0]['percentage']);
        $this->assertEquals(90.0, $data['performance_trend'][4]['percentage']);
    }

    /**
     * Test caching functionality (2-minute TTL)
     */
    public function test_dashboard_data_is_cached_with_2_minute_ttl(): void
    {
        // Arrange: Create test data
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);

        // Act: Call twice
        Cache::flush(); // Clear cache first
        $data1 = $this->analyticsService->getStudentDashboardData($this->student);
        $data2 = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Data should be identical (from cache)
        $this->assertEquals($data1, $data2);
        
        // Verify cache key exists
        $cacheKey = "student_analytics_{$this->student->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test error handling when student has no enrollments
     */
    public function test_handles_student_with_no_enrollments_gracefully(): void
    {
        // Arrange: Create new student with no enrollments
        $newStudent = User::factory()->create();

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($newStudent);

        // Assert: Should return zero values, not errors
        $this->assertEquals(0, $data['completion_progress']['total_assignments']);
        $this->assertEquals(0, $data['completion_progress']['completed_assignments']);
        $this->assertEquals(0.0, $data['completion_progress']['completion_rate']);
        $this->assertEquals(0.0, $data['average_score']);
        $this->assertIsArray($data['upcoming_assignments']);
        $this->assertIsArray($data['overdue_assignments']);
        $this->assertIsArray($data['missed_assignments']);
        $this->assertIsArray($data['recent_grades']);
        $this->assertIsArray($data['performance_trend']);
    }

    /**
     * Test that only published grades are included
     */
    public function test_only_published_grades_are_included(): void
    {
        // Arrange: Create published and unpublished grades
        $assignment1 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'max_score' => 100
        ]);
        
        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        $submission1 = Submission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $this->student->id,
            'submitted_at' => now()
        ]);
        
        $submission2 = Submission::factory()->create([
            'assignment_id' => $assignment2->id,
            'user_id' => $this->student->id,
            'submitted_at' => now()
        ]);

        Grade::factory()->create([
            'submission_id' => $submission1->id,
            'grader_id' => $this->teacher->id,
            'score' => 85,
            'is_published' => true
        ]);
        
        Grade::factory()->create([
            'submission_id' => $submission2->id,
            'grader_id' => $this->teacher->id,
            'score' => 90,
            'is_published' => false // Unpublished
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Only published grade should be included
        $this->assertEquals(85.0, $data['average_score']);
        $this->assertCount(1, $data['recent_grades']);
        $this->assertCount(1, $data['performance_trend']);
    }

    /**
     * Test that only active enrollments are counted
     */
    public function test_only_active_enrollments_are_counted(): void
    {
        // Arrange: Create another course with inactive enrollment
        $inactiveCourse = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Inactive Course'
        ]);
        
        Enrollment::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $inactiveCourse->id,
            'status' => 'inactive'
        ]);

        $activeAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);
        
        $inactiveAssignment = Assignment::factory()->create([
            'course_id' => $inactiveCourse->id,
            'is_published' => true
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Only active course assignment should be counted
        $this->assertEquals(1, $data['completion_progress']['total_assignments']);
    }

    /**
     * Test cache invalidation
     */
    public function test_cache_can_be_invalidated(): void
    {
        // Arrange: Get initial data
        $data1 = $this->analyticsService->getStudentDashboardData($this->student);

        // Act: Invalidate cache
        $this->analyticsService->invalidateStudentCache($this->student->id);

        // Assert: Cache should be cleared
        $cacheKey = "student_analytics_{$this->student->id}";
        $this->assertFalse(Cache::has($cacheKey));
    }

    /**
     * Test that draft assignments are excluded
     */
    public function test_draft_assignments_are_excluded(): void
    {
        // Arrange: Create published and draft assignments
        $publishedAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Published Assignment'
        ]);
        
        $draftAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => false,
            'title' => 'Draft Assignment'
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Only published assignment should be counted
        $this->assertEquals(1, $data['completion_progress']['total_assignments']);
    }

    /**
     * Test feedback preview in recent grades
     */
    public function test_recent_grades_include_feedback_preview(): void
    {
        // Arrange: Create grade with long feedback
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'submitted_at' => now()
        ]);

        $longFeedback = str_repeat('This is feedback. ', 20); // Long feedback
        
        Grade::factory()->create([
            'submission_id' => $submission->id,
            'grader_id' => $this->teacher->id,
            'score' => 85,
            'feedback' => $longFeedback,
            'is_published' => true
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getStudentDashboardData($this->student);

        // Assert: Feedback should be truncated to 100 characters
        $this->assertNotNull($data['recent_grades'][0]['feedback']);
        $this->assertLessThanOrEqual(100, strlen($data['recent_grades'][0]['feedback']));
    }
}
