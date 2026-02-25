<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish assignments that are scheduled for auto-publication';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $assignments = Assignment::readyForAutoPublish()->get();

        if ($assignments->isEmpty()) {
            $this->info('No assignments ready for auto-publication.');
            return Command::SUCCESS;
        }

        $publishedCount = 0;

        foreach ($assignments as $assignment) {
            try {
                // Publish the assignment
                $assignment->publish();

                // Log the auto-publication
                Log::info('Assignment auto-published', [
                    'assignment_id' => $assignment->id,
                    'assignment_title' => $assignment->title,
                    'course_id' => $assignment->course_id,
                    'scheduled_at' => $assignment->scheduled_publish_at,
                    'published_at' => now(),
                ]);

                // Send notifications to enrolled students
                $enrollments = Enrollment::where('course_id', $assignment->course_id)
                    ->where('status', 'active')
                    ->with('user')
                    ->get();

                foreach ($enrollments as $enrollment) {
                    $enrollment->user->notify(new AssignmentPublishedNotification($assignment));
                }

                // Clear the scheduled_publish_at field
                $assignment->update(['scheduled_publish_at' => null]);

                $publishedCount++;
                $this->info("Published: {$assignment->title} (ID: {$assignment->id})");
            } catch (\Exception $e) {
                Log::error('Failed to auto-publish assignment', [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to publish: {$assignment->title} (ID: {$assignment->id})");
            }
        }

        $this->info("Successfully published {$publishedCount} assignment(s).");
        return Command::SUCCESS;
    }
}
