@extends('layouts.app')

@section('title', $courseWithModules->title . ' - Modules')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.index') }}">Browse Courses</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.enrolled') }}">My Courses</a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.index') }}">Courses</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.show', $courseWithModules) }}">{{ $courseWithModules->title }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Modules</li>
                </ol>
            </nav>

            <!-- Course Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">{{ $courseWithModules->title }} - Modules</h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-user me-2"></i>{{ $courseWithModules->teacher->name }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('student.courses.show', $courseWithModules) }}" class="btn btn-secondary back-btn">
                        <i class="fas fa-arrow-left me-md-2"></i><span class="d-none d-md-inline">Back to Course</span>
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

            <!-- Modules List -->
            @if($courseWithModules->modules->count() > 0)
                <div class="row">
                    @foreach($courseWithModules->modules as $module)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-book me-2"></i>{{ $module->title }}
                                    </h5>
                                    
                                    @if($module->description)
                                        <p class="card-text text-muted">{{ Str::limit($module->description, 100) }}</p>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-file-alt me-1"></i>
                                            {{ $module->subpages->count() }} {{ Str::plural('subpage', $module->subpages->count()) }}
                                        </small>
                                        
                                        <a href="{{ route('student.courses.module', [$courseWithModules, $module]) }}" 
                                           class="btn btn-sm btn-primary">
                                            View Module
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Modules Available</h5>
                        <p class="text-muted">This course doesn't have any published modules yet.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* =============================================
   MOBILE REDESIGN (<768px)
   - Hide breadcrumb
   - Card styling (12px radius, shadow, 12px gap)
   - Compact back button
   ============================================= */
@media screen and (max-width: 767px) {
    /* Hide breadcrumb */
    nav[aria-label="breadcrumb"] {
        display: none !important;
    }

    /* Container padding */
    .container-fluid {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }

    /* Compact header area */
    .d-flex.justify-content-between.align-items-center.mb-4 {
        margin-bottom: 16px !important;
    }
    
    .h3 {
        font-size: 20px !important;
    }

    /* Make back button icon-only and square */
    .btn.back-btn {
        width: 44px;
        height: 44px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px !important;
    }
    .btn.back-btn i {
        margin: 0 !important;
    }

    /* Module Cards */
    .card {
        border-radius: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    
    /* 12px gap */
    .col-md-6.col-lg-4.mb-4 {
        margin-bottom: 12px !important;
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    .row.mb-4 {
        margin-left: -8px !important;
        margin-right: -8px !important;
    }
}
</style>
@endpush
