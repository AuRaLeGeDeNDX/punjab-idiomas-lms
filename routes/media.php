<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Media Routes
|--------------------------------------------------------------------------
|
| Routes for serving private media files with proper access control
|
*/

Route::middleware(['auth'])->group(function () {
    // Serve private content files with access control
    Route::get('/media/content/{content}', [MediaController::class, 'serveContentFile'])
        ->name('media.content');
});