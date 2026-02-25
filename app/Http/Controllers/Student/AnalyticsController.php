<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Analytics Controller for Students
 * 
 * Provides progress tracking and performance analytics for students
 * to monitor their own academic progress.
 * 
 * Requirements: 4 (Student Progress), 14 (Performance Optimization)
 */
class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
        $this->middleware('auth');
        $this->middleware('role:Student');
    }

    /**
     * Display student progress dashboard
     * 
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            $data = $this->analyticsService->getStudentDashboardData(Auth::user());
            
            return view('student.analytics.dashboard', [
                'analytics' => $data
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load progress dashboard. Please try again.');
        }
    }
}
