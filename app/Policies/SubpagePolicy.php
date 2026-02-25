<?php

namespace App\Policies;

use App\Models\Subpage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubpagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any subpages.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Teacher', 'Student']);
    }

    /**
     * Determine whether the user can view the subpage.
     */
    public function view(User $user, Subpage $subpage): bool
    {
        return $subpage->canBeAccessedBy($user);
    }

    /**
     * Determine whether the user can create subpages.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Teacher']);
    }

    /**
     * Determine whether the user can update the subpage.
     */
    public function update(User $user, Subpage $subpage): bool
    {
        // Admins can update any subpage
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can update subpages in their courses
        if ($user->hasRole('Teacher')) {
            return $subpage->module->course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the subpage.
     */
    public function delete(User $user, Subpage $subpage): bool
    {
        return $this->update($user, $subpage);
    }

    /**
     * Determine whether the user can restore the subpage.
     */
    public function restore(User $user, Subpage $subpage): bool
    {
        return $this->update($user, $subpage);
    }

    /**
     * Determine whether the user can permanently delete the subpage.
     */
    public function forceDelete(User $user, Subpage $subpage): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can reorder subpages.
     */
    public function reorder(User $user, Subpage $subpage): bool
    {
        return $this->update($user, $subpage);
    }
}