<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // If AJAX request, return only updated data
        if ($request->ajax()) {
            $pendingGradingCount = Submission::whereHas('assignment', function ($query) use ($user) {
                $query->whereHas('course', function ($q) use ($user) {
                    $q->where('teacher_id', $user->id);
                });
            })
            ->where('status', 'submitted')
            ->whereDoesntHave('grade')
            ->count();

            return response()->json([
                'pendingGradingCount' => $pendingGradingCount,
            ]);
        }

        // Cache dashboard data for 5 minutes
        $cacheKey = "teacher_dashboard_{$user->id}";
        $dashboardData = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->getDashboardData($user);
        });

        return view('teacher.dashboard', $dashboardData);
    }

    private function getDashboardData($user): array
    {
        // Get teacher's course IDs once — reuse everywhere
        $courseIds = Course::where('teacher_id', $user->id)->pluck('id');

        // Get teacher's courses with counts (limited to 5 for display)
        $courses = Course::where('teacher_id', $user->id)
            ->withCount(['enrollments', 'modules'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Get pending submissions — one query, derive count from collection
        $pendingSubmissions = Submission::whereHas('assignment', function ($query) use ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        })
        ->where('status', 'submitted')
        ->whereDoesntHave('grade')
        ->with(['user', 'assignment.course', 'assignment.module', 'assignment.subpage'])
        ->orderBy('submitted_at', 'asc')
        ->limit(5)
        ->get();

        // Derive count from a separate efficient count query
        $pendingGradingCount = Submission::whereHas('assignment', function ($query) use ($courseIds) {
            $query->whereIn('course_id', $courseIds);
        })
        ->where('status', 'submitted')
        ->whereDoesntHave('grade')
        ->count();

        // Statistics — use courseIds directly instead of nested whereHas
        $coursesCount = $courseIds->count();
        
        $totalStudentsCount = DB::table('enrollments')
            ->whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        $activeAssignmentsCount = Assignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->where('is_active', true)
            ->count();

        // Get course performance data
        $coursePerformance = $this->getCoursePerformance($user);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);

        // Get active announcements for teacher
        $announcements = Announcement::visibleToRole('Teacher')
            ->orderBy('priority', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'courses' => $courses,
            'pendingSubmissions' => $pendingSubmissions,
            'coursesCount' => $coursesCount,
            'totalStudentsCount' => $totalStudentsCount,
            'pendingGradingCount' => $pendingGradingCount,
            'activeAssignmentsCount' => $activeAssignmentsCount,
            'coursePerformance' => $coursePerformance,
            'recentActivities' => $recentActivities,
            'announcements' => $announcements,
        ];
    }

    private function getCoursePerformance($user): array
    {
        return Course::where('teacher_id', $user->id)
            ->withCount('enrollments')
            ->addSelect(['avg_grade' => function ($query) {
                $query->from('grades')
                    ->join('submissions', 'submissions.id', '=', 'grades.submission_id')
                    ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
                    ->whereColumn('assignments.course_id', 'courses.id')
                    ->where('grades.is_published', true)
                    ->selectRaw('AVG(grades.score)');
            }])
            ->get()
            ->map(function ($course) {
                return [
                    'course_title' => $course->title,
                    'students_count' => $course->enrollments_count,
                    'avg_grade' => $course->avg_grade ? round($course->avg_grade, 1) : 0,
                ];
            })
            ->toArray();
    }

    private function getRecentActivities($user): array
    {
        $activities = [];

        // Recent submissions received
        $recentSubmissions = Submission::whereHas('assignment', function ($query) use ($user) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        })
        ->with(['user', 'assignment'])
        ->orderBy('submitted_at', 'desc')
        ->limit(3)
        ->get();

        foreach ($recentSubmissions as $submission) {
            $activities[] = [
                'icon' => 'fa-upload',
                'color' => 'info',
                'message' => "{$submission->user->name} submitted: {$submission->assignment->title}",
                'time' => $submission->submitted_at->diffForHumans(),
            ];
        }

        // Recent grades published
        $recentGrades = Grade::whereHas('submission.assignment', function ($query) use ($user) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        })
        ->where('grader_id', $user->id)
        ->where('is_published', true)
        ->with(['submission.user', 'submission.assignment'])
        ->orderBy('published_at', 'desc')
        ->limit(2)
        ->get();

        foreach ($recentGrades as $grade) {
            $activities[] = [
                'icon' => 'fa-star',
                'color' => 'success',
                'message' => "Graded {$grade->submission->user->name}'s {$grade->submission->assignment->title}",
                'time' => $grade->published_at->diffForHumans(),
            ];
        }

        // Sort by time and limit
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 5);
    }
}
