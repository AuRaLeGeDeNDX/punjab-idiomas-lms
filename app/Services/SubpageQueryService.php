<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class SubpageQueryService
{
    /**
     * Get course structure for teachers/admins with all subpages and content.
     */
    public function getCourseStructureForTeacher(Course $course): Collection
    {
        return Cache::remember(
            "teacher_course_structure_{$course->id}",
            600, // 10 minutes
            function () use ($course) {
                return $course->modules()
                    ->with([
                        'subpages' => function ($query) {
                            $query->withTrashed() // Include soft-deleted subpages for teachers
                                  ->orderBy('order_index')
                                  ->with(['contents' => function ($contentQuery) {
                                      $contentQuery->orderBy('order_index');
                                  }]);
                        }
                    ])
                    ->orderBy('order_index')
                    ->get();
            }
        );
    }

    /**
     * Get course structure for students with only active, accessible content.
     */
    public function getCourseStructureForStudent(Course $course, User $student): Collection
    {
        // Verify student enrollment
        if (!$student->enrollments()->where('course_id', $course->id)->exists()) {
            return collect();
        }

        return Cache::remember(
            "student_course_structure_{$course->id}_{$student->id}",
            300, // 5 minutes
            function () use ($course) {
                return $course->modules()
                    ->where('is_published', true)
                    ->with([
                        'activeSubpages' => function ($query) {
                            $query->with(['contents' => function ($contentQuery) {
                                $contentQuery->active()
                                            ->visibleToStudents()
                                            ->ordered();
                            }]);
                        }
                    ])
                    ->orderBy('order_index')
                    ->get();
            }
        );
    }

    /**
     * Get subpages for a specific module based on user role.
     */
    public function getSubpagesForModule(Module $module, User $user): Collection
    {
        $cacheKey = $user->hasRole('student') 
            ? "student_subpages_{$module->id}_{$user->id}"
            : "teacher_subpages_{$module->id}";

        return Cache::remember($cacheKey, 300, function () use ($module, $user) {
            $query = $module->subpages();

            if ($user->hasRole('student')) {
                // Students only see active subpages with student-visible content
                $query->active()
                      ->with(['contents' => function ($contentQuery) {
                          $contentQuery->active()
                                      ->visibleToStudents()
                                      ->ordered();
                      }]);
            } else {
                // Teachers/Admins see all subpages including soft-deleted ones
                $query->withTrashed()
                      ->with(['contents' => function ($contentQuery) {
                          $contentQuery->ordered();
                      }]);
            }

            return $query->ordered()->get();
        });
    }

    /**
     * Get accessible content for a subpage based on user role.
     */
    public function getAccessibleContent(Subpage $subpage, User $user): Collection
    {
        // Check if user can access the subpage
        if (!$subpage->canBeAccessedBy($user)) {
            return collect();
        }

        $query = $subpage->contents();

        if ($user->hasRole('student')) {
            // Students only see active, student-visible content
            $query->active()->visibleToStudents();
        }

        return $query->ordered()->get();
    }

    /**
     * Get navigation breadcrumbs for a subpage.
     */
    public function getNavigationBreadcrumbs(Subpage $subpage): array
    {
        $module = $subpage->module;
        $course = $module->course;

        return [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'url' => route('teacher.courses.show', $course)
            ],
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'type' => $module->type,
                'url' => route('teacher.modules.show', [$course, $module])
            ],
            'subpage' => [
                'id' => $subpage->id,
                'title' => $subpage->title,
                'url' => route('teacher.courses.modules.subpages.show', [$course, $module, $subpage])
            ]
        ];
    }

    /**
     * Search subpages and content across courses for a user.
     */
    public function searchContent(string $query, User $user, ?Course $course = null): Collection
    {
        $searchQuery = Subpage::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->with(['module.course', 'contents']);

        // Filter by course if specified
        if ($course) {
            $searchQuery->whereHas('module', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            });
        }

        // Apply role-based filtering
        if ($user->hasRole('student')) {
            // Students only see active subpages in enrolled courses
            $enrolledCourseIds = $user->enrollments()->pluck('course_id');
            
            $searchQuery->active()
                        ->whereHas('module', function ($q) use ($enrolledCourseIds) {
                            $q->whereIn('course_id', $enrolledCourseIds)
                              ->where('is_published', true);
                        });
        } elseif ($user->hasRole('teacher')) {
            // Teachers only see subpages in their courses
            $searchQuery->whereHas('module.course', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }
        // Admins see all subpages (no additional filtering)

        return $searchQuery->orderBy('title')->get();
    }

    /**
     * Get subpage statistics for a course.
     */
    public function getSubpageStatistics(Course $course): array
    {
        $stats = Cache::remember(
            "subpage_stats_{$course->id}",
            600, // 10 minutes
            function () use ($course) {
                $totalSubpages = Subpage::whereHas('module', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->count();

                $activeSubpages = Subpage::whereHas('module', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->active()->count();

                $totalContent = \App\Models\Content::whereHas('subpage.module', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->count();

                $studentVisibleContent = \App\Models\Content::whereHas('subpage.module', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->visibleToStudents()->active()->count();

                return [
                    'total_subpages' => $totalSubpages,
                    'active_subpages' => $activeSubpages,
                    'inactive_subpages' => $totalSubpages - $activeSubpages,
                    'total_content' => $totalContent,
                    'student_visible_content' => $studentVisibleContent,
                    'teacher_only_content' => $totalContent - $studentVisibleContent,
                ];
            }
        );

        return $stats;
    }

    /**
     * Clear cache for a specific course structure.
     */
    public function clearCourseCache(Course $course): void
    {
        Cache::forget("teacher_course_structure_{$course->id}");
        Cache::forget("subpage_stats_{$course->id}");
        
        // Clear student caches for all enrolled students
        $course->enrollments()->chunk(100, function ($enrollments) use ($course) {
            foreach ($enrollments as $enrollment) {
                Cache::forget("student_course_structure_{$course->id}_{$enrollment->user_id}");
            }
        });

        // Clear module-specific caches
        $course->modules()->chunk(100, function ($modules) {
            foreach ($modules as $module) {
                Cache::forget("teacher_subpages_{$module->id}");
                Cache::forget("module_subpages_{$module->id}");
            }
        });
    }
}