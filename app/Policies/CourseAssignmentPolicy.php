<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CourseAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can assign students to courses.
     */
    public function canAssignStudents(User $user, Course $course): bool
    {
        // Admins can assign students to any course
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can only assign students to courses they are assigned to teach
        if ($user->hasRole('Teacher')) {
            return $course->teacher_id === $user->id;
        }

        // Students cannot assign other students
        return false;
    }

    /**
     * Determine whether the user can remove students from courses.
     */
    public function canRemoveStudents(User $user, Course $course): bool
    {
        // Same logic as assignment - admins can remove from any course,
        // teachers can only remove from their own courses
        return $this->canAssignStudents($user, $course);
    }

    /**
     * Determine whether the user can view enrolled students for a course.
     */
    public function canViewEnrolledStudents(User $user, Course $course): bool
    {
        // Admins can view enrolled students for any course
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can view enrolled students for their own courses
        if ($user->hasRole('Teacher')) {
            return $course->teacher_id === $user->id;
        }

        // Students cannot view other enrolled students
        return false;
    }

    /**
     * Determine whether the user can view available students for assignment.
     */
    public function canViewAvailableStudents(User $user, Course $course): bool
    {
        // Same logic as assignment permissions
        return $this->canAssignStudents($user, $course);
    }

    /**
     * Determine whether the user can perform bulk assignment operations.
     */
    public function canBulkAssign(User $user, Course $course): bool
    {
        // Same logic as regular assignment
        return $this->canAssignStudents($user, $course);
    }

    /**
     * Determine whether the user can view assignment history.
     */
    public function canViewAssignmentHistory(User $user, Course $course): bool
    {
        // Admins can view assignment history for any course
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can view assignment history for their own courses
        if ($user->hasRole('Teacher')) {
            return $course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can override enrollment restrictions.
     */
    public function canOverrideRestrictions(User $user): bool
    {
        // Only admins can override capacity and other restrictions
        return $user->hasRole('Admin');
    }
}