<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAnnouncementNotification extends Notification implements ShouldQueue
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
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'system_alert', 'mail')) {
            $channels[] = 'mail';
        }
        
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'system_alert', 'database')) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.announcements.show', $this->announcement);

        return (new MailMessage)
            ->subject('System Announcement: ' . $this->announcement->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new system-wide announcement has been posted.')
            ->line('**Title:** ' . $this->announcement->title)
            ->when($this->announcement->priority === 'high', function ($mail) {
                return $mail->line('**Priority:** HIGH PRIORITY');
            })
            ->line('**Message:**')
            ->line($this->announcement->message)
            ->action('View Announcement', $url)
            ->line('This is a system-wide announcement from the administration.')
            ->salutation('Best regards, ' . config('app.name') . ' Administration');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'system_announcement',
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'priority' => $this->announcement->priority,
            'message_preview' => substr($this->announcement->message, 0, 100),
            'created_at' => $this->announcement->created_at->toISOString(),
        ];
    }
}