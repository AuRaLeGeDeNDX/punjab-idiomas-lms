<?php

use App\Http\Controllers\SecureFileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Secure File Routes
|--------------------------------------------------------------------------
|
| These routes handle secure file access with proper authorization and logging.
| Files are stored outside the web root and accessed through controlled endpoints.
|
*/

Route::middleware(['auth'])->group(function () {
    // Download file using secure token
    Route::get('/secure-files/download/{token}', [SecureFileController::class, 'downloadByToken'])
        ->name('secure-files.download-token')
        ->where('token', '[a-zA-Z0-9]{64}'); // 64-character token

    // Download content file with authorization
    Route::get('/secure-files/content/{content}', [SecureFileController::class, 'downloadContentFile'])
        ->name('secure-files.download-content');

    // Generate secure URL for content file
    Route::post('/secure-files/content/{content}/generate-url', [SecureFileController::class, 'generateSecureUrl'])
        ->name('secure-files.generate-url');
});