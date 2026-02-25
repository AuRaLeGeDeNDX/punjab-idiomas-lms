<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseHierarchyController;
use App\Http\Controllers\Api\CourseAssignmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Course Hierarchy Management API Routes
Route::middleware(['auth', 'web'])->group(function () {
    // Get course hierarchy
    Route::get('/courses/{course}/hierarchy', [CourseHierarchyController::class, 'getCourseHierarchy']);
    
    // Course hierarchy data
    Route::get('/courses/{course}/modules', [CourseHierarchyController::class, 'getCourseHierarchy']);
    
    // Module management
    Route::post('/courses/{course}/modules', [CourseHierarchyController::class, 'createModule']);
    Route::put('/courses/{course}/modules/{module}', [CourseHierarchyController::class, 'updateModule']);
    Route::delete('/courses/{course}/modules/{module}', [CourseHierarchyController::class, 'deleteModule']);
    Route::post('/courses/{course}/modules/reorder', [CourseHierarchyController::class, 'reorderModules']);
    
    // Subpage management
    Route::post('/courses/{course}/modules/{module}/subpages', [CourseHierarchyController::class, 'createSubpage']);
    Route::put('/courses/{course}/modules/{module}/subpages/{subpage}', [CourseHierarchyController::class, 'updateSubpage']);
    Route::delete('/courses/{course}/modules/{module}/subpages/{subpage}', [CourseHierarchyController::class, 'deleteSubpage']);
    Route::post('/courses/{course}/modules/{module}/subpages/reorder', [CourseHierarchyController::class, 'reorderSubpages']);

    // Content Block restoration (moved to subpages.php under api/v1 prefix)
});

// Course Assignment API Routes
Route::middleware(['auth', 'web'])->group(function () {
    // Student assignment operations
    Route::post('/courses/{course}/assign-students', [CourseAssignmentController::class, 'assignStudents']);
    Route::delete('/courses/{course}/students/{student}', [CourseAssignmentController::class, 'removeStudent']);
    
    // Get enrolled and available students
    Route::get('/courses/{course}/enrolled-students', [CourseAssignmentController::class, 'getEnrolledStudents']);
    Route::get('/courses/{course}/available-students', [CourseAssignmentController::class, 'getAvailableStudents']);
    
    // Student course information
    Route::get('/students/{student}/courses', [CourseAssignmentController::class, 'getStudentCourses']);
    Route::get('/courses/{course}/students/{student}/status', [CourseAssignmentController::class, 'getEnrollmentStatus']);
});