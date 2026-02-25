<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Subpage;
use App\Models\User;

class AssignmentPolicy
{
    /**
     * Determine whether the user can view any assignments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Teacher', 'Student']);
    }

    /**
     * Determine whether the user can view the assignment.
     */
    public function view(User $user, Assignment $assignment): bool
    {
        // Admin can view all assignments
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teacher can view assignments in their courses
        if ($user->hasRole('Teacher')) {
            return $assignment->course->teacher_id === $user->id;
        }

        // Student can view published assignments in enrolled courses
        if ($user->hasRole('Student')) {
            return $assignment->is_published && 
                   $assignment->is_active &&
                   $user->enrollments()->where('course_id', $assignment->course_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create assignments.
     */
    public function create(User $user, ?Subpage $subpage = null): bool
    {
        // Admin can create assignments anywhere
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teacher can create assignments in their courses
        if ($user->hasRole('Teacher') && $subpage) {
            return $subpage->module->course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the assignment.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        // Admin can update all assignments
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teacher can update assignments in their courses
        if ($user->hasRole('Teacher')) {
            return $assignment->course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the assignment.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        // Admin can delete all assignments
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teacher can delete assignments in their courses (but not if they have submissions)
        if ($user->hasRole('Teacher')) {
            return $assignment->course->teacher_id === $user->id && 
                   !$assignment->hasSubmissions();
        }

        return false;
    }

    /**
     * Determine whether the user can submit to the assignment.
     */
    public function submit(User $user, Assignment $assignment): bool
    {
        // Only students can submit
        if (!$user->hasRole('Student')) {
            return false;
        }

        // Must be enrolled in the course
        if (!$user->enrollments()->where('course_id', $assignment->course_id)->exists()) {
            return false;
        }

        // Assignment must be published and active
        if (!$assignment->is_published || !$assignment->is_active) {
            return false;
        }

        // Check if user can submit (handles due dates, existing submissions, etc.)
        return $assignment->canSubmit($user);
    }

    /**
     * Determine whether the user can grade the assignment.
     */
    public function grade(User $user, Assignment $assignment): bool
    {
        // Admin can grade all assignments
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teacher can grade assignments in their courses
        if ($user->hasRole('Teacher')) {
            return $assignment->course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can publish/unpublish the assignment.
     */
    public function publish(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    /**
     * Determine whether the user can reorder assignments.
     */
    public function reorder(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    /**
     * Determine whether the user can send reminders for the assignment.
     */
    public function sendReminders(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}