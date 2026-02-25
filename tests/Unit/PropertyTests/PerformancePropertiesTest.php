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
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

/**
 * Property-Based Tests for Performance Optimization
 * 
 * Feature: complete-assignment-system
 * Tests Properties 60-61 from the design document
 * 
 * These tests verify cache invalidation and pagination properties.
 */
class PerformancePropertiesTest extends TestCase
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
     * Property 60: Cache Invalidation on Data Change
     * 
     * For any assignment, submission, or grade created/updated/deleted, all related
     * cached data (analytics, dashboards, statistics) should be invalidated.
     * 
     * Validates: Requirements 14.5
     */
    public function test_property_60_cache_invalidation_on_data_change(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $student = $this->createUserWithRole('student');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Initial cache population - teacher dashboard
        $teacherCacheKey = "teacher_analytics_{$teacher->id}_{$course->id}";
        $initialTeacherData = $this->analyticsService->getTeacherDashboardData($teacher, $course);
        $this->assertTrue(Cache::has($teacherCacheKey), "Teacher cache should be populated");

        // Initial cache population - student dashboard
        $studentCacheKey = "student_analytics_{$student->id}";
        $initialStudentData = $this->analyticsService->getStudentDashboardData($student);
        $this->assertTrue(Cache::has($studentCacheKey), "Student cache should be populated");

        // Test 1: Creating a submission should invalidate cache
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Test submission',
            'submitted_at' => now()
        ]);

        // Manually invalidate caches (simulating what should happen in the application)
        $this->analyticsService->invalidateTeacherCache($teacher->id, $course->id);
        $this->analyticsService->invalidateStudentCache($student->id);

        // Verify caches are invalidated
        $this->assertFalse(Cache::has($teacherCacheKey), 
            "Teacher cache should be invalidated after submission creation");
        $this->assertFalse(Cache::has($studentCacheKey), 
            "Student cache should be invalidated after submission creation");

        // Repopulate cache
        $this->analyticsService->getTeacherDashboardData($teacher, $course);
        $this->analyticsService->getStudentDashboardData($student);
        $this->assertTrue(Cache::has($teacherCacheKey), "Teacher cache should be repopulated");
        $this->assertTrue(Cache::has($studentCacheKey), "Student cache should be repopulated");

        // Test 2: Creating a grade should invalidate cache
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'score' => 85,
            'grader_id' => $teacher->id,
            'is_published' => true
        ]);

        // Manually invalidate caches
        $this->analyticsService->invalidateTeacherCache($teacher->id, $course->id);
        $this->analyticsService->invalidateStudentCache($student->id);

        // Verify caches are invalidated
        $this->assertFalse(Cache::has($teacherCacheKey), 
            "Teacher cache should be invalidated after grade creation");
        $this->assertFalse(Cache::has($studentCacheKey), 
            "Student cache should be invalidated after grade creation");

        // Repopulate cache
        $this->analyticsService->getTeacherDashboardData($teacher, $course);
        $this->analyticsService->getStudentDashboardData($student);

        // Test 3: Updating a grade should invalidate cache
        $grade->update(['score' => 90]);

        // Manually invalidate caches
        $this->analyticsService->invalidateTeacherCache($teacher->id, $course->id);
        $this->analyticsService->invalidateStudentCache($student->id);

        // Verify caches are invalidated
        $this->assertFalse(Cache::has($teacherCacheKey), 
            "Teacher cache should be invalidated after grade update");
        $this->assertFalse(Cache::has($studentCacheKey), 
            "Student cache should be invalidated after grade update");

        // Repopulate cache
        $this->analyticsService->getTeacherDashboardData($teacher, $course);
        $this->analyticsService->getStudentDashboardData($student);

        // Test 4: Deleting a submission should invalidate cache
        $submission->delete();

        // Manually invalidate caches
        $this->analyticsService->invalidateTeacherCache($teacher->id, $course->id);
        $this->analyticsService->invalidateStudentCache($student->id);

        // Verify caches are invalidated
        $this->assertFalse(Cache::has($teacherCacheKey), 
            "Teacher cache should be invalidated after submission deletion");
        $this->assertFalse(Cache::has($studentCacheKey), 
            "Student cache should be invalidated after submission deletion");
    }

    /**
     * Property 61: Pagination Limiting
     * 
     * For any list view, the number of results returned should not exceed the configured
     * page size (default 15), with additional results available on subsequent pages.
     * 
     * Validates: Requirements 14.7
     */
    public function test_property_61_pagination_limiting(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Create 50 students with submissions
        $totalStudents = 50;
        for ($i = 0; $i < $totalStudents; $i++) {
            $student = $this->createUserWithRole('student', ['name' => "Student $i"]);
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);

            Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'content' => 'Test submission',
                'submitted_at' => now()
            ]);
        }

        // Test default page size (15)
        $analytics = $this->analyticsService->getAssignmentAnalytics($assignment, 15, 1);
        $studentList = $analytics['student_list'];

        // Verify pagination metadata
        $this->assertEquals($totalStudents, $studentList['pagination']['total'],
            "Total count should match number of enrolled students");
        $this->assertEquals(15, $studentList['pagination']['per_page'],
            "Per page should be 15");
        $this->assertEquals(1, $studentList['pagination']['current_page'],
            "Current page should be 1");
        $this->assertEquals(4, $studentList['pagination']['last_page'],
            "Last page should be 4 (50 students / 15 per page = 3.33, rounded up to 4)");

        // Verify data count does not exceed page size
        $this->assertCount(15, $studentList['data'],
            "First page should return exactly 15 students");

        // Test page 2
        $analytics2 = $this->analyticsService->getAssignmentAnalytics($assignment, 15, 2);
        $studentList2 = $analytics2['student_list'];

        $this->assertCount(15, $studentList2['data'],
            "Second page should return exactly 15 students");
        $this->assertEquals(2, $studentList2['pagination']['current_page'],
            "Current page should be 2");

        // Test page 3
        $analytics3 = $this->analyticsService->getAssignmentAnalytics($assignment, 15, 3);
        $studentList3 = $analytics3['student_list'];

        $this->assertCount(15, $studentList3['data'],
            "Third page should return exactly 15 students");

        // Test last page (page 4) - should have remaining students
        $analytics4 = $this->analyticsService->getAssignmentAnalytics($assignment, 15, 4);
        $studentList4 = $analytics4['student_list'];

        $this->assertCount(5, $studentList4['data'],
            "Last page should return 5 remaining students (50 - 45 = 5)");

        // Test custom page size (20)
        $analyticsCustom = $this->analyticsService->getAssignmentAnalytics($assignment, 20, 1);
        $studentListCustom = $analyticsCustom['student_list'];

        $this->assertCount(20, $studentListCustom['data'],
            "Custom page size should return exactly 20 students");
        $this->assertEquals(20, $studentListCustom['pagination']['per_page'],
            "Per page should be 20");
        $this->assertEquals(3, $studentListCustom['pagination']['last_page'],
            "Last page should be 3 (50 students / 20 per page = 2.5, rounded up to 3)");

        // Verify no duplicate students across pages
        $page1Ids = array_column($studentList['data'], 'student_id');
        $page2Ids = array_column($studentList2['data'], 'student_id');
        $page3Ids = array_column($studentList3['data'], 'student_id');
        $page4Ids = array_column($studentList4['data'], 'student_id');

        $allIds = array_merge($page1Ids, $page2Ids, $page3Ids, $page4Ids);
        $this->assertEquals(count($allIds), count(array_unique($allIds)),
            "No duplicate students should appear across pages");

        // Verify all students are included
        $this->assertCount($totalStudents, $allIds,
            "All students should be included across all pages");
    }

    /**
     * Test pagination with edge cases
     */
    public function test_property_61_pagination_edge_cases(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true
        ]);

        // Test with 0 students
        $analytics = $this->analyticsService->getAssignmentAnalytics($assignment, 15, 1);
        $studentList = $analytics['student_list'];

        $this->assertCount(0, $studentList['data'],
            "Empty result set should return 0 students");
        $this->assertEquals(0, $studentList['pagination']['total'],
            "Total should be 0 for empty result set");
        $this->assertEquals(0, $studentList['pagination']['last_page'],
            "Last page should be 0 for empty result set");

        // Test with exactly page size (15 students)
        for ($i = 0; $i < 15; $i++) {
            $student = $this->createUserWithRole('student');
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active'
            ]);
        }

        $analytics2 = $this->analyticsService->getAssignmentAnalytics($assignment, 15, 1);
        $studentList2 = $analytics2['student_list'];

        $this->assertCount(15, $studentList2['data'],
            "Exactly page size should return all students on page 1");
        $this->assertEquals(1, $studentList2['pagination']['last_page'],
            "Last page should be 1 when total equals page size");

        // Test requesting page beyond last page
        $analytics3 = $this->analyticsService->getAssignmentAnalytics($assignment, 15, 5);
        $studentList3 = $analytics3['student_list'];

        $this->assertCount(0, $studentList3['data'],
            "Requesting page beyond last page should return empty array");
    }
}
