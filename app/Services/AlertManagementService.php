<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

/**
 * AlertManagementService handles alert generation, notification, and management.
 * 
 * This service creates alerts based on monitoring thresholds, sends notifications
 * through various channels, and manages alert lifecycle and escalation.
 * 
 * Requirements: 4.4
 */
class AlertManagementService
{
    private array $config;
    private array $thresholds;

    public function __construct()
    {
        $this->config = config('monitoring.alerts', []);
        $this->thresholds = config('monitoring.thresholds', []);
    }

    /**
     * Create and process a new alert.
     * 
     * @param string $type Alert type
     * @param string $severity Alert severity (info, warning, critical)
     * @param string $message Alert message
     * @param array $context Additional context data
     * @param string|null $correlationId Optional correlation ID
     * @return array Created alert data
     */
    public function createAlert(
        string $type,
        string $severity,
        string $message,
        array $context = [],
        ?string $correlationId = null
    ): array {
        $alert = [
            'id' => uniqid('alert_'),
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'correlation_id' => $correlationId,
            'created_at' => now()->toISOString(),
            'status' => 'active',
            'acknowledged' => false,
            'resolved' => false,
            'notification_sent' => false,
        ];

        // Check rate limiting
        if ($this->isRateLimited($type, $severity)) {
            Log::info('Alert rate limited', [
                'alert_type' => $type,
                'severity' => $severity,
                'message' => $message,
            ]);
            return $alert;
        }

        // Store alert
        $this->storeAlert($alert);

        // Send notifications
        if ($this->config['enabled'] ?? true) {
            $this->sendAlertNotifications($alert);
        }

        // Log alert creation
        Log::warning('Alert created', [
            'alert_id' => $alert['id'],
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'correlation_id' => $correlationId,
        ]);

        return $alert;
    }

    /**
     * Create performance-related alerts based on metrics.
     * 
     * @param array $performanceMetrics Performance metrics
     * @param string|null $correlationId Optional correlation ID
     * @return array Created alerts
     */
    public function createPerformanceAlerts(array $performanceMetrics, ?string $correlationId = null): array
    {
        $alerts = [];

        // Check upload duration
        if (isset($performanceMetrics['duration_seconds'])) {
            $duration = $performanceMetrics['duration_seconds'];
            $alert = $this->checkThreshold(
                'upload_duration',
                $duration,
                'Upload Duration Alert',
                "Upload took " . ($performanceMetrics['duration_formatted'] ?? $duration . 's'),
                $correlationId
            );
            if ($alert) {
                $alerts[] = $alert;
            }
        }

        // Check memory usage
        if (isset($performanceMetrics['memory_used'])) {
            $memoryUsed = $performanceMetrics['memory_used'];
            $alert = $this->checkThreshold(
                'memory_usage',
                $memoryUsed,
                'Memory Usage Alert',
                "Upload used " . ($performanceMetrics['memory_used_formatted'] ?? $this->formatBytes($memoryUsed)) . " memory",
                $correlationId
            );
            if ($alert) {
                $alerts[] = $alert;
            }
        }

        // Check upload speed
        if (isset($performanceMetrics['upload_speed_bps']) && isset($performanceMetrics['file_size'])) {
            $speed = $performanceMetrics['upload_speed_bps'];
            $fileSize = $performanceMetrics['file_size'];
            
            if ($fileSize > 1024 * 1024) { // Only check for files > 1MB
                $alert = $this->checkThreshold(
                    'upload_speed',
                    $speed,
                    'Upload Speed Alert',
                    "Upload speed of " . ($performanceMetrics['upload_speed_formatted'] ?? $this->formatBytes($speed) . '/s') . " is slow",
                    $correlationId,
                    'reverse' // Lower values are worse for speed
                );
                if ($alert) {
                    $alerts[] = $alert;
                }
            }
        }

        return $alerts;
    }

    /**
     * Create system resource alerts.
     * 
     * @param array $resourceMetrics Resource metrics
     * @return array Created alerts
     */
    public function createResourceAlerts(array $resourceMetrics): array
    {
        $alerts = [];

        // Check disk usage
        if (isset($resourceMetrics['disk_usage']['upload_directory']['usage_percentage'])) {
            $diskUsage = $resourceMetrics['disk_usage']['upload_directory']['usage_percentage'];
            $alert = $this->checkThreshold(
                'disk_usage',
                $diskUsage,
                'Disk Usage Alert',
                "Upload directory disk usage is at {$diskUsage}%"
            );
            if ($alert) {
                $alerts[] = $alert;
            }
        }

        // Check memory limit usage
        if (isset($resourceMetrics['memory_usage']['usage_percentage'])) {
            $memoryUsage = $resourceMetrics['memory_usage']['usage_percentage'];
            $alert = $this->checkThreshold(
                'memory_limit',
                $memoryUsage,
                'Memory Limit Alert',
                "System memory usage is at {$memoryUsage}%"
            );
            if ($alert) {
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    /**
     * Create failure rate alerts.
     * 
     * @param float $failureRate Failure rate percentage
     * @param string $period Time period
     * @return array|null Created alert or null
     */
    public function createFailureRateAlert(float $failureRate, string $period): ?array
    {
        return $this->checkThreshold(
            'failure_rate',
            $failureRate,
            'Upload Failure Rate Alert',
            "Upload failure rate is {$failureRate}% over the last {$period}"
        );
    }

    /**
     * Check if a metric exceeds thresholds and create alert if needed.
     * 
     * @param string $metricType Metric type
     * @param float $value Current value
     * @param string $title Alert title
     * @param string $message Alert message
     * @param string|null $correlationId Optional correlation ID
     * @param string $direction Threshold direction ('normal' or 'reverse')
     * @return array|null Created alert or null
     */
    private function checkThreshold(
        string $metricType,
        float $value,
        string $title,
        string $message,
        ?string $correlationId = null,
        string $direction = 'normal'
    ): ?array {
        $thresholds = $this->thresholds[$metricType] ?? null;
        if (!$thresholds) {
            return null;
        }

        $severity = null;
        $threshold = null;

        if ($direction === 'normal') {
            // Higher values are worse
            if (isset($thresholds['critical']) && $value >= $thresholds['critical']) {
                $severity = 'critical';
                $threshold = $thresholds['critical'];
            } elseif (isset($thresholds['warning']) && $value >= $thresholds['warning']) {
                $severity = 'warning';
                $threshold = $thresholds['warning'];
            }
        } else {
            // Lower values are worse (reverse threshold)
            if (isset($thresholds['critical']) && $value <= $thresholds['critical']) {
                $severity = 'critical';
                $threshold = $thresholds['critical'];
            } elseif (isset($thresholds['warning']) && $value <= $thresholds['warning']) {
                $severity = 'warning';
                $threshold = $thresholds['warning'];
            }
        }

        if ($severity) {
            return $this->createAlert(
                $metricType,
                $severity,
                $message,
                [
                    'metric_type' => $metricType,
                    'current_value' => $value,
                    'threshold' => $threshold,
                    'direction' => $direction,
                    'title' => $title,
                ],
                $correlationId
            );
        }

        return null;
    }

    /**
     * Send alert notifications through configured channels.
     * 
     * @param array $alert Alert data
     */
    private function sendAlertNotifications(array $alert): void
    {
        $channels = $this->config['channels'] ?? [];

        try {
            // Log notification
            if ($channels['log'] ?? true) {
                $this->sendLogNotification($alert);
            }

            // Email notification
            if ($channels['email'] ?? false) {
                $this->sendEmailNotification($alert);
            }

            // Slack notification
            if ($channels['slack'] ?? false) {
                $this->sendSlackNotification($alert);
            }

            // Webhook notification
            if ($channels['webhook'] ?? false) {
                $this->sendWebhookNotification($alert);
            }

            // Mark notification as sent
            $alert['notification_sent'] = true;
            $this->updateAlert($alert);

        } catch (\Exception $e) {
            Log::error('Failed to send alert notifications', [
                'alert_id' => $alert['id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send log notification.
     * 
     * @param array $alert Alert data
     */
    private function sendLogNotification(array $alert): void
    {
        $logLevel = $alert['severity'] === 'critical' ? 'error' : 'warning';
        
        Log::channel('alerts')->{$logLevel}('Alert notification', [
            'alert_id' => $alert['id'],
            'type' => $alert['type'],
            'severity' => $alert['severity'],
            'message' => $alert['message'],
            'context' => $alert['context'],
            'correlation_id' => $alert['correlation_id'],
        ]);
    }

    /**
     * Send email notification.
     * 
     * @param array $alert Alert data
     */
    private function sendEmailNotification(array $alert): void
    {
        $recipients = $this->config['notification_settings']['email_recipients'] ?? '';
        if (empty($recipients)) {
            return;
        }

        $recipientList = explode(',', $recipients);
        
        foreach ($recipientList as $recipient) {
            $recipient = trim($recipient);
            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::send('emails.alert', ['alert' => $alert], function ($message) use ($recipient, $alert) {
                        $message->to($recipient)
                               ->subject("Upload Monitoring Alert: {$alert['type']} ({$alert['severity']})")
                               ->priority($alert['severity'] === 'critical' ? 1 : 3);
                    });
                } catch (\Exception $e) {
                    Log::error('Failed to send email alert', [
                        'recipient' => $recipient,
                        'alert_id' => $alert['id'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Send Slack notification.
     * 
     * @param array $alert Alert data
     */
    private function sendSlackNotification(array $alert): void
    {
        $webhookUrl = $this->config['notification_settings']['slack_webhook_url'] ?? '';
        if (empty($webhookUrl)) {
            return;
        }

        $color = $alert['severity'] === 'critical' ? 'danger' : 'warning';
        $emoji = $alert['severity'] === 'critical' ? ':rotating_light:' : ':warning:';

        $payload = [
            'text' => "{$emoji} Upload Monitoring Alert",
            'attachments' => [
                [
                    'color' => $color,
                    'title' => $alert['context']['title'] ?? $alert['type'],
                    'text' => $alert['message'],
                    'fields' => [
                        [
                            'title' => 'Severity',
                            'value' => strtoupper($alert['severity']),
                            'short' => true,
                        ],
                        [
                            'title' => 'Type',
                            'value' => $alert['type'],
                            'short' => true,
                        ],
                        [
                            'title' => 'Time',
                            'value' => $alert['created_at'],
                            'short' => true,
                        ],
                    ],
                ],
            ],
        ];

        if (isset($alert['context']['current_value']) && isset($alert['context']['threshold'])) {
            $payload['attachments'][0]['fields'][] = [
                'title' => 'Current Value',
                'value' => $alert['context']['current_value'],
                'short' => true,
            ];
            $payload['attachments'][0]['fields'][] = [
                'title' => 'Threshold',
                'value' => $alert['context']['threshold'],
                'short' => true,
            ];
        }

        try {
            Http::post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Failed to send Slack alert', [
                'alert_id' => $alert['id'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send webhook notification.
     * 
     * @param array $alert Alert data
     */
    private function sendWebhookNotification(array $alert): void
    {
        $webhookUrl = $this->config['notification_settings']['webhook_url'] ?? '';
        if (empty($webhookUrl)) {
            return;
        }

        $payload = [
            'event' => 'alert_created',
            'alert' => $alert,
            'timestamp' => now()->toISOString(),
        ];

        // Add signature if secret is configured
        $secret = $this->config['notification_settings']['webhook_secret'] ?? '';
        $headers = ['Content-Type' => 'application/json'];
        
        if (!empty($secret)) {
            $signature = hash_hmac('sha256', json_encode($payload), $secret);
            $headers['X-Signature'] = 'sha256=' . $signature;
        }

        try {
            Http::withHeaders($headers)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            Log::error('Failed to send webhook alert', [
                'alert_id' => $alert['id'],
                'webhook_url' => $webhookUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if alert type/severity is rate limited.
     * 
     * @param string $type Alert type
     * @param string $severity Alert severity
     * @return bool True if rate limited
     */
    private function isRateLimited(string $type, string $severity): bool
    {
        $rateLimiting = $this->config['rate_limiting'] ?? [];
        $maxAlertsPerHour = $rateLimiting['max_alerts_per_hour'] ?? 10;
        $cooldownMinutes = $rateLimiting['cooldown_minutes'] ?? 15;

        $hourKey = "alerts_count_" . now()->format('Y-m-d-H');
        $cooldownKey = "alert_cooldown_{$type}_{$severity}";

        // Check hourly limit
        $alertsThisHour = Cache::get($hourKey, 0);
        if ($alertsThisHour >= $maxAlertsPerHour) {
            return true;
        }

        // Check cooldown for specific alert type/severity
        if (Cache::has($cooldownKey)) {
            return true;
        }

        // Update counters
        Cache::increment($hourKey, 1);
        Cache::put($hourKey, $alertsThisHour + 1, now()->addHour());
        Cache::put($cooldownKey, true, now()->addMinutes($cooldownMinutes));

        return false;
    }

    /**
     * Store alert in cache for retrieval.
     * 
     * @param array $alert Alert data
     */
    private function storeAlert(array $alert): void
    {
        $alertKey = "alert_{$alert['id']}";
        $alertsListKey = "active_alerts_" . now()->format('Y-m-d-H');

        // Store individual alert
        Cache::put($alertKey, $alert, now()->addDays(7));

        // Add to active alerts list
        $activeAlerts = Cache::get($alertsListKey, []);
        $activeAlerts[] = $alert['id'];
        Cache::put($alertsListKey, $activeAlerts, now()->addDays(1));

        // Store in severity-specific list
        $severityKey = "alerts_{$alert['severity']}_" . now()->format('Y-m-d-H');
        $severityAlerts = Cache::get($severityKey, []);
        $severityAlerts[] = $alert['id'];
        Cache::put($severityKey, $severityAlerts, now()->addDays(1));
    }

    /**
     * Update existing alert.
     * 
     * @param array $alert Alert data
     */
    private function updateAlert(array $alert): void
    {
        $alertKey = "alert_{$alert['id']}";
        Cache::put($alertKey, $alert, now()->addDays(7));
    }

    /**
     * Get active alerts.
     * 
     * @param string|null $severity Filter by severity
     * @param int $limit Maximum number of alerts to return
     * @return array Active alerts
     */
    public function getActiveAlerts(?string $severity = null, int $limit = 50): array
    {
        $alerts = [];
        $hours = 24; // Look back 24 hours

        for ($i = 0; $i < $hours; $i++) {
            $hourKey = now()->subHours($i)->format('Y-m-d-H');
            $listKey = $severity ? "alerts_{$severity}_{$hourKey}" : "active_alerts_{$hourKey}";
            
            $alertIds = Cache::get($listKey, []);
            
            foreach ($alertIds as $alertId) {
                $alert = Cache::get("alert_{$alertId}");
                if ($alert && !$alert['resolved']) {
                    $alerts[] = $alert;
                }
            }
        }

        // Sort by creation time (newest first)
        usort($alerts, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($alerts, 0, $limit);
    }

    /**
     * Acknowledge an alert.
     * 
     * @param string $alertId Alert ID
     * @param string $acknowledgedBy User who acknowledged the alert
     * @return bool Success status
     */
    public function acknowledgeAlert(string $alertId, string $acknowledgedBy): bool
    {
        $alert = Cache::get("alert_{$alertId}");
        if (!$alert) {
            return false;
        }

        $alert['acknowledged'] = true;
        $alert['acknowledged_by'] = $acknowledgedBy;
        $alert['acknowledged_at'] = now()->toISOString();

        $this->updateAlert($alert);

        Log::info('Alert acknowledged', [
            'alert_id' => $alertId,
            'acknowledged_by' => $acknowledgedBy,
        ]);

        return true;
    }

    /**
     * Resolve an alert.
     * 
     * @param string $alertId Alert ID
     * @param string $resolvedBy User who resolved the alert
     * @param string|null $resolution Resolution notes
     * @return bool Success status
     */
    public function resolveAlert(string $alertId, string $resolvedBy, ?string $resolution = null): bool
    {
        $alert = Cache::get("alert_{$alertId}");
        if (!$alert) {
            return false;
        }

        $alert['resolved'] = true;
        $alert['resolved_by'] = $resolvedBy;
        $alert['resolved_at'] = now()->toISOString();
        $alert['resolution'] = $resolution;
        $alert['status'] = 'resolved';

        $this->updateAlert($alert);

        Log::info('Alert resolved', [
            'alert_id' => $alertId,
            'resolved_by' => $resolvedBy,
            'resolution' => $resolution,
        ]);

        return true;
    }

    /**
     * Get alert statistics.
     * 
     * @param string $period Time period
     * @return array Alert statistics
     */
    public function getAlertStatistics(string $period = 'day'): array
    {
        $stats = [
            'total_alerts' => 0,
            'critical_alerts' => 0,
            'warning_alerts' => 0,
            'info_alerts' => 0,
            'acknowledged_alerts' => 0,
            'resolved_alerts' => 0,
            'active_alerts' => 0,
        ];

        $hours = $period === 'hour' ? 1 : ($period === 'day' ? 24 : ($period === 'week' ? 168 : 720));

        for ($i = 0; $i < $hours; $i++) {
            $hourKey = now()->subHours($i)->format('Y-m-d-H');
            
            foreach (['critical', 'warning', 'info'] as $severity) {
                $alertIds = Cache::get("alerts_{$severity}_{$hourKey}", []);
                $stats["{$severity}_alerts"] += count($alertIds);
                $stats['total_alerts'] += count($alertIds);

                foreach ($alertIds as $alertId) {
                    $alert = Cache::get("alert_{$alertId}");
                    if ($alert) {
                        if ($alert['acknowledged']) {
                            $stats['acknowledged_alerts']++;
                        }
                        if ($alert['resolved']) {
                            $stats['resolved_alerts']++;
                        } else {
                            $stats['active_alerts']++;
                        }
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Format bytes into human-readable format.
     * 
     * @param int $bytes Number of bytes
     * @return string Formatted size string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Clean up old alerts and statistics.
     * 
     * @param int $daysToKeep Number of days to keep alerts
     */
    public function cleanupOldAlerts(int $daysToKeep = 7): void
    {
        $cutoffDate = now()->subDays($daysToKeep);
        $cleaned = 0;

        // This is a simplified cleanup - in a real implementation,
        // you might want to iterate through stored alert keys
        for ($i = $daysToKeep; $i < $daysToKeep + 30; $i++) {
            $date = now()->subDays($i);
            $hourKey = $date->format('Y-m-d-H');
            
            foreach (['critical', 'warning', 'info'] as $severity) {
                $listKey = "alerts_{$severity}_{$hourKey}";
                $alertIds = Cache::get($listKey, []);
                
                foreach ($alertIds as $alertId) {
                    Cache::forget("alert_{$alertId}");
                    $cleaned++;
                }
                
                Cache::forget($listKey);
            }
            
            Cache::forget("active_alerts_{$hourKey}");
        }

        Log::info('Old alerts cleaned up', [
            'alerts_cleaned' => $cleaned,
            'cutoff_date' => $cutoffDate->toISOString(),
        ]);
    }
}