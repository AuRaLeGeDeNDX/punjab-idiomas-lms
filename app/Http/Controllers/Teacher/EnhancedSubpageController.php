<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EnhancedSubpageController extends Controller
{
    /**
     * Display subpages for a module with enhanced features.
     */
    public function index(Request $request, Course $course, Module $module)
    {
        $this->authorize('view', $course);
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        // Handle AJAX requests for pagination/search
        if ($request->wantsJson()) {
            return $this->getSubpagesJson($request, $course, $module);
        }

        // Handle CSV export
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportSubpages($request, $course, $module);
        }

        // For regular page load, return the view
        $stats = $this->getSubpageStats($module);
        
        return view('teacher.subpages.enhanced-index', compact('course', 'module', 'stats'));
    }

    /**
     * Get subpages as JSON for AJAX requests.
     */
    private function getSubpagesJson(Request $request, Course $course, Module $module): JsonResponse
    {
        $query = $module->subpages()
            ->with(['contents', 'exercises', 'assignments']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'deleted':
                    $query->onlyTrashed();
                    break;
            }
        } else {
            // Include soft deleted by default for teachers
            $query->withTrashed();
        }

        // Apply sorting
        switch ($request->get('sort', 'order')) {
            case 'title':
                $query->orderBy('title');
                break;
            case 'updated':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'content_count':
                $query->withCount('contents')->orderBy('contents_count', 'desc');
                break;
            default:
                $query->orderBy('order_index');
        }

        // Paginate results
        $perPage = $request->get('per_page', 20);
        $subpages = $query->paginate($perPage);

        // Transform data for frontend
        $transformedSubpages = $subpages->getCollection()->map(function ($subpage) use ($course, $module) {
            return [
                'id' => $subpage->id,
                'title' => $subpage->title,
                'description' => $subpage->description,
                'is_active' => $subpage->is_active,
                'deleted_at' => $subpage->deleted_at,
                'updated_at_human' => $subpage->updated_at->diffForHumans(),
                'contents_count' => $subpage->contents->count(),
                'exercises_count' => $subpage->exercises->count(),
                'assignments_count' => $subpage->assignments->count(),
                'show_url' => route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]),
                'edit_url' => route('teacher.courses.modules.subpages.edit', [$course, $module, $subpage]),
                'recent_activity' => $this->getRecentActivity($subpage),
            ];
        });

        return response()->json([
            'subpages' => $transformedSubpages,
            'current_page' => $subpages->currentPage(),
            'has_more_pages' => $subpages->hasMorePages(),
            'total' => $subpages->total(),
            'per_page' => $subpages->perPage(),
        ]);
    }

    /**
     * Get recent activity for a subpage.
     */
    private function getRecentActivity(Subpage $subpage): ?string
    {
        // Check for recent content additions
        $recentContent = $subpage->contents()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentContent > 0) {
            return "{$recentContent} new content item(s) this week";
        }

        // Check for recent exercises
        $recentExercises = $subpage->exercises()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentExercises > 0) {
            return "{$recentExercises} new exercise(s) this week";
        }

        // Check for recent assignments
        $recentAssignments = $subpage->assignments()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentAssignments > 0) {
            return "{$recentAssignments} new assignment(s) this week";
        }

        return null;
    }

    /**
     * Get subpage statistics for the module.
     */
    private function getSubpageStats(Module $module): array
    {
        return Cache::remember(
            "subpage_stats_{$module->id}",
            300, // 5 minutes
            function () use ($module) {
                $totalSubpages = $module->subpages()->withTrashed()->count();
                $activeSubpages = $module->subpages()->where('is_active', true)->count();
                $deletedSubpages = $module->subpages()->onlyTrashed()->count();

                return [
                    'total' => $totalSubpages,
                    'active' => $activeSubpages,
                    'inactive' => $totalSubpages - $activeSubpages - $deletedSubpages,
                    'deleted' => $deletedSubpages,
                ];
            }
        );
    }

    /**
     * Handle bulk actions on subpages.
     */
    public function bulkAction(Request $request, Course $course, Module $module): JsonResponse
    {
        $this->authorize('update', $course);
        
        if ($module->course_id !== $course->id) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete,restore',
            'subpage_ids' => 'required|array',
            'subpage_ids.*' => 'exists:subpages,id',
        ]);

        $subpageIds = $validated['subpage_ids'];
        $action = $validated['action'];

        // Verify all subpages belong to this module
        $query = Subpage::whereIn('id', $subpageIds)->where('module_id', $module->id);
        
        if ($action === 'restore') {
            $query->onlyTrashed();
        }

        $subpages = $query->get();

        if ($subpages->count() !== count($subpageIds)) {
            return response()->json(['success' => false, 'message' => 'Invalid subpage IDs'], 400);
        }

        $successCount = 0;
        $errors = [];

        DB::transaction(function () use ($subpages, $action, &$successCount, &$errors) {
            foreach ($subpages as $subpage) {
                try {
                    switch ($action) {
                        case 'activate':
                            $subpage->update(['is_active' => true]);
                            break;
                        case 'deactivate':
                            $subpage->update(['is_active' => false]);
                            break;
                        case 'delete':
                            $subpage->delete();
                            break;
                        case 'restore':
                            $subpage->restore();
                            break;
                    }
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to {$action} subpage '{$subpage->title}': " . $e->getMessage();
                }
            }
        });

        // Clear cache
        Cache::forget("subpage_stats_{$module->id}");
        Cache::forget("module_subpages_{$module->id}");

        $message = "Successfully {$action}d {$successCount} subpage(s)";
        
        if (!empty($errors)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'errors' => $errors
            ]);
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Export subpages to CSV.
     */
    private function exportSubpages(Request $request, Course $course, Module $module)
    {
        $query = $module->subpages()->withTrashed()->with(['contents', 'exercises', 'assignments']);

        // Apply same filters as the main query
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'deleted':
                    $query->onlyTrashed();
                    break;
            }
        }

        $subpages = $query->orderBy('order_index')->get();

        $filename = "subpages_{$course->id}_{$module->id}_" . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($subpages) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Title',
                'Description',
                'Status',
                'Order',
                'Content Items',
                'Exercises',
                'Assignments',
                'Created At',
                'Updated At',
                'Deleted At'
            ]);

            // CSV data
            foreach ($subpages as $subpage) {
                fputcsv($file, [
                    $subpage->id,
                    $subpage->title,
                    $subpage->description,
                    $subpage->is_active ? 'Active' : 'Inactive',
                    $subpage->order_index,
                    $subpage->contents->count(),
                    $subpage->exercises->count(),
                    $subpage->assignments->count(),
                    $subpage->created_at->format('Y-m-d H:i:s'),
                    $subpage->updated_at->format('Y-m-d H:i:s'),
                    $subpage->deleted_at ? $subpage->deleted_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Quick create subpage with minimal form.
     */
    public function quickCreate(Request $request, Course $course, Module $module): JsonResponse
    {
        $this->authorize('update', $course);
        
        if ($module->course_id !== $course->id) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['module_id'] = $module->id;
        $validated['order_index'] = Subpage::getNextOrderIndex($module->id);
        $validated['is_active'] = true;

        $subpage = Subpage::create($validated);

        // Clear cache
        Cache::forget("subpage_stats_{$module->id}");
        Cache::forget("module_subpages_{$module->id}");

        return response()->json([
            'success' => true,
            'message' => 'Subpage created successfully',
            'subpage' => [
                'id' => $subpage->id,
                'title' => $subpage->title,
                'show_url' => route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]),
                'edit_url' => route('teacher.courses.modules.subpages.edit', [$course, $module, $subpage]),
            ]
        ]);
    }

    /**
     * Duplicate a subpage with all its content.
     */
    public function duplicate(Course $course, Module $module, Subpage $subpage): JsonResponse
    {
        $this->authorize('update', $course);
        
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            return response()->json(['success' => false, 'message' => 'Subpage not found'], 404);
        }

        DB::transaction(function () use ($subpage, $module, &$newSubpage) {
            // Create duplicate subpage
            $newSubpage = $subpage->replicate();
            $newSubpage->title = $subpage->title . ' (Copy)';
            $newSubpage->order_index = Subpage::getNextOrderIndex($module->id);
            $newSubpage->save();

            // Duplicate content items
            foreach ($subpage->contents as $content) {
                $newContent = $content->replicate();
                $newContent->subpage_id = $newSubpage->id;
                $newContent->save();
            }

            // Duplicate exercises
            foreach ($subpage->exercises as $exercise) {
                $newExercise = $exercise->replicate();
                $newExercise->subpage_id = $newSubpage->id;
                $newExercise->save();
            }
        });

        // Clear cache
        Cache::forget("subpage_stats_{$module->id}");
        Cache::forget("module_subpages_{$module->id}");

        return response()->json([
            'success' => true,
            'message' => 'Subpage duplicated successfully',
            'subpage' => [
                'id' => $newSubpage->id,
                'title' => $newSubpage->title,
                'show_url' => route('teacher.courses.modules.subpages.show', [$course, $module, $newSubpage]),
                'edit_url' => route('teacher.courses.modules.subpages.edit', [$course, $module, $newSubpage]),
            ]
        ]);
    }

    /**
     * Get subpage templates for quick creation.
     */
    public function getTemplates(): JsonResponse
    {
        $templates = [
            [
                'id' => 'lesson',
                'name' => 'Lesson Page',
                'description' => 'Standard lesson with text content and exercises',
                'icon' => 'fas fa-book-open',
                'structure' => [
                    'contents' => ['introduction', 'main_content', 'summary'],
                    'exercises' => ['practice_questions'],
                ]
            ],
            [
                'id' => 'assignment',
                'name' => 'Assignment Page',
                'description' => 'Assignment instructions and submission area',
                'icon' => 'fas fa-tasks',
                'structure' => [
                    'contents' => ['instructions', 'requirements', 'rubric'],
                    'assignments' => ['main_assignment'],
                ]
            ],
            [
                'id' => 'quiz',
                'name' => 'Quiz Page',
                'description' => 'Interactive quiz with multiple question types',
                'icon' => 'fas fa-question-circle',
                'structure' => [
                    'contents' => ['quiz_instructions'],
                    'exercises' => ['quiz_questions'],
                ]
            ],
            [
                'id' => 'discussion',
                'name' => 'Discussion Page',
                'description' => 'Discussion topic with prompts',
                'icon' => 'fas fa-comments',
                'structure' => [
                    'contents' => ['discussion_prompt', 'guidelines'],
                ]
            ],
            [
                'id' => 'resource',
                'name' => 'Resource Page',
                'description' => 'Collection of resources and materials',
                'icon' => 'fas fa-folder-open',
                'structure' => [
                    'contents' => ['resource_list', 'additional_reading'],
                ]
            ]
        ];

        return response()->json(['templates' => $templates]);
    }
}