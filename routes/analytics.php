<?php

use App\Http\Controllers\Teacher\AnalyticsController as TeacherAnalyticsController;
use App\Http\Controllers\Student\AnalyticsController as StudentAnalyticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Analytics Routes
|--------------------------------------------------------------------------
|
| Routes for analytics dashboards and reports for teachers and students.
| Provides comprehensive metrics for assignment performance and progress tracking.
|
*/

// Teacher Analytics Routes
Route::middleware(['auth', 'role:Teacher,Admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/courses/{course}/analytics', [TeacherAnalyticsController::class, 'dashboard'])
        ->name('analytics.dashboard');
    
    Route::get('/assignments/{assignment}/analytics', [TeacherAnalyticsController::class, 'assignment'])
        ->name('analytics.assignment');
    
    Route::get('/courses/{course}/analytics/export', [TeacherAnalyticsController::class, 'exportCsv'])
        ->name('analytics.export');
});

// Student Analytics Routes
Route::middleware(['auth', 'role:Student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/analytics/dashboard', [StudentAnalyticsController::class, 'dashboard'])
        ->name('analytics.dashboard');
});
