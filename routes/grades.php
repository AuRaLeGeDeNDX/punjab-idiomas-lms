<?php

use App\Http\Controllers\Teacher\GradeBookController;
use App\Http\Controllers\Student\GradeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Grade Routes
|--------------------------------------------------------------------------
|
| Routes for the comprehensive grading system including grade books,
| grade management, and grade viewing for both teachers and students.
|
*/

// Teacher Grade Management Routes
Route::middleware(['auth', 'role:Teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // Grade Book Routes
    Route::get('/courses/{course}/gradebook', [GradeBookController::class, 'index'])
        ->name('gradebook.index');
    
    Route::get('/assignments/{assignment}/submissions/{submission}/grade', [GradeBookController::class, 'grade'])
        ->name('gradebook.grade');
    
    Route::post('/assignments/{assignment}/submissions/{submission}/grade', [GradeBookController::class, 'storeGrade'])
        ->name('gradebook.store-grade');
    
    Route::post('/assignments/{assignment}/submissions/{submission}/save-draft', [GradeBookController::class, 'saveDraft'])
        ->name('gradebook.save-draft');
    
    Route::post('/assignments/{assignment}/publish-grades', [GradeBookController::class, 'publishGrades'])
        ->name('gradebook.publish-grades');
    
    Route::get('/courses/{course}/gradebook/export', [GradeBookController::class, 'export'])
        ->name('gradebook.export');
    
    // Grading Configuration Routes
    Route::get('/courses/{course}/grading-configuration', [GradeBookController::class, 'configuration'])
        ->name('gradebook.configuration');
    
    Route::put('/courses/{course}/grading-configuration', [GradeBookController::class, 'updateConfiguration'])
        ->name('gradebook.update-configuration');
    
    // AJAX Routes
    Route::get('/assignments/{assignment}/stats', [GradeBookController::class, 'assignmentStats'])
        ->name('gradebook.assignment-stats');
    
    // Grade Locking Routes
    Route::post('/grades/{grade}/lock', [GradeBookController::class, 'lock'])
        ->name('gradebook.lock');
    
    Route::post('/grades/{grade}/unlock', [GradeBookController::class, 'unlock'])
        ->name('gradebook.unlock');
    
    // Override History (teachers can view their own grade overrides)
    Route::get('/grades/{grade}/override-history', [GradeBookController::class, 'overrideHistory'])
        ->name('gradebook.override-history');
});

// Student Grade Viewing Routes
Route::middleware(['auth', 'role:Student'])->prefix('student')->name('student.')->group(function () {
    // Grade Overview Routes
    Route::get('/grades', [GradeController::class, 'index'])
        ->name('grades.index');
    
    Route::get('/courses/{course}/grades', [GradeController::class, 'course'])
        ->name('grades.course');
    
    Route::get('/assignments/{assignment}/grade', [GradeController::class, 'assignment'])
        ->name('grades.assignment');
    
    // Grade Statistics and Transcript Routes
    Route::get('/grades/statistics', [GradeController::class, 'statistics'])
        ->name('grades.statistics');
    
    Route::get('/transcript', [GradeController::class, 'transcript'])
        ->name('grades.transcript');
});

// Admin Grade Management Routes (if needed)
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin can access all grade books
    Route::get('/courses/{course}/gradebook', [GradeBookController::class, 'index'])
        ->name('gradebook.index');
    
    Route::get('/courses/{course}/gradebook/export', [GradeBookController::class, 'export'])
        ->name('gradebook.export');
    
    // Admin Grade Override Routes
    Route::get('/grades/{grade}/override', [GradeBookController::class, 'showOverride'])
        ->name('gradebook.show-override');
    
    Route::post('/grades/{grade}/override', [GradeBookController::class, 'override'])
        ->name('gradebook.override');
    
    Route::get('/grades/{grade}/override-history', [GradeBookController::class, 'overrideHistory'])
        ->name('gradebook.override-history');
});