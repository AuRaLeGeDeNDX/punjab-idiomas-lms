<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Grade $grade;

    /**
     * Create a new notification instance.
     */
    public function __construct(Grade $grade)
    {
        $this->grade = $grade;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        
        // Check if user should receive this notification type
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'grade_published', 'mail')) {
            $channels[] = 'mail';
        }
        
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'grade_published', 'database')) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $assignment = $this->grade->submission->assignment;
        $url = route('student.grades.assignment', [
            'assignment' => $assignment->id
        ]);

        $percentage = $this->grade->getPercentage();
        $scoreText = "{$this->grade->score}/{$assignment->max_score}";
        if ($percentage > 0) {
            $scoreText .= " ({$percentage}%)";
        }

        return (new MailMessage)
            ->subject('Grade Published: ' . $assignment->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your grade has been published for an assignment.')
            ->line('**Assignment:** ' . $assignment->title)
            ->line('**Course:** ' . $assignment->course->title)
            ->line('**Score:** ' . $scoreText)
            ->when($this->grade->feedback, function ($mail) {
                return $mail->line('**Feedback:** ' . $this->grade->feedback);
            })
            ->line('**Graded by:** ' . $this->grade->grader->name)
            ->line('**Graded on:** ' . $this->grade->graded_at->format('M j, Y g:i A'))
            ->action('View Assignment', $url)
            ->line('Keep up the great work!')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $assignment = $this->grade->submission->assignment;
        
        return [
            'type' => 'grade_published',
            'grade_id' => $this->grade->id,
            'assignment_id' => $assignment->id,
            'assignment_title' => $assignment->title,
            'course_id' => $assignment->course_id,
            'course_title' => $assignment->course->title,
            'score' => $this->grade->score,
            'max_score' => $assignment->max_score,
            'percentage' => $this->grade->getPercentage(),
            'feedback' => $this->grade->feedback,
            'grader_name' => $this->grade->grader->name,
            'graded_at' => $this->grade->graded_at->toISOString(),
            'message' => "Grade published for '{$assignment->title}': {$this->grade->score}/{$assignment->max_score}",
        ];
    }
}