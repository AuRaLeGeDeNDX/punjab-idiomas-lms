<?php

namespace App\Services;

use App\Models\Content;
use Carbon\Carbon;

/**
 * DiagnosticResult represents the comprehensive result of a file storage diagnostic operation.
 * 
 * This class encapsulates all diagnostic information including inconsistencies,
 * recommendations, and detailed reporting capabilities for file storage issues.
 * 
 * Requirements: 1.1, 7.1
 */
class DiagnosticResult
{
    private Content $content;
    private string $correlationId;
    private Carbon $timestamp;
    private bool $fileExists = false;
    private ?FileLocation $actualLocation = null;
    private array $inconsistencies = [];
    private ?string $generatedUrl = null;
    private ?string $urlGenerationError = null;
    private array $recommendations = [];
    private array $metadata = [];

    /**
     * Create a new DiagnosticResult instance.
     * 
     * @param Content $content The content record being diagnosed
     * @param string $correlationId Correlation ID for tracking
     */
    public function __construct(Content $content, string $correlationId)
    {
        $this->content = $content;
        $this->correlationId = $correlationId;
        $this->timestamp = now();
    }

    /**
     * Get the content record being diagnosed.
     * 
     * @return Content Content record
     */
    public function getContent(): Content
    {
        return $this->content;
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
     * Get the diagnostic timestamp.
     * 
     * @return Carbon Timestamp
     */
    public function getTimestamp(): Carbon
    {
        return $this->timestamp;
    }

    /**
     * Check if the file exists.
     * 
     * @return bool True if file exists
     */
    public function fileExists(): bool
    {
        return $this->fileExists;
    }

    /**
     * Set whether the file exists.
     * 
     * @param bool $exists File existence status
     */
    public function setFileExists(bool $exists): void
    {
        $this->fileExists = $exists;
    }

    /**
     * Get the actual file location.
     * 
     * @return FileLocation|null Actual file location or null if not found
     */
    public function getActualLocation(): ?FileLocation
    {
        return $this->actualLocation;
    }

    /**
     * Set the actual file location.
     * 
     * @param FileLocation|null $location Actual file location
     */
    public function setActualLocation(?FileLocation $location): void
    {
        $this->actualLocation = $location;
    }

    /**
     * Check if there are any inconsistencies.
     * 
     * @return bool True if inconsistencies exist
     */
    public function hasInconsistencies(): bool
    {
        return !empty($this->inconsistencies);
    }

    /**
     * Get all inconsistencies.
     * 
     * @return array Array of inconsistencies
     */
    public function getInconsistencies(): array
    {
        return $this->inconsistencies;
    }

    /**
     * Add an inconsistency.
     * 
     * @param string $type Inconsistency type
     * @param array $details Inconsistency details
     */
    public function addInconsistency(string $type, array $details): void
    {
        $this->inconsistencies[$type] = array_merge($details, [
            'detected_at' => now()->toISOString(),
            'severity' => $this->determineInconsistencySeverity($type),
        ]);
    }

    /**
     * Get inconsistencies by severity.
     * 
     * @param string $severity Severity level (critical, high, medium, low)
     * @return array Inconsistencies of specified severity
     */
    public function getInconsistenciesBySeverity(string $severity): array
    {
        return array_filter($this->inconsistencies, function($inconsistency) use ($severity) {
            return ($inconsistency['severity'] ?? 'medium') === $severity;
        });
    }

    /**
     * Get the highest severity level of inconsistencies.
     * 
     * @return string Highest severity level
     */
    public function getHighestSeverity(): string
    {
        if (empty($this->inconsistencies)) {
            return 'none';
        }
        
        $severityLevels = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
        $maxSeverity = 'low';
        $maxLevel = 1;
        
        foreach ($this->inconsistencies as $inconsistency) {
            $severity = $inconsistency['severity'] ?? 'medium';
            $level = $severityLevels[$severity] ?? 2;
            
            if ($level > $maxLevel) {
                $maxLevel = $level;
                $maxSeverity = $severity;
            }
        }
        
        return $maxSeverity;
    }

    /**
     * Get the generated URL.
     * 
     * @return string|null Generated URL or null if not generated
     */
    public function getGeneratedUrl(): ?string
    {
        return $this->generatedUrl;
    }

    /**
     * Set the generated URL.
     * 
     * @param string|null $url Generated URL
     */
    public function setGeneratedUrl(?string $url): void
    {
        $this->generatedUrl = $url;
    }

    /**
     * Get URL generation error.
     * 
     * @return string|null URL generation error or null if no error
     */
    public function getUrlGenerationError(): ?string
    {
        return $this->urlGenerationError;
    }

    /**
     * Set URL generation error.
     * 
     * @param string|null $error URL generation error
     */
    public function setUrlGenerationError(?string $error): void
    {
        $this->urlGenerationError = $error;
    }

    /**
     * Check if URL generation was successful.
     * 
     * @return bool True if URL was generated successfully
     */
    public function urlGenerationSuccessful(): bool
    {
        return $this->generatedUrl !== null && $this->urlGenerationError === null;
    }

    /**
     * Get all recommendations.
     * 
     * @return array Array of recommendations
     */
    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    /**
     * Set recommendations.
     * 
     * @param array $recommendations Array of recommendations
     */
    public function setRecommendations(array $recommendations): void
    {
        $this->recommendations = $recommendations;
    }

    /**
     * Add a recommendation.
     * 
     * @param array $recommendation Recommendation details
     */
    public function addRecommendation(array $recommendation): void
    {
        $this->recommendations[] = array_merge($recommendation, [
            'added_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get recommendations by priority.
     * 
     * @param string $priority Priority level (critical, high, medium, low)
     * @return array Recommendations of specified priority
     */
    public function getRecommendationsByPriority(string $priority): array
    {
        return array_filter($this->recommendations, function($recommendation) use ($priority) {
            return ($recommendation['priority'] ?? 'medium') === $priority;
        });
    }

    /**
     * Get recommendations that can be automatically fixed.
     * 
     * @return array Recommendations with automated fixes available
     */
    public function getAutomaticFixRecommendations(): array
    {
        return array_filter($this->recommendations, function($recommendation) {
            return $recommendation['automated_fix_available'] ?? false;
        });
    }

    /**
     * Get metadata.
     * 
     * @return array Diagnostic metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set metadata.
     * 
     * @param array $metadata Diagnostic metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * Add metadata entry.
     * 
     * @param string $key Metadata key
     * @param mixed $value Metadata value
     */
    public function addMetadata(string $key, $value): void
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Get overall diagnostic status.
     * 
     * @return string Overall status (healthy, warning, critical)
     */
    public function getOverallStatus(): string
    {
        if (!$this->fileExists) {
            return 'critical';
        }
        
        $highestSeverity = $this->getHighestSeverity();
        
        switch ($highestSeverity) {
            case 'critical':
                return 'critical';
            case 'high':
                return 'warning';
            case 'medium':
            case 'low':
                return $this->hasInconsistencies() ? 'warning' : 'healthy';
            default:
                return 'healthy';
        }
    }

    /**
     * Check if the diagnostic result indicates a healthy file.
     * 
     * @return bool True if file is healthy
     */
    public function isHealthy(): bool
    {
        return $this->getOverallStatus() === 'healthy';
    }

    /**
     * Check if the diagnostic result requires immediate attention.
     * 
     * @return bool True if immediate attention is required
     */
    public function requiresImmediateAttention(): bool
    {
        return $this->getOverallStatus() === 'critical' || 
               !empty($this->getRecommendationsByPriority('critical')) ||
               !empty($this->getRecommendationsByPriority('high'));
    }

    /**
     * Get recommended actions based on diagnostic results.
     * 
     * @return array Array of recommended actions
     */
    public function getRecommendedActions(): array
    {
        $actions = [];
        
        if (!$this->fileExists) {
            $actions[] = [
                'action' => 'investigate_missing_file',
                'description' => 'File not found - investigate if file was deleted or moved',
                'priority' => 'critical',
                'automated' => false,
            ];
        }
        
        if ($this->hasInconsistencies()) {
            foreach ($this->inconsistencies as $type => $inconsistency) {
                switch ($type) {
                    case 'storage_disk_mismatch':
                        $actions[] = [
                            'action' => 'update_storage_disk_field',
                            'description' => 'Update database record to match actual file location',
                            'priority' => 'medium',
                            'automated' => true,
                            'fix_data' => [
                                'content_id' => $this->content->id,
                                'new_storage_disk' => $inconsistency['actual_disk'],
                            ],
                        ];
                        break;
                        
                    case 'file_size_mismatch':
                        $actions[] = [
                            'action' => 'update_file_size',
                            'description' => 'Update file size in database to match actual file',
                            'priority' => 'low',
                            'automated' => true,
                            'fix_data' => [
                                'content_id' => $this->content->id,
                                'new_file_size' => $inconsistency['actual_size'],
                            ],
                        ];
                        break;
                        
                    case 'file_hash_mismatch':
                        $actions[] = [
                            'action' => 'investigate_file_corruption',
                            'description' => 'File content has changed - investigate potential corruption',
                            'priority' => 'high',
                            'automated' => false,
                        ];
                        break;
                }
            }
        }
        
        if (!$this->urlGenerationSuccessful()) {
            $actions[] = [
                'action' => 'fix_url_generation',
                'description' => 'Fix URL generation issues for file access',
                'priority' => 'medium',
                'automated' => false,
            ];
        }
        
        return $actions;
    }

    /**
     * Generate a summary of the diagnostic results.
     * 
     * @return array Summary information
     */
    public function getSummary(): array
    {
        return [
            'content_id' => $this->content->id,
            'correlation_id' => $this->correlationId,
            'timestamp' => $this->timestamp->toISOString(),
            'overall_status' => $this->getOverallStatus(),
            'file_exists' => $this->fileExists,
            'file_location' => $this->actualLocation?->toArray(),
            'inconsistencies_count' => count($this->inconsistencies),
            'highest_severity' => $this->getHighestSeverity(),
            'url_generation_successful' => $this->urlGenerationSuccessful(),
            'recommendations_count' => count($this->recommendations),
            'automatic_fixes_available' => count($this->getAutomaticFixRecommendations()),
            'requires_immediate_attention' => $this->requiresImmediateAttention(),
        ];
    }

    /**
     * Convert to array representation.
     * 
     * @return array Array representation of the diagnostic result
     */
    public function toArray(): array
    {
        return [
            'content' => [
                'id' => $this->content->id,
                'type' => $this->content->type,
                'recorded_file_path' => $this->content->file_path,
                'recorded_storage_disk' => $this->content->storage_disk,
                'recorded_file_size' => $this->content->file_size,
                'recorded_file_hash' => $this->content->file_hash,
            ],
            'diagnostic_info' => [
                'correlation_id' => $this->correlationId,
                'timestamp' => $this->timestamp->toISOString(),
                'overall_status' => $this->getOverallStatus(),
            ],
            'file_status' => [
                'exists' => $this->fileExists,
                'actual_location' => $this->actualLocation?->toArray(),
                'url_generated' => $this->generatedUrl,
                'url_generation_error' => $this->urlGenerationError,
            ],
            'inconsistencies' => $this->inconsistencies,
            'recommendations' => $this->recommendations,
            'metadata' => $this->metadata,
            'summary' => $this->getSummary(),
        ];
    }

    /**
     * Convert to JSON representation.
     * 
     * @return string JSON representation
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Determine the severity of an inconsistency type.
     * 
     * @param string $type Inconsistency type
     * @return string Severity level
     */
    private function determineInconsistencySeverity(string $type): string
    {
        $severityMap = [
            'file_not_found' => 'critical',
            'file_not_at_recorded_location' => 'high',
            'storage_disk_mismatch' => 'medium',
            'file_size_mismatch' => 'low',
            'file_hash_mismatch' => 'high',
            'storage_disk_error' => 'high',
        ];
        
        return $severityMap[$type] ?? 'medium';
    }
}