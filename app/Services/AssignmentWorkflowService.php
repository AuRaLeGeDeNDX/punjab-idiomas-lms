<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\User;
use App\Models\Submission;
use App\Models\Grade;
use App\Jobs\SendAssignmentNotification;
use App\Jobs\SendGradeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentWorkflowService
{
    /**
     * Publish an assignment and notify students.
     */
    public function publishAssignment(Assignment $assignment): bool
    {
        try {
            DB::beginTransaction();

            $assignment->publish();
            SendAssignmentNotification::dispatch($assignment, 'published');

            DB::commit();

            Log::info('Assignment published successfully', [
                'assignment_id' => $assignment->id,
                'course_id' => $assignment->course_id
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish assignment', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Grade a submission and optionally publish immediately.
     */
    public function gradeSubmission(Submission $submission, array $gradeData, bool $publishImmediately = false): Grade
    {
        try {
            DB::beginTransaction();

            // Create or update grade
            $grade = $submission->grade ?? new Grade();
            $grade->fill([
                'submission_id' => $submission->id,
                'grader_id' => $gradeData['grader_id'],
                'score' => $gradeData['score'],
                'feedback' => $gradeData['feedback'] ?? null,
                'rubric_scores' => $gradeData['rubric_scores'] ?? [],
                'grade_letter' => $gradeData['grade_letter'] ?? null,
                'graded_at' => now(),
                'is_published' => false,
            ]);

            $grade->save();
            $submission->markAsGraded();

            if ($publishImmediately) {
                $grade->publish(); // This will trigger notification
            }

            DB::commit();

            Log::info('Submission graded successfully', [
                'submission_id' => $submission->id,
                'grade_id' => $grade->id,
                'published' => $publishImmediately
            ]);

            return $grade;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to grade submission', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Bulk grade multiple submissions.
     */
    public function bulkGradeSubmissions(Assignment $assignment, array $submissionsData, bool $publishAll = false): int
    {
        $gradedCount = 0;

        try {
            DB::beginTransaction();

            foreach ($submissionsData as $submissionData) {
                $submission = Submission::where('id', $submissionData['id'])
                    ->where('assignment_id', $assignment->id)
                    ->first();

                if ($submission) {
                    $grade = $this->gradeSubmission($submission, [
                        'grader_id' => $submissionData['grader_id'],
                        'score' => $submissionData['score'],
                        'feedback' => $submissionData['feedback'] ?? null,
                    ], $publishAll);

                    $gradedCount++;
                }
            }

            DB::commit();

            Log::info('Bulk grading completed', [
                'assignment_id' => $assignment->id,
                'graded_count' => $gradedCount,
                'published_all' => $publishAll
            ]);

            return $gradedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk grading failed', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send due date reminders for an assignment.
     */
    public function sendDueReminders(Assignment $assignment, ?User $specificUser = null): int
    {
        try {
            SendAssignmentNotification::dispatch($assignment, 'due_reminder', $specificUser);

            $reminderCount = $specificUser ? 1 : $this->getStudentsWithoutSubmission($assignment)->count();

            Log::info('Due reminders sent', [
                'assignment_id' => $assignment->id,
                'reminder_count' => $reminderCount,
                'specific_user' => $specificUser?->id
            ]);

            return $reminderCount;
        } catch (\Exception $e) {
            Log::error('Failed to send due reminders', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get students who haven't submitted for an assignment.
     */
    public function getStudentsWithoutSubmission(Assignment $assignment)
    {
        return User::whereHas('enrollments', function ($query) use ($assignment) {
            $query->where('course_id', $assignment->course_id);
        })
        ->whereDoesntHave('submissions', function ($query) use ($assignment) {
            $query->where('assignment_id', $assignment->id);
        })
        ->whereHas('roles', function ($query) {
            $query->where('name', 'student');
        });
    }

    /**
     * Get assignment statistics.
     */
    public function getAssignmentStatistics(Assignment $assignment): array
    {
        $totalEnrolled = User::whereHas('enrollments', function ($query) use ($assignment) {
            $query->where('course_id', $assignment->course_id);
        })->whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->count();

        $submissions = $assignment->submissions();
        $totalSubmissions = $submissions->count();
        $gradedSubmissions = $submissions->whereHas('grade')->count();
        $publishedGrades = $submissions->whereHas('grade', function ($query) {
            $query->where('is_published', true);
        })->count();

        $lateSubmissions = $submissions->where('is_late', true)->count();

        $averageScore = Grade::whereHas('submission', function ($query) use ($assignment) {
            $query->where('assignment_id', $assignment->id);
        })->where('is_published', true)->avg('score');

        return [
            'total_enrolled' => $totalEnrolled,
            'total_submissions' => $totalSubmissions,
            'graded_submissions' => $gradedSubmissions,
            'published_grades' => $publishedGrades,
            'late_submissions' => $lateSubmissions,
            'submission_rate' => $totalEnrolled > 0 ? round(($totalSubmissions / $totalEnrolled) * 100, 2) : 0,
            'grading_progress' => $totalSubmissions > 0 ? round(($gradedSubmissions / $totalSubmissions) * 100, 2) : 0,
            'average_score' => $averageScore ? round($averageScore, 2) : null,
            'students_without_submission' => $totalEnrolled - $totalSubmissions,
        ];
    }

    /**
     * Check if assignment deadline reminders should be sent.
     */
    public function shouldSendDeadlineReminders(Assignment $assignment): bool
    {
        if (!$assignment->due_date || !$assignment->is_published) {
            return false;
        }

        $hoursUntilDue = now()->diffInHours($assignment->due_date, false);
        
        // Send reminders 24 hours before due date
        return $hoursUntilDue <= 24 && $hoursUntilDue > 0;
    }

    /**
     * Auto-publish grades for an assignment if all submissions are graded.
     */
    public function autoPublishGradesIfComplete(Assignment $assignment): bool
    {
        $totalSubmissions = $assignment->submissions()->count();
        $gradedSubmissions = $assignment->submissions()->whereHas('grade')->count();

        if ($totalSubmissions > 0 && $totalSubmissions === $gradedSubmissions) {
            $unpublishedGrades = Grade::whereHas('submission', function ($query) use ($assignment) {
                $query->where('assignment_id', $assignment->id);
            })->where('is_published', false)->get();

            foreach ($unpublishedGrades as $grade) {
                $grade->publish();
            }

            Log::info('Auto-published all grades for assignment', [
                'assignment_id' => $assignment->id,
                'grades_published' => $unpublishedGrades->count()
            ]);

            return true;
        }

        return false;
    }
}