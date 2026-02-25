<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AssignmentTemplate;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentTemplateController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index()
    {
        $templates = AssignmentTemplate::accessibleBy(auth()->id())
            ->with('teacher')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('teacher.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        return view('teacher.templates.create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'assignment_type' => 'required|in:homework,project,quiz,exam,essay',
            'submission_type' => 'required|in:text,file,both',
            'max_score' => 'required|numeric|min:0|max:1000',
            'is_public' => 'boolean',
        ]);

        $template = AssignmentTemplate::create(array_merge($validated, [
            'teacher_id' => auth()->id(),
        ]));

        return redirect()
            ->route('teacher.templates.index')
            ->with('success', 'Template created successfully.');
    }

    /**
     * Apply template to create assignment.
     */
    public function apply(Request $request, AssignmentTemplate $template, Course $course, Module $module, Subpage $subpage)
    {
        Gate::authorize('create', [Assignment::class, $subpage]);

        $validated = $request->validate([
            'due_date' => 'nullable|date|after:now',
            'allow_late_submission' => 'boolean',
        ]);

        $assignment = $template->createAssignment(array_merge($validated, [
            'course_id' => $course->id,
            'module_id' => $module->id,
            'subpage_id' => $subpage->id,
            'order_index' => Assignment::getNextOrderIndex($subpage->id),
            'is_active' => true,
            'is_published' => false,
        ]));

        return redirect()
            ->route('teacher.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment])
            ->with('success', 'Assignment created from template.');
    }

    /**
     * Duplicate an existing assignment as template.
     */
    public function duplicate(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $template = AssignmentTemplate::create([
            'teacher_id' => auth()->id(),
            'title' => $assignment->title . ' (Template)',
            'description' => $assignment->description,
            'instructions' => $assignment->instructions,
            'assignment_type' => $assignment->assignment_type,
            'submission_type' => $assignment->submission_type,
            'max_score' => $assignment->max_score,
            'is_public' => false,
        ]);

        return redirect()
            ->route('teacher.templates.index')
            ->with('success', 'Template created from assignment.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(AssignmentTemplate $template)
    {
        if ($template->teacher_id !== auth()->id()) {
            abort(403);
        }

        $template->delete();

        return redirect()
            ->route('teacher.templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
