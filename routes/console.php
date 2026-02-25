<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule auto-publication of assignments every minute
Schedule::command('assignments:publish-scheduled')->everyMinute();

// ── Backup Schedules ──────────────────────────────────────────────────────────
// Weekly full DB backup — every Sunday at 2:00 AM
Schedule::command('backup:database')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Monthly full backup with R2 summary — 1st of each month at 3:00 AM
Schedule::command('backup:full')
    ->monthly()
    ->at('03:00')
    ->withoutOverlapping()
    ->runInBackground();

Artisan::command('open:index {--legacy}', function () {
    $legacy = $this->option('legacy');
    $path = $legacy 
        ? base_path('_trash/index.php') 
        : resource_path('views/landing.blade.php');

    if (file_exists($path)) {
        $this->info("Opening code for: " . str_replace(base_path(), '', $path));
        $this->line(file_get_contents($path));
    } else {
        $this->error("File not found: " . str_replace(base_path(), '', $path));
    }
})->purpose('Display the code of the landing page view (or legacy index.php with --legacy)');

