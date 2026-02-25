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
 * Test suite for AnalyticsService teacher dashboard data aggregation
 * 
 * Task 3.2: Implement teacher dashboard data aggregation
 * Requirements: 3 (Teacher Analytics), 14 (Performance Optimization)
 */
class AnalyticsServiceTeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $analyticsService;
    private User $teacher;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->analyticsService = new AnalyticsService();
        
        // Create test teacher and course
        $this->teacher = User::factory()->create();
        $this->course = Course::factory()->create([
            'teacher_id' => $this->teacher->id,
            'title' => 'Test Course'
        ]);
    }

    /**
     * Test that teacher dashboard data is returned with all required fields
     */
    public function test_teacher_dashboard_returns_all_required_fields(): void
    {
        // Arrange: Create test data
        $student = User::factory()->create();
        
        Enrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);

        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'submitted_at' => now()
        ]);

        Grade::factory()->create([
            'submission_id' => $submission->id,
            'grader_id' => $this->teacher->id,
            'score' => 85,
            'is_published' => true
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Verify all required fields are present
        $this->assertIsArray($data);
        $this->assertArrayHasKey('overall_completion_rate', $data);
        $this->assertArrayHasKey('average_score', $data);
        $this->assertArrayHasKey('submission_timeline', $data);
        $this->assertArrayHasKey('top_performers', $data);
        $this->assertArrayHasKey('at_risk_students', $data);
        $this->assertArrayHasKey('assignment_stats', $data);
    }

    /**
     * Test completion rate calculation accuracy
     */
    public function test_completion_rate_calculation_is_accurate(): void
    {
        // Arrange: Create 2 students and 2 assignments
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();
        
        Enrollment::factory()->create([
            'user_id' => $student1->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);
        
        Enrollment::factory()->create([
            'user_id' => $student2->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);

        $assignment1 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);
        
        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);

        // Student 1 submits both assignments, Student 2 submits only one
        Submission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $student1->id,
            'submitted_at' => now()
        ]);
        
        Submission::factory()->create([
            'assignment_id' => $assignment2->id,
            'user_id' => $student1->id,
            'submitted_at' => now()
        ]);
        
        Submission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $student2->id,
            'submitted_at' => now()
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Completion rate should be 75% (3 submissions out of 4 possible)
        $this->assertEquals(75.0, $data['overall_completion_rate']);
    }

    /**
     * Test average score calculation accuracy
     */
    public function test_average_score_calculation_is_accurate(): void
    {
        // Arrange: Create students with different scores
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();
        
        Enrollment::factory()->create([
            'user_id' => $student1->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);
        
        Enrollment::factory()->create([
            'user_id' => $student2->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);

        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        $submission1 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student1->id,
            'submitted_at' => now()
        ]);
        
        $submission2 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student2->id,
            'submitted_at' => now()
        ]);

        Grade::factory()->create([
            'submission_id' => $submission1->id,
            'grader_id' => $this->teacher->id,
            'score' => 80,
            'is_published' => true
        ]);
        
        Grade::factory()->create([
            'submission_id' => $submission2->id,
            'grader_id' => $this->teacher->id,
            'score' => 90,
            'is_published' => true
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Average score should be 85 ((80 + 90) / 2)
        $this->assertEquals(85.0, $data['average_score']);
    }

    /**
     * Test top performers identification
     */
    public function test_top_performers_are_correctly_identified(): void
    {
        // Arrange: Create students with different scores
        $students = [];
        $scores = [95, 88, 92, 75, 85]; // Top 3 should be: 95, 92, 88
        
        foreach ($scores as $index => $score) {
            $student = User::factory()->create([
                'name' => "Student $index"
            ]);
            $students[] = $student;
            
            Enrollment::factory()->create([
                'user_id' => $student->id,
                'course_id' => $this->course->id,
                'status' => 'active'
            ]);

            $assignment = Assignment::factory()->create([
                'course_id' => $this->course->id,
                'is_published' => true,
                'max_score' => 100
            ]);

            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
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
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Top performers should be ordered by score
        $this->assertCount(5, $data['top_performers']);
        $this->assertEquals(95.0, $data['top_performers'][0]['average_score']);
        $this->assertEquals(92.0, $data['top_performers'][1]['average_score']);
        $this->assertEquals(88.0, $data['top_performers'][2]['average_score']);
    }

    /**
     * Test at-risk students identification
     */
    public function test_at_risk_students_are_correctly_identified(): void
    {
        // Arrange: Create students with low scores (below 60)
        $lowScoreStudent = User::factory()->create([
            'name' => 'Low Score Student'
        ]);
        
        $highScoreStudent = User::factory()->create([
            'name' => 'High Score Student'
        ]);
        
        Enrollment::factory()->create([
            'user_id' => $lowScoreStudent->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);
        
        Enrollment::factory()->create([
            'user_id' => $highScoreStudent->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);

        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'max_score' => 100
        ]);

        $submission1 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $lowScoreStudent->id,
            'submitted_at' => now()
        ]);
        
        $submission2 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $highScoreStudent->id,
            'submitted_at' => now()
        ]);

        Grade::factory()->create([
            'submission_id' => $submission1->id,
            'grader_id' => $this->teacher->id,
            'score' => 45, // Below 60 - at risk
            'is_published' => true
        ]);
        
        Grade::factory()->create([
            'submission_id' => $submission2->id,
            'grader_id' => $this->teacher->id,
            'score' => 85, // Above 60 - not at risk
            'is_published' => true
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Only low score student should be at risk
        $this->assertCount(1, $data['at_risk_students']);
        $this->assertEquals(45.0, $data['at_risk_students'][0]['average_score']);
        $this->assertEquals('Low Score Student', $data['at_risk_students'][0]['student_name']);
    }

    /**
     * Test submission timeline aggregation
     */
    public function test_submission_timeline_aggregates_correctly(): void
    {
        // Arrange: Create submissions on different dates
        $student = User::factory()->create();
        Enrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);

        // Create 3 different assignments for today's submissions
        for ($i = 0; $i < 3; $i++) {
            $assignment = Assignment::factory()->create([
                'course_id' => $this->course->id,
                'is_published' => true
            ]);
            
            Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'submitted_at' => now(),
                'attempt_number' => 1
            ]);
        }
        
        // Create 2 different assignments for yesterday's submissions
        for ($i = 0; $i < 2; $i++) {
            $assignment = Assignment::factory()->create([
                'course_id' => $this->course->id,
                'is_published' => true
            ]);
            
            Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'submitted_at' => now()->subDay(),
                'attempt_number' => 1
            ]);
        }

        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Timeline should show correct counts
        $this->assertIsArray($data['submission_timeline']);
        $this->assertGreaterThan(0, count($data['submission_timeline']));
    }

    /**
     * Test assignment statistics aggregation
     */
    public function test_assignment_stats_include_all_assignments(): void
    {
        // Arrange: Create multiple assignments
        $assignment1 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Assignment 1'
        ]);
        
        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'title' => 'Assignment 2'
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Stats should include both assignments
        $this->assertCount(2, $data['assignment_stats']);
        $this->assertEquals('Assignment 1', $data['assignment_stats'][0]['title']);
        $this->assertEquals('Assignment 2', $data['assignment_stats'][1]['title']);
    }

    /**
     * Test caching functionality
     */
    public function test_dashboard_data_is_cached(): void
    {
        // Arrange: Create test data
        $student = User::factory()->create();
        Enrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);

        // Act: Call twice
        Cache::flush(); // Clear cache first
        $data1 = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);
        $data2 = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Data should be identical (from cache)
        $this->assertEquals($data1, $data2);
        
        // Verify cache key exists
        $cacheKey = "teacher_analytics_{$this->teacher->id}_{$this->course->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * Test error handling when course has no data
     */
    public function test_handles_empty_course_gracefully(): void
    {
        // Arrange: Empty course (no assignments, no enrollments)
        
        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Should return zero values, not errors
        $this->assertEquals(0.0, $data['overall_completion_rate']);
        $this->assertEquals(0.0, $data['average_score']);
        $this->assertIsArray($data['submission_timeline']);
        $this->assertIsArray($data['top_performers']);
        $this->assertIsArray($data['at_risk_students']);
        $this->assertIsArray($data['assignment_stats']);
    }

    /**
     * Test that only published assignments are included
     */
    public function test_only_published_assignments_are_included(): void
    {
        // Arrange: Create published and unpublished assignments
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
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Only published assignment should be in stats
        $this->assertCount(1, $data['assignment_stats']);
        $this->assertEquals('Published Assignment', $data['assignment_stats'][0]['title']);
    }

    /**
     * Test that only active enrollments are counted
     */
    public function test_only_active_enrollments_are_counted(): void
    {
        // Arrange: Create active and inactive enrollments
        $activeStudent = User::factory()->create();
        $inactiveStudent = User::factory()->create();
        
        Enrollment::factory()->create([
            'user_id' => $activeStudent->id,
            'course_id' => $this->course->id,
            'status' => 'active'
        ]);
        
        Enrollment::factory()->create([
            'user_id' => $inactiveStudent->id,
            'course_id' => $this->course->id,
            'status' => 'inactive'
        ]);

        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true
        ]);

        // Only active student submits
        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $activeStudent->id,
            'submitted_at' => now(),
            'attempt_number' => 1
        ]);

        // Act: Get dashboard data
        $data = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Assert: Completion rate should be 100% (1 submission / 1 active student * 1 assignment)
        $this->assertEquals(100.0, $data['overall_completion_rate']);
    }

    /**
     * Test cache invalidation
     */
    public function test_cache_can_be_invalidated(): void
    {
        // Arrange: Get initial data
        $data1 = $this->analyticsService->getTeacherDashboardData($this->teacher, $this->course);

        // Act: Invalidate cache
        $this->analyticsService->invalidateTeacherCache($this->teacher->id, $this->course->id);

        // Assert: Cache should be cleared
        $cacheKey = "teacher_analytics_{$this->teacher->id}_{$this->course->id}";
        $this->assertFalse(Cache::has($cacheKey));
    }
}
