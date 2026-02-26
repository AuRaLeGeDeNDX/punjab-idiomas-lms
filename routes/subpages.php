<?php

use App\Http\Controllers\Teacher\SubpageController as TeacherSubpageController;
use App\Http\Controllers\Teacher\ContentController as TeacherContentController;
use App\Http\Controllers\Student\SubpageController as StudentSubpageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Subpage Routes
|--------------------------------------------------------------------------
|
| Routes for managing the dynamic WBS structure: Course → Module → Subpage → Content
|
*/

// Teacher/Admin Routes for Subpage Management
Route::middleware(['auth', 'role:Teacher|Admin'])->prefix('teacher')->name('teacher.')->group(function () {
    
    // Subpage management within courses and modules
    Route::prefix('courses/{course}/modules/{module}')->name('courses.modules.')->group(function () {
        
        // Subpage CRUD
        Route::resource('subpages', TeacherSubpageController::class);
        
        // Content Builder Interface
        Route::get('subpages/{subpage}/content-builder', [TeacherSubpageController::class, 'contentBuilder'])
             ->name('subpages.content-builder');
        
        // Subpage specific actions
        Route::post('subpages/reorder', [TeacherSubpageController::class, 'reorder'])
             ->name('subpages.reorder');
        Route::patch('subpages/{subpage}/toggle-active', [TeacherSubpageController::class, 'toggleActive'])
             ->name('subpages.toggle-active');
        Route::post('subpages/{subpage}/restore', [TeacherSubpageController::class, 'restore'])
             ->name('subpages.restore');
        
        // Content management within subpages
        Route::prefix('subpages/{subpage}')->name('subpages.')->group(function () {
            Route::resource('content', TeacherContentController::class)->except(['index', 'show']);
            Route::get('content/{content}/download', [TeacherContentController::class, 'download'])
                 ->name('content.download');
            Route::post('content/reorder', [TeacherContentController::class, 'reorder'])
                 ->name('content.reorder');
            Route::post('content/{contentId}/restore', [TeacherContentController::class, 'restore'])
                 ->name('content.restore');
            
            // Exercise management within subpages
            Route::resource('exercises', \App\Http\Controllers\Teacher\ExerciseController::class);
            Route::post('exercises/reorder', [\App\Http\Controllers\Teacher\ExerciseController::class, 'reorder'])
                 ->name('exercises.reorder');
            Route::patch('exercises/{exercise}/toggle-active', [\App\Http\Controllers\Teacher\ExerciseController::class, 'toggleActive'])
                 ->name('exercises.toggle-active');
            Route::post('exercises/{exerciseId}/restore', [\App\Http\Controllers\Teacher\ExerciseController::class, 'restore'])
                 ->name('exercises.restore');
            
            // Submission management within exercises
            Route::prefix('exercises/{exercise}')->name('exercises.')->group(function () {
                Route::get('submissions', [\App\Http\Controllers\Teacher\SubmissionController::class, 'index'])
                     ->name('submissions.index');
                Route::get('submissions/{submission}', [\App\Http\Controllers\Teacher\SubmissionController::class, 'show'])
                     ->name('submissions.show');
                Route::post('submissions/{submission}/grade', [\App\Http\Controllers\Teacher\SubmissionController::class, 'grade'])
                     ->name('submissions.grade');
                Route::post('submissions/bulk-grade', [\App\Http\Controllers\Teacher\SubmissionController::class, 'bulkGrade'])
                     ->name('submissions.bulk-grade');
                Route::get('submissions/{submission}/download', [\App\Http\Controllers\Teacher\SubmissionController::class, 'downloadFile'])
                     ->name('submissions.download');
                Route::get('export', [\App\Http\Controllers\Teacher\SubmissionController::class, 'export'])
                     ->name('export');
            });
        });
    });
});

// Student Routes for Read-Only Access
Route::middleware(['auth', 'role:Student'])->prefix('student')->name('student.')->group(function () {
    
    // Student view of subpages and content
    Route::prefix('courses/{course}/modules/{module}')->name('courses.modules.')->group(function () {
        Route::get('subpages', [StudentSubpageController::class, 'index'])
             ->name('subpages.index');
        Route::get('subpages/{subpage}', [StudentSubpageController::class, 'show'])
             ->name('subpages.show');
        
        // Content access for students
        Route::get('subpages/{subpage}/content/{content}/download', [StudentSubpageController::class, 'downloadContent'])
             ->name('subpages.content.download');
        Route::get('subpages/{subpage}/content/{content}/url', [StudentSubpageController::class, 'getContentUrl'])
             ->name('subpages.content.url');
        
        // Exercise access for students
        Route::prefix('subpages/{subpage}')->name('subpages.')->group(function () {
            Route::get('exercises', [\App\Http\Controllers\Student\ExerciseController::class, 'index'])
                 ->name('exercises.index');
            Route::get('exercises/{exercise}', [\App\Http\Controllers\Student\ExerciseController::class, 'show'])
                 ->name('exercises.show');
            Route::get('exercises/{exercise}/submit', [\App\Http\Controllers\Student\ExerciseController::class, 'submit'])
                 ->name('exercises.submit');
            Route::post('exercises/{exercise}/submit', [\App\Http\Controllers\Student\ExerciseController::class, 'storeSubmission'])
                 ->name('exercises.store-submission');
            Route::get('exercises/{exercise}/download-submission', [\App\Http\Controllers\Student\ExerciseController::class, 'downloadSubmission'])
                 ->name('exercises.download-submission');
        });
    });
});

// Admin Routes (inherit teacher routes but with admin prefix)
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Admin can access all teacher functionality
    Route::prefix('courses/{course}/modules/{module}')->name('courses.modules.')->group(function () {
        
        // Subpage management
        Route::resource('subpages', TeacherSubpageController::class);
        
        // Content Builder Interface
        Route::get('subpages/{subpage}/content-builder', [TeacherSubpageController::class, 'contentBuilder'])
             ->name('subpages.content-builder');
        Route::post('subpages/reorder', [TeacherSubpageController::class, 'reorder'])
             ->name('subpages.reorder');
        Route::patch('subpages/{subpage}/toggle-active', [TeacherSubpageController::class, 'toggleActive'])
             ->name('subpages.toggle-active');
        Route::post('subpages/{subpage}/restore', [TeacherSubpageController::class, 'restore'])
             ->name('subpages.restore');
        
        // Content management
        Route::prefix('subpages/{subpage}')->name('subpages.')->group(function () {
            Route::resource('content', TeacherContentController::class)->except(['index', 'show']);
            Route::get('content/{content}/download', [TeacherContentController::class, 'download'])
                 ->name('content.download');
            Route::post('content/reorder', [TeacherContentController::class, 'reorder'])
                 ->name('content.reorder');
            Route::post('content/{contentId}/restore', [TeacherContentController::class, 'restore'])
                 ->name('content.restore');
            
            // Exercise management (admin inherits teacher functionality)
            Route::resource('exercises', \App\Http\Controllers\Teacher\ExerciseController::class);
            Route::post('exercises/reorder', [\App\Http\Controllers\Teacher\ExerciseController::class, 'reorder'])
                 ->name('exercises.reorder');
            Route::patch('exercises/{exercise}/toggle-active', [\App\Http\Controllers\Teacher\ExerciseController::class, 'toggleActive'])
                 ->name('exercises.toggle-active');
            Route::post('exercises/{exerciseId}/restore', [\App\Http\Controllers\Teacher\ExerciseController::class, 'restore'])
                 ->name('exercises.restore');
            
            // Submission management
            Route::prefix('exercises/{exercise}')->name('exercises.')->group(function () {
                Route::get('submissions', [\App\Http\Controllers\Teacher\SubmissionController::class, 'index'])
                     ->name('submissions.index');
                Route::get('submissions/{submission}', [\App\Http\Controllers\Teacher\SubmissionController::class, 'show'])
                     ->name('submissions.show');
                Route::post('submissions/{submission}/grade', [\App\Http\Controllers\Teacher\SubmissionController::class, 'grade'])
                     ->name('submissions.grade');
                Route::post('submissions/bulk-grade', [\App\Http\Controllers\Teacher\SubmissionController::class, 'bulkGrade'])
                     ->name('submissions.bulk-grade');
                Route::get('submissions/{submission}/download', [\App\Http\Controllers\Teacher\SubmissionController::class, 'downloadFile'])
                     ->name('submissions.download');
                Route::get('export', [\App\Http\Controllers\Teacher\SubmissionController::class, 'export'])
                     ->name('export');
            });
        });
    });
});

// API Routes for AJAX operations
Route::middleware(['auth', 'web'])->prefix('api/v1')->name('api.')->group(function () {
    
    // Subpage API endpoints
    Route::prefix('courses/{course}/modules/{module}')->group(function () {
        Route::get('subpages', function (Course $course, Module $module) {
            if (!auth()->user()->can('view', $course)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $query = $module->subpages()->ordered();
            
            // Students only see active subpages
            if (auth()->user()->hasRole('Student')) {
                $query->active();
            }
            
            return response()->json($query->get());
        })->name('subpages.list');
        
        Route::get('subpages/{subpage}/content', function (Course $course, Module $module, Subpage $subpage) {
            if (!$subpage->canBeAccessedBy(auth()->user())) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $query = $subpage->contents()->ordered();
            
            // Students only see student-visible content
            if (auth()->user()->hasRole('Student')) {
                $query->active()->visibleToStudents();
            }
            
            return response()->json($query->get());
        })->name('subpages.content.list');
        
        // Content Block API endpoints
        Route::prefix('subpages/{subpage}')->group(function () {
            Route::get('content-blocks', [\App\Http\Controllers\Api\ContentBlockController::class, 'index'])
                 ->name('content-blocks.index');
            Route::post('content-blocks', [\App\Http\Controllers\Api\ContentBlockController::class, 'store'])
                 ->name('content-blocks.store');
                 
            // Generate presigned URL for direct uploads
            Route::post('content-blocks/presigned-url', [\App\Http\Controllers\Api\UploadController::class, 'getPresignedUrl'])
                 ->name('content-blocks.presigned-url');

            // Static routes MUST come before parameterized {contentBlock} routes
            Route::post('content-blocks/reorder', [\App\Http\Controllers\Api\ContentBlockController::class, 'reorder'])
                 ->name('content-blocks.reorder');
            Route::post('content-blocks/update-layout', [\App\Http\Controllers\Api\ContentBlockController::class, 'updateLayout'])
                 ->name('content-blocks.update-layout');
            Route::post('content-blocks/reorder-sections', [\App\Http\Controllers\Api\ContentBlockController::class, 'reorderSections'])
                 ->name('content-blocks.reorder-sections');
            Route::post('content-blocks/paste', [\App\Http\Controllers\Api\ContentBlockController::class, 'paste'])
                 ->name('content-blocks.paste');
            Route::delete('content-blocks/empty-trash', [\App\Http\Controllers\Api\ContentBlockController::class, 'emptyTrash'])
                 ->name('content-blocks.empty-trash');

            // Parameterized routes
            Route::get('content-blocks/{contentBlock}', [\App\Http\Controllers\Api\ContentBlockController::class, 'show'])
                 ->name('content-blocks.show');
            Route::put('content-blocks/{contentBlock}', [\App\Http\Controllers\Api\ContentBlockController::class, 'update'])
                 ->name('content-blocks.update');
            Route::delete('content-blocks/{contentBlock}', [\App\Http\Controllers\Api\ContentBlockController::class, 'destroy'])
                 ->name('content-blocks.destroy');
            Route::post('content-blocks/{contentBlock}/duplicate', [\App\Http\Controllers\Api\ContentBlockController::class, 'duplicate'])
                 ->name('content-blocks.duplicate');
            
            // Advanced Content Builder endpoints
            Route::post('content-blocks/{contentBlock}/move-section', [\App\Http\Controllers\Api\ContentBlockController::class, 'moveToSection'])
                 ->name('content-blocks.move-section');
            Route::put('content-blocks/{contentBlock}/visibility', [\App\Http\Controllers\Api\ContentBlockController::class, 'updateVisibility'])
                 ->name('content-blocks.update-visibility');
            Route::get('content-blocks/{contentBlock}/versions', [\App\Http\Controllers\Api\ContentBlockController::class, 'versionHistory'])
                 ->name('content-blocks.version-history');
            Route::post('content-blocks/{contentBlock}/restore', [\App\Http\Controllers\Api\ContentBlockController::class, 'restoreVersion'])
                 ->name('content-blocks.restore-version');
            
            // Phase 5: Layout versioning endpoints
            Route::get('content-blocks/{contentBlock}/layout-history', [\App\Http\Controllers\Api\ContentBlockController::class, 'layoutHistory'])
                 ->name('content-blocks.layout-history');
            Route::post('content-blocks/{contentBlock}/restore-layout', [\App\Http\Controllers\Api\ContentBlockController::class, 'restoreLayoutVersion'])
                 ->name('content-blocks.restore-layout');

            // Trash management (restore from trash & permanent delete)
            Route::post('content-blocks/{contentId}/restore-from-trash', [\App\Http\Controllers\Api\ContentBlockController::class, 'restore'])
                 ->name('content-blocks.restore-from-trash');
            Route::delete('content-blocks/{contentId}/force-delete', [\App\Http\Controllers\Api\ContentBlockController::class, 'forceDelete'])
                 ->name('content-blocks.force-delete');
        });
        
        // Content types configuration
        Route::get('content-types', [\App\Http\Controllers\Api\ContentBlockController::class, 'contentTypes'])
             ->name('content-types');
        
        // SECURITY: Exercise API endpoints (students get safe data without answers)
        Route::get('subpages/{subpage}/exercises/{exercise}', [\App\Http\Controllers\Student\ExerciseController::class, 'apiShow'])
             ->name('exercises.api.show');
    });
});