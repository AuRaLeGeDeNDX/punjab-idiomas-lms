<?php

namespace App\Policies;

use App\Models\ExerciseSubmission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExerciseSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any submissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'teacher', 'student']);
    }

    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, ExerciseSubmission $submission): bool
    {
        return $submission->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create submissions.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('student');
    }

    /**
     * Determine whether the user can update the submission.
     * Students can only update their own ungraded submissions.
     */
    public function update(User $user, ExerciseSubmission $submission): bool
    {
        // Students can only update their own submissions if not graded
        if ($user->hasRole('student')) {
            return $submission->user_id === $user->id && 
                   $submission->status !== 'graded';
        }

        // Teachers and admins cannot update student submissions
        // (they can only grade them)
        return false;
    }

    /**
     * Determine whether the user can delete the submission.
     */
    public function delete(User $user, ExerciseSubmission $submission): bool
    {
        // Students can delete their own ungraded submissions
        if ($user->hasRole('student')) {
            return $submission->user_id === $user->id && 
                   $submission->status !== 'graded';
        }

        // Teachers can delete submissions in their courses
        if ($user->hasRole('teacher')) {
            $course = $submission->exercise->subpage->module->course;
            return $course->teacher_id === $user->id;
        }

        // Admins can delete any submission
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can grade the submission.
     */
    public function grade(User $user, ExerciseSubmission $submission): bool
    {
        // Students cannot grade submissions
        if ($user->hasRole('student')) {
            return false;
        }

        // Admins can grade any submission
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can grade submissions in their courses
        if ($user->hasRole('teacher')) {
            $course = $submission->exercise->subpage->module->course;
            return $course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can download submission files.
     */
    public function download(User $user, ExerciseSubmission $submission): bool
    {
        return $this->view($user, $submission) && $submission->hasFile();
    }
}