<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log user authentication events.
     */
    public function logAuthentication(User $user, string $action, Request $request, bool $success = true): void
    {
        $this->createAuditLog([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => 'authentication',
            'resource_id' => $user->id,
            'details' => [
                'success' => $success,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log course access events.
     */
    public function logCourseAccess(User $user, $course, string $action, Request $request): void
    {
        $this->createAuditLog([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => 'course',
            'resource_id' => $course->id,
            'details' => [
                'course_title' => $course->title,
                'user_role' => $user->getRoleNames()->first(),
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log assignment submission events.
     */
    public function logAssignmentSubmission(User $user, $assignment, $submission, string $action): void
    {
        $this->createAuditLog([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => 'assignment_submission',
            'resource_id' => $submission->id,
            'details' => [
                'assignment_id' => $assignment->id,
                'assignment_title' => $assignment->title,
                'course_id' => $assignment->course_id,
                'submission_type' => $submission->submission_type,
                'is_late' => $submission->is_late,
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log grading events.
     */
    public function logGrading(User $grader, $grade, string $action): void
    {
        $submission = $grade->submission;
        $assignment = $submission->assignment;
        
        $this->createAuditLog([
            'user_id' => $grader->id,
            'action' => $action,
            'resource_type' => 'grade',
            'resource_id' => $grade->id,
            'details' => [
                'student_id' => $submission->user_id,
                'assignment_id' => $assignment->id,
                'assignment_title' => $assignment->title,
                'course_id' => $assignment->course_id,
                'score' => $grade->score,
                'max_score' => $assignment->max_score,
                'is_published' => $grade->is_published,
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log exercise access events (security-critical).
     */
    public function logExerciseAccess(User $user, $exercise, string $action, array $additionalDetails = []): void
    {
        $details = array_merge([
            'exercise_id' => $exercise->id,
            'exercise_title' => $exercise->title,
            'subpage_id' => $exercise->subpage_id,
            'user_role' => $user->getRoleNames()->first(),
            'timestamp' => now()->toISOString(),
        ], $additionalDetails);

        $this->createAuditLog([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => 'exercise',
            'resource_id' => $exercise->id,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Log security-critical events separately
        if (in_array($action, ['answer_access_attempt', 'unauthorized_access'])) {
            Log::warning('Security-critical exercise access', [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'action' => $action,
                'details' => $details,
            ]);
        }
    }

    /**
     * Log file access events.
     */
    public function logFileAccess(User $user, string $filePath, string $action, bool $success = true): void
    {
        $this->createAuditLog([
            'user_id' => $user->id,
            'action' => $action,
            'resource_type' => 'file',
            'resource_id' => null,
            'details' => [
                'file_path' => $filePath,
                'success' => $success,
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log administrative actions.
     */
    public function logAdminAction(User $admin, string $action, string $resourceType, $resourceId = null, array $details = []): void
    {
        $auditDetails = array_merge([
            'admin_action' => true,
            'timestamp' => now()->toISOString(),
        ], $details);

        $this->createAuditLog([
            'user_id' => $admin->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $auditDetails,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Log admin actions separately for security
        Log::info('Admin action performed', [
            'admin_id' => $admin->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $auditDetails,
        ]);
    }

    /**
     * Log security violations.
     */
    public function logSecurityViolation(User $user, string $violationType, array $details = []): void
    {
        $auditDetails = array_merge([
            'violation_type' => $violationType,
            'severity' => 'high',
            'timestamp' => now()->toISOString(),
        ], $details);

        $this->createAuditLog([
            'user_id' => $user->id,
            'action' => 'security_violation',
            'resource_type' => 'security',
            'resource_id' => null,
            'details' => $auditDetails,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Log security violations with high priority
        Log::error('Security violation detected', [
            'user_id' => $user->id,
            'violation_type' => $violationType,
            'details' => $auditDetails,
        ]);
    }

    /**
     * Get audit logs for a specific user.
     */
    public function getUserAuditLogs(User $user, int $limit = 50): array
    {
        return DB::table('audit_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get audit logs for a specific resource.
     */
    public function getResourceAuditLogs(string $resourceType, $resourceId, int $limit = 50): array
    {
        return DB::table('audit_logs')
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get security-related audit logs.
     */
    public function getSecurityAuditLogs(int $limit = 100): array
    {
        return DB::table('audit_logs')
            ->whereIn('action', [
                'login_failed',
                'unauthorized_access',
                'security_violation',
                'answer_access_attempt',
                'idor_attempt',
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Generate audit report for a date range.
     */
    public function generateAuditReport(\DateTime $startDate, \DateTime $endDate): array
    {
        $logs = DB::table('audit_logs')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $report = [
            'total_events' => $logs->count(),
            'unique_users' => $logs->pluck('user_id')->unique()->count(),
            'actions_summary' => $logs->groupBy('action')->map->count(),
            'resource_types_summary' => $logs->groupBy('resource_type')->map->count(),
            'security_events' => $logs->whereIn('action', [
                'login_failed',
                'unauthorized_access',
                'security_violation',
            ])->count(),
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];

        return $report;
    }

    /**
     * Clean up old audit logs.
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return DB::table('audit_logs')
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Create an audit log entry.
     */
    private function createAuditLog(array $data): void
    {
        try {
            DB::table('audit_logs')->insert(array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        } catch (\Exception $e) {
            // Log the error but don't fail the main operation
            Log::error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }
}