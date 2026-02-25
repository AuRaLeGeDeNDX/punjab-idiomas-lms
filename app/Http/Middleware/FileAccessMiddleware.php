<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FileAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log file access attempts for security monitoring
        Log::info('File access attempt', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);

        // Check for suspicious patterns
        $suspiciousPatterns = [
            '../',
            '..\\',
            '/etc/',
            '/var/',
            '/usr/',
            'C:\\',
            'D:\\',
            '.env',
            'config',
            'database',
        ];

        $requestUri = $request->getRequestUri();
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($requestUri, $pattern) !== false) {
                Log::warning('Suspicious file access attempt detected', [
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'pattern' => $pattern,
                    'url' => $requestUri,
                ]);

                abort(403, 'Access denied');
            }
        }

        // Rate limiting for file downloads
        $key = 'file_downloads:' . auth()->id() . ':' . $request->ip();
        $maxAttempts = 100; // Max 100 downloads per hour
        $decayMinutes = 60;

        if (app('cache')->has($key)) {
            $attempts = app('cache')->get($key, 0);
            if ($attempts >= $maxAttempts) {
                Log::warning('File download rate limit exceeded', [
                    'user_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'attempts' => $attempts,
                ]);

                abort(429, 'Too many download attempts. Please try again later.');
            }
        }

        // Increment download counter
        $attempts = app('cache')->get($key, 0) + 1;
        app('cache')->put($key, $attempts, $decayMinutes * 60);

        return $next($request);
    }
}