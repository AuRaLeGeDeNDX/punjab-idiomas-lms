<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Grade;
use App\Services\GradingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GradingQueueController extends Controller
{
    use AuthorizesRequests;
    
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Display the grading queue.
     */
    public function index(Request $request): View|JsonResponse
    {
        $teacher = auth()->user();
        
        // Get filter parameters
        $courseId = $request->get('course_id');
        $priority = $request->get('priority', 'all');
        $status = $request->get('status', 'pending');
        $sortBy = $request->get('sort_by', 'submitted_at');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Get teacher's courses for filter dropdown
        $courses = Course::where('teacher_id', $teacher->id)
            ->where('is_published', true)
            ->orderBy('title')
            ->get(['id', 'title']);
        
        // Build query for submissions
        $submissionsQuery = Submission::with([
            'assignment.course',
            'assignment.subpage',
            'assignment.module',
            'user',
            'grade'
        ])
        ->whereHas('assignment.course', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        });
        
        // Apply filters
        if ($courseId) {
            $submissionsQuery->whereHas('assignment.course', function ($query) use ($courseId) {
                $query->where('id', $courseId);
            });
        }
        
        if ($status === 'pending') {
            $submissionsQuery->where('status', 'submitted')
                ->whereDoesntHave('grade');
        } elseif ($status === 'graded') {
            $submissionsQuery->whereHas('grade');
        } elseif ($status === 'late') {
            $submissionsQuery->where('is_late', true)
                ->where('status', 'submitted');
        }
        
        // Apply priority filter
        if ($priority === 'overdue') {
            $submissionsQuery->whereHas('assignment', function ($query) {
                $query->where('due_date', '<', now());
            })->where('is_late', true);
        } elseif ($priority === 'due_soon') {
            $submissionsQuery->whereHas('assignment', function ($query) {
                $query->whereBetween('due_date', [now(), now()->addDays(2)]);
            });
        }
        
        // Apply sorting
        switch ($sortBy) {
            case 'due_date':
                $submissionsQuery->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
                    ->orderBy('assignments.due_date', $sortOrder);
                break;
            case 'course':
                $submissionsQuery->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
                    ->join('courses', 'assignments.course_id', '=', 'courses.id')
                    ->orderBy('courses.title', $sortOrder);
                break;
            case 'student':
                $submissionsQuery->join('users', 'submissions.user_id', '=', 'users.id')
                    ->orderBy('users.name', $sortOrder);
                break;
            default:
                $submissionsQuery->orderBy($sortBy, $sortOrder);
        }
        
        $submissions = $submissionsQuery->paginate(20);
        
        // Get summary statistics
        $stats = $this->getGradingStats($teacher);
        
        if ($request->expectsJson()) {
            return response()->json([
                'submissions' => $submissions,
                'stats' => $stats,
                'courses' => $courses
            ]);
        }
        
        return view('teacher.grading.index', compact(
            'submissions',
            'courses',
            'stats',
            'courseId',
            'priority',
            'status',
            'sortBy',
            'sortOrder'
        ));
    }

    /**
     * Show individual submission for grading.
     */
    public function show(Submission $submission): View
    {
        $this->authorize('grade', $submission);
        
        $submission->load([
            'assignment.course',
            'assignment.subpage',
            'assignment.module',
            'user',
            'grade',
            'files'
        ]);
        
        // Get rubric if exists
        $rubric = $submission->assignment->rubric;
        
        // Get previous submissions from same student for this assignment
        $previousSubmissions = Submission::where('assignment_id', $submission->assignment_id)
            ->where('user_id', $submission->user_id)
            ->where('id', '!=', $submission->id)
            ->orderBy('submitted_at', 'desc')
            ->limit(3)
            ->get();
        
        // Get student's other grades in this course for context
        $studentGrades = Grade::whereHas('submission', function ($query) use ($submission) {
            $query->where('user_id', $submission->user_id)
                ->whereHas('assignment', function ($q) use ($submission) {
                    $q->where('course_id', $submission->assignment->course_id);
                });
        })->with('submission.assignment')->get();
        
        return view('teacher.grading.show', compact(
            'submission',
            'rubric',
            'previousSubmissions',
            'studentGrades'
        ));
    }

    /**
     * Store grade for submission.
     */
    public function grade(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('grade', $submission);
        
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $submission->assignment->max_score,
            'feedback' => 'nullable|string|max:2000',
            'rubric_scores' => 'nullable|array',
            'rubric_scores.*' => 'numeric|min:0',
            'private_notes' => 'nullable|string|max:1000',
            'return_to_student' => 'boolean'
        ]);
        
        try {
            $grade = $this->gradingService->gradeSubmission(
                $submission,
                $validated['score'],
                $validated['feedback'] ?? null,
                $validated['rubric_scores'] ?? [],
                $validated['private_notes'] ?? null,
                auth()->user()
            );
            
            // Return to student if requested
            if ($validated['return_to_student'] ?? false) {
                $submission->update(['status' => 'graded']);
                
                // Send notification to student
                $submission->user->notify(new \App\Notifications\GradePublishedNotification($grade));
            }
            
            return response()->json([
                'message' => 'Grade saved successfully',
                'grade' => $grade,
                'next_submission_url' => $this->getNextSubmissionUrl($submission)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save grade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk grade multiple submissions.
     */
    public function bulkGrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'submission_ids' => 'required|array',
            'submission_ids.*' => 'exists:submissions,id',
            'action' => 'required|in:grade,return,extend_deadline',
            'score' => 'nullable|numeric|min:0',
            'feedback' => 'nullable|string|max:2000',
            'days_extension' => 'nullable|integer|min:1|max:30'
        ]);
        
        $submissions = Submission::whereIn('id', $validated['submission_ids'])
            ->whereHas('assignment.course', function ($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->with(['assignment', 'user', 'grade'])
            ->get();
        
        $results = [];
        
        foreach ($submissions as $submission) {
            try {
                switch ($validated['action']) {
                    case 'grade':
                        if (isset($validated['score'])) {
                            $grade = $this->gradingService->gradeSubmission(
                                $submission,
                                $validated['score'],
                                $validated['feedback'] ?? null,
                                [],
                                null,
                                auth()->user()
                            );
                            $results[] = ['id' => $submission->id, 'status' => 'graded'];
                        }
                        break;
                        
                    case 'return':
                        $submission->update(['status' => 'graded']);
                        if ($submission->grade) {
                            $submission->user->notify(new \App\Notifications\GradePublishedNotification($submission->grade));
                        }
                        $results[] = ['id' => $submission->id, 'status' => 'returned'];
                        break;
                        
                    case 'extend_deadline':
                        $assignment = $submission->assignment;
                        $newDueDate = $assignment->due_date->addDays($validated['days_extension']);
                        // This would require a per-student deadline extension system
                        $results[] = ['id' => $submission->id, 'status' => 'extended'];
                        break;
                }
            } catch (\Exception $e) {
                $results[] = ['id' => $submission->id, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }
        
        return response()->json([
            'message' => 'Bulk action completed',
            'results' => $results
        ]);
    }

    /**
     * Get grading statistics for teacher.
     */
    private function getGradingStats(User $teacher): array
    {
        $baseQuery = Submission::whereHas('assignment.course', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        });
        
        return [
            'total_pending' => (clone $baseQuery)->where('status', 'submitted')
                ->whereDoesntHave('grade')->count(),
            'overdue_submissions' => (clone $baseQuery)->where('is_late', true)
                ->where('status', 'submitted')->count(),
            'due_this_week' => (clone $baseQuery)->whereHas('assignment', function ($query) {
                $query->whereBetween('due_date', [now(), now()->addWeek()]);
            })->where('status', 'submitted')->count(),
            'graded_today' => (clone $baseQuery)->whereHas('grade', function ($query) {
                $query->whereDate('created_at', today());
            })->count(),
            'avg_grading_time' => $this->getAverageGradingTime($teacher),
            'courses_with_pending' => Course::where('teacher_id', $teacher->id)
                ->whereHas('assignments.submissions', function ($query) {
                    $query->where('status', 'submitted')->whereDoesntHave('grade');
                })->count()
        ];
    }

    /**
     * Get average grading time for teacher.
     */
    private function getAverageGradingTime(User $teacher): string
    {
        $grades = Grade::whereHas('submission.assignment.course', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })
        ->where('created_at', '>=', now()->subMonth())
        ->with('submission')
        ->get();
        
        if ($grades->isEmpty()) {
            return 'N/A';
        }
        
        $totalMinutes = 0;
        $count = 0;
        
        foreach ($grades as $grade) {
            if ($grade->submission && $grade->submission->submitted_at) {
                $submittedAt = $grade->submission->submitted_at;
                $gradedAt = $grade->created_at;
                $diffInMinutes = $submittedAt->diffInMinutes($gradedAt);
                $totalMinutes += $diffInMinutes;
                $count++;
            }
        }
        
        if ($count === 0) {
            return 'N/A';
        }
        
        $avgMinutes = $totalMinutes / $count;
        
        if ($avgMinutes < 60) {
            return round($avgMinutes) . ' minutes';
        }
        
        return round($avgMinutes / 60, 1) . ' hours';
    }

    /**
     * Get URL for next submission to grade.
     */
    private function getNextSubmissionUrl(Submission $currentSubmission): ?string
    {
        $nextSubmission = Submission::where('status', 'submitted')
            ->whereDoesntHave('grade')
            ->whereHas('assignment.course', function ($query) {
                $query->where('teacher_id', auth()->id());
            })
            ->where('id', '!=', $currentSubmission->id)
            ->orderBy('submitted_at')
            ->first();
        
        return $nextSubmission ? route('teacher.grading.show', $nextSubmission) : null;
    }
}