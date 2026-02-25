@extends('layouts.app')

@section('title', 'Submit Assignment - ' . $assignment->title)

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/creative-professional.css'
])
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header with Breadcrumb -->
    <div class="creative-page-header fade-in-up">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.dashboard') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.show', $course) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $course->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.module', [$course, $module]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $module->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $subpage->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            Assignments
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $assignment->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color: #764ba2;">Submit</li>
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
            <h1><i class="fas fa-upload me-2"></i>Submit Assignment</h1>
            <p>{{ $assignment->title }}</p>
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

    <form method="POST" action="{{ route('student.courses.modules.subpages.assignments.store-submission', [$course, $module, $subpage, $assignment]) }}" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <!-- Assignment Instructions -->
                <div class="creative-card mb-4 fade-in-up">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-info-circle"></i> Assignment Instructions</h3>
                    </div>
                    <div class="creative-card-body">
                        @if($assignment->instructions)
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($assignment->instructions)) !!}
                            </div>
                        @else
                            <p class="text-muted">No specific instructions provided.</p>
                        @endif
                    </div>
                </div>

                <!-- Submission Form -->
                <div class="creative-card mb-4 fade-in-up stagger-1">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-edit"></i> Your Submission</h3>
                    </div>
                    <div class="creative-card-body">
                        @if(in_array($assignment->submission_type, ['text', 'both']))
                            <!-- Text Content -->
                            <div class="creative-form-group">
                                <label for="content" class="creative-form-label">
                                    Your Response 
                                    @if($assignment->submission_type === 'text')
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <textarea class="creative-form-input @error('content') is-invalid @enderror" 
                                          id="content" 
                                          name="content" 
                                          rows="10"
                                          placeholder="Enter your response here..."
                                          {{ $assignment->submission_type === 'text' ? 'required' : '' }}>{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 10 words required</small>
                            </div>
                        @endif

                        @if(in_array($assignment->submission_type, ['file', 'both']))
                            <!-- File Upload -->
                            <div class="creative-form-group">
                                <label for="files" class="creative-form-label">
                                    Upload Files 
                                    @if($assignment->submission_type === 'file')
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="file" 
                                       class="creative-form-input @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror" 
                                       id="files" 
                                       name="files[]" 
                                       multiple
                                       accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip"
                                       {{ $assignment->submission_type === 'file' ? 'required' : '' }}>
                                @error('files')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('files.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Maximum 5 files, 50MB each. Accepted formats: PDF, DOC, DOCX, TXT, JPG, PNG, ZIP
                                </small>
                            </div>

                            <!-- File Preview -->
                            <div id="file-preview" class="mt-3"></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Assignment Info -->
                <div class="creative-card mb-4 fade-in-up stagger-2">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-info"></i> Assignment Info</h3>
                    </div>
                    <div class="creative-card-body">
                        <div class="mb-3">
                            <strong>Type:</strong>
                            <span class="creative-badge creative-badge-secondary ms-2">
                                {{ ucfirst($assignment->assignment_type) }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Submission Type:</strong>
                            <span class="creative-badge creative-badge-info ms-2">
                                {{ ucfirst($assignment->submission_type) }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Max Score:</strong>
                            <span class="ms-2">{{ $assignment->max_score }} points</span>
                        </div>
                        @if($assignment->due_date)
                            <div class="mb-3">
                                <strong>Due Date:</strong>
                                <div class="mt-1 {{ $assignment->isOverdue() ? 'text-danger' : '' }}">
                                    {{ $assignment->due_date->format('M j, Y') }}<br>
                                    <small>{{ $assignment->due_date->format('g:i A') }}</small>
                                    <br>
                                    <small>{{ $assignment->due_date->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endif
                        @if($assignment->isOverdue())
                            <div class="alert alert-warning mb-0">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    This assignment is overdue. Your submission will be marked as late.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2 fade-in-up stagger-3">
                    <button type="submit" class="creative-btn creative-btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit Assignment
                    </button>
                    <a href="{{ route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
                       class="creative-btn creative-btn-outline">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>

                <div class="alert alert-info mt-3 fade-in-up stagger-4">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Note:</strong> You can edit your submission before it's graded.
                    </small>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('files');
    const filePreview = document.getElementById('file-preview');

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            filePreview.innerHTML = '';
            
            if (this.files.length > 0) {
                const previewContainer = document.createElement('div');
                previewContainer.className = 'alert alert-info';
                
                const title = document.createElement('strong');
                title.textContent = 'Selected Files:';
                previewContainer.appendChild(title);
                
                const fileList = document.createElement('ul');
                fileList.className = 'mb-0 mt-2';
                
                Array.from(this.files).forEach(file => {
                    const li = document.createElement('li');
                    li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                    fileList.appendChild(li);
                });
                
                previewContainer.appendChild(fileList);
                filePreview.appendChild(previewContainer);
            }
        });
    }
});
</script>
@endpush
@endsection
