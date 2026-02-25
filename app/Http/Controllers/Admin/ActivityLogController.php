<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request)
    {
        // Must be an admin
        $this->authorize('viewAny', User::class);

        $query = Activity::with(['causer', 'subject'])->latest();

        // Optional filtering by user
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', User::class);
        }
        
        // Optional filtering by log name (type of activity)
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        $logs = $query->paginate(20)->withQueryString();
        
        // Get all unique log names for the filter dropdown
        $logNames = Activity::select('log_name')->distinct()->pluck('log_name');

        return view('admin.activity_logs.index', compact('logs', 'logNames'));
    }
}
