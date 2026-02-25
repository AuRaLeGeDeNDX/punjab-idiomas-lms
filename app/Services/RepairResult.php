<?php

namespace App\Services;

use App\Models\Content;

class RepairResult
{
    private Content $content;
    private string $action;
    private bool $success;
    private ?string $error;
    private ?FileLocation $actualLocation;
    private ?array $searchedLocations;
    private string $correlationId;

    private function __construct(
        Content $content,
        string $action,
        bool $success,
        ?string $error = null,
        ?FileLocation $actualLocation = null,
        ?array $searchedLocations = null,
        string $correlationId = ''
    ) {
        $this->content = $content;
        $this->action = $action;
        $this->success = $success;
        $this->error = $error;
        $this->actualLocation = $actualLocation;
        $this->searchedLocations = $searchedLocations;
        $this->correlationId = $correlationId;
    }

    public static function success(Content $content, FileLocation $actualLocation, string $correlationId): self
    {
        return new self($content, 'repaired', true, null, $actualLocation, null, $correlationId);
    }

    public static function noActionNeeded(Content $content, string $correlationId): self
    {
        return new self($content, 'no_action_needed', true, null, null, null, $correlationId);
    }

    public static function fileNotFound(Content $content, array $searchedLocations, string $correlationId): self
    {
        return new self($content, 'file_not_found', false, 'File not found in any location', null, $searchedLocations, $correlationId);
    }

    public static function error(Content $content, string $error, string $correlationId): self
    {
        return new self($content, 'error', false, $error, null, null, $correlationId);
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getActualLocation(): ?FileLocation
    {
        return $this->actualLocation;
    }

    public function getSearchedLocations(): ?array
    {
        return $this->searchedLocations;
    }

    public function getDescription(): string
    {
        switch ($this->action) {
            case 'repaired':
                return 'File path inconsistencies were successfully repaired';
            case 'no_action_needed':
                return 'No inconsistencies found, no action needed';
            case 'file_not_found':
                return 'File not found in any storage location';
            case 'error':
                return $this->error ?? 'An error occurred during repair';
            default:
                return 'Unknown repair action';
        }
    }

    public function hasChanges(): bool
    {
        return $this->action === 'repaired';
    }

    public function getChanges(): array
    {
        if (!$this->hasChanges() || !$this->actualLocation) {
            return [];
        }

        return [
            'storage_disk' => $this->actualLocation->getDisk(),
            'file_path' => $this->actualLocation->getPath(),
        ];
    }

    public function getMetadata(): array
    {
        return [
            'correlation_id' => $this->correlationId,
            'content_id' => $this->content->id,
            'action' => $this->action,
            'success' => $this->success,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }
}