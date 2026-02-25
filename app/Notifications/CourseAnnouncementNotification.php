<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Announcement $announcement;

    /**
     * Create a new notification instance.
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        
        // Check if user should receive this notification type
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'course_announcement', 'mail')) {
            $channels[] = 'mail';
        }
        
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'course_announcement', 'database')) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->announcement->course;
        $url = route('courses.announcements.show', [$course, $this->announcement]);

        return (new MailMessage)
            ->subject('Course Announcement: ' . $this->announcement->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new announcement has been posted in your course.')
            ->line('**Course:** ' . $course->title)
            ->line('**Announcement:** ' . $this->announcement->title)
            ->line('**Message:**')
            ->line($this->announcement->message)
            ->action('View Announcement', $url)
            ->line('Stay updated with your course!')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $course = $this->announcement->course;

        return [
            'type' => 'course_announcement',
            'announcement_id' => $this->announcement->id,
            'announcement_title' => $this->announcement->title,
            'course_id' => $course->id,
            'course_title' => $course->title,
            'priority' => $this->announcement->priority,
            'message_preview' => substr($this->announcement->message, 0, 100),
            'created_at' => $this->announcement->created_at->toISOString(),
        ];
    }
}