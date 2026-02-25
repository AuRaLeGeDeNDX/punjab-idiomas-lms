<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;

class GradeBookController extends Controller
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Display the grade book for a course.
     */
    public function index(Course $course, Request $request)
    {
        Gate::authorize('view', $course);

        // Apply filters to grade book query
        $filters = $request->only([
            'assignment_id', 'student_name', 'status', 'grade_min', 'grade_max', 
            'submitted_from', 'submitted_to', 'graded', 'late', 'search'
        ]);

        $gradeBook = $this->gradingService->getGradeBook($course, $filters);
        $statistics = $this->gradingService->getGradingStatistics($course);

        // Get assignments for filter dropdown
        $assignments = $course->assignments()->orderBy('title')->get();

        // Store filters in session
        session()->put('gradebook_filters', $filters);

        return view('teacher.gradebook.index', compact('gradeBook', 'statistics', 'assignments'));
    }

    /**
     * Show the form for grading a specific submission.
     */
    public function grade(Assignment $assignment, Submission $submission)
    {
        Gate::authorize('update', $assignment);
        
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $existingGrade = $submission->grade;

        return view('teacher.gradebook.enhanced-grade', compact('assignment', 'submission', 'existingGrade'));
    }

    /**
     * Save draft grade (for auto-save).
     */
    public function saveDraft(Request $request, Assignment $assignment, Submission $submission)
    {
        Gate::authorize('update', $assignment);
        
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $request->validate([
            'score' => 'nullable|numeric|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string|max:5000',
            'rubric_scores' => 'nullable|array',
        ]);

        // Store draft in session
        session()->put("grade_draft_{$submission->id}", [
            'score' => $request->score,
            'feedback' => $request->feedback,
            'rubric_scores' => $request->rubric_scores ?? [],
            'saved_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully',
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Store or update a grade for a submission.
     */
    public function storeGrade(Request $request, Assignment $assignment, Submission $submission)
    {
        Gate::authorize('update', $assignment);
        
        if ($submission->assignment_id !== $assignment->id) {
            abort(404);
        }

        $request->validate([
            'score' => 'required|numeric|min:0|max:' . $assignment->max_score,
            'feedback' => 'nullable|string|max:2000',
            'rubric_scores' => 'nullable|array',
            'rubric_scores.*' => 'numeric|min:0',
        ]);

        try {
            $grade = $this->gradingService->gradeSubmission(
                $submission,
                $request->score,
                auth()->user(),
                $request->feedback,
                $request->rubric_scores ?? []
            );

            // Auto-publish if course is configured for it
            if ($assignment->course->shouldAutoPublishGrades()) {
                $grade->publish();
            }

            return redirect()
                ->route('teacher.gradebook.index', $assignment->course)
                ->with('success', 'Grade saved successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save grade: ' . $e->getMessage()]);
        }
    }

    /**
     * Publish grades for an assignment.
     */
    public function publishGrades(Request $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        try {
            $publishedCount = $this->gradingService->publishGrades($assignment, auth()->user());

            return redirect()
                ->route('teacher.gradebook.index', $assignment->course)
                ->with('success', "Published {$publishedCount} grades successfully.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to publish grades: ' . $e->getMessage()]);
        }
    }

    /**
     * Export grades for a course.
     */
    public function export(Request $request, Course $course)
    {
        Gate::authorize('view', $course);

        $format = $request->get('format', 'csv');
        
        if (!in_array($format, ['csv', 'xlsx'])) {
            return back()->withErrors(['error' => 'Invalid export format.']);
        }

        try {
            $exportData = $this->gradingService->exportGrades($course, $format);

            if ($format === 'csv') {
                return $this->exportAsCsv($exportData);
            } else {
                return $this->exportAsExcel($exportData);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to export grades: ' . $e->getMessage()]);
        }
    }

    /**
     * Show grading configuration for a course.
     */
    public function configuration(Course $course)
    {
        Gate::authorize('update', $course);

        return view('teacher.gradebook.configuration', compact('course'));
    }

    /**
     * Update grading configuration for a course.
     */
    public function updateConfiguration(Request $request, Course $course)
    {
        Gate::authorize('update', $course);

        $request->validate([
            'grading_scheme' => 'required|array',
            'grading_scheme.*' => 'required|numeric|min:0|max:100',
            'passing_grade' => 'required|numeric|min:0|max:100',
            'auto_publish_grades' => 'boolean',
        ]);

        // Validate that grading scheme weights sum to 100
        $totalWeight = array_sum($request->grading_scheme);
        if ($totalWeight !== 100) {
            return back()
                ->withInput()
                ->withErrors(['grading_scheme' => 'Grading scheme weights must sum to 100%.']);
        }

        try {
            $course->update([
                'grading_scheme' => $request->grading_scheme,
                'passing_grade' => $request->passing_grade,
                'auto_publish_grades' => $request->boolean('auto_publish_grades'),
            ]);

            return redirect()
                ->route('teacher.gradebook.configuration', $course)
                ->with('success', 'Grading configuration updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * Get assignment statistics via AJAX.
     */
    public function assignmentStats(Assignment $assignment)
    {
        Gate::authorize('view', $assignment->course);

        $stats = $assignment->getSubmissionStats();
        
        return response()->json($stats);
    }

    /**
     * Export data as CSV.
     */
    protected function exportAsCsv(array $exportData): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $exportData['filename'];
        $data = $exportData['data'];

        return Response::streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export data as Excel (simplified - would need a proper Excel library).
     */
    protected function exportAsExcel(array $exportData): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // For now, export as CSV with .xlsx extension
        // In a real implementation, you would use a library like PhpSpreadsheet
        $filename = str_replace('.xlsx', '.csv', $exportData['filename']);
        return $this->exportAsCsv(array_merge($exportData, ['filename' => $filename]));
    }

    /**
     * Show the form for overriding a locked grade (Admin only).
     */
    public function showOverride(Grade $grade)
    {
        Gate::authorize('override', $grade);

        $grade->load(['submission.assignment', 'submission.user', 'grader', 'overrides.admin']);

        return view('teacher.gradebook.override', compact('grade'));
    }

    /**
     * Process the admin override of a locked grade.
     */
    public function override(Request $request, Grade $grade)
    {
        Gate::authorize('override', $grade);

        $request->validate([
            'new_score' => 'required|numeric|min:0|max:' . $grade->submission->assignment->max_score,
            'reason' => 'required|string|min:10|max:2000',
        ], [
            'reason.min' => 'Override reason must be at least 10 characters.',
            'reason.required' => 'Override reason is required.',
        ]);

        try {
            // Create the override record and update the grade
            $override = $grade->createOverride(
                auth()->user(),
                $request->new_score,
                $request->reason
            );

            // Send notification to the original grader
            \App\Jobs\SendGradeOverrideNotification::dispatch($override);

            return redirect()
                ->route('teacher.gradebook.index', $grade->submission->assignment->course)
                ->with('success', 'Grade override completed successfully. The original grader has been notified.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to override grade: ' . $e->getMessage()]);
        }
    }

    /**
     * Display override history for a grade.
     */
    public function overrideHistory(Grade $grade)
    {
        Gate::authorize('viewOverrides', $grade);

        $grade->load([
            'submission.assignment',
            'submission.user',
            'grader',
            'overrides' => function ($query) {
                $query->with('admin')->orderBy('overridden_at', 'desc');
            }
        ]);

        return view('teacher.gradebook.override-history', compact('grade'));
    }

    /**
     * Lock a grade to prevent further edits.
     */
    public function lock(Grade $grade)
    {
        Gate::authorize('lock', $grade);

        try {
            $grade->lock();

            return back()->with('success', 'Grade locked successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to lock grade: ' . $e->getMessage()]);
        }
    }

    /**
     * Unlock a grade to allow edits.
     */
    public function unlock(Grade $grade)
    {
        Gate::authorize('lock', $grade);

        try {
            $grade->unlock();

            return back()->with('success', 'Grade unlocked successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to unlock grade: ' . $e->getMessage()]);
        }
    }
}