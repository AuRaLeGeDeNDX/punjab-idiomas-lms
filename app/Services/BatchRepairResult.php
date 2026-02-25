<?php

namespace App\Services;

class BatchRepairResult
{
    private array $results;
    private string $correlationId;
    private int $totalProcessed;
    private int $successfulRepairs;
    private int $failedRepairs;
    private int $skippedRepairs;
    private int $databaseUpdates;
    private int $filesNotFound;
    private float $duration;
    private \DateTime $startTime;
    private ?\DateTime $endTime = null;

    public function __construct(string $correlationId, array $results = [])
    {
        $this->correlationId = $correlationId;
        $this->results = $results;
        $this->startTime = new \DateTime();
        $this->databaseUpdates = 0;
        $this->filesNotFound = 0;
        $this->duration = 0.0;
        
        if (!empty($results)) {
            $this->calculateStats();
        } else {
            $this->resetStats();
        }
    }

    public function addRepairResult(RepairResult $result): void
    {
        $this->results[] = $result;
        $this->calculateStats();
    }

    public function complete(): void
    {
        $this->endTime = new \DateTime();
        $this->duration = $this->endTime->getTimestamp() - $this->startTime->getTimestamp();
    }

    private function resetStats(): void
    {
        $this->totalProcessed = 0;
        $this->successfulRepairs = 0;
        $this->failedRepairs = 0;
        $this->skippedRepairs = 0;
    }

    private function calculateStats(): void
    {
        $this->totalProcessed = count($this->results);
        $this->successfulRepairs = 0;
        $this->failedRepairs = 0;
        $this->skippedRepairs = 0;
        $this->databaseUpdates = 0;
        $this->filesNotFound = 0;

        foreach ($this->results as $result) {
            if ($result instanceof RepairResult) {
                if ($result->isSuccess()) {
                    if ($result->getAction() === 'repaired') {
                        $this->successfulRepairs++;
                        $this->databaseUpdates++;
                    } else {
                        $this->skippedRepairs++;
                    }
                } else {
                    $this->failedRepairs++;
                    if ($result->getAction() === 'file_not_found') {
                        $this->filesNotFound++;
                    }
                }
            }
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function getTotalProcessed(): int
    {
        return $this->totalProcessed;
    }

    public function getSuccessfulRepairs(): int
    {
        return $this->successfulRepairs;
    }

    public function getFailedRepairs(): int
    {
        return $this->failedRepairs;
    }

    public function getSkippedRepairs(): int
    {
        return $this->skippedRepairs;
    }

    public function getDatabaseUpdates(): int
    {
        return $this->databaseUpdates;
    }

    public function getFilesNotFound(): int
    {
        return $this->filesNotFound;
    }

    public function getSuccessRate(): float
    {
        if ($this->totalProcessed === 0) {
            return 0.0;
        }
        return ($this->successfulRepairs / $this->totalProcessed) * 100;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getOverallStatus(): string
    {
        if ($this->failedRepairs === 0) {
            return 'success';
        } elseif ($this->successfulRepairs > 0) {
            return 'partial_success';
        } else {
            return 'failure';
        }
    }

    public function isOverallSuccess(): bool
    {
        return $this->failedRepairs === 0;
    }

    public function getSummary(): array
    {
        return [
            'processing_stats' => [
                'total_processed' => $this->totalProcessed,
                'successful_repairs' => $this->successfulRepairs,
                'failed_repairs' => $this->failedRepairs,
                'skipped_repairs' => $this->skippedRepairs,
                'no_action_needed' => $this->skippedRepairs, // Alias for skipped_repairs
                'database_updates' => $this->databaseUpdates,
                'files_not_found' => $this->filesNotFound,
                'success_rate' => $this->getSuccessRate(),
                'repair_rate' => $this->getSuccessRate(), // Alias for success_rate
                'duration' => $this->duration,
                'overall_status' => $this->getOverallStatus(),
            ],
            'correlation_id' => $this->correlationId,
            'start_time' => $this->startTime->format('Y-m-d H:i:s'),
            'end_time' => $this->endTime ? $this->endTime->format('Y-m-d H:i:s') : null,
        ];
    }
}