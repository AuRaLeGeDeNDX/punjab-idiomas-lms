<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    protected CourseService $courseService;
    protected EnrollmentService $enrollmentService;

    public function __construct(CourseService $courseService, EnrollmentService $enrollmentService)
    {
        $this->courseService = $courseService;
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Display a listing of published courses available for enrollment.
     */
    public function index(Request $request): View
    {
        $courses = $this->courseService->getPublishedCourses();
        
        // Apply filters if provided
        if ($request->has('category') && $request->category) {
            $courses = $courses->where('category', $request->category);
        }
        
        if ($request->has('difficulty') && $request->difficulty) {
            $courses = $courses->where('difficulty_level', $request->difficulty);
        }
        
        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search);
            $courses = $courses->filter(function ($course) use ($search) {
                return str_contains(strtolower($course->title), $search) ||
                       str_contains(strtolower($course->description), $search);
            });
        }
        
        // Get unique categories and difficulty levels for filters
        $allCourses = $this->courseService->getPublishedCourses();
        $categories = $allCourses->pluck('category')->filter()->unique()->sort();
        $difficultyLevels = $allCourses->pluck('difficulty_level')->filter()->unique()->sort();
        
        return view('student.courses.index', compact('courses', 'categories', 'difficultyLevels'));
    }

    /**
     * Display the specified course details.
     */
    public function show(Course $course): View
    {
        // Ensure course is published or user has access
        if (!$course->is_published && !Auth::user()->hasRole(['teacher', 'admin'])) {
              return view('student.courses.coming-soon', compact('course'));
        }
        
        $courseWithModules = $this->courseService->getCourseWithModules($course->id);
        
        // Check if user is enrolled
        $isEnrolled = Auth::user()->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();
        
        // Check prerequisites
        $prerequisitesMet = $this->courseService->checkPrerequisites($course, Auth::user());
        
        // Get enrollment count
        $enrollmentCount = $course->enrollments()->where('status', 'active')->count();
        
        return view('student.courses.show', compact(
            'courseWithModules',
            'isEnrolled',
            'prerequisitesMet',
            'enrollmentCount'
        ));
    }

    /**
     * Display course modules for enrolled students.
     */
    public function modules(Course $course): View
    {
        // Check if user is enrolled
        $enrollment = Auth::user()->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->first();
        
        if (!$enrollment) {
            return redirect()
                ->route('student.courses.show', $course)
                ->withErrors(['error' => 'You must be enrolled in this course to access modules.']);
        }
        
        // Use cached course with modules
        $courseWithModules = $this->courseService->getCourseWithModules($course->id);
        
        return view('student.courses.modules', compact('courseWithModules', 'enrollment'));
    }

    /**
     * Display a specific module for enrolled students.
     */
    public function showModule(Course $course, $moduleId): View
    {
        // Check if user is enrolled
        $enrollment = Auth::user()->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->first();
        
        if (!$enrollment) {
            return redirect()
                ->route('student.courses.show', $course)
                ->withErrors(['error' => 'You must be enrolled in this course to access modules.']);
        }
        
        $module = $course->modules()
            ->where('id', $moduleId)
            ->where('is_published', true)
            ->firstOrFail();
        
        // Track module access
        $this->enrollmentService->markModuleAccessed($enrollment, $module);
        
        return view('student.courses.module', compact('course', 'module', 'enrollment'));
    }

    /**
     * Get courses the student is enrolled in.
     */
    public function enrolled(): View
    {
        $enrollments = $this->enrollmentService->getEnrolledCoursesWithProgress(Auth::user());
        
        return view('student.courses.enrolled', compact('enrollments'));
    }

    /**
     * Redirect enrollment attempts to course assignment information.
     * Students cannot enroll themselves - only Admins and Teachers can assign students.
     */
    public function enroll(Course $course): RedirectResponse
    {
        return redirect()
            ->route('student.courses.show', $course)
            ->withErrors(['enrollment' => 'Students cannot enroll themselves in courses. Please contact your teacher or administrator for course assignment.']);
    }

    /**
     * Redirect unenrollment attempts to course assignment information.
     * Students cannot unenroll themselves - only Admins and Teachers can remove students.
     */
    public function unenroll(Course $course): RedirectResponse
    {
        return redirect()
            ->route('student.courses.show', $course)
            ->withErrors(['enrollment' => 'Students cannot unenroll themselves from courses. Please contact your teacher or administrator to be removed from the course.']);
    }

    /**
     * Show detailed progress for a specific enrollment.
     */
    public function progress(Course $course): View
    {
        $enrollment = Auth::user()->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->first();
        
        if (!$enrollment) {
            return redirect()
                ->route('student.courses.show', $course)
                ->withErrors(['error' => 'You must be enrolled in this course to view progress.']);
        }

        $progressData = $this->enrollmentService->trackProgress($enrollment);
        $learningRecord = $this->enrollmentService->getLearningRecord(Auth::user());
        
        return view('student.courses.progress', compact('course', 'enrollment', 'progressData', 'learningRecord'));
    }
}
