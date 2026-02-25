<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DirectMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Message $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        
        // Check if user should receive this notification type
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'direct_message', 'mail')) {
            $channels[] = 'mail';
        }
        
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'direct_message', 'database')) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('messages.show', $this->message);

        return (new MailMessage)
            ->subject('New Message: ' . $this->message->subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new message.')
            ->line('**From:** ' . $this->message->sender->name)
            ->line('**Subject:** ' . $this->message->subject)
            ->line('**Message:**')
            ->line(substr($this->message->message, 0, 200) . (strlen($this->message->message) > 200 ? '...' : ''))
            ->action('Read Message', $url)
            ->line('Reply to continue the conversation!')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'direct_message',
            'message_id' => $this->message->id,
            'subject' => $this->message->subject,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'message_preview' => substr($this->message->message, 0, 100),
            'sent_at' => $this->message->sent_at->toISOString(),
        ];
    }
}