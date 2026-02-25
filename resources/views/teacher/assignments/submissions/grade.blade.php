@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.show', $course) }}" class="text-decoration-none">
                    {{ $course->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}" class="text-decoration-none">
                    {{ $module->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" class="text-decoration-none">
                    {{ $assignment->title }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Grade Submission</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Left Column: Submission Details -->
        <div class="col-lg-6 mb-4">
            <!-- Submission Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Submission Details</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Student:</dt>
                        <dd class="col-sm-8">{{ $submission->user->name }}</dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $submission->user->email }}</dd>
                        
                        <dt class="col-sm-4">Submitted:</dt>
                        <dd class="col-sm-8">
                            @if($submission->submitted_at)
                                {{ $submission->submitted_at->format('M d, Y g:i A') }}
                            @else
                                <span class="text-muted">Not submitted</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            @if($submission->is_late)
                                <span class="badge bg-danger">Late</span>
                            @else
                                <span class="badge bg-success">On Time</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Max Score:</dt>
                        <dd class="col-sm-8">{{ $assignment->max_score }} points</dd>
                    </dl>
                </div>
            </div>

            <!-- Text Content -->
            @if($submission->content)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">Submission Text</h3>
                    <div style="white-space: pre-wrap;">{{ $submission->content }}</div>
                    @if($submission->word_count)
                        <p class="text-muted small mt-3 mb-0">Word Count: {{ number_format($submission->word_count) }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Files -->
            @if($submission->files && $submission->files->count() > 0)
            <div class="card shadow-sm">
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
                </div>
            </div>
            @elseif($submission->file_paths && count($submission->file_paths) > 0)
            <div class="card shadow-sm">
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
        </div>

        <!-- Right Column: Grading Form -->
        <div class="col-lg-6">
            <form action="{{ route('teacher.courses.modules.subpages.assignments.submissions.store-grade', [$course, $module, $subpage, $assignment, $submission]) }}" method="POST">
                @csrf
                
                <!-- Score Input -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Grade</h3>
                        
                        <div class="mb-3">
                            <label for="score" class="form-label">Score (out of {{ $assignment->max_score }})</label>
                            <input type="number" 
                                   class="form-control @error('score') is-invalid @enderror" 
                                   id="score" 
                                   name="score" 
                                   min="0" 
                                   max="{{ $assignment->max_score }}" 
                                   step="0.01"
                                   value="{{ old('score', $submission->grade->score ?? '') }}"
                                   required>
                            @error('score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Percentage:</span>
                                <strong id="percentage-display">0%</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Feedback</h3>
                        <textarea class="form-control @error('feedback') is-invalid @enderror" 
                                  id="feedback" 
                                  name="feedback" 
                                  rows="8" 
                                  placeholder="Enter feedback for the student...">{{ old('feedback', $submission->grade->feedback ?? '') }}</textarea>
                        @error('feedback')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Publishing Options -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="h5 mb-3">Publishing</h3>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="publish_immediately" 
                                   id="publish_immediately" 
                                   value="1"
                                   {{ old('publish_immediately', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="publish_immediately">
                                <strong>Publish grade immediately</strong>
                                <br>
                                <small class="text-muted">If unchecked, the grade will be saved as a draft and you'll need to publish it manually later.</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('teacher.courses.modules.subpages.assignments.submissions.show', [$course, $module, $subpage, $assignment, $submission]) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Save Grade
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Previous Grade Info -->
            @if($submission->grade)
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="h5 mb-3">Previous Grade</h3>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Score:</dt>
                        <dd class="col-sm-8">{{ $submission->grade->score }} / {{ $assignment->max_score }}</dd>
                        
                        <dt class="col-sm-4">Graded by:</dt>
                        <dd class="col-sm-8">{{ $submission->grade->grader->name ?? 'System' }}</dd>
                        
                        <dt class="col-sm-4">Graded at:</dt>
                        <dd class="col-sm-8">
                            {{ $submission->grade->graded_at ? $submission->grade->graded_at->format('M d, Y g:i A') : $submission->grade->created_at->format('M d, Y g:i A') }}
                        </dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            @if($submission->grade->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-warning text-dark">Draft</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Calculate percentage
document.getElementById('score').addEventListener('input', function() {
    const score = parseFloat(this.value) || 0;
    const maxScore = {{ $assignment->max_score }};
    const percentage = (score / maxScore * 100).toFixed(1);
    document.getElementById('percentage-display').textContent = percentage + '%';
});

// Initialize percentage display
document.getElementById('score').dispatchEvent(new Event('input'));
</script>
@endpush

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

@endsection
