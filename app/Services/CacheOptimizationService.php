<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheOptimizationService
{
    /**
     * Cache duration constants (in seconds)
     */
    const CACHE_SHORT = 300;    // 5 minutes
    const CACHE_MEDIUM = 1800;  // 30 minutes
    const CACHE_LONG = 3600;    // 1 hour
    const CACHE_VERY_LONG = 86400; // 24 hours

    /**
     * Get cached course data for a user.
     */
    public function getCachedUserCourses(User $user, string $role): array
    {
        $cacheKey = "user_courses_{$user->id}_{$role}";
        
        return Cache::remember($cacheKey, self::CACHE_MEDIUM, function () use ($user, $role) {
            if ($role === 'student') {
                return $user->enrollments()
                    ->with(['course.modules.subpages'])
                    ->get()
                    ->pluck('course')
                    ->toArray();
            } elseif ($role === 'teacher') {
                return Course::where('teacher_id', $user->id)
                    ->with(['modules.subpages', 'enrollments'])
                    ->get()
                    ->toArray();
            } else {
                return Course::with(['modules.subpages', 'enrollments'])
                    ->get()
                    ->toArray();
            }
        });
    }

    /**
     * Get cached course navigation for a specific course.
     */
    public function getCachedCourseNavigation(Course $course): array
    {
        $cacheKey = "course_navigation_{$course->id}";
        
        return Cache::remember($cacheKey, self::CACHE_LONG, function () use ($course) {
            return $course->modules()
                ->where('is_published', true)
                ->orderBy('order_index')
                ->with(['subpages' => function ($query) {
                    $query->where('is_active', true)->orderBy('order_index');
                }])
                ->get()
                ->toArray();
        });
    }

    /**
     * Get full cached course hierarchy (for students).
     */
    public function getCachedCourseHierarchy(Course $course): array
    {
        $cacheKey = "course_hierarchy_full_{$course->id}";
        
        return Cache::remember($cacheKey, self::CACHE_LONG, function () use ($course) {
            return $course->load([
                'modules' => function ($query) {
                    $query->where('is_published', true)->orderBy('order_index');
                },
                'modules.subpages' => function ($query) {
                    $query->where('is_active', true)->orderBy('order_index');
                },
                'modules.subpages.contentBlocks' => function ($query) {
                    $query->orderBy('order_index');
                }
            ])->toArray();
        });
    }

    /**
     * Get cached assignment statistics.
     */
    public function getCachedAssignmentStats(int $assignmentId): array
    {
        $cacheKey = "assignment_stats_{$assignmentId}";
        
        return Cache::remember($cacheKey, self::CACHE_SHORT, function () use ($assignmentId) {
            return DB::table('submissions')
                ->where('assignment_id', $assignmentId)
                ->selectRaw('
                    COUNT(*) as total_submissions,
                    COUNT(CASE WHEN status = "graded" THEN 1 END) as graded_submissions,
                    COUNT(CASE WHEN is_late = 1 THEN 1 END) as late_submissions,
                    AVG(CASE WHEN grades.score IS NOT NULL THEN grades.score END) as avg_score
                ')
                ->leftJoin('grades', 'submissions.id', '=', 'grades.submission_id')
                ->where('grades.is_published', true)
                ->first();
        });
    }

    /**
     * Get cached user dashboard data.
     */
    public function getCachedDashboardData(User $user): array
    {
        $cacheKey = "dashboard_data_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_SHORT, function () use ($user) {
            $role = $user->getRoleNames()->first();
            
            switch ($role) {
                case 'student':
                    return $this->getStudentDashboardData($user);
                case 'teacher':
                    return $this->getTeacherDashboardData($user);
                case 'admin':
                    return $this->getAdminDashboardData();
                default:
                    return [];
            }
        });
    }

    /**
     * Cache frequently accessed course content.
     */
    public function cacheFrequentContent(): void
    {
        // Cache popular courses
        $popularCourses = Course::withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(10)
            ->get();

        foreach ($popularCourses as $course) {
            $this->getCachedCourseNavigation($course);
        }

        // Cache active assignments
        $activeAssignments = DB::table('assignments')
            ->where('is_published', true)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($activeAssignments as $assignmentId) {
            $this->getCachedAssignmentStats($assignmentId);
        }
    }

    /**
     * Invalidate cache for a specific course.
     */
    public function invalidateCourseCache(Course $course): void
    {
        Cache::forget("course_navigation_{$course->id}");
        
        // Invalidate user caches for enrolled students and teacher
        $userIds = $course->enrollments()->pluck('user_id')->toArray();
        $userIds[] = $course->teacher_id;
        
        foreach ($userIds as $userId) {
            Cache::forget("user_courses_{$userId}_student");
            Cache::forget("user_courses_{$userId}_teacher");
            Cache::forget("dashboard_data_{$userId}");
        }
    }

    /**
     * Invalidate cache for assignment statistics.
     */
    public function invalidateAssignmentCache(int $assignmentId): void
    {
        Cache::forget("assignment_stats_{$assignmentId}");
    }

    /**
     * Invalidate user-specific caches.
     */
    public function invalidateUserCache(User $user): void
    {
        $role = $user->getRoleNames()->first();
        Cache::forget("user_courses_{$user->id}_{$role}");
        Cache::forget("dashboard_data_{$user->id}");
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStatistics(): array
    {
        try {
            $redis = Cache::store('redis');
            $keys = $redis->getRedis()->keys('*');
            
            $stats = [
                'total_keys' => count($keys),
                'memory_usage' => $redis->getRedis()->info('memory')['used_memory_human'] ?? 'Unknown',
                'hit_rate' => 'N/A', // Would need Redis monitoring for accurate hit rate
            ];
            
            // Categorize keys
            $categories = [
                'user_courses' => 0,
                'course_navigation' => 0,
                'assignment_stats' => 0,
                'dashboard_data' => 0,
                'other' => 0,
            ];
            
            foreach ($keys as $key) {
                if (strpos($key, 'user_courses_') === 0) {
                    $categories['user_courses']++;
                } elseif (strpos($key, 'course_navigation_') === 0) {
                    $categories['course_navigation']++;
                } elseif (strpos($key, 'assignment_stats_') === 0) {
                    $categories['assignment_stats']++;
                } elseif (strpos($key, 'dashboard_data_') === 0) {
                    $categories['dashboard_data']++;
                } else {
                    $categories['other']++;
                }
            }
            
            $stats['categories'] = $categories;
            
            return $stats;
        } catch (\Exception $e) {
            return [
                'total_keys' => 0,
                'memory_usage' => 'Unknown',
                'hit_rate' => 'N/A',
                'categories' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear all application caches.
     */
    public function clearAllCaches(): bool
    {
        try {
            // Clear known application cache keys instead of flushing everything
            // This preserves session data and framework caches
            $courses = Course::pluck('id', 'teacher_id');
            foreach ($courses as $teacherId => $courseId) {
                Cache::forget("course:modules:{$courseId}");
                Cache::forget("course:details:v2:{$courseId}");
                Cache::forget("course:students:{$courseId}");
                Cache::forget("course_navigation_{$courseId}");
                Cache::forget("course_hierarchy_full_{$courseId}");
                Cache::forget("teacher:courses:{$teacherId}");
                Cache::forget("teacher_dashboard_{$teacherId}");
                Cache::forget("user:accessible_courses:{$teacherId}");
            }
            Cache::forget("courses:published");
            Cache::forget("popular_courses");
            Cache::forget("system_settings");
            Cache::forget("admin_dashboard");
            
            // Clear user-specific dashboard caches
            $userIds = User::pluck('id');
            foreach ($userIds as $userId) {
                Cache::forget("student_dashboard_{$userId}");
                Cache::forget("teacher_dashboard_{$userId}");
                Cache::forget("dashboard_data_{$userId}");
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getStudentDashboardData(User $user): array
    {
        // Implementation would be similar to the dashboard controller
        return [];
    }

    private function getTeacherDashboardData(User $user): array
    {
        // Implementation would be similar to the dashboard controller
        return [];
    }

    private function getAdminDashboardData(): array
    {
        // Implementation would be similar to the dashboard controller
        return [];
    }
}