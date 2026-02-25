<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $alertMessage;
    public string $alertType;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $message, string $type = 'info')
    {
        $this->alertMessage = $message;
        $this->alertType = $type;
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
        $subject = match($this->alertType) {
            'warning' => 'System Warning',
            'error' => 'System Alert',
            'success' => 'System Update',
            default => 'System Notification'
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a system notification.')
            ->line($this->alertMessage)
            ->when($this->alertType === 'error', function ($mail) {
                return $mail->error();
            })
            ->when($this->alertType === 'success', function ($mail) {
                return $mail->success();
            })
            ->line('If you have any questions, please contact support.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'system_alert',
            'alert_type' => $this->alertType,
            'message' => $this->alertMessage,
            'created_at' => now()->toISOString(),
        ];
    }
}