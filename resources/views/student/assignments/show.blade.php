@extends('layouts.app')

@section('title', $assignment->title)

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/creative-professional.css'
])
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route('student.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.show', $course) }}" class="text-decoration-none">
                    {{ $course->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.module', [$course, $module]) }}" class="text-decoration-none">
                    {{ $module->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" class="text-decoration-none">
                    {{ $subpage->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" class="text-decoration-none">
                    Assignments
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $assignment->title }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="creative-page-header fade-in-up mb-4">
        <div>
            <h1><i class="fas fa-clipboard-list me-2"></i>{{ $assignment->title }}</h1>
            <p class="text-muted">{{ $subpage->title }} - {{ $module->title }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Assignment Details Card -->
            <div class="creative-card mb-4 fade-in-up">
                <div class="creative-card-header">
                    <h3><i class="fas fa-info-circle"></i> Assignment Details</h3>
                </div>
                <div class="creative-card-body">
                    @if($assignment->description)
                        <div class="mb-4">
                            <h5 class="text-muted mb-2">Description</h5>
                            <p>{{ $assignment->description }}</p>
                        </div>
                    @endif

                    @if($assignment->instructions)
                        <div class="mb-4">
                            <h5 class="text-muted mb-2">Instructions</h5>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($assignment->instructions)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Type:</strong>
                            <span class="creative-badge creative-badge-secondary ms-2">
                                {{ ucfirst($assignment->assignment_type) }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Submission Type:</strong>
                            <span class="creative-badge creative-badge-info ms-2">
                                {{ ucfirst($assignment->submission_type) }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Max Score:</strong>
                            <span class="ms-2">{{ $assignment->max_score }} points</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Due Date:</strong>
                            @if($assignment->due_date)
                                <span class="ms-2 {{ $assignment->isOverdue() ? 'text-danger' : '' }}">
                                    {{ $assignment->due_date->format('M j, Y g:i A') }}
                                    @if($assignment->isOverdue())
                                        <span class="creative-badge creative-badge-danger ms-1">Overdue</span>
                                    @else
                                        <small class="text-muted">({{ $assignment->due_date->diffForHumans() }})</small>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted ms-2">No due date</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Late Submissions:</strong>
                            <span class="ms-2">
                                @if($assignment->allow_late_submission)
                                    <i class="fas fa-check text-success"></i> Allowed
                                @else
                                    <i class="fas fa-times text-danger"></i> Not Allowed
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Status Card -->
            @if($submission)
                <div class="creative-card mb-4 fade-in-up stagger-1">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-file-upload"></i> Your Submission</h3>
                    </div>
                    <div class="creative-card-body">
                        <div class="mb-3">
                            <strong>Status:</strong>
                            @if($submission->grade && $submission->grade->is_published)
                                <span class="creative-badge creative-badge-success ms-2">Graded</span>
                            @elseif($submission->grade)
                                <span class="creative-badge creative-badge-info ms-2">Graded (Unpublished)</span>
                            @else
                                <span class="creative-badge creative-badge-warning ms-2">Pending Review</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <strong>Submitted:</strong>
                            <span class="ms-2">{{ $submission->submitted_at->format('M j, Y g:i A') }}</span>
                            @if($submission->is_late)
                                <span class="creative-badge creative-badge-danger ms-2">Late Submission</span>
                            @endif
                        </div>

                        @if($submission->content)
                            <div class="mb-3">
                                <strong>Your Response:</strong>
                                <div class="p-3 bg-light rounded mt-2">
                                    {!! nl2br(e($submission->content)) !!}
                                </div>
                                @if($submission->word_count)
                                    <small class="text-muted">Word count: {{ $submission->word_count }}</small>
                                @endif
                            </div>
                        @endif

                        @if($submission->getFilePaths() && count($submission->getFilePaths()) > 0)
                            <div class="mb-3">
                                <strong>Attached Files:</strong>
                                <ul class="list-group mt-2">
                                    @foreach($submission->getFilePaths() as $filePath)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-file me-2"></i>{{ basename($filePath) }}</span>
                                            <a href="{{ route('student.courses.modules.subpages.assignments.submissions.download', [$course, $module, $subpage, $assignment, $submission, basename($filePath)]) }}" 
                                               class="creative-btn creative-btn-outline creative-btn-sm">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Grade Display -->
                        @if($submission->grade && $submission->grade->is_published)
                            <div class="alert alert-success mt-4">
                                <h5 class="alert-heading"><i class="fas fa-star me-2"></i>Your Grade</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Score:</strong>
                                        <span class="h4 ms-2">{{ $submission->grade->score }} / {{ $assignment->max_score }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Letter Grade:</strong>
                                        <span class="h4 ms-2">{{ $submission->grade->getLetterGrade() }}</span>
                                    </div>
                                </div>
                                @if($submission->grade->feedback)
                                    <hr>
                                    <strong>Feedback:</strong>
                                    <div class="mt-2">
                                        {!! nl2br(e($submission->grade->feedback)) !!}
                                    </div>
                                @endif
                                <hr>
                                <small class="text-muted">
                                    Graded on {{ $submission->grade->published_at->format('M j, Y g:i A') }}
                                </small>
                            </div>
                        @endif

                        <!-- Edit Submission Button -->
                        @if(!$submission->isGraded() && $canSubmit)
                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ route('student.courses.modules.subpages.assignments.submissions.edit', [$course, $module, $subpage, $assignment, $submission]) }}" 
                                   class="creative-btn creative-btn-warning">
                                    <i class="fas fa-edit me-2"></i>Edit Submission
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Info Card -->
            <div class="creative-card mb-4 fade-in-up stagger-2">
                <div class="creative-card-header">
                    <h3><i class="fas fa-info"></i> Quick Info</h3>
                </div>
                <div class="creative-card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Max Score</span>
                            <strong class="h5 mb-0">{{ $assignment->max_score }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Your Score</span>
                            @if($submission && $submission->grade && $submission->grade->is_published)
                                <strong class="h5 mb-0 text-{{ $submission->grade->score >= ($assignment->max_score * 0.7) ? 'success' : 'danger' }}">
                                    {{ $submission->grade->score }}
                                </strong>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Status</span>
                            @if($submission)
                                @if($submission->grade && $submission->grade->is_published)
                                    <span class="creative-badge creative-badge-success">Graded</span>
                                @else
                                    <span class="creative-badge creative-badge-warning">Submitted</span>
                                @endif
                            @elseif($canSubmit)
                                <span class="creative-badge creative-badge-primary">Not Submitted</span>
                            @else
                                <span class="creative-badge creative-badge-danger">Unavailable</span>
                            @endif
                        </div>
                    </div>
                    @if($assignment->due_date)
                        <hr>
                        <div>
                            <div class="text-muted mb-1">Due Date</div>
                            <div class="{{ $assignment->isOverdue() ? 'text-danger' : '' }}">
                                {{ $assignment->due_date->format('M j, Y') }}<br>
                                <small>{{ $assignment->due_date->format('g:i A') }}</small>
                            </div>
                            <small class="text-muted">{{ $assignment->due_date->diffForHumans() }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="creative-card fade-in-up stagger-3">
                <div class="creative-card-header">
                    <h3><i class="fas fa-tasks"></i> Actions</h3>
                </div>
                <div class="creative-card-body">
                    <div class="d-grid gap-2">
                        @if($canSubmit && !$submission)
                            <a href="{{ route('student.courses.modules.subpages.assignments.submit', [$course, $module, $subpage, $assignment]) }}" 
                               class="creative-btn creative-btn-primary">
                                <i class="fas fa-upload me-2"></i>Submit Assignment
                            </a>
                        @elseif($submission && !$submission->isGraded() && $canSubmit)
                            <a href="{{ route('student.courses.modules.subpages.assignments.submissions.edit', [$course, $module, $subpage, $assignment, $submission]) }}" 
                               class="creative-btn creative-btn-warning">
                                <i class="fas fa-edit me-2"></i>Edit Submission
                            </a>
                        @elseif(!$canSubmit && !$submission)
                            <button class="creative-btn creative-btn-secondary" disabled>
                                <i class="fas fa-lock me-2"></i>Submission Unavailable
                            </button>
                        @endif

                        <a href="{{ route('student.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" 
                           class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left me-2"></i>Back to Assignments
                        </a>

                        <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                           class="creative-btn creative-btn-outline">
                            <i class="fas fa-book me-2"></i>Back to {{ $subpage->title }}
                        </a>
                    </div>

                    @if(!$canSubmit && !$submission && $assignment->isOverdue())
                        <div class="alert alert-danger mt-3 mb-0">
                            <small>
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                This assignment is overdue and late submissions are not allowed.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
}

.breadcrumb-item a {
    color: #667eea;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #764ba2;
}
</style>
@endpush
