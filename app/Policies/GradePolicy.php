<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GradePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Teacher', 'Admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Grade $grade): bool
    {
        // Admins can view all grades
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can view grades they created
        if ($user->hasRole('Teacher') && $grade->grader_id === $user->id) {
            return true;
        }

        // Students can view their own grades if published
        if ($user->hasRole('Student') && $grade->submission->user_id === $user->id && $grade->is_published) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Teacher', 'Admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Grade $grade): Response
    {
        // Admins can always update
        if ($user->hasRole('Admin')) {
            return Response::allow();
        }

        // If grade is locked, only admins can edit
        if ($grade->is_locked) {
            return Response::deny('This grade is locked and cannot be edited. Contact an administrator for changes.');
        }

        // Teachers can update their own grades
        if ($user->hasRole('Teacher') && $grade->grader_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to update this grade.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Grade $grade): bool
    {
        // Only admins can delete grades
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can lock/unlock the grade.
     */
    public function lock(User $user, Grade $grade): bool
    {
        // Only teachers and admins can lock grades
        return $user->hasRole(['Teacher', 'Admin']) && $grade->grader_id === $user->id;
    }

    /**
     * Determine whether the user can override a locked grade.
     */
    public function override(User $user, Grade $grade): Response
    {
        // Only admins can override locked grades
        if (!$user->hasRole('Admin')) {
            return Response::deny('Only administrators can override locked grades.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can view override history.
     */
    public function viewOverrides(User $user, Grade $grade): bool
    {
        // Admins and the original grader can view override history
        return $user->hasRole('Admin') || $grade->grader_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Grade $grade): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Grade $grade): bool
    {
        return $user->hasRole('Admin');
    }
}
