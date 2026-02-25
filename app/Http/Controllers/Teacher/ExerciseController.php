<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display exercises for a subpage.
     */
    public function index(Course $course, Module $module, Subpage $subpage): View
    {
        $this->authorize('view', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        $exercises = $subpage->exercises()
            ->with(['submissions' => function ($query) {
                $query->with('user');
            }])
            ->ordered()
            ->get();

        return view('teacher.exercises.index', compact('course', 'module', 'subpage', 'exercises'));
    }

    /**
     * Show the form for creating a new exercise.
     */
    public function create(Course $course, Module $module, Subpage $subpage): View
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        return view('teacher.exercises.create', compact('course', 'module', 'subpage'));
    }

    /**
     * Store a newly created exercise.
     */
    public function store(Request $request, Course $course, Module $module, Subpage $subpage): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question' => 'required|string',
            'answer' => 'required|string',
            'instructions' => 'nullable|string',
            'submission_type' => 'required|in:text,file,both',
            'max_score' => 'required|integer|min:1|max:1000',
            'due_date' => 'nullable|date|after:now',
            'is_active' => 'boolean',
        ]);

        $validated['subpage_id'] = $subpage->id;
        $validated['order_index'] = Exercise::getNextOrderIndex($subpage->id);

        $exercise = Exercise::create($validated);

        return redirect()
            ->route('teacher.courses.modules.subpages.exercises.index', [$course, $module, $subpage])
            ->with('success', 'Exercise created successfully.');
    }

    /**
     * Display the specified exercise with submissions.
     */
    public function show(Course $course, Module $module, Subpage $subpage, Exercise $exercise): View
    {
        $this->authorize('view', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        $exercise->load(['submissions' => function ($query) {
            $query->with(['user', 'grader'])->orderBy('submitted_at', 'desc');
        }]);

        // Get submission statistics
        $stats = [
            'total_submissions' => $exercise->submissions()->count(),
            'graded_submissions' => $exercise->submissions()->where('status', 'graded')->count(),
            'pending_submissions' => $exercise->submissions()->where('status', 'submitted')->count(),
            'average_score' => $exercise->submissions()->where('status', 'graded')->avg('score'),
        ];

        return view('teacher.exercises.show', compact('course', 'module', 'subpage', 'exercise', 'stats'));
    }

    /**
     * Show the form for editing the exercise.
     */
    public function edit(Course $course, Module $module, Subpage $subpage, Exercise $exercise): View
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        return view('teacher.exercises.edit', compact('course', 'module', 'subpage', 'exercise'));
    }

    /**
     * Update the specified exercise.
     */
    public function update(Request $request, Course $course, Module $module, Subpage $subpage, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question' => 'required|string',
            'answer' => 'required|string',
            'instructions' => 'nullable|string',
            'submission_type' => 'required|in:text,file,both',
            'max_score' => 'required|integer|min:1|max:1000',
            'due_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $exercise->update($validated);

        return redirect()
            ->route('teacher.courses.modules.subpages.exercises.show', [$course, $module, $subpage, $exercise])
            ->with('success', 'Exercise updated successfully.');
    }

    /**
     * Remove the specified exercise.
     */
    public function destroy(Course $course, Module $module, Subpage $subpage, Exercise $exercise): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        $exercise->delete();

        return redirect()
            ->route('teacher.courses.modules.subpages.exercises.index', [$course, $module, $subpage])
            ->with('success', 'Exercise deleted successfully.');
    }

    /**
     * Reorder exercises within a subpage.
     */
    public function reorder(Request $request, Course $course, Module $module, Subpage $subpage): JsonResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            return response()->json(['success' => false, 'message' => 'Subpage not found'], 404);
        }

        $validated = $request->validate([
            'exercise_ids' => 'required|array',
            'exercise_ids.*' => 'exists:exercises,id',
        ]);

        // Verify all exercises belong to this subpage
        $exerciseCount = Exercise::whereIn('id', $validated['exercise_ids'])
            ->where('subpage_id', $subpage->id)
            ->count();

        if ($exerciseCount !== count($validated['exercise_ids'])) {
            return response()->json(['success' => false, 'message' => 'Invalid exercise IDs'], 400);
        }

        foreach ($validated['exercise_ids'] as $index => $exerciseId) {
            Exercise::where('id', $exerciseId)
                   ->where('subpage_id', $subpage->id)
                   ->update(['order_index' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle exercise active status.
     */
    public function toggleActive(Course $course, Module $module, Subpage $subpage, Exercise $exercise): JsonResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            return response()->json(['success' => false, 'message' => 'Exercise not found'], 404);
        }

        $exercise->update(['is_active' => !$exercise->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $exercise->is_active,
            'message' => $exercise->is_active ? 'Exercise activated' : 'Exercise deactivated'
        ]);
    }
    /**
     * Restore a soft-deleted exercise.
     */
    public function restore(Course $course, Module $module, Subpage $subpage, $exerciseId): RedirectResponse
    {
        $this->authorize('update', $course);

        $exercise = Exercise::withTrashed()
            ->where('id', $exerciseId)
            ->where('subpage_id', $subpage->id)
            ->firstOrFail();

        $exercise->restore();

        return redirect()
            ->route('teacher.courses.modules.subpages.exercises.index', [$course, $module, $subpage])
            ->with('success', 'Exercise restored successfully.');
    }
}
