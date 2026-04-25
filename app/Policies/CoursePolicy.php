<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view courses
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        // Admins can view all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can view their own courses and published courses
        if ($user->hasRole('Teacher')) {
            return $course->hasTeacher($user) || $course->is_published;
        }

        // Students can only view published courses
        return $course->is_published;
    }

    /**
     * Determine whether the user can create courses.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Teacher', 'Admin']);
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        // Admins can update all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can only update their own courses
        return $user->hasRole('Teacher') && $course->hasTeacher($user);
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        // Admins can delete all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can only delete their own courses if they have no active enrollments
        if ($user->hasRole('Teacher') && $course->hasTeacher($user)) {
            return $course->enrollments()->where('status', 'active')->count() === 0;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the course.
     */
    public function restore(User $user, Course $course): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the course.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can publish/unpublish the course.
     */
    public function publish(User $user, Course $course): bool
    {
        // Admins can publish/unpublish all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can publish/unpublish their own courses
        return $user->hasRole('Teacher') && $course->hasTeacher($user);
    }

    /**
     * Determine whether the user can manage course modules.
     */
    public function manageModules(User $user, Course $course): bool
    {
        // Admins can manage modules for all courses
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can manage modules for their own courses
        return $user->hasRole('Teacher') && $course->hasTeacher($user);
    }

    /**
     * Determine whether the user can enroll students in the course.
     * Only Admins and Teachers can assign students to courses.
     */
    public function enroll(User $user, Course $course): bool
    {
        // Students cannot enroll themselves - only Admins and Teachers can assign students
        if ($user->hasRole('Student')) {
            return false;
        }

        // Admins can assign students to any published course
        if ($user->hasRole('Admin')) {
            return $course->is_published;
        }

        // Teachers can assign students to their own published courses
        if ($user->hasRole('Teacher')) {
            return $course->is_published && $course->hasTeacher($user);
        }

        return false;
    }
}