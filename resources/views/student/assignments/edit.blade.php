@extends('layouts.app')

@section('title', 'Edit Submission - ' . $assignment->title)

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
                    <li class="breadcrumb-item active" style="color: #764ba2;">Edit Submission</li>
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
            <h1><i class="fas fa-edit me-2"></i>Edit Submission</h1>
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

    <form method="POST" action="{{ route('student.courses.modules.subpages.assignments.submissions.update', [$course, $module, $subpage, $assignment, $submission]) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- Current Submission -->
                <div class="creative-card mb-4 fade-in-up">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-file-alt"></i> Current Submission</h3>
                    </div>
                    <div class="creative-card-body">
                        <div class="mb-3">
                            <strong>Submitted:</strong>
                            <span class="ms-2">{{ $submission->submitted_at->format('M j, Y g:i A') }}</span>
                            @if($submission->is_late)
                                <span class="creative-badge creative-badge-danger ms-2">Late Submission</span>
                            @endif
                        </div>

                        @if($submission->content)
                            <div class="mb-3">
                                <strong>Current Response:</strong>
                                <div class="p-3 bg-light rounded mt-2">
                                    {!! nl2br(e($submission->content)) !!}
                                </div>
                            </div>
                        @endif

                        @if($submission->getFilePaths() && count($submission->getFilePaths()) > 0)
                            <div>
                                <strong>Current Files:</strong>
                                <ul class="list-group mt-2">
                                    @foreach($submission->getFilePaths() as $filePath)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <input type="checkbox" 
                                                       name="remove_files[]" 
                                                       value="{{ $filePath }}" 
                                                       id="remove_{{ md5($filePath) }}"
                                                       class="form-check-input me-2">
                                                <label for="remove_{{ md5($filePath) }}">
                                                    <i class="fas fa-file me-2"></i>{{ basename($filePath) }}
                                                </label>
                                            </div>
                                            <a href="{{ route('student.courses.modules.subpages.assignments.submissions.download', [$course, $module, $subpage, $assignment, $submission, basename($filePath)]) }}" 
                                               class="creative-btn creative-btn-outline creative-btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>Check the boxes to remove files
                                </small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Edit Submission Form -->
                <div class="creative-card mb-4 fade-in-up stagger-1">
                    <div class="creative-card-header">
                        <h3><i class="fas fa-edit"></i> Update Your Submission</h3>
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
                                          {{ $assignment->submission_type === 'text' ? 'required' : '' }}>{{ old('content', $submission->content) }}</textarea>
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
                                    Add More Files
                                </label>
                                <input type="file" 
                                       class="creative-form-input @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror" 
                                       id="files" 
                                       name="files[]" 
                                       multiple
                                       accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip">
                                @error('files')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('files.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Maximum 5 files total, 10MB each. Accepted formats: PDF, DOC, DOCX, TXT, JPG, PNG, ZIP
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
                        <div class="mb-3">
                            <strong>Original Submission:</strong>
                            <div class="mt-1">
                                {{ $submission->submitted_at->format('M j, Y g:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2 fade-in-up stagger-3">
                    <button type="submit" class="creative-btn creative-btn-primary">
                        <i class="fas fa-save me-2"></i>Update Submission
                    </button>
                    <a href="{{ route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
                       class="creative-btn creative-btn-outline">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>

                <div class="alert alert-warning mt-3 fade-in-up stagger-4">
                    <small>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Note:</strong> Updating your submission will reset its status to pending review.
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
                title.textContent = 'New Files to Add:';
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
