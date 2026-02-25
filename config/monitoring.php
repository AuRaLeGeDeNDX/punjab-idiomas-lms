<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Upload Performance Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file defines settings for monitoring file upload
    | performance, alerting thresholds, and dashboard configurations.
    |
    */

    'enabled' => env('UPLOAD_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    |
    | Define thresholds for various performance metrics that trigger alerts
    | when exceeded. Values are used by the monitoring system to determine
    | when to generate warnings or critical alerts.
    |
    */
    'thresholds' => [
        'upload_duration' => [
            'warning' => env('UPLOAD_DURATION_WARNING_THRESHOLD', 30), // seconds
            'critical' => env('UPLOAD_DURATION_CRITICAL_THRESHOLD', 60), // seconds
        ],
        'memory_usage' => [
            'warning' => env('UPLOAD_MEMORY_WARNING_THRESHOLD', 50 * 1024 * 1024), // 50MB
            'critical' => env('UPLOAD_MEMORY_CRITICAL_THRESHOLD', 100 * 1024 * 1024), // 100MB
        ],
        'upload_speed' => [
            'warning' => env('UPLOAD_SPEED_WARNING_THRESHOLD', 100 * 1024), // 100KB/s
            'critical' => env('UPLOAD_SPEED_CRITICAL_THRESHOLD', 50 * 1024), // 50KB/s
        ],
        'failure_rate' => [
            'warning' => env('UPLOAD_FAILURE_RATE_WARNING', 10), // 10%
            'critical' => env('UPLOAD_FAILURE_RATE_CRITICAL', 20), // 20%
        ],
        'disk_usage' => [
            'warning' => env('DISK_USAGE_WARNING_THRESHOLD', 80), // 80%
            'critical' => env('DISK_USAGE_CRITICAL_THRESHOLD', 90), // 90%
        ],
        'memory_limit' => [
            'warning' => env('MEMORY_LIMIT_WARNING_THRESHOLD', 80), // 80%
            'critical' => env('MEMORY_LIMIT_CRITICAL_THRESHOLD', 90), // 90%
        ],
        'concurrent_uploads' => [
            'warning' => env('CONCURRENT_UPLOADS_WARNING', 10),
            'critical' => env('CONCURRENT_UPLOADS_CRITICAL', 20),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for alert generation, notification channels, and
    | alert management settings.
    |
    */
    'alerts' => [
        'enabled' => env('UPLOAD_ALERTS_ENABLED', true),
        'channels' => [
            'log' => env('ALERT_LOG_ENABLED', true),
            'email' => env('ALERT_EMAIL_ENABLED', false),
            'slack' => env('ALERT_SLACK_ENABLED', false),
            'webhook' => env('ALERT_WEBHOOK_ENABLED', false),
        ],
        'notification_settings' => [
            'email_recipients' => env('ALERT_EMAIL_RECIPIENTS', 'admin@example.com'),
            'slack_webhook_url' => env('ALERT_SLACK_WEBHOOK_URL'),
            'webhook_url' => env('ALERT_WEBHOOK_URL'),
        ],
        'rate_limiting' => [
            'max_alerts_per_hour' => env('MAX_ALERTS_PER_HOUR', 10),
            'cooldown_minutes' => env('ALERT_COOLDOWN_MINUTES', 15),
        ],
        'severity_levels' => ['info', 'warning', 'critical'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the monitoring dashboard including refresh intervals,
    | data retention periods, and display preferences.
    |
    */
    'dashboard' => [
        'enabled' => env('MONITORING_DASHBOARD_ENABLED', true),
        'refresh_interval' => env('DASHBOARD_REFRESH_INTERVAL', 30), // seconds
        'auto_refresh' => env('DASHBOARD_AUTO_REFRESH', true),
        'default_period' => env('DASHBOARD_DEFAULT_PERIOD', 'day'),
        'available_periods' => ['hour', 'day', 'week', 'month'],
        'charts' => [
            'upload_success_rate' => true,
            'upload_duration_trend' => true,
            'memory_usage_trend' => true,
            'disk_usage_status' => true,
            'failure_patterns' => true,
            'performance_metrics' => true,
        ],
        'widgets' => [
            'system_health' => true,
            'active_alerts' => true,
            'recent_uploads' => true,
            'performance_recommendations' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long monitoring data is retained in cache and logs.
    | Longer retention periods provide better trend analysis but use more storage.
    |
    */
    'retention' => [
        'metrics_cache_ttl' => env('METRICS_CACHE_TTL', 3600), // 1 hour
        'alerts_cache_ttl' => env('ALERTS_CACHE_TTL', 86400), // 24 hours
        'performance_stats_ttl' => env('PERFORMANCE_STATS_TTL', 604800), // 7 days
        'log_retention_days' => env('LOG_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Intervals
    |--------------------------------------------------------------------------
    |
    | Define how frequently various monitoring tasks should run.
    | These settings affect system resource usage and monitoring accuracy.
    |
    */
    'intervals' => [
        'health_check' => env('HEALTH_CHECK_INTERVAL', 300), // 5 minutes
        'metrics_collection' => env('METRICS_COLLECTION_INTERVAL', 60), // 1 minute
        'alert_evaluation' => env('ALERT_EVALUATION_INTERVAL', 120), // 2 minutes
        'cleanup_old_data' => env('CLEANUP_INTERVAL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Monitoring
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring server resources during file uploads.
    | These settings help identify resource bottlenecks and capacity issues.
    |
    */
    'resources' => [
        'monitor_disk_space' => env('MONITOR_DISK_SPACE', true),
        'monitor_memory_usage' => env('MONITOR_MEMORY_USAGE', true),
        'monitor_cpu_load' => env('MONITOR_CPU_LOAD', true),
        'monitor_network_io' => env('MONITOR_NETWORK_IO', false),
        'paths_to_monitor' => [
            'upload_directory' => storage_path('app/public'),
            'temp_directory' => sys_get_temp_dir(),
            'log_directory' => storage_path('logs'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for integrating with external monitoring tools
    | and services like Prometheus, Grafana, or custom monitoring solutions.
    |
    */
    'integrations' => [
        'prometheus' => [
            'enabled' => env('PROMETHEUS_INTEGRATION_ENABLED', false),
            'endpoint' => env('PROMETHEUS_ENDPOINT', '/metrics'),
            'namespace' => env('PROMETHEUS_NAMESPACE', 'upload_monitoring'),
        ],
        'grafana' => [
            'enabled' => env('GRAFANA_INTEGRATION_ENABLED', false),
            'dashboard_url' => env('GRAFANA_DASHBOARD_URL'),
        ],
        'custom_webhook' => [
            'enabled' => env('CUSTOM_WEBHOOK_ENABLED', false),
            'url' => env('CUSTOM_WEBHOOK_URL'),
            'secret' => env('CUSTOM_WEBHOOK_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring security-related upload events and
    | generating alerts for suspicious activities.
    |
    */
    'security' => [
        'monitor_failed_uploads' => env('MONITOR_FAILED_UPLOADS', true),
        'monitor_large_files' => env('MONITOR_LARGE_FILES', true),
        'monitor_unusual_patterns' => env('MONITOR_UNUSUAL_PATTERNS', true),
        'suspicious_activity_threshold' => env('SUSPICIOUS_ACTIVITY_THRESHOLD', 5),
        'large_file_threshold' => env('LARGE_FILE_THRESHOLD', 50 * 1024 * 1024), // 50MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    |
    | Settings to optimize the monitoring system itself to minimize
    | impact on application performance.
    |
    */
    'optimization' => [
        'batch_metrics_updates' => env('BATCH_METRICS_UPDATES', true),
        'async_alert_processing' => env('ASYNC_ALERT_PROCESSING', true),
        'cache_dashboard_data' => env('CACHE_DASHBOARD_DATA', true),
        'compress_historical_data' => env('COMPRESS_HISTORICAL_DATA', true),
    ],
];