@extends('layouts.app')

@section('title', 'Grade Submission')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.grading.index') }}">Back to Queue</a>
    </li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Submission Details -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $submission->assignment->title }}</h5>
                    <div class="d-flex gap-2">
                        @if($submission->is_late)
                            <span class="badge bg-danger">Late Submission</span>
                        @endif
                        <span class="badge bg-{{ $submission->status === 'graded' ? 'success' : 'warning' }}">
                            {{ ucfirst($submission->status) }}
                        </span>
                    </div>
                </div>
                <div class="text-muted mt-2">
                    <small>
                        <strong>Course:</strong> {{ $submission->assignment->course->title }} |
                        <strong>Module:</strong> {{ $submission->assignment->module->title }} |
                        <strong>Due:</strong> {{ $submission->assignment->due_date->format('M j, Y g:i A') }}
                    </small>
                </div>
            </div>
            <div class="card-body">
                <!-- Student Info -->
                <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                    <img src="{{ $submission->user->avatar ?? '/images/default-avatar.png' }}" 
                         alt="{{ $submission->user->name }}" class="rounded-circle me-3" width="48" height="48">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $submission->user->name }}</h6>
                        <small class="text-muted">{{ $submission->user->email }}</small>
                    </div>
                    <div class="text-end">
                        <div class="text-muted">
                            <small>Submitted:</small><br>
                            <strong>{{ $submission->submitted_at->format('M j, Y g:i A') }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Assignment Instructions -->
                @if($submission->assignment->instructions)
                    <div class="mb-4">
                        <h6>Assignment Instructions:</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($submission->assignment->instructions)) !!}
                        </div>
                    </div>
                @endif

                <!-- Submission Content -->
                <div class="mb-4">
                    <h6>Student Submission:</h6>
                    <div class="p-3 border rounded">
                        @if($submission->content)
                            {!! nl2br(e($submission->content)) !!}
                        @else
                            <em class="text-muted">No text content provided</em>
                        @endif
                    </div>
                </div>

                <!-- Submitted Files -->
                @if($submission->files->count() > 0)
                    <div class="mb-4">
                        <h6>Submitted Files:</h6>
                        <div class="list-group">
                            @foreach($submission->files as $file)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file me-2"></i>
                                        <div>
                                            <div class="fw-medium">{{ $file->original_name }}</div>
                                            <small class="text-muted">{{ $file->size_formatted }} • {{ $file->created_at->format('M j, g:i A') }}</small>
                                        </div>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('files.view', $file) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Previous Submissions -->
        @if($previousSubmissions->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Previous Submissions</h6>
                </div>
                <div class="card-body">
                    @foreach($previousSubmissions as $prevSubmission)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-medium">Attempt {{ $loop->iteration }}</span>
                                <small class="text-muted">{{ $prevSubmission->submitted_at->format('M j, g:i A') }}</small>
                            </div>
                            @if($prevSubmission->grade)
                                <small class="text-success">Grade: {{ $prevSubmission->grade->score }}/{{ $submission->assignment->max_points }}</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Grading Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Grade Submission</h6>
            </div>
            <div class="card-body">
                <form id="gradingForm">
                    @csrf
                    
                    <!-- Score Input -->
                    <div class="mb-3">
                        <label for="score" class="form-label">
                            Score <span class="text-muted">(out of {{ $submission->assignment->max_points }})</span>
                        </label>
                        <input type="number" class="form-control" id="score" name="score" 
                               min="0" max="{{ $submission->assignment->max_points }}" 
                               value="{{ $submission->grade->score ?? '' }}" required>
                        <div class="form-text">
                            Percentage: <span id="percentage">{{ $submission->grade ? round(($submission->grade->score / $submission->assignment->max_points) * 100, 1) : 0 }}%</span>
                        </div>
                    </div>

                    <!-- Rubric Grading -->
                    @if($rubric)
                        <div class="mb-3">
                            <label class="form-label">Rubric Scoring</label>
                            @foreach($rubric['criteria'] as $index => $criterion)
                                <div class="mb-2">
                                    <label class="form-label small">{{ $criterion['name'] }} ({{ $criterion['points'] }} pts)</label>
                                    <input type="number" class="form-control form-control-sm rubric-score" 
                                           name="rubric_scores[{{ $index }}]" 
                                           min="0" max="{{ $criterion['points'] }}"
                                           data-max="{{ $criterion['points'] }}">
                                    <small class="text-muted">{{ $criterion['description'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Feedback -->
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback for Student</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="4" 
                                  placeholder="Provide constructive feedback...">{{ $submission->grade->feedback ?? '' }}</textarea>
                    </div>

                    <!-- Private Notes -->
                    <div class="mb-3">
                        <label for="private_notes" class="form-label">Private Notes</label>
                        <textarea class="form-control" id="private_notes" name="private_notes" rows="2" 
                                  placeholder="Notes for your reference only...">{{ $submission->grade->private_notes ?? '' }}</textarea>
                    </div>

                    <!-- Return to Student -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="return_to_student" name="return_to_student" value="1">
                            <label class="form-check-label" for="return_to_student">
                                Return grade to student immediately
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Save Grade
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="saveAndNext()">
                            <i class="fas fa-arrow-right me-1"></i>Save & Next
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assignment Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Assignment Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Max Points:</strong> {{ $submission->assignment->max_points }}
                </div>
                <div class="mb-2">
                    <strong>Due Date:</strong> {{ $submission->assignment->due_date->format('M j, Y g:i A') }}
                </div>
                @if($submission->assignment->late_penalty)
                    <div class="mb-2">
                        <strong>Late Penalty:</strong> {{ $submission->assignment->late_penalty }}% per day
                    </div>
                @endif
                @if($submission->assignment->attempts_allowed)
                    <div class="mb-2">
                        <strong>Attempts Allowed:</strong> {{ $submission->assignment->attempts_allowed }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Student Performance Context -->
        @if($studentGrades->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Student's Course Performance</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Course Average:</strong> 
                        {{ round($studentGrades->avg('score'), 1) }}/{{ $studentGrades->first()->submission->assignment->max_points }}
                        ({{ round(($studentGrades->avg('score') / $studentGrades->first()->submission->assignment->max_points) * 100, 1) }}%)
                    </div>
                    <div class="mb-2">
                        <strong>Assignments Completed:</strong> {{ $studentGrades->count() }}
                    </div>
                    
                    <!-- Recent Grades -->
                    <div class="mt-3">
                        <small class="text-muted">Recent Grades:</small>
                        @foreach($studentGrades->take(3) as $grade)
                            <div class="d-flex justify-content-between small">
                                <span>{{ Str::limit($grade->submission->assignment->title, 20) }}</span>
                                <span class="text-{{ $grade->score >= ($grade->submission->assignment->max_points * 0.8) ? 'success' : ($grade->score >= ($grade->submission->assignment->max_points * 0.6) ? 'warning' : 'danger') }}">
                                    {{ $grade->score }}/{{ $grade->submission->assignment->max_points }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Calculate percentage when score changes
document.getElementById('score').addEventListener('input', function() {
    const score = parseFloat(this.value) || 0;
    const maxPoints = {{ $submission->assignment->max_points }};
    const percentage = Math.round((score / maxPoints) * 100 * 10) / 10;
    document.getElementById('percentage').textContent = percentage + '%';
});

// Auto-calculate total from rubric scores
document.querySelectorAll('.rubric-score').forEach(input => {
    input.addEventListener('input', function() {
        let total = 0;
        document.querySelectorAll('.rubric-score').forEach(rubricInput => {
            total += parseFloat(rubricInput.value) || 0;
        });
        document.getElementById('score').value = total;
        document.getElementById('score').dispatchEvent(new Event('input'));
    });
});

// Handle form submission
document.getElementById('gradingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    
    fetch('{{ route("teacher.grading.grade", $submission) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            if (data.next_submission_url) {
                // Optionally redirect to next submission
            } else {
                window.location.href = '{{ route("teacher.grading.index") }}';
            }
        } else {
            alert('Error: ' + (data.error || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the grade.');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});

function saveAndNext() {
    const form = document.getElementById('gradingForm');
    const formData = new FormData(form);
    
    fetch('{{ route("teacher.grading.grade", $submission) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            if (data.next_submission_url) {
                window.location.href = data.next_submission_url;
            } else {
                alert('No more submissions to grade!');
                window.location.href = '{{ route("teacher.grading.index") }}';
            }
        } else {
            alert('Error: ' + (data.error || 'Unknown error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the grade.');
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 's':
                e.preventDefault();
                document.getElementById('gradingForm').dispatchEvent(new Event('submit'));
                break;
            case 'n':
                e.preventDefault();
                saveAndNext();
                break;
        }
    }
});
</script>
@endpush