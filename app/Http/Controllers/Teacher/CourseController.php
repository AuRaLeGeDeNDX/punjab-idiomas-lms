<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Display a listing of the teacher's courses.
     */
    public function index(): View
    {
        $courses = $this->courseService->getCoursesByTeacher(Auth::user());
        
        return view('teacher.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create(): View
    {
        return view('teacher.courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'difficulty_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration_hours' => 'nullable|integer|min:1',
            'max_students' => 'nullable|integer|min:1',
            'enrollment_start_date' => 'nullable|date',
            'enrollment_end_date' => 'nullable|date|after_or_equal:enrollment_start_date',
            'course_start_date' => 'nullable|date',
            'course_end_date' => 'nullable|date|after_or_equal:course_start_date',
        ]);

        try {
            $course = $this->courseService->createCourse($validated, Auth::user());
            
            return redirect()
                ->route('teacher.courses.show', $course)
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
        $this->authorize('view', $course);
        
        $courseWithModules = $this->courseService->getCourseWithModules($course->id);
        
        // Get trashed modules for recovery
        $trashedModules = \App\Models\Module::onlyTrashed()
            ->where('course_id', $course->id)
            ->get();
        
        return view('teacher.courses.show', compact('courseWithModules', 'trashedModules'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course): View
    {
        $this->authorize('update', $course);
        
        // Load relationships and counts
        $course->loadCount(['enrollments', 'modules', 'assignments']);
        
        return view('teacher.courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'difficulty_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration_hours' => 'nullable|integer|min:1',
            'max_students' => 'nullable|integer|min:1',
            'enrollment_start_date' => 'nullable|date',
            'enrollment_end_date' => 'nullable|date|after_or_equal:enrollment_start_date',
            'course_start_date' => 'nullable|date',
            'course_end_date' => 'nullable|date|after_or_equal:course_start_date',
        ]);

        try {
            $this->courseService->updateCourse($course, $validated);
            
            return redirect()
                ->route('teacher.courses.show', $course)
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
        $this->authorize('delete', $course);
        
        try {
            $this->courseService->deleteCourse($course);
            
            return redirect()
                ->route('teacher.courses.index')
                ->with('success', 'Course deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete course: ' . $e->getMessage()]);
        }
    }

    /**
     * Publish a course.
     */
    public function publish(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        
        try {
            $this->courseService->publishCourse($course);
            
            return back()->with('success', 'Course published successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to publish course: ' . $e->getMessage()]);
        }
    }

    /**
     * Unpublish a course.
     */
    public function unpublish(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);
        
        try {
            $this->courseService->unpublishCourse($course);
            
            return back()->with('success', 'Course unpublished successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to unpublish course: ' . $e->getMessage()]);
        }
    }
}