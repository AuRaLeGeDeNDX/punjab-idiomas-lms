@extends('layouts.app')

@section('title', $subpage->title . ' - ' . $course->title)

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
            <li class="breadcrumb-item">
                <a href="{{ route('student.courses.modules.subpages.index', [$course, $module]) }}">
                    {{ $module->title }}
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $subpage->title }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="creative-page-header fade-in-up">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1><i class="fas fa-file-alt me-2"></i>{{ $subpage->title }}</h1>
                        @if($subpage->description)
                            <p class="mb-2">{{ $subpage->description }}</p>
                        @endif
                        <div class="small">
                            <span class="creative-badge creative-badge-info">{{ ucfirst(str_replace('_', ' ', $module->type)) }}</span>
                            <span class="ms-2">
                                <i class="fas fa-file-alt"></i> {{ $subpage->contents->count() }} content items
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <!-- Navigation between subpages -->
                        @php
                            $allSubpages = $module->activeSubpages;
                            $currentIndex = $allSubpages->search(function($item) use ($subpage) {
                                return $item->id === $subpage->id;
                            });
                            $prevSubpage = $currentIndex > 0 ? $allSubpages[$currentIndex - 1] : null;
                            $nextSubpage = $currentIndex < $allSubpages->count() - 1 ? $allSubpages[$currentIndex + 1] : null;
                        @endphp
                        
                        <div class="btn-group" role="group">
                            @if($prevSubpage)
                                <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $prevSubpage]) }}" 
                                   class="creative-btn creative-btn-outline">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            @endif
                            @if($nextSubpage)
                                <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $nextSubpage]) }}" 
                                   class="creative-btn creative-btn-primary">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="row">
        <div class="col-lg-9">
            {{-- Unified Rendering Container --}}
            <div id="student-content-container"></div>

            {{-- Fallback for SEO / No-JS (Hidden by default or used if JS fails) --}}
            <noscript>
                @if($subpage->contents->where('is_active', true)->where('visibility', 'student')->count() > 0)
                    @foreach($subpage->contents->where('is_active', true)->where('visibility', 'student') as $content)
                        <div class="mb-4">
                            <h3>{{ $content->title }}</h3>
                            <div class="content-body">
                                {!! $content->getRenderedContent() !!}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">No content available.</div>
                @endif
            </noscript>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Module Navigation -->
            <div class="creative-card mb-4">
                <div class="creative-card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-list"></i> {{ $module->title }}
                    </h3>
                </div>
                <div class="creative-card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($module->activeSubpages as $moduleSubpage)
                            <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $moduleSubpage]) }}" 
                               class="list-group-item list-group-item-action {{ $moduleSubpage->id === $subpage->id ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{{ $moduleSubpage->title }}</span>
                                    @if($moduleSubpage->contents->count() > 0)
                                        <small class="creative-badge creative-badge-primary">{{ $moduleSubpage->contents->count() }}</small>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-chart-line"></i> Progress
                    </h3>
                </div>
                <div class="creative-card-body">
                    @php
                        $totalSubpages = $module->activeSubpages->count();
                        $currentPosition = $currentIndex + 1;
                        $progressPercent = $totalSubpages > 0 ? ($currentPosition / $totalSubpages) * 100 : 0;
                    @endphp
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $progressPercent }}%" 
                             aria-valuenow="{{ $progressPercent }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted">
                        Subpage {{ $currentPosition }} of {{ $totalSubpages }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.content-item {
    transition: box-shadow 0.2s ease;
}
.content-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Unified Layout Engine Styles */
.page-row {
    transition: all 0.2s ease;
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 0; /* Align with editor gap 0 */
}
.page-col {
    transition: background-color 0.2s ease;
    display: flex;
    flex-direction: column;
    align-self: stretch; /* Ensure equal height columns */
    position: relative;
    padding: 0.5rem; /* Match editor p-2 (0.5rem) */
}
.page-block {
    position: relative;
    width: 100%;
    /* Ensure children (iframes) fill the min-height */
    display: flex; 
    flex-direction: column;
}

/* Media Container Styles & Overrides */
.page-block img,
.page-block video,
.page-block iframe,
.page-block object {
    width: 100% !important;
    height: 100% !important;
    min-height: inherit; /* Inherit min-height from block if needed, or rely on flex fill */
    object-fit: cover;
    display: block;
    flex-grow: 1; /* Fill the flex container (.page-block) */
}

/* PDF/Video specific */
.media-container {
    width: 100%;
    height: 100%;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.progress {
    height: 8px;
}
</style>
@endpush

@push('scripts')
{{-- Load Unified Editor Bundle --}}
@vite(['resources/js/editor/index.js'])

<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing Student View Renderer...');
        
        // Wait for Bundle
        const initRenderer = () => {
            if (window.PageBuilder) {
                console.log('PageBuilder found. Rendering content...');
                try {
                    new window.PageBuilder('student-content-container', {
                        initialData: @json($subpage->contents->where('is_active', true)->where('visibility', 'student')->sortBy('order_index')->values()),
                        readOnly: true
                    });
                } catch (e) {
                    console.error('Renderer Initialization Failed:', e);
                }
            } else {
                console.warn('PageBuilder NOT found. Retrying...');
                setTimeout(initRenderer, 500);
            }
        };

        // Start checking
        initRenderer();
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image click to enlarge
    document.querySelectorAll('.image-viewer img').forEach(img => {
        img.addEventListener('click', function() {
            // Create modal for image enlargement
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body p-0">
                            <img src="${this.src}" class="img-fluid w-100" alt="${this.alt}">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            $(modal).modal('show');
            $(modal).on('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        });
    });
});
</script>
@endpush