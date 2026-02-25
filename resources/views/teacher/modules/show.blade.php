@extends('layouts.app')

@section('title', $module->title)

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
                    <li class="breadcrumb-item active">{{ $module->title }}</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">{{ $module->title }}</h1>
                    @if($module->description)
                        <p class="text-muted mb-0">{{ $module->description }}</p>
                    @endif
                </div>
                <div class="btn-group">
                    <a href="{{ route('teacher.modules.edit', [$course, $module]) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Module
                    </a>
                    <a href="{{ auth()->user()->hasRole('Admin') ? route('admin.courses.show', $course) : route('teacher.courses.show', $course) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Course
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Module Info Card -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Module Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Type:</strong>
                                    <span class="badge bg-info">{{ ucfirst($module->type) }}</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Order:</strong>
                                    <span class="badge bg-secondary">{{ $module->order_index }}</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Status:</strong>
                                    @if($module->is_published)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-warning">Draft</span>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Active:</strong>
                                    @if($module->is_active)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($module->description)
                                <hr>
                                <div>
                                    <strong>Description:</strong>
                                    <p class="mt-2">{{ $module->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Subpages:</span>
                                    <strong>{{ $module->subpages()->count() }}</strong>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Created:</span>
                                    <strong>{{ $module->created_at->format('M j, Y') }}</strong>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between">
                                    <span>Updated:</span>
                                    <strong>{{ $module->updated_at->format('M j, Y') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subpages Section -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Subpages</h5>
                    <a href="{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-list me-1"></i>Manage Subpages
                    </a>
                </div>
                <div class="card-body">
                    @if($module->subpages()->count() > 0)
                        <div class="list-group">
                            @foreach($module->subpages as $subpage)
                                <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $subpage->title }}</h6>
                                            @if($subpage->description)
                                                <p class="mb-0 text-muted small">{{ Str::limit($subpage->description, 100) }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            @if($subpage->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Subpages Yet</h5>
                            <p class="text-muted">Create subpages to organize your module content.</p>
                            <a href="{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Subpage
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
