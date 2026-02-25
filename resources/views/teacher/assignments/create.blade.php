@extends('layouts.app')

@section('title', 'Create Assignment')

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/creative-professional.css'
])
@endpush

@php
    $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
@endphp

@section('content')
<div class="container-fluid">
    <!-- Page Header with Breadcrumb -->
    <div class="creative-page-header fade-in-up">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.dashboard') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.courses.show', $course) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $course->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('teacher.modules.show', [$course, $module]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $module->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.courses.modules.subpages.show', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $subpage->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            Assignments
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color: #764ba2;">Create</li>
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
            <h1><i class="fas fa-plus-circle me-2"></i>Create Assignment</h1>
            <p>{{ $subpage->title }} - {{ $module->title }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please fix the following issues:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route($routePrefix . '.courses.modules.subpages.assignments.store', [$course, $module, $subpage]) }}">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="creative-card mb-4 fade-in-up stagger-1">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-file-alt"></i> Assignment Details</h3>
                    </div>
                    <div class="creative-card-body">
                        <!-- Title -->
                        <div class="creative-form-group">
                            <label for="title" class="creative-form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="creative-form-input @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="creative-form-group">
                            <label for="description" class="creative-form-label">Description</label>
                            <textarea class="creative-form-input @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Instructions -->
                        <div class="creative-form-group">
                            <label for="instructions" class="creative-form-label">Instructions</label>
                            <textarea class="creative-form-input @error('instructions') is-invalid @enderror" 
                                      id="instructions" 
                                      name="instructions" 
                                      rows="5">{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Detailed instructions for students on how to complete this assignment.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Settings Card -->
                <div class="creative-card mb-4 fade-in-up stagger-2">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-cog"></i> Settings</h3>
                    </div>
                    <div class="creative-card-body">
                        <!-- Assignment Type -->
                        <div class="creative-form-group">
                            <label for="assignment_type" class="creative-form-label">Type <span class="text-danger">*</span></label>
                            <select class="creative-form-input @error('assignment_type') is-invalid @enderror" 
                                    id="assignment_type" 
                                    name="assignment_type" 
                                    required>
                                <option value="">Select type...</option>
                                <option value="homework" {{ old('assignment_type') == 'homework' ? 'selected' : '' }}>Homework</option>
                                <option value="project" {{ old('assignment_type') == 'project' ? 'selected' : '' }}>Project</option>
                                <option value="quiz" {{ old('assignment_type') == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                <option value="exam" {{ old('assignment_type') == 'exam' ? 'selected' : '' }}>Exam</option>
                                <option value="essay" {{ old('assignment_type') == 'essay' ? 'selected' : '' }}>Essay</option>
                            </select>
                            @error('assignment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submission Type -->
                        <div class="creative-form-group">
                            <label for="submission_type" class="creative-form-label">Submission Type <span class="text-danger">*</span></label>
                            <select class="creative-form-input @error('submission_type') is-invalid @enderror" 
                                    id="submission_type" 
                                    name="submission_type" 
                                    required>
                                <option value="">Select type...</option>
                                <option value="text" {{ old('submission_type') == 'text' ? 'selected' : '' }}>Text Only</option>
                                <option value="file" {{ old('submission_type') == 'file' ? 'selected' : '' }}>File Upload</option>
                                <option value="both" {{ old('submission_type') == 'both' ? 'selected' : '' }}>Text & File</option>
                            </select>
                            @error('submission_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Max Score -->
                        <div class="creative-form-group">
                            <label for="max_score" class="creative-form-label">Max Score <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="creative-form-input @error('max_score') is-invalid @enderror" 
                                   id="max_score" 
                                   name="max_score" 
                                   value="{{ old('max_score', 100) }}" 
                                   min="0" 
                                   max="1000" 
                                   required>
                            @error('max_score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div class="creative-form-group">
                            <label for="due_date" class="creative-form-label">Due Date</label>
                            <input type="datetime-local" 
                                   class="creative-form-input @error('due_date') is-invalid @enderror" 
                                   id="due_date" 
                                   name="due_date" 
                                   value="{{ old('due_date') }}">
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Allow Late Submission -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="allow_late_submission" 
                                   name="allow_late_submission" 
                                   value="1" 
                                   {{ old('allow_late_submission') ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_late_submission">
                                Allow late submissions
                            </label>
                        </div>

                        <!-- Auto Grade -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="auto_grade" 
                                   name="auto_grade" 
                                   value="1" 
                                   {{ old('auto_grade') ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_grade">
                                Auto-grade (for quizzes)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Publishing Card -->
                <div class="creative-card mb-4 fade-in-up stagger-3">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-paper-plane"></i> Publishing</h3>
                    </div>
                    <div class="creative-card-body">
                        <!-- Publish Now -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="is_published" 
                                   name="is_published" 
                                   value="1" 
                                   {{ old('is_published') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">
                                <strong>Publish immediately</strong>
                            </label>
                            <small class="d-block text-muted">Make this assignment visible to students now</small>
                        </div>

                        <div class="text-center my-3">
                            <span class="text-muted">— OR —</span>
                        </div>

                        <!-- Schedule Publication -->
                        <div class="creative-form-group">
                            <label for="scheduled_publish_at" class="creative-form-label">
                                <i class="fas fa-clock me-1"></i>Schedule Publication
                            </label>
                            <input type="datetime-local" 
                                   class="creative-form-input @error('scheduled_publish_at') is-invalid @enderror" 
                                   id="scheduled_publish_at" 
                                   name="scheduled_publish_at" 
                                   value="{{ old('scheduled_publish_at') }}">
                            @error('scheduled_publish_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Assignment will be automatically published at this date/time
                            </small>
                        </div>

                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Note:</strong> You cannot schedule publication if you publish immediately.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2 fade-in-up stagger-4">
                    <button type="submit" class="creative-btn creative-btn-primary">
                        <i class="fas fa-save me-2"></i>Create Assignment
                    </button>
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" 
                       class="creative-btn creative-btn-outline">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const publishCheckbox = document.getElementById('is_published');
    const scheduleInput = document.getElementById('scheduled_publish_at');

    // Disable schedule input when publish immediately is checked
    publishCheckbox.addEventListener('change', function() {
        if (this.checked) {
            scheduleInput.value = '';
            scheduleInput.disabled = true;
        } else {
            scheduleInput.disabled = false;
        }
    });

    // Uncheck publish immediately when schedule is set
    scheduleInput.addEventListener('change', function() {
        if (this.value) {
            publishCheckbox.checked = false;
        }
    });

    // Initialize state
    if (publishCheckbox.checked) {
        scheduleInput.disabled = true;
    }
});
</script>
@endpush
@endsection
