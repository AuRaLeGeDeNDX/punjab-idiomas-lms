@extends('layouts.app')

@section('title', $safeExercise['title'] . ' - Exercise')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.enrolled') }}">My Courses</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}">
                    {{ $subpage->title }}
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $safeExercise['title'] }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Exercise Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-tasks"></i> {{ $safeExercise['title'] }}
                    </h4>
                    @if($safeExercise['due_date'])
                        <small class="text-muted">
                            Due: {{ \Carbon\Carbon::parse($safeExercise['due_date'])->format('M j, Y g:i A') }}
                            @if(\Carbon\Carbon::parse($safeExercise['due_date'])->isPast())
                                <span class="badge badge-danger ml-1">Overdue</span>
                            @endif
                        </small>
                    @endif
                </div>
                <div class="card-body">
                    @if($safeExercise['description'])
                        <div class="mb-3">
                            <h6>Description:</h6>
                            <p class="text-muted">{{ $safeExercise['description'] }}</p>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h6>Question:</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($safeExercise['question'])) !!}
                        </div>
                    </div>

                    @if($safeExercise['instructions'])
                        <div class="mb-3">
                            <h6>Instructions:</h6>
                            <div class="alert alert-info">
                                {!! nl2br(e($safeExercise['instructions'])) !!}
                            </div>
                        </div>
                    @endif

                    <!-- SECURITY NOTE: Answer is NEVER displayed to students -->
                    <!-- The $safeExercise array explicitly excludes the 'answer' field -->
                </div>
            </div>

            <!-- Submission Status -->
            @if($submission)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-check-circle"></i> Your Submission
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                @if($submission->status === 'graded')
                                    <span class="badge badge-success">Graded</span>
                                @else
                                    <span class="badge badge-warning">Submitted</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Submitted:</strong>
                                {{ $submission->submitted_at->format('M j, Y g:i A') }}
                                @if($submission->isLate())
                                    <span class="badge badge-warning ml-1">Late</span>
                                @endif
                            </div>
                        </div>

                        @if($submission->status === 'graded')
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Score:</strong>
                                    <span class="h5 text-primary">
                                        {{ $submission->score }}/{{ $safeExercise['max_score'] }}
                                        ({{ round($submission->score_percentage, 1) }}%)
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Graded:</strong>
                                    {{ $submission->graded_at->format('M j, Y g:i A') }}
                                </div>
                            </div>

                            @if($submission->feedback)
                                <hr>
                                <div>
                                    <strong>Feedback:</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        {!! nl2br(e($submission->feedback)) !!}
                                    </div>
                                </div>
                            @endif
                        @endif

                        <hr>
                        <div>
                            <strong>Your Response:</strong>
                            @if($submission->text_response)
                                <div class="mt-2 p-3 border rounded">
                                    {!! nl2br(e($submission->text_response)) !!}
                                </div>
                            @endif

                            @if($submission->file_name)
                                <div class="mt-2">
                                    <a href="{{ route('student.courses.modules.subpages.exercises.download-submission', [$course, $module, $subpage, $safeExercise['id']]) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download"></i> {{ $submission->file_name }}
                                        ({{ $submission->formatted_file_size }})
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Submit Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-upload"></i> Submit Your Response
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" 
                              action="{{ route('student.courses.modules.subpages.exercises.store-submission', [$course, $module, $subpage, $safeExercise['id']]) }}" 
                              enctype="multipart/form-data">
                            @csrf

                            @if(in_array($safeExercise['submission_type'], ['text', 'both']))
                                <div class="form-group">
                                    <label for="text_response">Text Response:</label>
                                    <textarea name="text_response" 
                                              id="text_response" 
                                              class="form-control @error('text_response') is-invalid @enderror" 
                                              rows="8" 
                                              placeholder="Enter your response here..."
                                              {{ $safeExercise['submission_type'] === 'text' ? 'required' : '' }}>{{ old('text_response') }}</textarea>
                                    @error('text_response')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            @if(in_array($safeExercise['submission_type'], ['file', 'both']))
                                <div class="form-group">
                                    <label for="file">File Upload:</label>
                                    <input type="file" 
                                           name="file" 
                                           id="file" 
                                           class="form-control-file @error('file') is-invalid @enderror"
                                           {{ $safeExercise['submission_type'] === 'file' ? 'required' : '' }}>
                                    <small class="form-text text-muted">
                                        Maximum file size: 50MB
                                    </small>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Response
                                </button>
                                <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                                   class="btn btn-secondary ml-2">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Exercise Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Exercise Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Submission Type:</strong>
                        <span class="badge badge-info">
                            {{ ucfirst(str_replace('_', ' ', $safeExercise['submission_type'])) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Maximum Score:</strong>
                        {{ $safeExercise['max_score'] }} points
                    </div>
                    @if($safeExercise['due_date'])
                        <div class="mb-2">
                            <strong>Due Date:</strong>
                            {{ \Carbon\Carbon::parse($safeExercise['due_date'])->format('M j, Y g:i A') }}
                        </div>
                    @endif
                    <div>
                        <strong>Status:</strong>
                        @if($submission)
                            @if($submission->status === 'graded')
                                <span class="badge badge-success">Completed</span>
                            @else
                                <span class="badge badge-warning">Submitted</span>
                            @endif
                        @else
                            <span class="badge badge-secondary">Not Submitted</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-list"></i> Other Exercises
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($subpage->activeExercises as $otherExercise)
                            <a href="{{ route('student.courses.modules.subpages.exercises.show', [$course, $module, $subpage, $otherExercise]) }}" 
                               class="list-group-item list-group-item-action {{ $otherExercise->id == $safeExercise['id'] ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{{ $otherExercise->title }}</span>
                                    @php
                                        $otherSubmission = $otherExercise->submissionFor(auth()->user());
                                    @endphp
                                    @if($otherSubmission)
                                        @if($otherSubmission->status === 'graded')
                                            <i class="fas fa-check-circle text-success"></i>
                                        @else
                                            <i class="fas fa-clock text-warning"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-circle text-muted"></i>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-save draft functionality (optional)
    const textArea = document.getElementById('text_response');
    if (textArea) {
        let saveTimeout;
        textArea.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                // Save draft to localStorage
                localStorage.setItem('exercise_{{ $safeExercise["id"] }}_draft', this.value);
            }, 1000);
        });

        // Load draft on page load
        const draft = localStorage.getItem('exercise_{{ $safeExercise["id"] }}_draft');
        if (draft && !textArea.value) {
            textArea.value = draft;
        }
    }

    // Clear draft on successful submission
    @if($submission)
        localStorage.removeItem('exercise_{{ $safeExercise["id"] }}_draft');
    @endif
});
</script>
@endpush