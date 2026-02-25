<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboard;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\ModuleController as TeacherModuleController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\FileController;

Route::get('/', [HomeController::class, 'index']);

// Dashboard redirect route - redirects to role-specific dashboard
Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware('auth')->name('dashboard');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public Contact Form Route
Route::post('/contact/submit', [\App\Http\Controllers\ContactController::class, 'store'])->name('contact.submit');

// File Routes (authenticated users only)
Route::middleware(['auth', 'file.access'])->group(function () {
    Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::get('/files/download/{token}', [FileController::class, 'download'])->name('files.download');
    Route::get('/files/{fileUpload}', [FileController::class, 'show'])->name('files.show');
    Route::delete('/files/{fileUpload}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::post('/files/{fileUpload}/version', [FileController::class, 'createVersion'])->name('files.create-version');
    Route::get('/files/{fileUpload}/versions', [FileController::class, 'versions'])->name('files.versions');
    Route::get('/storage/stats', [FileController::class, 'storageStats'])->name('files.storage-stats');
});

// Secure File Routes
require __DIR__.'/secure-files.php';

// Secure Media Streaming Routes
Route::middleware(['auth'])->group(function () {
    // Video streaming
    Route::get('/secure/video/{content}', [\App\Http\Controllers\SecureVideoController::class, 'stream'])
        ->name('secure.video.stream')
        ->middleware('signed');
    
    // Enhanced PDF.js Viewer Routes (Primary PDF System)
    Route::prefix('secure-pdf')->group(function () {
        // PDF.js viewer page with anti-download protections (requires auth)
        Route::get('/viewer/{content}/{token}', [\App\Http\Controllers\SecurePdfController::class, 'viewer'])
            ->name('secure.pdf.viewer');
        
        // Log page views (AJAX endpoint, requires auth)
        Route::post('/log-page-view/{content}', [\App\Http\Controllers\SecurePdfController::class, 'logPageView'])
            ->name('secure.pdf.log-page-view');
        
        // Get viewing statistics (requires auth)
        Route::get('/stats/{content}', [\App\Http\Controllers\SecurePdfController::class, 'getStats'])
            ->name('secure.pdf.stats');
        
        // Test route for secure PDF viewer (development/testing only, requires auth)
        Route::get('/test', [\App\Http\Controllers\SecurePdfController::class, 'test'])
            ->name('secure.pdf.test');
    });
    
    // Audio streaming (with Range support for seeking)
    Route::get('/secure/audio/{content}', [\App\Http\Controllers\SecureMediaController::class, 'streamAudio'])
        ->name('secure.audio.stream')
        ->middleware('signed');
    
    // Image serving
    Route::get('/secure/image/{content}', [\App\Http\Controllers\SecureMediaController::class, 'serveImage'])
        ->name('secure.image.serve')
        ->middleware('signed');
});

// PDF data stream for PDF.js (signed URL required, NO auth middleware)
// This endpoint is accessed by PDF.js and relies on signed URL for security
// NOTE: We handle signature validation manually in the controller to provide
// better error responses for PDF.js instead of Laravel's default 403/204
Route::get('/secure-pdf/stream/{content}', [\App\Http\Controllers\SecurePdfController::class, 'stream'])
    ->name('secure.pdf.stream');

// PDF viewer error logging (no auth required - can be called from viewer)
// Requirements 6.3, 6.4: Log errors with full context
Route::post('/secure-pdf/log-error/{content}', [\App\Http\Controllers\SecurePdfController::class, 'logError'])
    ->name('secure.pdf.log-error');

// PDF viewer DevTools detection logging (no auth required)
// Requirement 3.7: Detect and log developer tools usage
Route::post('/secure-pdf/log-devtools-detection/{content}', [\App\Http\Controllers\SecurePdfController::class, 'logDevToolsDetection'])
    ->name('secure.pdf.log-devtools-detection');


// Admin Routes
Route::prefix('admin')->middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard');
    
    // Admin Course Management
    Route::get('/courses/trashed', [AdminCourseController::class, 'trashed'])->name('admin.courses.trashed');
    Route::post('/courses/{course}/restore', [AdminCourseController::class, 'restore'])->name('admin.courses.restore')->withTrashed();
    Route::delete('/courses/{course}/force-delete', [AdminCourseController::class, 'forceDelete'])->name('admin.courses.force-delete')->withTrashed();
    Route::delete('/courses/empty-trash', [AdminCourseController::class, 'emptyTrash'])->name('admin.courses.empty-trash');
    Route::resource('courses', AdminCourseController::class)->names([
        'index' => 'admin.courses.index',
        'create' => 'admin.courses.create',
        'store' => 'admin.courses.store',
        'show' => 'admin.courses.show',
        'edit' => 'admin.courses.edit',
        'update' => 'admin.courses.update',
        'destroy' => 'admin.courses.destroy',
    ]);
    Route::post('/courses/bulk-action', [AdminCourseController::class, 'bulkAction'])->name('admin.courses.bulk-action');
    
    // Admin Module Management (publish/unpublish)
    Route::prefix('courses/{course}')->group(function () {
        Route::post('/modules/{module}/publish', [TeacherModuleController::class, 'publish'])->name('admin.modules.publish');
        Route::post('/modules/{module}/unpublish', [TeacherModuleController::class, 'unpublish'])->name('admin.modules.unpublish');
    });
    
    // Admin User Management
    Route::get('/users/trashed', [\App\Http\Controllers\Admin\UserController::class, 'trashed'])->name('admin.users.trashed');
    Route::post('/users/{user}/restore', [\App\Http\Controllers\Admin\UserController::class, 'restore'])->name('admin.users.restore')->withTrashed();
    Route::delete('/users/{user}/force-delete', [\App\Http\Controllers\Admin\UserController::class, 'forceDelete'])->name('admin.users.force-delete')->withTrashed();
    Route::delete('/users/empty-trash', [\App\Http\Controllers\Admin\UserController::class, 'emptyTrash'])->name('admin.users.empty-trash');
    
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    
    // Admin System Logs
    Route::get('/logs', [\App\Http\Controllers\Admin\SystemLogController::class, 'index'])->name('admin.logs.index');
    Route::get('/logs/{filename}/download', [\App\Http\Controllers\Admin\SystemLogController::class, 'download'])->name('admin.logs.download');
    Route::post('/logs/{filename}/clear', [\App\Http\Controllers\Admin\SystemLogController::class, 'clear'])->name('admin.logs.clear');
    Route::get('/logs/health', [\App\Http\Controllers\Admin\SystemLogController::class, 'health'])->name('admin.logs.health');
    
    // Admin Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    
    
    // Admin Reports
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/{type}/download', [\App\Http\Controllers\Admin\ReportController::class, 'download'])->name('admin.reports.download');
    
    // Admin System Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'clearCache'])->name('admin.settings.clear-cache');
    Route::post('/settings/toggle-maintenance', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'toggleMaintenance'])->name('admin.settings.toggle-maintenance');
    Route::post('/settings/optimize', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'optimize'])->name('admin.settings.optimize');
    Route::post('/settings/backup', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'backup'])->name('admin.settings.backup');
    Route::get('/settings/backup/{filename}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'downloadBackup'])->name('admin.settings.download-backup');
    Route::get('/settings/system-info', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'systemInfo'])->name('admin.settings.system-info');
    
    // Admin System Announcements
    Route::get('/announcements/trashed', [\App\Http\Controllers\Admin\AnnouncementController::class, 'trashed'])->name('admin.announcements.trashed');
    Route::post('/announcements/{announcement}/restore', [\App\Http\Controllers\Admin\AnnouncementController::class, 'restore'])->name('admin.announcements.restore')->withTrashed();
    Route::delete('/announcements/{announcement}/force-delete', [\App\Http\Controllers\Admin\AnnouncementController::class, 'forceDelete'])->name('admin.announcements.force-delete')->withTrashed();
    Route::delete('/announcements/empty-trash', [\App\Http\Controllers\Admin\AnnouncementController::class, 'emptyTrash'])->name('admin.announcements.empty-trash');
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->names([
        'index' => 'admin.announcements.index',
        'create' => 'admin.announcements.create',
        'store' => 'admin.announcements.store',
        'show' => 'admin.announcements.show',
        'edit' => 'admin.announcements.edit',
        'update' => 'admin.announcements.update',
        'destroy' => 'admin.announcements.destroy',
    ]);
    
    // Admin Contact Messages
    Route::get('/contact-messages', [\App\Http\Controllers\ContactController::class, 'index'])->name('admin.contact-messages.index');
    Route::get('/contact-messages/{message}', [\App\Http\Controllers\ContactController::class, 'show'])->name('admin.contact-messages.show');
    Route::post('/contact-messages/{message}/reply', [\App\Http\Controllers\ContactController::class, 'reply'])->name('admin.contact-messages.reply');
    
    // Admin File Repair Management
    Route::prefix('file-repair')->name('admin.file-repair.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FileRepairController::class, 'index'])->name('index');
        Route::post('/diagnose/{contentId}', [\App\Http\Controllers\Admin\FileRepairController::class, 'diagnose'])->name('diagnose');
        Route::post('/repair/{contentId}', [\App\Http\Controllers\Admin\FileRepairController::class, 'repair'])->name('repair');
        Route::post('/batch', [\App\Http\Controllers\Admin\FileRepairController::class, 'batchRepair'])->name('batch');
        Route::post('/missing-files-report', [\App\Http\Controllers\Admin\FileRepairController::class, 'missingFilesReport'])->name('missing-files-report');
        Route::get('/status/{correlationId}', [\App\Http\Controllers\Admin\FileRepairController::class, 'status'])->name('status');
        Route::post('/schedule', [\App\Http\Controllers\Admin\FileRepairController::class, 'scheduleRepair'])->name('schedule');
    });
    
    // Admin PDF Access Logs
    Route::get('/pdf-access-logs', [\App\Http\Controllers\Admin\PdfAccessLogController::class, 'index'])->name('admin.pdf-access-logs.index');
});

// Teacher Routes
Route::prefix('teacher')->middleware(['auth', 'role:Teacher'])->group(function () {
    Route::get('/dashboard', [TeacherDashboard::class, 'index'])->name('teacher.dashboard');
    
    // Teacher Course Management
    Route::resource('courses', TeacherCourseController::class)->names([
        'index' => 'teacher.courses.index',
        'create' => 'teacher.courses.create',
        'store' => 'teacher.courses.store',
        'show' => 'teacher.courses.show',
        'edit' => 'teacher.courses.edit',
        'update' => 'teacher.courses.update',
        'destroy' => 'teacher.courses.destroy',
    ]);
    Route::post('/courses/{course}/publish', [TeacherCourseController::class, 'publish'])->name('teacher.courses.publish');
    Route::post('/courses/{course}/unpublish', [TeacherCourseController::class, 'unpublish'])->name('teacher.courses.unpublish');
    
    // Teacher Grading Queue
    Route::prefix('grading')->name('teacher.grading.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Teacher\GradingQueueController::class, 'index'])->name('index');
        Route::get('/{submission}', [\App\Http\Controllers\Teacher\GradingQueueController::class, 'show'])->name('show');
        Route::post('/{submission}/grade', [\App\Http\Controllers\Teacher\GradingQueueController::class, 'grade'])->name('grade');
        Route::post('/bulk', [\App\Http\Controllers\Teacher\GradingQueueController::class, 'bulkGrade'])->name('bulk');
    });
    
    // Teacher Bulk Operations
    Route::prefix('bulk')->name('teacher.bulk.')->group(function () {
        Route::post('/download-submissions', [\App\Http\Controllers\Teacher\BulkOperationsController::class, 'downloadSubmissions'])->name('download-submissions');
        Route::post('/export-csv', [\App\Http\Controllers\Teacher\BulkOperationsController::class, 'exportToCSV'])->name('export-csv');
        Route::post('/send-reminders/{assignment}', [\App\Http\Controllers\Teacher\BulkOperationsController::class, 'sendReminders'])->name('send-reminders');
        Route::post('/bulk-grade', [\App\Http\Controllers\Teacher\BulkOperationsController::class, 'bulkGrade'])->name('bulk-grade');
        Route::post('/publish-grades/{assignment}', [\App\Http\Controllers\Teacher\BulkOperationsController::class, 'publishAllGrades'])->name('publish-grades');
    });
    
    // Teacher Student Progress
    Route::prefix('progress')->name('teacher.progress.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Teacher\StudentProgressController::class, 'index'])->name('index');
        Route::get('/student/{student}', [\App\Http\Controllers\Teacher\StudentProgressController::class, 'show'])->name('show');
        Route::get('/export', [\App\Http\Controllers\Teacher\StudentProgressController::class, 'export'])->name('export');
    });
    
    // Assignment Templates
    Route::prefix('templates')->name('teacher.templates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Teacher\AssignmentTemplateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Teacher\AssignmentTemplateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Teacher\AssignmentTemplateController::class, 'store'])->name('store');
        Route::post('/{template}/apply/{course}/{module}/{subpage}', [\App\Http\Controllers\Teacher\AssignmentTemplateController::class, 'apply'])->name('apply');
        Route::post('/duplicate/{assignment}', [\App\Http\Controllers\Teacher\AssignmentTemplateController::class, 'duplicate'])->name('duplicate');
        Route::delete('/{template}', [\App\Http\Controllers\Teacher\AssignmentTemplateController::class, 'destroy'])->name('destroy');
    });
    
    // Rubrics
    Route::prefix('assignments/{assignment}/rubrics')->name('teacher.rubrics.')->group(function () {
        Route::get('/create', [\App\Http\Controllers\Teacher\RubricController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Teacher\RubricController::class, 'store'])->name('store');
        Route::get('/{rubric}/edit', [\App\Http\Controllers\Teacher\RubricController::class, 'edit'])->name('edit');
        Route::put('/{rubric}', [\App\Http\Controllers\Teacher\RubricController::class, 'update'])->name('update');
        Route::delete('/{rubric}', [\App\Http\Controllers\Teacher\RubricController::class, 'destroy'])->name('destroy');
    });

    // Teacher Contact Messages
    Route::get('/contact-messages', [\App\Http\Controllers\ContactController::class, 'index'])->name('teacher.contact-messages.index');
    Route::get('/contact-messages/{message}', [\App\Http\Controllers\ContactController::class, 'show'])->name('teacher.contact-messages.show');
    Route::post('/contact-messages/{message}/reply', [\App\Http\Controllers\ContactController::class, 'reply'])->name('teacher.contact-messages.reply');

    // Teacher Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Teacher\ActivityLogController::class, 'index'])->name('teacher.logs.index');
});

// Module Management Routes (accessible by both Teacher and Admin)
Route::prefix('teacher/courses/{course}')->middleware(['auth', 'role:Teacher|Admin'])->group(function () {
    // Static routes MUST come before resource/parameterized routes
    Route::post('/modules/reorder', [TeacherModuleController::class, 'reorder'])->name('teacher.modules.reorder');
    Route::delete('/modules/empty-trash', [TeacherModuleController::class, 'emptyTrash'])->name('teacher.modules.empty-trash');

    Route::resource('modules', TeacherModuleController::class)->names([
        'create' => 'teacher.modules.create',
        'store' => 'teacher.modules.store',
        'show' => 'teacher.modules.show',
        'edit' => 'teacher.modules.edit',
        'update' => 'teacher.modules.update',
        'destroy' => 'teacher.modules.destroy',
    ]);
    Route::post('/modules/{module}/content', [TeacherModuleController::class, 'addContent'])->name('teacher.modules.add-content');
    Route::post('/modules/{module}/publish', [TeacherModuleController::class, 'publish'])->name('teacher.modules.publish');
    Route::post('/modules/{module}/unpublish', [TeacherModuleController::class, 'unpublish'])->name('teacher.modules.unpublish');
    Route::post('/modules/{moduleId}/restore', [TeacherModuleController::class, 'restore'])->name('teacher.modules.restore');
    Route::delete('/modules/{moduleId}/force-delete', [TeacherModuleController::class, 'forceDelete'])->name('teacher.modules.force-delete');
});

// Student Routes
Route::prefix('student')->middleware(['auth', 'role:Student'])->group(function () {
    Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('student.dashboard');
    
    // Student Course Browsing and Access
    Route::get('/courses', [StudentCourseController::class, 'index'])->name('student.courses.index');
    Route::get('/courses/enrolled', [StudentCourseController::class, 'enrolled'])->name('student.courses.enrolled');
    Route::get('/courses/{course}', [StudentCourseController::class, 'show'])->name('student.courses.show');
    Route::get('/courses/{course}/modules', [StudentCourseController::class, 'modules'])->name('student.courses.modules');
    Route::get('/courses/{course}/modules/{module}', [StudentCourseController::class, 'showModule'])->name('student.courses.module');
    
    // Student Enrollment Management - DISABLED: Students cannot self-enroll
    // Only Admins and Teachers can assign students to courses via the CourseAssignmentController
    // These routes are kept for backward compatibility but will show appropriate error messages
    Route::post('/courses/{course}/enroll', [StudentCourseController::class, 'enroll'])->name('student.courses.enroll');
    Route::delete('/courses/{course}/unenroll', [StudentCourseController::class, 'unenroll'])->name('student.courses.unenroll');
    Route::get('/courses/{course}/progress', [StudentCourseController::class, 'progress'])->name('student.courses.progress');

    // Student Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\Student\ActivityLogController::class, 'index'])->name('student.logs.index');
});

// Include Subpage Routes
require __DIR__.'/subpages.php';

// Include Media Routes
require __DIR__.'/media.php';

// Include Assignment Routes
require __DIR__.'/assignments.php';

// Include Grade Routes
require __DIR__.'/grades.php';

// Include Forum Routes
require __DIR__.'/forums.php';

// Include Analytics Routes
require __DIR__.'/analytics.php';

// Announcement Routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('courses/{course}')->group(function () {
        Route::resource('announcements', \App\Http\Controllers\AnnouncementController::class)->names([
            'index' => 'courses.announcements.index',
            'create' => 'courses.announcements.create',
            'store' => 'courses.announcements.store',
            'show' => 'courses.announcements.show',
            'edit' => 'courses.announcements.edit',
            'update' => 'courses.announcements.update',
            'destroy' => 'courses.announcements.destroy',
        ]);
    });
});

// Message Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('messages', \App\Http\Controllers\MessageController::class)->except(['edit', 'update'])->names([
        'index' => 'messages.index',
        'create' => 'messages.create',
        'store' => 'messages.store',
        'show' => 'messages.show',
        'destroy' => 'messages.destroy',
    ]);
    Route::get('/messages/{message}/reply', [\App\Http\Controllers\MessageController::class, 'reply'])->name('messages.reply');
    Route::post('/messages/{message}/reply', [\App\Http\Controllers\MessageController::class, 'storeReply'])->name('messages.reply.store');
});

// Notification Preference Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/preferences', [\App\Http\Controllers\NotificationPreferenceController::class, 'edit'])->name('notifications.preferences');
    Route::put('/notifications/preferences', [\App\Http\Controllers\NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
    Route::post('/notifications/preferences/reset', [\App\Http\Controllers\NotificationPreferenceController::class, 'reset'])->name('notifications.preferences.reset');
    Route::post('/notifications/mark-read', [\App\Http\Controllers\NotificationPreferenceController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationPreferenceController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationPreferenceController::class, 'getUnreadCount'])->name('notifications.unread-count');
});
