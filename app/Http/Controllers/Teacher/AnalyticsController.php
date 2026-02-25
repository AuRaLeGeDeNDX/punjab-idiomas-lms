<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Analytics Controller for Teachers
 * 
 * Provides analytics dashboards and reports for teachers to track
 * student performance, assignment completion, and course metrics.
 * 
 * Requirements: 3 (Teacher Analytics), 14 (Performance Optimization)
 */
class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->middleware('auth');
        $this->middleware('role:Teacher,Admin');
    }

    /**
     * Display teacher analytics dashboard for a course
     * 
     * @param Course $course
     * @return \Illuminate\View\View
     */
    public function dashboard(Course $course)
    {
        Gate::authorize('viewAnalytics', $course);

        try {
            $data = $this->analyticsService->getTeacherDashboardData(Auth::user(), $course);
            
            return view('teacher.analytics.dashboard', [
                'course' => $course,
                'analytics' => $data
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load analytics dashboard. Please try again.');
        }
    }

    /**
     * Display assignment-specific analytics
     * 
     * @param Request $request
     * @param Assignment $assignment
     * @return \Illuminate\View\View
     */
    public function assignment(Request $request, Assignment $assignment)
    {
        Gate::authorize('viewAnalytics', $assignment);

        try {
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);
            
            // Validate pagination parameters
            $perPage = max(5, min(100, (int)$perPage)); // Between 5 and 100
            $page = max(1, (int)$page);
            
            $data = $this->analyticsService->getAssignmentAnalytics($assignment, $perPage, $page);
            
            return view('teacher.analytics.assignment', [
                'assignment' => $assignment,
                'analytics' => $data
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load assignment analytics. Please try again.');
        }
    }

    /**
     * Export analytics data as CSV
     * 
     * @param Course $course
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Course $course)
    {
        Gate::authorize('viewAnalytics', $course);

        try {
            $data = $this->analyticsService->getTeacherDashboardData(Auth::user(), $course);
            
            $filename = "analytics_{$course->code}_" . now()->format('Y-m-d') . ".csv";
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // Write header
                fputcsv($file, ['Metric', 'Value']);
                
                // Write data
                fputcsv($file, ['Overall Completion Rate', $data['overall_completion_rate'] . '%']);
                fputcsv($file, ['Average Score', $data['average_score']]);
                
                // Write assignment stats
                fputcsv($file, []);
                fputcsv($file, ['Assignment', 'Completion Rate', 'Average Score', 'Late Count', 'Not Submitted']);
                
                foreach ($data['assignment_stats'] as $stat) {
                    fputcsv($file, [
                        $stat['title'],
                        $stat['completion_rate'] . '%',
                        $stat['average_score'],
                        $stat['late_count'],
                        $stat['not_submitted_count']
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export analytics. Please try again.');
        }
    }
}
