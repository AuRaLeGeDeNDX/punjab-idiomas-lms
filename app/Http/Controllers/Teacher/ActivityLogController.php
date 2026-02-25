<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\Course;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs for students in the teacher's courses.
     */
    public function index(Request $request)
    {
        $teacherId = auth()->id();
        
        // Get all courses taught by this teacher
        $courseIds = Course::where('teacher_id', $teacherId)->pluck('id');
        
        // Get all students enrolled in these courses
        $studentIds = \App\Models\Enrollment::whereIn('course_id', $courseIds)
                        ->pluck('user_id')
                        ->unique();

        // Get activities performed by these students or related to the teacher's courses/submissions
        $query = Activity::with(['causer', 'subject'])
            ->where(function ($q) use ($studentIds, $courseIds) {
                // Activities by enrolled students
                $q->whereIn('causer_id', $studentIds)
                  ->where('causer_type', \App\Models\User::class);
                
                // OR Activities related to the teacher's courses
                $q->orWhere(function($subQ) use ($courseIds) {
                    $subQ->where('subject_type', Course::class)
                         ->whereIn('subject_id', $courseIds);
                });
                
                // OR Activities related to modules in these courses
                $q->orWhere(function($subQ) use ($courseIds) {
                    $subQ->where('subject_type', \App\Models\Module::class)
                         ->whereIn('subject_id', \App\Models\Module::whereIn('course_id', $courseIds)->pluck('id'));
                });
            })
            ->latest();

        // Optional filtering by specific student
        if ($request->filled('student_id')) {
            // Verify the student is enrolled in one of the teacher's courses
            if ($studentIds->contains($request->student_id)) {
                $query->where('causer_id', $request->student_id)
                      ->where('causer_type', \App\Models\User::class);
            }
        }
        
        // Optional filtering by log name (type of activity)
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        $logs = $query->paginate(20)->withQueryString();
        
        // Get all unique log names for the filter dropdown (scoped to what we queried)
        $logNames = config('activitylog.default_log_name') 
            ? collect([config('activitylog.default_log_name')]) 
            : Activity::select('log_name')->distinct()->pluck('log_name');
            
        // Get my students for the dropdown
        $students = \App\Models\User::whereIn('id', $studentIds)->orderBy('name')->get();

        return view('teacher.activity_logs.index', compact('logs', 'logNames', 'students'));
    }
}
