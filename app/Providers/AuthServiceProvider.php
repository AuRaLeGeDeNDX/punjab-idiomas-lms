<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\FileUpload;
use App\Policies\CoursePolicy;
use App\Policies\CourseAssignmentPolicy;
use App\Policies\FileUploadPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        FileUpload::class => FileUploadPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Subpage::class => \App\Policies\SubpagePolicy::class,
        \App\Models\Content::class => \App\Policies\ContentPolicy::class,
        \App\Models\Exercise::class => \App\Policies\ExercisePolicy::class,
        \App\Models\ExerciseSubmission::class => \App\Policies\ExerciseSubmissionPolicy::class,
        \App\Models\SubmissionFile::class => \App\Policies\SubmissionFilePolicy::class,
        \App\Models\Assignment::class => \App\Policies\AssignmentPolicy::class,
        \App\Models\Submission::class => \App\Policies\SubmissionPolicy::class,
        \App\Models\Grade::class => \App\Policies\GradePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // File-related gates
        Gate::define('view-file', [FileUploadPolicy::class, 'view']);
        Gate::define('download-file', [FileUploadPolicy::class, 'download']);
        Gate::define('update-file', [FileUploadPolicy::class, 'update']);
        Gate::define('delete-file', [FileUploadPolicy::class, 'delete']);
        Gate::define('upload-to-course', [FileUploadPolicy::class, 'uploadToCourse']);

        // Course assignment gates
        Gate::define('canAssignStudents', [CourseAssignmentPolicy::class, 'canAssignStudents']);
        Gate::define('canRemoveStudents', [CourseAssignmentPolicy::class, 'canRemoveStudents']);
        Gate::define('canViewEnrolledStudents', [CourseAssignmentPolicy::class, 'canViewEnrolledStudents']);
        Gate::define('canViewAvailableStudents', [CourseAssignmentPolicy::class, 'canViewAvailableStudents']);
    }
}