<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AuthorizesRequests;
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display reports dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', User::class); // Admin only
        
        $reportType = $request->get('type', 'overview');
        $dateRange = $request->get('range', '30');
        
        $data = match($reportType) {
            'users' => $this->getUsersReport($dateRange),
            'courses' => $this->getCoursesReport($dateRange),
            'enrollments' => $this->getEnrollmentsReport($dateRange),
            'activity' => $this->getActivityReport($dateRange),
            default => $this->getOverviewReport($dateRange)
        };
        
        if ($request->expectsJson()) {
            return response()->json($data);
        }
        
        return view('admin.reports.index', compact('data', 'reportType', 'dateRange'));
    }

    /**
     * Generate and download report.
     */
    public function download(Request $request, string $type): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('viewAny', User::class);
        
        $dateRange = $request->get('range', '30');
        $format = $request->get('format', 'csv');
        
        $data = match($type) {
            'users' => $this->getUsersReport($dateRange),
            'courses' => $this->getCoursesReport($dateRange),
            'enrollments' => $this->getEnrollmentsReport($dateRange),
            'activity' => $this->getActivityReport($dateRange),
            default => $this->getOverviewReport($dateRange)
        };
        
        $filename = $this->generateReport($type, $data, $format);
        
        return response()->download($filename)->deleteFileAfterSend();
    }

    /**
     * Get overview report data.
     */
    private function getOverviewReport(string $dateRange): array
    {
        $days = (int) $dateRange;
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'summary' => [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'new_users' => User::where('created_at', '>=', $startDate)->count(),
                'total_courses' => Course::count(),
                'published_courses' => Course::where('is_published', true)->count(),
                'total_enrollments' => Enrollment::count(),
                'active_enrollments' => Enrollment::where('status', 'active')->count(),
                'completion_rate' => $this->getCompletionRate($startDate)
            ],
            'charts' => [
                'user_growth' => $this->getUserGrowthChart($days),
                'enrollment_trends' => $this->getEnrollmentTrendsChart($days),
                'course_popularity' => $this->getCoursePopularityChart($days),
                'user_activity' => $this->getUserActivityChart($days)
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'days' => $days
            ]
        ];
    }

    /**
     * Get users report data.
     */
    private function getUsersReport(string $dateRange): array
    {
        $days = (int) $dateRange;
        $startDate = Carbon::now()->subDays($days);
        
        $userStats = $this->userService->getUserStatistics();
        
        return [
            'summary' => $userStats,
            'registrations_by_day' => $this->getRegistrationsByDay($days),
            'users_by_role' => [
                'students' => User::role('Student')->count(),
                'teachers' => User::role('Teacher')->count(),
                'admins' => User::role('Admin')->count()
            ],
            'activity_status' => [
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'never_logged_in' => User::whereNull('last_login_at')->count(),
                'recent_login' => User::where('last_login_at', '>=', Carbon::now()->subDays(7))->count()
            ],
            'top_active_users' => $this->getTopActiveUsers(10),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'days' => $days
            ]
        ];
    }

    /**
     * Get courses report data.
     */
    private function getCoursesReport(string $dateRange): array
    {
        $days = (int) $dateRange;
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'summary' => [
                'total_courses' => Course::count(),
                'published_courses' => Course::where('is_published', true)->count(),
                'draft_courses' => Course::where('is_published', false)->count(),
                'new_courses' => Course::where('created_at', '>=', $startDate)->count(),
                'avg_enrollments_per_course' => round(Enrollment::count() / max(Course::count(), 1), 2)
            ],
            'popular_courses' => $this->getPopularCourses(10),
            'courses_by_teacher' => $this->getCoursesByTeacher(),
            'enrollment_distribution' => $this->getEnrollmentDistribution(),
            'completion_rates' => $this->getCourseCompletionRates(),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'days' => $days
            ]
        ];
    }

    /**
     * Get enrollments report data.
     */
    private function getEnrollmentsReport(string $dateRange): array
    {
        $days = (int) $dateRange;
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'summary' => [
                'total_enrollments' => Enrollment::count(),
                'active_enrollments' => Enrollment::where('status', 'active')->count(),
                'completed_enrollments' => Enrollment::where('status', 'completed')->count(),
                'dropped_enrollments' => Enrollment::where('status', 'dropped')->count(),
                'new_enrollments' => Enrollment::where('created_at', '>=', $startDate)->count(),
                'avg_progress' => round(Enrollment::avg('progress_percentage') ?? 0, 2)
            ],
            'enrollments_by_day' => $this->getEnrollmentsByDay($days),
            'enrollments_by_status' => $this->getEnrollmentsByStatus(),
            'progress_distribution' => $this->getProgressDistribution(),
            'top_performing_students' => $this->getTopPerformingStudents(10),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'days' => $days
            ]
        ];
    }

    /**
     * Get activity report data.
     */
    private function getActivityReport(string $dateRange): array
    {
        $days = (int) $dateRange;
        $startDate = Carbon::now()->subDays($days);
        
        return [
            'summary' => [
                'daily_active_users' => $this->getDailyActiveUsers($days),
                'weekly_active_users' => $this->getWeeklyActiveUsers(),
                'monthly_active_users' => $this->getMonthlyActiveUsers(),
                'peak_activity_hour' => $this->getPeakActivityHour(),
                'avg_session_duration' => $this->getAverageSessionDuration()
            ],
            'activity_by_day' => $this->getActivityByDay($days),
            'activity_by_hour' => $this->getActivityByHour($days),
            'most_accessed_courses' => $this->getMostAccessedCourses(10),
            'user_engagement' => $this->getUserEngagementMetrics($days),
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'days' => $days
            ]
        ];
    }

    // Helper methods for data generation
    private function getCompletionRate(Carbon $startDate): float
    {
        $totalEnrollments = Enrollment::where('created_at', '>=', $startDate)->count();
        $completedEnrollments = Enrollment::where('created_at', '>=', $startDate)
            ->where('status', 'completed')->count();
        
        return $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 2) : 0;
    }

    private function getUserGrowthChart(int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getEnrollmentTrendsChart(int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Enrollment::whereDate('created_at', $date)->count();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getCoursePopularityChart(int $days): array
    {
        return Course::withCount(['enrollments' => function ($query) use ($days) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }])
        ->orderByDesc('enrollments_count')
        ->limit(10)
        ->get(['title', 'enrollments_count'])
        ->toArray();
    }

    private function getUserActivityChart(int $days): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::where('last_login_at', '>=', $date->startOfDay())
                ->where('last_login_at', '<=', $date->endOfDay())
                ->count();
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'active_users' => $count
            ];
        }
        return $data;
    }

    private function getRegistrationsByDay(int $days): array
    {
        return DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getTopActiveUsers(int $limit): array
    {
        return User::with('roles')
            ->whereNotNull('last_login_at')
            ->orderByDesc('last_login_at')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'last_login_at'])
            ->toArray();
    }

    private function getPopularCourses(int $limit): array
    {
        return Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get(['id', 'title', 'code', 'enrollments_count'])
            ->toArray();
    }

    private function getCoursesByTeacher(): array
    {
        return DB::table('courses')
            ->join('users', 'courses.teacher_id', '=', 'users.id')
            ->select('users.name as teacher_name', DB::raw('COUNT(*) as course_count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('course_count')
            ->get()
            ->toArray();
    }

    private function getEnrollmentDistribution(): array
    {
        return Course::withCount('enrollments')
            ->get()
            ->groupBy(function ($course) {
                $count = $course->enrollments_count;
                if ($count == 0) return '0';
                if ($count <= 10) return '1-10';
                if ($count <= 50) return '11-50';
                if ($count <= 100) return '51-100';
                return '100+';
            })
            ->map(fn($group) => $group->count())
            ->toArray();
    }

    private function getCourseCompletionRates(): array
    {
        return Course::withCount(['enrollments as total_enrollments', 'enrollments as completed_enrollments' => function ($query) {
            $query->where('status', 'completed');
        }])
        ->get(['id', 'title']) // Only fetch needed columns
        ->map(function ($course) {
            $total = $course->total_enrollments;
            $completed = $course->completed_enrollments;
            return [
                'course_title' => $course->title,
                'total_enrollments' => $total,
                'completed' => $completed,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
            ];
        })
        ->sortByDesc('completion_rate')
        ->values()
        ->toArray();
    }

    private function getEnrollmentsByDay(int $days): array
    {
        return DB::table('enrollments')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getEnrollmentsByStatus(): array
    {
        return DB::table('enrollments')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getProgressDistribution(): array
    {
        return Enrollment::get()
            ->groupBy(function ($enrollment) {
                $progress = $enrollment->progress_percentage ?? 0;
                if ($progress == 0) return '0%';
                if ($progress <= 25) return '1-25%';
                if ($progress <= 50) return '26-50%';
                if ($progress <= 75) return '51-75%';
                if ($progress <= 99) return '76-99%';
                return '100%';
            })
            ->map(fn($group) => $group->count())
            ->toArray();
    }

    private function getTopPerformingStudents(int $limit): array
    {
        // Use withAvg to calculate average progress in DB instead of loading all users
        // Note: this assumes Laravel 8+ 'withAvg'
        return User::role('Student')
            ->withCount(['enrollments as completed_courses' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->withCount('enrollments as total_enrollments')
            ->withAvg('enrollments as avg_progress', 'progress_percentage')
            ->orderByDesc('avg_progress')
            ->limit($limit)
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'avg_progress' => round($user->avg_progress ?? 0, 2),
                    'completed_courses' => $user->completed_courses,
                    'total_enrollments' => $user->total_enrollments
                ];
            })
            ->toArray();
    }

    // Placeholder methods for activity metrics (would need proper session tracking)
    private function getDailyActiveUsers(int $days): int
    {
        return User::where('last_login_at', '>=', Carbon::now()->subDay())->count();
    }

    private function getWeeklyActiveUsers(): int
    {
        return User::where('last_login_at', '>=', Carbon::now()->subWeek())->count();
    }

    private function getMonthlyActiveUsers(): int
    {
        return User::where('last_login_at', '>=', Carbon::now()->subMonth())->count();
    }

    private function getPeakActivityHour(): int
    {
        // This would require proper session/activity tracking
        return 14; // 2 PM as placeholder
    }

    private function getAverageSessionDuration(): string
    {
        // This would require proper session tracking
        return '25 minutes'; // Placeholder
    }

    private function getActivityByDay(int $days): array
    {
        // Placeholder - would need proper activity tracking
        return [];
    }

    private function getActivityByHour(int $days): array
    {
        // Placeholder - would need proper activity tracking
        return [];
    }

    private function getMostAccessedCourses(int $limit): array
    {
        return Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get(['title', 'enrollments_count'])
            ->toArray();
    }

    private function getUserEngagementMetrics(int $days): array
    {
        // Placeholder - would need proper engagement tracking
        return [
            'avg_courses_per_user' => round(Enrollment::count() / max(User::role('Student')->count(), 1), 2),
            'avg_completion_time' => '3.5 weeks',
            'retention_rate' => 85.5
        ];
    }

    /**
     * Generate report file.
     */
    private function generateReport(string $type, array $data, string $format): string
    {
        $filename = storage_path("app/reports/{$type}_report_" . date('Y-m-d_H-i-s') . ".{$format}");
        
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }
        
        if ($format === 'csv') {
            $this->generateCSVReport($filename, $data);
        } else {
            $this->generateJSONReport($filename, $data);
        }
        
        return $filename;
    }

    private function generateCSVReport(string $filename, array $data): void
    {
        $file = fopen($filename, 'w');
        
        // Write summary data
        if (isset($data['summary'])) {
            fputcsv($file, ['Summary']);
            foreach ($data['summary'] as $key => $value) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $key)), $value]);
            }
            fputcsv($file, []); // Empty row
        }
        
        // Write other data sections
        foreach ($data as $section => $sectionData) {
            if ($section === 'summary' || $section === 'period') continue;
            
            fputcsv($file, [ucwords(str_replace('_', ' ', $section))]);
            
            if (is_array($sectionData) && !empty($sectionData)) {
                $firstItem = reset($sectionData);
                if (is_array($firstItem)) {
                    // Write headers
                    fputcsv($file, array_keys($firstItem));
                    // Write data
                    foreach ($sectionData as $item) {
                        fputcsv($file, array_values($item));
                    }
                }
            }
            
            fputcsv($file, []); // Empty row
        }
        
        fclose($file);
    }

    private function generateJSONReport(string $filename, array $data): void
    {
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    }
}