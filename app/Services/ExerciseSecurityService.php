<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExerciseSecurityService
{
    /**
     * SECURITY: Get exercise data safe for student consumption.
     * This is the ONLY method students should use to get exercise data.
     */
    public function getStudentSafeExercise(Exercise $exercise, User $student): array
    {
        // Verify student can access this exercise
        if (!$exercise->canSubmit($student) && !$exercise->submissionFor($student)) {
            throw new \Exception('Access denied to exercise');
        }

        // Log access for security monitoring
        $this->logExerciseAccess($exercise, $student);

        // Return only safe fields
        return $exercise->toStudentArray();
    }

    /**
     * SECURITY: Get multiple exercises safe for student consumption.
     */
    public function getStudentSafeExercises($exercises, User $student): array
    {
        return $exercises->map(function ($exercise) use ($student) {
            try {
                return $this->getStudentSafeExercise($exercise, $student);
            } catch (\Exception $e) {
                // Skip exercises the student can't access
                return null;
            }
        })->filter()->values()->toArray();
    }

    /**
     * SECURITY: Validate that no answer data is being exposed to students.
     */
    public function validateNoAnswerExposure(array $data): bool
    {
        $forbiddenFields = [
            'answer', 'correct_answer', 'solution', 'key', 
            'teacher_notes', 'grading_rubric', 'expected_response'
        ];

        return !$this->containsForbiddenFields($data, $forbiddenFields);
    }

    /**
     * SECURITY: Check if data contains forbidden fields.
     */
    private function containsForbiddenFields(array $data, array $forbiddenFields): bool
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $forbiddenFields)) {
                return true;
            }
            
            if (is_array($value) && $this->containsForbiddenFields($value, $forbiddenFields)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * SECURITY: Log exercise access for monitoring.
     */
    private function logExerciseAccess(Exercise $exercise, User $student): void
    {
        // Rate limit logging to prevent log spam
        $cacheKey = "exercise_access_log_{$student->id}_{$exercise->id}";
        
        if (!Cache::has($cacheKey)) {
            Log::info('Exercise accessed by student', [
                'exercise_id' => $exercise->id,
                'exercise_title' => $exercise->title,
                'student_id' => $student->id,
                'student_email' => $student->email,
                'course_id' => $exercise->subpage->module->course->id,
                'timestamp' => now()->toISOString(),
                'ip' => request()->ip(),
            ]);
            
            // Cache for 5 minutes to prevent spam
            Cache::put($cacheKey, true, 300);
        }
    }

    /**
     * SECURITY: Audit exercise data before sending to frontend.
     */
    public function auditExerciseData(array $data, User $user): array
    {
        if ($user->hasRole('student')) {
            // For students, ensure no forbidden data
            if (!$this->validateNoAnswerExposure($data)) {
                Log::critical('SECURITY BREACH: Answer data exposed to student', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'data_keys' => array_keys($data),
                    'timestamp' => now()->toISOString(),
                ]);
                
                throw new \Exception('Security validation failed');
            }
        }

        return $data;
    }

    /**
     * SECURITY: Check for potential SQL injection in exercise content.
     */
    public function validateExerciseContent(array $data): bool
    {
        $sqlPatterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION)\b/i',
            '/[\'";].*(\bOR\b|\bAND\b).*[\'";]/i',
            '/\b(script|javascript|vbscript|onload|onerror)\b/i',
        ];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        Log::warning('Suspicious content detected in exercise', [
                            'field' => $key,
                            'pattern' => $pattern,
                            'user_id' => auth()->id(),
                            'timestamp' => now()->toISOString(),
                        ]);
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * SECURITY: Generate secure exercise hash for integrity checking.
     */
    public function generateExerciseHash(Exercise $exercise): string
    {
        $data = [
            'id' => $exercise->id,
            'question' => $exercise->question,
            'max_score' => $exercise->max_score,
            'created_at' => $exercise->created_at->timestamp,
        ];

        return hash('sha256', json_encode($data) . config('app.key'));
    }

    /**
     * SECURITY: Verify exercise integrity.
     */
    public function verifyExerciseIntegrity(Exercise $exercise, string $hash): bool
    {
        return hash_equals($this->generateExerciseHash($exercise), $hash);
    }

    /**
     * SECURITY: Clean user input for exercise submissions.
     */
    public function sanitizeSubmissionInput(array $input): array
    {
        $cleaned = [];
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous content
                $value = strip_tags($value, '<p><br><strong><em><u><ol><ul><li>');
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            
            $cleaned[$key] = $value;
        }

        return $cleaned;
    }

    /**
     * SECURITY: Rate limit exercise submissions.
     */
    public function checkSubmissionRateLimit(User $user, Exercise $exercise): bool
    {
        $cacheKey = "submission_rate_limit_{$user->id}_{$exercise->id}";
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 5) { // Max 5 submission attempts per hour
            Log::warning('Submission rate limit exceeded', [
                'user_id' => $user->id,
                'exercise_id' => $exercise->id,
                'attempts' => $attempts,
                'timestamp' => now()->toISOString(),
            ]);
            return false;
        }

        Cache::put($cacheKey, $attempts + 1, 3600); // 1 hour
        return true;
    }
}