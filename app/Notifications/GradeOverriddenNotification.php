<?php

namespace App\Notifications;

use App\Models\GradeOverride;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradeOverriddenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The grade override instance.
     */
    public GradeOverride $override;

    /**
     * Create a new notification instance.
     */
    public function __construct(GradeOverride $override)
    {
        $this->override = $override;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Check user's notification preferences
        $preferences = $notifiable->notificationPreferences()
            ->where('notification_type', 'grade_overridden')
            ->first();

        $channels = [];

        if (!$preferences || $preferences->in_app_enabled) {
            $channels[] = 'database';
        }

        if (!$preferences || $preferences->email_enabled) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $override = $this->override->load(['grade.submission.assignment.course', 'grade.submission.user', 'admin']);
        
        $assignment = $override->grade->submission->assignment;
        $student = $override->grade->submission->user;
        $admin = $override->admin;
        
        $scoreDifference = $override->getScoreDifference();
        $changeType = $override->isIncrease() ? 'increased' : 'decreased';
        
        return (new MailMessage)
            ->subject('Grade Override Notification')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('An administrator has overridden a grade that you assigned.')
            ->line('**Assignment:** ' . $assignment->title)
            ->line('**Course:** ' . $assignment->course->title)
            ->line('**Student:** ' . $student->name)
            ->line('**Original Score:** ' . $override->original_score . ' / ' . $assignment->max_score)
            ->line('**New Score:** ' . $override->new_score . ' / ' . $assignment->max_score)
            ->line('**Change:** ' . abs($scoreDifference) . ' points ' . $changeType)
            ->line('**Overridden By:** ' . $admin->name)
            ->line('**Reason:** ' . $override->reason)
            ->line('**Date:** ' . $override->overridden_at->format('F j, Y g:i A'))
            ->action('View Grade Details', route('teacher.gradebook.override-history', $override->grade))
            ->line('If you have questions about this override, please contact the administrator.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $override = $this->override->load(['grade.submission.assignment', 'grade.submission.user', 'admin']);
        
        return [
            'type' => 'grade_overridden',
            'grade_id' => $override->grade_id,
            'override_id' => $override->id,
            'assignment_title' => $override->grade->submission->assignment->title,
            'student_name' => $override->grade->submission->user->name,
            'original_score' => $override->original_score,
            'new_score' => $override->new_score,
            'admin_name' => $override->admin->name,
            'reason' => $override->reason,
            'overridden_at' => $override->overridden_at->toIso8601String(),
            'url' => route('teacher.gradebook.override-history', $override->grade),
        ];
    }
}
