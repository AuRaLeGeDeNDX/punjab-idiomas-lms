<?php

namespace App\Notifications;

use App\Models\ForumPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForumReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ForumPost $post;

    /**
     * Create a new notification instance.
     */
    public function __construct(ForumPost $post)
    {
        $this->post = $post;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        
        // Check if user should receive this notification type
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'forum_reply', 'mail')) {
            $channels[] = 'mail';
        }
        
        if (\App\Models\NotificationPreference::shouldNotify($notifiable->id, 'forum_reply', 'database')) {
            $channels[] = 'database';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $topic = $this->post->topic;
        $forum = $topic->forum;
        $url = route('forums.topics.show', [
            'forum' => $forum->id,
            'topic' => $topic->id
        ]);

        return (new MailMessage)
            ->subject('New Reply: ' . $topic->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Someone has replied to a forum topic you\'re following.')
            ->line('**Forum:** ' . $forum->title)
            ->line('**Topic:** ' . $topic->title)
            ->line('**Reply by:** ' . $this->post->user->name)
            ->line('**Reply:**')
            ->line(substr($this->post->content, 0, 200) . (strlen($this->post->content) > 200 ? '...' : ''))
            ->action('View Topic', $url)
            ->line('Join the discussion!')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $topic = $this->post->topic;
        $forum = $topic->forum;

        return [
            'type' => 'forum_reply',
            'post_id' => $this->post->id,
            'topic_id' => $topic->id,
            'topic_title' => $topic->title,
            'forum_id' => $forum->id,
            'forum_title' => $forum->title,
            'course_id' => $forum->course_id,
            'course_title' => $forum->course->title,
            'reply_by' => $this->post->user->name,
            'reply_preview' => substr($this->post->content, 0, 100),
            'created_at' => $this->post->created_at->toISOString(),
        ];
    }
}