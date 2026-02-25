<?php

namespace App\Policies;

use App\Models\FileUpload;
use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\Response;

class FileUploadPolicy
{
    /**
     * Determine whether the user can view the file.
     */
    public function view(User $user, FileUpload $fileUpload): bool
    {
        // Admin can view all files
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Owner can always view their files
        if ($fileUpload->user_id === $user->id) {
            return true;
        }

        // If file is public, anyone can view
        if ($fileUpload->is_public) {
            return true;
        }

        // If file belongs to a course, check course access
        if ($fileUpload->course_id) {
            $course = $fileUpload->course;
            
            // Teacher of the course can view files
            if ($course->teacher_id === $user->id) {
                return true;
            }
            
            // Enrolled students can view course files
            if ($user->hasRole('Student') && $user->enrollments()->where('course_id', $course->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can download the file.
     */
    public function download(User $user, FileUpload $fileUpload): bool
    {
        return $this->view($user, $fileUpload);
    }

    /**
     * Determine whether the user can update the file.
     */
    public function update(User $user, FileUpload $fileUpload): bool
    {
        // Admin can update all files
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Owner can update their files
        if ($fileUpload->user_id === $user->id) {
            return true;
        }

        // Teacher can update files in their courses
        if ($fileUpload->course_id) {
            $course = $fileUpload->course;
            if ($course->teacher_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the file.
     */
    public function delete(User $user, FileUpload $fileUpload): bool
    {
        // Admin can delete all files
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Owner can delete their files
        if ($fileUpload->user_id === $user->id) {
            return true;
        }

        // Teacher can delete files in their courses
        if ($fileUpload->course_id) {
            $course = $fileUpload->course;
            if ($course->teacher_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can upload files to a course.
     */
    public function uploadToCourse(User $user, ?Course $course = null): bool
    {
        // Admin can upload anywhere
        if ($user->hasRole('Admin')) {
            return true;
        }

        // If no course specified, user can upload to their personal space
        if (!$course) {
            return true;
        }

        // Teacher can upload to their courses
        if ($user->hasRole('Teacher') && $course->teacher_id === $user->id) {
            return true;
        }

        // Students can upload to courses they're enrolled in
        if ($user->hasRole('Student') && $user->enrollments()->where('course_id', $course->id)->exists()) {
            return true;
        }

        return false;
    }
}