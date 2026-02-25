<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Services\AssignmentWorkflowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAssignmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'assignments:send-reminders 
                            {--hours=24 : Hours before due date to send reminders}
                            {--assignment= : Specific assignment ID to send reminders for}';

    /**
     * The console command description.
     */
    protected $description = 'Send assignment due date reminders to students';

    protected AssignmentWorkflowService $workflowService;

    public function __construct(AssignmentWorkflowService $workflowService)
    {
        parent::__construct();
        $this->workflowService = $workflowService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $assignmentId = $this->option('assignment');

        $this->info("Checking for assignments due within {$hours} hours...");

        $query = Assignment::where('is_published', true)
            ->where('is_active', true)
            ->whereNotNull('due_date')
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addHours($hours));

        if ($assignmentId) {
            $query->where('id', $assignmentId);
        }

        $assignments = $query->with(['course', 'module', 'subpage'])->get();

        if ($assignments->isEmpty()) {
            $this->info('No assignments found that need reminders.');
            return self::SUCCESS;
        }

        $this->info("Found {$assignments->count()} assignment(s) that need reminders.");

        $totalReminders = 0;

        foreach ($assignments as $assignment) {
            try {
                $reminderCount = $this->workflowService->sendDueReminders($assignment);
                $totalReminders += $reminderCount;

                $this->line("✓ Sent {$reminderCount} reminder(s) for: {$assignment->title} (Course: {$assignment->course->title})");
            } catch (\Exception $e) {
                $this->error("✗ Failed to send reminders for: {$assignment->title} - {$e->getMessage()}");
                Log::error('Assignment reminder command failed', [
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Completed! Sent {$totalReminders} total reminder(s).");

        return self::SUCCESS;
    }
}