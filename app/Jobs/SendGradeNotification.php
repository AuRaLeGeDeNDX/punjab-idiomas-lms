<?php

namespace App\Jobs;

use App\Models\Grade;
use App\Models\User;
use App\Notifications\GradePublishedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendGradeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Grade $grade;

    /**
     * Create a new job instance.
     */
    public function __construct(Grade $grade)
    {
        $this->grade = $grade;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Only send notification if grade is published
            if (!$this->grade->is_published) {
                Log::info('Grade notification skipped - not published', [
                    'grade_id' => $this->grade->id
                ]);
                return;
            }

            $student = $this->grade->submission->user;
            $student->notify(new GradePublishedNotification($this->grade));

            Log::info('Grade notification sent', [
                'grade_id' => $this->grade->id,
                'student_id' => $student->id,
                'assignment_id' => $this->grade->submission->assignment_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send grade notification', [
                'grade_id' => $this->grade->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}