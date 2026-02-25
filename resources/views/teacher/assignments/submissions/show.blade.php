@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.dashboard') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.show', $course) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                    {{ $course->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                    {{ $module->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                    {{ $assignment->title }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page" style="color: #764ba2;">Submission by {{ $submission->user->name }}</li>
        </ol>
    </nav>
    <style>
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #667eea;
        }
        .breadcrumb-item a:hover {
            color: #764ba2 !important;
        }
    </style>

    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Header -->
            <div class="border-bottom pb-4 mb-4">
                <h1 class="h2 mb-3">Submission Details</h1>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Student:</strong> {{ $submission->user->name }}</p>
                        <p class="mb-2"><strong>Email:</strong> {{ $submission->user->email }}</p>
                        <p class="mb-2"><strong>Attempt:</strong> #{{ $submission->attempt_number ?? 1 }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        @if($submission->submitted_at)
                            <p class="mb-2"><strong>Submitted:</strong> {{ $submission->submitted_at->format('M d, Y g:i A') }}</p>
                        @endif
                        <div class="mt-2">
                            @if($submission->is_late)
                                <span class="badge bg-danger">Late Submission</span>
                            @else
                                <span class="badge bg-success">On Time</span>
                            @endif
                            @if($submission->status)
                                <span class="badge bg-secondary ms-2">{{ ucfirst($submission->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Content -->
            <div class="mb-4">
                <h2 class="h4 mb-3">Submission Content</h2>
                
                @if($submission->content)
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Text Submission</h3>
                            <div class="submission-content" style="white-space: pre-wrap;">
                                {{ $submission->content }}
                            </div>
                            @if($submission->word_count)
                                <p class="text-muted small mt-3 mb-0">Word Count: {{ number_format($submission->word_count) }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($submission->files && $submission->files->count() > 0)
                    <div class="card bg-light">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Attached Files</h3>
                            <ul class="list-group">
                                @foreach($submission->files as $file)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-muted me-2"></i>
                                            <div>
                                                <span>{{ $file->file_name }}</span>
                                                <small class="text-muted ms-2">({{ number_format($file->file_size / 1024, 2) }} KB)</small>
                                            </div>
                                        </div>
                                        <a href="{{ $file->getSignedUrl() }}" class="btn btn-sm btn-primary" download>
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            @if($submission->file_size_bytes)
                                <p class="text-muted small mt-3 mb-0">Total File Size: {{ number_format($submission->file_size_bytes / 1024, 2) }} KB</p>
                            @endif
                        </div>
                    </div>
                @elseif($submission->file_paths && count($submission->file_paths) > 0)
                    <!-- Fallback for legacy file_paths -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Attached Files</h3>
                            <ul class="list-group">
                                @foreach($submission->file_paths as $filePath)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-muted me-2"></i>
                                            <span>{{ basename($filePath) }}</span>
                                        </div>
                                        @if(Storage::exists($filePath))
                                            <a href="{{ route('teacher.courses.modules.subpages.assignments.submissions.download', [$course, $module, $subpage, $assignment, $submission, basename($filePath)]) }}" 
                                               class="btn btn-sm btn-primary" download>
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        @else
                                            <span class="badge bg-danger">File not found</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if(!$submission->content && (!$submission->files || $submission->files->count() === 0) && (!$submission->file_paths || count($submission->file_paths) === 0))
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>No content or files submitted.
                    </div>
                @endif
            </div>

            <!-- Grading Section -->
            <div class="border-top pt-4">
                <h2 class="h4 mb-3">Grading</h2>
                
                @if($submission->grade)
                    <div class="card bg-primary bg-opacity-10 mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0">Current Grade</h3>
                                <div class="text-end">
                                    <span class="h3 text-primary mb-0">{{ $submission->grade->score }} / {{ $assignment->max_score }}</span>
                                    @if($submission->grade->grade_letter)
                                        <span class="h4 text-primary ms-2">({{ $submission->grade->grade_letter }})</span>
                                    @endif
                                </div>
                            </div>
                            @if($submission->grade->feedback)
                                <div class="mt-3">
                                    <h4 class="h6 mb-2">Feedback:</h4>
                                    <div style="white-space: pre-wrap;">{{ $submission->grade->feedback }}</div>
                                </div>
                            @endif
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    <p class="mb-1"><strong>Graded by:</strong> {{ $submission->grade->grader->name ?? 'System' }}</p>
                                    <p class="mb-0"><strong>Graded on:</strong> {{ $submission->grade->graded_at ? $submission->grade->graded_at->format('M d, Y g:i A') : $submission->grade->created_at->format('M d, Y g:i A') }}</p>
                                </div>
                                @if($submission->grade->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('teacher.courses.modules.subpages.assignments.submissions.grade', [$course, $module, $subpage, $assignment, $submission]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Update Grade
                    </a>
                    @if(!$submission->grade->is_published)
                        <form action="{{ route('teacher.courses.modules.subpages.assignments.submissions.publish-grade', [$course, $module, $subpage, $assignment, $submission]) }}" 
                              method="POST" 
                              class="d-inline-block ms-2">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to publish this grade? The student will be able to see it.')">
                                <i class="fas fa-paper-plane me-1"></i>Publish Grade
                            </button>
                        </form>
                    @endif
                @else
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <p class="text-muted mb-0">This submission has not been graded yet.</p>
                        </div>
                    </div>
                    <a href="{{ route('teacher.courses.modules.subpages.assignments.submissions.grade', [$course, $module, $subpage, $assignment, $submission]) }}" 
                       class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i>Grade Submission
                    </a>
                @endif
            </div>

            <!-- Back Button -->
            <div class="mt-4 pt-4 border-top">
                <a href="{{ route('teacher.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Assignment
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
