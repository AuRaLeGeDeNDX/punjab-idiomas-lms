<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Display grades for all enrolled courses.
     */
    public function index()
    {
        $user = auth()->user();
        
        $enrollments = $user->enrollments()
            ->with(['course.assignments' => function ($query) {
                $query->published()->orderBy('due_date');
            }])
            ->where('status', 'active')
            ->get();

        $courseGrades = [];
        
        foreach ($enrollments as $enrollment) {
            $courseGrade = $this->gradingService->calculateCourseGrade($enrollment);
            $courseGrades[$enrollment->course_id] = $courseGrade;
        }

        return view('student.grades.index', compact('enrollments', 'courseGrades'));
    }

    /**
     * Display grades for a specific course.
     */
    public function course(Course $course)
    {
        $user = auth()->user();
        
        // Check if student is enrolled in the course
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this course.');
        }

        // Get student's grades for this course
        $grades = \App\Models\Grade::whereHas('submission', function ($query) use ($course, $user) {
            $query->whereHas('assignment', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })->where('user_id', $user->id);
        })->where('is_published', true)
          ->with(['submission.assignment'])
          ->orderBy('graded_at', 'desc')
          ->get();

        // Calculate course grade
        $courseGrade = $this->gradingService->calculateCourseGrade($enrollment);

        // Get assignments without grades (pending)
        $pendingAssignments = $course->assignments()
            ->published()
            ->whereDoesntHave('submissions', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereHas('grade', function ($q) {
                          $q->where('is_published', true);
                      });
            })
            ->orderBy('due_date')
            ->get();

        return view('student.grades.course', compact(
            'course',
            'enrollment',
            'grades',
            'courseGrade',
            'pendingAssignments'
        ));
    }

    /**
     * Display grade details for a specific assignment.
     */
    public function assignment(\App\Models\Assignment $assignment)
    {
        $user = auth()->user();
        
        // Check if student is enrolled in the course
        $enrollment = $user->enrollments()
            ->where('course_id', $assignment->course_id)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this course.');
        }

        // Get the student's submission and grade
        $submission = $assignment->submissions()
            ->with('grade')
            ->where('user_id', $user->id)
            ->first();

        if (!$submission) {
            abort(404, 'No submission found for this assignment.');
        }

        $grade = $submission->grade;

        if (!$grade || !$grade->is_published) {
            abort(404, 'Grade not available yet.');
        }

        return view('student.grades.assignment', compact(
            'assignment',
            'submission',
            'grade'
        ));
    }

    /**
     * Get grade statistics for student dashboard.
     */
    public function statistics()
    {
        $user = auth()->user();
        
        $enrollments = $user->enrollments()
            ->where('status', 'active')
            ->with('course')
            ->get();

        $statistics = [
            'total_courses' => $enrollments->count(),
            'courses_with_grades' => 0,
            'average_grade' => 0,
            'passing_courses' => 0,
            'recent_grades' => [],
        ];

        $totalGradePoints = 0;
        $coursesWithGrades = 0;

        foreach ($enrollments as $enrollment) {
            $courseGrade = $this->gradingService->calculateCourseGrade($enrollment);
            
            if ($courseGrade['percentage'] > 0) {
                $coursesWithGrades++;
                $totalGradePoints += $courseGrade['percentage'];
                
                if ($courseGrade['percentage'] >= $enrollment->course->getPassingGrade()) {
                    $statistics['passing_courses']++;
                }
            }
        }

        $statistics['courses_with_grades'] = $coursesWithGrades;
        $statistics['average_grade'] = $coursesWithGrades > 0 ? 
            round($totalGradePoints / $coursesWithGrades, 2) : 0;

        // Get recent grades
        $statistics['recent_grades'] = \App\Models\Grade::whereHas('submission', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('is_published', true)
          ->with(['submission.assignment.course'])
          ->orderBy('published_at', 'desc')
          ->limit(5)
          ->get();

        return response()->json($statistics);
    }

    /**
     * Export student's transcript.
     */
    public function transcript(Request $request)
    {
        $user = auth()->user();
        $format = $request->get('format', 'pdf');

        $enrollments = $user->enrollments()
            ->with(['course.assignments'])
            ->where('status', 'active')
            ->get();

        $transcriptData = [];
        
        foreach ($enrollments as $enrollment) {
            $courseGrade = $this->gradingService->calculateCourseGrade($enrollment);
            
            $transcriptData[] = [
                'course' => $enrollment->course,
                'enrollment' => $enrollment,
                'grade' => $courseGrade,
                'completed_at' => $enrollment->completed_at,
            ];
        }

        if ($format === 'pdf') {
            return $this->generateTranscriptPdf($user, $transcriptData);
        } else {
            return $this->generateTranscriptCsv($user, $transcriptData);
        }
    }

    /**
     * Generate PDF transcript (simplified implementation).
     */
    protected function generateTranscriptPdf($user, $transcriptData)
    {
        // This would use a PDF library like DomPDF or similar
        // For now, return a simple view that can be printed
        return view('student.grades.transcript-pdf', compact('user', 'transcriptData'));
    }

    /**
     * Generate CSV transcript.
     */
    protected function generateTranscriptCsv($user, $transcriptData)
    {
        $filename = "transcript_{$user->id}_" . now()->format('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($transcriptData) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, [
                'Course Title',
                'Course Code',
                'Credits',
                'Grade Percentage',
                'Letter Grade',
                'Completion Date',
            ]);
            
            // Data
            foreach ($transcriptData as $data) {
                fputcsv($handle, [
                    $data['course']->title,
                    $data['course']->id,
                    $data['course']->duration_hours ?? 'N/A',
                    $data['grade']['percentage'] . '%',
                    $data['grade']['letter_grade'],
                    $data['completed_at'] ? $data['completed_at']->format('Y-m-d') : 'In Progress',
                ]);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}