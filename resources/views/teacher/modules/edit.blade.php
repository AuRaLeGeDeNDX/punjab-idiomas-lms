@extends('layouts.app')

@section('title', 'Edit Module - ' . $module->title)

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
                    <li class="breadcrumb-item">
                        <a href="{{ route('teacher.modules.show', [$course, $module]) }}">{{ $module->title }}</a>
                    </li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Module: {{ $module->title }}</h1>
                <a href="{{ route('teacher.modules.show', [$course, $module]) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Module
                </a>
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

            <!-- Edit Form -->
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('teacher.modules.update', [$course, $module]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Module Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $module->title) }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4">{{ old('description', $module->description) }}</textarea>
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
                                       {{ old('is_published', $module->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    Published
                                </label>
                            </div>
                            <small class="form-text text-muted">Make this module visible to students.</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('teacher.modules.show', [$course, $module]) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
