<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'file.access' => \App\Http\Middleware\FileAccessMiddleware::class,
            'prevent.idor' => \App\Http\Middleware\PreventIDORMiddleware::class,
            'performance' => \App\Http\Middleware\PerformanceOptimizationMiddleware::class,
            'exercise.security' => \App\Http\Middleware\ExerciseSecurityMiddleware::class,
            'storage.validation' => \App\Http\Middleware\StorageValidationMiddleware::class,
        ]);
        
        // Exclude PDF stream route from CSRF verification
        // This route uses signed URLs for security and is accessed by PDF.js
        // PDF.js doesn't send CSRF tokens, so we exclude this route
        // Requirements: 1.2, 4.1
        $middleware->validateCsrfTokens(except: [
            'secure-pdf/stream/*',
        ]);
        
        // Add global middleware
        $middleware->web([
            \App\Http\Middleware\PerformanceOptimizationMiddleware::class,
        ]);
        
        // Add throttle to API and storage validation
        $middleware->api([
            'throttle:60,1',
            \App\Http\Middleware\StorageValidationMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
