<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Services\CourseAssignmentService;
use App\Policies\CourseAssignmentPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CourseAssignmentController extends Controller
{
    protected CourseAssignmentService $assignmentService;

    public function __construct(CourseAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Assign students to a course.
     */
    public function assignStudents(Request $request, Course $course): JsonResponse
    {
        // Check authorization
        if (!Gate::allows('canAssignStudents', $course)) {
            return response()->json([
                'message' => 'You are not authorized to assign students to this course.',
                'error' => 'Unauthorized'
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $results = $this->assignmentService->bulkAssignStudents(
                $validated['student_ids'],
                $course,
                $request->user(),
                $validated['notes'] ?? null
            );

            return response()->json([
                'message' => 'Assignment operation completed.',
                'data' => $results,
                'summary' => [
                    'successful_count' => count($results['successful']),
                    'failed_count' => count($results['failed']),
                    'total_count' => count($validated['student_ids']),
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a student from a course.
     */
    public function removeStudent(Course $course, User $student): JsonResponse
    {
        // Check authorization
        if (!Gate::allows('canRemoveStudents', $course)) {
            return response()->json([
                'message' => 'You are not authorized to remove students from this course.',
                'error' => 'Unauthorized'
            ], 403);
        }

        try {
            $this->assignmentService->removeStudentFromCourse($student, $course);

            return response()->json([
                'message' => 'Student successfully removed from course.',
                'data' => [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while removing the student.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enrolled students for a course.
     */
    public function getEnrolledStudents(Course $course): JsonResponse
    {
        // Check authorization
        if (!Gate::allows('canViewEnrolledStudents', $course)) {
            return response()->json([
                'message' => 'You are not authorized to view enrolled students for this course.',
                'error' => 'Unauthorized'
            ], 403);
        }

        try {
            $enrolledStudents = $this->assignmentService->getEnrolledStudents($course);

            return response()->json([
                'message' => 'Enrolled students retrieved successfully.',
                'data' => [
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                        'teacher_name' => $course->teacher->name,
                    ],
                    'enrolled_students' => $enrolledStudents,
                    'total_count' => $enrolledStudents->count(),
                ]
            ], 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving enrolled students.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available students for assignment to a course.
     */
    public function getAvailableStudents(Course $course): JsonResponse
    {
        // Check authorization
        if (!Gate::allows('canViewAvailableStudents', $course)) {
            return response()->json([
                'message' => 'You are not authorized to view available students for this course.',
                'error' => 'Unauthorized'
            ], 403);
        }

        try {
            $availableStudents = $this->assignmentService->getAvailableStudents($course);

            return response()->json([
                'message' => 'Available students retrieved successfully.',
                'data' => [
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                        'max_students' => $course->max_students,
                        'current_enrollment_count' => $course->getActiveEnrollmentsCount(),
                    ],
                    'available_students' => $availableStudents,
                    'total_count' => $availableStudents->count(),
                ]
            ], 200)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching available students: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'An error occurred while retrieving available students.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get courses assigned to a student.
     */
    public function getStudentCourses(User $student): JsonResponse
    {
        // Check authorization - users can view their own courses, admins and teachers can view any
        $user = request()->user();
        if ($user->id !== $student->id && !$user->hasRole(['Admin', 'Teacher'])) {
            return response()->json([
                'message' => 'You are not authorized to view this student\'s courses.',
                'error' => 'Unauthorized'
            ], 403);
        }

        try {
            $studentCourses = $this->assignmentService->getStudentCourses($student);

            return response()->json([
                'message' => 'Student courses retrieved successfully.',
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                    ],
                    'courses' => $studentCourses,
                    'total_count' => $studentCourses->count(),
                    'active_count' => $studentCourses->where('status', 'active')->count(),
                    'completed_count' => $studentCourses->where('status', 'completed')->count(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving student courses.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enrollment status for a specific student-course combination.
     */
    public function getEnrollmentStatus(Course $course, User $student): JsonResponse
    {
        // Check authorization
        $user = request()->user();
        if (!Gate::allows('canViewEnrolledStudents', $course) && 
            $user->id !== $student->id) {
            return response()->json([
                'message' => 'You are not authorized to view this enrollment status.',
                'error' => 'Unauthorized'
            ], 403);
        }

        try {
            $enrollmentStatus = $this->assignmentService->getEnrollmentStatus($student, $course);

            return response()->json([
                'message' => 'Enrollment status retrieved successfully.',
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                    ],
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                    ],
                    'enrollment_status' => $enrollmentStatus,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving enrollment status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}