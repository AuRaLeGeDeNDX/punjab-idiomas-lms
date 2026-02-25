<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentDueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Assignment $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        
        // Check if user should receive this notification type
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'assignment_due_reminder', 'mail')) {
            $channels[] = 'mail';
        }
        
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'assignment_due_reminder', 'database')) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('student.courses.modules.subpages.assignments.show', [
            'course' => $this->assignment->course_id,
            'module' => $this->assignment->module_id,
            'subpage' => $this->assignment->subpage_id,
            'assignment' => $this->assignment->id
        ]);

        $timeUntilDue = $this->assignment->due_date->diffForHumans();
        $isOverdue = $this->assignment->due_date->isPast();

        return (new MailMessage)
            ->subject(($isOverdue ? 'Overdue' : 'Due Soon') . ': ' . $this->assignment->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($isOverdue 
                ? 'This assignment is now overdue.' 
                : 'This assignment is due soon.')
            ->line('**Assignment:** ' . $this->assignment->title)
            ->line('**Course:** ' . $this->assignment->course->title)
            ->line('**Due Date:** ' . $this->assignment->due_date->format('M j, Y g:i A'))
            ->line('**Status:** ' . ($isOverdue ? "Overdue ({$timeUntilDue})" : "Due {$timeUntilDue}"))
            ->when($isOverdue && $this->assignment->allowsLateSubmission(), function ($mail) {
                return $mail->line('Late submissions are still accepted for this assignment.');
            })
            ->when($isOverdue && !$this->assignment->allowsLateSubmission(), function ($mail) {
                return $mail->line('**Important:** Late submissions are not accepted for this assignment.');
            })
            ->action('View Assignment', $url)
            ->line($isOverdue 
                ? 'Please submit as soon as possible if late submissions are allowed.' 
                : 'Please submit your work before the deadline.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $isOverdue = $this->assignment->due_date->isPast();
        $timeUntilDue = $this->assignment->due_date->diffForHumans();

        return [
            'type' => 'assignment_due_reminder',
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'course_id' => $this->assignment->course_id,
            'course_title' => $this->assignment->course->title,
            'due_date' => $this->assignment->due_date->toISOString(),
            'is_overdue' => $isOverdue,
            'time_until_due' => $timeUntilDue,
            'allows_late_submission' => $this->assignment->allowsLateSubmission(),
            'message' => $isOverdue 
                ? "Assignment '{$this->assignment->title}' is overdue ({$timeUntilDue})"
                : "Assignment '{$this->assignment->title}' is due {$timeUntilDue}",
        ];
    }
}