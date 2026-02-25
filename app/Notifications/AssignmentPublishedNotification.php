<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentPublishedNotification extends Notification implements ShouldQueue
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
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'assignment_published', 'mail')) {
            $channels[] = 'mail';
        }
        
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'assignment_published', 'database')) {
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

        return (new MailMessage)
            ->subject('New Assignment: ' . $this->assignment->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new assignment has been published in your course.')
            ->line('**Assignment:** ' . $this->assignment->title)
            ->line('**Course:** ' . $this->assignment->course->title)
            ->when($this->assignment->due_date, function ($mail) {
                return $mail->line('**Due Date:** ' . $this->assignment->due_date->format('M j, Y g:i A'));
            })
            ->when($this->assignment->description, function ($mail) {
                return $mail->line('**Description:** ' . $this->assignment->description);
            })
            ->action('View Assignment', $url)
            ->line('Please submit your work before the due date.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_published',
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'course_id' => $this->assignment->course_id,
            'course_title' => $this->assignment->course->title,
            'due_date' => $this->assignment->due_date?->toISOString(),
            'message' => "New assignment '{$this->assignment->title}' has been published in {$this->assignment->course->title}",
        ];
    }
}