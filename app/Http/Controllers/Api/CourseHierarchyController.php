<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CourseHierarchyController extends Controller
{
    /**
     * Get course hierarchy data for editing.
     */
    public function getCourseHierarchy(Course $course): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        $courseData = $course->load([
            'modules' => function ($query) {
                $query->orderBy('order_index');
            },
            'modules.subpages' => function ($query) {
                $query->orderBy('order_index');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $courseData,
                'modules' => $courseData->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'title' => $module->title,
                        'description' => $module->description,
                        'order_index' => $module->order_index,
                        'is_published' => $module->is_published,
                        'subpages_count' => $module->subpages->count(),
                        'subpages' => $module->subpages->map(function ($subpage) {
                            return [
                                'id' => $subpage->id,
                                'title' => $subpage->title,
                                'description' => $subpage->description,
                                'order_index' => $subpage->order_index,
                                'is_active' => $subpage->is_active,
                            ];
                        })
                    ];
                })
            ]
        ]);
    }

    /**
     * Create a new module.
     */
    public function createModule(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Get next order index
            $nextOrder = $course->modules()->max('order_index') + 1;

            $module = $course->modules()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'order_index' => $nextOrder,
                'is_published' => false,
            ]);

            // Clear course cache
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:{$course->id}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Module created successfully',
                'data' => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'description' => $module->description,
                    'order_index' => $module->order_index,
                    'is_published' => $module->is_published,
                    'subpages_count' => 0,
                    'subpages' => []
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create module: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a module.
     */
    public function updateModule(Request $request, Course $course, Module $module): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        if ($module->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $module->update($validated);

            // Clear course cache
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:{$course->id}");

            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully',
                'data' => [
                    'id' => $module->id,
                    'title' => $module->title,
                    'description' => $module->description,
                    'order_index' => $module->order_index,
                    'is_published' => $module->is_published,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a module.
     */
    public function deleteModule(Course $course, Module $module): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        if ($module->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Check if module has subpages
            $subpagesCount = $module->subpages()->count();
            if ($subpagesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete module with {$subpagesCount} subpages. Please delete or move subpages first."
                ], 400);
            }

            $module->delete();

            // Reorder remaining modules
            $this->reorderModulesAfterDeletion($course, $module->order_index);

            // Clear course cache
            \Cache::forget("course:modules:{$course->id}");
            \Cache::forget("course:details:{$course->id}");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder modules.
     */
    public function reorderModules(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        $validated = $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'integer|exists:modules,id',
        ]);

        try {
            DB::beginTransaction();

            // Verify all modules belong to this course
            $moduleIds = $validated['module_ids'];
            $courseModuleIds = $course->modules()->pluck('id')->toArray();
            
            if (array_diff($moduleIds, $courseModuleIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid module IDs provided'
                ], 400);
            }

            // Update order indices
            foreach ($moduleIds as $index => $moduleId) {
                Module::where('id', $moduleId)->update(['order_index' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Modules reordered successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder modules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new subpage.
     */
    public function createSubpage(Request $request, Course $course, Module $module): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        if ($module->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Get next order index
            $nextOrder = $module->subpages()->max('order_index') + 1;

            $subpage = $module->subpages()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'order_index' => $nextOrder,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subpage created successfully',
                'data' => [
                    'id' => $subpage->id,
                    'title' => $subpage->title,
                    'description' => $subpage->description,
                    'order_index' => $subpage->order_index,
                    'is_active' => $subpage->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subpage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a subpage.
     */
    public function updateSubpage(Request $request, Course $course, Module $module, Subpage $subpage): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            return response()->json([
                'success' => false,
                'message' => 'Subpage not found'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $subpage->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subpage updated successfully',
                'data' => [
                    'id' => $subpage->id,
                    'title' => $subpage->title,
                    'description' => $subpage->description,
                    'order_index' => $subpage->order_index,
                    'is_active' => $subpage->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subpage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a subpage.
     */
    public function deleteSubpage(Course $course, Module $module, Subpage $subpage): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            return response()->json([
                'success' => false,
                'message' => 'Subpage not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Check if subpage has content, exercises, or assignments
            $contentCount = $subpage->contents()->count();
            $exerciseCount = $subpage->exercises()->count();
            $assignmentCount = $subpage->assignments()->count();

            if ($contentCount > 0 || $exerciseCount > 0 || $assignmentCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete subpage with existing content ({$contentCount} content items, {$exerciseCount} exercises, {$assignmentCount} assignments). Please remove content first."
                ], 400);
            }

            $subpage->delete();

            // Reorder remaining subpages
            $this->reorderSubpagesAfterDeletion($module, $subpage->order_index);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subpage deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subpage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder subpages within a module.
     */
    public function reorderSubpages(Request $request, Course $course, Module $module): JsonResponse
    {
        Gate::authorize('manageModules', $course);

        if ($module->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found'
            ], 404);
        }

        $validated = $request->validate([
            'subpage_ids' => 'required|array',
            'subpage_ids.*' => 'integer|exists:subpages,id',
        ]);

        try {
            DB::beginTransaction();

            // Verify all subpages belong to this module
            $subpageIds = $validated['subpage_ids'];
            $moduleSubpageIds = $module->subpages()->pluck('id')->toArray();
            
            if (array_diff($subpageIds, $moduleSubpageIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subpage IDs provided'
                ], 400);
            }

            // Update order indices
            foreach ($subpageIds as $index => $subpageId) {
                Subpage::where('id', $subpageId)->update(['order_index' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subpages reordered successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder subpages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder modules after deletion.
     */
    private function reorderModulesAfterDeletion(Course $course, int $deletedOrder): void
    {
        $course->modules()
            ->where('order_index', '>', $deletedOrder)
            ->decrement('order_index');
    }

    /**
     * Reorder subpages after deletion.
     */
    private function reorderSubpagesAfterDeletion(Module $module, int $deletedOrder): void
    {
        $module->subpages()
            ->where('order_index', '>', $deletedOrder)
            ->decrement('order_index');
    }
}