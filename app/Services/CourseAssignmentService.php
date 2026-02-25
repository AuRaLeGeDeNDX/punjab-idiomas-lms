<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class CourseAssignmentService
{
    /**
     * Cache TTL constants
     */
    const ASSIGNMENT_CACHE_TTL = 1800; // 30 minutes

    /**
     * Assign a student to a course by an admin or teacher.
     */
    public function assignStudentToCourse(User $student, Course $course, User $assignor, ?string $notes = null): Enrollment
    {
        // Validate student role
        if (!$student->hasRole('Student')) {
            throw ValidationException::withMessages([
                'student' => 'Only users with Student role can be assigned to courses.'
            ]);
        }

        // Validate assignor role
        if (!$assignor->hasRole(['Admin', 'Teacher'])) {
            throw ValidationException::withMessages([
                'assignor' => 'Only Admins and Teachers can assign students to courses.'
            ]);
        }

        // Check if teacher is assigned to this course (if assignor is teacher)
        if ($assignor->hasRole('Teacher') && !$assignor->hasRole('Admin')) {
            if ($course->teacher_id !== $assignor->id) {
                throw ValidationException::withMessages([
                    'course' => 'Teachers can only assign students to courses they are assigned to teach.'
                ]);
            }
        }

        // Check if already enrolled (including dropped records)
        $existingEnrollment = $student->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            if (in_array($existingEnrollment->status, ['active', 'completed'])) {
                throw ValidationException::withMessages([
                    'enrollment' => 'Student is already enrolled in this course.'
                ]);
            }

            // Reactivate dropped/suspended enrollment
            // We use update inside transaction below
        }

        // Check capacity (admins can override)
        if (!$assignor->hasRole('Admin') && $course->isFull()) {
            throw ValidationException::withMessages([
                'capacity' => 'Course has reached maximum enrollment capacity.'
            ]);
        }

        DB::beginTransaction();
        try {
            if ($existingEnrollment) {
                $existingEnrollment->update([
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'assigned_by' => $assignor->id,
                    'assigned_at' => now(),
                    'assignment_notes' => $notes,
                    // Preserve progress percentage and last accessed
                ]);
                $enrollment = $existingEnrollment;
            } else {
                // Create enrollment with assignment tracking
                $enrollment = Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'enrolled_at' => now(),
                    'assigned_by' => $assignor->id,
                    'assigned_at' => now(),
                    'assignment_notes' => $notes,
                    'progress_percentage' => 0.00,
                    'last_accessed_at' => now(),
                    'status' => 'active',
                ]);
            }

            // Clear relevant caches
            $this->clearAssignmentCaches($student, $course);

            DB::commit();
            return $enrollment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove a student from a course.
     */
    public function removeStudentFromCourse(User $student, Course $course): void
    {
        $updated = $student->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->update(['status' => 'dropped']);

        if (!$updated) {
            throw ValidationException::withMessages([
                'enrollment' => 'Student is not enrolled in this course.'
            ]);
        }

        // Clear relevant caches
        $this->clearAssignmentCaches($student, $course);
    }

    /**
     * Get enrollment status for a student in a course.
     */
    public function getEnrollmentStatus(User $student, Course $course): ?array
    {
        $enrollment = $student->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return null;
        }

        return [
            'status' => $enrollment->status,
            'enrolled_at' => $enrollment->enrolled_at,
            'progress_percentage' => $enrollment->progress_percentage,
            'was_assigned' => $enrollment->wasAssigned(),
            'assignment_info' => $enrollment->getAssignmentInfo(),
        ];
    }

    /**
     * Bulk assign multiple students to a course.
     */
    public function bulkAssignStudents(array $studentIds, Course $course, User $assignor, ?string $notes = null): array
    {
        $results = [
            'successful' => [],
            'failed' => [],
        ];

        foreach ($studentIds as $studentId) {
            try {
                $student = User::findOrFail($studentId);
                $enrollment = $this->assignStudentToCourse($student, $course, $assignor, $notes);
                
                $results['successful'][] = [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'enrollment_id' => $enrollment->id,
                ];
            } catch (\Exception $e) {
                $student = User::find($studentId);
                $results['failed'][] = [
                    'student_id' => $studentId,
                    'student_name' => $student ? $student->name : 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get students available for assignment to a course.
     */
    public function getAvailableStudents(Course $course): Collection
    {
        $cacheKey = "course:available_students:{$course->id}";
        
        return Cache::remember($cacheKey, self::ASSIGNMENT_CACHE_TTL, function () use ($course) {
            // Get all students who are not enrolled in this course (using whereDoesntHave as requested)
            return User::role('Student')
                ->where('is_active', true)
                ->whereDoesntHave('enrollments', function ($query) use ($course) {
                    $query->where('course_id', $course->id)
                          ->whereIn('status', ['active', 'completed']);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        });
    }

    /**
     * Get enrolled students for a course with assignment information.
     */
    public function getEnrolledStudents(Course $course): \Illuminate\Support\Collection
    {
        return $course->enrollments()
            ->with(['student', 'assignor'])
            ->where('status', 'active')
            ->orderBy('enrolled_at', 'desc')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'student' => [
                        'id' => $enrollment->student->id,
                        'name' => $enrollment->student->name,
                        'email' => $enrollment->student->email,
                    ],
                    'enrolled_at' => $enrollment->enrolled_at,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'was_assigned' => $enrollment->wasAssigned(),
                    'assignor' => $enrollment->assignor ? [
                        'id' => $enrollment->assignor->id,
                        'name' => $enrollment->assignor->name,
                    ] : null,
                    'assigned_at' => $enrollment->assigned_at,
                    'assignment_notes' => $enrollment->assignment_notes,
                ];
            });
    }

    /**
     * Get courses assigned to a student with assignment information.
     */
    public function getStudentCourses(User $student): \Illuminate\Support\Collection
    {
        return $student->enrollments()
            ->with(['course.teacher', 'assignor'])
            ->whereIn('status', ['active', 'completed'])
            ->orderBy('enrolled_at', 'desc')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'course' => [
                        'id' => $enrollment->course->id,
                        'title' => $enrollment->course->title,
                        'description' => $enrollment->course->description,
                        'teacher_name' => $enrollment->course->teacher->name,
                    ],
                    'enrolled_at' => $enrollment->enrolled_at,
                    'status' => $enrollment->status,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'was_assigned' => $enrollment->wasAssigned(),
                    'assignor' => $enrollment->assignor ? [
                        'id' => $enrollment->assignor->id,
                        'name' => $enrollment->assignor->name,
                    ] : null,
                    'assigned_at' => $enrollment->assigned_at,
                    'assignment_notes' => $enrollment->assignment_notes,
                ];
            });
    }

    /**
     * Check if a user can assign students to a course.
     */
    public function canAssignStudents(User $user, Course $course): bool
    {
        // Admins can assign to any course
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Teachers can only assign to their own courses
        if ($user->hasRole('Teacher')) {
            return $course->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Clear assignment-related caches.
     */
    protected function clearAssignmentCaches(User $student, Course $course): void
    {
        Cache::forget("course:available_students:{$course->id}");
        Cache::forget("student:learning_record:{$student->id}");
        Cache::forget("student_dashboard_{$student->id}");
        Cache::forget("course:students:{$course->id}");
        
        // Clear enrollment-specific caches
        $enrollments = $student->enrollments()->where('course_id', $course->id)->get();
        foreach ($enrollments as $enrollment) {
            Cache::forget("enrollment:progress:{$enrollment->id}");
        }
    }
}