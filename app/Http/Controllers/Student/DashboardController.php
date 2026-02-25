<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Submission;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected EnrollmentService $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // If AJAX request, return only updated data
        if ($request->ajax()) {
            return response()->json([
                'notificationCount' => $user->unreadNotifications->count(),
            ]);
        }

        // Cache dashboard data for 5 minutes to improve performance
        $cacheKey = "student_dashboard_{$user->id}";
        $dashboardData = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->getDashboardData($user);
        });

        return view('student.dashboard', $dashboardData);
    }

    private function getDashboardData($user): array
    {
        // Get enrolled courses with progress using the enrollment service
        $enrolledCourses = $this->enrollmentService->getEnrolledCoursesWithProgress($user);

        // Get learning record
        $learningRecord = $this->enrollmentService->getLearningRecord($user);

        // Get enrolled course IDs once for reuse
        $courseIds = Enrollment::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('course_id');

        // Single query: all unsubmitted published assignments for enrolled courses
        $allAssignments = Assignment::whereIn('course_id', $courseIds)
            ->where('is_published', true)
            ->whereDoesntHave('submissions', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['course'])
            ->orderBy('due_date')
            ->get();

        // Partition in PHP: upcoming vs overdue
        $now = now();
        $upcomingAssignments = $allAssignments->filter(function ($a) use ($now) {
            return is_null($a->due_date) || $a->due_date > $now;
        })->take(5)->values();
        
        $overdueAssignmentsCount = $allAssignments->filter(function ($a) use ($now) {
            return $a->due_date && $a->due_date < $now;
        })->count();

        // Get recent grades
        $recentGrades = Grade::whereHas('submission', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('is_published', true)
        ->with(['submission.assignment.course'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        // Calculate statistics
        $enrolledCoursesCount = $enrolledCourses->count();
        $pendingAssignmentsCount = $upcomingAssignments->count();
        
        $averageGrade = $recentGrades->avg('score') ?? 0;

        // Get recent activities
        $recentActivities = $this->getRecentActivities($user);

        // Get active announcements for student
        $announcements = Announcement::visibleToRole('Student')
            ->orderBy('priority', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'enrolledCourses' => $enrolledCourses,
            'upcomingAssignments' => $upcomingAssignments,
            'recentGrades' => $recentGrades,
            'enrolledCoursesCount' => $enrolledCoursesCount,
            'pendingAssignmentsCount' => $pendingAssignmentsCount,
            'overdueAssignmentsCount' => $overdueAssignmentsCount,
            'averageGrade' => $averageGrade,
            'recentActivities' => $recentActivities,
            'learningRecord' => $learningRecord,
            'announcements' => $announcements,
        ];
    }

    private function getRecentActivities($user): array
    {
        $activities = [];

        // Recent submissions
        $recentSubmissions = Submission::where('user_id', $user->id)
            ->with('assignment.course')
            ->orderBy('submitted_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentSubmissions as $submission) {
            // Skip if assignment has been deleted or is null
            if (!$submission->assignment || !isset($submission->assignment->title)) {
                continue;
            }
            
            $activities[] = [
                'icon' => 'fa-upload',
                'color' => 'primary',
                'message' => "Submitted assignment: {$submission->assignment->title}",
                'time' => $submission->submitted_at->diffForHumans(),
            ];
        }

        // Recent grades
        $recentGrades = Grade::whereHas('submission', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('is_published', true)
        ->with('submission.assignment.course')
        ->orderBy('published_at', 'desc')
        ->limit(2)
        ->get();

        foreach ($recentGrades as $grade) {
            // Skip if submission or assignment has been deleted or is null
            if (!$grade->submission || !$grade->submission->assignment || !isset($grade->submission->assignment->title)) {
                continue;
            }
            
            $activities[] = [
                'icon' => 'fa-star',
                'color' => $grade->isPassing() ? 'success' : 'warning',
                'message' => "Received grade for: {$grade->submission->assignment->title} ({$grade->score}/{$grade->submission->assignment->max_score})",
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
