<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class SubpageController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display subpages for a module (student view).
     */
    public function index(Course $course, Module $module): View
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure module belongs to the course
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        // Get only active subpages with student-visible content
        $subpages = Cache::remember(
            "student_subpages_{$module->id}_" . auth()->id(),
            300, // 5 minutes
            function () use ($module) {
                return $module->subpages()
                    ->active()
                    ->with(['contents' => function ($query) {
                        $query->active()
                              ->visibleToStudents()
                              ->ordered();
                    }])
                    ->ordered()
                    ->get();
            }
        );

        return view('student.subpages.index', compact('course', 'module', 'subpages'));
    }

    /**
     * Display the specified subpage with its content (student view).
     */
    public function show(Course $course, Module $module, Subpage $subpage): View
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        // Check if subpage is active
        if (!$subpage->is_active) {
            abort(404, 'This subpage is not available.');
        }

        // Load only student-visible content
        $subpage->load(['contents' => function ($query) {
            $query->active()
                  ->visibleToStudents()
                  ->ordered();
        }]);

        return view('student.subpages.show', compact('course', 'module', 'subpage'));
    }

    /**
     * Download content file (student access).
     */
    public function downloadContent(Course $course, Module $module, Subpage $subpage, Content $content)
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            abort(403, 'You are not enrolled in this course.');
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $content->subpage_id !== $subpage->id) {
            abort(404);
        }

        // Check if content is accessible to students
        if (!$content->canBeAccessedBy(auth()->user())) {
            abort(403, 'You do not have access to this content.');
        }

        if (!$content->file_path || !\Storage::exists($content->file_path)) {
            abort(404, 'File not found');
        }

        return \Storage::download($content->file_path, $content->file_name);
    }

    /**
     * Get signed URL for content file (AJAX).
     */
    public function getContentUrl(Course $course, Module $module, Subpage $subpage, Content $content)
    {
        // Check if student is enrolled in the course
        if (!auth()->user()->enrollments()->where('course_id', $course->id)->exists()) {
            return response()->json(['error' => 'Not enrolled'], 403);
        }
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $content->subpage_id !== $subpage->id) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        // Check if content is accessible to students
        if (!$content->canBeAccessedBy(auth()->user())) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Enhanced file serving with validation
        try {
            // Check if content has a file
            if (!$content->isFile() || !$content->file_path) {
                return response()->json([
                    'error' => 'Content has no associated file',
                    'error_code' => 'NO_FILE_ASSOCIATED'
                ], 404);
            }

            // Use enhanced getSignedUrl which includes file existence validation
            $signedUrl = $content->getSignedUrl();
            
            if (!$signedUrl) {
                // Enhanced error response with more specific information
                \Log::warning('Student SubpageController: File URL generation failed', [
                    'content_id' => $content->id,
                    'user_id' => auth()->id(),
                    'file_path' => $content->file_path,
                    'storage_disk' => $content->storage_disk,
                    'content_type' => $content->type,
                ]);
                
                return response()->json([
                    'error' => 'File not available',
                    'error_code' => 'FILE_NOT_AVAILABLE',
                    'details' => 'The requested file could not be accessed. It may have been moved or deleted.'
                ], 404);
            }

            return response()->json(['url' => $signedUrl]);
            
        } catch (\Exception $e) {
            \Log::error('Student SubpageController: File serving error', [
                'content_id' => $content->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'File serving failed',
                'error_code' => 'FILE_SERVING_ERROR',
                'details' => 'An unexpected error occurred while accessing the file.'
            ], 500);
        }
    }
}