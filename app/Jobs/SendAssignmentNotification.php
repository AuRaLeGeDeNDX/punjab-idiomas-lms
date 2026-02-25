<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Models\User;
use App\Notifications\AssignmentPublishedNotification;
use App\Notifications\AssignmentDueReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAssignmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Assignment $assignment;
    public string $notificationType;
    public ?User $specificUser;

    /**
     * Create a new job instance.
     */
    public function __construct(Assignment $assignment, string $notificationType, ?User $specificUser = null)
    {
        $this->assignment = $assignment;
        $this->notificationType = $notificationType;
        $this->specificUser = $specificUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            switch ($this->notificationType) {
                case 'published':
                    $this->sendPublishedNotification();
                    break;
                case 'due_reminder':
                    $this->sendDueReminderNotification();
                    break;
                default:
                    Log::warning('Unknown assignment notification type', [
                        'type' => $this->notificationType,
                        'assignment_id' => $this->assignment->id
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send assignment notification', [
                'assignment_id' => $this->assignment->id,
                'notification_type' => $this->notificationType,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send assignment published notification to enrolled students.
     */
    private function sendPublishedNotification(): void
    {
        $enrolledStudents = User::whereHas('enrollments', function ($query) {
            $query->where('course_id', $this->assignment->course_id);
        })->where('roles.name', 'student')->get();

        foreach ($enrolledStudents as $student) {
            $student->notify(new AssignmentPublishedNotification($this->assignment));
        }

        Log::info('Assignment published notifications sent', [
            'assignment_id' => $this->assignment->id,
            'student_count' => $enrolledStudents->count()
        ]);
    }

    /**
     * Send assignment due reminder notification.
     */
    private function sendDueReminderNotification(): void
    {
        if ($this->specificUser) {
            // Send to specific user
            $this->specificUser->notify(new AssignmentDueReminderNotification($this->assignment));
        } else {
            // Send to all enrolled students who haven't submitted
            $studentsWithoutSubmission = User::whereHas('enrollments', function ($query) {
                $query->where('course_id', $this->assignment->course_id);
            })
            ->whereDoesntHave('submissions', function ($query) {
                $query->where('assignment_id', $this->assignment->id);
            })
            ->where('roles.name', 'student')
            ->get();

            foreach ($studentsWithoutSubmission as $student) {
                $student->notify(new AssignmentDueReminderNotification($this->assignment));
            }

            Log::info('Assignment due reminder notifications sent', [
                'assignment_id' => $this->assignment->id,
                'student_count' => $studentsWithoutSubmission->count()
            ]);
        }
    }
}