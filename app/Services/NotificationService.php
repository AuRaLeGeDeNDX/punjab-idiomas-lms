<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Announcement;
use App\Models\Message;
use App\Models\NotificationPreference;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Notifications\CourseAnnouncementNotification;
use App\Notifications\DirectMessageNotification;
use App\Notifications\SystemAlertNotification;
use App\Notifications\ForumReplyNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send course announcement to all enrolled students.
     */
    public function sendCourseAnnouncement(Course $course, string $title, string $message, User $sender): Announcement
    {
        // Create announcement record
        $announcement = Announcement::create([
            'course_id' => $course->id,
            'user_id' => $sender->id,
            'title' => $title,
            'message' => $message,
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Get all enrolled students
        $enrolledStudents = $course->students()->get();

        // Send notifications to students based on their preferences
        foreach ($enrolledStudents as $student) {
            if ($this->shouldReceiveNotification($student, 'course_announcement')) {
                $student->notify(new CourseAnnouncementNotification($announcement));
            }
        }

        Log::info('Course announcement sent', [
            'announcement_id' => $announcement->id,
            'course_id' => $course->id,
            'recipient_count' => $enrolledStudents->count(),
            'sender_id' => $sender->id,
        ]);

        return $announcement;
    }

    /**
     * Send assignment deadline reminder notifications.
     */
    public function notifyAssignmentDue(Assignment $assignment): void
    {
        // Get students who haven't submitted yet
        $studentsWithoutSubmission = $assignment->course->students()
            ->whereDoesntHave('submissions', function ($query) use ($assignment) {
                $query->where('assignment_id', $assignment->id);
            })
            ->get();

        foreach ($studentsWithoutSubmission as $student) {
            if ($this->shouldReceiveNotification($student, 'assignment_reminder')) {
                $student->notify(new \App\Notifications\AssignmentDueReminderNotification($assignment));
            }
        }

        Log::info('Assignment due reminders sent', [
            'assignment_id' => $assignment->id,
            'recipient_count' => $studentsWithoutSubmission->count(),
        ]);
    }

    /**
     * Send grade published notification to student.
     */
    public function notifyGradePublished(Grade $grade): void
    {
        $student = $grade->submission->user;
        
        if ($this->shouldReceiveNotification($student, 'grade_published')) {
            $student->notify(new \App\Notifications\GradePublishedNotification($grade));
        }

        Log::info('Grade published notification sent', [
            'grade_id' => $grade->id,
            'student_id' => $student->id,
            'assignment_id' => $grade->submission->assignment_id,
        ]);
    }

    /**
     * Send direct message between users.
     */
    public function sendDirectMessage(User $sender, User $recipient, string $subject, string $message): Message
    {
        // Check if sender can message recipient
        if (!$this->canSendMessage($sender, $recipient)) {
            throw new \Exception('You do not have permission to send messages to this user.');
        }

        // Create message record
        $messageRecord = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => $subject,
            'message' => $message,
            'sent_at' => now(),
        ]);

        // Send notification if recipient allows direct messages
        if ($this->shouldReceiveNotification($recipient, 'direct_message')) {
            $recipient->notify(new DirectMessageNotification($messageRecord));
        }

        Log::info('Direct message sent', [
            'message_id' => $messageRecord->id,
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
        ]);

        return $messageRecord;
    }

    /**
     * Send forum reply notification.
     */
    public function notifyForumReply(ForumPost $post): void
    {
        $topic = $post->topic;
        $topicCreator = $topic->user;
        
        // Don't notify if the reply author is the same as topic creator
        if ($post->user_id === $topicCreator->id) {
            return;
        }
        
        if ($this->shouldReceiveNotification($topicCreator, 'forum_reply')) {
            $topicCreator->notify(new ForumReplyNotification($post));
        }

        Log::info('Forum reply notification sent', [
            'post_id' => $post->id,
            'topic_id' => $topic->id,
            'topic_creator_id' => $topicCreator->id,
            'reply_author_id' => $post->user_id,
        ]);
    }

    /**
     * Send system alert to user.
     */
    public function sendSystemAlert(User $user, string $message, string $type = 'info'): void
    {
        if ($this->shouldReceiveNotification($user, 'system_alert')) {
            $user->notify(new SystemAlertNotification($message, $type));
        }

        Log::info('System alert sent', [
            'user_id' => $user->id,
            'type' => $type,
            'message' => $message,
        ]);
    }

    /**
     * Bulk send notifications to multiple users.
     */
    public function bulkNotify(Collection $users, $notification): void
    {
        foreach ($users as $user) {
            $user->notify($notification);
        }

        Log::info('Bulk notification sent', [
            'recipient_count' => $users->count(),
            'notification_class' => get_class($notification),
        ]);
    }

    /**
     * Get user's notification preferences.
     */
    public function getUserPreferences(User $user): NotificationPreference
    {
        return NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'email_notifications' => true,
                'database_notifications' => true,
                'course_announcement' => true,
                'assignment_reminder' => true,
                'grade_published' => true,
                'direct_message' => true,
                'system_alert' => true,
                'forum_reply' => true,
            ]
        );
    }

    /**
     * Update user's notification preferences.
     */
    public function updateUserPreferences(User $user, array $preferences): NotificationPreference
    {
        $userPreferences = $this->getUserPreferences($user);
        $userPreferences->update($preferences);

        Log::info('Notification preferences updated', [
            'user_id' => $user->id,
            'preferences' => $preferences,
        ]);

        return $userPreferences;
    }

    /**
     * Check if user should receive a specific type of notification.
     */
    private function shouldReceiveNotification(User $user, string $type): bool
    {
        $preferences = $this->getUserPreferences($user);
        
        // Check if the specific notification type is enabled
        return $preferences->{$type} ?? true;
    }

    /**
     * Check if sender can send message to recipient based on roles.
     */
    private function canSendMessage(User $sender, User $recipient): bool
    {
        // Admins can message anyone
        if ($sender->hasRole('admin')) {
            return true;
        }

        // Teachers can message students in their courses and other teachers
        if ($sender->hasRole('teacher')) {
            if ($recipient->hasRole('admin') || $recipient->hasRole('teacher')) {
                return true;
            }

            if ($recipient->hasRole('student')) {
                // Check if student is enrolled in any of teacher's courses
                return $sender->teachingCourses()
                    ->whereHas('enrollments', function ($query) use ($recipient) {
                        $query->where('user_id', $recipient->id);
                    })
                    ->exists();
            }
        }

        // Students can message teachers of their courses and admins
        if ($sender->hasRole('student')) {
            if ($recipient->hasRole('admin')) {
                return true;
            }

            if ($recipient->hasRole('teacher')) {
                // Check if teacher teaches any of student's courses
                return $sender->enrollments()
                    ->whereHas('course', function ($query) use ($recipient) {
                        $query->where('teacher_id', $recipient->id);
                    })
                    ->exists();
            }
        }

        return false;
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read for user.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * Get unread notification count for user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Clean up old notifications.
     */
    public function cleanupOldNotifications(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        $deletedCount = \DB::table('notifications')
            ->where('created_at', '<', $cutoffDate)
            ->where('read_at', '!=', null)
            ->delete();

        Log::info('Old notifications cleaned up', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate,
        ]);

        return $deletedCount;
    }
}