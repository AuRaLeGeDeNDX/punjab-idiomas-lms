<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExerciseSecurityMiddleware
{
    /**
     * Handle an incoming request.
     * SECURITY: Prevent any potential answer leakage through various attack vectors.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only apply security checks to students
        if (!auth()->check() || !auth()->user()->hasRole('student')) {
            return $response;
        }

        // SECURITY CHECK 1: Scan response content for potential answer leakage
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $content = $response->getData(true);
            $this->scanForAnswerLeakage($content, $request);
        } elseif ($response instanceof \Illuminate\Http\Response) {
            $content = $response->getContent();
            $this->scanResponseContent($content, $request);
        }

        // SECURITY CHECK 2: Remove any potential answer fields from JSON responses
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            $cleanedData = $this->removeAnswerFields($data);
            $response->setData($cleanedData);
        }

        return $response;
    }

    /**
     * SECURITY: Scan for potential answer leakage in JSON responses.
     */
    private function scanForAnswerLeakage(array $data, Request $request): void
    {
        $suspiciousFields = ['answer', 'correct_answer', 'solution', 'key'];
        
        $this->recursiveScan($data, $suspiciousFields, function($field, $value) use ($request) {
            Log::warning('SECURITY ALERT: Potential answer leakage detected', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'field' => $field,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]);
        });
    }

    /**
     * SECURITY: Scan HTML content for potential answer exposure.
     */
    private function scanResponseContent(string $content, Request $request): void
    {
        // Look for suspicious patterns that might indicate answer leakage
        $patterns = [
            '/answer["\']?\s*:\s*["\']([^"\']+)["\']/',
            '/correct["\']?\s*:\s*["\']([^"\']+)["\']/',
            '/solution["\']?\s*:\s*["\']([^"\']+)["\']/',
            '/<input[^>]*name=["\']answer["\'][^>]*value=["\']([^"\']+)["\']/',
            '/data-answer=["\']([^"\']+)["\']/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                Log::warning('SECURITY ALERT: Potential answer in HTML content', [
                    'user_id' => auth()->id(),
                    'user_email' => auth()->user()->email,
                    'pattern' => $pattern,
                    'match' => $matches[1] ?? 'N/A',
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'timestamp' => now()->toISOString(),
                ]);
            }
        }
    }

    /**
     * SECURITY: Recursively scan array for suspicious fields.
     */
    private function recursiveScan(array $data, array $suspiciousFields, callable $callback, string $path = ''): void
    {
        foreach ($data as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : $key;
            
            if (in_array(strtolower($key), $suspiciousFields)) {
                $callback($currentPath, $value);
            }
            
            if (is_array($value)) {
                $this->recursiveScan($value, $suspiciousFields, $callback, $currentPath);
            }
        }
    }

    /**
     * SECURITY: Remove answer fields from data structure.
     */
    private function removeAnswerFields(array $data): array
    {
        $forbiddenFields = ['answer', 'correct_answer', 'solution', 'key'];
        
        return $this->recursiveClean($data, $forbiddenFields);
    }

    /**
     * SECURITY: Recursively clean data structure.
     */
    private function recursiveClean(array $data, array $forbiddenFields): array
    {
        $cleaned = [];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $forbiddenFields)) {
                // Skip forbidden fields entirely
                continue;
            }
            
            if (is_array($value)) {
                $cleaned[$key] = $this->recursiveClean($value, $forbiddenFields);
            } else {
                $cleaned[$key] = $value;
            }
        }
        
        return $cleaned;
    }
}