<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\AssignmentDueReminderNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BulkOperationsService
{
    /**
     * Download multiple submissions as a ZIP file
     *
     * @param array $submissionIds
     * @return string Path to the generated ZIP file
     */
    public function downloadSubmissions(array $submissionIds): string
    {
        $submissions = Submission::with(['files', 'student', 'assignment'])
            ->whereIn('id', $submissionIds)
            ->get();

        $zipFileName = 'submissions_' . now()->format('Y-m-d_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Failed to create ZIP file');
        }

        foreach ($submissions as $submission) {
            $studentName = $this->sanitizeFilename($submission->student->name);
            $assignmentTitle = $this->sanitizeFilename($submission->assignment->title);
            $folderName = "{$assignmentTitle}/{$studentName}";

            // Add text content if exists
            if ($submission->content) {
                $zip->addFromString(
                    "{$folderName}/submission.txt",
                    $submission->content
                );
            }

            // Add all files
            foreach ($submission->files as $file) {
                $filePath = Storage::disk('private')->path($file->file_path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, "{$folderName}/{$file->file_name}");
                }
            }
        }

        $zip->close();

        Log::info('Bulk download created', [
            'submission_count' => $submissions->count(),
            'zip_path' => $zipPath
        ]);

        return $zipPath;
    }

    /**
     * Export submissions to CSV
     *
     * @param array $submissionIds
     * @return string Path to the generated CSV file
     */
    public function exportToCSV(array $submissionIds): string
    {
        $submissions = Submission::with(['student', 'assignment', 'grade'])
            ->whereIn('id', $submissionIds)
            ->orderBy('submitted_at')
            ->get();

        $csvFileName = 'submissions_export_' . now()->format('Y-m-d_His') . '.csv';
        $csvPath = storage_path('app/temp/' . $csvFileName);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file = fopen($csvPath, 'w');

        // Write header
        fputcsv($file, [
            'Student Name',
            'Student Email',
            'Assignment',
            'Submission Date',
            'Status',
            'Is Late',
            'Score',
            'Feedback',
            'File Count'
        ]);

        // Write data rows
        foreach ($submissions as $submission) {
            fputcsv($file, [
                $submission->student->name,
                $submission->student->email,
                $submission->assignment->title,
                $submission->submitted_at?->format('Y-m-d H:i:s') ?? 'Not submitted',
                $submission->status,
                $submission->is_late ? 'Yes' : 'No',
                $submission->grade?->score ?? 'Not graded',
                $submission->grade?->feedback ?? '',
                $submission->files()->count()
            ]);
        }

        fclose($file);

        Log::info('CSV export created', [
            'submission_count' => $submissions->count(),
            'csv_path' => $csvPath
        ]);

        return $csvPath;
    }

    /**
     * Send reminder notifications to students who haven't submitted
     *
     * @param array $studentIds
     * @param Assignment $assignment
     * @return array Results with successful and failed counts
     */
    public function sendReminders(array $studentIds, Assignment $assignment): array
    {
        $students = User::whereIn('id', $studentIds)->get();
        
        $successful = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($students as $student) {
            // Check if student has already submitted
            $hasSubmitted = Submission::where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->exists();

            if ($hasSubmitted) {
                $skipped++;
                continue;
            }

            try {
                $student->notify(new AssignmentDueReminderNotification($assignment));
                $successful++;
            } catch (\Exception $e) {
                Log::error('Failed to send reminder', [
                    'student_id' => $student->id,
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        Log::info('Bulk reminders sent', [
            'assignment_id' => $assignment->id,
            'successful' => $successful,
            'failed' => $failed,
            'skipped' => $skipped
        ]);

        return [
            'successful' => $successful,
            'failed' => $failed,
            'skipped' => $skipped,
            'total' => count($studentIds)
        ];
    }

    /**
     * Apply the same grade to multiple submissions
     *
     * @param array $submissionIds
     * @param float $score
     * @param string|null $feedback
     * @return array Results with successful and failed counts
     */
    public function bulkGrade(array $submissionIds, float $score, ?string $feedback = null): array
    {
        $submissions = Submission::with('assignment')->whereIn('id', $submissionIds)->get();
        
        $successful = 0;
        $failed = 0;

        foreach ($submissions as $submission) {
            try {
                // Validate score is within assignment max_score
                if ($score > $submission->assignment->max_score) {
                    $failed++;
                    continue;
                }

                $submission->grade()->updateOrCreate(
                    ['submission_id' => $submission->id],
                    [
                        'score' => $score,
                        'feedback' => $feedback,
                        'graded_at' => now(),
                        'graded_by' => auth()->id()
                    ]
                );

                $successful++;
            } catch (\Exception $e) {
                Log::error('Failed to bulk grade submission', [
                    'submission_id' => $submission->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        Log::info('Bulk grading completed', [
            'successful' => $successful,
            'failed' => $failed,
            'score' => $score
        ]);

        return [
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($submissionIds)
        ];
    }

    /**
     * Publish all unpublished grades for an assignment
     *
     * @param Assignment $assignment
     * @return array Results with successful and failed counts
     */
    public function publishAllGrades(Assignment $assignment): array
    {
        $unpublishedGrades = $assignment->submissions()
            ->with('grade')
            ->whereHas('grade', function ($query) {
                $query->where('is_published', false);
            })
            ->get()
            ->pluck('grade');

        $successful = 0;
        $failed = 0;

        foreach ($unpublishedGrades as $grade) {
            try {
                $grade->update(['is_published' => true]);
                $successful++;
            } catch (\Exception $e) {
                Log::error('Failed to publish grade', [
                    'grade_id' => $grade->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        Log::info('Bulk grade publishing completed', [
            'assignment_id' => $assignment->id,
            'successful' => $successful,
            'failed' => $failed
        ]);

        return [
            'successful' => $successful,
            'failed' => $failed,
            'total' => $unpublishedGrades->count()
        ];
    }

    /**
     * Sanitize filename for safe use in ZIP archives
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters and replace spaces with underscores
        $filename = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        return trim($filename, '_');
    }
}
