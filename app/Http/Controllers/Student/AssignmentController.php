<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Submission;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a general overview of all assignments for the student.
     */
    public function overview()
    {
        $user = Auth::user();
        
        // Get all assignments from courses the student is enrolled in
        $assignments = Assignment::whereHas('course.enrollments', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', 'active');
        })
        ->where('is_published', true)
        ->with([
            'course',
            'module',
            'subpage',
            'submissions' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        ])
        ->orderBy('due_date', 'asc')
        ->get();

        // Categorize assignments
        $upcomingAssignments = $assignments->filter(function ($assignment) {
            return $assignment->due_date && $assignment->due_date->isFuture() && !$assignment->hasSubmissionFrom(Auth::user());
        });

        $overdueAssignments = $assignments->filter(function ($assignment) {
            return $assignment->due_date && $assignment->due_date->isPast() && !$assignment->hasSubmissionFrom(Auth::user());
        });

        $submittedAssignments = $assignments->filter(function ($assignment) {
            return $assignment->hasSubmissionFrom(Auth::user());
        });

        $completedAssignments = $assignments->filter(function ($assignment) {
            $submission = $assignment->submissionFor(Auth::user());
            return $submission && $submission->grade && $submission->grade->is_published;
        });

        return view('student.assignments.overview', compact(
            'upcomingAssignments',
            'overdueAssignments', 
            'submittedAssignments',
            'completedAssignments'
        ));
    }

    /**
     * Display a listing of assignments for a subpage.
     */
    public function index(Course $course, Module $module, Subpage $subpage)
    {
        Gate::authorize('view', $subpage);

        $assignments = $subpage->publishedAssignments()
            ->with(['submissions' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get();

        return view('student.assignments.index', compact('course', 'module', 'subpage', 'assignments'));
    }

    /**
     * Display the specified assignment.
     */
    public function show(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $submission = $assignment->submissionFor(Auth::user());
        $canSubmit = $assignment->canSubmit(Auth::user());

        return view('student.assignments.show', compact('course', 'module', 'subpage', 'assignment', 'submission', 'canSubmit'));
    }

    /**
     * Show the form for creating a submission.
     */
    public function createSubmission(Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('submit', $assignment);

        if (!$assignment->canSubmit(Auth::user())) {
            return redirect()
                ->route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment])
                ->with('error', 'You cannot submit to this assignment.');
        }

        return view('student.assignments.submit', compact('course', 'module', 'subpage', 'assignment'));
    }

    /**
     * Store a submission for the assignment.
     */
    public function storeSubmission(Request $request, Course $course, Module $module, Subpage $subpage, Assignment $assignment)
    {
        Gate::authorize('submit', $assignment);

        if (!$assignment->canSubmit(Auth::user())) {
            return redirect()
                ->route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment])
                ->with('error', 'You cannot submit to this assignment.');
        }

        $rules = [
            'content' => 'nullable|string',
            'files' => 'nullable|array|max:5',
            'files.*' => 'file|max:51200', // 50MB per file
        ];

        // Validate based on submission type
        if ($assignment->submission_type === 'text') {
            $rules['content'] = 'required|string|min:10';
        } elseif ($assignment->submission_type === 'file') {
            $rules['files'] = 'required|array|min:1|max:5';
        } elseif ($assignment->submission_type === 'both') {
            $rules['content'] = 'required_without:files|string|min:10';
            $rules['files'] = 'required_without:content|array|max:5';
        }

        $validated = $request->validate($rules);

        $filePaths = [];
        $totalFileSize = 0;

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Store file using Laravel's storage system directly
                $path = $file->store('assignments/' . $assignment->id, 'local');
                $filePaths[] = $path;
                $totalFileSize += $file->getSize();
            }
        }

        // Check if submission is late
        $isLate = $assignment->due_date && $assignment->due_date->isPast();

        // Create submission
        $submission = new Submission([
            'assignment_id' => $assignment->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'] ?? null,
            'file_paths' => $filePaths,
            'submitted_at' => now(),
            'is_late' => $isLate,
            'attempt_number' => 1,
            'status' => 'submitted',
            'submission_type' => $assignment->submission_type,
            'word_count' => !empty($validated['content']) ? str_word_count($validated['content']) : 0,
            'file_size_bytes' => $totalFileSize,
        ]);

        $submission->save();

        return redirect()
            ->route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment])
            ->with('success', 'Assignment submitted successfully.');
    }

    /**
     * Show the submission for editing.
     */
    public function editSubmission(Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission)
    {
        Gate::authorize('update', $submission);

        if ($submission->isGraded()) {
            return redirect()
                ->route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment])
                ->with('error', 'Cannot edit a graded submission.');
        }

        return view('student.assignments.edit', compact('course', 'module', 'subpage', 'assignment', 'submission'));
    }

    /**
     * Update the submission.
     */
    public function updateSubmission(Request $request, Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission)
    {
        Gate::authorize('update', $submission);

        if ($submission->isGraded()) {
            return redirect()
                ->route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment])
                ->with('error', 'Cannot edit a graded submission.');
        }

        $rules = [
            'content' => 'nullable|string',
            'files' => 'nullable|array|max:5',
            'files.*' => 'file|max:51200', // 50MB per file
            'remove_files' => 'nullable|array',
            'remove_files.*' => 'string',
        ];

        // Validate based on submission type
        if ($assignment->submission_type === 'text') {
            $rules['content'] = 'required|string|min:10';
        } elseif ($assignment->submission_type === 'file') {
            $rules['files'] = 'required_without:existing_files|array|max:5';
        }

        $validated = $request->validate($rules);

        $filePaths = $submission->getFilePaths();
        $totalFileSize = $submission->file_size_bytes ?? 0;

        // Remove files if requested
        if (!empty($validated['remove_files'])) {
            foreach ($validated['remove_files'] as $fileToRemove) {
                if (in_array($fileToRemove, $filePaths)) {
                    Storage::delete($fileToRemove);
                    $filePaths = array_filter($filePaths, fn($path) => $path !== $fileToRemove);
                }
            }
        }

        // Add new files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Store file using Laravel's storage system directly
                $path = $file->store('assignments/' . $assignment->id, 'local');
                $filePaths[] = $path;
                $totalFileSize += $file->getSize();
            }
        }

        // Update submission
        $submission->update([
            'content' => $validated['content'] ?? $submission->content,
            'file_paths' => array_values($filePaths),
            'word_count' => !empty($validated['content']) ? str_word_count($validated['content']) : $submission->word_count,
            'file_size_bytes' => $totalFileSize,
            'status' => 'submitted', // Reset to submitted after edit
        ]);

        return redirect()
            ->route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment])
            ->with('success', 'Submission updated successfully.');
    }

    /**
     * Download a submission file.
     */
    public function downloadFile(Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission, string $filename)
    {
        Gate::authorize('view', $submission);

        $filePaths = $submission->getFilePaths();
        $requestedPath = null;

        foreach ($filePaths as $path) {
            if (basename($path) === $filename) {
                $requestedPath = $path;
                break;
            }
        }

        if (!$requestedPath || !Storage::exists($requestedPath)) {
            abort(404, 'File not found.');
        }

        return Storage::download($requestedPath, $filename);
    }
}