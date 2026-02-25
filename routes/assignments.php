<?php

use App\Http\Controllers\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Teacher\SubmissionController;
use App\Http\Controllers\SubmissionFileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Assignment Routes
|--------------------------------------------------------------------------
|
| Routes for assignment management, submissions, and grading.
| Organized by role (teacher/student) with proper middleware.
|
*/

// Secure File Download Routes (with signed URL validation)
Route::middleware(['auth'])->group(function () {
    Route::get('/submission-files/{submissionFile}/download', [SubmissionFileController::class, 'download'])
        ->name('submission.file.download')
        ->middleware('signed');
    
    Route::get('/assignments/files/download', [SubmissionFileController::class, 'downloadByPath'])
        ->name('assignments.files.download')
        ->middleware('signed');
});

// Teacher Assignment Routes
Route::middleware(['auth', 'role:Teacher|Admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::prefix('courses/{course}/modules/{module}/subpages/{subpage}')->name('courses.modules.subpages.')->group(function () {
        // Assignment management
        Route::resource('assignments', TeacherAssignmentController::class)->except(['index']);
        Route::get('assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
        
        // Assignment actions
        Route::post('assignments/{assignment}/publish', [TeacherAssignmentController::class, 'publish'])->name('assignments.publish');
        Route::post('assignments/{assignment}/unpublish', [TeacherAssignmentController::class, 'unpublish'])->name('assignments.unpublish');
        Route::post('assignments/{assignment}/send-reminders', [TeacherAssignmentController::class, 'sendReminders'])->name('assignments.send-reminders');
        Route::post('assignments/{assignment}/cancel-schedule', [TeacherAssignmentController::class, 'cancelSchedule'])->name('assignments.cancel-schedule');
        Route::post('assignments/reorder', [TeacherAssignmentController::class, 'reorder'])->name('assignments.reorder');
        Route::get('assignments/trashed/view', [TeacherAssignmentController::class, 'trashed'])->name('assignments.trashed');
        Route::post('assignments/{assignmentId}/restore', [TeacherAssignmentController::class, 'restore'])->name('assignments.restore');
        Route::delete('assignments/{assignmentId}/force-delete', [TeacherAssignmentController::class, 'forceDelete'])->name('assignments.force-delete');
        Route::delete('assignments/trash/empty', [TeacherAssignmentController::class, 'emptyTrash'])->name('assignments.empty-trash');
        
        // Submission management
        Route::prefix('assignments/{assignment}')->group(function () {
            Route::get('submissions', [SubmissionController::class, 'index'])->name('assignments.submissions.index');
            Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('assignments.submissions.show');
            Route::get('submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('assignments.submissions.grade');
            Route::post('submissions/{submission}/grade', [SubmissionController::class, 'storeGrade'])->name('assignments.submissions.store-grade');
            Route::post('submissions/{submission}/publish-grade', [SubmissionController::class, 'publishGrade'])->name('assignments.submissions.publish-grade');
            Route::get('submissions/{submission}/download/{filename}', [SubmissionController::class, 'downloadFile'])->name('assignments.submissions.download');
        });
    });
});

// Student Assignment Routes
Route::middleware(['auth', 'role:Student'])->prefix('student')->name('student.')->group(function () {
    // General assignments overview
    Route::get('assignments', [StudentAssignmentController::class, 'overview'])->name('assignments.overview');
    
    Route::prefix('courses/{course}/modules/{module}/subpages/{subpage}')->name('courses.modules.subpages.')->group(function () {
        // Assignment viewing
        Route::get('assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
        
        // Submission management
        Route::prefix('assignments/{assignment}')->group(function () {
            Route::get('submit', [StudentAssignmentController::class, 'createSubmission'])->name('assignments.submit');
            Route::post('submit', [StudentAssignmentController::class, 'storeSubmission'])->name('assignments.store-submission');
            Route::get('submissions/{submission}/edit', [StudentAssignmentController::class, 'editSubmission'])->name('assignments.submissions.edit');
            Route::put('submissions/{submission}', [StudentAssignmentController::class, 'updateSubmission'])->name('assignments.submissions.update');
            Route::get('submissions/{submission}/download/{filename}', [StudentAssignmentController::class, 'downloadFile'])->name('assignments.submissions.download');
        });
    });
});

// Admin Assignment Routes (inherits teacher routes with admin role)
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('courses/{course}/modules/{module}/subpages/{subpage}')->name('courses.modules.subpages.')->group(function () {
        // Assignment management (same as teacher)
        Route::resource('assignments', TeacherAssignmentController::class)->except(['index']);
        Route::get('assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
        
        // Assignment actions
        Route::post('assignments/{assignment}/publish', [TeacherAssignmentController::class, 'publish'])->name('assignments.publish');
        Route::post('assignments/{assignment}/unpublish', [TeacherAssignmentController::class, 'unpublish'])->name('assignments.unpublish');
        Route::post('assignments/{assignment}/send-reminders', [TeacherAssignmentController::class, 'sendReminders'])->name('assignments.send-reminders');
        Route::post('assignments/{assignment}/cancel-schedule', [TeacherAssignmentController::class, 'cancelSchedule'])->name('assignments.cancel-schedule');
        Route::post('assignments/reorder', [TeacherAssignmentController::class, 'reorder'])->name('assignments.reorder');
        Route::get('assignments/trashed/view', [TeacherAssignmentController::class, 'trashed'])->name('assignments.trashed');
        Route::post('assignments/{assignmentId}/restore', [TeacherAssignmentController::class, 'restore'])->name('assignments.restore');
        Route::delete('assignments/{assignmentId}/force-delete', [TeacherAssignmentController::class, 'forceDelete'])->name('assignments.force-delete');
        Route::delete('assignments/trash/empty', [TeacherAssignmentController::class, 'emptyTrash'])->name('assignments.empty-trash');
        
        // Submission management
        Route::prefix('assignments/{assignment}')->group(function () {
            Route::get('submissions', [SubmissionController::class, 'index'])->name('assignments.submissions.index');
            Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('assignments.submissions.show');
            Route::get('submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('assignments.submissions.grade');
            Route::post('submissions/{submission}/grade', [SubmissionController::class, 'storeGrade'])->name('assignments.submissions.store-grade');
            Route::post('submissions/{submission}/publish-grade', [SubmissionController::class, 'publishGrade'])->name('assignments.submissions.publish-grade');
            Route::get('submissions/{submission}/download/{filename}', [SubmissionController::class, 'downloadFile'])->name('assignments.submissions.download');
        });
    });
});