<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Module;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class EnrollmentService
{
    /**
     * Cache TTL constants
     */
    const ENROLLMENT_CACHE_TTL = 1800; // 30 minutes
    const PROGRESS_CACHE_TTL = 300; // 5 minutes

    /**
     * Enroll a student in a course.
     * Only Admins and Teachers can assign students to courses.
     */
    public function enrollStudent(Course $course, User $student, User $assignedBy = null): Enrollment
    {
        // Validate student role
        if (!$student->hasRole('Student')) {
            throw ValidationException::withMessages([
                'student' => 'Only students can be enrolled in courses.'
            ]);
        }

        // Validate that the person assigning has permission
        if ($assignedBy) {
            // Check if the assigning user has permission to enroll students
            if (!$assignedBy->hasRole(['Admin', 'Teacher'])) {
                throw ValidationException::withMessages([
                    'authorization' => 'Only Admins and Teachers can assign students to courses.'
                ]);
            }

            // Teachers can only assign to their own courses
            if ($assignedBy->hasRole('Teacher') && $course->teacher_id !== $assignedBy->id) {
                throw ValidationException::withMessages([
                    'authorization' => 'Teachers can only assign students to their own courses.'
                ]);
            }
        } else {
            // If no assignedBy user provided, this is likely a student self-enrollment attempt
            throw ValidationException::withMessages([
                'authorization' => 'Students cannot enroll themselves. Only Admins and Teachers can assign students to courses.'
            ]);
        }

        // Check if course is published
        if (!$course->is_published) {
            throw ValidationException::withMessages([
                'course' => 'Cannot enroll in unpublished courses.'
            ]);
        }

        // Check if already enrolled
        $existingEnrollment = $student->enrollments()
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if ($existingEnrollment) {
            throw ValidationException::withMessages([
                'enrollment' => 'Student is already enrolled in this course.'
            ]);
        }

        // Check prerequisites
        if (!$this->checkPrerequisites($course, $student)) {
            $prerequisiteCourses = $course->prerequisiteCourses();
            $prerequisiteNames = $prerequisiteCourses->pluck('title')->join(', ');
            throw ValidationException::withMessages([
                'prerequisites' => "Prerequisites not met. Required courses: {$prerequisiteNames}"
            ]);
        }

        // Check capacity
        if ($course->isFull()) {
            throw ValidationException::withMessages([
                'capacity' => 'Course has reached maximum enrollment capacity.'
            ]);
        }

        DB::beginTransaction();
        try {
            // Create enrollment with assignment tracking
            $enrollment = Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'enrolled_at' => now(),
                'progress_percentage' => 0.00,
                'last_accessed_at' => now(),
                'status' => 'active',
                'assigned_by' => $assignedBy ? $assignedBy->id : null,
                'assigned_at' => $assignedBy ? now() : null,
            ]);

            // Initialize progress tracking
            $this->initializeProgressTracking($enrollment);

            // Clear relevant caches
            $this->clearEnrollmentCaches($student, $course);

            DB::commit();
            return $enrollment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if a student meets course prerequisites.
     */
    public function checkPrerequisites(Course $course, User $student): bool
    {
        // If no prerequisites are defined, allow enrollment
        if (!$course->hasPrerequisites()) {
            return true;
        }

        // Get completed courses for the student
        $completedCourseIds = $student->enrollments()
            ->where('status', 'completed')
            ->pluck('course_id')
            ->toArray();

        // Check if all prerequisites are met
        foreach ($course->prerequisites as $prerequisiteId) {
            if (!in_array($prerequisiteId, $completedCourseIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Track progress for a student in a course.
     */
    public function trackProgress(Enrollment $enrollment, $studentSubmissions = null, bool $forceRefresh = false): array
    {
        // For standard tracking, if we have fresh enough data in the model, return it
        // This makes progress retrieval O(1) for dashboards
        if (!$forceRefresh && !is_null($enrollment->progress_percentage)) {
             return [
                'overall_progress' => (float)$enrollment->progress_percentage,
                'modules_completed' => $enrollment->status === 'completed' ? ($enrollment->course->modules()->count()) : 'N/A', // Estimating for simplicity if cached
                'total_modules' => $enrollment->course->modules()->count(),
                'status' => $enrollment->status,
                'is_completed' => (bool)$enrollment->is_completed,
                'cached' => true
            ];
        }

        $cacheKey = "enrollment:progress:{$enrollment->id}";
        
        return Cache::remember($cacheKey, self::PROGRESS_CACHE_TTL, function () use ($enrollment, $studentSubmissions) {
            $course = $enrollment->course;
            $student = $enrollment->user;

            // Eager load modules with subpages and assignments to avoid N+1 in calculations
            $modules = $course->modules()
                ->where('is_published', true)
                ->with(['subpages' => function($q) { $q->where('is_active', true); }])
                ->with(['assignments' => function($q) { $q->where('is_published', true); }])
                ->get();
                
            $totalModules = $modules->count();

            if ($totalModules === 0) {
                return [
                    'overall_progress' => 0,
                    'modules_completed' => 0,
                    'total_modules' => 0,
                    'module_progress' => [],
                    'is_completed' => false
                ];
            }

            $completedModules = 0;
            $moduleProgress = [];

            foreach ($modules as $module) {
                $progress = $this->calculateModuleProgress($module, $student, $studentSubmissions);
                $moduleProgress[] = [
                    'module_id' => $module->id,
                    'module_title' => $module->title,
                    'progress_percentage' => $progress,
                    'is_completed' => $progress >= 100,
                ];

                if ($progress >= 100) {
                    $completedModules++;
                }
            }

            $overallProgress = ($completedModules / $totalModules) * 100;
            $isCompleted = $overallProgress >= 100;

            // Update enrollment progress PERSISTENTLY
            $enrollment->update([
                'progress_percentage' => $overallProgress,
                'last_accessed_at' => now(),
                'status' => $isCompleted ? 'completed' : 'active',
                'completed_at' => ($isCompleted && !$enrollment->completed_at) ? now() : $enrollment->completed_at,
                'is_completed' => $isCompleted
            ]);

            return [
                'overall_progress' => round($overallProgress, 2),
                'modules_completed' => $completedModules,
                'total_modules' => $totalModules,
                'module_progress' => $moduleProgress,
                'is_completed' => $isCompleted
            ];
        });
    }

    /**
     * Calculate completion percentage for a module.
     */
    public function calculateModuleProgress(Module $module, User $student, $studentSubmissions = null): float
    {
        // Use pre-loaded subpages if available
        $subpages = $module->relationLoaded('subpages') 
            ? $module->subpages 
            : $module->subpages()->where('is_active', true)->get();
            
        $totalSubpages = $subpages->count();

        if ($totalSubpages === 0) {
            return 100; // Consider empty modules as completed
        }

        // Use pre-loaded assignments if available
        $assignments = $module->relationLoaded('assignments')
            ? $module->assignments
            : $module->assignments()->where('is_published', true)->get();
            
        $totalAssignments = $assignments->count();
        $completedAssignments = 0;

        foreach ($assignments as $assignment) {
            $submission = $studentSubmissions 
                ? ($studentSubmissions[$assignment->id] ?? null)
                : $assignment->submissions()
                    ->where('user_id', $student->id)
                    ->where('status', 'submitted')
                    ->first();

            if ($submission) {
                $completedAssignments++;
            }
        }

        // Calculate progress based on assignments (if any) and subpages
        if ($totalAssignments > 0) {
            $assignmentProgress = ($completedAssignments / $totalAssignments) * 70; // 70% weight for assignments
            $subpageProgress = 30; // Assume 30% for reading materials
            return min(100, $assignmentProgress + $subpageProgress);
        }

        // If no assignments, base progress on subpage access (simplified)
        // In a real implementation, you'd track actual subpage visits
        return rand(0, 100); // Placeholder - would be replaced with actual tracking
    }

    /**
     * Get learning record for a student across all courses.
     */
    public function getLearningRecord(User $student): array
    {
        $cacheKey = "student:learning_record:{$student->id}";
        
        return Cache::remember($cacheKey, self::ENROLLMENT_CACHE_TTL, function () use ($student) {
            $enrollments = $student->enrollments()
                ->with(['course.teacher', 'course.modules'])
                ->orderBy('enrolled_at', 'desc')
                ->get();

            // Pre-load all submissions for this student to avoid N+1 in progress calculations
            $studentSubmissions = \App\Models\Submission::where('user_id', $student->id)
                ->where('status', 'submitted')
                ->get()
                ->keyBy('assignment_id');

            $totalCourses = $enrollments->count();
            $completedCourses = $enrollments->where('status', 'completed')->count();
            $activeCourses = $enrollments->where('status', 'active')->count();
            $totalHours = $enrollments->sum(function ($enrollment) {
                return $enrollment->course->duration_hours ?? 0;
            });

            $courseRecords = [];
            foreach ($enrollments as $enrollment) {
                // Return persistent data from model (O(1)) instead of calculating
                $courseRecords[] = [
                    'course_id' => $enrollment->course->id,
                    'course_title' => $enrollment->course->title,
                    'teacher_name' => $enrollment->course->teacher->name,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'completed_at' => $enrollment->completed_at,
                    'status' => $enrollment->status,
                    'progress_percentage' => (float)$enrollment->progress_percentage,
                    'is_completed' => (bool)$enrollment->is_completed,
                ];
            }

            return [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'total_courses' => $totalCourses,
                'completed_courses' => $completedCourses,
                'active_courses' => $activeCourses,
                'total_learning_hours' => $totalHours,
                'overall_completion_rate' => $totalCourses > 0 ? ($completedCourses / $totalCourses) * 100 : 0,
                'course_records' => $courseRecords,
            ];
        });
    }

    /**
     * Get enrolled courses for a student with progress information.
     */
    public function getEnrolledCoursesWithProgress(User $student): Collection
    {
        // Simple, fast query relying on persistent columns
        return $student->enrollments()
            ->with(['course.teacher'])
            ->where('status', 'active')
            ->get();
    }

    /**
     * Mark a module as accessed by a student.
     */
    public function markModuleAccessed(Enrollment $enrollment, Module $module): void
    {
        // Update last accessed time
        $enrollment->update(['last_accessed_at' => now()]);

        // Clear progress cache to force recalculation
        Cache::forget("enrollment:progress:{$enrollment->id}");
        Cache::forget("student:learning_record:{$enrollment->user_id}");
    }

    /**
     * Unenroll a student from a course.
     * Only Admins and Teachers can remove students from courses.
     */
    public function unenrollStudent(Course $course, User $student, User $removedBy = null): void
    {
        // Validate that the person removing has permission
        if ($removedBy) {
            // Check if the removing user has permission to unenroll students
            if (!$removedBy->hasRole(['Admin', 'Teacher'])) {
                throw ValidationException::withMessages([
                    'authorization' => 'Only Admins and Teachers can remove students from courses.'
                ]);
            }

            // Teachers can only remove from their own courses
            if ($removedBy->hasRole('Teacher') && $course->teacher_id !== $removedBy->id) {
                throw ValidationException::withMessages([
                    'authorization' => 'Teachers can only remove students from their own courses.'
                ]);
            }
        } else {
            // If no removedBy user provided, this is likely a student self-unenrollment attempt
            throw ValidationException::withMessages([
                'authorization' => 'Students cannot unenroll themselves. Only Admins and Teachers can remove students from courses.'
            ]);
        }

        $enrollment = $student->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            throw ValidationException::withMessages([
                'enrollment' => 'Student is not enrolled in this course.'
            ]);
        }

        DB::beginTransaction();
        try {
            $enrollment->update(['status' => 'dropped']);

            // Clear relevant caches
            $this->clearEnrollmentCaches($student, $course);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Initialize progress tracking for a new enrollment.
     */
    protected function initializeProgressTracking(Enrollment $enrollment): void
    {
        // This method can be extended to create initial progress records
        // For now, we'll just ensure the enrollment has proper initial values
        $enrollment->update([
            'progress_percentage' => 0.00,
            'last_accessed_at' => now(),
        ]);
    }

    /**
     * Clear enrollment-related caches.
     */
    protected function clearEnrollmentCaches(User $student, Course $course): void
    {
        Cache::forget("student:learning_record:{$student->id}");
        Cache::forget("student_dashboard_{$student->id}");
        Cache::forget("course:students:{$course->id}");
        
        // Clear any enrollment-specific caches
        $enrollments = $student->enrollments()->where('course_id', $course->id)->get();
        foreach ($enrollments as $enrollment) {
            Cache::forget("enrollment:progress:{$enrollment->id}");
        }
    }
}