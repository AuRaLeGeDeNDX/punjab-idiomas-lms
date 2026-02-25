<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Grade;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentProgressController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display student progress overview.
     */
    public function index(Request $request): View|JsonResponse
    {
        $teacher = auth()->user();
        
        // Get filter parameters
        $courseId = $request->get('course_id');
        $status = $request->get('status', 'all');
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $search = $request->get('search');
        
        // Get teacher's courses for filter dropdown
        $courses = Course::where('teacher_id', $teacher->id)
            ->where('is_published', true)
            ->withCount('enrollments')
            ->orderBy('title')
            ->get();
        
        // Build query for student enrollments
        $enrollmentsQuery = Enrollment::with([
            'user',
            'course',
            'user.submissions' => function ($query) use ($courseId) {
                $query->with('assignment'); // Eager load assignment for in-memory filtering
                if ($courseId) {
                    $query->whereHas('assignment', function ($q) use ($courseId) {
                        $q->where('course_id', $courseId);
                    });
                }
            },
            'user.grades' => function ($query) use ($courseId) {
                // Eager load submission assignment
                $query->with('submission.assignment'); 
                if ($courseId) {
                    $query->whereHas('submission.assignment', function ($q) use ($courseId) {
                        $q->where('course_id', $courseId);
                    });
                }
            }
        ])
        ->whereHas('course', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        });
        
        // Apply course filter
        if ($courseId) {
            $enrollmentsQuery->where('course_id', $courseId);
        }
        
        // Apply status filter
        if ($status !== 'all') {
            $enrollmentsQuery->where('status', $status);
        }
        
        // Apply search filter
        if ($search) {
            $enrollmentsQuery->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        switch ($sortBy) {
            case 'name':
                $enrollmentsQuery->join('users', 'enrollments.user_id', '=', 'users.id')
                    ->orderBy('users.name', $sortOrder);
                break;
            case 'course':
                $enrollmentsQuery->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->orderBy('courses.title', $sortOrder);
                break;
            case 'progress':
                $enrollmentsQuery->orderBy('progress_percentage', $sortOrder);
                break;
            case 'enrolled_at':
                $enrollmentsQuery->orderBy('enrolled_at', $sortOrder);
                break;
            default:
                $enrollmentsQuery->orderBy($sortBy, $sortOrder);
        }
        
        $enrollments = $enrollmentsQuery->paginate(20);
        
        // Calculate progress metrics for each enrollment
        $enrollments->getCollection()->transform(function ($enrollment) {
            $enrollment->progress_metrics = $this->calculateProgressMetrics($enrollment);
            return $enrollment;
        });
        
        // Get summary statistics
        $stats = $this->getProgressStats($teacher, $courseId);
        
        if ($request->expectsJson()) {
            return response()->json([
                'enrollments' => $enrollments,
                'stats' => $stats,
                'courses' => $courses
            ]);
        }
        
        return view('teacher.progress.index', compact(
            'enrollments',
            'courses',
            'stats',
            'courseId',
            'status',
            'sortBy',
            'sortOrder',
            'search'
        ));
    }

    /**
     * Show detailed progress for a specific student.
     */
    public function show(User $student, Request $request): View
    {
        $teacher = auth()->user();
        $courseId = $request->get('course_id');
        
        // Verify teacher has access to this student
        $enrollment = Enrollment::where('user_id', $student->id)
            ->whereHas('course', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->when($courseId, function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with('course')
            ->firstOrFail();
        
        // Get student's assignments and submissions for this course
        $assignments = Assignment::where('course_id', $enrollment->course_id)
            ->with([
                'submissions' => function ($query) use ($student) {
                    $query->where('user_id', $student->id);
                },
                'submissions.grade',
                'subpage',
                'module'
            ])
            ->orderBy('due_date')
            ->get();
        
        // Get student's overall course progress
        $courseProgress = $this->calculateDetailedProgress($student, $enrollment->course);
        
        // Get student's grade history
        $gradeHistory = Grade::whereHas('submission', function ($query) use ($student, $enrollment) {
            $query->where('user_id', $student->id)
                ->whereHas('assignment', function ($q) use ($enrollment) {
                    $q->where('course_id', $enrollment->course_id);
                });
        })
        ->with('submission.assignment')
        ->orderBy('created_at')
        ->get();
        
        // Get attendance/participation data if available
        $participationData = $this->getParticipationData($student, $enrollment->course);
        
        return view('teacher.progress.show', compact(
            'student',
            'enrollment',
            'assignments',
            'courseProgress',
            'gradeHistory',
            'participationData'
        ));
    }

    /**
     * Export progress data.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $teacher = auth()->user();
        $courseId = $request->get('course_id');
        $format = $request->get('format', 'csv');
        
        $enrollments = Enrollment::with(['user', 'course'])
            ->whereHas('course', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->when($courseId, function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->get();
        
        $data = $enrollments->map(function ($enrollment) {
            $metrics = $this->calculateProgressMetrics($enrollment);
            return [
                'Student Name' => $enrollment->user->name,
                'Email' => $enrollment->user->email,
                'Course' => $enrollment->course->title,
                'Status' => ucfirst($enrollment->status),
                'Progress' => $enrollment->progress_percentage . '%',
                'Assignments Completed' => $metrics['assignments_completed'],
                'Total Assignments' => $metrics['total_assignments'],
                'Average Grade' => $metrics['average_grade'] . '%',
                'Last Activity' => $metrics['last_activity'],
                'Enrolled Date' => $enrollment->enrolled_at->format('Y-m-d')
            ];
        });
        
        $filename = $this->generateProgressReport($data, $format);
        
        return response()->download($filename)->deleteFileAfterSend();
    }

    /**
     * Calculate progress metrics for an enrollment.
     */
    private function calculateProgressMetrics(Enrollment $enrollment): array
    {
        $courseId = $enrollment->course_id;
        $userId = $enrollment->user_id;
        
        // Use eager loaded relations if available, otherwise fall back to queries check
        $submissions = $enrollment->user->submissions ?? collect([]);
        $grades = $enrollment->user->grades ?? collect([]);
        
        // Filter submissions for this SPECIFIC course (in memory)
        $courseSubmissions = $submissions->filter(function($s) use ($courseId) {
             return $s->assignment && $s->assignment->course_id == $courseId;
        });

        // Filter grades for this SPECIFIC course (in memory)
        $courseGrades = $grades->filter(function($g) use ($courseId) {
             return $g->submission && $g->submission->assignment && $g->submission->assignment->course_id == $courseId;
        });
        
        // Count assignment stats
        // Note: Total assignments is constant for the course, can be passed or cached
        // Optimization: simple count is fast.
        $totalAssignments = \Illuminate\Support\Facades\Cache::remember("course_assignments_count:{$courseId}", 600, function() use ($courseId) {
            return Assignment::where('course_id', $courseId)->count();
        });

        $completedAssignments = $courseSubmissions->where('status', '!=', 'draft')->count();
        
        // Get grade stats
        $gradeScores = $courseGrades->pluck('score');
        $averageGrade = $gradeScores->count() > 0 ? round($gradeScores->avg(), 1) : 0;
        
        // Get last activity
        $lastSubmission = $courseSubmissions->sortByDesc('submitted_at')->first();
        $lastActivity = $lastSubmission && $lastSubmission->submitted_at ? $lastSubmission->submitted_at->diffForHumans() : 'No activity';
        
        // Calculate completion rate
        $completionRate = $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 1) : 0;
        
        // Determine status
        $status = 'on_track';
        if ($completionRate < 50) {
            $status = 'behind';
        } elseif ($completionRate > 80 && $averageGrade > 85) {
            $status = 'excellent';
        }
        
        return [
            'total_assignments' => $totalAssignments,
            'assignments_completed' => $completedAssignments,
            'completion_rate' => $completionRate,
            'average_grade' => $averageGrade,
            'last_activity' => $lastActivity,
            'status' => $status,
            'grade_trend' => $this->calculateGradeTrend($gradeScores)
        ];
    }

    /**
     * Calculate detailed progress for a student in a course.
     */
    private function calculateDetailedProgress(User $student, Course $course): array
    {
        $modules = $course->modules()->with([
            'assignments' => function ($query) use ($student) {
                $query->with(['submissions' => function ($q) use ($student) {
                    $q->where('user_id', $student->id);
                }]);
            }
        ])->get();
        
        $moduleProgress = [];
        
        foreach ($modules as $module) {
            $totalAssignments = $module->assignments->count();
            $completedAssignments = $module->assignments->filter(function ($assignment) {
                return $assignment->submissions->where('status', '!=', 'draft')->count() > 0;
            })->count();
            
            $moduleProgress[] = [
                'module' => $module,
                'total_assignments' => $totalAssignments,
                'completed_assignments' => $completedAssignments,
                'progress_percentage' => $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 1) : 0
            ];
        }
        
        return $moduleProgress;
    }

    /**
     * Get participation data for a student.
     */
    private function getParticipationData(User $student, Course $course): array
    {
        // This would integrate with actual participation tracking
        // For now, return placeholder data
        return [
            'forum_posts' => 0,
            'discussion_replies' => 0,
            'peer_reviews' => 0,
            'attendance_rate' => 0,
            'engagement_score' => 0
        ];
    }

    /**
     * Calculate grade trend.
     */
    private function calculateGradeTrend($grades): string
    {
        if ($grades->count() < 2) {
            return 'insufficient_data';
        }
        
        $recent = $grades->slice(-3)->avg();
        $earlier = $grades->slice(0, -3)->avg();
        
        if ($recent > $earlier + 5) {
            return 'improving';
        } elseif ($recent < $earlier - 5) {
            return 'declining';
        }
        
        return 'stable';
    }

    /**
     * Get progress statistics.
     */
    private function getProgressStats(User $teacher, ?int $courseId): array
    {
        $baseQuery = Enrollment::whereHas('course', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        });
        
        if ($courseId) {
            $baseQuery->where('course_id', $courseId);
        }
        
        $totalStudents = (clone $baseQuery)->count();
        $activeStudents = (clone $baseQuery)->where('status', 'active')->count();
        $completedStudents = (clone $baseQuery)->where('status', 'completed')->count();
        $droppedStudents = (clone $baseQuery)->where('status', 'dropped')->count();
        
        // Calculate average progress
        $avgProgress = (clone $baseQuery)->avg('progress_percentage') ?? 0;
        
        // Get students by performance level
        $excellentStudents = (clone $baseQuery)->where('progress_percentage', '>=', 90)->count();
        $goodStudents = (clone $baseQuery)->whereBetween('progress_percentage', [70, 89])->count();
        $strugglingStudents = (clone $baseQuery)->where('progress_percentage', '<', 50)->count();
        
        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'completed_students' => $completedStudents,
            'dropped_students' => $droppedStudents,
            'average_progress' => round($avgProgress, 1),
            'excellent_students' => $excellentStudents,
            'good_students' => $goodStudents,
            'struggling_students' => $strugglingStudents,
            'retention_rate' => $totalStudents > 0 ? round((($totalStudents - $droppedStudents) / $totalStudents) * 100, 1) : 0
        ];
    }

    /**
     * Generate progress report file.
     */
    private function generateProgressReport($data, string $format): string
    {
        $filename = storage_path("app/reports/student_progress_" . date('Y-m-d_H-i-s') . ".{$format}");
        
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }
        
        if ($format === 'csv') {
            $file = fopen($filename, 'w');
            
            // Write headers
            if ($data->count() > 0) {
                fputcsv($file, array_keys($data->first()));
                
                // Write data
                foreach ($data as $row) {
                    fputcsv($file, array_values($row));
                }
            }
            
            fclose($file);
        } else {
            file_put_contents($filename, json_encode($data->toArray(), JSON_PRETTY_PRINT));
        }
        
        return $filename;
    }
}