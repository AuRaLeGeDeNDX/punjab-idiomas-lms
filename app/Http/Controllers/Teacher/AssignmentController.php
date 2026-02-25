<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Jobs\SendAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments for a subpage.
     */
    public function index(Course $course, Module $module, Subpage $subpage, Request $request)
    {
        Gate::authorize('view', $subpage);

        $query = $subpage->assignments()
            ->with(['submissions.user', 'submissions.grade']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('assignment_type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        }

        if ($request->filled('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('instructions', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'order_index');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        if (in_array($sortBy, ['title', 'due_date', 'max_score', 'created_at', 'order_index'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $assignments = $query->paginate(15)->withQueryString();

        // Store filters in session for persistence
        session()->put('assignment_filters', $request->only([
            'type', 'status', 'due_date_from', 'due_date_to', 'search', 'sort_by', 'sort_direction'
        ]));

        return view('teacher.assignments.index', compact('course', 'module', 'subpage', 'assignments'));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(Course $course, Module $module, Subpage $subpage)
    {
        // Eager load relationships needed for authorization
        $subpage->load('module.course');
        
        Gate::authorize('create', [Assignment::class, $subpage]);

        return view('teacher.assignments.create', compact('course', 'module', 'subpage'));
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request, Course $course, Module $module, Subpage $subpage)
    {
        Gate::authorize('create', [Assignment::class, $subpage]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'assignment_type' => 'required|in:homework,project,quiz,exam,essay',
            'submission_type' => 'required|in:text,file,both',
            'max_score' => 'required|numeric|min:0|max:1000',
            'due_date' => 'nullable|date|after:now',
            'allow_late_submission' => 'boolean',
            'auto_grade' => 'boolean',
            'is_published' => 'boolean',
            'scheduled_publish_at' => 'nullable|date|after:now',
        ]);

        // Validate scheduling logic
        if (isset($validated['scheduled_publish_at']) && ($validated['is_published'] ?? false)) {
            return back()->withErrors(['scheduled_publish_at' => 'Cannot schedule publication for an already published assignment.'])->withInput();
        }

        $assignment = new Assignment($validated);
        $assignment->course_id = $course->id;
        $assignment->module_id = $module->id;
        $assignment->subpage_id = $subpage->id;
        $assignment->order_index = Assignment::getNextOrderIndex($subpage->id);
        $assignment->is_active = true;

        if ($validated['is_published'] ?? false) {
            $assignment->published_at = now();
        }

        $assignment->save();

        // Send notification if published
        if ($assignment->is_published) {
            SendAssignmentNotification::dispatch($assignment, 'published');
        }

        // Determine route prefix based on user role
        $routePrefix = Auth::user()->hasRole('Admin') ? 'admin' : 'teacher';

        return redirect()
            ->route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage])
            ->with('success', 'Assignment created successfully.');
    }

    /**
     * Display the specified assignment.
     */
    public function show(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $assignment->load([
            'submissions.user',
            'submissions.grade' => function ($query) {
                $query->orderBy('graded_at', 'desc');
            }
        ]);

        $stats = $assignment->getSubmissionStats();

        return view('teacher.assignments.show', compact('course', 'module', 'subpage', 'assignment', 'stats'));
    }

    /**
     * Show the form for editing the assignment.
     */
    public function edit(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        return view('teacher.assignments.edit', compact('course', 'module', 'subpage', 'assignment'));
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'assignment_type' => 'required|in:homework,project,quiz,exam,essay',
            'submission_type' => 'required|in:text,file,both',
            'max_score' => 'required|numeric|min:0|max:1000',
            'due_date' => 'nullable|date',
            'allow_late_submission' => 'boolean',
            'auto_grade' => 'boolean',
            'is_published' => 'boolean',
            'is_active' => 'boolean',
            'scheduled_publish_at' => 'nullable|date|after:now',
        ]);

        // Validate scheduling logic
        if (isset($validated['scheduled_publish_at']) && ($validated['is_published'] ?? false)) {
            return back()->withErrors(['scheduled_publish_at' => 'Cannot schedule publication for an already published assignment.'])->withInput();
        }

        $wasPublished = $assignment->is_published;
        $assignment->update($validated);

        // Handle publishing
        $isPublished = $validated['is_published'] ?? false;
        if ($isPublished && !$wasPublished) {
            $assignment->publish();
            SendAssignmentNotification::dispatch($assignment, 'published');
        } elseif (!$isPublished && $wasPublished) {
            $assignment->unpublish();
        }

        // Determine route prefix based on user role
        $routePrefix = Auth::user()->hasRole('Admin') ? 'admin' : 'teacher';

        return redirect()
            ->route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage])
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('delete', $assignment);

        $assignment->delete();

        // Determine route prefix based on user role
        $routePrefix = Auth::user()->hasRole('Admin') ? 'admin' : 'teacher';

        return redirect()
            ->route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage])
            ->with('success', 'Assignment deleted successfully.');
    }

    /**
     * Publish the assignment.
     */
    public function publish(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $assignment->publish();
        SendAssignmentNotification::dispatch($assignment, 'published');

        return back()->with('success', 'Assignment published successfully.');
    }

    /**
     * Unpublish the assignment.
     */
    public function unpublish(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $assignment->unpublish();

        return back()->with('success', 'Assignment unpublished successfully.');
    }

    /**
     * Reorder assignments within a subpage.
     */
    public function reorder(Request $request, Course $course, Module $module, Subpage $subpage)
    {
        Gate::authorize('update', $subpage);

        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'exists:assignments,id',
        ]);

        foreach ($validated['assignment_ids'] as $index => $assignmentId) {
            Assignment::where('id', $assignmentId)
                ->where('subpage_id', $subpage->id)
                ->update(['order_index' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Send due date reminders.
     */
    public function sendReminders(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        SendAssignmentNotification::dispatch($assignment, 'due_reminder');

        return back()->with('success', 'Due date reminders sent successfully.');
    }

    /**
     * Cancel scheduled publication.
     */
    public function cancelSchedule(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        if (!$assignment->isScheduled()) {
            return back()->withErrors(['error' => 'This assignment is not scheduled for publication.']);
        }

        $assignment->cancelSchedule();

        return back()->with('success', 'Scheduled publication cancelled successfully.');
    }
    /**
     * Restore a soft-deleted assignment.
     */
    public function restore(Course $course, Module $module, Subpage $subpage, $assignmentId): RedirectResponse
    {
        Gate::authorize('update', $assignmentId instanceof Assignment ? $assignmentId : Assignment::class);

        $assignment = Assignment::withTrashed()
            ->where('id', $assignmentId)
            ->where('subpage_id', $subpage->id)
            ->firstOrFail();

        $assignment->restore();

        // Determine route prefix based on user role
        $routePrefix = Auth::user()->hasRole('Admin') ? 'admin' : 'teacher';

        return redirect()
            ->route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage])
            ->with('success', 'Assignment restored successfully.');
    }

    /**
     * Display trashed assignments.
     */
    public function trashed(Course $course, Module $module, Subpage $subpage)
    {
        Gate::authorize('view', $subpage);

        $assignments = Assignment::onlyTrashed()
            ->where('subpage_id', $subpage->id)
            ->paginate(15);

        return view('teacher.assignments.trashed', compact('course', 'module', 'subpage', 'assignments'));
    }

    /**
     * Permanently delete an assignment.
     */
    public function forceDelete(Course $course, Module $module, Subpage $subpage, $assignmentId)
    {
        Gate::authorize('update', $subpage);

        $assignment = Assignment::onlyTrashed()
            ->where('id', $assignmentId)
            ->where('subpage_id', $subpage->id)
            ->firstOrFail();

        $assignment->forceDelete();

        $routePrefix = Auth::user()->hasRole('Admin') ? 'admin' : 'teacher';

        return redirect()
            ->route($routePrefix . '.courses.modules.subpages.assignments.trashed', [$course, $module, $subpage])
            ->with('success', 'Assignment permanently deleted.');
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(Course $course, Module $module, Subpage $subpage)
    {
        Gate::authorize('update', $subpage);

        Assignment::onlyTrashed()
            ->where('subpage_id', $subpage->id)
            ->forceDelete();

        $routePrefix = Auth::user()->hasRole('Admin') ? 'admin' : 'teacher';

        return redirect()
            ->route($routePrefix . '.courses.modules.subpages.assignments.trashed', [$course, $module, $subpage])
            ->with('success', 'Trash emptied successfully.');
    }
}
