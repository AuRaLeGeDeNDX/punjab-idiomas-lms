<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Analytics Policy
 * 
 * Defines authorization rules for viewing analytics dashboards and reports.
 * 
 * Requirements: 3 (Teacher Analytics), 4 (Student Progress)
 */
class AnalyticsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view course analytics
     * 
     * @param User $user
     * @param Course $course
     * @return bool
     */
    public function viewAnalytics(User $user, $resource): bool
    {
        // Admins can view all analytics
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Handle Course analytics
        if ($resource instanceof Course) {
            // Teachers can view analytics for courses they teach
            if ($user->hasRole('Teacher')) {
                return $resource->teacher_id === $user->id;
            }
        }

        // Handle Assignment analytics
        if ($resource instanceof Assignment) {
            // Teachers can view analytics for assignments in courses they teach
            if ($user->hasRole('Teacher')) {
                return $resource->course->teacher_id === $user->id;
            }
        }

        return false;
    }
}
