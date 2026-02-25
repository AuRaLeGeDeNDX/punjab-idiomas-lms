<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure file upload limits and security settings for assignment submissions
    |
    */

    // Maximum file size per file (in bytes) - default 10MB
    'max_file_size' => env('ASSIGNMENT_MAX_FILE_SIZE', 10485760),

    // Maximum total submission size (in bytes) - default 50MB
    'max_total_size' => env('ASSIGNMENT_MAX_TOTAL_SIZE', 52428800),

    // Maximum number of files per submission
    'max_files_per_submission' => env('ASSIGNMENT_MAX_FILES', 5),

    // Signed URL expiration time (in minutes)
    'signed_url_expiration' => env('ASSIGNMENT_SIGNED_URL_EXPIRATION', 60),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification preferences and defaults
    |
    */

    'notifications' => [
        'types' => [
            'assignment_published',
            'assignment_due_reminder',
            'submission_received',
            'grade_published',
            'grade_overridden',
        ],
        
        // Default notification preferences for new users
        'defaults' => [
            'in_app_enabled' => true,
            'email_enabled' => true,
        ],
        
        // Due date reminder threshold (in hours)
        'due_reminder_threshold' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure storage paths for assignment files
    |
    */

    'storage' => [
        'disk' => 'local',
        'path' => 'assignments',
    ],
];
