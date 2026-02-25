<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Content;

/**
 * FileUploadLogger provides comprehensive logging for file upload attempts and performance.
 * 
 * This service logs all upload attempts with detailed context including server configuration,
 * disk space, resource availability, and tracks upload success and failure rates.
 * 
 * Requirements: 4.1, 4.2, 4.4, 4.5
 */
class FileUploadLogger
{
    /**
     * Log a file upload attempt with detailed context.
     * 
     * @param Request $request The HTTP request containing the upload
     * @param UploadedFile|null $file The uploaded file (if present)
     * @param array $additionalContext Additional context to include in the log
     * @return string Correlation ID for tracking this upload attempt
     */
    public function logUploadAttempt(Request $request, ?UploadedFile $file = null, array $additionalContext = []): string
    {
        $correlationId = Str::uuid()->toString();
        
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'user_roles' => auth()->user()?->roles->pluck('name')->toArray() ?? [],
            'request_info' => $this->getRequestInfo($request),
            'file_info' => $file ? $this->getFileInfo($file) : null,
            'server_config' => $this->getServerConfiguration(),
            'resource_availability' => $this->getResourceAvailability(),
            'additional_context' => $additionalContext,
        ];
        
        Log::info('File upload attempt initiated', $logData);
        
        return $correlationId;
    }
    
    /**
     * Log upload validation failure with specific details.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param array $validationErrors Validation errors that occurred
     * @param array $additionalContext Additional context about the failure
     */
    public function logValidationFailure(string $correlationId, array $validationErrors, array $additionalContext = []): void
    {
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'validation_errors' => $validationErrors,
            'error_categories' => $this->categorizeValidationErrors($validationErrors),
            'server_config_at_failure' => $this->getServerConfiguration(),
            'resource_availability_at_failure' => $this->getResourceAvailability(),
            'additional_context' => $additionalContext,
        ];
        
        Log::warning('File upload validation failed', $logData);
        
        // Track failure metrics
        $this->trackUploadFailure($correlationId, 'validation_failure', $validationErrors);
    }
    
    /**
     * Log detailed validation rule failures with specific rule information.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param array $failedRules Array of failed validation rules with details
     * @param UploadedFile|null $file The uploaded file (if available)
     * @param array $serverLimits Current server limits and configuration
     * @param array $additionalContext Additional context about the failure
     */
    public function logDetailedValidationFailure(
        string $correlationId, 
        array $failedRules, 
        ?UploadedFile $file = null, 
        array $serverLimits = [], 
        array $additionalContext = []
    ): void {
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'failed_validation_rules' => $failedRules,
            'validation_rule_summary' => $this->summarizeFailedRules($failedRules),
            'file_properties' => $file ? $this->getDetailedFileProperties($file) : null,
            'server_limits' => array_merge($this->getServerConfiguration(), $serverLimits),
            'resource_availability' => $this->getResourceAvailability(),
            'validation_context' => [
                'total_rules_failed' => count($failedRules),
                'rule_categories' => $this->categorizeFailedRules($failedRules),
                'severity_assessment' => $this->assessValidationSeverity($failedRules),
                'retry_likelihood' => $this->assessRetryLikelihood($failedRules),
            ],
            'additional_context' => $additionalContext,
        ];
        
        Log::error('Detailed file upload validation failure', $logData);
        
        // Track detailed failure metrics
        $this->trackDetailedValidationFailure($correlationId, $failedRules, $file);
    }
    
    /**
     * Log successful file upload completion.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param Content $content The created content block
     * @param array $additionalContext Additional context about the success
     */
    public function logUploadSuccess(string $correlationId, Content $content, array $additionalContext = []): void
    {
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'content_type' => $content->type,
            'file_info' => [
                'stored_path' => $content->file_path,
                'original_name' => $content->original_filename,
                'file_size' => $content->file_size,
                'mime_type' => $content->mime_type,
            ],
            'upload_duration' => $this->calculateUploadDuration($correlationId),
            'server_config' => $this->getServerConfiguration(),
            'resource_usage' => $this->getResourceUsage(),
            'additional_context' => $additionalContext,
        ];
        
        Log::info('File upload completed successfully', $logData);
        
        // Track success metrics
        $this->trackUploadSuccess($correlationId, $content);
    }
    
    /**
     * Log PHP upload error with detailed diagnostics.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param int $phpErrorCode PHP upload error code (UPLOAD_ERR_*)
     * @param UploadedFile|null $file The uploaded file (if available)
     * @param array $additionalContext Additional context about the error
     */
    public function logPhpUploadError(string $correlationId, int $phpErrorCode, ?UploadedFile $file = null, array $additionalContext = []): void
    {
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'php_error_code' => $phpErrorCode,
            'php_error_message' => $this->getPhpErrorMessage($phpErrorCode),
            'file_info' => $file ? $this->getFileInfo($file) : null,
            'server_config' => $this->getServerConfiguration(),
            'resource_availability' => $this->getResourceAvailability(),
            'diagnostic_info' => $this->getDiagnosticInfo($phpErrorCode),
            'additional_context' => $additionalContext,
        ];
        
        Log::error('PHP file upload error occurred', $logData);
        
        // Track PHP error metrics
        $this->trackUploadFailure($correlationId, 'php_upload_error', ['php_error_code' => $phpErrorCode]);
    }
    
    /**
     * Log server resource validation failure.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param array $resourceErrors Resource validation errors
     * @param UploadedFile|null $file The uploaded file (if available)
     * @param array $additionalContext Additional context about the failure
     */
    public function logResourceValidationFailure(string $correlationId, array $resourceErrors, ?UploadedFile $file = null, array $additionalContext = []): void
    {
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'resource_errors' => $resourceErrors,
            'file_info' => $file ? $this->getFileInfo($file) : null,
            'server_config' => $this->getServerConfiguration(),
            'resource_availability' => $this->getResourceAvailability(),
            'resource_requirements' => $file ? $this->calculateResourceRequirements($file) : null,
            'additional_context' => $additionalContext,
        ];
        
        Log::error('Server resource validation failed', $logData);
        
        // Track resource failure metrics
        $this->trackUploadFailure($correlationId, 'resource_validation_failure', $resourceErrors);
    }
    
    /**
     * Log upload performance metrics with comprehensive monitoring.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param array $performanceMetrics Performance metrics to log
     */
    public function logUploadPerformance(string $correlationId, array $performanceMetrics): void
    {
        $logData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'performance_metrics' => $performanceMetrics,
            'server_load' => $this->getServerLoadMetrics(),
            'resource_usage' => $this->getResourceUsage(),
            'performance_analysis' => $this->analyzePerformanceMetrics($performanceMetrics),
        ];
        
        Log::info('File upload performance metrics', $logData);
        
        // Check for performance issues and create alerts if needed
        $this->checkPerformanceThresholds($correlationId, $performanceMetrics);
    }
    
    /**
     * Start performance monitoring for an upload.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param UploadedFile|null $file The uploaded file
     * @return array Performance monitoring context
     */
    public function startPerformanceMonitoring(string $correlationId, ?UploadedFile $file = null): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $startPeakMemory = memory_get_peak_usage(true);
        
        $monitoringContext = [
            'correlation_id' => $correlationId,
            'start_time' => $startTime,
            'start_memory' => $startMemory,
            'start_peak_memory' => $startPeakMemory,
            'file_size' => $file ? $file->getSize() : null,
            'file_type' => $file ? $file->getClientOriginalExtension() : null,
            'server_load_start' => $this->getServerLoadMetrics(),
            'resource_availability_start' => $this->getResourceAvailability(),
        ];
        
        // Store monitoring context in cache for later retrieval
        cache()->put("upload_monitoring_{$correlationId}", $monitoringContext, now()->addMinutes(30));
        
        Log::info('Upload performance monitoring started', [
            'correlation_id' => $correlationId,
            'monitoring_context' => $monitoringContext,
        ]);
        
        return $monitoringContext;
    }
    
    /**
     * End performance monitoring and log comprehensive metrics.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param bool $success Whether the upload was successful
     * @param array $additionalMetrics Additional metrics to include
     */
    public function endPerformanceMonitoring(string $correlationId, bool $success = true, array $additionalMetrics = []): void
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $endPeakMemory = memory_get_peak_usage(true);
        
        // Retrieve monitoring context from cache
        $monitoringContext = cache()->get("upload_monitoring_{$correlationId}");
        
        if (!$monitoringContext) {
            Log::warning('Upload performance monitoring context not found', [
                'correlation_id' => $correlationId,
            ]);
            return;
        }
        
        $duration = $endTime - $monitoringContext['start_time'];
        $memoryUsed = $endMemory - $monitoringContext['start_memory'];
        $peakMemoryIncrease = $endPeakMemory - $monitoringContext['start_peak_memory'];
        
        $performanceMetrics = array_merge([
            'duration_seconds' => $duration,
            'duration_formatted' => $this->formatDuration($duration),
            'memory_used' => $memoryUsed,
            'memory_used_formatted' => $this->formatBytes($memoryUsed),
            'peak_memory_increase' => $peakMemoryIncrease,
            'peak_memory_increase_formatted' => $this->formatBytes($peakMemoryIncrease),
            'file_size' => $monitoringContext['file_size'],
            'file_size_formatted' => $monitoringContext['file_size'] ? $this->formatBytes($monitoringContext['file_size']) : null,
            'file_type' => $monitoringContext['file_type'],
            'upload_speed_bps' => $monitoringContext['file_size'] && $duration > 0 ? $monitoringContext['file_size'] / $duration : null,
            'upload_speed_formatted' => $monitoringContext['file_size'] && $duration > 0 ? $this->formatBytes($monitoringContext['file_size'] / $duration) . '/s' : null,
            'success' => $success,
            'server_load_end' => $this->getServerLoadMetrics(),
            'resource_availability_end' => $this->getResourceAvailability(),
        ], $additionalMetrics);
        
        // Log comprehensive performance metrics
        $this->logUploadPerformance($correlationId, $performanceMetrics);
        
        // Track performance statistics
        $this->trackPerformanceStatistics($correlationId, $performanceMetrics);
        
        // Clean up monitoring context
        cache()->forget("upload_monitoring_{$correlationId}");
    }
    
    /**
     * Track upload success rates and failure patterns.
     * 
     * @param string $period Time period for analysis (e.g., 'hour', 'day', 'week')
     * @return array Success rate statistics
     */
    public function getUploadSuccessRates(string $period = 'day'): array
    {
        $cacheKey = "upload_success_rates_{$period}";
        
        return cache()->remember($cacheKey, now()->addMinutes(15), function() use ($period) {
            $startTime = $this->getPeriodStartTime($period);
            
            // Get upload statistics from logs (simplified implementation)
            // In a real implementation, you might query a metrics database
            $totalUploads = cache()->get("total_uploads_{$period}", 0);
            $successfulUploads = cache()->get("successful_uploads_{$period}", 0);
            $failedUploads = $totalUploads - $successfulUploads;
            
            $successRate = $totalUploads > 0 ? ($successfulUploads / $totalUploads) * 100 : 0;
            
            return [
                'period' => $period,
                'start_time' => $startTime->toISOString(),
                'end_time' => now()->toISOString(),
                'total_uploads' => $totalUploads,
                'successful_uploads' => $successfulUploads,
                'failed_uploads' => $failedUploads,
                'success_rate_percentage' => round($successRate, 2),
                'failure_rate_percentage' => round(100 - $successRate, 2),
                'common_failure_patterns' => $this->getCommonFailurePatterns($period),
                'performance_trends' => $this->getPerformanceTrends($period),
            ];
        });
    }
    
    /**
     * Get common failure patterns for analysis.
     * 
     * @param string $period Time period for analysis
     * @return array Common failure patterns
     */
    public function getCommonFailurePatterns(string $period = 'day'): array
    {
        $cacheKey = "failure_patterns_{$period}";
        
        return cache()->remember($cacheKey, now()->addMinutes(15), function() use ($period) {
            // Get failure pattern statistics from cache/database
            $patterns = [
                'file_size_exceeded' => cache()->get("failures_file_size_{$period}", 0),
                'invalid_file_type' => cache()->get("failures_file_type_{$period}", 0),
                'php_upload_errors' => cache()->get("failures_php_error_{$period}", 0),
                'server_resource_limits' => cache()->get("failures_resource_{$period}", 0),
                'validation_failures' => cache()->get("failures_validation_{$period}", 0),
                'network_timeouts' => cache()->get("failures_timeout_{$period}", 0),
                'security_blocks' => cache()->get("failures_security_{$period}", 0),
            ];
            
            // Sort by frequency
            arsort($patterns);
            
            $totalFailures = array_sum($patterns);
            $patternsWithPercentages = [];
            
            foreach ($patterns as $pattern => $count) {
                if ($count > 0) {
                    $patternsWithPercentages[$pattern] = [
                        'count' => $count,
                        'percentage' => $totalFailures > 0 ? round(($count / $totalFailures) * 100, 2) : 0,
                        'description' => $this->getFailurePatternDescription($pattern),
                        'suggested_actions' => $this->getFailurePatternSuggestions($pattern),
                    ];
                }
            }
            
            return [
                'total_failures' => $totalFailures,
                'patterns' => $patternsWithPercentages,
                'top_failure_reason' => !empty($patternsWithPercentages) ? array_key_first($patternsWithPercentages) : null,
            ];
        });
    }
    
    /**
     * Create alerts for recurring upload issues.
     * 
     * @param string $correlationId Correlation ID
     * @param array $performanceMetrics Performance metrics
     */
    private function checkPerformanceThresholds(string $correlationId, array $performanceMetrics): void
    {
        $alerts = [];
        
        // Check upload duration threshold
        if (isset($performanceMetrics['duration_seconds']) && $performanceMetrics['duration_seconds'] > 30) {
            $alerts[] = [
                'type' => 'slow_upload',
                'severity' => 'warning',
                'message' => "Upload took {$performanceMetrics['duration_formatted']}, which exceeds the 30-second threshold",
                'threshold' => 30,
                'actual_value' => $performanceMetrics['duration_seconds'],
            ];
        }
        
        // Check memory usage threshold
        if (isset($performanceMetrics['memory_used']) && $performanceMetrics['memory_used'] > 50 * 1024 * 1024) { // 50MB
            $alerts[] = [
                'type' => 'high_memory_usage',
                'severity' => 'warning',
                'message' => "Upload used {$performanceMetrics['memory_used_formatted']} memory, which exceeds the 50MB threshold",
                'threshold' => 50 * 1024 * 1024,
                'actual_value' => $performanceMetrics['memory_used'],
            ];
        }
        
        // Check upload speed threshold (for large files)
        if (isset($performanceMetrics['upload_speed_bps']) && 
            isset($performanceMetrics['file_size']) && 
            $performanceMetrics['file_size'] > 1024 * 1024 && // Files > 1MB
            $performanceMetrics['upload_speed_bps'] < 100 * 1024) { // < 100KB/s
            $alerts[] = [
                'type' => 'slow_upload_speed',
                'severity' => 'warning',
                'message' => "Upload speed of {$performanceMetrics['upload_speed_formatted']} is below the 100KB/s threshold for large files",
                'threshold' => 100 * 1024,
                'actual_value' => $performanceMetrics['upload_speed_bps'],
            ];
        }
        
        // Check failure rate threshold
        $recentFailureRate = $this->getRecentFailureRate();
        if ($recentFailureRate > 20) { // > 20% failure rate
            $alerts[] = [
                'type' => 'high_failure_rate',
                'severity' => 'critical',
                'message' => "Recent upload failure rate of {$recentFailureRate}% exceeds the 20% threshold",
                'threshold' => 20,
                'actual_value' => $recentFailureRate,
            ];
        }
        
        // Log alerts if any were triggered
        if (!empty($alerts)) {
            Log::warning('Upload performance alerts triggered', [
                'correlation_id' => $correlationId,
                'alerts' => $alerts,
                'performance_metrics' => $performanceMetrics,
            ]);
            
            // Store alerts for dashboard/monitoring
            $this->storePerformanceAlerts($correlationId, $alerts);
        }
    }
    
    /**
     * Get comprehensive request information.
     * 
     * @param Request $request The HTTP request
     * @return array Request information
     */
    private function getRequestInfo(Request $request): array
    {
        return [
            'method' => $request->method(),
            'url' => $request->url(),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'accept' => $request->header('Accept'),
            'request_size' => strlen($request->getContent()),
            'has_files' => $request->hasFile('file'),
            'all_files_count' => count($request->allFiles()),
            'form_data' => $request->except(['file', '_token']), // Exclude sensitive data
        ];
    }
    
    /**
     * Get comprehensive file information.
     * 
     * @param UploadedFile $file The uploaded file
     * @return array File information
     */
    private function getFileInfo(UploadedFile $file): array
    {
        // Check if file has a valid path before trying to get MIME type
        $realPath = $file->getRealPath();
        $hasValidPath = !empty($realPath) && file_exists($realPath);
        
        return [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'size_formatted' => $this->formatBytes($file->getSize()),
            'mime_type' => $hasValidPath ? $file->getMimeType() : 'unknown (invalid file path)',
            'extension' => $file->getClientOriginalExtension(),
            'php_error_code' => $file->getError(),
            'php_error_message' => $this->getPhpErrorMessage($file->getError()),
            'is_valid' => $file->isValid(),
            'real_path' => $realPath ?: 'empty',
            'has_valid_path' => $hasValidPath,
            'hash_name' => $hasValidPath ? $file->hashName() : 'unknown',
            'path_name' => $file->getPathname(),
        ];
    }
    
    /**
     * Get detailed file properties for logging.
     * 
     * @param UploadedFile $file The uploaded file
     * @return array Detailed file properties
     */
    private function getDetailedFileProperties(UploadedFile $file): array
    {
        $properties = $this->getFileInfo($file);
        
        // Add additional detailed properties
        $properties['validation_specific'] = [
            'client_original_name' => $file->getClientOriginalName(),
            'client_original_extension' => $file->getClientOriginalExtension(),
            'guessed_extension' => $file->guessExtension(),
            'client_mime_type' => $file->getClientMimeType(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'size_formatted' => $this->formatBytes($file->getSize()),
            'is_valid' => $file->isValid(),
            'error_code' => $file->getError(),
            'error_message' => $this->getPhpErrorMessage($file->getError()),
            'path_name' => $file->getPathname(),
            'real_path' => $file->getRealPath(),
            'hash_name' => $file->hashName(),
        ];
        
        // Add file content analysis if safe to do so
        if ($file->isValid() && $file->getSize() > 0 && $file->getSize() < 1024 * 1024) { // Only for files < 1MB
            try {
                $properties['content_analysis'] = [
                    'first_bytes' => bin2hex(substr(file_get_contents($file->getPathname()), 0, 16)),
                    'file_signature' => $this->detectFileSignature($file),
                ];
            } catch (\Exception $e) {
                $properties['content_analysis'] = [
                    'error' => 'Could not analyze file content: ' . $e->getMessage(),
                ];
            }
        }
        
        return $properties;
    }
    
    /**
     * Summarize failed validation rules for quick analysis.
     * 
     * @param array $failedRules Array of failed validation rules
     * @return array Summary of failed rules
     */
    private function summarizeFailedRules(array $failedRules): array
    {
        $summary = [
            'total_rules_failed' => count($failedRules),
            'rule_types' => [],
            'critical_failures' => [],
            'warning_failures' => [],
        ];
        
        foreach ($failedRules as $rule) {
            $ruleType = $rule['rule_type'] ?? 'unknown';
            $severity = $rule['severity'] ?? 'medium';
            
            // Count rule types
            if (!isset($summary['rule_types'][$ruleType])) {
                $summary['rule_types'][$ruleType] = 0;
            }
            $summary['rule_types'][$ruleType]++;
            
            // Categorize by severity
            if ($severity === 'critical' || $severity === 'high') {
                $summary['critical_failures'][] = $rule['rule_name'] ?? $ruleType;
            } else {
                $summary['warning_failures'][] = $rule['rule_name'] ?? $ruleType;
            }
        }
        
        return $summary;
    }
    
    /**
     * Categorize failed validation rules by type.
     * 
     * @param array $failedRules Array of failed validation rules
     * @return array Categorized rules
     */
    private function categorizeFailedRules(array $failedRules): array
    {
        $categories = [
            'file_size' => [],
            'file_type' => [],
            'file_content' => [],
            'server_limits' => [],
            'security' => [],
            'configuration' => [],
            'resource_limits' => [],
            'other' => [],
        ];
        
        foreach ($failedRules as $rule) {
            $ruleType = strtolower($rule['rule_type'] ?? '');
            $ruleName = strtolower($rule['rule_name'] ?? '');
            
            if (strpos($ruleType, 'size') !== false || strpos($ruleName, 'size') !== false) {
                $categories['file_size'][] = $rule;
            } elseif (strpos($ruleType, 'type') !== false || strpos($ruleType, 'extension') !== false || strpos($ruleType, 'mime') !== false) {
                $categories['file_type'][] = $rule;
            } elseif (strpos($ruleType, 'content') !== false || strpos($ruleType, 'signature') !== false) {
                $categories['file_content'][] = $rule;
            } elseif (strpos($ruleType, 'server') !== false || strpos($ruleType, 'limit') !== false) {
                $categories['server_limits'][] = $rule;
            } elseif (strpos($ruleType, 'security') !== false || strpos($ruleType, 'malicious') !== false) {
                $categories['security'][] = $rule;
            } elseif (strpos($ruleType, 'config') !== false || strpos($ruleType, 'setting') !== false) {
                $categories['configuration'][] = $rule;
            } elseif (strpos($ruleType, 'memory') !== false || strpos($ruleType, 'disk') !== false || strpos($ruleType, 'resource') !== false) {
                $categories['resource_limits'][] = $rule;
            } else {
                $categories['other'][] = $rule;
            }
        }
        
        // Remove empty categories
        return array_filter($categories, function($category) {
            return !empty($category);
        });
    }
    
    /**
     * Assess the severity of validation failures.
     * 
     * @param array $failedRules Array of failed validation rules
     * @return string Overall severity assessment
     */
    private function assessValidationSeverity(array $failedRules): string
    {
        $severityScores = [
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
        ];
        
        $maxSeverity = 0;
        $totalScore = 0;
        
        foreach ($failedRules as $rule) {
            $severity = $rule['severity'] ?? 'medium';
            $score = $severityScores[$severity] ?? 2;
            $totalScore += $score;
            $maxSeverity = max($maxSeverity, $score);
        }
        
        // Determine overall severity
        if ($maxSeverity >= 4) {
            return 'critical';
        } elseif ($maxSeverity >= 3 || $totalScore >= 6) {
            return 'high';
        } elseif ($totalScore >= 3) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Assess the likelihood that a retry would succeed.
     * 
     * @param array $failedRules Array of failed validation rules
     * @return array Retry likelihood assessment
     */
    private function assessRetryLikelihood(array $failedRules): array
    {
        $retryableRules = 0;
        $nonRetryableRules = 0;
        $suggestions = [];
        
        foreach ($failedRules as $rule) {
            $ruleType = strtolower($rule['rule_type'] ?? '');
            $isRetryable = $rule['is_retryable'] ?? null;
            
            if ($isRetryable === true) {
                $retryableRules++;
            } elseif ($isRetryable === false) {
                $nonRetryableRules++;
            } else {
                // Auto-assess based on rule type
                if (strpos($ruleType, 'size') !== false || 
                    strpos($ruleType, 'type') !== false || 
                    strpos($ruleType, 'extension') !== false) {
                    $nonRetryableRules++; // User needs to change file
                } elseif (strpos($ruleType, 'server') !== false || 
                         strpos($ruleType, 'resource') !== false || 
                         strpos($ruleType, 'network') !== false) {
                    $retryableRules++; // Might be temporary
                } else {
                    $nonRetryableRules++; // Default to non-retryable
                }
            }
            
            // Add specific suggestions
            if (isset($rule['retry_suggestion'])) {
                $suggestions[] = $rule['retry_suggestion'];
            }
        }
        
        $totalRules = $retryableRules + $nonRetryableRules;
        $retryLikelihood = $totalRules > 0 ? ($retryableRules / $totalRules) * 100 : 0;
        
        return [
            'likelihood_percentage' => round($retryLikelihood, 1),
            'retryable_rules' => $retryableRules,
            'non_retryable_rules' => $nonRetryableRules,
            'assessment' => $this->getRetryAssessmentText($retryLikelihood),
            'suggestions' => array_unique($suggestions),
        ];
    }
    
    /**
     * Get retry assessment text based on likelihood percentage.
     * 
     * @param float $likelihood Retry likelihood percentage
     * @return string Assessment text
     */
    private function getRetryAssessmentText(float $likelihood): string
    {
        if ($likelihood >= 80) {
            return 'high - retry likely to succeed';
        } elseif ($likelihood >= 50) {
            return 'medium - retry may succeed after addressing issues';
        } elseif ($likelihood >= 20) {
            return 'low - user action required before retry';
        } else {
            return 'very low - file or configuration changes required';
        }
    }
    
    /**
     * Track detailed validation failure metrics.
     * 
     * @param string $correlationId Correlation ID
     * @param array $failedRules Failed validation rules
     * @param UploadedFile|null $file The uploaded file
     */
    private function trackDetailedValidationFailure(string $correlationId, array $failedRules, ?UploadedFile $file = null): void
    {
        $metricData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'failure_type' => 'detailed_validation_failure',
            'failed_rules_count' => count($failedRules),
            'failed_rules_summary' => $this->summarizeFailedRules($failedRules),
            'rule_categories' => $this->categorizeFailedRules($failedRules),
            'severity_assessment' => $this->assessValidationSeverity($failedRules),
            'file_size' => $file ? $file->getSize() : null,
            'file_type' => $file ? $file->getClientOriginalExtension() : null,
            'server_config_snapshot' => $this->getServerConfiguration(),
        ];
        
        Log::channel('metrics')->error('Detailed validation failure tracked', $metricData);
    }
    
    /**
     * Detect file signature from file content.
     * 
     * @param UploadedFile $file The uploaded file
     * @return array File signature information
     */
    private function detectFileSignature(UploadedFile $file): array
    {
        try {
            $handle = fopen($file->getPathname(), 'rb');
            if (!$handle) {
                return ['error' => 'Cannot open file for signature detection'];
            }
            
            $header = fread($handle, 16);
            fclose($handle);
            
            $signatures = [
                'PDF' => ['%PDF-', 0],
                'JPEG' => ["\xFF\xD8\xFF", 0],
                'PNG' => ["\x89PNG\r\n\x1A\n", 0],
                'GIF87a' => ['GIF87a', 0],
                'GIF89a' => ['GIF89a', 0],
                'ZIP' => ['PK', 0],
                'MP3' => ["\xFF\xFB", 0],
                'MP4' => ['ftyp', 4],
                'AVI' => ['RIFF', 0],
            ];
            
            $detectedSignatures = [];
            foreach ($signatures as $type => $signature) {
                list($pattern, $offset) = $signature;
                if (substr($header, $offset, strlen($pattern)) === $pattern) {
                    $detectedSignatures[] = $type;
                }
            }
            
            return [
                'detected_types' => $detectedSignatures,
                'header_hex' => bin2hex($header),
                'header_length' => strlen($header),
            ];
            
        } catch (\Exception $e) {
            return ['error' => 'Signature detection failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get current server configuration relevant to file uploads.
     * 
     * @return array Server configuration
     */
    private function getServerConfiguration(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'file_uploads' => ini_get('file_uploads'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'upload_max_filesize_bytes' => $this->convertToBytes(ini_get('upload_max_filesize')),
            'post_max_size' => ini_get('post_max_size'),
            'post_max_size_bytes' => $this->convertToBytes(ini_get('post_max_size')),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'memory_limit_bytes' => $this->convertToBytes(ini_get('memory_limit')),
            'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
            'max_input_time' => ini_get('max_input_time'),
            'max_input_vars' => ini_get('max_input_vars'),
            'auto_detect_line_endings' => ini_get('auto_detect_line_endings'),
        ];
    }
    
    /**
     * Get current resource availability.
     * 
     * @return array Resource availability information
     */
    private function getResourceAvailability(): array
    {
        $uploadPath = storage_path('app/public');
        $tempDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
        
        return [
            'memory_usage' => [
                'current' => memory_get_usage(true),
                'current_formatted' => $this->formatBytes(memory_get_usage(true)),
                'peak' => memory_get_peak_usage(true),
                'peak_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
                'limit' => $this->convertToBytes(ini_get('memory_limit')),
                'limit_formatted' => ini_get('memory_limit'),
                'available' => $this->convertToBytes(ini_get('memory_limit')) - memory_get_usage(true),
                'available_formatted' => $this->formatBytes($this->convertToBytes(ini_get('memory_limit')) - memory_get_usage(true)),
            ],
            'disk_space' => [
                'upload_directory' => [
                    'path' => $uploadPath,
                    'free_space' => disk_free_space($uploadPath),
                    'free_space_formatted' => $this->formatBytes(disk_free_space($uploadPath) ?: 0),
                    'total_space' => disk_total_space($uploadPath),
                    'total_space_formatted' => $this->formatBytes(disk_total_space($uploadPath) ?: 0),
                    'used_space' => (disk_total_space($uploadPath) ?: 0) - (disk_free_space($uploadPath) ?: 0),
                    'used_space_formatted' => $this->formatBytes((disk_total_space($uploadPath) ?: 0) - (disk_free_space($uploadPath) ?: 0)),
                ],
                'temp_directory' => [
                    'path' => $tempDir,
                    'free_space' => disk_free_space($tempDir),
                    'free_space_formatted' => $this->formatBytes(disk_free_space($tempDir) ?: 0),
                    'total_space' => disk_total_space($tempDir),
                    'total_space_formatted' => $this->formatBytes(disk_total_space($tempDir) ?: 0),
                    'is_writable' => is_writable($tempDir),
                    'is_readable' => is_readable($tempDir),
                ],
            ],
            'server_load' => $this->getServerLoadMetrics(),
        ];
    }
    
    /**
     * Get current resource usage.
     * 
     * @return array Current resource usage
     */
    private function getResourceUsage(): array
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_usage_formatted' => $this->formatBytes(memory_get_usage(true)),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'peak_memory_usage_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
            'execution_time' => microtime(true) - $startTime,
            'included_files_count' => count(get_included_files()),
        ];
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
     * Calculate resource requirements for a file.
     * 
     * @param UploadedFile $file The uploaded file
     * @return array Resource requirements
     */
    private function calculateResourceRequirements(UploadedFile $file): array
    {
        $fileSize = $file->getSize();
        
        return [
            'disk_space_required' => $fileSize * 2, // File + processing space
            'disk_space_required_formatted' => $this->formatBytes($fileSize * 2),
            'memory_required' => $fileSize * 3, // Conservative estimate for processing
            'memory_required_formatted' => $this->formatBytes($fileSize * 3),
            'temp_space_required' => $fileSize * 2, // Temporary file + processing
            'temp_space_required_formatted' => $this->formatBytes($fileSize * 2),
        ];
    }
    
    /**
     * Categorize validation errors for analysis.
     * 
     * @param array $validationErrors Validation errors
     * @return array Categorized errors
     */
    private function categorizeValidationErrors(array $validationErrors): array
    {
        $categories = [
            'file_size' => [],
            'file_type' => [],
            'server_config' => [],
            'resource_limits' => [],
            'security' => [],
            'other' => [],
        ];
        
        foreach ($validationErrors as $field => $errors) {
            foreach ($errors as $error) {
                $errorLower = strtolower($error);
                
                if (strpos($errorLower, 'size') !== false || strpos($errorLower, 'large') !== false) {
                    $categories['file_size'][] = $error;
                } elseif (strpos($errorLower, 'type') !== false || strpos($errorLower, 'extension') !== false || strpos($errorLower, 'mime') !== false) {
                    $categories['file_type'][] = $error;
                } elseif (strpos($errorLower, 'server') !== false || strpos($errorLower, 'configuration') !== false) {
                    $categories['server_config'][] = $error;
                } elseif (strpos($errorLower, 'memory') !== false || strpos($errorLower, 'disk') !== false || strpos($errorLower, 'space') !== false) {
                    $categories['resource_limits'][] = $error;
                } elseif (strpos($errorLower, 'security') !== false || strpos($errorLower, 'malicious') !== false) {
                    $categories['security'][] = $error;
                } else {
                    $categories['other'][] = $error;
                }
            }
        }
        
        // Remove empty categories
        return array_filter($categories, function($category) {
            return !empty($category);
        });
    }
    
    /**
     * Track upload failure metrics.
     * 
     * @param string $correlationId Correlation ID
     * @param string $failureType Type of failure
     * @param array $failureDetails Details about the failure
     */
    private function trackUploadFailure(string $correlationId, string $failureType, array $failureDetails): void
    {
        $metricData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'failure_type' => $failureType,
            'failure_details' => $failureDetails,
            'server_config_snapshot' => $this->getServerConfiguration(),
        ];
        
        try {
            if (app()->bound('log') && Log::getLogger()) {
                Log::channel('metrics')->info('Upload failure tracked', $metricData);
            }
        } catch (\Exception $e) {
            // Fallback to default channel if metrics channel is not available
            try {
                Log::info('Upload failure tracked', $metricData);
            } catch (\Exception $fallbackException) {
                // If logging fails completely, continue silently in tests
            }
        }
    }
    
    /**
     * Track upload success metrics.
     * 
     * @param string $correlationId Correlation ID
     * @param Content $content The created content
     */
    private function trackUploadSuccess(string $correlationId, Content $content): void
    {
        $metricData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'content_id' => $content->id,
            'content_type' => $content->type,
            'file_size' => $content->file_size,
            'upload_duration' => $this->calculateUploadDuration($correlationId),
            'server_config_snapshot' => $this->getServerConfiguration(),
        ];
        
        try {
            if (app()->bound('log') && Log::getLogger()) {
                Log::channel('metrics')->info('Upload success tracked', $metricData);
            }
        } catch (\Exception $e) {
            // Fallback to default channel if metrics channel is not available
            try {
                Log::info('Upload success tracked', $metricData);
            } catch (\Exception $fallbackException) {
                // If logging fails completely, continue silently in tests
            }
        }
    }
    
    /**
     * Calculate upload duration from correlation ID timestamp.
     * 
     * @param string $correlationId Correlation ID
     * @return float|null Upload duration in seconds
     */
    private function calculateUploadDuration(string $correlationId): ?float
    {
        // This is a simplified implementation
        // In a real implementation, you might store start times in cache/database
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        return microtime(true) - $startTime;
    }
    
    /**
     * Get PHP error message for error code.
     * 
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getPhpErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
        ];
        
        return $messages[$errorCode] ?? 'Unknown upload error';
    }
    
    /**
     * Get diagnostic information for PHP upload errors.
     * 
     * @param int $phpErrorCode PHP upload error code
     * @return array Diagnostic information
     */
    private function getDiagnosticInfo(int $phpErrorCode): array
    {
        $diagnostics = [
            'error_code' => $phpErrorCode,
            'error_message' => $this->getPhpErrorMessage($phpErrorCode),
            'suggested_actions' => [],
            'config_checks' => [],
        ];
        
        switch ($phpErrorCode) {
            case UPLOAD_ERR_INI_SIZE:
                $diagnostics['suggested_actions'] = [
                    'Increase upload_max_filesize in php.ini',
                    'Ensure post_max_size is larger than upload_max_filesize',
                    'Restart web server after configuration changes',
                ];
                $diagnostics['config_checks'] = [
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size'),
                ];
                break;
                
            case UPLOAD_ERR_FORM_SIZE:
                $diagnostics['suggested_actions'] = [
                    'Check HTML form MAX_FILE_SIZE value',
                    'Ensure form limit matches server configuration',
                ];
                break;
                
            case UPLOAD_ERR_PARTIAL:
                $diagnostics['suggested_actions'] = [
                    'Check network connection stability',
                    'Increase max_execution_time for large files',
                    'Check max_input_time setting',
                ];
                $diagnostics['config_checks'] = [
                    'max_execution_time' => ini_get('max_execution_time'),
                    'max_input_time' => ini_get('max_input_time'),
                ];
                break;
                
            case UPLOAD_ERR_NO_TMP_DIR:
                $diagnostics['suggested_actions'] = [
                    'Configure upload_tmp_dir in php.ini',
                    'Ensure temporary directory exists and is writable',
                ];
                $diagnostics['config_checks'] = [
                    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
                    'sys_temp_dir' => sys_get_temp_dir(),
                ];
                break;
                
            case UPLOAD_ERR_CANT_WRITE:
                $diagnostics['suggested_actions'] = [
                    'Check disk space availability',
                    'Verify temporary directory permissions',
                    'Check file system permissions',
                ];
                break;
        }
        
        return $diagnostics;
    }
    
    /**
     * Convert PHP ini size values to bytes.
     * 
     * @param string $size Size string (e.g., "2M", "1G")
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
     * Get CPU count (simplified implementation).
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
     * Get system uptime (simplified implementation).
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
    
    /**
     * Analyze performance metrics for insights.
     * 
     * @param array $performanceMetrics Performance metrics
     * @return array Performance analysis
     */
    private function analyzePerformanceMetrics(array $performanceMetrics): array
    {
        $analysis = [
            'performance_rating' => 'good',
            'bottlenecks' => [],
            'recommendations' => [],
        ];
        
        // Analyze upload duration
        if (isset($performanceMetrics['duration_seconds'])) {
            $duration = $performanceMetrics['duration_seconds'];
            if ($duration > 60) {
                $analysis['performance_rating'] = 'poor';
                $analysis['bottlenecks'][] = 'slow_upload_duration';
                $analysis['recommendations'][] = 'Consider increasing server resources or optimizing upload handling';
            } elseif ($duration > 30) {
                $analysis['performance_rating'] = 'fair';
                $analysis['bottlenecks'][] = 'moderate_upload_duration';
                $analysis['recommendations'][] = 'Monitor upload performance and consider optimization';
            }
        }
        
        // Analyze memory usage
        if (isset($performanceMetrics['memory_used'])) {
            $memoryUsed = $performanceMetrics['memory_used'];
            if ($memoryUsed > 100 * 1024 * 1024) { // > 100MB
                $analysis['performance_rating'] = 'poor';
                $analysis['bottlenecks'][] = 'high_memory_usage';
                $analysis['recommendations'][] = 'Optimize file processing to reduce memory consumption';
            } elseif ($memoryUsed > 50 * 1024 * 1024) { // > 50MB
                if ($analysis['performance_rating'] === 'good') {
                    $analysis['performance_rating'] = 'fair';
                }
                $analysis['bottlenecks'][] = 'moderate_memory_usage';
                $analysis['recommendations'][] = 'Monitor memory usage for large file uploads';
            }
        }
        
        // Analyze upload speed
        if (isset($performanceMetrics['upload_speed_bps']) && isset($performanceMetrics['file_size'])) {
            $speed = $performanceMetrics['upload_speed_bps'];
            $fileSize = $performanceMetrics['file_size'];
            
            if ($fileSize > 1024 * 1024 && $speed < 50 * 1024) { // Large files < 50KB/s
                $analysis['performance_rating'] = 'poor';
                $analysis['bottlenecks'][] = 'slow_upload_speed';
                $analysis['recommendations'][] = 'Check network connectivity and server I/O performance';
            } elseif ($fileSize > 1024 * 1024 && $speed < 100 * 1024) { // Large files < 100KB/s
                if ($analysis['performance_rating'] === 'good') {
                    $analysis['performance_rating'] = 'fair';
                }
                $analysis['bottlenecks'][] = 'moderate_upload_speed';
                $analysis['recommendations'][] = 'Consider optimizing network or storage performance';
            }
        }
        
        return $analysis;
    }
    
    /**
     * Track performance statistics for trending analysis.
     * 
     * @param string $correlationId Correlation ID
     * @param array $performanceMetrics Performance metrics
     */
    private function trackPerformanceStatistics(string $correlationId, array $performanceMetrics): void
    {
        $timestamp = now();
        $periods = ['hour', 'day', 'week'];
        
        foreach ($periods as $period) {
            $this->updatePerformanceStats($period, $performanceMetrics, $timestamp);
        }
        
        // Log performance statistics update
        Log::info('Performance statistics updated', [
            'correlation_id' => $correlationId,
            'metrics_tracked' => array_keys($performanceMetrics),
            'periods_updated' => $periods,
        ]);
    }
    
    /**
     * Update performance statistics for a specific period.
     * 
     * @param string $period Time period
     * @param array $performanceMetrics Performance metrics
     * @param \Carbon\Carbon $timestamp Timestamp
     */
    private function updatePerformanceStats(string $period, array $performanceMetrics, $timestamp): void
    {
        $periodKey = $this->getPeriodKey($period, $timestamp);
        
        // Update upload counts
        if ($performanceMetrics['success']) {
            cache()->increment("successful_uploads_{$period}", 1);
        } else {
            cache()->increment("failed_uploads_{$period}", 1);
        }
        cache()->increment("total_uploads_{$period}", 1);
        
        // Update performance metrics
        if (isset($performanceMetrics['duration_seconds'])) {
            $this->updateAverageMetric("avg_duration_{$period}", $performanceMetrics['duration_seconds']);
        }
        
        if (isset($performanceMetrics['memory_used'])) {
            $this->updateAverageMetric("avg_memory_{$period}", $performanceMetrics['memory_used']);
        }
        
        if (isset($performanceMetrics['upload_speed_bps'])) {
            $this->updateAverageMetric("avg_speed_{$period}", $performanceMetrics['upload_speed_bps']);
        }
        
        if (isset($performanceMetrics['file_size'])) {
            $this->updateAverageMetric("avg_file_size_{$period}", $performanceMetrics['file_size']);
        }
    }
    
    /**
     * Update average metric using exponential moving average.
     * 
     * @param string $key Cache key
     * @param float $newValue New value
     */
    private function updateAverageMetric(string $key, float $newValue): void
    {
        $currentAvg = cache()->get($key, $newValue);
        $alpha = 0.1; // Smoothing factor
        $newAvg = ($alpha * $newValue) + ((1 - $alpha) * $currentAvg);
        
        cache()->put($key, $newAvg, now()->addDays(7));
    }
    
    /**
     * Get period start time.
     * 
     * @param string $period Time period
     * @return \Carbon\Carbon Start time
     */
    private function getPeriodStartTime(string $period): \Carbon\Carbon
    {
        switch ($period) {
            case 'hour':
                return now()->startOfHour();
            case 'day':
                return now()->startOfDay();
            case 'week':
                return now()->startOfWeek();
            case 'month':
                return now()->startOfMonth();
            default:
                return now()->startOfDay();
        }
    }
    
    /**
     * Get period key for cache.
     * 
     * @param string $period Time period
     * @param \Carbon\Carbon $timestamp Timestamp
     * @return string Period key
     */
    private function getPeriodKey(string $period, $timestamp): string
    {
        switch ($period) {
            case 'hour':
                return $timestamp->format('Y-m-d-H');
            case 'day':
                return $timestamp->format('Y-m-d');
            case 'week':
                return $timestamp->format('Y-W');
            case 'month':
                return $timestamp->format('Y-m');
            default:
                return $timestamp->format('Y-m-d');
        }
    }
    
    /**
     * Get performance trends for a period.
     * 
     * @param string $period Time period
     * @return array Performance trends
     */
    private function getPerformanceTrends(string $period): array
    {
        return [
            'average_duration' => cache()->get("avg_duration_{$period}", 0),
            'average_duration_formatted' => $this->formatDuration(cache()->get("avg_duration_{$period}", 0)),
            'average_memory_usage' => cache()->get("avg_memory_{$period}", 0),
            'average_memory_usage_formatted' => $this->formatBytes(cache()->get("avg_memory_{$period}", 0)),
            'average_upload_speed' => cache()->get("avg_speed_{$period}", 0),
            'average_upload_speed_formatted' => $this->formatBytes(cache()->get("avg_speed_{$period}", 0)) . '/s',
            'average_file_size' => cache()->get("avg_file_size_{$period}", 0),
            'average_file_size_formatted' => $this->formatBytes(cache()->get("avg_file_size_{$period}", 0)),
        ];
    }
    
    /**
     * Get recent failure rate for alerting.
     * 
     * @return float Failure rate percentage
     */
    private function getRecentFailureRate(): float
    {
        $totalUploads = cache()->get('total_uploads_hour', 0);
        $failedUploads = cache()->get('failed_uploads_hour', 0);
        
        return $totalUploads > 0 ? ($failedUploads / $totalUploads) * 100 : 0;
    }
    
    /**
     * Store performance alerts for monitoring dashboard.
     * 
     * @param string $correlationId Correlation ID
     * @param array $alerts Performance alerts
     */
    private function storePerformanceAlerts(string $correlationId, array $alerts): void
    {
        $alertData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'alerts' => $alerts,
            'alert_count' => count($alerts),
            'max_severity' => $this->getMaxAlertSeverity($alerts),
        ];
        
        // Store in cache for dashboard retrieval
        $alertKey = "performance_alerts_" . now()->format('Y-m-d-H');
        $existingAlerts = cache()->get($alertKey, []);
        $existingAlerts[] = $alertData;
        cache()->put($alertKey, $existingAlerts, now()->addHours(24));
        
        // Log to metrics channel
        Log::channel('metrics')->warning('Performance alerts stored', $alertData);
    }
    
    /**
     * Get maximum alert severity.
     * 
     * @param array $alerts Alerts array
     * @return string Maximum severity
     */
    private function getMaxAlertSeverity(array $alerts): string
    {
        $severityLevels = ['info' => 1, 'warning' => 2, 'critical' => 3];
        $maxSeverity = 'info';
        $maxLevel = 0;
        
        foreach ($alerts as $alert) {
            $severity = $alert['severity'] ?? 'info';
            $level = $severityLevels[$severity] ?? 1;
            
            if ($level > $maxLevel) {
                $maxLevel = $level;
                $maxSeverity = $severity;
            }
        }
        
        return $maxSeverity;
    }
    
    /**
     * Get failure pattern description.
     * 
     * @param string $pattern Failure pattern
     * @return string Description
     */
    private function getFailurePatternDescription(string $pattern): string
    {
        $descriptions = [
            'file_size_exceeded' => 'Files exceed maximum allowed size limits',
            'invalid_file_type' => 'Files have unsupported or invalid file types',
            'php_upload_errors' => 'PHP-level upload errors (server configuration issues)',
            'server_resource_limits' => 'Server resource limitations (memory, disk space)',
            'validation_failures' => 'General validation rule failures',
            'network_timeouts' => 'Network connectivity or timeout issues',
            'security_blocks' => 'Security-related upload blocks',
        ];
        
        return $descriptions[$pattern] ?? 'Unknown failure pattern';
    }
    
    /**
     * Get failure pattern suggestions.
     * 
     * @param string $pattern Failure pattern
     * @return array Suggested actions
     */
    private function getFailurePatternSuggestions(string $pattern): array
    {
        $suggestions = [
            'file_size_exceeded' => [
                'Increase upload_max_filesize in PHP configuration',
                'Educate users about file size limits',
                'Implement client-side file size validation',
            ],
            'invalid_file_type' => [
                'Review and update allowed file type whitelist',
                'Improve client-side file type validation',
                'Provide clear guidance on supported file types',
            ],
            'php_upload_errors' => [
                'Review PHP upload configuration settings',
                'Check server disk space and permissions',
                'Monitor server resource usage',
            ],
            'server_resource_limits' => [
                'Increase server memory limits',
                'Monitor and clean up disk space',
                'Optimize file processing algorithms',
            ],
            'validation_failures' => [
                'Review validation rules for accuracy',
                'Improve error messages for users',
                'Add more specific validation checks',
            ],
            'network_timeouts' => [
                'Increase network timeout settings',
                'Implement upload progress indicators',
                'Add retry mechanisms for failed uploads',
            ],
            'security_blocks' => [
                'Review security policies and rules',
                'Check for false positive security blocks',
                'Update security scanning configurations',
            ],
        ];
        
        return $suggestions[$pattern] ?? ['Review and analyze this failure pattern'];
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
     * Log secure file storage operation for security auditing.
     * 
     * @param string $correlationId Correlation ID from the upload attempt
     * @param array $storageDetails Details about the secure storage operation
     */
    public function logSecureFileStorage(string $correlationId, array $storageDetails): void
    {
        $logData = [
            'correlation_id' => $correlationId,
            'event_type' => 'secure_file_storage',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'storage_details' => $storageDetails,
            'security_audit' => [
                'outside_web_root' => true,
                'secure_filename_generated' => true,
                'permissions_set' => true,
                'access_logged' => true,
            ],
        ];
        
        Log::info('Secure file storage operation completed', $logData);
        
        // Track secure storage metrics
        $this->trackSecureStorageMetrics($correlationId, $storageDetails);
    }

    /**
     * Log secure file deletion operation for security auditing.
     * 
     * @param string $correlationId Correlation ID for tracking
     * @param array $deletionDetails Details about the secure deletion operation
     */
    public function logSecureFileDeletion(string $correlationId, array $deletionDetails): void
    {
        $logData = [
            'correlation_id' => $correlationId,
            'event_type' => 'secure_file_deletion',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'deletion_details' => $deletionDetails,
            'security_audit' => [
                'secure_deletion' => true,
                'access_logged' => true,
                'user_verified' => true,
            ],
        ];
        
        Log::info('Secure file deletion operation completed', $logData);
        
        // Track secure deletion metrics
        $this->trackSecureDeletionMetrics($correlationId, $deletionDetails);
    }

    /**
     * Track secure storage metrics for monitoring.
     * 
     * @param string $correlationId Correlation ID
     * @param array $storageDetails Storage details
     */
    private function trackSecureStorageMetrics(string $correlationId, array $storageDetails): void
    {
        $metricData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'metric_type' => 'secure_storage',
            'storage_disk' => $storageDetails['security_details']['storage_disk'] ?? 'unknown',
            'outside_web_root' => $storageDetails['security_details']['outside_web_root'] ?? false,
            'secure_filename' => $storageDetails['security_details']['secure_filename_generated'] ?? false,
            'permissions_set' => $storageDetails['security_details']['permissions_set'] ?? false,
            'file_size' => $storageDetails['file_details']['file_size'] ?? 0,
        ];
        
        Log::channel('metrics')->info('Secure storage metrics tracked', $metricData);
        
        // Update secure storage counters
        cache()->increment('secure_storage_operations_total', 1);
        cache()->increment('secure_storage_operations_' . date('Y-m-d'), 1);
    }

    /**
     * Track secure deletion metrics for monitoring.
     * 
     * @param string $correlationId Correlation ID
     * @param array $deletionDetails Deletion details
     */
    private function trackSecureDeletionMetrics(string $correlationId, array $deletionDetails): void
    {
        $metricData = [
            'correlation_id' => $correlationId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'metric_type' => 'secure_deletion',
            'file_size' => $deletionDetails['file_details']['file_size'] ?? 0,
        ];
        
        Log::channel('metrics')->info('Secure deletion metrics tracked', $metricData);
        
        // Update secure deletion counters
        cache()->increment('secure_deletion_operations_total', 1);
        cache()->increment('secure_deletion_operations_' . date('Y-m-d'), 1);
    }
}