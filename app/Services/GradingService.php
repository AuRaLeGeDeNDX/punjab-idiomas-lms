<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Jobs\SendGradeNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GradingService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Grade a submission manually.
     */
    public function gradeSubmission(
        Submission $submission,
        float $score,
        User $grader,
        string $feedback = null,
        array $rubricScores = []
    ): Grade {
        DB::beginTransaction();
        
        try {
            // Validate score against assignment max score
            $maxScore = $submission->assignment->max_score;
            if ($score > $maxScore) {
                throw new \InvalidArgumentException("Score cannot exceed maximum score of {$maxScore}");
            }

            // Create or update grade
            $grade = Grade::updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'grader_id' => $grader->id,
                    'score' => $score,
                    'feedback' => $feedback,
                    'graded_at' => now(),
                    'is_published' => false, // Explicitly set to false
                    'rubric_scores' => $rubricScores,
                    'grade_letter' => $this->calculateLetterGrade($score, $maxScore),
                ]
            );

            // Mark submission as graded
            $submission->markAsGraded();

            // Log the grading action
            $this->auditLogService->logGrading($grader, $grade, 'manual_grade_created');

            // Clear relevant caches
            $this->clearGradingCaches($submission->assignment->course_id, $submission->user_id);

            DB::commit();

            return $grade;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Auto-grade a submission based on assignment configuration.
     */
    public function autoGradeSubmission(Submission $submission): ?Grade
    {
        if (!$submission->assignment->isAutoGraded()) {
            return null;
        }

        DB::beginTransaction();
        
        try {
            $score = $this->calculateAutoGradeScore($submission);
            
            if ($score === null) {
                return null;
            }

            $grade = Grade::updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'grader_id' => null, // System graded
                    'score' => $score,
                    'feedback' => 'Auto-graded by system',
                    'graded_at' => now(),
                    'grade_letter' => $this->calculateLetterGrade($score, $submission->assignment->max_score),
                ]
            );

            // Mark submission as graded
            $submission->markAsGraded();

            // Log the auto-grading action
            $this->auditLogService->logGrading(
                $submission->user, // Use student as the "grader" for auto-grade
                $grade,
                'auto_grade_created'
            );

            // Clear relevant caches
            $this->clearGradingCaches($submission->assignment->course_id, $submission->user_id);

            DB::commit();

            return $grade;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Publish grades for an assignment.
     */
    public function publishGrades(Assignment $assignment, User $publisher): int
    {
        DB::beginTransaction();
        
        try {
            $unpublishedGrades = Grade::whereHas('submission', function ($query) use ($assignment) {
                $query->where('assignment_id', $assignment->id);
            })->where('is_published', false)->get();

            $publishedCount = 0;

            foreach ($unpublishedGrades as $grade) {
                $grade->publish();
                $publishedCount++;

                // Log the publishing action
                $this->auditLogService->logGrading($publisher, $grade, 'grade_published');
            }

            // Clear relevant caches
            $this->clearAssignmentGradingCaches($assignment);

            DB::commit();

            return $publishedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate course grade for a student based on weighting scheme.
     */
    public function calculateCourseGrade(Enrollment $enrollment, array $weightingScheme = null): array
    {
        $cacheKey = "course_grade:{$enrollment->course_id}:{$enrollment->user_id}";
        
        return Cache::remember($cacheKey, 300, function () use ($enrollment, $weightingScheme) {
            $course = $enrollment->course;
            $student = $enrollment->user;

            // Get all published grades for the student in this course
            $grades = Grade::whereHas('submission', function ($query) use ($course, $student) {
                $query->whereHas('assignment', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->where('user_id', $student->id);
            })->where('is_published', true)->with('submission.assignment')->get();

            if ($grades->isEmpty()) {
                return [
                    'total_score' => 0,
                    'total_possible' => 0,
                    'percentage' => 0,
                    'letter_grade' => 'N/A',
                    'grade_breakdown' => [],
                    'weighted_score' => 0,
                ];
            }

            // Use default weighting scheme if none provided
            if (!$weightingScheme) {
                $weightingScheme = $this->getDefaultWeightingScheme();
            }

            $gradeBreakdown = [];
            $totalWeightedScore = 0;
            $totalWeight = 0;

            // Group grades by assignment type
            $gradesByType = $grades->groupBy(function ($grade) {
                return $grade->submission->assignment->assignment_type;
            });

            foreach ($weightingScheme as $type => $weight) {
                if (!isset($gradesByType[$type])) {
                    continue;
                }

                $typeGrades = $gradesByType[$type];
                $typeScore = $typeGrades->sum('score');
                $typePossible = $typeGrades->sum(function ($grade) {
                    return $grade->submission->assignment->max_score;
                });

                $typePercentage = $typePossible > 0 ? ($typeScore / $typePossible) * 100 : 0;
                $weightedContribution = ($typePercentage * $weight) / 100;

                $gradeBreakdown[$type] = [
                    'score' => $typeScore,
                    'possible' => $typePossible,
                    'percentage' => round($typePercentage, 2),
                    'weight' => $weight,
                    'weighted_contribution' => round($weightedContribution, 2),
                ];

                $totalWeightedScore += $weightedContribution;
                $totalWeight += $weight;
            }

            // Calculate final percentage
            $finalPercentage = $totalWeight > 0 ? $totalWeightedScore : 0;

            return [
                'total_score' => $grades->sum('score'),
                'total_possible' => $grades->sum(function ($grade) {
                    return $grade->submission->assignment->max_score;
                }),
                'percentage' => round($finalPercentage, 2),
                'letter_grade' => $this->calculateLetterGrade($finalPercentage, 100),
                'grade_breakdown' => $gradeBreakdown,
                'weighted_score' => round($totalWeightedScore, 2),
            ];
        });
    }

    /**
     * Get grade book data for a course.
     */
    public function getGradeBook(Course $course, array $filters = []): array
    {
        // Don't cache when filters are applied
        if (empty($filters)) {
            $cacheKey = "grade_book:{$course->id}";
            return Cache::remember($cacheKey, 600, function () use ($course, $filters) {
                return $this->buildGradeBook($course, $filters);
            });
        }
        
        return $this->buildGradeBook($course, $filters);
    }

    /**
     * Build grade book data with optional filters.
     */
    protected function buildGradeBook(Course $course, array $filters = []): array
    {
        // Get all assignments for the course
        $assignmentsQuery = $course->assignments()->published()->orderBy('due_date');
        
        // Filter by specific assignment
        if (!empty($filters['assignment_id'])) {
            $assignmentsQuery->where('id', $filters['assignment_id']);
        }
        
        $assignments = $assignmentsQuery->get();

        // Get all enrolled students
        $studentsQuery = $course->students();
        
        // Filter by student name
        if (!empty($filters['student_name'])) {
            $studentsQuery->where('name', 'like', '%' . $filters['student_name'] . '%');
        }
        
        // Search across student fields
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $students = $studentsQuery->with(['enrollments' => function($q) use ($course) {
            $q->where('course_id', $course->id);
        }])->get();

        // Get all grades for the course
        $gradesQuery = Grade::whereHas('submission', function ($query) use ($course, $filters) {
            $query->whereHas('assignment', function ($q) use ($course, $filters) {
                $q->where('course_id', $course->id);
                
                // Filter by assignment
                if (!empty($filters['assignment_id'])) {
                    $q->where('id', $filters['assignment_id']);
                }
            });
            
            // Filter by submission date range
            if (!empty($filters['submitted_from'])) {
                $query->where('submitted_at', '>=', $filters['submitted_from']);
            }
            if (!empty($filters['submitted_to'])) {
                $query->where('submitted_at', '<=', $filters['submitted_to']);
            }
            
            // Filter by late status
            if (isset($filters['late'])) {
                $query->where('is_late', $filters['late'] === 'yes');
            }
        });
        
        // Filter by graded status
        if (isset($filters['graded'])) {
            if ($filters['graded'] === 'yes') {
                $gradesQuery->whereNotNull('graded_at');
            } elseif ($filters['graded'] === 'no') {
                $gradesQuery->whereNull('graded_at');
            }
        }
        
        // Filter by grade range
        if (!empty($filters['grade_min'])) {
            $gradesQuery->where('score', '>=', $filters['grade_min']);
        }
        if (!empty($filters['grade_max'])) {
            $gradesQuery->where('score', '<=', $filters['grade_max']);
        }
        
        // Filter by published status
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'published') {
                $gradesQuery->where('is_published', true);
            } elseif ($filters['status'] === 'unpublished') {
                $gradesQuery->where('is_published', false);
            }
        } else {
            $gradesQuery->where('is_published', true);
        }
        
        $grades = $gradesQuery->with(['submission.assignment', 'submission.user', 'overrides'])
            ->withCount('overrides')
            ->get();

            // Organize grades by student and assignment
            $gradeMatrix = [];
            foreach ($students as $student) {
                $studentGrades = [];
                foreach ($assignments as $assignment) {
                    $grade = $grades->first(function ($g) use ($student, $assignment) {
                        return $g->submission->user_id === $student->id && 
                               $g->submission->assignment_id === $assignment->id;
                    });

                    $studentGrades[$assignment->id] = $grade ? [
                        'score' => $grade->score,
                        'percentage' => $grade->getPercentage(),
                        'letter_grade' => $grade->getLetterGrade(),
                        'graded_at' => $grade->graded_at,
                        'is_locked' => $grade->is_locked,
                        'overrides_count' => $grade->overrides_count ?? 0,
                        'grade_model' => $grade,
                    ] : null;
                }

                // Calculate course grade for this student
                // Use eager loaded enrollment (first one matches because of the filter in eager load)
                $enrollment = $student->enrollments->first();
                $courseGrade = $enrollment ? $this->calculateCourseGrade($enrollment) : null;

                $gradeMatrix[$student->id] = [
                    'student' => $student,
                    'grades' => $studentGrades,
                    'course_grade' => $courseGrade,
                ];
            }

            // Calculate assignment statistics
            $assignmentStats = [];
            foreach ($assignments as $assignment) {
                $assignmentGrades = $grades->filter(function ($grade) use ($assignment) {
                    return $grade->submission->assignment_id === $assignment->id;
                });

                $scores = $assignmentGrades->pluck('score');
                
                $assignmentStats[$assignment->id] = [
                    'assignment' => $assignment,
                    'submitted_count' => $assignmentGrades->count(),
                    'total_students' => $students->count(),
                    'average_score' => $scores->avg(),
                    'median_score' => $this->calculateMedian($scores->toArray()),
                    'min_score' => $scores->min(),
                    'max_score' => $scores->max(),
                    'average_percentage' => $assignment->max_score > 0 ? 
                        ($scores->avg() / $assignment->max_score) * 100 : 0,
                ];
            }

            return [
                'course' => $course,
                'assignments' => $assignments,
                'students' => $students,
                'grade_matrix' => $gradeMatrix,
                'assignment_stats' => $assignmentStats,
                'generated_at' => now(),
            ];
    }

    /**
     * Export grades for institutional reporting.
     */
    public function exportGrades(Course $course, string $format = 'csv'): array
    {
        $gradeBook = $this->getGradeBook($course);
        
        $exportData = [];
        
        // Header row
        $headers = [
            'Student ID',
            'Student Name',
            'Student Email',
        ];
        
        foreach ($gradeBook['assignments'] as $assignment) {
            $headers[] = $assignment->title . ' (Score)';
            $headers[] = $assignment->title . ' (Percentage)';
        }
        
        $headers = array_merge($headers, [
            'Course Total Score',
            'Course Percentage',
            'Course Letter Grade',
        ]);
        
        $exportData[] = $headers;
        
        // Data rows
        foreach ($gradeBook['grade_matrix'] as $studentData) {
            $student = $studentData['student'];
            $row = [
                $student->id,
                $student->name,
                $student->email,
            ];
            
            foreach ($gradeBook['assignments'] as $assignment) {
                $grade = $studentData['grades'][$assignment->id];
                $row[] = $grade ? $grade['score'] : '';
                $row[] = $grade ? round($grade['percentage'], 2) . '%' : '';
            }
            
            $courseGrade = $studentData['course_grade'];
            $row[] = $courseGrade ? $courseGrade['total_score'] : '';
            $row[] = $courseGrade ? $courseGrade['percentage'] . '%' : '';
            $row[] = $courseGrade ? $courseGrade['letter_grade'] : '';
            
            $exportData[] = $row;
        }

        // Log the export action
        if (auth()->check()) {
            $this->auditLogService->logAdminAction(
                auth()->user(),
                'grade_export',
                'course',
                $course->id,
                [
                    'format' => $format,
                    'student_count' => count($gradeBook['students']),
                    'assignment_count' => count($gradeBook['assignments']),
                ]
            );
        }
        
        return [
            'data' => $exportData,
            'filename' => "grades_{$course->title}_{now()->format('Y-m-d_H-i-s')}.{$format}",
            'course' => $course,
            'exported_at' => now(),
        ];
    }

    /**
     * Get grading statistics for a course.
     */
    public function getGradingStatistics(Course $course): array
    {
        $cacheKey = "grading_stats:{$course->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($course) {
            $assignments = $course->assignments()->published()->get();
            $totalAssignments = $assignments->count();
            
            $submissions = DB::table('submissions')
                ->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
                ->where('assignments.course_id', $course->id)
                ->count();
            
            $gradedSubmissions = DB::table('submissions')
                ->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
                ->join('grades', 'submissions.id', '=', 'grades.submission_id')
                ->where('assignments.course_id', $course->id)
                ->where('grades.is_published', true)
                ->count();
            
            $pendingGrading = $submissions - $gradedSubmissions;
            
            $averageGrade = DB::table('grades')
                ->join('submissions', 'grades.submission_id', '=', 'submissions.id')
                ->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
                ->where('assignments.course_id', $course->id)
                ->where('grades.is_published', true)
                ->avg('grades.score');
            
            return [
                'total_assignments' => $totalAssignments,
                'total_submissions' => $submissions,
                'graded_submissions' => $gradedSubmissions,
                'pending_grading' => $pendingGrading,
                'grading_completion_rate' => $submissions > 0 ? 
                    round(($gradedSubmissions / $submissions) * 100, 2) : 0,
                'average_grade' => $averageGrade ? round($averageGrade, 2) : null,
            ];
        });
    }

    /**
     * Calculate auto-grade score based on assignment type.
     */
    protected function calculateAutoGradeScore(Submission $submission): ?float
    {
        $assignment = $submission->assignment;
        
        // This is a simplified auto-grading implementation
        // In a real system, this would integrate with specific grading engines
        // based on assignment type (quiz, multiple choice, etc.)
        
        switch ($assignment->assignment_type) {
            case 'quiz':
                // For quizzes, we would parse the submission content
                // and compare against correct answers
                return $this->gradeQuizSubmission($submission);
                
            case 'multiple_choice':
                // For multiple choice, compare selected answers
                return $this->gradeMultipleChoiceSubmission($submission);
                
            default:
                // Other types require manual grading
                return null;
        }
    }

    /**
     * Grade a quiz submission (simplified implementation).
     */
    protected function gradeQuizSubmission(Submission $submission): float
    {
        // This is a placeholder implementation
        // In a real system, this would parse quiz answers and calculate score
        return 0.0;
    }

    /**
     * Grade a multiple choice submission (simplified implementation).
     */
    protected function gradeMultipleChoiceSubmission(Submission $submission): float
    {
        // This is a placeholder implementation
        // In a real system, this would compare selected answers against correct ones
        return 0.0;
    }

    /**
     * Calculate letter grade based on percentage.
     */
    protected function calculateLetterGrade(float $score, float $maxScore): string
    {
        $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Get default weighting scheme for course grades.
     */
    protected function getDefaultWeightingScheme(): array
    {
        return [
            'quiz' => 20,
            'assignment' => 40,
            'project' => 25,
            'exam' => 15,
        ];
    }

    /**
     * Calculate median of an array of numbers.
     */
    protected function calculateMedian(array $numbers): ?float
    {
        if (empty($numbers)) {
            return null;
        }
        
        sort($numbers);
        $count = count($numbers);
        $middle = floor($count / 2);
        
        if ($count % 2 === 0) {
            return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
        }
        
        return $numbers[$middle];
    }

    /**
     * Clear grading-related caches.
     */
    protected function clearGradingCaches(int $courseId, int $userId): void
    {
        Cache::forget("course_grade:{$courseId}:{$userId}");
        Cache::forget("grade_book:{$courseId}");
        Cache::forget("grading_stats:{$courseId}");
    }

    /**
     * Clear assignment-specific grading caches.
     */
    protected function clearAssignmentGradingCaches(Assignment $assignment): void
    {
        Cache::forget("grade_book:{$assignment->course_id}");
        Cache::forget("grading_stats:{$assignment->course_id}");
        
        // Clear individual student caches
        $studentIds = $assignment->submissions()->pluck('user_id');
        foreach ($studentIds as $userId) {
            Cache::forget("course_grade:{$assignment->course_id}:{$userId}");
        }
    }
}