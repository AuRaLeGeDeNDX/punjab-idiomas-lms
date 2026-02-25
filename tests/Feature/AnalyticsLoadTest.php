<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Grade;
use App\Models\Enrollment;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AnalyticsLoadTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = app(AnalyticsService::class);
        
        // Create roles
        \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        \Spatie\Permission\Models\Role::create(['name' => 'Teacher']);
        \Spatie\Permission\Models\Role::create(['name' => 'Student']);
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
     * Test dashboard performance with 1000+ assignments
     * 
     * Requirements: Dashboard should load in under 2 seconds
     * 
     * @group load
     * @group slow
     */
    public function test_teacher_dashboard_loads_under_2_seconds_with_1000_assignments(): void
    {
        // Arrange: Create large dataset
        $teacher = $this->createUserWithRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        // Create 100 students
        $students = [];
        for ($i = 0; $i < 100; $i++) {
            $student = $this->createUserWithRole('Student');
            $students[] = $student;
            Enrollment::factory()->create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
        }
        
        // Create 1000 assignments
        $assignments = Assignment::factory()->count(1000)->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);
        
        // Create submissions for 50% of assignments (50,000 submissions)
        $submissionCount = 0;
        foreach ($assignments->take(500) as $assignment) {
            foreach (collect($students)->take(50) as $student) {
                Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id
                ]);
                $submissionCount++;
            }
        }
        
        // Create grades for 80% of submissions
        $submissions = Submission::inRandomOrder()->limit(40000)->get();
        foreach ($submissions as $submission) {
            Grade::factory()->create([
                'submission_id' => $submission->id,
                'grader_id' => $teacher->id,
                'is_published' => true
            ]);
        }
        
        // Clear any existing cache
        Cache::flush();
        
        // Act: Measure dashboard load time
        $startTime = microtime(true);
        
        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        // Assert: Load time should be under 2 seconds
        $this->assertLessThan(2.0, $loadTime, 
            "Dashboard load time ({$loadTime}s) exceeded 2 second threshold");
        
        // Verify data integrity
        $this->assertIsArray($data);
        $this->assertArrayHasKey('overall_completion_rate', $data);
        $this->assertArrayHasKey('average_score', $data);
        $this->assertArrayHasKey('submission_timeline', $data);
        $this->assertArrayHasKey('top_performers', $data);
        $this->assertArrayHasKey('at_risk_students', $data);
        
        // Verify cache was populated
        $cacheKey = "teacher_analytics_{$teacher->id}_{$course->id}";
        $this->assertTrue(Cache::has($cacheKey), 'Dashboard data should be cached');
        
        // Test cached load time (should be much faster)
        $cachedStartTime = microtime(true);
        $cachedData = $this->analyticsService->getTeacherDashboardData($teacher, $course);
        $cachedEndTime = microtime(true);
        $cachedLoadTime = $cachedEndTime - $cachedStartTime;
        
        $this->assertLessThan(0.1, $cachedLoadTime, 
            "Cached dashboard load time ({$cachedLoadTime}s) should be under 0.1 seconds");
        
        echo "\n";
        echo "Load Test Results:\n";
        echo "==================\n";
        echo "Dataset: 1000 assignments, 100 students, {$submissionCount} submissions\n";
        echo "Initial load time: " . number_format($loadTime, 3) . "s\n";
        echo "Cached load time: " . number_format($cachedLoadTime, 3) . "s\n";
        echo "Performance improvement: " . number_format(($loadTime / $cachedLoadTime), 1) . "x faster\n";
    }

    /**
     * Test student dashboard performance with large dataset
     * 
     * @group load
     * @group slow
     */
    public function test_student_dashboard_loads_under_2_seconds_with_large_dataset(): void
    {
        // Arrange: Create student with many courses and assignments
        $student = $this->createUserWithRole('Student');
        
        // Enroll in 10 courses
        $courses = Course::factory()->count(10)->create();
        foreach ($courses as $course) {
            Enrollment::factory()->create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
            
            // Create 100 assignments per course (1000 total)
            $assignments = Assignment::factory()->count(100)->create([
                'course_id' => $course->id,
                'is_published' => true
            ]);
            
            // Submit 70% of assignments
            foreach ($assignments->take(70) as $assignment) {
                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id
                ]);
                
                // Grade 80% of submissions
                if (rand(1, 100) <= 80) {
                    Grade::factory()->create([
                        'submission_id' => $submission->id,
                        'grader_id' => $course->teacher_id,
                        'is_published' => true
                    ]);
                }
            }
        }
        
        // Clear cache
        Cache::flush();
        
        // Act: Measure dashboard load time
        $startTime = microtime(true);
        
        $data = $this->analyticsService->getStudentDashboardData($student);
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        // Assert: Load time should be under 2 seconds
        $this->assertLessThan(2.0, $loadTime, 
            "Student dashboard load time ({$loadTime}s) exceeded 2 second threshold");
        
        // Verify data integrity
        $this->assertIsArray($data);
        $this->assertArrayHasKey('completion_progress', $data);
        $this->assertArrayHasKey('average_score', $data);
        $this->assertArrayHasKey('upcoming_assignments', $data);
        $this->assertArrayHasKey('overdue_assignments', $data);
        $this->assertArrayHasKey('recent_grades', $data);
        
        echo "\n";
        echo "Student Dashboard Load Test Results:\n";
        echo "====================================\n";
        echo "Dataset: 10 courses, 1000 assignments, 700 submissions\n";
        echo "Load time: " . number_format($loadTime, 3) . "s\n";
    }

    /**
     * Test assignment analytics with many students
     * 
     * @group load
     * @group slow
     */
    public function test_assignment_analytics_loads_under_2_seconds_with_500_students(): void
    {
        // Arrange: Create assignment with 500 students
        $teacher = $this->createUserWithRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);
        
        // Create 500 students
        for ($i = 0; $i < 500; $i++) {
            $student = $this->createUserWithRole('Student');
            Enrollment::factory()->create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
            
            // 80% submission rate
            if (rand(1, 100) <= 80) {
                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id
                ]);
                
                // Grade all submissions
                Grade::factory()->create([
                    'submission_id' => $submission->id,
                    'grader_id' => $teacher->id,
                    'is_published' => true
                ]);
            }
        }
        
        // Clear cache
        Cache::flush();
        
        // Act: Measure analytics load time
        $startTime = microtime(true);
        
        $data = $this->analyticsService->getAssignmentAnalytics($assignment);
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        // Assert: Load time should be under 2 seconds
        $this->assertLessThan(2.0, $loadTime, 
            "Assignment analytics load time ({$loadTime}s) exceeded 2 second threshold");
        
        // Verify data integrity
        $this->assertIsArray($data);
        $this->assertArrayHasKey('completion_rate', $data);
        $this->assertArrayHasKey('average_score', $data);
        $this->assertArrayHasKey('grade_distribution', $data);
        $this->assertArrayHasKey('student_list', $data);
        
        echo "\n";
        echo "Assignment Analytics Load Test Results:\n";
        echo "=======================================\n";
        echo "Dataset: 500 students, 400 submissions\n";
        echo "Load time: " . number_format($loadTime, 3) . "s\n";
    }

    /**
     * Test query efficiency with N+1 prevention
     * 
     * @group load
     */
    public function test_analytics_queries_use_eager_loading(): void
    {
        // Arrange: Create dataset
        $teacher = $this->createUserWithRole('Teacher');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $students = [];
        for ($i = 0; $i < 50; $i++) {
            $student = $this->createUserWithRole('Student');
            $students[] = $student;
            Enrollment::factory()->create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
        }
        
        $assignments = Assignment::factory()->count(100)->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);
        
        foreach ($assignments->take(50) as $assignment) {
            foreach (collect($students)->take(25) as $student) {
                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id
                ]);
                
                Grade::factory()->create([
                    'submission_id' => $submission->id,
                    'grader_id' => $teacher->id,
                    'is_published' => true
                ]);
            }
        }
        
        // Act: Count queries
        \DB::enableQueryLog();
        
        $data = $this->analyticsService->getTeacherDashboardData($teacher, $course);
        
        $queries = \DB::getQueryLog();
        $queryCount = count($queries);
        
        \DB::disableQueryLog();
        
        // Assert: Query count should be reasonable (not N+1)
        // With proper eager loading, should be under 20 queries
        $this->assertLessThan(20, $queryCount, 
            "Query count ({$queryCount}) suggests N+1 problem. Expected < 20 queries.");
        
        echo "\n";
        echo "Query Efficiency Test Results:\n";
        echo "==============================\n";
        echo "Dataset: 100 assignments, 50 students, 1250 submissions\n";
        echo "Total queries: {$queryCount}\n";
        echo "Eager loading: " . ($queryCount < 20 ? "✓ PASS" : "✗ FAIL") . "\n";
    }
}
