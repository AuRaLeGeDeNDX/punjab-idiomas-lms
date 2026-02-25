<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Exercise;
use App\Models\ExerciseSubmission;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display all submissions for an assignment.
     */
    public function index(Course $course, Module $module, Subpage $subpage, Assignment $assignment): View
    {
        Gate::authorize('view', $assignment);

        $submissions = $assignment->submissions()
            ->with(['user', 'grade.grader'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        $stats = $assignment->getSubmissionStats();

        return view('teacher.assignments.submissions.index', compact('course', 'module', 'subpage', 'assignment', 'submissions', 'stats'));
    }

    /**
     * Display a specific submission.
     */
    public function show(Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission): View
    {
        // Load relationships needed for authorization
        $submission->load('assignment.course');
        
        Gate::authorize('view', $submission);

        $submission->load(['user', 'grade.grader', 'files']);

        return view('teacher.assignments.submissions.show', compact('course', 'module', 'subpage', 'assignment', 'submission'));
    }

    /**
     * Show the grading form for a submission.
     */
    public function grade(Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission): View
    {
        Gate::authorize('grade', $submission);

        $submission->load(['user', 'grade']);

        return view('teacher.assignments.submissions.grade', compact('course', 'module', 'subpage', 'assignment', 'submission'));
    }

    /**
     * Store or update a grade for a submission.
     */
    public function storeGrade(Request $request, Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission): RedirectResponse
    {
        Gate::authorize('grade', $submission);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string|max:5000',
            'rubric_scores' => 'nullable|array',
            'grade_letter' => 'nullable|string|in:A,B,C,D,F',
            'publish_immediately' => 'nullable|boolean',
        ]);

        // Create or update grade
        $grade = $submission->grade ?? new Grade();
        $grade->fill([
            'submission_id' => $submission->id,
            'grader_id' => auth()->id(),
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'rubric_scores' => $validated['rubric_scores'] ?? [],
            'grade_letter' => $validated['grade_letter'] ?? null,
            'graded_at' => now(),
            'is_published' => false,
        ]);

        $grade->save();

        // Mark submission as graded
        $submission->markAsGraded();

        // Publish immediately if requested (default to true if not specified)
        $publishImmediately = $request->has('publish_immediately') ? (bool)$request->input('publish_immediately') : true;
        
        if ($publishImmediately) {
            $grade->publish();
            $message = 'Grade saved and published successfully.';
        } else {
            $message = 'Grade saved successfully. Remember to publish it when ready.';
        }

        return redirect()
            ->route('teacher.courses.modules.subpages.assignments.submissions.show', [$course, $module, $subpage, $assignment, $submission])
            ->with('success', $message);
    }

    /**
     * Publish a grade.
     */
    public function publishGrade(Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission): RedirectResponse
    {
        Gate::authorize('grade', $submission);

        $grade = $submission->grade;
        if (!$grade) {
            return back()->with('error', 'No grade found for this submission.');
        }

        $grade->publish();

        return back()->with('success', 'Grade published successfully.');
    }

    /**
     * Download a submission file.
     */
    public function downloadFile(Course $course, Module $module, Subpage $subpage, Assignment $assignment, Submission $submission, string $filename): mixed
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

    /**
     * Bulk grade multiple submissions.
     */
    public function bulkGrade(Request $request, Course $course, Module $module, Subpage $subpage, Assignment $assignment): JsonResponse
    {
        Gate::authorize('grade', $assignment);

        $validated = $request->validate([
            'submissions' => 'required|array',
            'submissions.*.id' => 'required|exists:submissions,id',
            'submissions.*.score' => 'required|numeric|min:0|max:' . $assignment->max_score,
            'submissions.*.feedback' => 'nullable|string|max:5000',
            'publish_all' => 'boolean',
        ]);

        $gradedCount = 0;

        foreach ($validated['submissions'] as $submissionData) {
            $submission = Submission::where('id', $submissionData['id'])
                ->where('assignment_id', $assignment->id)
                ->first();

            if ($submission) {
                // Create or update grade
                $grade = $submission->grade ?? new Grade();
                $grade->fill([
                    'submission_id' => $submission->id,
                    'grader_id' => auth()->id(),
                    'score' => $submissionData['score'],
                    'feedback' => $submissionData['feedback'] ?? null,
                    'graded_at' => now(),
                    'is_published' => $validated['publish_all'] ?? false,
                ]);

                if ($validated['publish_all'] ?? false) {
                    $grade->published_at = now();
                }

                $grade->save();
                $submission->markAsGraded();

                if ($validated['publish_all'] ?? false) {
                    \App\Jobs\SendGradeNotification::dispatch($grade);
                }

                $gradedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully graded {$gradedCount} submissions." . 
                        ($validated['publish_all'] ? ' Grades have been published.' : '')
        ]);
    }

    /**
     * Export submissions as CSV.
     */
    public function export(Course $course, Module $module, Subpage $subpage, Assignment $assignment): mixed
    {
        Gate::authorize('view', $assignment);

        $submissions = $assignment->submissions()
            ->with(['user', 'grade.grader'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        $csvData = [];
        $csvData[] = [
            'Student Name',
            'Student Email',
            'Submitted At',
            'Status',
            'Is Late',
            'Score',
            'Max Score',
            'Percentage',
            'Letter Grade',
            'Feedback',
            'Graded By',
            'Graded At',
            'Published',
            'Word Count',
            'File Count',
            'File Size'
        ];

        foreach ($submissions as $submission) {
            $grade = $submission->grade;
            $csvData[] = [
                $submission->user->name,
                $submission->user->email,
                $submission->submitted_at->format('Y-m-d H:i:s'),
                $submission->status,
                $submission->isLate() ? 'Yes' : 'No',
                $grade?->score ?? 'Not graded',
                $assignment->max_score,
                $grade ? round($grade->getPercentage(), 2) . '%' : 'N/A',
                $grade?->getLetterGrade() ?? 'N/A',
                $grade?->feedback ?? '',
                $grade?->grader?->name ?? '',
                $grade?->graded_at?->format('Y-m-d H:i:s') ?? '',
                $grade?->isPublished() ? 'Yes' : 'No',
                $submission->word_count ?? 0,
                count($submission->getFilePaths()),
                $submission->getFormattedFileSize()
            ];
        }

        $filename = "assignment_{$assignment->id}_submissions_" . now()->format('Y-m-d') . ".csv";

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ========== EXERCISE SUBMISSION METHODS (EXISTING) ==========

    /**
     * Display all submissions for an exercise.
     */
    public function indexExercise(Course $course, Module $module, Subpage $subpage, Exercise $exercise): View
    {
        $this->authorize('view', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id) {
            abort(404);
        }

        $submissions = $exercise->submissions()
            ->with(['user', 'grader'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('teacher.submissions.index', compact('course', 'module', 'subpage', 'exercise', 'submissions'));
    }

    /**
     * Display a specific exercise submission for grading.
     */
    public function showExercise(Course $course, Module $module, Subpage $subpage, Exercise $exercise, ExerciseSubmission $submission): View
    {
        $this->authorize('view', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id ||
            $submission->exercise_id !== $exercise->id) {
            abort(404);
        }

        $submission->load(['user', 'grader']);

        return view('teacher.submissions.show', compact('course', 'module', 'subpage', 'exercise', 'submission'));
    }

    /**
     * Grade an exercise submission.
     */
    public function gradeExercise(Request $request, Course $course, Module $module, Subpage $subpage, Exercise $exercise, ExerciseSubmission $submission): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $exercise->subpage_id !== $subpage->id ||
            $submission->exercise_id !== $exercise->id) {
            abort(404);
        }

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $exercise->max_score,
            'feedback' => 'nullable|string|max:2000',
        ]);

        $submission->grade(
            $validated['score'],
            $validated['feedback'],
            auth()->user()
        );

        return redirect()
            ->route('teacher.courses.modules.subpages.exercises.submissions.show', 
                   [$course, $module, $subpage, $exercise, $submission])
            ->with('success', 'Submission graded successfully.');
    }
}