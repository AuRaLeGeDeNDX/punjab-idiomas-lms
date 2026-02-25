<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_notifications',
        'database_notifications',
        'course_announcement',
        'assignment_reminder',
        'grade_published',
        'direct_message',
        'system_alert',
        'forum_reply',
        'assignment_published',
        'course_update',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'database_notifications' => 'boolean',
        'course_announcement' => 'boolean',
        'assignment_reminder' => 'boolean',
        'grade_published' => 'boolean',
        'direct_message' => 'boolean',
        'system_alert' => 'boolean',
        'forum_reply' => 'boolean',
        'assignment_published' => 'boolean',
        'course_update' => 'boolean',
    ];

    /**
     * Get the user that owns the notification preference
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if email notifications are enabled
     */
    public function isEmailEnabled(): bool
    {
        return $this->email_notifications;
    }

    /**
     * Check if database (in-app) notifications are enabled
     */
    public function isInAppEnabled(): bool
    {
        return $this->database_notifications;
    }

    /**
     * Get or create default preferences for a user
     */
    public static function getOrCreateDefaults(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'email_notifications' => true,
                'database_notifications' => true,
                'course_announcement' => true,
                'assignment_reminder' => true,
                'grade_published' => true,
                'direct_message' => true,
                'system_alert' => true,
                'forum_reply' => true,
                'assignment_published' => true,
                'course_update' => true,
            ]
        );
    }

    /**
     * Check if user should receive a notification of given type
     */
    public static function shouldNotify(int $userId, string $notificationType, string $channel = 'database'): bool
    {
        $preference = self::where('user_id', $userId)->first();

        // If no preference exists, use defaults (all enabled)
        if (!$preference) {
            return true;
        }

        // Check channel-level preference first
        if ($channel === 'mail' && !$preference->email_notifications) {
            return false;
        }
        
        if ($channel === 'database' && !$preference->database_notifications) {
            return false;
        }

        // Map notification types to preference fields
        $typeMap = [
            'assignment_published' => 'assignment_published',
            'assignment_due_reminder' => 'assignment_reminder',
            'submission_received' => 'course_update',
            'grade_published' => 'grade_published',
            'grade_overridden' => 'grade_published',
            'course_announcement' => 'course_announcement',
        ];

        $field = $typeMap[$notificationType] ?? null;
        
        if ($field && isset($preference->$field)) {
            return (bool) $preference->$field;
        }

        // Default to true if type not mapped
        return true;
    }

    /**
     * Check if assignment published notifications are enabled
     */
    public function shouldReceiveAssignmentPublished(): bool
    {
        return $this->assignment_published && ($this->email_notifications || $this->database_notifications);
    }

    /**
     * Check if assignment reminder notifications are enabled
     */
    public function shouldReceiveAssignmentReminder(): bool
    {
        return $this->assignment_reminder && ($this->email_notifications || $this->database_notifications);
    }

    /**
     * Check if grade published notifications are enabled
     */
    public function shouldReceiveGradePublished(): bool
    {
        return $this->grade_published && ($this->email_notifications || $this->database_notifications);
    }
}

