@extends('layouts.app')

@section('title', $module->title . ' - ' . $course->title)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.enrolled') }}">My Courses</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
            </li>
            <li class="breadcrumb-item active">{{ $module->title }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="creative-page-header fade-in-up mb-4">
        <h1><i class="fas fa-book-open me-2"></i>{{ $module->title }}</h1>
        @if($module->description)
            <p>{{ $module->description }}</p>
        @endif
        <div class="small">
            <span class="creative-badge creative-badge-info">{{ ucfirst(str_replace('_', ' ', $module->type)) }}</span>
            <span class="ms-2">
                <i class="fas fa-file-alt"></i> {{ $subpages->count() }} subpages
            </span>
        </div>
    </div>

    <!-- Subpages List -->
    <div class="row">
        <div class="col-lg-8">
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3><i class="fas fa-list"></i> Subpages</h3>
                </div>
                <div class="creative-card-body p-0">
                    @forelse($subpages as $index => $subpage)
                        <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                           class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center"
                           style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05) !important;">
                            <div class="d-flex align-items-center">
                                <span class="me-3 text-muted fw-bold" style="width: 28px;">{{ $index + 1 }}.</span>
                                <div>
                                    <h6 class="mb-1">{{ $subpage->title }}</h6>
                                    @if($subpage->description)
                                        <p class="text-muted small mb-0">{{ Str::limit($subpage->description, 100) }}</p>
                                    @endif
                                    <small class="text-muted">
                                        <i class="fas fa-file-alt me-1"></i>{{ $subpage->contents->count() }} content items
                                    </small>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No subpages available</h5>
                            <p class="text-muted">This module doesn't have any subpages yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3><i class="fas fa-info-circle"></i> Module Info</h3>
                </div>
                <div class="creative-card-body">
                    <div class="mb-3">
                        <strong>Course:</strong>
                        <div>{{ $course->title }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Type:</strong>
                        <div><span class="creative-badge creative-badge-info">{{ ucfirst(str_replace('_', ' ', $module->type)) }}</span></div>
                    </div>
                    <div>
                        <strong>Total Subpages:</strong>
                        <div>{{ $subpages->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection

@push('styles')
<style>
/* =============================================
   MOBILE REDESIGN (<768px)
   - Hide sidebar
   - Compact header
   - Convert list items to cards
   ============================================= */
@media screen and (max-width: 767px) {
    /* Hide sidebar */
    .col-lg-4 {
        display: none !important;
    }

    /* Full width main column */
    .col-lg-8 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* Remove container padding */
    .container-fluid {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }

    /* Remove outer card wrapper styles */
    .creative-card {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .creative-card-header {
        display: none !important; /* hide "Subpages" title */
    }
    .creative-card-body {
        padding: 0 !important;
    }

    /* Convert list items to cards */
    .list-group-item {
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
        margin-bottom: 12px !important;
        border: 1px solid rgba(0,0,0,0.05) !important;
        padding: 16px !important;
    }
    
    /* Ensure content is cleanly spaced inside the card */
    .list-group-item .d-flex.align-items-center {
        width: 100%;
    }
    
    /* Make the chevron align right */
    .list-group-item .fa-chevron-right {
        margin-left: auto;
    }
}
</style>
@endpush
