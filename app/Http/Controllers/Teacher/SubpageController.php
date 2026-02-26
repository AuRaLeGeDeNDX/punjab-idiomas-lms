<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Services\ContentBlockService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SubpageController extends Controller
{
    protected ContentBlockService $contentBlockService;

    public function __construct(ContentBlockService $contentBlockService)
    {
        $this->contentBlockService = $contentBlockService;
    }

    /**
     * Display subpages for a module.
     */
    public function index(Course $course, Module $module): View
    {
        $this->authorize('view', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $subpages = $module->subpages()
            ->with(['contents' => function ($query) {
                $query->ordered();
            }])
            ->ordered()
            ->get();

        return view('teacher.subpages.index', compact('course', 'module', 'subpages'));
    }

    /**
     * Show the form for creating a new subpage.
     */
    public function create(Course $course, Module $module): View
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        return view('teacher.subpages.create', compact('course', 'module'));
    }

    /**
     * Store a newly created subpage.
     */
    public function store(Request $request, Course $course, Module $module): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['module_id'] = $module->id;
        $validated['order_index'] = Subpage::getNextOrderIndex($module->id);

        $subpage = Subpage::create($validated);

        // Initialize default content for the new subpage
        $this->contentBlockService->initializeSubpageContent($subpage);

        // Clear module cache
        Cache::forget("module_subpages_{$module->id}");

        return redirect()
            ->route('teacher.courses.modules.subpages.index', [$course, $module])
            ->with('success', 'Subpage created successfully.');
    }

    /**
     * Display the specified subpage with its content.
     */
    public function show(Course $course, Module $module, Subpage $subpage): View
    {
        $this->authorize('view', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        $subpage->load(['contents' => function ($query) {
            $query->ordered();
        }]);

        return view('teacher.subpages.show', compact('course', 'module', 'subpage'));
    }

    /**
     * Show the content builder interface for the subpage.
     */
    public function contentBuilder(Course $course, Module $module, Subpage $subpage): View
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        // STEP 3 — DATA FLOW FIX: Load content blocks server-side
        $contentBlocks = $this->contentBlockService->getContentBlocksForSubpage($subpage);
        $contentBySection = $this->contentBlockService->getContentBlocksBySection($subpage);
        $trashedContentBySection = $this->contentBlockService->getTrashedContentBlocksBySection($subpage);
        
        // Get content types and other configuration
        $contentTypes = \App\Models\Content::getContentTypes();
        $sections = \App\Enums\ContentSection::getAllSections();
        $visibilityOptions = \App\Enums\ContentVisibility::getAllVisibilities();

        return view('teacher.subpages.content-builder', compact(
            'course', 
            'module', 
            'subpage',
            'contentBlocks',
            'contentBySection',
            'trashedContentBySection',
            'contentTypes',
            'sections',
            'visibilityOptions'
        ));
    }

    /**
     * Show the form for editing the subpage.
     */
    public function edit(Course $course, Module $module, Subpage $subpage): View
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        return view('teacher.subpages.edit', compact('course', 'module', 'subpage'));
    }

    /**
     * Update the specified subpage.
     */
    public function update(Request $request, Course $course, Module $module, Subpage $subpage): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Checkboxes don't send a value when unchecked, so we must explicitly set it
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $subpage->update($validated);

        // Clear module cache
        Cache::forget("module_subpages_{$module->id}");

        // Redirect to the correct route based on role
        $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
        
        return redirect()
            ->route("{$routePrefix}.courses.modules.subpages.show", [$course, $module, $subpage])
            ->with('success', 'Subpage updated successfully.');
    }

    /**
     * Remove the specified subpage (soft delete).
     */
    public function destroy(Course $course, Module $module, Subpage $subpage): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        $subpage->delete();

        // Clear module cache
        Cache::forget("module_subpages_{$module->id}");

        return redirect()
            ->route('teacher.courses.modules.subpages.index', [$course, $module])
            ->with('success', 'Subpage deleted successfully.');
    }

    /**
     * Reorder subpages within a module.
     */
    public function reorder(Request $request, Course $course, Module $module): JsonResponse
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        $validated = $request->validate([
            'subpage_ids' => 'required|array',
            'subpage_ids.*' => 'exists:subpages,id',
        ]);

        // Verify all subpages belong to this module
        $subpageCount = Subpage::whereIn('id', $validated['subpage_ids'])
            ->where('module_id', $module->id)
            ->count();

        if ($subpageCount !== count($validated['subpage_ids'])) {
            return response()->json(['success' => false, 'message' => 'Invalid subpage IDs'], 400);
        }

        Subpage::reorderForModule($module->id, $validated['subpage_ids']);

        // Clear module cache
        Cache::forget("module_subpages_{$module->id}");

        return response()->json(['success' => true]);
    }

    /**
     * Toggle subpage active status.
     */
    public function toggleActive(Course $course, Module $module, Subpage $subpage): JsonResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            return response()->json(['success' => false, 'message' => 'Subpage not found'], 404);
        }

        $subpage->update(['is_active' => !$subpage->is_active]);

        // Clear module cache
        Cache::forget("module_subpages_{$module->id}");

        return response()->json([
            'success' => true,
            'is_active' => $subpage->is_active,
            'message' => $subpage->is_active ? 'Subpage activated' : 'Subpage deactivated'
        ]);
    }

    /**
     * Restore a soft-deleted subpage.
     */
    public function restore(Course $course, Module $module, $subpageId): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $subpage = Subpage::withTrashed()
            ->where('id', $subpageId)
            ->where('module_id', $module->id)
            ->firstOrFail();

        $subpage->restore();

        // Clear module cache
        Cache::forget("module_subpages_{$module->id}");

        return redirect()
            ->route('teacher.courses.modules.subpages.index', [$course, $module])
            ->with('success', 'Subpage restored successfully.');
    }
}