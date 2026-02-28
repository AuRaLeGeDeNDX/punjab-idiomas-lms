@extends('layouts.app')

@section('title', $module->title . ' - ' . $course->title)

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
                        <a href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.modules', $course) }}">Modules</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $module->title }}</li>
                </ol>
            </nav>

            <!-- Module Header -->
            <div class="creative-page-header fade-in-up mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-book me-2"></i>{{ $module->title }}</h1>
                        <p class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>{{ $course->title }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('student.courses.modules', $course) }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left me-2"></i>Back to Modules
                        </a>
                    </div>
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

            <!-- Module Description -->
            @if($module->description)
                <div class="creative-card mb-4">
                    <div class="creative-card-body">
                        <h5 class="mb-3">
                            <i class="fas fa-info-circle me-2"></i>About This Module
                        </h5>
                        <p class="mb-0">{{ $module->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Subpages List -->
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-list me-2"></i>Module Content
                    </h3>
                </div>
                <div class="creative-card-body">
                    @if($module->activeSubpages->count() > 0)
                        <div class="list-group">
                            @foreach($module->activeSubpages as $subpage)
                                <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-file-alt me-2"></i>{{ $subpage->title }}
                                            </h6>
                                            @if($subpage->description)
                                                <p class="mb-0 text-muted small">{{ Str::limit($subpage->description, 150) }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Content Available</h5>
                            <p class="text-muted">This module doesn't have any content yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Module Progress (if available) -->
            @if(isset($enrollment))
                <div class="creative-card mt-4">
                    <div class="creative-card-body">
                        <h6 class="mb-3">
                            <i class="fas fa-chart-line me-2"></i>Your Progress
                        </h6>
                        <div class="progress" style="height: 25px;">
                            @php
                                $totalSubpages = $module->activeSubpages->count();
                                $completedSubpages = 0; // This would come from progress tracking
                                $progressPercent = $totalSubpages > 0 ? ($completedSubpages / $totalSubpages) * 100 : 0;
                            @endphp
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $progressPercent }}%;" 
                                 aria-valuenow="{{ $progressPercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($progressPercent, 0) }}%
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            {{ $completedSubpages }} of {{ $totalSubpages }} lessons completed
                        </small>
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
   - Compact header card
   - Align chevron horizontally
   - Balanced spacing
   ============================================= */
@media screen and (max-width: 767px) {
    /* Hide breadcrumb for more space */
    nav[aria-label="breadcrumb"] {
        display: none !important;
    }

    .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    /* Compact Header Card */
    .creative-page-header {
        padding: 1.25rem !important;
        margin-bottom: 1rem !important;
        border-radius: 16px !important;
    }

    .creative-page-header h1 {
        font-size: 1.25rem !important;
        margin-bottom: 0.25rem !important;
    }

    .creative-page-header p {
        font-size: 0.85rem !important;
    }

    .creative-btn-outline {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.75rem !important;
        border-radius: 8px !important;
    }

    /* Module Description Card */
    .creative-card.mb-4 {
        margin-bottom: 0.75rem !important;
    }
    
    .creative-card-body {
        padding: 1rem !important;
    }

    /* Subpage List Items */
    .creative-card-header {
        padding: 1rem 1.25rem !important;
    }

    .creative-card-header h3 {
        font-size: 1rem !important;
    }

    .list-group-item {
        padding: 0.85rem 1.25rem !important;
        border-left: none !important;
        border-right: none !important;
    }

    /* FORCE HORIZONTAL ALIGNMENT */
    .list-group-item .d-flex {
        flex-direction: row !important;
        align-items: center !important;
        width: 100% !important;
    }

    .list-group-item h6 {
        font-size: 0.95rem !important;
        margin-bottom: 0 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 70vw;
    }

    .list-group-item .text-muted.small {
        display: none !important; /* Hide description in list to save space */
    }

    .fa-chevron-right {
        font-size: 0.8rem !important;
    }
}
</style>
@endpush
