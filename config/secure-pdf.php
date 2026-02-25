<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token Expiration
    |--------------------------------------------------------------------------
    |
    | This value determines how long (in minutes) a secure PDF viewer session
    | token remains valid. After this time, users will need to request a new
    | viewing link. Default is 60 minutes (1 hour).
    |
    */

    'token_expiration_minutes' => env('SECURE_PDF_TOKEN_EXPIRATION', 60),

    /*
    |--------------------------------------------------------------------------
    | Watermark Configuration
    |--------------------------------------------------------------------------
    |
    | These values control the appearance of the watermark overlay that
    | displays user identification information over the PDF content.
    |
    */

    'watermark_opacity' => env('SECURE_PDF_WATERMARK_OPACITY', 0.2),
    'watermark_font_size' => env('SECURE_PDF_WATERMARK_FONT_SIZE', 24),
    'watermark_rotation' => env('SECURE_PDF_WATERMARK_ROTATION', -45),
    'watermark_color' => env('SECURE_PDF_WATERMARK_COLOR', '#000000'),

    /*
    |--------------------------------------------------------------------------
    | IP Address Tracking
    |--------------------------------------------------------------------------
    |
    | When enabled, the user's IP address will be displayed in the watermark
    | overlay and logged with each access attempt. This helps trace
    | unauthorized sharing back to the source.
    |
    */

    'enable_ip_tracking' => env('SECURE_PDF_ENABLE_IP_TRACKING', false),

    /*
    |--------------------------------------------------------------------------
    | Access Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, all PDF access attempts (successful and failed) will be
    | logged to the database for security monitoring and audit purposes.
    |
    */

    'log_access_attempts' => env('SECURE_PDF_LOG_ACCESS', true),

    /*
    |--------------------------------------------------------------------------
    | Viewer UI Configuration
    |--------------------------------------------------------------------------
    |
    | These values control the appearance and behavior of the secure PDF
    | viewer interface.
    |
    */

    'viewer_background_color' => env('SECURE_PDF_VIEWER_BG_COLOR', '#2c3e50'),
    'enable_page_navigation' => env('SECURE_PDF_ENABLE_PAGE_NAV', true),
    'enable_zoom_controls' => env('SECURE_PDF_ENABLE_ZOOM', true),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | These values control various security features of the PDF viewer.
    |
    */

    'block_right_click' => env('SECURE_PDF_BLOCK_RIGHT_CLICK', true),
    'block_keyboard_shortcuts' => env('SECURE_PDF_BLOCK_SHORTCUTS', true),
    'block_text_selection' => env('SECURE_PDF_BLOCK_TEXT_SELECT', true),
    'detect_developer_tools' => env('SECURE_PDF_DETECT_DEVTOOLS', true),

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | These values control performance-related features.
    |
    */

    'chunk_size' => env('SECURE_PDF_CHUNK_SIZE', 8192), // 8KB chunks for streaming
    'cache_tokens' => env('SECURE_PDF_CACHE_TOKENS', true),
    'token_cache_ttl' => env('SECURE_PDF_TOKEN_CACHE_TTL', 300), // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | Signed URL Configuration
    |--------------------------------------------------------------------------
    |
    | Controls whether signed URLs are required for PDF streaming.
    | Set to false for development/testing if experiencing signature issues.
    | ALWAYS set to true in production for security.
    |
    */

    'require_signed_urls' => env('SECURE_PDF_REQUIRE_SIGNED_URLS', true),

];
