<?php

namespace App\Policies;

use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionFilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can download the submission file.
     *
     * A user can download a file if they are:
     * 1. The student who submitted it
     * 2. A teacher of the course
     * 3. An admin
     *
     * @param User $user
     * @param SubmissionFile $submissionFile
     * @return bool
     */
    public function download(User $user, SubmissionFile $submissionFile): bool
    {
        // Load the submission with necessary relationships
        $submission = $submissionFile->submission()->with([
            'assignment.subpage.module.course'
        ])->first();

        if (!$submission) {
            return false;
        }

        // Admin can access all files (check both 'Admin' and 'admin' for case sensitivity)
        if ($user->hasRole('Admin') || $user->hasRole('admin')) {
            return true;
        }

        // Student who submitted can access their own files
        if (($user->hasRole('Student') || $user->hasRole('student')) && $submission->user_id === $user->id) {
            return true;
        }

        // Teacher of the course can access files
        if ($user->hasRole('Teacher') || $user->hasRole('teacher')) {
            $course = $submission->assignment->subpage->module->course;
            
            // Check if the user is the teacher assigned to this course
            return $course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can view the submission file.
     * Same logic as download for now.
     *
     * @param User $user
     * @param SubmissionFile $submissionFile
     * @return bool
     */
    public function view(User $user, SubmissionFile $submissionFile): bool
    {
        return $this->download($user, $submissionFile);
    }
}
