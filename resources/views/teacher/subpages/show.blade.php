@extends('layouts.app')

@section('title', $subpage->title)

@php
    $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
@endphp

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route($routePrefix . '.courses.index') }}" class="text-decoration-none">
                    <i class="fas fa-book me-1"></i>Courses
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route($routePrefix . '.courses.show', $course) }}" class="text-decoration-none">
                    {{ $course->title }}
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route($routePrefix . '.courses.modules.subpages.index', [$course, $module]) }}" class="text-decoration-none">
                    {{ $module->title }} - Subpages
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $subpage->title }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-2 fw-bold" style="color: #1a1a2e;">
                <i class="fas fa-file-alt me-2" style="color: #6366f1;"></i>
                {{ $subpage->title }}
            </h1>
            @if($subpage->description)
                <p class="text-muted mb-0">{{ $subpage->description }}</p>
            @endif
        </div>
        <div>
            <a href="{{ route($routePrefix . '.courses.modules.subpages.content-builder', [$course, $module, $subpage]) }}" 
               class="btn btn-primary shadow-sm me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 500;">
                <i class="fas fa-edit me-2"></i>Edit Content
            </a>
            <a href="{{ route($routePrefix . '.courses.modules.subpages.edit', [$course, $module, $subpage]) }}" 
               class="btn btn-outline-secondary shadow-sm" style="padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 500;">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
        </div>
    </div>

    <!-- Content Display -->
    {{-- Unified Rendering Container --}}
    <div class="card shadow-sm content-preview-card" style="border: none; border-radius: 12px; overflow: hidden;">
        <div class="card-body p-4" style="background: #ffffff; min-height: 300px;">
            <div id="teacher-content-container"></div>
        </div>
    </div>

    {{-- Fallback for SEO / No-JS (Hidden by default or used if JS fails) --}}
    <noscript>
        @if($subpage->contents->count() > 0)
            @foreach($subpage->contents as $content)
                @if($content->is_active)
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            @include('partials.content-display', ['content' => $content])
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <h5 class="fw-bold mb-2">No content yet</h5>
                    <p class="text-muted mb-4">Start building your page by adding content blocks.</p>
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.content-builder', [$course, $module, $subpage]) }}" 
                       class="btn btn-primary shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-plus me-2"></i>Add Content
                    </a>
                </div>
            </div>
        @endif
    </noscript>

    <!-- Assignments Section -->
    <div class="card shadow-sm mt-4" style="border: none; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center" style="padding: 1.25rem 1.5rem;">
            <h5 class="mb-0 fw-bold" style="color: #1a1a2e;">
                <i class="fas fa-tasks me-2" style="color: #6366f1;"></i>Assignments
            </h5>
            <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.create', [$course, $module, $subpage]) }}" 
               class="btn btn-sm btn-primary shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 0.5rem 1.2rem; border-radius: 8px; font-weight: 500;">
                <i class="fas fa-plus me-2"></i>Create Assignment
            </a>
        </div>
        <div class="card-body" style="padding: 1.5rem;">
            @php
                $assignments = $subpage->assignments()->ordered()->get();
            @endphp

            @if($assignments->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-4" style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clipboard-list fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color: #1a1a2e;">No assignments yet</h5>
                    <p class="text-muted mb-4">Create assignments for students to complete.</p>
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.create', [$course, $module, $subpage]) }}" 
                       class="btn btn-primary shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-plus me-2"></i>Create Assignment
                    </a>
                </div>
            @else
                <div class="list-group list-group-flush" style="margin: -1.5rem;">
                    @foreach($assignments as $assignment)
                        <div class="list-group-item border-0" style="padding: 1.25rem 1.5rem; transition: all 0.2s ease;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-2 fw-bold" style="color: #1a1a2e;">
                                        <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
                                           class="text-decoration-none" style="color: #667eea; transition: color 0.2s ease;">
                                            {{ $assignment->title }}
                                        </a>
                                        @if(!$assignment->is_published)
                                            <span class="badge bg-secondary ms-2" style="font-weight: 500; padding: 0.35rem 0.65rem; border-radius: 6px;">Draft</span>
                                        @endif
                                        @if($assignment->isScheduled())
                                            <span class="badge ms-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-weight: 500; padding: 0.35rem 0.65rem; border-radius: 6px;">
                                                <i class="fas fa-clock me-1"></i>Scheduled
                                            </span>
                                        @endif
                                    </h6>
                                    <div class="mb-2">
                                        <span class="badge bg-light text-dark me-2" style="font-weight: 500; padding: 0.35rem 0.65rem; border-radius: 6px; border: 1px solid #e0e0e0;">
                                            {{ ucfirst($assignment->assignment_type) }}
                                        </span>
                                        @if($assignment->due_date)
                                            <span class="text-muted small me-3">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                Due: {{ $assignment->due_date->format('M d, Y g:i A') }}
                                            </span>
                                            @if($assignment->isOverdue())
                                                <span class="badge bg-danger ms-1" style="font-weight: 500; padding: 0.35rem 0.65rem; border-radius: 6px;">Overdue</span>
                                            @endif
                                        @endif
                                        <span class="text-muted small">
                                            <i class="fas fa-star me-1" style="color: #fbbf24;"></i>{{ $assignment->max_score }} points
                                        </span>
                                    </div>
                                    @if($assignment->description)
                                        <p class="mb-0 text-muted small">{{ Str::limit($assignment->description, 120) }}</p>
                                    @endif
                                </div>
                                <div class="btn-group ms-3" role="group">
                                    <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       style="border-radius: 6px 0 0 6px; padding: 0.4rem 0.8rem;"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.edit', [$course, $module, $subpage, $assignment]) }}" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       style="border-radius: 0 6px 6px 0; padding: 0.4rem 0.8rem;"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 text-end" style="padding: 0 0.5rem;">
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" 
                       class="btn btn-sm btn-outline-primary shadow-sm" style="padding: 0.5rem 1.2rem; border-radius: 8px; font-weight: 500;">
                        View All Assignments <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Unified Layout Engine Styles */
.page-row {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 16px;
    align-items: start;
    position: relative;
    transition: all 0.2s ease;
}
.page-col {
    min-width: 0;
    overflow: hidden;
    transition: background-color 0.2s ease;
    /* Enforce grid column sizing — prevent Bootstrap flex overrides */
    width: auto !important;
    max-width: none !important;
    flex: none !important;
}
.page-block {
    position: relative;
    overflow: hidden;
}

/* Prose Content: proper text flow */
.prose-content {
    word-wrap: break-word;
    overflow-wrap: break-word;
    min-width: 0;
    width: 100%;
}

.prose-content.read-only {
    max-width: 100%;
}

/* Media Container Styles */
.media-container { overflow: hidden; }
.media-container img { max-width: 100%; height: auto; border-radius: 0.25rem; display: block; }
.media-container video { width: 100%; border-radius: 0.25rem; }

/* EditorJS rendered content: ensure proper width */
.ce-block__content,
.ce-toolbar__content {
    max-width: 100% !important;
}

.codex-editor__redactor {
    margin-right: 0 !important;
    margin-left: 0 !important;
    max-width: 100% !important;
    padding-bottom: 0 !important;
}

/* EditorJS Content Styles for Preview */
.editorjs-content {
    width: 100%;
    line-height: 1.7;
}

.editorjs-content p,
.editorjs-content div {
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
}

/* Breadcrumb styling */
.breadcrumb-item + .breadcrumb-item::before { content: "›"; color: #6c757d; }
.breadcrumb-item a { color: #667eea; transition: color 0.2s ease; }
.breadcrumb-item a:hover { color: #764ba2; }

/* Card subtle hover */
.card { transition: all 0.3s ease; }
.card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important; }

/* ========================
   MOBILE RESPONSIVE
   ======================== */
@media (max-width: 768px) {
    /* Container padding */
    .container-fluid.px-4 {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    /* Header: stack title and buttons */
    .d-flex.justify-content-between.align-items-center.mb-4 {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }

    .d-flex.justify-content-between.align-items-center.mb-4 > div:last-child {
        display: flex;
        gap: 0.5rem;
        width: 100%;
    }

    .d-flex.justify-content-between.align-items-center.mb-4 > div:last-child .btn {
        flex: 1;
        text-align: center;
    }

    /* Page title smaller */
    .h2 {
        font-size: 1.25rem !important;
    }

    /* Grid: stack columns on mobile */
    .page-row {
        display: flex !important;
        flex-direction: column !important;
        gap: 12px;
    }

    .page-col {
        width: 100% !important;
        grid-column: unset !important;
    }

    /* Breadcrumb */
    .breadcrumb {
        font-size: 0.8rem;
        flex-wrap: wrap;
    }

    /* Assignment header */
    .card-header.d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }

    .card-header.d-flex .btn {
        width: 100%;
        text-align: center;
    }

    /* Assignment items */
    .list-group-item .d-flex.justify-content-between {
        flex-direction: column;
        gap: 0.5rem;
    }

    .list-group-item .btn-group {
        align-self: flex-start;
    }
}
</style>
@endpush

@push('scripts')
{{-- Load Unified Editor Bundle --}}
@vite(['resources/js/editor/index.js'])

<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing Teacher View Renderer...');
        
        const initRenderer = () => {
            if (window.PageBuilder) {
                console.log('PageBuilder found. Rendering content...');
                try {
                    new window.PageBuilder('teacher-content-container', {
                        initialData: @json($subpage->contents->where('is_active', true)->values()),
                        readOnly: true
                    });
                } catch (e) {
                    console.error('Renderer Initialization Failed:', e);
                }
            } else {
                setTimeout(initRenderer, 500);
            }
        };

        initRenderer();
    });
</script>
@endpush
