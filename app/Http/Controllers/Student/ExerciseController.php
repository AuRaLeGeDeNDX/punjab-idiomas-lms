<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Exercise;
use App\Models\ExerciseSubmission;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display exercises for a subpage (student view).
     * SECURITY: Students can only see questions, never answers.
     */
    public function index(Course $course, Module $module, Subpage $subpage): View
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        // Check if subpage is active
        if (!$subpage->is_active) {
            abort(404, 'This subpage is not available.');
        }

        // Get only active exercises with user's submissions
        $exercises = $subpage->exercises()
            ->active()
            ->with(['submissions' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->ordered()
            ->get();

        // SECURITY: Transform exercises to student-safe format
        $safeExercises = $exercises->map(function ($exercise) {
            $exerciseData = $exercise->toStudentArray();
            $exerciseData['user_submission'] = $exercise->submissions->first();
            return $exerciseData;
        });

        return view('student.exercises.index', compact('course', 'module', 'subpage', 'safeExercises'));
    }

    /**
     * Display the specified exercise (student view).
     * SECURITY: Students can only see questions, never answers.
     */
    public function show(Course $course, Module $module, Subpage $subpage, Exercise $exercise): View
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        // Check if exercise is active
        if (!$exercise->is_active) {
            abort(404, 'This exercise is not available.');
        }

        // Get user's submission if exists
        $submission = $exercise->submissionFor(auth()->user());

        // SECURITY: Use student-safe exercise data
        $safeExercise = $exercise->toStudentArray();

        return view('student.exercises.show', compact('course', 'module', 'subpage', 'safeExercise', 'submission'));
    }

    /**
     * Show the form for submitting to an exercise.
     */
    public function submit(Course $course, Module $module, Subpage $subpage, Exercise $exercise): View
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        // Check if user can submit
        if (!$exercise->canSubmit(auth()->user())) {
            return redirect()
                ->route('student.courses.modules.subpages.exercises.show', [$course, $module, $subpage, $exercise])
                ->with('error', 'You cannot submit to this exercise.');
        }

        // SECURITY: Use student-safe exercise data
        $safeExercise = $exercise->toStudentArray();

        return view('student.exercises.submit', compact('course', 'module', 'subpage', 'safeExercise'));
    }

    /**
     * Store a new submission.
     */
    public function storeSubmission(Request $request, Course $course, Module $module, Subpage $subpage, Exercise $exercise): RedirectResponse
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        // Check if user can submit
        if (!$exercise->canSubmit(auth()->user())) {
            return back()->with('error', 'You cannot submit to this exercise.');
        }

        // Validate based on submission type
        $rules = [
            'text_response' => $exercise->submission_type === 'file' ? 'nullable|string' : 'required|string',
            'file' => $exercise->submission_type === 'text' ? 'nullable' : 'required|file|max:51200', // 50MB
        ];

        $validated = $request->validate($rules);

        $submissionData = [
            'exercise_id' => $exercise->id,
            'user_id' => auth()->id(),
            'text_response' => $validated['text_response'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            $filePath = $this->fileService->storeSecurely(
                $file,
                "exercises/{$exercise->id}/submissions/" . auth()->id()
            );

            $submissionData['file_path'] = $filePath;
            $submissionData['file_name'] = $file->getClientOriginalName();
            $submissionData['file_size'] = $file->getSize();
            $submissionData['mime_type'] = $file->getMimeType();
        }

        ExerciseSubmission::create($submissionData);

        return redirect()
            ->route('student.courses.modules.subpages.exercises.show', [$course, $module, $subpage, $exercise])
            ->with('success', 'Your submission has been recorded successfully.');
    }

    /**
     * Download submission file.
     */
    public function downloadSubmission(Course $course, Module $module, Subpage $subpage, Exercise $exercise): mixed
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        $submission = $exercise->submissionFor(auth()->user());
        
        if (!$submission || !$submission->hasFile()) {
            abort(404, 'File not found');
        }

        return \Storage::download($submission->file_path, $submission->file_name);
    }

    /**
     * SECURITY: API endpoint to get exercise data (without answers).
     */
    public function apiShow(Course $course, Module $module, Subpage $subpage, Exercise $exercise): JsonResponse
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            return response()->json(['error' => 'Not enrolled'], 403);
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            return response()->json(['error' => 'Exercise not found'], 404);
        }

        // Check if exercise is active
        if (!$exercise->is_active) {
            return response()->json(['error' => 'Exercise not available'], 404);
        }

        // Get user's submission if exists
        $submission = $exercise->submissionFor(auth()->user());

        // SECURITY: Return only student-safe data
        return response()->json([
            'exercise' => $exercise->toStudentArray(),
            'submission' => $submission ? [
                'id' => $submission->id,
                'text_response' => $submission->text_response,
                'file_name' => $submission->file_name,
                'file_size' => $submission->formatted_file_size,
                'status' => $submission->status,
                'score' => $submission->score,
                'feedback' => $submission->feedback,
                'submitted_at' => $submission->submitted_at->toISOString(),
                'graded_at' => $submission->graded_at?->toISOString(),
                'is_late' => $submission->isLate(),
            ] : null,
        ]);
    }
}