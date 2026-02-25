@extends('layouts.app')

@section('title', 'Edit Subpage - ' . $subpage->title)

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
                                <li class="breadcrumb-item">
                                    @if(auth()->user()->hasRole('Admin'))
                                        <a href="{{ route('admin.courses.modules.subpages.show', [$course, $module, $subpage]) }}" class="text-decoration-none">
                                            {{ $subpage->title }}
                                        </a>
                                    @else
                                        <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}" class="text-decoration-none">
                                            {{ $subpage->title }}
                                        </a>
                                    @endif
                                </li>
                                <li class="breadcrumb-item active">Edit Settings</li>
                            </ol>
                        </nav>
                        <h1><i class="fas fa-cog me-2"></i>Edit Subpage Settings</h1>
                        <p class="mb-0">Update the title, description, and visibility settings for this subpage.</p>
                    </div>
                    <div>
                        @if(auth()->user()->hasRole('Admin'))
                            <a href="{{ route('admin.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                               class="creative-btn creative-btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Subpage
                            </a>
                        @else
                            <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                               class="creative-btn creative-btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Subpage
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

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Subpage Settings</h3>
                </div>
                <div class="creative-card-body">
                    <form method="POST" action="@if(auth()->user()->hasRole('Admin')){{ route('admin.courses.modules.subpages.update', [$course, $module, $subpage]) }}@else{{ route('teacher.courses.modules.subpages.update', [$course, $module, $subpage]) }}@endif">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="title" class="creative-form-label">
                                Subpage Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="creative-form-input @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $subpage->title) }}" 
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
                                      placeholder="Provide a brief description of what this subpage covers...">{{ old('description', $subpage->description) }}</textarea>
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
                                       {{ old('is_active', $subpage->is_active) ? 'checked' : '' }}>
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
                                <a href="{{ route('admin.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                                   class="creative-btn creative-btn-outline">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            @else
                                <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                                   class="creative-btn creative-btn-outline">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            @endif
                            <button type="submit" class="creative-btn creative-btn-primary">
                                <i class="fas fa-save me-2"></i>Update Subpage
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
                        <h6 class="text-primary"><i class="fas fa-puzzle-piece me-2"></i>Content Management</h6>
                        <p class="small text-muted mb-2">To edit the actual content blocks, use the Content Builder:</p>
                        @if(auth()->user()->hasRole('Admin'))
                            <a href="{{ route('admin.courses.modules.subpages.content-builder', [$course, $module, $subpage]) }}" 
                               class="creative-btn creative-btn-sm creative-btn-primary w-100">
                                <i class="fas fa-edit me-2"></i>Open Content Builder
                            </a>
                        @else
                            <a href="{{ route('teacher.courses.modules.subpages.content-builder', [$course, $module, $subpage]) }}" 
                               class="creative-btn creative-btn-sm creative-btn-primary w-100">
                                <i class="fas fa-edit me-2"></i>Open Content Builder
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="creative-card mt-3">
                <div class="creative-card-header">
                    <h4 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h4>
                </div>
                <div class="creative-card-body">
                    @if(auth()->user()->hasRole('Admin'))
                        <a href="{{ route('admin.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                           class="creative-btn creative-btn-outline w-100 mb-2">
                            <i class="fas fa-eye me-2"></i>Preview Subpage
                        </a>
                        <a href="{{ route('admin.courses.modules.subpages.content-builder', [$course, $module, $subpage]) }}" 
                           class="creative-btn creative-btn-outline w-100 mb-2">
                            <i class="fas fa-puzzle-piece me-2"></i>Edit Content
                        </a>
                        <a href="{{ route('admin.courses.modules.subpages.index', [$course, $module]) }}" 
                           class="creative-btn creative-btn-outline w-100">
                            <i class="fas fa-list me-2"></i>All Subpages
                        </a>
                    @else
                        <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                           class="creative-btn creative-btn-outline w-100 mb-2">
                            <i class="fas fa-eye me-2"></i>Preview Subpage
                        </a>
                        <a href="{{ route('teacher.courses.modules.subpages.content-builder', [$course, $module, $subpage]) }}" 
                           class="creative-btn creative-btn-outline w-100 mb-2">
                            <i class="fas fa-puzzle-piece me-2"></i>Edit Content
                        </a>
                        <a href="{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}" 
                           class="creative-btn creative-btn-outline w-100">
                            <i class="fas fa-list me-2"></i>All Subpages
                        </a>
                    @endif
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
