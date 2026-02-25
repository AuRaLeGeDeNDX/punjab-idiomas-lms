<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * AuditReport represents the comprehensive result of a file storage audit operation.
 * 
 * This class aggregates multiple diagnostic results and provides summary statistics,
 * trend analysis, and batch reporting capabilities for file storage audits.
 * 
 * Requirements: 1.1, 7.1, 7.2
 */
class AuditReport
{
    private string $correlationId;
    private Carbon $startTime;
    private ?Carbon $endTime = null;
    private array $diagnosticResults = [];
    private array $summary = [];
    private array $statistics = [];
    private array $trends = [];
    private array $recommendations = [];

    /**
     * Create a new AuditReport instance.
     * 
     * @param string $correlationId Correlation ID for tracking
     */
    public function __construct(string $correlationId)
    {
        $this->correlationId = $correlationId;
        $this->startTime = now();
    }

    /**
     * Get the correlation ID.
     * 
     * @return string Correlation ID
     */
    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    /**
     * Get the audit start time.
     * 
     * @return Carbon Start time
     */
    public function getStartTime(): Carbon
    {
        return $this->startTime;
    }

    /**
     * Get the audit end time.
     * 
     * @return Carbon|null End time or null if not completed
     */
    public function getEndTime(): ?Carbon
    {
        return $this->endTime;
    }

    /**
     * Get the audit duration in seconds.
     * 
     * @return float|null Duration in seconds or null if not completed
     */
    public function getDuration(): ?float
    {
        if (!$this->endTime) {
            return null;
        }
        
        return $this->endTime->diffInSeconds($this->startTime, true);
    }

    /**
     * Add a diagnostic result to the audit.
     * 
     * @param DiagnosticResult $result Diagnostic result
     */
    public function addDiagnosticResult(DiagnosticResult $result): void
    {
        $this->diagnosticResults[] = $result;
    }

    /**
     * Get all diagnostic results.
     * 
     * @return array Array of DiagnosticResult objects
     */
    public function getDiagnosticResults(): array
    {
        return $this->diagnosticResults;
    }

    /**
     * Get diagnostic results by status.
     * 
     * @param string $status Status filter (healthy, warning, critical)
     * @return array Filtered diagnostic results
     */
    public function getDiagnosticResultsByStatus(string $status): array
    {
        return array_filter($this->diagnosticResults, function(DiagnosticResult $result) use ($status) {
            return $result->getOverallStatus() === $status;
        });
    }

    /**
     * Get diagnostic results with inconsistencies.
     * 
     * @return array Diagnostic results with inconsistencies
     */
    public function getDiagnosticResultsWithInconsistencies(): array
    {
        return array_filter($this->diagnosticResults, function(DiagnosticResult $result) {
            return $result->hasInconsistencies();
        });
    }

    /**
     * Get diagnostic results for missing files.
     * 
     * @return array Diagnostic results for missing files
     */
    public function getMissingFileResults(): array
    {
        return array_filter($this->diagnosticResults, function(DiagnosticResult $result) {
            return !$result->fileExists();
        });
    }

    /**
     * Generate comprehensive audit summary.
     */
    public function generateSummary(): void
    {
        $this->endTime = now();
        
        $totalProcessed = count($this->diagnosticResults);
        $filesFound = count(array_filter($this->diagnosticResults, fn($r) => $r->fileExists()));
        $filesMissing = $totalProcessed - $filesFound;
        $inconsistenciesFound = count($this->getDiagnosticResultsWithInconsistencies());
        
        $statusCounts = [
            'healthy' => count($this->getDiagnosticResultsByStatus('healthy')),
            'warning' => count($this->getDiagnosticResultsByStatus('warning')),
            'critical' => count($this->getDiagnosticResultsByStatus('critical')),
        ];
        
        $this->summary = [
            'audit_info' => [
                'correlation_id' => $this->correlationId,
                'start_time' => $this->startTime->toISOString(),
                'end_time' => $this->endTime->toISOString(),
                'duration_seconds' => $this->getDuration(),
                'duration_formatted' => $this->formatDuration($this->getDuration()),
            ],
            'processing_stats' => [
                'total_processed' => $totalProcessed,
                'files_found' => $filesFound,
                'files_missing' => $filesMissing,
                'inconsistencies_found' => $inconsistenciesFound,
                'success_rate' => $totalProcessed > 0 ? round(($filesFound / $totalProcessed) * 100, 2) : 0,
            ],
            'status_distribution' => $statusCounts,
            'health_percentage' => $totalProcessed > 0 ? round(($statusCounts['healthy'] / $totalProcessed) * 100, 2) : 0,
        ];
        
        $this->generateStatistics();
        $this->generateTrends();
        $this->generateRecommendations();
    }

    /**
     * Get the audit summary.
     * 
     * @return array Audit summary
     */
    public function getSummary(): array
    {
        return $this->summary;
    }

    /**
     * Get total number of processed records.
     * 
     * @return int Total processed
     */
    public function getTotalProcessed(): int
    {
        return count($this->diagnosticResults);
    }

    /**
     * Get number of files found.
     * 
     * @return int Files found
     */
    public function getFilesFound(): int
    {
        return count(array_filter($this->diagnosticResults, fn($r) => $r->fileExists()));
    }

    /**
     * Get number of missing files.
     * 
     * @return int Files missing
     */
    public function getFilesMissing(): int
    {
        return $this->getTotalProcessed() - $this->getFilesFound();
    }

    /**
     * Get number of inconsistencies found.
     * 
     * @return int Inconsistencies found
     */
    public function getInconsistenciesFound(): int
    {
        return count($this->getDiagnosticResultsWithInconsistencies());
    }

    /**
     * Get audit statistics.
     * 
     * @return array Audit statistics
     */
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    /**
     * Get audit trends.
     * 
     * @return array Audit trends
     */
    public function getTrends(): array
    {
        return $this->trends;
    }

    /**
     * Get audit recommendations.
     * 
     * @return array Audit recommendations
     */
    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    /**
     * Get inconsistency breakdown by type.
     * 
     * @return array Inconsistency breakdown
     */
    public function getInconsistencyBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->diagnosticResults as $result) {
            foreach ($result->getInconsistencies() as $type => $inconsistency) {
                if (!isset($breakdown[$type])) {
                    $breakdown[$type] = [
                        'count' => 0,
                        'severity' => $inconsistency['severity'] ?? 'medium',
                        'examples' => [],
                    ];
                }
                
                $breakdown[$type]['count']++;
                
                // Store up to 5 examples
                if (count($breakdown[$type]['examples']) < 5) {
                    $breakdown[$type]['examples'][] = [
                        'content_id' => $result->getContent()->id,
                        'file_path' => $result->getContent()->file_path,
                        'details' => $inconsistency,
                    ];
                }
            }
        }
        
        // Sort by count (most common first)
        uasort($breakdown, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        return $breakdown;
    }

    /**
     * Get storage disk distribution.
     * 
     * @return array Storage disk distribution
     */
    public function getStorageDiskDistribution(): array
    {
        $distribution = [
            'recorded' => [],
            'actual' => [],
            'mismatches' => 0,
        ];
        
        foreach ($this->diagnosticResults as $result) {
            $content = $result->getContent();
            $recordedDisk = $content->storage_disk ?? 'public';
            
            // Count recorded disk distribution
            if (!isset($distribution['recorded'][$recordedDisk])) {
                $distribution['recorded'][$recordedDisk] = 0;
            }
            $distribution['recorded'][$recordedDisk]++;
            
            // Count actual disk distribution
            $actualLocation = $result->getActualLocation();
            if ($actualLocation) {
                $actualDisk = $actualLocation->getDisk();
                
                if (!isset($distribution['actual'][$actualDisk])) {
                    $distribution['actual'][$actualDisk] = 0;
                }
                $distribution['actual'][$actualDisk]++;
                
                // Count mismatches
                if ($recordedDisk !== $actualDisk) {
                    $distribution['mismatches']++;
                }
            }
        }
        
        return $distribution;
    }

    /**
     * Get content type breakdown.
     * 
     * @return array Content type breakdown
     */
    public function getContentTypeBreakdown(): array
    {
        $breakdown = [];
        
        foreach ($this->diagnosticResults as $result) {
            $contentType = $result->getContent()->type;
            
            if (!isset($breakdown[$contentType])) {
                $breakdown[$contentType] = [
                    'total' => 0,
                    'healthy' => 0,
                    'warning' => 0,
                    'critical' => 0,
                    'missing' => 0,
                ];
            }
            
            $breakdown[$contentType]['total']++;
            
            $status = $result->getOverallStatus();
            $breakdown[$contentType][$status]++;
            
            if (!$result->fileExists()) {
                $breakdown[$contentType]['missing']++;
            }
        }
        
        return $breakdown;
    }

    /**
     * Export audit report to array.
     * 
     * @param bool $includeDetailedResults Include detailed diagnostic results
     * @return array Array representation of the audit report
     */
    public function toArray(bool $includeDetailedResults = false): array
    {
        $report = [
            'audit_info' => [
                'correlation_id' => $this->correlationId,
                'start_time' => $this->startTime->toISOString(),
                'end_time' => $this->endTime?->toISOString(),
                'duration_seconds' => $this->getDuration(),
                'duration_formatted' => $this->formatDuration($this->getDuration()),
            ],
            'summary' => $this->summary,
            'statistics' => $this->statistics,
            'trends' => $this->trends,
            'recommendations' => $this->recommendations,
            'breakdowns' => [
                'inconsistencies' => $this->getInconsistencyBreakdown(),
                'storage_disks' => $this->getStorageDiskDistribution(),
                'content_types' => $this->getContentTypeBreakdown(),
            ],
        ];
        
        if ($includeDetailedResults) {
            $report['detailed_results'] = array_map(function(DiagnosticResult $result) {
                return $result->toArray();
            }, $this->diagnosticResults);
        } else {
            $report['result_summaries'] = array_map(function(DiagnosticResult $result) {
                return $result->getSummary();
            }, $this->diagnosticResults);
        }
        
        return $report;
    }

    /**
     * Export audit report to JSON.
     * 
     * @param bool $includeDetailedResults Include detailed diagnostic results
     * @return string JSON representation
     */
    public function toJson(bool $includeDetailedResults = false): string
    {
        return json_encode($this->toArray($includeDetailedResults), JSON_PRETTY_PRINT);
    }

    /**
     * Generate detailed statistics.
     */
    private function generateStatistics(): void
    {
        $totalProcessed = count($this->diagnosticResults);
        
        if ($totalProcessed === 0) {
            $this->statistics = [];
            return;
        }
        
        // File size statistics
        $fileSizes = [];
        foreach ($this->diagnosticResults as $result) {
            if ($result->fileExists() && $result->getActualLocation()) {
                $size = $result->getActualLocation()->getSize();
                if ($size !== null) {
                    $fileSizes[] = $size;
                }
            }
        }
        
        $this->statistics = [
            'file_sizes' => $this->calculateSizeStatistics($fileSizes),
            'inconsistency_rates' => $this->calculateInconsistencyRates(),
            'url_generation_stats' => $this->calculateUrlGenerationStats(),
            'storage_health_score' => $this->calculateStorageHealthScore(),
        ];
    }

    /**
     * Generate trend analysis.
     */
    private function generateTrends(): void
    {
        // This is a simplified implementation
        // In a real system, you might compare with historical audit data
        
        $this->trends = [
            'health_trend' => $this->calculateHealthTrend(),
            'common_issues' => $this->identifyCommonIssues(),
            'improvement_areas' => $this->identifyImprovementAreas(),
        ];
    }

    /**
     * Generate audit recommendations.
     */
    private function generateRecommendations(): void
    {
        $recommendations = [];
        
        // High-level recommendations based on audit results
        $missingFileRate = $this->getTotalProcessed() > 0 ? 
            ($this->getFilesMissing() / $this->getTotalProcessed()) * 100 : 0;
        
        if ($missingFileRate > 10) {
            $recommendations[] = [
                'type' => 'high_missing_file_rate',
                'priority' => 'critical',
                'description' => "High missing file rate ({$missingFileRate}%) detected - investigate file deletion or migration issues",
                'action' => 'investigate_missing_files',
                'automated_fix_available' => false,
            ];
        }
        
        $inconsistencyRate = $this->getTotalProcessed() > 0 ? 
            ($this->getInconsistenciesFound() / $this->getTotalProcessed()) * 100 : 0;
        
        if ($inconsistencyRate > 20) {
            $recommendations[] = [
                'type' => 'high_inconsistency_rate',
                'priority' => 'high',
                'description' => "High inconsistency rate ({$inconsistencyRate}%) detected - run repair operations",
                'action' => 'run_batch_repair',
                'automated_fix_available' => true,
            ];
        }
        
        // Storage disk specific recommendations
        $diskDistribution = $this->getStorageDiskDistribution();
        if ($diskDistribution['mismatches'] > 0) {
            $recommendations[] = [
                'type' => 'storage_disk_mismatches',
                'priority' => 'medium',
                'description' => "{$diskDistribution['mismatches']} storage disk mismatches found - update database records",
                'action' => 'fix_storage_disk_mismatches',
                'automated_fix_available' => true,
            ];
        }
        
        $this->recommendations = $recommendations;
    }

    /**
     * Calculate file size statistics.
     * 
     * @param array $fileSizes Array of file sizes
     * @return array Size statistics
     */
    private function calculateSizeStatistics(array $fileSizes): array
    {
        if (empty($fileSizes)) {
            return [
                'count' => 0,
                'total_size' => 0,
                'average_size' => 0,
                'median_size' => 0,
                'min_size' => 0,
                'max_size' => 0,
            ];
        }
        
        sort($fileSizes);
        $count = count($fileSizes);
        $totalSize = array_sum($fileSizes);
        
        return [
            'count' => $count,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'average_size' => round($totalSize / $count),
            'average_size_formatted' => $this->formatBytes(round($totalSize / $count)),
            'median_size' => $fileSizes[floor($count / 2)],
            'median_size_formatted' => $this->formatBytes($fileSizes[floor($count / 2)]),
            'min_size' => min($fileSizes),
            'min_size_formatted' => $this->formatBytes(min($fileSizes)),
            'max_size' => max($fileSizes),
            'max_size_formatted' => $this->formatBytes(max($fileSizes)),
        ];
    }

    /**
     * Calculate inconsistency rates.
     * 
     * @return array Inconsistency rates
     */
    private function calculateInconsistencyRates(): array
    {
        $totalProcessed = $this->getTotalProcessed();
        
        if ($totalProcessed === 0) {
            return [];
        }
        
        $breakdown = $this->getInconsistencyBreakdown();
        $rates = [];
        
        foreach ($breakdown as $type => $data) {
            $rates[$type] = [
                'count' => $data['count'],
                'rate_percentage' => round(($data['count'] / $totalProcessed) * 100, 2),
                'severity' => $data['severity'],
            ];
        }
        
        return $rates;
    }

    /**
     * Calculate URL generation statistics.
     * 
     * @return array URL generation statistics
     */
    private function calculateUrlGenerationStats(): array
    {
        $totalProcessed = $this->getTotalProcessed();
        $successfulGeneration = 0;
        $failedGeneration = 0;
        
        foreach ($this->diagnosticResults as $result) {
            if ($result->urlGenerationSuccessful()) {
                $successfulGeneration++;
            } else {
                $failedGeneration++;
            }
        }
        
        return [
            'total_attempts' => $totalProcessed,
            'successful' => $successfulGeneration,
            'failed' => $failedGeneration,
            'success_rate' => $totalProcessed > 0 ? round(($successfulGeneration / $totalProcessed) * 100, 2) : 0,
            'failure_rate' => $totalProcessed > 0 ? round(($failedGeneration / $totalProcessed) * 100, 2) : 0,
        ];
    }

    /**
     * Calculate overall storage health score.
     * 
     * @return array Storage health score
     */
    private function calculateStorageHealthScore(): array
    {
        $totalProcessed = $this->getTotalProcessed();
        
        if ($totalProcessed === 0) {
            return ['score' => 100, 'grade' => 'A', 'description' => 'No files to evaluate'];
        }
        
        $healthyCount = count($this->getDiagnosticResultsByStatus('healthy'));
        $warningCount = count($this->getDiagnosticResultsByStatus('warning'));
        $criticalCount = count($this->getDiagnosticResultsByStatus('critical'));
        
        // Calculate weighted score
        $score = (($healthyCount * 100) + ($warningCount * 70) + ($criticalCount * 0)) / $totalProcessed;
        
        $grade = 'F';
        $description = 'Poor storage health';
        
        if ($score >= 95) {
            $grade = 'A';
            $description = 'Excellent storage health';
        } elseif ($score >= 85) {
            $grade = 'B';
            $description = 'Good storage health';
        } elseif ($score >= 70) {
            $grade = 'C';
            $description = 'Fair storage health';
        } elseif ($score >= 50) {
            $grade = 'D';
            $description = 'Poor storage health';
        }
        
        return [
            'score' => round($score, 1),
            'grade' => $grade,
            'description' => $description,
            'breakdown' => [
                'healthy' => $healthyCount,
                'warning' => $warningCount,
                'critical' => $criticalCount,
            ],
        ];
    }

    /**
     * Calculate health trend (simplified implementation).
     * 
     * @return array Health trend
     */
    private function calculateHealthTrend(): array
    {
        // This is a placeholder - in a real implementation,
        // you would compare with historical data
        return [
            'direction' => 'stable',
            'confidence' => 'low',
            'note' => 'Trend analysis requires historical data',
        ];
    }

    /**
     * Identify common issues.
     * 
     * @return array Common issues
     */
    private function identifyCommonIssues(): array
    {
        $breakdown = $this->getInconsistencyBreakdown();
        $commonIssues = [];
        
        foreach ($breakdown as $type => $data) {
            if ($data['count'] >= 3) { // Consider issues affecting 3+ files as "common"
                $commonIssues[] = [
                    'type' => $type,
                    'count' => $data['count'],
                    'severity' => $data['severity'],
                    'description' => $this->getIssueDescription($type),
                ];
            }
        }
        
        return $commonIssues;
    }

    /**
     * Identify improvement areas.
     * 
     * @return array Improvement areas
     */
    private function identifyImprovementAreas(): array
    {
        $areas = [];
        
        $missingFileRate = $this->getTotalProcessed() > 0 ? 
            ($this->getFilesMissing() / $this->getTotalProcessed()) * 100 : 0;
        
        if ($missingFileRate > 5) {
            $areas[] = [
                'area' => 'file_retention',
                'description' => 'Improve file retention and backup procedures',
                'impact' => 'high',
            ];
        }
        
        $inconsistencyRate = $this->getTotalProcessed() > 0 ? 
            ($this->getInconsistenciesFound() / $this->getTotalProcessed()) * 100 : 0;
        
        if ($inconsistencyRate > 10) {
            $areas[] = [
                'area' => 'data_consistency',
                'description' => 'Implement automated consistency checks and repairs',
                'impact' => 'medium',
            ];
        }
        
        return $areas;
    }

    /**
     * Get description for issue type.
     * 
     * @param string $type Issue type
     * @return string Issue description
     */
    private function getIssueDescription(string $type): string
    {
        $descriptions = [
            'file_not_found' => 'Files are missing from storage',
            'storage_disk_mismatch' => 'Database records point to wrong storage disk',
            'file_size_mismatch' => 'File sizes in database do not match actual files',
            'file_hash_mismatch' => 'File content has been modified or corrupted',
            'storage_disk_error' => 'Storage disk access errors',
        ];
        
        return $descriptions[$type] ?? 'Unknown issue type';
    }

    /**
     * Format duration in human-readable format.
     * 
     * @param float|null $seconds Duration in seconds
     * @return string Formatted duration
     */
    private function formatDuration(?float $seconds): string
    {
        if ($seconds === null) {
            return 'N/A';
        }
        
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
}