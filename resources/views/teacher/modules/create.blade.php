@extends('layouts.app')

@section('title', 'Create Module - ' . $course->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ auth()->user()->hasRole('Admin') ? route('admin.courses.index') : route('teacher.courses.index') }}">Courses</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ auth()->user()->hasRole('Admin') ? route('admin.courses.show', $course) : route('teacher.courses.show', $course) }}">{{ $course->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">Create Module</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-plus-circle me-2"></i>Create New Module</h1>
                        <p class="text-muted mb-0">Add a new module to {{ $course->title }}</p>
                    </div>
                    <a href="{{ auth()->user()->hasRole('Admin') ? route('admin.courses.show', $course) : route('teacher.courses.show', $course) }}" class="creative-btn creative-btn-outline">
                        <i class="fas fa-arrow-left"></i>Back to Course
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <!-- Create Form -->
                    <div class="creative-card fade-in-up stagger-1">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-edit me-2"></i>Module Details</h3>
                        </div>
                        <div class="creative-card-body">
                            <form method="POST" action="{{ route('teacher.modules.store', $course) }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="title" class="form-label">Module Title <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('title') is-invalid @enderror" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title') }}" 
                                           required
                                           placeholder="e.g., Introduction to Programming">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4"
                                              placeholder="Provide a brief description of what this module covers...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Provide a brief description of what this module covers.</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_published" 
                                               name="is_published" 
                                               value="1" 
                                               {{ old('is_published') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_published">
                                            Published
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Make this module visible to students immediately.</small>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ auth()->user()->hasRole('Admin') ? route('admin.courses.show', $course) : route('teacher.courses.show', $course) }}" class="creative-btn creative-btn-outline">Cancel</a>
                                    <button type="submit" class="creative-btn creative-btn-primary">
                                        <i class="fas fa-plus me-2"></i>Create Module
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Tips Card -->
                    <div class="creative-card fade-in-up stagger-2">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-lightbulb me-2"></i>Tips</h3>
                        </div>
                        <div class="creative-card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Clear Titles:</strong> Use descriptive titles that clearly indicate the module content.
                                </li>
                                <li class="mb-3">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Draft First:</strong> Leave unpublished until content is ready.
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong>Add Subpages:</strong> After creating, add subpages with detailed content.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Course Info Card -->
                    <div class="creative-card fade-in-up stagger-3">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-info-circle me-2"></i>Course Info</h3>
                        </div>
                        <div class="creative-card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Course</small>
                                <strong>{{ $course->title }}</strong>
                            </div>
                            @if($course->modules)
                            <div class="mb-0">
                                <small class="text-muted d-block">Current Modules</small>
                                <strong>{{ $course->modules->count() }}</strong>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
