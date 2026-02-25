<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PreventIDORMiddleware
{
    /**
     * Handle an incoming request to prevent IDOR attacks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get route parameters
        $routeParameters = $request->route()->parameters();
        
        // Check course access
        if (isset($routeParameters['course'])) {
            $course = $routeParameters['course'];
            
            if ($user->hasRole('student')) {
                // Students can only access courses they're enrolled in
                if (!$user->enrollments()->where('course_id', $course->id)->exists()) {
                    abort(403, 'You are not enrolled in this course.');
                }
            } elseif ($user->hasRole('teacher')) {
                // Teachers can only access their own courses
                if ($course->teacher_id !== $user->id) {
                    abort(403, 'You can only access your own courses.');
                }
            }
            // Admins can access all courses
        }

        // Check module access
        if (isset($routeParameters['module'])) {
            $module = $routeParameters['module'];
            $course = $routeParameters['course'] ?? $module->course;
            
            // Ensure module belongs to the course
            if ($module->course_id !== $course->id) {
                abort(404, 'Module not found in this course.');
            }
        }

        // Check subpage access
        if (isset($routeParameters['subpage'])) {
            $subpage = $routeParameters['subpage'];
            $module = $routeParameters['module'] ?? $subpage->module;
            
            // Ensure subpage belongs to the module
            if ($subpage->module_id !== $module->id) {
                abort(404, 'Subpage not found in this module.');
            }
            
            // Students can only access active subpages
            if ($user->hasRole('student') && !$subpage->is_active) {
                abort(403, 'This content is not available.');
            }
        }

        // Check assignment access
        if (isset($routeParameters['assignment'])) {
            $assignment = $routeParameters['assignment'];
            $subpage = $routeParameters['subpage'] ?? $assignment->subpage;
            
            // Ensure assignment belongs to the subpage
            if ($assignment->subpage_id !== $subpage->id) {
                abort(404, 'Assignment not found in this subpage.');
            }
            
            // Students can only access published assignments
            if ($user->hasRole('student') && (!$assignment->is_published || !$assignment->is_active)) {
                abort(403, 'This assignment is not available.');
            }
        }

        // Check exercise access
        if (isset($routeParameters['exercise'])) {
            $exercise = $routeParameters['exercise'];
            $subpage = $routeParameters['subpage'] ?? $exercise->subpage;
            
            // Ensure exercise belongs to the subpage
            if ($exercise->subpage_id !== $subpage->id) {
                abort(404, 'Exercise not found in this subpage.');
            }
            
            // Students can only access active exercises
            if ($user->hasRole('student') && !$exercise->is_active) {
                abort(403, 'This exercise is not available.');
            }
        }

        // Check submission access
        if (isset($routeParameters['submission'])) {
            $submission = $routeParameters['submission'];
            
            if ($user->hasRole('student')) {
                // Students can only access their own submissions
                if ($submission->user_id !== $user->id) {
                    abort(403, 'You can only access your own submissions.');
                }
            } elseif ($user->hasRole('teacher')) {
                // Teachers can only access submissions in their courses
                $course = $submission->assignment->course ?? $submission->exercise->subpage->module->course;
                if ($course->teacher_id !== $user->id) {
                    abort(403, 'You can only access submissions in your courses.');
                }
            }
        }

        // Check grade access
        if (isset($routeParameters['grade'])) {
            $grade = $routeParameters['grade'];
            
            if ($user->hasRole('student')) {
                // Students can only access their own grades
                if ($grade->submission->user_id !== $user->id) {
                    abort(403, 'You can only access your own grades.');
                }
                
                // Students can only see published grades
                if (!$grade->is_published) {
                    abort(403, 'This grade is not yet available.');
                }
            } elseif ($user->hasRole('teacher')) {
                // Teachers can only access grades in their courses
                $course = $grade->submission->assignment->course;
                if ($course->teacher_id !== $user->id) {
                    abort(403, 'You can only access grades in your courses.');
                }
            }
        }

        return $next($request);
    }
}