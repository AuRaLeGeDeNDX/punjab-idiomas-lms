<?php

namespace App\Policies;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExercisePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any exercises.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'teacher', 'student']);
    }

    /**
     * Determine whether the user can view the exercise.
     * SECURITY: Students can view exercises but NOT answers.
     */
    public function view(User $user, Exercise $exercise): bool
    {
        // Check if subpage is accessible
        if (!$exercise->subpage->canBeAccessedBy($user)) {
            return false;
        }

        // Students can only view active exercises
        if ($user->hasRole('student')) {
            return $exercise->is_active;
        }

        // Teachers and admins can view all exercises
        return true;
    }

    /**
     * SECURITY: Determine whether the user can view the exercise answer.
     * CRITICAL: Only teachers and admins can view answers.
     */
    public function viewAnswer(User $user, Exercise $exercise): bool
    {
        // First check if they can view the exercise
        if (!$this->view($user, $exercise)) {
            return false;
        }

        // ONLY teachers and admins can view answers
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can create exercises.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can update the exercise.
     */
    public function update(User $user, Exercise $exercise): bool
    {
        // Admins can update any exercise
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can update exercises in their courses
        if ($user->hasRole('teacher')) {
            $course = $exercise->subpage->module->course;
            return $course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the exercise.
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        return $this->update($user, $exercise);
    }

    /**
     * Determine whether the user can restore the exercise.
     */
    public function restore(User $user, Exercise $exercise): bool
    {
        return $this->update($user, $exercise);
    }

    /**
     * Determine whether the user can permanently delete the exercise.
     */
    public function forceDelete(User $user, Exercise $exercise): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can submit to this exercise.
     */
    public function submit(User $user, Exercise $exercise): bool
    {
        // Only students can submit
        if (!$user->hasRole('student')) {
            return false;
        }

        // Check if exercise allows submissions
        return $exercise->canSubmit($user);
    }

    /**
     * Determine whether the user can grade submissions.
     */
    public function grade(User $user, Exercise $exercise): bool
    {
        // Admins can grade any exercise
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can grade exercises in their courses
        if ($user->hasRole('teacher')) {
            $course = $exercise->subpage->module->course;
            return $course->teacher_id === $user->id;
        }

        return false;
    }
}