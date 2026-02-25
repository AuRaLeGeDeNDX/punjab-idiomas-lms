<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the student's own activity logs.
     */
    public function index(Request $request)
    {
        $studentId = auth()->id();

        // Get activities performed by THIS student
        $query = Activity::where('causer_id', $studentId)
            ->where('causer_type', \App\Models\User::class)
            ->with(['subject'])
            ->latest();

        // Optional filtering by log name (type of activity)
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        $logs = $query->paginate(20)->withQueryString();
        
        // Get all unique log names for the filter dropdown (scoped to this user's activities)
        $logNames = Activity::where('causer_id', $studentId)
            ->where('causer_type', \App\Models\User::class)
            ->select('log_name')
            ->distinct()
            ->pluck('log_name');

        return view('student.activity_logs.index', compact('logs', 'logNames'));
    }
}
