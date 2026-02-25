<?php

namespace App\Jobs;

use App\Models\GradeOverride;
use App\Notifications\GradeOverriddenNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGradeOverrideNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The grade override instance.
     */
    public GradeOverride $override;

    /**
     * Create a new job instance.
     */
    public function __construct(GradeOverride $override)
    {
        $this->override = $override;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $override = $this->override->load(['grade.grader', 'grade.submission.assignment', 'admin']);

        // Send notification to the original grader
        $originalGrader = $override->grade->grader;
        
        if ($originalGrader) {
            $originalGrader->notify(new GradeOverriddenNotification($override));
        }
    }
}
