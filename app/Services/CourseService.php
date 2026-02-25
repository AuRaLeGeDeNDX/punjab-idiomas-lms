<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseService
{
    /**
     * Cache TTL constants
     */
    const COURSE_CACHE_TTL = 1800; // 30 minutes
    const COURSE_LIST_CACHE_TTL = 300; // 5 minutes

    /**
     * Create a new course.
     */
    public function createCourse(array $data, User $teacher): Course
    {
        // Validate that the user is a teacher
        if (!$teacher->hasRole('Teacher') && !$teacher->hasRole('Admin')) {
            throw ValidationException::withMessages([
                'teacher' => 'Only teachers and admins can create courses.'
            ]);
        }

        DB::beginTransaction();
        try {
            $courseData = array_merge($data, [
                'teacher_id' => $teacher->id,
                'is_published' => false, // New courses start as unpublished
            ]);

            $course = Course::create($courseData);

            // Clear relevant caches
            $this->clearCourseListCaches();

            DB::commit();
            return $course;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing course.
     */
    public function updateCourse(Course $course, array $data): Course
    {
        DB::beginTransaction();
        try {
            $course->update($data);

            // Clear course-specific caches
            $this->invalidateCourseCache($course);

            DB::commit();
            return $course->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Publish a course, making it available for enrollment.
     */
    public function publishCourse(Course $course): void
    {
        // Validate course has required content before publishing
        if (!$this->canPublishCourse($course)) {
            throw ValidationException::withMessages([
                'course' => 'Course must have at least one published module before it can be published.'
            ]);
        }

        DB::beginTransaction();
        try {
            $course->update(['is_published' => true]);

            // Clear relevant caches
            $this->invalidateCourseCache($course);
            $this->clearCourseListCaches();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Unpublish a course.
     */
    public function unpublishCourse(Course $course): void
    {
        DB::beginTransaction();
        try {
            $course->update(['is_published' => false]);

            // Clear relevant caches
            $this->invalidateCourseCache($course);
            $this->clearCourseListCaches();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add a module to a course.
     */
    public function addModule(Course $course, array $moduleData): Module
    {
        DB::beginTransaction();
        try {
            // Set the order index if not provided
            if (!isset($moduleData['order_index'])) {
                $moduleData['order_index'] = $course->modules()->max('order_index') + 1;
            }

            $moduleData['course_id'] = $course->id;
            $module = Module::create($moduleData);

            // Clear course cache
            $this->invalidateCourseCache($course);

            DB::commit();
            return $module;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reorder modules within a course.
     */
    public function reorderModules(Course $course, array $moduleIds): void
    {
        DB::beginTransaction();
        try {
            foreach ($moduleIds as $index => $moduleId) {
                Module::where('id', $moduleId)
                    ->where('course_id', $course->id)
                    ->update(['order_index' => $index + 1]);
            }

            // Clear course cache
            $this->invalidateCourseCache($course);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get course with modules (cached).
     */
    public function getCourseWithModules(int $courseId): Course
    {
        $cacheKey = "course:details:v2:{$courseId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($courseId) {
            return Course::with(['modules' => function ($query) {
                $query->orderBy('order_index');
            }, 'modules.subpages' => function ($query) {
                $query->where('is_active', true)->orderBy('order_index');
            }, 'teacher'])->findOrFail($courseId);
        });
    }

    /**
     * Get courses accessible by a user (cached).
     */
    public function getAccessibleCourses(User $user): Collection
    {
        $cacheKey = "user:accessible_courses:{$user->id}";
        
        return Cache::remember($cacheKey, self::COURSE_LIST_CACHE_TTL, function () use ($user) {
            return Course::accessibleBy($user)->with('teacher')->get();
        });
    }

    /**
     * Get published courses (cached).
     */
    public function getPublishedCourses(): Collection
    {
        $cacheKey = "courses:published";
        
        return Cache::remember($cacheKey, self::COURSE_LIST_CACHE_TTL, function () {
            return Course::published()->with('teacher')->get();
        });
    }

    /**
     * Get courses by teacher (cached).
     */
    public function getCoursesByTeacher(User $teacher): Collection
    {
        $cacheKey = "teacher:courses:{$teacher->id}";
        
        return Cache::remember($cacheKey, self::COURSE_LIST_CACHE_TTL, function () use ($teacher) {
            return Course::where('teacher_id', $teacher->id)->with('modules')->get();
        });
    }

    /**
     * Check if a course can be published.
     */
    protected function canPublishCourse(Course $course): bool
    {
        // Course must have at least one published module
        return $course->modules()->where('is_published', true)->exists();
    }

    /**
     * Check course prerequisites for a student.
     */
    public function checkPrerequisites(Course $course, User $student): bool
    {
        // For now, we'll implement a simple prerequisite system
        // This can be extended later with a more complex prerequisite model
        
        // If no prerequisites are defined, allow enrollment
        if (!isset($course->prerequisites) || empty($course->prerequisites)) {
            return true;
        }

        // Check if student has completed all prerequisite courses
        $completedCourses = $student->enrollments()
            ->where('status', 'completed')
            ->pluck('course_id')
            ->toArray();

        $prerequisites = is_array($course->prerequisites) ? $course->prerequisites : [$course->prerequisites];
        
        foreach ($prerequisites as $prerequisiteId) {
            if (!in_array($prerequisiteId, $completedCourses)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete a course and all related data.
     */
    public function deleteCourse(Course $course): void
    {
        DB::beginTransaction();
        try {
            // Delete related data (modules, enrollments, etc. will be cascade deleted)
            $course->delete();

            // Clear caches
            $this->invalidateCourseCache($course);
            $this->clearCourseListCaches();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Invalidate course-specific caches.
     */
    public function invalidateCourseCache(Course $course)
    {
        Cache::forget("course:details:v2:{$course->id}");
        Cache::forget("course:hierarchy_full_{$course->id}");
        Cache::forget("course_navigation_{$course->id}");
        Cache::forget("course:students:{$course->id}");
        
        // Invalidate enrollment specifically if needed, but progress is persistence-based now
        $this->clearCourseListCaches();
        
        // Clear teacher's course cache
        Cache::forget("teacher:courses:{$course->teacher_id}");
    }

    /**
     * Clear course list caches.
     */
    protected function clearCourseListCaches(): void
    {
        Cache::forget("courses:published");
        // We could implement a more sophisticated cache tagging system here
        // For now, we'll clear specific known cache keys
    }
}
