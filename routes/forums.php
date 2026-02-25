<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForumController;

// Forum Routes - Course-specific forums
Route::middleware(['auth'])->group(function () {
    // Course Forums
    Route::prefix('courses/{course}')->group(function () {
        // Forum management
        Route::resource('forums', ForumController::class)->except(['index'])->names([
            'create' => 'courses.forums.create',
            'store' => 'courses.forums.store',
            'show' => 'courses.forums.show',
            'edit' => 'courses.forums.edit',
            'update' => 'courses.forums.update',
            'destroy' => 'courses.forums.destroy',
        ]);
        
        // Forum index for course
        Route::get('/forums', [ForumController::class, 'index'])->name('courses.forums.index');
        
        // Forum Topics
        Route::prefix('forums/{forum}')->group(function () {
            // Topic management
            Route::get('/topics/create', [ForumController::class, 'createTopic'])->name('courses.forums.topics.create');
            Route::post('/topics', [ForumController::class, 'storeTopic'])->name('courses.forums.topics.store');
            Route::get('/topics/{topic}', [ForumController::class, 'showTopic'])->name('courses.forums.topics.show');
            Route::post('/topics/{topic}/replies', [ForumController::class, 'storeReply'])->name('courses.forums.topics.replies.store');
            
            // Post management
            Route::get('/topics/{topic}/posts/{post}/edit', [ForumController::class, 'editPost'])->name('courses.forums.posts.edit');
            Route::put('/topics/{topic}/posts/{post}', [ForumController::class, 'updatePost'])->name('courses.forums.posts.update');
            Route::delete('/topics/{topic}/posts/{post}', [ForumController::class, 'destroyPost'])->name('courses.forums.posts.destroy');
        });
    });
});