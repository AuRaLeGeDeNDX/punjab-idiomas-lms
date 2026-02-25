<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UploadPerformanceMonitor;
use App\Services\AlertManagementService;
use App\Services\FileUploadLogger;
use Illuminate\Support\Facades\Log;

/**
 * MonitorUploads command provides automated monitoring and alerting for file uploads.
 * 
 * This command runs periodically to check upload performance metrics,
 * generate alerts for threshold violations, and maintain monitoring data.
 * 
 * Requirements: 4.4
 */
class MonitorUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:monitor 
                            {--period=hour : Time period to analyze (hour, day, week, month)}
                            {--cleanup : Clean up old monitoring data}
                            {--alerts-only : Only check for alerts, skip other monitoring}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor file upload performance and generate alerts for issues';

    private UploadPerformanceMonitor $performanceMonitor;
    private AlertManagementService $alertService;
    private FileUploadLogger $uploadLogger;
    private array $config;

    /**
     * Create a new command instance.
     */
    public function __construct(
        UploadPerformanceMonitor $performanceMonitor,
        AlertManagementService $alertService,
        FileUploadLogger $uploadLogger
    ) {
        parent::__construct();
        
        $this->performanceMonitor = $performanceMonitor;
        $this->alertService = $alertService;
        $this->uploadLogger = $uploadLogger;
        $this->config = config('monitoring', []);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting upload monitoring...');
        
        try {
            if ($this->option('cleanup')) {
                return $this->handleCleanup();
            }
            
            if ($this->option('alerts-only')) {
                return $this->handleAlertsOnly();
            }
            
            return $this->handleFullMonitoring();
            
        } catch (\Exception $e) {
            $this->error('Monitoring failed: ' . $e->getMessage());
            Log::error('Upload monitoring command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'options' => $this->options(),
            ]);
            
            return 1;
        }
    }

    /**
     * Handle full monitoring process.
     * 
     * @return int Exit code
     */
    private function handleFullMonitoring(): int
    {
        $period = $this->option('period');
        $dryRun = $this->option('dry-run');
        
        $this->info("Analyzing upload performance for period: {$period}");
        
        // Get dashboard data
        $dashboardData = $this->performanceMonitor->getDashboardData($period);
        
        // Display current status
        $this->displaySystemStatus($dashboardData);
        
        // Check for performance issues and generate alerts
        $alerts = $this->checkPerformanceThresholds($dashboardData, $dryRun);
        
        // Check resource usage
        $resourceAlerts = $this->checkResourceUsage($dashboardData, $dryRun);
        $alerts = array_merge($alerts, $resourceAlerts);
        
        // Check failure rates
        $failureAlerts = $this->checkFailureRates($dashboardData, $dryRun);
        $alerts = array_merge($alerts, $failureAlerts);
        
        // Display results
        $this->displayResults($alerts, $dryRun);
        
        // Generate recommendations
        $this->displayRecommendations($dashboardData['recommendations'] ?? []);
        
        return 0;
    }

    /**
     * Handle alerts-only monitoring.
     * 
     * @return int Exit code
     */
    private function handleAlertsOnly(): int
    {
        $period = $this->option('period');
        $dryRun = $this->option('dry-run');
        
        $this->info("Checking for alerts (period: {$period})");
        
        // Get current metrics
        $performanceMetrics = $this->performanceMonitor->getPerformanceMetrics($period);
        $resourceMetrics = $this->performanceMonitor->getResourceUsageMetrics($period);
        $successRates = $this->uploadLogger->getUploadSuccessRates($period);
        
        $alerts = [];
        
        // Check performance thresholds
        foreach ($performanceMetrics as $metric => $data) {
            $alert = $this->checkMetricThreshold($metric, $data, $dryRun);
            if ($alert) {
                $alerts[] = $alert;
            }
        }
        
        // Check resource thresholds
        $resourceAlerts = $this->alertService->createResourceAlerts($resourceMetrics);
        if (!$dryRun) {
            $alerts = array_merge($alerts, $resourceAlerts);
        }
        
        // Check failure rate
        $failureRate = 100 - $successRates['success_rate_percentage'];
        $failureAlert = $this->alertService->createFailureRateAlert($failureRate, $period);
        if ($failureAlert && !$dryRun) {
            $alerts[] = $failureAlert;
        }
        
        $this->displayResults($alerts, $dryRun);
        
        return 0;
    }

    /**
     * Handle cleanup of old monitoring data.
     * 
     * @return int Exit code
     */
    private function handleCleanup(): int
    {
        $dryRun = $this->option('dry-run');
        $retentionDays = $this->config['retention']['log_retention_days'] ?? 30;
        
        $this->info("Cleaning up monitoring data older than {$retentionDays} days");
        
        if ($dryRun) {
            $this->warn('DRY RUN: Would clean up old alerts and monitoring data');
            return 0;
        }
        
        // Clean up old alerts
        $this->alertService->cleanupOldAlerts($retentionDays);
        
        $this->info('Cleanup completed successfully');
        
        return 0;
    }

    /**
     * Display current system status.
     * 
     * @param array $dashboardData Dashboard data
     */
    private function displaySystemStatus(array $dashboardData): void
    {
        $this->info('=== System Status ===');
        
        $systemHealth = $dashboardData['system_health']['overall_status'] ?? 'unknown';
        $statusColor = $this->getStatusColor($systemHealth);
        
        $this->line("System Health: <{$statusColor}>" . ucfirst($systemHealth) . "</{$statusColor}>");
        
        // Display key metrics
        $successRate = $dashboardData['success_rates']['success_rate_percentage'] ?? 0;
        $totalUploads = $dashboardData['success_rates']['total_uploads'] ?? 0;
        
        $this->line("Upload Success Rate: <info>{$successRate}%</info> ({$totalUploads} total uploads)");
        
        // Display performance metrics
        $avgDuration = $dashboardData['performance_metrics']['average_upload_duration']['formatted'] ?? '0s';
        $avgMemory = $dashboardData['performance_metrics']['average_memory_usage']['formatted'] ?? '0 MB';
        
        $this->line("Average Duration: <comment>{$avgDuration}</comment>");
        $this->line("Average Memory: <comment>{$avgMemory}</comment>");
        
        // Display active alerts
        $activeAlerts = $dashboardData['active_alerts']['total_alerts'] ?? 0;
        $criticalAlerts = $dashboardData['active_alerts']['critical_alerts'] ?? 0;
        
        if ($activeAlerts > 0) {
            $alertColor = $criticalAlerts > 0 ? 'error' : 'comment';
            $this->line("Active Alerts: <{$alertColor}>{$activeAlerts}</{$alertColor}> (Critical: {$criticalAlerts})");
        } else {
            $this->line("Active Alerts: <info>0</info>");
        }
        
        $this->newLine();
    }

    /**
     * Check performance thresholds and generate alerts.
     * 
     * @param array $dashboardData Dashboard data
     * @param bool $dryRun Whether this is a dry run
     * @return array Generated alerts
     */
    private function checkPerformanceThresholds(array $dashboardData, bool $dryRun): array
    {
        $alerts = [];
        $performanceMetrics = $dashboardData['performance_metrics'] ?? [];
        
        foreach ($performanceMetrics as $metric => $data) {
            $alert = $this->checkMetricThreshold($metric, $data, $dryRun);
            if ($alert) {
                $alerts[] = $alert;
            }
        }
        
        return $alerts;
    }

    /**
     * Check a specific metric threshold.
     * 
     * @param string $metric Metric name
     * @param array $data Metric data
     * @param bool $dryRun Whether this is a dry run
     * @return array|null Generated alert or null
     */
    private function checkMetricThreshold(string $metric, array $data, bool $dryRun): ?array
    {
        $status = $data['status'] ?? 'info';
        $value = $data['value'] ?? 0;
        $formatted = $data['formatted'] ?? $value;
        $threshold = $data['threshold'] ?? null;
        
        if ($status === 'warning' || $status === 'critical') {
            $message = "Performance threshold exceeded for {$metric}: {$formatted}";
            if ($threshold) {
                $message .= " (threshold: {$threshold})";
            }
            
            if ($dryRun) {
                $this->warn("WOULD CREATE ALERT: {$message}");
                return null;
            }
            
            return $this->alertService->createAlert(
                $metric,
                $status,
                $message,
                [
                    'metric' => $metric,
                    'current_value' => $value,
                    'formatted_value' => $formatted,
                    'threshold' => $threshold,
                    'status' => $status,
                ]
            );
        }
        
        return null;
    }

    /**
     * Check resource usage and generate alerts.
     * 
     * @param array $dashboardData Dashboard data
     * @param bool $dryRun Whether this is a dry run
     * @return array Generated alerts
     */
    private function checkResourceUsage(array $dashboardData, bool $dryRun): array
    {
        $resourceMetrics = $dashboardData['resource_usage'] ?? [];
        
        if ($dryRun) {
            $this->info('Would check resource usage thresholds...');
            return [];
        }
        
        return $this->alertService->createResourceAlerts($resourceMetrics);
    }

    /**
     * Check failure rates and generate alerts.
     * 
     * @param array $dashboardData Dashboard data
     * @param bool $dryRun Whether this is a dry run
     * @return array Generated alerts
     */
    private function checkFailureRates(array $dashboardData, bool $dryRun): array
    {
        $successRates = $dashboardData['success_rates'] ?? [];
        $failureRate = $successRates['failure_rate_percentage'] ?? 0;
        $period = $this->option('period');
        
        if ($dryRun) {
            if ($failureRate > 10) {
                $this->warn("WOULD CREATE ALERT: High failure rate: {$failureRate}%");
            }
            return [];
        }
        
        $alert = $this->alertService->createFailureRateAlert($failureRate, $period);
        return $alert ? [$alert] : [];
    }

    /**
     * Display monitoring results.
     * 
     * @param array $alerts Generated alerts
     * @param bool $dryRun Whether this is a dry run
     */
    private function displayResults(array $alerts, bool $dryRun): void
    {
        $this->info('=== Monitoring Results ===');
        
        if (empty($alerts)) {
            $this->info('No alerts generated - system is performing well');
            return;
        }
        
        $this->warn("Generated {count($alerts)} alert(s):");
        
        foreach ($alerts as $alert) {
            $severity = $alert['severity'] ?? 'info';
            $type = $alert['type'] ?? 'unknown';
            $message = $alert['message'] ?? 'No message';
            
            $color = $this->getAlertColor($severity);
            $this->line("  [{$severity}] {$type}: <{$color}>{$message}</{$color}>");
        }
        
        if ($dryRun) {
            $this->newLine();
            $this->comment('This was a dry run - no alerts were actually created');
        }
        
        $this->newLine();
    }

    /**
     * Display performance recommendations.
     * 
     * @param array $recommendations Performance recommendations
     */
    private function displayRecommendations(array $recommendations): void
    {
        if (empty($recommendations)) {
            return;
        }
        
        $this->info('=== Performance Recommendations ===');
        
        foreach ($recommendations as $recommendation) {
            $title = $recommendation['title'] ?? 'Recommendation';
            $priority = $recommendation['priority'] ?? 'medium';
            $description = $recommendation['description'] ?? '';
            
            $priorityColor = $priority === 'critical' ? 'error' : ($priority === 'high' ? 'comment' : 'info');
            
            $this->line("<{$priorityColor}>[{$priority}]</{$priorityColor}> {$title}");
            if ($description) {
                $this->line("  {$description}");
            }
            
            if (isset($recommendation['actions']) && is_array($recommendation['actions'])) {
                $this->line('  Actions:');
                foreach ($recommendation['actions'] as $action) {
                    $this->line("    - {$action}");
                }
            }
            
            $this->newLine();
        }
    }

    /**
     * Get color for status display.
     * 
     * @param string $status Status value
     * @return string Color name
     */
    private function getStatusColor(string $status): string
    {
        switch ($status) {
            case 'good':
                return 'info';
            case 'warning':
                return 'comment';
            case 'critical':
                return 'error';
            default:
                return 'comment';
        }
    }

    /**
     * Get color for alert display.
     * 
     * @param string $severity Alert severity
     * @return string Color name
     */
    private function getAlertColor(string $severity): string
    {
        switch ($severity) {
            case 'critical':
                return 'error';
            case 'warning':
                return 'comment';
            case 'info':
                return 'info';
            default:
                return 'comment';
        }
    }
}