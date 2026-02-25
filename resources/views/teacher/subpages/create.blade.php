@extends('layouts.app')

@section('title', 'Create New Subpage')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0">
                                <li class="breadcrumb-item">
                                    @if(auth()->user()->hasRole('Admin'))
                                        <a href="{{ route('admin.courses.index') }}" class="text-decoration-none">
                                            <i class="fas fa-book me-1"></i>Courses
                                        </a>
                                    @else
                                        <a href="{{ route('teacher.courses.index') }}" class="text-decoration-none">
                                            <i class="fas fa-book me-1"></i>Courses
                                        </a>
                                    @endif
                                </li>
                                <li class="breadcrumb-item">
                                    @if(auth()->user()->hasRole('Admin'))
                                        <a href="{{ route('admin.courses.show', $course) }}" class="text-decoration-none">
                                            {{ $course->title }}
                                        </a>
                                    @else
                                        <a href="{{ route('teacher.courses.show', $course) }}" class="text-decoration-none">
                                            {{ $course->title }}
                                        </a>
                                    @endif
                                </li>
                                <li class="breadcrumb-item">
                                    @if(auth()->user()->hasRole('Admin'))
                                        <a href="{{ route('admin.courses.modules.subpages.index', [$course, $module]) }}" class="text-decoration-none">
                                            {{ $module->title }}
                                        </a>
                                    @else
                                        <a href="{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}" class="text-decoration-none">
                                            {{ $module->title }}
                                        </a>
                                    @endif
                                </li>
                                <li class="breadcrumb-item active">Create Subpage</li>
                            </ol>
                        </nav>
                        <h1><i class="fas fa-plus-circle me-2"></i>Create New Subpage</h1>
                        <p class="mb-0">Add a new subpage to organize content within this module.</p>
                    </div>
                    <div>
                        @if(auth()->user()->hasRole('Admin'))
                            <a href="{{ route('admin.courses.modules.subpages.index', [$course, $module]) }}" 
                               class="creative-btn creative-btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Subpages
                            </a>
                        @else
                            <a href="{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}" 
                               class="creative-btn creative-btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Subpages
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Create Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3 class="mb-0"><i class="fas fa-file-alt me-2"></i>Subpage Details</h3>
                </div>
                <div class="creative-card-body">
                    <form method="POST" action="@if(auth()->user()->hasRole('Admin')){{ route('admin.courses.modules.subpages.store', [$course, $module]) }}@else{{ route('teacher.courses.modules.subpages.store', [$course, $module]) }}@endif">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="creative-form-label">
                                Subpage Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="creative-form-input @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required
                                   placeholder="Enter a descriptive title for this subpage">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>This title will be displayed in the module navigation and page header.
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="creative-form-label">Description</label>
                            <textarea class="creative-form-input @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Provide a brief description of what this subpage covers...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Optional description to help students understand the content of this subpage.
                            </small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>When active, this subpage will be visible to students. Inactive subpages are hidden but not deleted.
                            </small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            @if(auth()->user()->hasRole('Admin'))
                                <a href="{{ route('admin.courses.modules.subpages.index', [$course, $module]) }}" 
                                   class="creative-btn creative-btn-outline">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            @else
                                <a href="{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}" 
                                   class="creative-btn creative-btn-outline">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            @endif
                            <button type="submit" class="creative-btn creative-btn-primary">
                                <i class="fas fa-plus me-2"></i>Create Subpage
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with helpful information -->
        <div class="col-lg-4">
            <div class="creative-card">
                <div class="creative-card-header">
                    <h4 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h4>
                </div>
                <div class="creative-card-body">
                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-heading me-2"></i>Title Guidelines</h6>
                        <p class="small text-muted mb-0">Use clear, descriptive titles that help students understand the content at a glance.</p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-align-left me-2"></i>Description Best Practices</h6>
                        <p class="small text-muted mb-0">Keep descriptions concise but informative. Mention key topics or learning objectives.</p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-eye me-2"></i>Visibility Control</h6>
                        <p class="small text-muted mb-0">Use the Active toggle to control when students can see this subpage. Perfect for scheduling content releases.</p>
                    </div>
                    <hr>
                    <div>
                        <h6 class="text-primary"><i class="fas fa-puzzle-piece me-2"></i>Next Steps</h6>
                        <p class="small text-muted mb-0">After creating the subpage, you'll be able to add content blocks using the Content Builder.</p>
                    </div>
                </div>
            </div>

            <!-- Module Info -->
            <div class="creative-card mt-3">
                <div class="creative-card-header">
                    <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Module Info</h4>
                </div>
                <div class="creative-card-body">
                    <p class="mb-2"><strong>Course:</strong> {{ $course->title }}</p>
                    <p class="mb-0"><strong>Module:</strong> {{ $module->title }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

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
