<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Content;

/**
 * Simple FileOperationLogger for testing autoloader issues.
 */
class FileOperationLoggerSimple
{
    public function logFileServingError(Content $content, string $error, array $context = []): string
    {
        $correlationId = Str::uuid()->toString();
        Log::error('File serving error', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'error' => $error,
            'context' => $context,
        ]);
        return $correlationId;
    }

    public function logFileAccess(Content $content, string $operation, array $context = []): string
    {
        $correlationId = Str::uuid()->toString();
        Log::info('File access', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'operation' => $operation,
            'context' => $context,
        ]);
        return $correlationId;
    }

    public function logFileAccessDenial(Content $content, string $reason, array $context = []): string
    {
        $correlationId = Str::uuid()->toString();
        Log::warning('File access denied', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'reason' => $reason,
            'context' => $context,
        ]);
        return $correlationId;
    }

    public function logStorageInconsistency(Content $content, array $inconsistencies): string
    {
        $correlationId = Str::uuid()->toString();
        Log::error('Storage inconsistency detected', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'inconsistencies' => $inconsistencies,
        ]);
        return $correlationId;
    }

    public function logUrlGenerationFailure(Content $content, string $failureReason, array $context = []): string
    {
        $correlationId = Str::uuid()->toString();
        Log::error('URL generation failure', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'failure_reason' => $failureReason,
            'context' => $context,
        ]);
        return $correlationId;
    }
}