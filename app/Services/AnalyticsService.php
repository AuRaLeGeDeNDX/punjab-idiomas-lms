<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\Grade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Analytics Service
 * 
 * Provides comprehensive analytics and metrics for the assignment system.
 * Implements caching for performance optimization.
 * 
 * Requirements: 3 (Teacher Analytics), 4 (Student Progress), 14 (Performance Optimization)
 */
class AnalyticsService
{
    /**
     * Get comprehensive dashboard data for teachers
     * 
     * @param User $teacher
     * @param Course $course
     * @return array
     * @throws \Exception
     */
    public function getTeacherDashboardData(User $teacher, Course $course): array
    {
        try {
            return Cache::remember("teacher_analytics_{$teacher->id}_{$course->id}", 300, function() use ($teacher, $course) {
                // Eager load relationships to prevent N+1 queries
                $course->load([
                    'assignments' => function($query) {
                        $query->where('is_published', true)
                              ->with(['submissions.user', 'submissions.grade']);
                    },
                    'enrollments' => function($query) {
                        $query->where('status', 'active')
                              ->with('user:id,name,email');
                    }
                ]);

                return [
                    'overall_completion_rate' => $this->calculateCompletionRate($course),
                    'average_score' => $this->calculateAverageScore($course),
                    'submission_timeline' => $this->getSubmissionTimeline($course, 30),
                    'top_performers' => $this->getTopPerformers($course, 5),
                    'at_risk_students' => $this->getAtRiskStudents($course, 5),
                    'assignment_stats' => $this->getAssignmentStats($course)
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Failed to generate teacher dashboard data', [
                'teacher_id' => $teacher->id,
                'course_id' => $course->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Failed to generate analytics dashboard. Please try again later.');
        }
    }

    /**
     * Get comprehensive dashboard data for students
     * 
     * @param User $student
     * @return array
     * @throws \Exception
     */
    public function getStudentDashboardData(User $student): array
    {
        try {
            return Cache::remember("student_analytics_{$student->id}", 120, function() use ($student) {
                return [
                    'completion_progress' => $this->calculateStudentProgress($student),
                    'average_score' => $this->calculateStudentAverage($student),
                    'upcoming_assignments' => $this->getUpcomingAssignments($student, 7),
                    'overdue_assignments' => $this->getOverdueAssignments($student),
                    'missed_assignments' => $this->getMissedAssignments($student),
                    'recent_grades' => $this->getRecentGrades($student, 5),
                    'performance_trend' => $this->getPerformanceTrend($student)
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Failed to generate student dashboard data', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Failed to generate student dashboard. Please try again later.');
        }
    }

    /**
     * Get analytics for a specific assignment
     * 
     * Provides comprehensive metrics including completion rate, average score,
     * late submissions, not-submitted count, grade distribution, and student list.
     * Uses eager loading to prevent N+1 queries.
     * 
     * @param Assignment $assignment
     * @param int $perPage
     * @param int $page
     * @return array
     * @throws \Exception
     */
    public function getAssignmentAnalytics(Assignment $assignment, int $perPage = 15, int $page = 1): array
    {
        try {
            // Eager load course relationship to prevent N+1 queries
            $assignment->load('course.enrollments');

            return [
                'completion_rate' => $this->calculateAssignmentCompletion($assignment),
                'average_score' => $this->calculateAssignmentAverageScore($assignment),
                'late_count' => $assignment->submissions()->where('is_late', true)->count(),
                'not_submitted_count' => $this->getNotSubmittedCount($assignment),
                'grade_distribution' => $this->getGradeDistribution($assignment),
                'student_list' => $this->getStudentSubmissionList($assignment, $perPage, $page)
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to generate assignment analytics', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Failed to generate assignment analytics. Please try again later.');
        }
    }

    /**
     * Calculate overall completion rate for a course
     * 
     * @param Course $course
     * @return float Percentage (0-100)
     */
    private function calculateCompletionRate(Course $course): float
    {
        try {
            // Use eager loaded data if available
            $assignments = $course->relationLoaded('assignments') 
                ? $course->assignments 
                : $course->assignments()->where('is_published', true)->get();
            
            $enrolledStudents = $course->relationLoaded('enrollments')
                ? $course->enrollments->where('status', 'active')->count()
                : $course->enrollments()->where('status', 'active')->count();

            if ($assignments->isEmpty() || $enrolledStudents === 0) {
                return 0.0;
            }

            $totalPossibleSubmissions = $assignments->count() * $enrolledStudents;
            
            // Count submissions efficiently
            $actualSubmissions = Submission::whereIn('assignment_id', $assignments->pluck('id'))->count();

            return round(($actualSubmissions / $totalPossibleSubmissions) * 100, 2);
        } catch (\Exception $e) {
            \Log::error('Failed to calculate completion rate', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Calculate average score across all assignments in a course
     * 
     * @param Course $course
     * @return float
     */
    private function calculateAverageScore(Course $course): float
    {
        try {
            // Use eager loaded data if available
            $assignments = $course->relationLoaded('assignments')
                ? $course->assignments
                : $course->assignments()->where('is_published', true)->get();

            if ($assignments->isEmpty()) {
                return 0.0;
            }

            $assignmentIds = $assignments->pluck('id');

            $averageScore = Grade::whereHas('submission', function($query) use ($assignmentIds) {
                    $query->whereIn('assignment_id', $assignmentIds);
                })
                ->where('is_published', true)
                ->avg('score');

            return round($averageScore ?? 0.0, 2);
        } catch (\Exception $e) {
            \Log::error('Failed to calculate average score', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Get submission timeline for the last N days
     * 
     * @param Course $course
     * @param int $days
     * @return array
     */
    private function getSubmissionTimeline(Course $course, int $days = 30): array
    {
        try {
            // Use eager loaded data if available
            $assignments = $course->relationLoaded('assignments')
                ? $course->assignments
                : $course->assignments()->where('is_published', true)->get();

            if ($assignments->isEmpty()) {
                return [];
            }

            $assignmentIds = $assignments->pluck('id');
            $startDate = Carbon::now()->subDays($days);

            $submissions = Submission::whereIn('assignment_id', $assignmentIds)
                ->where('submitted_at', '>=', $startDate)
                ->whereNotNull('submitted_at')
                ->select(DB::raw('DATE(submitted_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return $submissions->map(function($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to get submission timeline', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get top performing students in a course
     * 
     * @param Course $course
     * @param int $limit
     * @return array
     */
    private function getTopPerformers(Course $course, int $limit = 5): array
    {
        try {
            // Use eager loaded data if available
            $assignments = $course->relationLoaded('assignments')
                ? $course->assignments
                : $course->assignments()->where('is_published', true)->get();

            if ($assignments->isEmpty()) {
                return [];
            }

            $assignmentIds = $assignments->pluck('id');

            // Eager load all necessary relationships in one query
            $grades = Grade::whereHas('submission', function($query) use ($assignmentIds) {
                    $query->whereIn('assignment_id', $assignmentIds);
                })
                ->where('is_published', true)
                ->with(['submission.user:id,name,email'])
                ->get();

            if ($grades->isEmpty()) {
                return [];
            }

            $topPerformers = $grades
                ->groupBy('submission.user_id')
                ->map(function($userGrades, $userId) {
                    $averageScore = $userGrades->avg('score');
                    $user = $userGrades->first()->submission->user;
                    return [
                        'student_id' => $userId,
                        'student_name' => $user->name ?? 'Unknown',
                        'student_email' => $user->email ?? '',
                        'average_score' => round($averageScore, 2),
                        'graded_count' => $userGrades->count()
                    ];
                })
                ->sortByDesc('average_score')
                ->take($limit)
                ->values()
                ->toArray();

            return $topPerformers;
        } catch (\Exception $e) {
            \Log::error('Failed to get top performers', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get at-risk students (low scores or missing submissions)
     * 
     * @param Course $course
     * @param int $limit
     * @return array
     */
    private function getAtRiskStudents(Course $course, int $limit = 5): array
    {
        try {
            // Use eager loaded data if available
            $assignments = $course->relationLoaded('assignments')
                ? $course->assignments
                : $course->assignments()->where('is_published', true)->get();

            $enrollments = $course->relationLoaded('enrollments')
                ? $course->enrollments->where('status', 'active')
                : $course->enrollments()->where('status', 'active')->with('user:id,name,email')->get();

            if ($assignments->isEmpty() || $enrollments->isEmpty()) {
                return [];
            }

            $assignmentIds = $assignments->pluck('id');
            $enrolledStudentIds = $enrollments->pluck('user_id');

            // Get students with low average scores - eager load relationships
            $grades = Grade::whereHas('submission', function($query) use ($assignmentIds, $enrolledStudentIds) {
                    $query->whereIn('assignment_id', $assignmentIds)
                          ->whereIn('user_id', $enrolledStudentIds);
                })
                ->where('is_published', true)
                ->with(['submission.user:id,name,email'])
                ->get();

            if ($grades->isEmpty()) {
                return [];
            }

            $lowScorers = $grades
                ->groupBy('submission.user_id')
                ->map(function($userGrades, $userId) {
                    $averageScore = $userGrades->avg('score');
                    $user = $userGrades->first()->submission->user;
                    return [
                        'student_id' => $userId,
                        'student_name' => $user->name ?? 'Unknown',
                        'student_email' => $user->email ?? '',
                        'average_score' => round($averageScore, 2),
                        'graded_count' => $userGrades->count(),
                        'risk_reason' => 'Low average score'
                    ];
                })
                ->filter(function($student) {
                    return $student['average_score'] < 60;
                })
                ->sortBy('average_score')
                ->take($limit)
                ->values()
                ->toArray();

            return $lowScorers;
        } catch (\Exception $e) {
            \Log::error('Failed to get at-risk students', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get assignment statistics for a course
     * 
     * @param Course $course
     * @return array
     */
    private function getAssignmentStats(Course $course): array
    {
        try {
            // Use eager loaded data if available
            $assignments = $course->relationLoaded('assignments')
                ? $course->assignments
                : $course->assignments()->where('is_published', true)
                    ->with(['submissions.grade' => function($query) {
                        $query->where('is_published', true);
                    }])
                    ->get();

            if ($assignments->isEmpty()) {
                return [];
            }

            return $assignments->map(function($assignment) {
                // Use eager loaded submissions if available
                $submissions = $assignment->relationLoaded('submissions')
                    ? $assignment->submissions
                    : $assignment->submissions;

                // Calculate average score from eager loaded grades
                $publishedGrades = $submissions->pluck('grade')->filter();
                $avgScore = $publishedGrades->isNotEmpty() 
                    ? $publishedGrades->avg('score') 
                    : 0;

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'due_date' => $assignment->due_date?->format('Y-m-d H:i:s'),
                    'submission_count' => $submissions->count(),
                    'graded_count' => $publishedGrades->count(),
                    'average_score' => round($avgScore, 2)
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to get assignment stats', [
                'course_id' => $course->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Calculate student's overall progress
     * 
     * @param User $student
     * @return array
     */
    private function calculateStudentProgress(User $student): array
    {
        try {
            // Eager load enrollments with courses and assignments
            $enrollments = $student->enrollments()
                ->where('status', 'active')
                ->with(['course.assignments' => function($query) {
                    $query->where('is_published', true);
                }])
                ->get();
            
            $totalAssignments = 0;
            $completedAssignments = 0;
            $assignmentIds = [];

            foreach ($enrollments as $enrollment) {
                $courseAssignments = $enrollment->course->assignments;
                $totalAssignments += $courseAssignments->count();
                $assignmentIds = array_merge($assignmentIds, $courseAssignments->pluck('id')->toArray());
            }

            if ($totalAssignments > 0) {
                // Count submissions efficiently in one query
                $completedAssignments = Submission::whereIn('assignment_id', $assignmentIds)
                    ->where('user_id', $student->id)
                    ->whereNotNull('submitted_at')
                    ->count();
            }

            $completionRate = $totalAssignments > 0 
                ? round(($completedAssignments / $totalAssignments) * 100, 2) 
                : 0.0;

            return [
                'total_assignments' => $totalAssignments,
                'completed_assignments' => $completedAssignments,
                'completion_rate' => $completionRate
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to calculate student progress', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_assignments' => 0,
                'completed_assignments' => 0,
                'completion_rate' => 0.0
            ];
        }
    }

    /**
     * Calculate student's average score
     * 
     * @param User $student
     * @return float
     */
    private function calculateStudentAverage(User $student): float
    {
        try {
            $averageScore = Grade::whereHas('submission', function($query) use ($student) {
                    $query->where('user_id', $student->id);
                })
                ->where('is_published', true)
                ->avg('score');

            return round($averageScore ?? 0.0, 2);
        } catch (\Exception $e) {
            \Log::error('Failed to calculate student average', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Get upcoming assignments for a student (due within N days)
     * 
     * @param User $student
     * @param int $days Number of days to look ahead
     * @return array
     */
    private function getUpcomingAssignments(User $student, int $days = 7): array
    {
        try {
            $enrolledCourseIds = $student->enrollments()
                ->where('status', 'active')
                ->pluck('course_id');
            
            $endDate = Carbon::now()->addDays($days);

            // Eager load course relationship to prevent N+1
            $assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->where('due_date', '>', Carbon::now())
                ->where('due_date', '<=', $endDate)
                ->whereDoesntHave('submissions', function($query) use ($student) {
                    $query->where('user_id', $student->id)
                          ->whereNotNull('submitted_at');
                })
                ->orderBy('due_date')
                ->with('course:id,title')
                ->get();

            return $assignments->map(function($assignment) {
                $daysUntilDue = Carbon::now()->diffInDays($assignment->due_date, false);
                
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'course_title' => $assignment->course->title ?? 'Unknown',
                    'due_date' => $assignment->due_date?->format('Y-m-d H:i:s'),
                    'days_until_due' => max(0, ceil($daysUntilDue))
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to get upcoming assignments', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get overdue assignments for a student (past due, not submitted, late submission allowed)
     * 
     * @param User $student
     * @return array
     */
    private function getOverdueAssignments(User $student): array
    {
        try {
            $enrolledCourseIds = $student->enrollments()
                ->where('status', 'active')
                ->pluck('course_id');

            // Eager load course relationship to prevent N+1
            $assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->where('due_date', '<', Carbon::now())
                ->where('allow_late_submission', true) // Only assignments that allow late submission
                ->whereDoesntHave('submissions', function($query) use ($student) {
                    $query->where('user_id', $student->id)
                          ->whereNotNull('submitted_at');
                })
                ->orderBy('due_date', 'desc')
                ->with('course:id,title')
                ->get();

            return $assignments->map(function($assignment) {
                $daysOverdue = abs(Carbon::now()->diffInDays($assignment->due_date, false));
                
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'course_title' => $assignment->course->title ?? 'Unknown',
                    'due_date' => $assignment->due_date?->format('Y-m-d H:i:s'),
                    'days_overdue' => (int) round($daysOverdue),
                    'allows_late_submission' => true
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to get overdue assignments', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get missed assignments (past due, not submitted, late submission NOT allowed)
     * 
     * @param User $student
     * @return array
     */
    private function getMissedAssignments(User $student): array
    {
        try {
            $enrolledCourseIds = $student->enrollments()
                ->where('status', 'active')
                ->pluck('course_id');

            // Eager load course relationship to prevent N+1
            $assignments = Assignment::whereIn('course_id', $enrolledCourseIds)
                ->where('is_published', true)
                ->where('due_date', '<', Carbon::now())
                ->where('allow_late_submission', false) // Only assignments that DON'T allow late submission
                ->whereDoesntHave('submissions', function($query) use ($student) {
                    $query->where('user_id', $student->id)
                          ->whereNotNull('submitted_at');
                })
                ->orderBy('due_date', 'desc')
                ->with('course:id,title')
                ->get();

            return $assignments->map(function($assignment) {
                $daysOverdue = abs(Carbon::now()->diffInDays($assignment->due_date, false));
                
                return [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'course_title' => $assignment->course->title ?? 'Unknown',
                    'due_date' => $assignment->due_date?->format('Y-m-d H:i:s'),
                    'days_overdue' => (int) round($daysOverdue),
                    'allows_late_submission' => false
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to get missed assignments', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get recent grades for a student (last N graded assignments)
     * 
     * @param User $student
     * @param int $limit
     * @return array
     */
    private function getRecentGrades(User $student, int $limit = 5): array
    {
        try {
            // Eager load all necessary relationships to prevent N+1
            $grades = Grade::whereHas('submission', function($query) use ($student) {
                    $query->where('user_id', $student->id);
                })
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->with(['submission.assignment.course:id,title'])
                ->get();

            return $grades->map(function($grade) {
                $assignment = $grade->submission->assignment;
                $maxScore = $assignment->max_score ?? 100;
                
                return [
                    'id' => $grade->id,
                    'assignment_title' => $assignment->title ?? 'Unknown',
                    'course_title' => $assignment->course->title ?? 'Unknown',
                    'score' => $grade->score,
                    'max_score' => $maxScore,
                    'percentage' => round(($grade->score / $maxScore) * 100, 2),
                    'feedback' => $grade->feedback ? substr($grade->feedback, 0, 100) : null, // Preview only
                    'graded_at' => $grade->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to get recent grades', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get performance trend for a student (scores over time)
     * 
     * @param User $student
     * @return array
     */
    private function getPerformanceTrend(User $student): array
    {
        try {
            // Eager load all necessary relationships to prevent N+1
            $grades = Grade::whereHas('submission', function($query) use ($student) {
                    $query->where('user_id', $student->id);
                })
                ->where('is_published', true)
                ->orderBy('created_at')
                ->with('submission.assignment:id,title,max_score')
                ->get();

            return $grades->map(function($grade) {
                $assignment = $grade->submission->assignment;
                $maxScore = $assignment->max_score ?? 100;
                
                return [
                    'date' => $grade->created_at->format('Y-m-d'),
                    'assignment_title' => $assignment->title ?? 'Unknown',
                    'score' => $grade->score,
                    'max_score' => $maxScore,
                    'percentage' => round(($grade->score / $maxScore) * 100, 2)
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to get performance trend', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Calculate completion rate for a specific assignment
     * 
     * @param Assignment $assignment
     * @return float Percentage (0-100)
     */
    private function calculateAssignmentCompletion(Assignment $assignment): float
    {
        try {
            $enrolledStudents = $assignment->course->enrollments()->where('status', 'active')->count();

            if ($enrolledStudents === 0) {
                return 0.0;
            }

            $submissionCount = $assignment->submissions()->count();

            return round(($submissionCount / $enrolledStudents) * 100, 2);
        } catch (\Exception $e) {
            \Log::error('Failed to calculate assignment completion', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Calculate average score for a specific assignment
     * 
     * @param Assignment $assignment
     * @return float
     */
    private function calculateAssignmentAverageScore(Assignment $assignment): float
    {
        try {
            $averageScore = Grade::whereHas('submission', function($query) use ($assignment) {
                    $query->where('assignment_id', $assignment->id);
                })
                ->where('is_published', true)
                ->avg('score');

            return round($averageScore ?? 0.0, 2);
        } catch (\Exception $e) {
            \Log::error('Failed to calculate assignment average score', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Get count of students who haven't submitted an assignment
     * 
     * @param Assignment $assignment
     * @return int
     */
    private function getNotSubmittedCount(Assignment $assignment): int
    {
        try {
            $enrolledStudents = $assignment->course->enrollments()->where('status', 'active')->count();
            $submissionCount = $assignment->submissions()->count();

            return max(0, $enrolledStudents - $submissionCount);
        } catch (\Exception $e) {
            \Log::error('Failed to get not submitted count', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get grade distribution for an assignment
     * 
     * @param Assignment $assignment
     * @return array
     */
    private function getGradeDistribution(Assignment $assignment): array
    {
        try {
            $maxScore = $assignment->max_score ?? 100;
            
            $grades = Grade::whereHas('submission', function($query) use ($assignment) {
                    $query->where('assignment_id', $assignment->id);
                })
                ->where('is_published', true)
                ->get();

            $distribution = [
                'A' => 0, // 90-100%
                'B' => 0, // 80-89%
                'C' => 0, // 70-79%
                'D' => 0, // 60-69%
                'F' => 0  // 0-59%
            ];

            foreach ($grades as $grade) {
                $percentage = ($grade->score / $maxScore) * 100;

                if ($percentage >= 90) {
                    $distribution['A']++;
                } elseif ($percentage >= 80) {
                    $distribution['B']++;
                } elseif ($percentage >= 70) {
                    $distribution['C']++;
                } elseif ($percentage >= 60) {
                    $distribution['D']++;
                } else {
                    $distribution['F']++;
                }
            }

            return $distribution;
        } catch (\Exception $e) {
            \Log::error('Failed to get grade distribution', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'A' => 0,
                'B' => 0,
                'C' => 0,
                'D' => 0,
                'F' => 0
            ];
        }
    }

    /**
     * Get list of students with their submission status for an assignment
     * 
     * Uses eager loading to prevent N+1 queries
     * Supports pagination for large student lists
     * 
     * @param Assignment $assignment
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function getStudentSubmissionList(Assignment $assignment, int $perPage = 15, int $page = 1): array
    {
        try {
            // Get total count first
            $totalStudents = $assignment->course->enrollments()
                ->where('status', 'active')
                ->count();

            // Calculate offset
            $offset = ($page - 1) * $perPage;

            // Eager load enrollments with users (paginated)
            $enrolledStudents = $assignment->course->enrollments()
                ->where('status', 'active')
                ->with('user:id,name,email')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            if ($enrolledStudents->isEmpty()) {
                return [
                    'data' => [],
                    'pagination' => [
                        'total' => $totalStudents,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => ceil($totalStudents / $perPage),
                        'from' => $offset + 1,
                        'to' => min($offset + $perPage, $totalStudents)
                    ]
                ];
            }

            $studentIds = $enrolledStudents->pluck('user_id');

            // Eager load all submissions and grades in one query to prevent N+1
            $submissions = $assignment->submissions()
                ->whereIn('user_id', $studentIds)
                ->with(['grade' => function($query) {
                    $query->where('is_published', true);
                }])
                ->get()
                ->keyBy('user_id'); // Key by user_id for easy lookup

            $maxScore = $assignment->max_score ?? 100;

            $data = $enrolledStudents->map(function($enrollment) use ($submissions, $maxScore, $assignment) {
                $submission = $submissions->get($enrollment->user_id);
                $grade = $submission?->grade;

                $status = 'not_submitted';
                if ($grade) {
                    $status = 'graded';
                } elseif ($submission) {
                    $status = $submission->is_late ? 'late' : 'submitted';
                }

                return [
                    'student_id' => $enrollment->user_id,
                    'student_name' => $enrollment->user->name ?? 'Unknown',
                    'student_email' => $enrollment->user->email ?? '',
                    'submission_id' => $submission?->id,
                    'status' => $status,
                    'submitted_at' => $submission?->submitted_at?->format('Y-m-d H:i:s'),
                    'is_late' => $submission?->is_late ?? false,
                    'score' => $grade?->score,
                    'max_score' => $maxScore,
                    'percentage' => $grade ? round(($grade->score / $maxScore) * 100, 2) : null
                ];
            })->toArray();

            return [
                'data' => $data,
                'pagination' => [
                    'total' => $totalStudents,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($totalStudents / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $totalStudents)
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get student submission list', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage()
            ]);
            return [
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => 0,
                    'from' => 0,
                    'to' => 0
                ]
            ];
        }
    }

    /**
     * Invalidate cache for teacher analytics
     * 
     * @param int $teacherId
     * @param int $courseId
     * @return void
     */
    public function invalidateTeacherCache(int $teacherId, int $courseId): void
    {
        Cache::forget("teacher_analytics_{$teacherId}_{$courseId}");
    }

    /**
     * Invalidate cache for student analytics
     * 
     * @param int $studentId
     * @return void
     */
    public function invalidateStudentCache(int $studentId): void
    {
        Cache::forget("student_analytics_{$studentId}");
    }

    /**
     * Invalidate all analytics caches for a course
     * 
     * @param Course $course
     * @return void
     */
    public function invalidateCourseCache(Course $course): void
    {
        // Invalidate teacher caches
        $teachers = User::role('teacher')->get();
        foreach ($teachers as $teacher) {
            $this->invalidateTeacherCache($teacher->id, $course->id);
        }

        // Invalidate student caches
        $students = $course->enrollments()->where('status', 'active')->pluck('user_id');
        foreach ($students as $studentId) {
            $this->invalidateStudentCache($studentId);
        }
    }
}
