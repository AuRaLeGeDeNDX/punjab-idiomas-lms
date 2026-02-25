<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SubmissionFile;
use App\Models\User;

// Get a submission file
$submissionFile = SubmissionFile::first();

if (!$submissionFile) {
    echo "No submission files found in database.\n";
    exit;
}

echo "Submission File ID: {$submissionFile->id}\n";
echo "File Name: {$submissionFile->file_name}\n";
echo "File Path: {$submissionFile->file_path}\n\n";

// Load the submission with relationships
$submission = $submissionFile->submission()->with([
    'assignment.subpage.module.course'
])->first();

if (!$submission) {
    echo "ERROR: Submission not found!\n";
    exit;
}

echo "Submission ID: {$submission->id}\n";
echo "Student ID: {$submission->user_id}\n";
echo "Assignment ID: {$submission->assignment_id}\n\n";

// Check if relationships are loaded
if ($submission->assignment) {
    echo "Assignment: {$submission->assignment->title}\n";
    
    if ($submission->assignment->subpage) {
        echo "Subpage: {$submission->assignment->subpage->title}\n";
        
        if ($submission->assignment->subpage->module) {
            echo "Module: {$submission->assignment->subpage->module->title}\n";
            
            if ($submission->assignment->subpage->module->course) {
                $course = $submission->assignment->subpage->module->course;
                echo "Course: {$course->title}\n";
                echo "Course ID: {$course->id}\n\n";
                
                // Check teachers
                echo "Teachers assigned to this course:\n";
                $teachers = $course->teachers()->get();
                foreach ($teachers as $teacher) {
                    echo "  - {$teacher->name} (ID: {$teacher->id})\n";
                }
                echo "\n";
            } else {
                echo "ERROR: Course not found!\n";
            }
        } else {
            echo "ERROR: Module not found!\n";
        }
    } else {
        echo "ERROR: Subpage not found!\n";
    }
} else {
    echo "ERROR: Assignment not found!\n";
}

// Check admin users
echo "\nAdmin users:\n";
$admins = User::role('Admin')->get();
foreach ($admins as $admin) {
    echo "  - {$admin->name} (ID: {$admin->id})\n";
}

// Check teacher users
echo "\nTeacher users:\n";
$teachers = User::role('Teacher')->get();
foreach ($teachers as $teacher) {
    echo "  - {$teacher->name} (ID: {$teacher->id})\n";
}

echo "\nDone!\n";
