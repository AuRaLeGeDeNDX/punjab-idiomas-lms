<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\BulkOperationsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BulkOperationsController extends Controller
{
    protected BulkOperationsService $bulkService;

    public function __construct(BulkOperationsService $bulkService)
    {
        $this->bulkService = $bulkService;
    }

    /**
     * Download selected submissions as ZIP
     */
    public function downloadSubmissions(Request $request)
    {
        $validated = $request->validate([
            'submission_ids' => 'required|array|min:1',
            'submission_ids.*' => 'exists:submissions,id'
        ]);

        // Authorize: teacher must own the assignment for all submissions
        $submissions = Submission::with('assignment.course')
            ->whereIn('id', $validated['submission_ids'])
            ->get();

        foreach ($submissions as $submission) {
            Gate::authorize('view', $submission);
        }

        try {
            $zipPath = $this->bulkService->downloadSubmissions($validated['submission_ids']);

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create download: ' . $e->getMessage());
        }
    }

    /**
     * Export selected submissions to CSV
     */
    public function exportToCSV(Request $request)
    {
        $validated = $request->validate([
            'submission_ids' => 'required|array|min:1',
            'submission_ids.*' => 'exists:submissions,id'
        ]);

        // Authorize: teacher must own the assignment for all submissions
        $submissions = Submission::with('assignment.course')
            ->whereIn('id', $validated['submission_ids'])
            ->get();

        foreach ($submissions as $submission) {
            Gate::authorize('view', $submission);
        }

        try {
            $csvPath = $this->bulkService->exportToCSV($validated['submission_ids']);

            return response()->download($csvPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create export: ' . $e->getMessage());
        }
    }

    /**
     * Send reminder notifications to selected students
     */
    public function sendReminders(Request $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id'
        ]);

        try {
            $results = $this->bulkService->sendReminders($validated['student_ids'], $assignment);

            $message = "Reminders sent to {$results['successful']} students.";
            
            if ($results['skipped'] > 0) {
                $message .= " {$results['skipped']} students already submitted.";
            }
            
            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} failed.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send reminders: ' . $e->getMessage());
        }
    }

    /**
     * Apply the same grade to multiple submissions
     */
    public function bulkGrade(Request $request)
    {
        $validated = $request->validate([
            'submission_ids' => 'required|array|min:1',
            'submission_ids.*' => 'exists:submissions,id',
            'score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:5000'
        ]);

        // Authorize: teacher must own the assignment for all submissions
        $submissions = Submission::with('assignment.course')
            ->whereIn('id', $validated['submission_ids'])
            ->get();

        foreach ($submissions as $submission) {
            Gate::authorize('grade', $submission);
        }

        try {
            $results = $this->bulkService->bulkGrade(
                $validated['submission_ids'],
                $validated['score'],
                $validated['feedback'] ?? null
            );

            $message = "Graded {$results['successful']} submissions.";
            
            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} failed (check score limits).";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to bulk grade: ' . $e->getMessage());
        }
    }

    /**
     * Publish all unpublished grades for an assignment
     */
    public function publishAllGrades(Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        try {
            $results = $this->bulkService->publishAllGrades($assignment);

            $message = "Published {$results['successful']} grades.";
            
            if ($results['failed'] > 0) {
                $message .= " {$results['failed']} failed.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to publish grades: ' . $e->getMessage());
        }
    }
}
