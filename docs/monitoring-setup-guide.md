# Upload Monitoring and Alerting Setup Guide

This guide provides comprehensive instructions for setting up and configuring the file upload monitoring and alerting system.

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Dashboard Setup](#dashboard-setup)
5. [Alert Configuration](#alert-configuration)
6. [Automated Monitoring](#automated-monitoring)
7. [Integration with External Tools](#integration-with-external-tools)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)

## Overview

The upload monitoring system provides:

- **Real-time Dashboard**: Visual monitoring of upload performance and system health
- **Automated Alerting**: Threshold-based alerts for performance issues
- **Performance Metrics**: Comprehensive tracking of upload success rates, duration, memory usage
- **Resource Monitoring**: Disk space, memory, and server resource tracking
- **Historical Analysis**: Trend analysis and performance reporting
- **Export Capabilities**: Data export in PDF, CSV, and JSON formats

## Installation

### 1. Enable Monitoring

Add the following to your `.env` file:

```env
# Enable upload monitoring
UPLOAD_MONITORING_ENABLED=true
UPLOAD_ALERTS_ENABLED=true
MONITORING_DASHBOARD_ENABLED=true

# Dashboard settings
DASHBOARD_REFRESH_INTERVAL=30
DASHBOARD_AUTO_REFRESH=true
DASHBOARD_DEFAULT_PERIOD=day
```

### 2. Configure Thresholds

Set performance thresholds in your `.env`:

```env
# Performance thresholds
UPLOAD_DURATION_WARNING_THRESHOLD=30
UPLOAD_DURATION_CRITICAL_THRESHOLD=60
UPLOAD_MEMORY_WARNING_THRESHOLD=52428800
UPLOAD_MEMORY_CRITICAL_THRESHOLD=104857600
UPLOAD_SPEED_WARNING_THRESHOLD=102400
UPLOAD_SPEED_CRITICAL_THRESHOLD=51200
UPLOAD_FAILURE_RATE_WARNING=10
UPLOAD_FAILURE_RATE_CRITICAL=20
DISK_USAGE_WARNING_THRESHOLD=80
DISK_USAGE_CRITICAL_THRESHOLD=90
MEMORY_LIMIT_WARNING_THRESHOLD=80
MEMORY_LIMIT_CRITICAL_THRESHOLD=90
```

### 3. Add Routes

Include the monitoring routes in your `routes/web.php`:

```php
// Include monitoring routes
require __DIR__.'/monitoring.php';
```

### 4. Set Up Permissions

Add the required permissions to your authorization system:

```php
// In your AuthServiceProvider or permission seeder
Gate::define('view-monitoring-dashboard', function ($user) {
    return $user->hasRole(['admin', 'teacher']);
});

Gate::define('manage-monitoring-system', function ($user) {
    return $user->hasRole('admin');
});
```

## Configuration

### Monitoring Configuration

The monitoring system is configured via `config/monitoring.php`. Key sections include:

#### Performance Thresholds

```php
'thresholds' => [
    'upload_duration' => [
        'warning' => 30,  // seconds
        'critical' => 60, // seconds
    ],
    'memory_usage' => [
        'warning' => 50 * 1024 * 1024,  // 50MB
        'critical' => 100 * 1024 * 1024, // 100MB
    ],
    // ... more thresholds
],
```

#### Alert Configuration

```php
'alerts' => [
    'enabled' => true,
    'channels' => [
        'log' => true,
        'email' => false,
        'slack' => false,
        'webhook' => false,
    ],
    'rate_limiting' => [
        'max_alerts_per_hour' => 10,
        'cooldown_minutes' => 15,
    ],
],
```

#### Dashboard Settings

```php
'dashboard' => [
    'enabled' => true,
    'refresh_interval' => 30, // seconds
    'auto_refresh' => true,
    'default_period' => 'day',
    'available_periods' => ['hour', 'day', 'week', 'month'],
],
```

## Dashboard Setup

### 1. Access the Dashboard

Navigate to `/monitoring` in your application to access the main dashboard.

### 2. Dashboard Features

The dashboard includes:

- **System Health Status**: Overall system health indicator
- **Upload Statistics**: Success rates and volume metrics
- **Performance Metrics**: Duration, memory usage, and speed trends
- **Active Alerts**: Current alerts and their severity
- **Resource Usage**: Disk space and memory utilization
- **Failure Patterns**: Analysis of common failure types
- **Recommendations**: Automated performance improvement suggestions

### 3. Dashboard Customization

Customize dashboard widgets by modifying the `MonitoringDashboardService`:

```php
// Add custom widgets
'custom_widget' => [
    'type' => 'custom_chart',
    'title' => 'Custom Metrics',
    'data' => $this->getCustomMetrics(),
    'config' => [
        'chart_type' => 'line',
        'show_legend' => true,
    ],
],
```

## Alert Configuration

### 1. Email Alerts

Configure email notifications:

```env
ALERT_EMAIL_ENABLED=true
ALERT_EMAIL_RECIPIENTS=admin@example.com,ops@example.com
```

### 2. Slack Alerts

Set up Slack webhook integration:

```env
ALERT_SLACK_ENABLED=true
ALERT_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
```

### 3. Custom Webhook Alerts

Configure custom webhook notifications:

```env
ALERT_WEBHOOK_ENABLED=true
ALERT_WEBHOOK_URL=https://your-monitoring-system.com/webhook
ALERT_WEBHOOK_SECRET=your-webhook-secret
```

### 4. Alert Rate Limiting

Prevent alert spam:

```env
MAX_ALERTS_PER_HOUR=10
ALERT_COOLDOWN_MINUTES=15
```

## Automated Monitoring

### 1. Schedule Monitoring Command

Add to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Run monitoring every 5 minutes
    $schedule->command('uploads:monitor --period=hour')
             ->everyFiveMinutes()
             ->withoutOverlapping();
    
    // Generate daily reports
    $schedule->command('uploads:monitor --period=day')
             ->dailyAt('08:00');
    
    // Weekly cleanup
    $schedule->command('uploads:monitor --cleanup')
             ->weekly()
             ->sundays()
             ->at('02:00');
}
```

### 2. Manual Monitoring Commands

Run monitoring manually:

```bash
# Check current status
php artisan uploads:monitor --period=hour

# Generate alerts only
php artisan uploads:monitor --alerts-only --period=day

# Dry run (show what would happen)
php artisan uploads:monitor --dry-run --period=day

# Clean up old data
php artisan uploads:monitor --cleanup
```

## Integration with External Tools

### 1. Prometheus Integration

Enable Prometheus metrics export:

```env
PROMETHEUS_INTEGRATION_ENABLED=true
PROMETHEUS_ENDPOINT=/metrics
PROMETHEUS_NAMESPACE=upload_monitoring
```

### 2. Grafana Integration

Configure Grafana dashboard:

```env
GRAFANA_INTEGRATION_ENABLED=true
GRAFANA_DASHBOARD_URL=https://your-grafana.com/d/upload-monitoring
```

### 3. Custom Monitoring Tools

Integrate with custom monitoring systems using webhooks:

```env
CUSTOM_WEBHOOK_ENABLED=true
CUSTOM_WEBHOOK_URL=https://your-monitoring.com/api/metrics
CUSTOM_WEBHOOK_SECRET=your-secret-key
```

## Troubleshooting

### Common Issues

#### 1. Dashboard Not Loading

**Problem**: Dashboard shows errors or doesn't load data.

**Solutions**:
- Check that monitoring is enabled: `UPLOAD_MONITORING_ENABLED=true`
- Verify user permissions: `can:view-monitoring-dashboard`
- Check logs for errors: `tail -f storage/logs/laravel.log`
- Clear cache: `php artisan cache:clear`

#### 2. Alerts Not Being Sent

**Problem**: Alerts are generated but not sent via email/Slack.

**Solutions**:
- Verify alert channels are enabled in config
- Check email/Slack configuration
- Test connectivity to external services
- Review rate limiting settings

#### 3. Performance Issues

**Problem**: Monitoring system impacts application performance.

**Solutions**:
- Enable caching: `CACHE_DASHBOARD_DATA=true`
- Use async processing: `ASYNC_ALERT_PROCESSING=true`
- Reduce monitoring frequency
- Optimize database queries

#### 4. High Memory Usage

**Problem**: Monitoring system uses too much memory.

**Solutions**:
- Enable data compression: `COMPRESS_HISTORICAL_DATA=true`
- Reduce data retention period
- Use batch processing: `BATCH_METRICS_UPDATES=true`
- Monitor memory limits

### Debug Mode

Enable debug logging for troubleshooting:

```env
LOG_LEVEL=debug
MONITORING_DEBUG=true
```

## Best Practices

### 1. Threshold Configuration

- **Start Conservative**: Begin with higher thresholds and adjust based on actual performance
- **Environment-Specific**: Use different thresholds for development, staging, and production
- **Regular Review**: Periodically review and adjust thresholds based on system changes

### 2. Alert Management

- **Avoid Alert Fatigue**: Use rate limiting and cooldown periods
- **Prioritize Alerts**: Focus on critical issues that require immediate attention
- **Clear Escalation**: Define clear escalation paths for different alert types

### 3. Dashboard Usage

- **Regular Monitoring**: Check dashboard regularly during business hours
- **Trend Analysis**: Use historical data to identify patterns and trends
- **Proactive Response**: Address warnings before they become critical issues

### 4. Performance Optimization

- **Cache Frequently**: Cache dashboard data and metrics
- **Batch Operations**: Process metrics in batches to reduce overhead
- **Async Processing**: Use queues for non-critical monitoring tasks

### 5. Data Retention

- **Balance Storage vs. History**: Keep enough data for trend analysis without excessive storage
- **Regular Cleanup**: Automate cleanup of old monitoring data
- **Archive Important Data**: Archive critical performance data for long-term analysis

### 6. Security Considerations

- **Access Control**: Restrict dashboard access to authorized users only
- **Secure Webhooks**: Use HTTPS and signature verification for webhooks
- **Log Protection**: Protect monitoring logs from unauthorized access

## Monitoring Checklist

### Daily Tasks
- [ ] Check dashboard for system health
- [ ] Review active alerts
- [ ] Monitor upload success rates
- [ ] Check resource usage levels

### Weekly Tasks
- [ ] Review performance trends
- [ ] Analyze failure patterns
- [ ] Update alert thresholds if needed
- [ ] Clean up resolved alerts

### Monthly Tasks
- [ ] Generate performance reports
- [ ] Review monitoring configuration
- [ ] Update documentation
- [ ] Plan capacity improvements

## Support and Maintenance

### Log Files

Monitor these log files for issues:
- `storage/logs/laravel.log` - General application logs
- `storage/logs/monitoring.log` - Monitoring-specific logs
- `storage/logs/alerts.log` - Alert notifications

### Health Checks

Regular health checks to perform:
- Dashboard accessibility
- Alert notification delivery
- Data accuracy and completeness
- System resource usage

### Updates and Maintenance

- Keep monitoring thresholds updated
- Review and update alert configurations
- Monitor system performance impact
- Update documentation as needed

For additional support or questions, refer to the application logs or contact the development team.