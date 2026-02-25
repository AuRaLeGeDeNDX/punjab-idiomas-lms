<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Display a listing of all courses.
     */
    public function index(Request $request): View
    {
        $query = Course::with('teacher')->withCount('enrollments');
        
        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'unpublished') {
                $query->where('is_published', false);
            }
        }
        
        if ($request->has('teacher') && $request->teacher) {
            $query->where('teacher_id', $request->teacher);
        }
        
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $courses = $query->paginate(20);
        
        // Get filter options (Cached for performance)
        $teachers = \Illuminate\Support\Facades\Cache::remember('teachers_list', 3600, function () {
            return User::role('Teacher')->orderBy('name')->get();
        });
        
        $categories = \Illuminate\Support\Facades\Cache::remember('course_categories', 3600, function () {
            return Course::distinct()->pluck('category')->filter()->sort();
        });
        
        return view('admin.courses.index', compact('courses', 'teachers', 'categories'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create(): View
    {
        $teachers = \Illuminate\Support\Facades\Cache::remember('teachers_list', 3600, function () {
            return User::role('Teacher')->orderBy('name')->get();
        });
        
        return view('admin.courses.create', compact('teachers'));
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'required|exists:users,id',
            'category' => 'nullable|string|max:100',
            'difficulty_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration_hours' => 'nullable|integer|min:1',
            'max_students' => 'nullable|integer|min:1',
            'enrollment_start_date' => 'nullable|date',
            'enrollment_end_date' => 'nullable|date|after_or_equal:enrollment_start_date',
            'course_start_date' => 'nullable|date',
            'course_end_date' => 'nullable|date|after_or_equal:course_start_date',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        try {
            $teacher = User::findOrFail($validated['teacher_id']);
            $course = $this->courseService->createCourse($validated, $teacher);
            
            return redirect()
                ->route('admin.courses.show', $course)
                ->with('success', 'Course created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create course: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course): View
    {
        $courseWithModules = $this->courseService->getCourseWithModules($course->id);
        
        // Get trashed modules for recovery
        $trashedModules = \App\Models\Module::onlyTrashed()
            ->where('course_id', $course->id)
            ->get();
        
        // Get enrollment statistics
        $enrollmentStats = [
            'total' => $course->enrollments()->count(),
            'active' => $course->enrollments()->where('status', 'active')->count(),
            'completed' => $course->enrollments()->where('status', 'completed')->count(),
            'dropped' => $course->enrollments()->where('status', 'dropped')->count(),
        ];
        
        return view('admin.courses.show', compact('courseWithModules', 'enrollmentStats', 'trashedModules'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course): View
    {
        $teachers = \Illuminate\Support\Facades\Cache::remember('teachers_list', 3600, function () {
            return User::role('Teacher')->orderBy('name')->get();
        });
        
        return view('admin.courses.edit', compact('course', 'teachers'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'required|exists:users,id',
            'category' => 'nullable|string|max:100',
            'difficulty_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration_hours' => 'nullable|integer|min:1',
            'max_students' => 'nullable|integer|min:1',
            'enrollment_start_date' => 'nullable|date',
            'enrollment_end_date' => 'nullable|date|after_or_equal:enrollment_start_date',
            'course_start_date' => 'nullable|date',
            'course_end_date' => 'nullable|date|after_or_equal:course_start_date',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ]);
          // Checkboxes don't send value when unchecked, so default to false
    $validated['is_published'] = $request->has('is_published');
    $validated['is_featured'] = $request->has('is_featured');

        try {
            $this->courseService->updateCourse($course, $validated);
            
            return redirect()
                ->route('admin.courses.show', $course)
                ->with('success', 'Course updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update course: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course): RedirectResponse
    {
        try {
            $this->courseService->deleteCourse($course);
            
            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'Course deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete course: ' . $e->getMessage()]);
        }
    }

    /**
     * Display trashed courses.
     */
    public function trashed(Request $request): View
    {
        $courses = Course::onlyTrashed()->with('teacher')->paginate(20);
        return view('admin.courses.trashed', compact('courses'));
    }

    /**
     * Restore a trashed course.
     */
    public function restore($id): RedirectResponse
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();
        return redirect()->route('admin.courses.trashed')->with('success', 'Course restored successfully.');
    }

    /**
     * Force delete a course permanently.
     */
    public function forceDelete($id): RedirectResponse
    {
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->forceDelete();
        return redirect()->route('admin.courses.trashed')->with('success', 'Course permanently deleted.');
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        Course::onlyTrashed()->forceDelete();
        return redirect()->route('admin.courses.trashed')->with('success', 'Trash emptied successfully.');
    }

    /**
     * Bulk actions for courses.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,unpublish,delete',
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        $courses = Course::whereIn('id', $validated['course_ids'])->get();
        $successCount = 0;
        $errors = [];

        foreach ($courses as $course) {
            try {
                switch ($validated['action']) {
                    case 'publish':
                        $this->courseService->publishCourse($course);
                        break;
                    case 'unpublish':
                        $this->courseService->unpublishCourse($course);
                        break;
                    case 'delete':
                        $this->courseService->deleteCourse($course);
                        break;
                }
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to {$validated['action']} course '{$course->title}': " . $e->getMessage();
            }
        }

        $message = "Successfully {$validated['action']}ed {$successCount} course(s).";
        
        if (!empty($errors)) {
            return back()
                ->with('success', $message)
                ->withErrors($errors);
        }

        return back()->with('success', $message);
    }
}
