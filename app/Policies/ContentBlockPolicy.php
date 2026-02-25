<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContentBlockPolicy
{
    /**
     * Determine whether the user can view any content blocks.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view content
    }

    /**
     * Determine whether the user can view the content block.
     */
    public function view(User $user, Content $content): bool
    {
        // Check if user can access the subpage first
        if (!$content->subpage->canBeAccessedBy($user)) {
            return false;
        }

        // Students can only see published, student-visible content
        if ($user->hasRole('Student')) {
            return $content->isPublished() && $content->visibility === 'student';
        }

        // Teachers and admins can see all content in their accessible subpages
        return true;
    }

    /**
     * Determine whether the user can create content blocks.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Admin', 'Teacher']);
    }

    /**
     * Determine whether the user can update the content block.
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
     * Determine whether the user can delete the content block.
     */
    public function delete(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }

    /**
     * Determine whether the user can restore the content block.
     */
    public function restore(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }

    /**
     * Determine whether the user can permanently delete the content block.
     */
    public function forceDelete(User $user, Content $content): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can reorder content blocks.
     */
    public function reorder(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }

    /**
     * Determine whether the user can duplicate content blocks.
     */
    public function duplicate(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }

    /**
     * Determine whether the user can manage content visibility.
     */
    public function manageVisibility(User $user, Content $content): bool
    {
        return $this->update($user, $content);
    }
}