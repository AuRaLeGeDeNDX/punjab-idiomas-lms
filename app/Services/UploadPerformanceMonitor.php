<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * UploadPerformanceMonitor provides comprehensive monitoring and alerting for file upload performance.
 * 
 * This service tracks upload metrics, analyzes performance trends, and generates alerts
 * for recurring upload issues to help maintain optimal system performance.
 * 
 * Requirements: 4.4
 */
class UploadPerformanceMonitor
{
    /**
     * Get comprehensive upload performance dashboard data.
     * 
     * @param string $period Time period for analysis ('hour', 'day', 'week', 'month')
     * @return array Dashboard data
     */
    public function getDashboardData(string $period = 'day'): array
    {
        $fileUploadLogger = new FileUploadLogger();
        
        return [
            'period' => $period,
            'timestamp' => now()->toISOString(),
            'success_rates' => $fileUploadLogger->getUploadSuccessRates($period),
            'performance_metrics' => $this->getPerformanceMetrics($period),
            'resource_usage' => $this->getResourceUsageMetrics($period),
            'failure_analysis' => $fileUploadLogger->getCommonFailurePatterns($period),
            'active_alerts' => $this->getActiveAlerts(),
            'system_health' => $this->getSystemHealthStatus(),
            'recommendations' => $this->getPerformanceRecommendations($period),
        ];
    }
    
    /**
     * Get performance metrics for a specific period.
     * 
     * @param string $period Time period
     * @return array Performance metrics
     */
    public function getPerformanceMetrics(string $period): array
    {
        return [
            'average_upload_duration' => [
                'value' => cache()->get("avg_duration_{$period}", 0),
                'formatted' => $this->formatDuration(cache()->get("avg_duration_{$period}", 0)),
                'threshold' => 30, // seconds
                'status' => $this->getMetricStatus(cache()->get("avg_duration_{$period}", 0), 30, 'duration'),
            ],
            'average_memory_usage' => [
                'value' => cache()->get("avg_memory_{$period}", 0),
                'formatted' => $this->formatBytes(cache()->get("avg_memory_{$period}", 0)),
                'threshold' => 50 * 1024 * 1024, // 50MB
                'status' => $this->getMetricStatus(cache()->get("avg_memory_{$period}", 0), 50 * 1024 * 1024, 'memory'),
            ],
            'average_upload_speed' => [
                'value' => cache()->get("avg_speed_{$period}", 0),
                'formatted' => $this->formatBytes(cache()->get("avg_speed_{$period}", 0)) . '/s',
                'threshold' => 100 * 1024, // 100KB/s
                'status' => $this->getMetricStatus(cache()->get("avg_speed_{$period}", 0), 100 * 1024, 'speed'),
            ],
            'average_file_size' => [
                'value' => cache()->get("avg_file_size_{$period}", 0),
                'formatted' => $this->formatBytes(cache()->get("avg_file_size_{$period}", 0)),
                'threshold' => null, // No threshold for file size
                'status' => 'info',
            ],
            'peak_concurrent_uploads' => [
                'value' => cache()->get("peak_concurrent_{$period}", 0),
                'formatted' => cache()->get("peak_concurrent_{$period}", 0) . ' uploads',
                'threshold' => 10,
                'status' => $this->getMetricStatus(cache()->get("peak_concurrent_{$period}", 0), 10, 'concurrent'),
            ],
        ];
    }
    
    /**
     * Get resource usage metrics.
     * 
     * @param string $period Time period
     * @return array Resource usage metrics
     */
    public function getResourceUsageMetrics(string $period): array
    {
        $uploadPath = storage_path('app/public');
        $tempDir = sys_get_temp_dir();
        
        return [
            'disk_usage' => [
                'upload_directory' => [
                    'free_space' => disk_free_space($uploadPath),
                    'free_space_formatted' => $this->formatBytes(disk_free_space($uploadPath) ?: 0),
                    'total_space' => disk_total_space($uploadPath),
                    'total_space_formatted' => $this->formatBytes(disk_total_space($uploadPath) ?: 0),
                    'usage_percentage' => $this->calculateDiskUsagePercentage($uploadPath),
                    'status' => $this->getDiskUsageStatus($uploadPath),
                ],
                'temp_directory' => [
                    'free_space' => disk_free_space($tempDir),
                    'free_space_formatted' => $this->formatBytes(disk_free_space($tempDir) ?: 0),
                    'total_space' => disk_total_space($tempDir),
                    'total_space_formatted' => $this->formatBytes(disk_total_space($tempDir) ?: 0),
                    'usage_percentage' => $this->calculateDiskUsagePercentage($tempDir),
                    'status' => $this->getDiskUsageStatus($tempDir),
                ],
            ],
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'current_formatted' => $this->formatBytes(memory_get_usage(true)),
                'peak' => memory_get_peak_usage(true),
                'peak_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
                'limit' => $this->convertToBytes(ini_get('memory_limit')),
                'limit_formatted' => ini_get('memory_limit'),
                'usage_percentage' => $this->calculateMemoryUsagePercentage(),
                'status' => $this->getMemoryUsageStatus(),
            ],
            'server_load' => $this->getServerLoadMetrics(),
        ];
    }
    
    /**
     * Get active performance alerts.
     * 
     * @return array Active alerts
     */
    public function getActiveAlerts(): array
    {
        $currentHour = now()->format('Y-m-d-H');
        $alerts = cache()->get("performance_alerts_{$currentHour}", []);
        
        // Get alerts from the last 24 hours
        $allAlerts = [];
        for ($i = 0; $i < 24; $i++) {
            $hourKey = now()->subHours($i)->format('Y-m-d-H');
            $hourAlerts = cache()->get("performance_alerts_{$hourKey}", []);
            $allAlerts = array_merge($allAlerts, $hourAlerts);
        }
        
        // Sort by timestamp (most recent first)
        usort($allAlerts, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return [
            'total_alerts' => count($allAlerts),
            'critical_alerts' => count(array_filter($allAlerts, fn($alert) => $alert['max_severity'] === 'critical')),
            'warning_alerts' => count(array_filter($allAlerts, fn($alert) => $alert['max_severity'] === 'warning')),
            'recent_alerts' => array_slice($allAlerts, 0, 10), // Last 10 alerts
            'alert_trends' => $this->getAlertTrends($allAlerts),
        ];
    }
    
    /**
     * Get system health status.
     * 
     * @return array System health status
     */
    public function getSystemHealthStatus(): array
    {
        $uploadPath = storage_path('app/public');
        $tempDir = sys_get_temp_dir();
        
        $healthChecks = [
            'disk_space' => [
                'status' => $this->getDiskUsageStatus($uploadPath),
                'message' => $this->getDiskUsageMessage($uploadPath),
            ],
            'temp_directory' => [
                'status' => $this->getDiskUsageStatus($tempDir),
                'message' => $this->getDiskUsageMessage($tempDir),
            ],
            'memory_usage' => [
                'status' => $this->getMemoryUsageStatus(),
                'message' => $this->getMemoryUsageMessage(),
            ],
            'upload_success_rate' => [
                'status' => $this->getSuccessRateStatus(),
                'message' => $this->getSuccessRateMessage(),
            ],
            'php_configuration' => [
                'status' => $this->getPhpConfigStatus(),
                'message' => $this->getPhpConfigMessage(),
            ],
        ];
        
        $overallStatus = $this->calculateOverallHealthStatus($healthChecks);
        
        return [
            'overall_status' => $overallStatus,
            'health_checks' => $healthChecks,
            'last_updated' => now()->toISOString(),
        ];
    }
    
    /**
     * Get performance recommendations based on current metrics.
     * 
     * @param string $period Time period for analysis
     * @return array Performance recommendations
     */
    public function getPerformanceRecommendations(string $period): array
    {
        $recommendations = [];
        $metrics = $this->getPerformanceMetrics($period);
        $resourceUsage = $this->getResourceUsageMetrics($period);
        $successRates = (new FileUploadLogger())->getUploadSuccessRates($period);
        
        // Check upload duration
        if ($metrics['average_upload_duration']['status'] === 'warning' || 
            $metrics['average_upload_duration']['status'] === 'critical') {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'high',
                'title' => 'Slow Upload Performance',
                'description' => 'Average upload duration exceeds recommended thresholds',
                'actions' => [
                    'Increase server memory limits',
                    'Optimize file processing algorithms',
                    'Consider implementing upload chunking for large files',
                    'Review network connectivity and bandwidth',
                ],
            ];
        }
        
        // Check memory usage
        if ($metrics['average_memory_usage']['status'] === 'warning' || 
            $metrics['average_memory_usage']['status'] === 'critical') {
            $recommendations[] = [
                'type' => 'resource',
                'priority' => 'high',
                'title' => 'High Memory Usage',
                'description' => 'Upload operations are consuming excessive memory',
                'actions' => [
                    'Increase PHP memory_limit setting',
                    'Optimize file processing to use streaming',
                    'Implement file size limits to prevent large uploads',
                    'Review and optimize image processing libraries',
                ],
            ];
        }
        
        // Check disk space
        if ($resourceUsage['disk_usage']['upload_directory']['status'] === 'warning' || 
            $resourceUsage['disk_usage']['upload_directory']['status'] === 'critical') {
            $recommendations[] = [
                'type' => 'storage',
                'priority' => 'critical',
                'title' => 'Low Disk Space',
                'description' => 'Upload directory is running low on available space',
                'actions' => [
                    'Clean up old or unnecessary files',
                    'Implement automated file cleanup policies',
                    'Consider moving to cloud storage',
                    'Add disk space monitoring alerts',
                ],
            ];
        }
        
        // Check success rate
        if ($successRates['success_rate_percentage'] < 90) {
            $recommendations[] = [
                'type' => 'reliability',
                'priority' => 'high',
                'title' => 'Low Upload Success Rate',
                'description' => "Upload success rate of {$successRates['success_rate_percentage']}% is below optimal",
                'actions' => [
                    'Review common failure patterns',
                    'Improve client-side validation',
                    'Enhance error handling and retry mechanisms',
                    'Update user documentation and guidance',
                ],
            ];
        }
        
        // Check upload speed
        if ($metrics['average_upload_speed']['status'] === 'warning' || 
            $metrics['average_upload_speed']['status'] === 'critical') {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'medium',
                'title' => 'Slow Upload Speed',
                'description' => 'Upload speeds are below optimal thresholds',
                'actions' => [
                    'Check network connectivity and bandwidth',
                    'Optimize server I/O performance',
                    'Consider implementing upload progress indicators',
                    'Review storage backend performance',
                ],
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Generate performance report for a specific period.
     * 
     * @param string $period Time period
     * @return array Performance report
     */
    public function generatePerformanceReport(string $period): array
    {
        $dashboardData = $this->getDashboardData($period);
        
        return [
            'report_id' => uniqid('perf_report_'),
            'generated_at' => now()->toISOString(),
            'period' => $period,
            'summary' => [
                'total_uploads' => $dashboardData['success_rates']['total_uploads'],
                'success_rate' => $dashboardData['success_rates']['success_rate_percentage'],
                'average_duration' => $dashboardData['performance_metrics']['average_upload_duration']['formatted'],
                'total_alerts' => $dashboardData['active_alerts']['total_alerts'],
                'system_health' => $dashboardData['system_health']['overall_status'],
            ],
            'detailed_metrics' => $dashboardData['performance_metrics'],
            'resource_usage' => $dashboardData['resource_usage'],
            'failure_analysis' => $dashboardData['failure_analysis'],
            'recommendations' => $dashboardData['recommendations'],
            'trends' => $this->getPerformanceTrends($period),
        ];
    }
    
    /**
     * Get performance trends over time.
     * 
     * @param string $period Time period
     * @return array Performance trends
     */
    private function getPerformanceTrends(string $period): array
    {
        // This would typically query historical data
        // For now, return basic trend information
        return [
            'upload_volume_trend' => 'stable',
            'performance_trend' => 'improving',
            'failure_rate_trend' => 'decreasing',
            'resource_usage_trend' => 'stable',
        ];
    }
    
    /**
     * Get alert trends from alert data.
     * 
     * @param array $alerts Alert data
     * @return array Alert trends
     */
    private function getAlertTrends(array $alerts): array
    {
        $hourlyAlerts = [];
        
        foreach ($alerts as $alert) {
            $hour = date('H', strtotime($alert['timestamp']));
            if (!isset($hourlyAlerts[$hour])) {
                $hourlyAlerts[$hour] = 0;
            }
            $hourlyAlerts[$hour]++;
        }
        
        return [
            'hourly_distribution' => $hourlyAlerts,
            'peak_alert_hour' => !empty($hourlyAlerts) ? array_keys($hourlyAlerts, max($hourlyAlerts))[0] : null,
            'total_hours_with_alerts' => count($hourlyAlerts),
        ];
    }
    
    /**
     * Get metric status based on value and threshold.
     * 
     * @param float $value Current value
     * @param float $threshold Threshold value
     * @param string $type Metric type
     * @return string Status
     */
    private function getMetricStatus(float $value, float $threshold, string $type): string
    {
        if ($value == 0) return 'info';
        
        switch ($type) {
            case 'duration':
            case 'memory':
                if ($value > $threshold * 2) return 'critical';
                if ($value > $threshold) return 'warning';
                return 'good';
                
            case 'speed':
                if ($value < $threshold / 2) return 'critical';
                if ($value < $threshold) return 'warning';
                return 'good';
                
            case 'concurrent':
                if ($value > $threshold * 2) return 'critical';
                if ($value > $threshold) return 'warning';
                return 'good';
                
            default:
                return 'info';
        }
    }
    
    /**
     * Calculate disk usage percentage.
     * 
     * @param string $path Directory path
     * @return float Usage percentage
     */
    private function calculateDiskUsagePercentage(string $path): float
    {
        $freeSpace = disk_free_space($path);
        $totalSpace = disk_total_space($path);
        
        if (!$freeSpace || !$totalSpace) return 0;
        
        return round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
    }
    
    /**
     * Get disk usage status.
     * 
     * @param string $path Directory path
     * @return string Status
     */
    private function getDiskUsageStatus(string $path): string
    {
        $usagePercentage = $this->calculateDiskUsagePercentage($path);
        
        if ($usagePercentage > 90) return 'critical';
        if ($usagePercentage > 80) return 'warning';
        return 'good';
    }
    
    /**
     * Get disk usage message.
     * 
     * @param string $path Directory path
     * @return string Message
     */
    private function getDiskUsageMessage(string $path): string
    {
        $usagePercentage = $this->calculateDiskUsagePercentage($path);
        $freeSpace = $this->formatBytes(disk_free_space($path) ?: 0);
        
        return "Disk usage: {$usagePercentage}% ({$freeSpace} free)";
    }
    
    /**
     * Calculate memory usage percentage.
     * 
     * @return float Usage percentage
     */
    private function calculateMemoryUsagePercentage(): float
    {
        $currentUsage = memory_get_usage(true);
        $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
        
        if ($memoryLimit <= 0) return 0;
        
        return round(($currentUsage / $memoryLimit) * 100, 2);
    }
    
    /**
     * Get memory usage status.
     * 
     * @return string Status
     */
    private function getMemoryUsageStatus(): string
    {
        $usagePercentage = $this->calculateMemoryUsagePercentage();
        
        if ($usagePercentage > 90) return 'critical';
        if ($usagePercentage > 80) return 'warning';
        return 'good';
    }
    
    /**
     * Get memory usage message.
     * 
     * @return string Message
     */
    private function getMemoryUsageMessage(): string
    {
        $usagePercentage = $this->calculateMemoryUsagePercentage();
        $currentUsage = $this->formatBytes(memory_get_usage(true));
        $memoryLimit = ini_get('memory_limit');
        
        return "Memory usage: {$usagePercentage}% ({$currentUsage} of {$memoryLimit})";
    }
    
    /**
     * Get success rate status.
     * 
     * @return string Status
     */
    private function getSuccessRateStatus(): string
    {
        $successRates = (new FileUploadLogger())->getUploadSuccessRates('day');
        $successRate = $successRates['success_rate_percentage'];
        
        if ($successRate < 80) return 'critical';
        if ($successRate < 90) return 'warning';
        return 'good';
    }
    
    /**
     * Get success rate message.
     * 
     * @return string Message
     */
    private function getSuccessRateMessage(): string
    {
        $successRates = (new FileUploadLogger())->getUploadSuccessRates('day');
        $successRate = $successRates['success_rate_percentage'];
        
        return "Upload success rate: {$successRate}% (last 24 hours)";
    }
    
    /**
     * Get PHP configuration status.
     * 
     * @return string Status
     */
    private function getPhpConfigStatus(): string
    {
        $uploadMaxFilesize = $this->convertToBytes(ini_get('upload_max_filesize'));
        $postMaxSize = $this->convertToBytes(ini_get('post_max_size'));
        $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
        
        // Check for common configuration issues
        if (!ini_get('file_uploads')) return 'critical';
        if ($postMaxSize < $uploadMaxFilesize) return 'warning';
        if ($memoryLimit > 0 && $memoryLimit < $uploadMaxFilesize * 2) return 'warning';
        
        return 'good';
    }
    
    /**
     * Get PHP configuration message.
     * 
     * @return string Message
     */
    private function getPhpConfigMessage(): string
    {
        $status = $this->getPhpConfigStatus();
        
        switch ($status) {
            case 'critical':
                return 'File uploads are disabled in PHP configuration';
            case 'warning':
                return 'PHP configuration may limit upload performance';
            default:
                return 'PHP configuration is optimal for file uploads';
        }
    }
    
    /**
     * Calculate overall health status.
     * 
     * @param array $healthChecks Health check results
     * @return string Overall status
     */
    private function calculateOverallHealthStatus(array $healthChecks): string
    {
        $statuses = array_column($healthChecks, 'status');
        
        if (in_array('critical', $statuses)) return 'critical';
        if (in_array('warning', $statuses)) return 'warning';
        return 'good';
    }
    
    /**
     * Get server load metrics.
     * 
     * @return array Server load metrics
     */
    private function getServerLoadMetrics(): array
    {
        $loadAvg = null;
        if (function_exists('sys_getloadavg')) {
            $loadAvg = sys_getloadavg();
        }
        
        return [
            'load_average' => $loadAvg,
            'cpu_count' => $this->getCpuCount(),
            'uptime' => $this->getSystemUptime(),
        ];
    }
    
    /**
     * Convert PHP ini size values to bytes.
     * 
     * @param string $size Size string
     * @return int Size in bytes
     */
    private function convertToBytes(string $size): int
    {
        if (empty($size) || $size === '-1') {
            return -1;
        }
        
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
                // fall through
            case 'm':
                $size *= 1024;
                // fall through
            case 'k':
                $size *= 1024;
        }
        
        return $size;
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
     * Format duration in human-readable format.
     * 
     * @param float $seconds Duration in seconds
     * @return string Formatted duration
     */
    private function formatDuration(float $seconds): string
    {
        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        } elseif ($seconds < 60) {
            return round($seconds, 2) . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . round($remainingSeconds) . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }
    
    /**
     * Get CPU count.
     * 
     * @return int|null Number of CPUs
     */
    private function getCpuCount(): ?int
    {
        if (function_exists('shell_exec')) {
            $cpuCount = shell_exec('nproc 2>/dev/null || echo "unknown"');
            return is_numeric(trim($cpuCount)) ? (int) trim($cpuCount) : null;
        }
        
        return null;
    }
    
    /**
     * Get system uptime.
     * 
     * @return string|null System uptime
     */
    private function getSystemUptime(): ?string
    {
        if (function_exists('shell_exec')) {
            $uptime = shell_exec('uptime 2>/dev/null');
            return $uptime ? trim($uptime) : null;
        }
        
        return null;
    }
}