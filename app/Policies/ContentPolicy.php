<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any content.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Teacher', 'Student']);
    }

    /**
     * Determine whether the user can view the content.
     */
    public function view(User $user, Content $content): bool
    {
        return $content->canBeAccessedBy($user);
    }

    /**
     * Determine whether the user can create content.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Teacher']);
    }

    /**
     * Determine whether the user can update the content.
     */
    public function update(User $user, Content $content): bool
    {
        // Admins can update any content
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can update content in their courses
        if ($user->hasRole('Teacher')) {
            return $content->subpage->module->course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the content.
     */
    public function delete(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }

    /**
     * Determine whether the user can restore the content.
     */
    public function restore(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }

    /**
     * Determine whether the user can permanently delete the content.
     */
    public function forceDelete(User $user, Content $content): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can download the content file.
     */
    public function download(User $user, Content $content): bool
    {
        return $this->view($user, $content) && $content->isFile();
    }
}