<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Show the form for creating a new module.
     */
    public function create(Course $course): View
    {
        $this->authorize('update', $course);
        
        return view('teacher.modules.create', compact('course'));
    }

    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        try {
            $module = $this->courseService->addModule($course, $validated);
            
            // Clear course-related caches (targeted invalidation)
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:v2:{$course->id}");
            \Cache::forget("course:students:{$course->id}");
            \Cache::forget("teacher:courses:{$course->teacher_id}");
            \Cache::forget("teacher_dashboard_{$course->teacher_id}");
            \Cache::forget("user:accessible_courses:{$course->teacher_id}");
            \Cache::forget("courses:published");
            \Cache::forget("course_navigation_{$course->id}");
            \Cache::forget("course_hierarchy_full_{$course->id}");
            
            // Also clear any admin user caches if admin is creating
            if (auth()->user()->hasRole('Admin')) {
                \Cache::forget("user:accessible_courses:" . auth()->id());
            }
            
            // Role-aware redirect: Admin goes to admin.courses.show, Teacher goes to teacher.courses.show
            $routeName = auth()->user()->hasRole('Admin') ? 'admin.courses.show' : 'teacher.courses.show';
            
            return redirect()
                ->route($routeName, $course)
                ->with('success', 'Module created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create module: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified module.
     */
    public function show(Course $course, Module $module): View
    {
        $this->authorize('view', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        return view('teacher.modules.show', compact('course', 'module'));
    }

    /**
     * Show the form for editing the specified module.
     */
    public function edit(Course $course, Module $module): View
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        return view('teacher.modules.edit', compact('course', 'module'));
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, Course $course, Module $module): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        try {
            $module->update($validated);
            
            // Clear course-related caches (targeted invalidation)
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:v2:{$course->id}");
            \Cache::forget("course:students:{$course->id}");
            \Cache::forget("teacher:courses:{$course->teacher_id}");
            \Cache::forget("teacher_dashboard_{$course->teacher_id}");
            \Cache::forget("user:accessible_courses:{$course->teacher_id}");
            \Cache::forget("courses:published");
            \Cache::forget("course_navigation_{$course->id}");
            \Cache::forget("course_hierarchy_full_{$course->id}");
            
            // Also clear any admin user caches if admin is updating
            if (auth()->user()->hasRole('Admin')) {
                \Cache::forget("user:accessible_courses:" . auth()->id());
            }
            
            // Role-aware redirect: Admin goes to admin.courses.show, Teacher goes to teacher.courses.show
            $routeName = auth()->user()->hasRole('Admin') ? 'admin.courses.show' : 'teacher.courses.show';
            
            return redirect()
                ->route($routeName, $course)
                ->with('success', 'Module updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update module: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(Course $course, Module $module): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }
        
        try {
            $module->delete();
            
            // Clear course-related caches (targeted invalidation)
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:v2:{$course->id}");
            \Cache::forget("course:students:{$course->id}");
            \Cache::forget("teacher:courses:{$course->teacher_id}");
            \Cache::forget("teacher_dashboard_{$course->teacher_id}");
            \Cache::forget("user:accessible_courses:{$course->teacher_id}");
            \Cache::forget("courses:published");
            \Cache::forget("course_navigation_{$course->id}");
            \Cache::forget("course_hierarchy_full_{$course->id}");
            
            // Also clear any admin user caches if admin is deleting
            if (auth()->user()->hasRole('Admin')) {
                \Cache::forget("user:accessible_courses:" . auth()->id());
            }
            
            // Role-aware redirect: Admin goes to admin.courses.show, Teacher goes to teacher.courses.show
            $routeName = auth()->user()->hasRole('Admin') ? 'admin.courses.show' : 'teacher.courses.show';
            
            return redirect()
                ->route($routeName, $course)
                ->with('success', 'Module deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete module: ' . $e->getMessage()]);
        }
    }

    /**
     * Reorder modules within a course.
     */
    public function reorder(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'integer|exists:course_modules,id',
        ]);

        try {
            $this->courseService->reorderModules($course, $validated['module_ids']);
            
            return response()->json(['success' => true, 'message' => 'Modules reordered successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to reorder modules: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add content to a module.
     */
    public function addContent(Request $request, Course $course, Module $module): JsonResponse
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }
        
        $validated = $request->validate([
            'content' => 'required|array',
            'content.*.type' => 'required|string|in:text,video,document,image,link',
            'content.*.title' => 'required|string|max:255',
            'content.*.data' => 'required',
        ]);

        try {
            $module->addContent($validated['content']);
            
            return response()->json(['success' => true, 'message' => 'Content added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add content: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Publish a module.
     */
    public function publish(Request $request, Course $course, Module $module)
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Module not found'], 404);
            }
            abort(404);
        }
        
        try {
            $module->update(['is_published' => true]);
            
            // Clear course cache so students see the updated module list
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:{$course->id}");
            
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Module published successfully!']);
            }
            
            return back()->with('success', 'Module published successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to publish module: ' . $e->getMessage()], 500);
            }
            
            return back()
                ->withErrors(['error' => 'Failed to publish module: ' . $e->getMessage()]);
        }
    }

    /**
     * Unpublish a module.
     */
    public function unpublish(Request $request, Course $course, Module $module)
    {
        $this->authorize('update', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Module not found'], 404);
            }
            abort(404);
        }
        
        try {
            $module->update(['is_published' => false]);
            
            // Clear course cache so students see the updated module list
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:{$course->id}");
            
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Module unpublished successfully!']);
            }
            
            return back()->with('success', 'Module unpublished successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to unpublish module: ' . $e->getMessage()], 500);
            }
            
            return back()
                ->withErrors(['error' => 'Failed to unpublish module: ' . $e->getMessage()]);
        }
    }
    /**
     * Restore a soft-deleted module.
     */
    public function restore(Course $course, $moduleId): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $course);

        try {
            $module = Module::withTrashed()
                ->where('id', $moduleId)
                ->where('course_id', $course->id)
                ->firstOrFail();

            $module->restore();

            \Log::info('Module restoration successful', [
                'module_id' => $moduleId,
                'course_id' => $course->id,
                'user_id' => auth()->id()
            ]);

            // Clear course-related caches (targeted invalidation)
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:v2:{$course->id}");
            \Cache::forget("course:students:{$course->id}");
            \Cache::forget("teacher:courses:{$course->teacher_id}");
            \Cache::forget("teacher_dashboard_{$course->teacher_id}");
            \Cache::forget("user:accessible_courses:{$course->teacher_id}");
            \Cache::forget("courses:published");
            \Cache::forget("course_navigation_{$course->id}");
            \Cache::forget("course_hierarchy_full_{$course->id}");

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Module restored successfully!'
                ]);
            }

            return redirect()
                ->route(auth()->user()->hasRole('Admin') ? 'admin.courses.show' : 'teacher.courses.show', $course)
                ->with('success', 'Module restored successfully!');
        } catch (\Exception $e) {
            \Log::error('Module restoration failed: ' . $e->getMessage(), [
                'module_id' => $moduleId,
                'course_id' => $course->id,
                'user_id' => auth()->id(),
                'exception' => $e
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to restore module: ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Failed to restore module: ' . $e->getMessage()]);
        }
    }

    /**
     * Permanently delete a module from trash.
     */
    public function forceDelete(Course $course, $moduleId): JsonResponse
    {
        $this->authorize('update', $course);

        try {
            $module = Module::onlyTrashed()
                ->where('id', $moduleId)
                ->where('course_id', $course->id)
                ->firstOrFail();

            // Permanent deletion
            $module->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Module permanently deleted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete module: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Permanently delete ALL soft-deleted modules for a course.
     */
    public function emptyTrash(Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        try {
            $count = Module::onlyTrashed()
                ->where('course_id', $course->id)
                ->count();

            if ($count === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Trash is already empty.',
                    'deleted_count' => 0,
                ]);
            }

            Module::onlyTrashed()
                ->where('course_id', $course->id)
                ->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Permanently deleted {$count} module(s).",
                'deleted_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to empty trash: ' . $e->getMessage(),
            ], 500);
        }
    }
}
