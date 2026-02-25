<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Determine whether the user can view any submissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Teacher', 'Student']);
    }

    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, Submission $submission): bool
    {
        // Admin can view all submissions
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teacher can view submissions in their courses
        if ($user->hasRole('Teacher')) {
            return $submission->assignment->course->teacher_id === $user->id;
        }

        // Student can view their own submissions
        if ($user->hasRole('Student')) {
            return $submission->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create submissions.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Student');
    }

    /**
     * Determine whether the user can update the submission.
     */
    public function update(User $user, Submission $submission): bool
    {
        // Only students can update their own submissions
        if (!$user->hasRole('Student') || $submission->user_id !== $user->id) {
            return false;
        }

        // Cannot update graded submissions
        if ($submission->isGraded()) {
            return false;
        }

        // Check if assignment still allows submissions
        return $submission->assignment->canSubmit($user);
    }

    /**
     * Determine whether the user can delete the submission.
     */
    public function delete(User $user, Submission $submission): bool
    {
        // Admin can delete any submission
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Students can delete their own ungraded submissions
        if ($user->hasRole('Student') && $submission->user_id === $user->id) {
            return !$submission->isGraded();
        }

        return false;
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, Submission $submission): bool
    {
        // Admin can grade all submissions
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teacher can grade submissions in their courses
        if ($user->hasRole('Teacher')) {
            return $submission->assignment->course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can download files from the submission.
     */
    public function downloadFiles(User $user, Submission $submission): bool
    {
        return $this->view($user, $submission);
    }
}