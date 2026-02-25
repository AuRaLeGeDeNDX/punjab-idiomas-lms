<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Disk
    |--------------------------------------------------------------------------
    | Where local database dump files are stored before uploading to Drive.
    */
    'local_disk' => 'local',
    'local_path' => 'backups/db',

    /*
    |--------------------------------------------------------------------------
    | Backup Retention
    |--------------------------------------------------------------------------
    | Local backup files older than this many days will be cleaned up.
    | Google Drive backups are NEVER deleted.
    */
    'local_retention_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Database Backup
    |--------------------------------------------------------------------------
    */
    'database' => [
        // mysqldump binary (on Windows; on Linux: 'mysqldump')
        'dump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'compress'    => true,   // gzip the SQL file
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Drive Settings
    |--------------------------------------------------------------------------
    */
    'google_drive' => [
        'credentials_path' => env('GOOGLE_DRIVE_CREDENTIALS_PATH', storage_path('app/google-service-account.json')),
        'backup_folder_id' => env('GOOGLE_DRIVE_BACKUP_FOLDER_ID', ''),
        'folder_structure' => [
            'database' => 'Backups/Database',
            'videos'   => 'Backups/Files/videos',
            'pdfs'     => 'Backups/Files/pdfs',
            'content'  => 'Backups/Files/content',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare R2 Backup Path
    |--------------------------------------------------------------------------
    | Within the r2 disk, where backup copies of files are stored.
    */
    'r2_backup_prefix' => 'backups/files',

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Settings
    |--------------------------------------------------------------------------
    */
    'ffmpeg' => [
        'binaries'  => env('FFMPEG_BINARIES_PATH', 'ffmpeg'),
        'ffprobe'   => env('FFPROBE_BINARIES_PATH', 'ffprobe'),
        'threads'   => env('FFMPEG_THREADS', 0),      // 0 = auto
        'timeout'   => env('FFMPEG_TIMEOUT', 3600),   // seconds

        // HLS output resolutions  [label => [width, height, video_bitrate, audio_bitrate]]
        'hls_renditions' => [
            '360p'  => [640,  360,  '800k',  '96k'],
            '720p'  => [1280, 720,  '2500k', '128k'],
            '1080p' => [1920, 1080, '5000k', '192k'],
        ],
        'hls_time'     => 6,     // segment length in seconds
        'hls_list_size'=> 0,     // 0 = keep all segments in playlist
    ],

];
