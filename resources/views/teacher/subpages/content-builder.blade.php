@extends('layouts.app')

@section('title', 'Content Builder - ' . $subpage->title)

@php
    $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
@endphp

@section('content')

<!-- Content Builder Wrapper -->
<div class="content-builder-page-wrapper">
    <!-- Sticky Header Section -->
    <div class="content-builder-sticky-header">
        <div class="container-fluid px-4 pt-2">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 0.85rem;">
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
                    <li class="breadcrumb-item active">{{ $subpage->title }} - Content Builder</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h1 class="h4 mb-0 fw-bold" style="color: #1a1a2e;">
                        <i class="fas fa-puzzle-piece me-2" style="color: #6366f1;"></i>
                        {{ $subpage->title }} - Content Builder
                    </h1>
                </div>
                <div>
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                       class="btn btn-sm btn-outline-primary shadow-sm me-2">
                        <i class="fas fa-eye me-1"></i>Preview
                    </a>
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.edit', [$course, $module, $subpage]) }}" 
                       class="btn btn-sm btn-outline-secondary shadow-sm">
                        <i class="fas fa-cog me-1"></i>Settings
                    </a>
                </div>
            </div>

            <!-- Content Builder Interface (Toolbar) -->
            <div class="card shadow-sm mb-0" style="border: none; border-radius: 12px 12px 0 0; overflow: visible;">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 0.75rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white fw-bold fs-6">
                            <i class="fas fa-layer-group me-2"></i>Content Canvas
                        </h5>
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-light shadow-sm" data-add-content data-section="main_content" style="border-radius: 6px 0 0 6px; font-weight: 500;">
                                <i class="fas fa-plus me-1"></i>Add Content Block
                            </button>
                            <button type="button" class="btn btn-sm btn-light shadow-sm" id="save-all-changes" style="border-radius: 0 6px 6px 0; font-weight: 500;">
                                <i class="fas fa-save me-1"></i>Save All
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light shadow-sm" id="view-trash" style="border-radius: 6px; font-weight: 500;">
                            <i class="fas fa-trash me-1"></i>Trash
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scrollable Content Area -->
    <div class="content-builder-scroll-area">
        <div class="container-fluid px-4 pb-4 h-100">
            <div class="card shadow-sm h-100" style="border: none; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                <div class="card-body p-0 h-100" style="overflow-y: auto;">
                    <!-- Content Blocks Container -->
                    <div id="content-blocks-container" class="content-builder-container p-3" style="min-height: 100%;">
                        <div id="page-builder-holder">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Initializing Page Builder...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Content Block Type Selection Modal -->
<div class="modal fade" id="contentTypeModal" tabindex="-1" aria-labelledby="contentTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentTypeModalLabel">Choose Content Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="content-type-grid">
                    <!-- Basic Content -->
                    <div class="col-12 mb-3">
                        <small class="text-muted text-uppercase fw-bold">Basic</small>
                        <hr class="mt-1 mb-2">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-primary shadow-sm" onclick="safeAddBlock('text')">
                                <i class="fas fa-paragraph me-2"></i>Text
                            </button>
                            <button class="btn btn-outline-primary shadow-sm" onclick="safeAddBlock('image')">
                                <i class="fas fa-image me-2"></i>Image
                            </button>
                            <button class="btn btn-outline-primary shadow-sm" onclick="safeAddBlock('video')">
                                <i class="fas fa-video me-2"></i>Video
                            </button>
                            <button class="btn btn-outline-primary shadow-sm" onclick="safeAddBlock('audio')">
                                <i class="fas fa-volume-up me-2"></i>Audio
                            </button>
                            <button class="btn btn-outline-primary shadow-sm" onclick="safeAddBlock('pdf')">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </button>
                        </div>
                    </div>

                    <!-- Media & Embeds (Phase 5) -->
                    <div class="col-12 mb-3">
                        <small class="text-muted text-uppercase fw-bold">Media</small>
                        <hr class="mt-1 mb-2">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-info shadow-sm" onclick="safeAddBlock('youtube', {url: ''}, {startTime: 0})">
                                <i class="fab fa-youtube me-2"></i>YouTube
                            </button>
                            <!-- Future: Carousel, Social -->
                        </div>
                    </div>

                    <!-- Layout & Utility (Phase 5) -->
                    <div class="col-12">
                        <small class="text-muted text-uppercase fw-bold">Layout</small>
                        <hr class="mt-1 mb-2">
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-secondary shadow-sm" onclick="safeAddBlock('spacer', {}, {height: '50px'})">
                                <i class="fas fa-arrows-alt-v me-2"></i>Spacer
                            </button>
                            <button class="btn btn-outline-secondary shadow-sm" onclick="safeAddBlock('divider', {}, {style: 'solid', width: '100%'})">
                                <i class="fas fa-minus me-2"></i>Divider
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Block Trash Modal -->
<div class="modal fade" id="trashModal" tabindex="-1" aria-labelledby="trashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="trashModalLabel">
                    <i class="fas fa-trash me-2 text-danger"></i>Content Trash
                </h5>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    @php
                        $hasAnyTrashedForButton = false;
                        foreach($trashedContentBySection as $sec) {
                            if($sec['blocks']->isNotEmpty()) { $hasAnyTrashedForButton = true; break; }
                        }
                    @endphp
                    @if($hasAnyTrashedForButton)
                    <button type="button" class="btn btn-danger btn-sm" id="empty-content-trash-btn" onclick="emptyContentTrash()">
                        <i class="fas fa-trash me-1"></i>Delete All
                    </button>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="alert alert-info border-0 rounded-0 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    These blocks were deleted but can still be restored. Restored blocks will return to their original section.
                </div>
                <div id="trash-blocks-container" class="p-4">
                    @forelse($trashedContentBySection as $sectionValue => $section)
                        @if($section['blocks']->isNotEmpty())
                            <div class="mb-4">
                                <h6 class="text-uppercase fw-bold text-muted small mb-3">
                                    <i class="{{ $section['section_info']['icon'] }} me-2"></i>{{ $section['section_info']['label'] }}
                                </h6>
                                <div class="row g-3">
                                    @foreach($section['blocks'] as $block)
                                        <div class="col-md-6 col-lg-4 trashed-block-card" data-id="{{ $block->id }}">
                                            <div class="card h-100 border shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span class="badge bg-secondary mb-2">{{ ucfirst($block->type) }}</span>
                                                        <small class="text-muted">Deleted: {{ $block->deleted_at->diffForHumans() }}</small>
                                                    </div>
                                                    <h6 class="card-title text-truncate fw-bold mb-1">{{ $block->title ?: 'Untitled Block' }}</h6>
                                                    @if($block->description)
                                                        <p class="card-text small text-muted text-truncate mb-2">{{ $block->description }}</p>
                                                    @endif
                                                    <div class="d-flex gap-2 mt-3">
                                                        <button class="btn btn-sm btn-success w-100" onclick="restoreBlock({{ $block->id }})">
                                                            <i class="fas fa-undo me-1"></i>Restore
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="permanentlyDeleteBlock({{ $block->id }}, '{{ addslashes($block->title) }}')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-trash-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Trash is Empty</h5>
                            <p class="text-muted">No content blocks have been deleted yet.</p>
                        </div>
                    @endforelse
                    
                    @php
                        $hasAnyTrashed = false;
                        foreach($trashedContentBySection as $section) {
                            if($section['blocks']->isNotEmpty()) {
                                $hasAnyTrashed = true;
                                break;
                            }
                        }
                    @endphp
                    
                    @if(!$hasAnyTrashed)
                        <div id="empty-trash-message" class="text-center py-5">
                            <i class="fas fa-trash-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Trash is Empty</h5>
                            <p class="text-muted">No content blocks have been deleted yet.</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Content Block Edit Modal -->
<div class="modal fade" id="contentEditModal" tabindex="-1" aria-labelledby="contentEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentEditModalLabel">Edit Content Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="content-edit-form" enctype="multipart/form-data">
                    <!-- Form content will be dynamically generated -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="creative-btn creative-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="creative-btn creative-btn-primary" id="save-content-block">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
<style>
/* Drag and Drop Enhancements */
.sortable-ghost {
    opacity: 0.4;
    background-color: #f8f9fa;
    border: 2px dashed #adb5bd !important;
    transform: scale(0.98);
}

.sortable-drag {
    cursor: grabbing;
    background: #fff;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    opacity: 1 !important;
    z-index: 9999;
    transform: scale(1.02);
}

/* Highlight Drop Zones when dragging */
body.is-dragging-block .page-col {
    background-color: #f0f4f8;
    border: 1px dashed #0d6efd !important;
    min-height: 100px; /* Expand drop zones */
    transition: all 0.2s ease;
}

/* Column Drop Slots — Google Sites Style */
.column-drop-slot {
  position: absolute;
  top: 0;
  bottom: 0;
  width: calc(100% / 12); /* 12 slots per row */
  border: 2px dashed transparent;
  background: transparent;
  transition: all 0.15s ease;
  pointer-events: auto;
  z-index: 999999; /* MAXIMUM - must be topmost for hit detection */
  opacity: 0.01;
  transform: scale(0.98);
}

/* Slot positioning (1-12) */
.column-drop-slot[data-slot="1"] { left: 0%; }
.column-drop-slot[data-slot="2"] { left: calc(100% / 12); }
.column-drop-slot[data-slot="3"] { left: calc(2 * 100% / 12); }
.column-drop-slot[data-slot="4"] { left: calc(3 * 100% / 12); }
.column-drop-slot[data-slot="5"] { left: calc(4 * 100% / 12); }
.column-drop-slot[data-slot="6"] { left: calc(5 * 100% / 12); }
.column-drop-slot[data-slot="7"] { left: calc(6 * 100% / 12); }
.column-drop-slot[data-slot="8"] { left: calc(7 * 100% / 12); }
.column-drop-slot[data-slot="9"] { left: calc(8 * 100% / 12); }
.column-drop-slot[data-slot="10"] { left: calc(9 * 100% / 12); }
.column-drop-slot[data-slot="11"] { left: calc(10 * 100% / 12); }
.column-drop-slot[data-slot="12"] { left: calc(11 * 100% / 12); }

/* Show slots during drag */
/* Allow pointer events on rows and slots during dragging so hit detection works */
body.is-pointer-dragging .page-row,
body.is-pointer-dragging .column-drop-slot {
  pointer-events: auto !important;
}

/* Ensure blocks and their children don't interfere with slot/row detection */
body.is-pointer-dragging .page-col,
body.is-pointer-dragging .page-col * {
  pointer-events: none !important;
}

/* DROP PREVIEW (Ghost Block) */
.drop-preview {
  position: absolute; /* Absolute within the row to overlay without pushing content */
  top: 0;
  left: 0;
  background: rgba(59, 130, 246, 0.2); /* Blue tint */
  border: 2px dashed #3b82f6;
  border-radius: 6px;
  pointer-events: none;
  z-index: 900; /* Visible but below slots/dragged item */
}

.drop-preview.is-invalid {
  background: rgba(239, 68, 68, 0.2); /* Red tint */
  border-color: #ef4444;
}

/* But keep slots hittable */
body.is-pointer-dragging .column-drop-slot {
  pointer-events: auto !important;
}

/* Hover/active state */
.column-drop-slot.active {
    background: rgba(13, 110, 253, 0.2) !important;
    border-color: #0d6efd !important;
    opacity: 1 !important;
    transform: scale(1);
}

body.is-dragging-block .formatted-content {
    min-height: 100px; /* Ensure content area is droppable */
}

body.is-dragging-block .page-col.empty-col {
    background-color: #e9ecef; /* slightly darker to highlight empty space */
}

/* Prevent background scrolling when modal is open */
body.modal-open {
    overflow: hidden !important;
}

/* CRITICAL: Lock the specific scroll area of our builder */
body.modal-open .content-builder-scroll-area,
body.modal-open .content-builder-page-wrapper * {
    overflow: hidden !important;
    pointer-events: none !important; /* NUKE IT: Prevent ANY clicks through to background elements */
}

/* Ensure the modal itself remains interactive */
.modal {
    pointer-events: auto !important;
}

/* Allow modal to handle scroll naturally with smoothness */
/* CSS Overlay Safety Fix - DON'T REMOVE! Prevents overlay from blocking clicks */
.page-block {
    position: relative;
}
.page-block::after {
    pointer-events: none !important;
}

.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 50px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden; /* Standard but we'll override for visible dropdowns */
}

/* Modal Body Scrolling & Dropdown Safety */
.modal-body {
    overflow-y: auto !important;
    max-height: calc(100vh - 200px);
    position: relative;
    padding: 1.5rem;
    scrollbar-width: thin;
}

/* Re-allow dropdowns to pop out */
.modal, .modal-dialog, .modal-content {
    overflow: visible !important;
}

.content-builder-page-wrapper {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 70px); /* Adjust based on global navbar height */
    overflow: hidden;
}

.content-builder-sticky-header {
    flex-shrink: 0;
    z-index: 1000;
    background: #f8fafc; /* Match global bg */
    border-bottom: 1px solid rgba(0,0,0,0.05);
     padding-right: 8px;
}

.content-builder-scroll-area {
    flex-grow: 1;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
    scrollbar-gutter: stable;
    /* Custom Scrollbar */
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

/* FIX: Dropdown click area & interaction safety */
.form-select {
    width: 100%;
    display: block;
    cursor: pointer;
    position: relative;
    z-index: 20; /* Ensure above standard content */
}

/* Ensure no immediate siblings (layout wrappers) block clicks */
.form-select + * {
    pointer-events: none;
}

/* Force interactivity for form elements inside our modal */
#contentEditModal select,
#contentEditModal input,
#contentEditModal textarea,
#contentEditModal label {
    pointer-events: auto !important;
    position: relative;
    z-index: 50;
}

#contentEditModal .mb-3 {
    position: relative; 
    z-index: auto;
}

.content-builder-scroll-area::-webkit-scrollbar {
    width: 8px;
}
.content-builder-scroll-area::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.content-builder-scroll-area::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}

/* Ensure drag ghosts are visible above everything */
.sortable-drag {
    z-index: 9999 !important;
}

/* IMPORTANT: Prevent iframes from stealing mouse events during drag or resize */
body.is-dragging-block iframe,
body.is-dragging-block object,
body.is-resizing iframe,
body.is-resizing object {
    pointer-events: none;
}

.content-builder-container {
    min-height: 400px;
}

/* ========================================
   CONTENT BUILDER MOBILE RESPONSIVE
   ======================================== */
@media (max-width: 768px) {
    /* Page wrapper: fill viewport */
    .content-builder-page-wrapper {
        height: auto;
        min-height: 100vh;
    }

    /* Sticky header: tighter padding */
    .content-builder-sticky-header {
        padding-right: 0;
    }

    .content-builder-sticky-header .container-fluid {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    /* Breadcrumb: smaller on mobile */
    .content-builder-sticky-header .breadcrumb {
        font-size: 0.75rem !important;
        flex-wrap: wrap;
    }

    .content-builder-sticky-header .breadcrumb-item {
        font-size: 0.75rem;
    }

    /* Header: stack title & buttons vertically */
    .content-builder-sticky-header .d-flex.justify-content-between.align-items-center.mb-2 {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }

    /* Page title: smaller on mobile */
    .content-builder-sticky-header .h4 {
        font-size: 1rem !important;
    }

    /* Preview & Settings buttons: full width row */
    .content-builder-sticky-header .d-flex.justify-content-between.align-items-center.mb-2 > div:last-child {
        display: flex;
        gap: 0.5rem;
        width: 100%;
    }

    .content-builder-sticky-header .d-flex.justify-content-between.align-items-center.mb-2 .btn {
        flex: 1;
        font-size: 0.8rem;
    }

    /* Content Canvas toolbar: stack items */
    .card-header .d-flex.justify-content-between.align-items-center {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .card-header .d-flex.justify-content-between.align-items-center h5 {
        font-size: 0.85rem !important;
        flex: 1 1 100%;
    }

    .card-header .btn-group {
        flex: 1 1 auto;
    }

    .card-header .btn-group .btn {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }

    .card-header #view-trash {
        font-size: 0.75rem;
        padding: 0.4rem 0.6rem;
    }

    /* ====== CONTENT AREA: FULL WIDTH ON MOBILE ====== */
    /* Remove all padding from scroll area container */
    .content-builder-scroll-area .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-bottom: 0 !important;
    }

    /* Card: remove border radius, go edge to edge */
    .content-builder-scroll-area .card {
        border-radius: 0 !important;
        border-left: none !important;
        border-right: none !important;
    }

    /* Card header: tighter on mobile */
    .content-builder-scroll-area .card-header {
        border-radius: 0 !important;
        padding: 0.75rem 0.5rem !important;
    }

    /* Card body: minimal padding so canvas fills screen */
    .content-builder-scroll-area .card-body {
        padding: 0 !important;
    }

    /* Builder container: fill width with small padding */
    .content-builder-container {
        min-height: 300px;
        padding: 0.5rem !important;
        margin: 0 !important;
    }

    /* Content blocks: full width, stack vertically */
    .page-row {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px;
    }

    .page-col {
        width: 100% !important;
        flex: none !important;
        grid-column: unset !important;
    }

    /* Block action buttons: compact */
    .content-block-actions {
        gap: 4px;
    }

    .content-block-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Block headers: compact */
    .content-block-header {
        padding: 8px 10px;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    /* EditorJS blocks: full width */
    .ce-block__content,
    .ce-toolbar__content {
        max-width: 100% !important;
        margin: 0 !important;
    }

    .codex-editor__redactor {
        margin: 0 !important;
        padding-bottom: 0 !important;
    }

    /* Modals: full width on mobile */
    .modal-dialog.modal-lg,
    .modal-dialog.modal-xl {
        max-width: 95%;
        margin: 0.5rem auto;
    }
}

/* Editor-only Styles (specific to blade, not in main CSS) */

/* Editor-only Visual Cues */
.page-col.editor-col-active {
    /* border: 1px dashed #dee2e6 !important; REMOVED - Moved to overlay */
    border-radius: 4px;
    position: relative; /* For handle positioning */
    display: flex;
    flex-direction: column;
    align-self: stretch; /* Rule 2: Ensure column stretches to match row height if needed */
}

/* Phase 4: Block Visual Polish */
.page-block {
    position: relative;
    padding: 0 !important; /* Rule 1: Remove padding from resize target */
    box-sizing: border-box;
    overflow: hidden; /* Rule 2: Prevent content bleeding */
    min-height: 50px;
}

/* Rule 3: Content Full Bleed */
.page-block img,
.page-block video,
.page-block iframe,
.page-block object {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
    display: block;
}

/* Phase 4: Overlay Border (Google Sites Style) */
.page-col.editor-col-active .page-block::after {
    content: '';
    position: absolute;
    inset: 0;
    border: 2px solid #3b82f6; /* Blue active border */
    pointer-events: none;
    z-index: 40; /* Below handles (50) but above content */
    display: none; /* Hidden by default */
}

/* Show on Hover or Resizing */
.page-col.editor-col-active:hover .page-block::after,
.page-block.is-resizing::after {
    display: block;
}

.formatted-content {
    flex-grow: 1;
    min-height: 50px; /* Ensure drop zone exists even if empty */
    height: auto !important; /* Rule 2: Allow auto-growth */
    overflow: visible !important; /* Rule 2: No clipping */
}

/* Resize Handles (Dot Style) */
.resize-handle {
    position: absolute;
    z-index: 50;
    background-color: transparent;
    opacity: 0.2; /* Default: Minimal visibility */
    transition: opacity 0.2s;
    touch-action: none;
    display: none; /* Hidden by default */
}

.page-col.editor-col-active:hover .resize-handle,
.page-block.is-resizing .resize-handle {
    display: block;
}

/* ... existing handle positioning ... */

/* Hover: Better visibility */
.page-col:hover .resize-handle {
    opacity: 0.6;
}

/* Active: Full visibility */
.page-block.is-resizing .resize-handle {
    opacity: 1 !important;
}

.resize-handle.left,
.resize-handle.right {
    width: 12px;
    top: 0;
    bottom: 0;
    cursor: col-resize;
}

.resize-handle.left { left: -6px; }
.resize-handle.right { right: -6px; }

.resize-handle.top,
.resize-handle.bottom {
    height: 12px;
    left: 0;
    right: 0;
    cursor: row-resize;
}

.resize-handle.top { top: -6px; }
.resize-handle.bottom { bottom: -6px; }

/* Corner Handles */
.resize-handle.nw, .resize-handle.ne, .resize-handle.sw, .resize-handle.se {
    width: 12px;
    height: 12px;
    z-index: 51; /* Above sides */
}

.resize-handle.nw { top: -6px; left: -6px; cursor: nwse-resize; }
.resize-handle.ne { top: -6px; right: -6px; cursor: nesw-resize; }
.resize-handle.sw { bottom: -6px; left: -6px; cursor: nesw-resize; }
.resize-handle.se { bottom: -6px; right: -6px; cursor: nwse-resize; }

/* The visible dot */
.resize-handle::after {
    content: '';
    position: absolute;
    width: 10px; /* Slightly smaller dot for elegance */
    height: 10px;
    background-color: #fff;
    border: 1px solid #0d6efd;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    transition: transform 0.2s ease, background-color 0.2s;
}

.resize-handle:hover::after {
    transform: translate(-50%, -50%) scale(1.3);
    background-color: #f8f9fa;
    border-color: #0b5ed7;
}

/* Media Resize Handles */
.media-resize-handle {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #fff;
    border: 1px solid #0d6efd;
    z-index: 20;
    opacity: 0; 
    transition: opacity 0.2s;
}

.media-container:hover .media-resize-handle {
    opacity: 1;
}

.handle-nw { top: -5px; left: -5px; cursor: nw-resize; }
.handle-n  { top: -5px; left: 50%; transform: translateX(-50%); cursor: n-resize; }
.handle-ne { top: -5px; right: -5px; cursor: ne-resize; }
.handle-e  { top: 50%; right: -5px; transform: translateY(-50%); cursor: e-resize; }
.handle-se { bottom: -5px; right: -5px; cursor: se-resize; }
.handle-s  { bottom: -5px; left: 50%; transform: translateX(-50%); cursor: s-resize; }
.handle-sw { bottom: -5px; left: -5px; cursor: sw-resize; }
.handle-w  { top: 50%; left: -5px; transform: translateY(-50%); cursor: w-resize; }

.page-col.empty-col {
    min-height: 80px;
    background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #ffffff 10px, #ffffff 20px);
    border: 1px dashed #dee2e6;
}

.page-block {
    position: relative;
}

.page-block.editable-block:hover {
    /* outline: 2px solid #0d6efd; REMOVED - Replaced by overlay */
    /* outline-offset: 2px; */
    z-index: 5;
}

/* Resize Hint (Google Sites Style) */
.resize-hint {
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.75rem;
    white-space: nowrap;
    z-index: 1000;
    pointer-events: none;
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translate(-50%, 5px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}

/* Drop Zone Highlight */
.sortable-ghost {
    background: #e9ecef;
    opacity: 0.5;
    border: 1px dashed #6c757d;
    z-index: 1000;
}

.sortable-drag {
    z-index: 1001;
    cursor: grabbing;
}

body.is-dragging-block .resize-handle,
body.is-dragging-block .media-resize-handle {
    pointer-events: none !important;
    opacity: 0 !important;
}

.content-block.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

/* EditorJS Full Width Overrides */
.ce-block__content,
.ce-toolbar__content {
    max-width: 100% !important; /* Force full width */
    margin: 0 auto;
}

.prose-content {
    width: 100%;
    /* Ensure text wraps nicely */
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.content-block-header {
    padding: 12px 16px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: between;
    align-items: center;
}

.content-block-body {
    padding: 16px;
}

.content-block-actions {
    display: flex;
    gap: 8px;
}

.content-block-drag-handle {
    cursor: grab;
    color: #6c757d;
    margin-right: 8px;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.content-block-drag-handle:hover {
    color: #007bff;
    background: rgba(0, 123, 255, 0.1);
}

.content-block-drag-handle:active {
    cursor: grabbing;
}

.content-type-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
}

.content-type-card:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.content-type-card.selected {
    border-color: #007bff;
    background: #e7f3ff;
}

.content-type-icon {
    font-size: 2rem;
    color: #007bff;
    margin-bottom: 12px;
}

.visibility-badge {
    font-size: 0.75rem;
}

/* Section-specific styles */
.content-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    background: #fafbfc;
}

.section-header h5 {
    color: #495057;
}

.section-blocks {
    min-height: 100px;
    border-radius: 6px;
    background: #fff;
    padding: 15px;
}

.empty-section {
    background: #f8f9fa;
    border-color: #dee2e6 !important;
}

.empty-section:hover {
    background: #e9ecef;
    border-color: #007bff !important;
}

/* Enhanced file input styles with improved drag-and-drop */
.form-control[type="file"] {
    transition: all 0.3s ease;
    border: 2px dashed #dee2e6;
    padding: 30px 20px;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    cursor: pointer;
    border-radius: 8px;
    position: relative;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.form-control[type="file"]:hover {
    border-color: #007bff;
    background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
}

.form-control[type="file"].drag-over {
    border-color: #28a745;
    background: linear-gradient(135deg, #d4edda 0%, #f0fff4 100%);
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
    transform: scale(1.02);
}

.form-control[type="file"].drag-over::before {
    content: "Drop files here to upload";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: bold;
    color: #28a745;
    font-size: 1.1em;
    pointer-events: none;
}

/* Enhanced drag-and-drop zone */
.file-drop-zone {
    position: relative;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.file-drop-zone:hover {
    border-color: #007bff;
    background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
}

.file-drop-zone.drag-over {
    border-color: #28a745;
    background: linear-gradient(135deg, #d4edda 0%, #f0fff4 100%);
    box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
    transform: scale(1.02);
}

.file-drop-zone-content {
    pointer-events: none;
}

.file-drop-zone-icon {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.file-drop-zone:hover .file-drop-zone-icon {
    color: #007bff;
    transform: scale(1.1);
}

.file-drop-zone.drag-over .file-drop-zone-icon {
    color: #28a745;
    transform: scale(1.2);
}

.file-drop-zone-text {
    font-size: 1.1rem;
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

.file-drop-zone-subtext {
    font-size: 0.9rem;
    color: #6c757d;
}

.file-drop-zone.drag-over .file-drop-zone-text {
    color: #28a745;
    font-weight: bold;
}

/* Hidden file input */
.file-input-hidden {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

/* Enhanced file preview and upload progress styles */
.file-preview-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.file-preview-container:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
}

.file-selection-feedback {
    animation: slideInUp 0.4s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.file-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.file-details {
    min-width: 0; /* Allow text truncation */
}

.file-size-indicator .progress {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

.upload-status-indicator {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-top: 8px;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.upload-progress .progress {
    height: 8px;
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    overflow: hidden;
}

.upload-progress .progress-bar {
    transition: width 0.3s ease;
}

.upload-status-message {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    font-weight: 500;
}

.upload-status-message i {
    font-size: 1.1rem;
}

.status-preparing i,
.status-processing i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.status-success {
    color: #28a745;
    animation: successPulse 0.6s ease-out;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.status-error {
    color: #dc3545;
}

.retry-upload-btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.retry-upload-btn:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    transform: translateY(-1px);
}

.file-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.file-preview-image img {
    transition: transform 0.2s ease;
}

.file-preview-image img:hover {
    transform: scale(1.02);
}

#file-preview-info {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced alert styles */
.alert-notification {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
}

.alert-notification .progress-bar {
    transition: width 0.3s ease;
}

.alert-notification ul {
    margin-bottom: 0.5rem;
}

.alert-notification ul li {
    margin-bottom: 0.25rem;
}

.alert-notification .border-top {
    border-color: rgba(255, 255, 255, 0.2) !important;
}

/* Breadcrumb Styles */
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

@push('body-class', 'content-builder-page')

@push('scripts')
<script>console.log('Push scripts started');</script>
{{-- Dependency loading moved to end of file --}}

<script>
console.log('=== CONTENT BUILDER INITIALIZATION ===');
console.log('Script loaded at:', new Date().toISOString());
console.log('Document ready state:', document.readyState);

// Debug: Check if required variables are available
console.log('Server variables check:');
console.log('- contentTypes:', @json($contentTypes ?? []));
console.log('- sections:', @json($sections ?? []));
console.log('- visibilityOptions:', @json($visibilityOptions ?? []));
console.log('- course:', @json($course->id ?? 'missing'));
console.log('- module:', @json($module->id ?? 'missing'));
console.log('- subpage:', @json($subpage->id ?? 'missing'));

// Core Modal & Template Management Functions
// Event delegation moved to central block at end of file

// STEP 2: MODAL MANAGEMENT FUNCTIONS
function openContentTypeModal() {
    try {
        console.time('OpenContentTypeModal');
        console.log('Opening content type modal...');
        console.log('Current content section:', window.currentContentSection);
        
        const modal = document.getElementById('contentTypeModal');
        if (!modal) {
            console.error('Content type modal not found!');
            console.log('Available modals:', document.querySelectorAll('.modal'));
            alert('Modal not found. Please refresh the page.');
            return;
        }
        
        console.log('✓ Modal element found:', modal);
        
        // Populate content types if not already done
        populateContentTypes();
        
        // Check Bootstrap availability
        console.log('Bootstrap check:');
        console.log('  window.bootstrap:', typeof window.bootstrap);
        console.log('  window.bootstrap.Modal:', typeof window.bootstrap?.Modal);
        
        // Open modal using Bootstrap
        if (window.bootstrap && window.bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            console.log('✓ Bootstrap Modal instance retrieved/created:', bsModal);
            bsModal.show();
            console.log('✓ Modal show() called');
            console.timeEnd('OpenContentTypeModal');
        } else {
            console.error('Bootstrap Modal not available');
            console.log('Bootstrap object:', window.bootstrap);
            console.log('Available Bootstrap methods:', window.bootstrap ? Object.keys(window.bootstrap) : 'Bootstrap not loaded');
            alert('Bootstrap not loaded. Please refresh the page.');
        }
    } catch (error) {
        console.error('Error opening modal:', error);
        console.error('Error stack:', error.stack);
        alert('Failed to open modal: ' + error.message);
    }
}

// STEP 3: POPULATE CONTENT TYPES IN MODAL
function populateContentTypes() {
    try {
        const grid = document.getElementById('content-type-grid');
        if (!grid) {
            console.error('Content type grid not found');
            return;
        }
        
        // Check if already populated
        if (grid.children.length > 0) {
            console.log('Content types already populated');
            return;
        }
        
        // Use server-side content types data
        const contentTypes = @json($contentTypes ?? []);
        console.log('Content types from server:', contentTypes);
        
        if (!contentTypes || Object.keys(contentTypes).length === 0) {
            console.error('No content types available from server');
            grid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="text-muted">No Content Types Available</h5>
                    <p class="text-muted">Please contact your administrator to configure content types.</p>
                </div>
            `;
            return;
        }
        
        // Generate content type cards
        let html = '';
        Object.entries(contentTypes).forEach(([type, config]) => {
            html += `
                <div class="col-md-4 mb-3">
                    <div class="content-type-card" data-type="${type}">
                        <div class="content-type-icon">
                            <i class="${config.icon}"></i>
                        </div>
                        <h6>${config.label}</h6>
                        <p class="text-muted small mb-0">${config.description}</p>
                    </div>
                </div>
            `;
        });
        
        grid.innerHTML = html;
        console.log('✓ Content type cards populated');
        
        // Add click handlers to content type cards
        grid.addEventListener('click', function(e) {
            try {
                const card = e.target.closest('.content-type-card');
                if (card) {
                    const type = card.dataset.type;
                    console.log('✓ Content type selected:', type);
                    
                    // Close the content type modal
                    const modal = document.getElementById('contentTypeModal');
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    
                    if (bsModal) {
                        // CRITICAL FIX: Wait for the first modal to fully close (including backdrop)
                        // before opening the second modal. Bootstrap 5 doesn't support overlapping modals well.
                        modal.addEventListener('hidden.bs.modal', function openNext() {
                            console.log('✓ Content type modal hidden, opening edit modal');
                            console.time('OpenContentEditModal');
                            openContentEditModal(type, window.currentContentSection);
                            modal.removeEventListener('hidden.bs.modal', openNext);
                        }, { once: true });
                        
                        bsModal.hide();
                    } else {
                        // Fallback if no instance (shouldn't happen)
                        openContentEditModal(type, window.currentContentSection);
                    }
                }
            } catch (error) {
                console.error('Error in content type selection:', error);
                alert('Error selecting content type: ' + error.message);
            }
        });
    } catch (error) {
        console.error('Error populating content types:', error);
        alert('Failed to load content types: ' + error.message);
    }
}

// STEP 4: CONTENT EDIT MODAL
let currentEditorInstance = null; // Track the current EditorJS instance

function openContentEditModal(type, section) {
    try {
        console.log('Opening content edit modal for type:', type, 'section:', section);
        console.time('OpenContentEditModal');
        
        // Destroy any existing editor instance
        if (currentEditorInstance) {
            console.log('Destroying previous editor instance');
            currentEditorInstance.destroy();
            currentEditorInstance = null;
        }
        
        // Get content type configuration
        const contentTypes = @json($contentTypes ?? []);
        const typeConfig = contentTypes[type];
        
        if (!typeConfig) {
            console.error('Content type configuration not found for:', type);
            alert('Content type configuration not found. Please refresh the page.');
            return;
        }
        
        // Generate form content FIRST (Synchronously, like Edit flow)
        console.time('FormGeneration');
        const form = document.getElementById('content-edit-form');
        if (form) {
            const formContent = generateContentForm(type, typeConfig, section);
            form.innerHTML = formContent;
            
            // Explicitly reset edit state
            form.dataset.isEdit = 'false';
            delete form.dataset.editId;
            delete form.dataset.contentId;
            
            console.log('✓ Form content inserted synchronously');
        }
        console.timeEnd('FormGeneration');

        // Open the content edit modal
        const modal = document.getElementById('contentEditModal');
        if (modal && window.bootstrap && window.bootstrap.Modal) {
            const bsModal = window.bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
            console.log('✓ Content edit modal show() called');
            
            // Initialize EditorJS for text content
            if (type === 'text') {
                modal.addEventListener('shown.bs.modal', function initEditor() {
                    console.log('Modal shown, initializing EditorJS...');
                    initializeEditorJS();
                    modal.removeEventListener('shown.bs.modal', initEditor);
                }, { once: true });
            }
            
            console.timeEnd('OpenContentEditModal');
        } else {
            console.error('Modal element or Bootstrap not found');
        }
    } catch (error) {
        console.error('Error opening content edit modal:', error);
        alert('Failed to open content editor: ' + error.message);
    }
}

// STEP 5: GENERATE CONTENT FORM
function generateContentForm(type, typeConfig, section) {
    const sections = @json($sections ?? []);
    const visibilityOptions = @json($visibilityOptions ?? []);
    
    let formHtml = `
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="block-title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="block-title" name="title" 
                           placeholder="Enter block title" required>
                </div>
                
                <div class="mb-3">
                    <label for="block-description" class="form-label">Description</label>
                    <textarea class="form-control" id="block-description" name="description" rows="2" 
                              placeholder="Optional description"></textarea>
                </div>
    `;
    
    // Type-specific content fields
    switch (type) {
        case 'youtube':
            formHtml += `
                <div class="mb-3">
                    <label for="block-content" class="form-label">YouTube Video URL</label>
                    <input type="url" class="form-control" id="block-content" name="content" 
                           placeholder="https://www.youtube.com/watch?v=..." required>
                    <div class="form-text">Paste the full YouTube video URL (e.g. https://www.youtube.com/watch?v=dQw4w9WgXcQ).</div>
                </div>
            `;
            break;

        case 'text':
            formHtml += `
                <div class="mb-3">
                    <label for="block-content" class="form-label">Content</label>
                    <div id="editorjs-holder" class="border rounded" style="min-height: 300px;"></div>
                    <input type="hidden" id="block-content" name="content" required>
                    <div class="form-text">Use the rich text editor above to format your content.</div>
                </div>
            `;
            break;
            
        case 'image':
        case 'pdf':
        case 'audio':
        case 'video':
            const allowedExtensions = typeConfig.allowed_extensions || [];
            const maxSize = typeConfig.max_file_size || 10485760; // 10MB default
            
            formHtml += `
                <div class="mb-3">
                    <label for="block-file" class="form-label">File Upload</label>
                    
                    <!-- Enhanced drag-and-drop file zone -->
                    <div class="file-drop-zone" onclick="document.getElementById('block-file').click()">
                        <input type="file" class="file-input-hidden" id="block-file" name="file" 
                               onclick="event.stopPropagation()"
                               accept="${allowedExtensions.length > 0 ? allowedExtensions.map(ext => '.' + ext).join(',') : '*/*'}">
                        
                        <div class="file-drop-zone-content">
                            <div class="file-drop-zone-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-drop-zone-text">
                                Click to select a file or drag and drop
                            </div>
                            <div class="file-drop-zone-subtext">
                                Allowed: ${allowedExtensions.length > 0 ? allowedExtensions.map(ext => `.${ext}`).join(', ') : 'All files'} 
                                (Max: ${formatFileSize(maxSize)})
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (typeConfig.supports_external_url) {
                formHtml += `
                    <div class="mb-3">
                        <label for="block-external-url" class="form-label">Or External URL</label>
                        <input type="url" class="form-control" id="block-external-url" name="external_url" 
                               placeholder="https://example.com/file.jpg">
                        <div class="form-text">You can provide a file OR an external URL.</div>
                    </div>
                `;
            }
            
            if (type === 'image') {
                formHtml += `
                    <div class="mb-3">
                        <label for="block-alt-text" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="block-alt-text" name="alt_text" 
                               placeholder="Describe the image for accessibility">
                    </div>
                `;
            }
            break;
    }
    
    formHtml += `
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="block-section" class="form-label">Section</label>
                    <select class="form-select" id="block-section" name="section" required>
    `;
    
    // Add section options
    sections.forEach(s => {
        const selected = s.value === section ? 'selected' : '';
        formHtml += `<option value="${s.value}" ${selected}>${s.label}</option>`;
    });
    
    formHtml += `
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="block-visibility" class="form-label">Visibility</label>
                    <select class="form-select" id="block-visibility" name="visibility" required>
    `;
    
    // Add visibility options
    visibilityOptions.forEach(v => {
        const selected = v.value === 'student' ? 'selected' : '';
        formHtml += `<option value="${v.value}" ${selected}>${v.label}</option>`;
    });
    
    formHtml += `
                    </select>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="block-is-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="block-is-active">
                            Published
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="alert alert-info">
                        <small>
                            <strong>Note:</strong> This is a simplified form. 
                            The full implementation will include rich text editing, 
                            drag & drop file upload, and real-time preview.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="type" value="${type}">
    `;
    
    return formHtml;
}

// STEP 6: UTILITY FUNCTIONS
function formatFileSize(bytes) {
    const sizes = ['B', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 B';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
}

// Initialize EditorJS for text content editing
async function initializeEditorJS() {
    try {
        console.time('InitializeEditorJS');
        console.log('Initializing EditorJS...');
        
        // Wait for EditorManager to be available (it's loaded via Vite module)
        let attempts = 0;
        while (typeof window.EditorManager === 'undefined' && attempts < 50) {
            console.log('Waiting for EditorManager... attempt', attempts + 1);
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }
        
        // Check if EditorManager is available
        if (typeof window.EditorManager === 'undefined') {
            console.error('EditorManager not found after waiting! Make sure editor/index.js is loaded.');
            alert('Rich text editor failed to load. Please refresh the page.');
            return;
        }
        
        // Check if holder exists
        const holder = document.getElementById('editorjs-holder');
        if (!holder) {
            console.error('EditorJS holder not found!');
            return;
        }
        
        console.log('✓ EditorJS holder found');
        console.log('✓ EditorManager available');
        
        // Create new editor instance
        currentEditorInstance = new window.EditorManager('editorjs-holder', {
            placeholder: 'Start writing your content here...',
            minHeight: 300
        });
        
        console.log('✓ EditorManager instance created');
        
        // Initialize the editor
        await currentEditorInstance.init();
        
        console.log('✓ EditorJS initialized successfully');
        console.log('✓ Editor ready:', currentEditorInstance.isReady());
        console.timeEnd('InitializeEditorJS');
        
    } catch (error) {
        console.error('Failed to initialize EditorJS:', error);
        console.error('Error stack:', error.stack);
        alert('Failed to initialize rich text editor: ' + error.message);
    }
}

// Enhanced client-side file validation using the comprehensive FileUploadValidator class
// The FileUploadValidator class is loaded from FileUploadValidator.js and provides:
// - Comprehensive file size validation with detailed error messages
// - File extension validation against whitelist
// - MIME type validation to prevent file spoofing
// - Security checks for dangerous file types
// - Empty file detection
// - Multiple file validation support
// - User-friendly error messages and recommendations

// Enhanced file input handling with improved drag-and-drop functionality
function setupFileInputHandling() {
    // Add event listeners to all file inputs
    document.addEventListener('change', function(e) {
        if (e.target && e.target.type === 'file' && e.target.id === 'block-file') {
            console.log('File input change event triggered:', e.target.files.length, 'files');
            handleFileSelection(e.target);
        }
    });
    
    // Prevent file input from triggering parent click events
    document.addEventListener('click', function(e) {
        if (e.target && e.target.type === 'file' && e.target.id === 'block-file') {
            console.log('File input clicked directly - stopping propagation');
            e.stopPropagation();
        }
    });
    
    // Enhanced drag and drop support with better visual feedback
    let dragCounter = 0; // Track drag enter/leave events
    
    document.addEventListener('dragenter', function(e) {
        const fileInput = document.getElementById('block-file');
        const dropZone = e.target.closest('.form-control, .file-drop-zone');
        
        if (fileInput && dropZone) {
            e.preventDefault();
            dragCounter++;
            dropZone.classList.add('drag-over');
            
            // Add visual feedback
            if (!dropZone.querySelector('.drag-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'drag-overlay';
                overlay.innerHTML = `
                    <div class="drag-overlay-content">
                        <i class="fas fa-cloud-upload-alt fa-3x text-success mb-2"></i>
                        <div class="h5 text-success">Drop your file here</div>
                        <div class="text-muted">Release to upload</div>
                    </div>
                `;
                overlay.style.cssText = `
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(40, 167, 69, 0.1);
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10;
                    pointer-events: none;
                `;
                dropZone.style.position = 'relative';
                dropZone.appendChild(overlay);
            }
        }
    });
    
    document.addEventListener('dragover', function(e) {
        const fileInput = document.getElementById('block-file');
        const dropZone = e.target.closest('.form-control, .file-drop-zone');
        
        if (fileInput && dropZone) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        }
    });
    
    document.addEventListener('dragleave', function(e) {
        const fileInput = document.getElementById('block-file');
        const dropZone = e.target.closest('.form-control, .file-drop-zone');
        
        if (fileInput && dropZone) {
            dragCounter--;
            
            if (dragCounter === 0) {
                dropZone.classList.remove('drag-over');
                const overlay = dropZone.querySelector('.drag-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }
        }
    });
    
    document.addEventListener('drop', function(e) {
        const fileInput = document.getElementById('block-file');
        const dropZone = e.target.closest('.form-control, .file-drop-zone');
        
        if (fileInput && dropZone) {
            e.preventDefault();
            dragCounter = 0;
            dropZone.classList.remove('drag-over');
            
            // Remove drag overlay
            const overlay = dropZone.querySelector('.drag-overlay');
            if (overlay) {
                overlay.remove();
            }
            
            if (e.dataTransfer.files.length > 0) {
                // Handle multiple files - take the first one
                const files = Array.from(e.dataTransfer.files);
                
                if (files.length > 1) {
                    showNotification('info', `Multiple files detected. Using the first file: ${files[0].name}`);
                }
                
                // Create a new FileList with just the first file
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                fileInput.files = dt.files;
                
                handleFileSelection(fileInput);
                
                // Show success feedback
                showDropSuccessFeedback(dropZone, files[0]);
            }
        }
    });
    
    // Prevent default drag behaviors on the document
    document.addEventListener('dragover', function(e) {
        e.preventDefault();
    });
    
    document.addEventListener('drop', function(e) {
        e.preventDefault();
    });
}

// Show success feedback when file is dropped
function showDropSuccessFeedback(dropZone, file) {
    const feedback = document.createElement('div');
    feedback.className = 'drop-success-feedback';
    feedback.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            File "${file.name}" selected successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert feedback after the drop zone
    dropZone.parentNode.insertBefore(feedback, dropZone.nextSibling);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (feedback.parentNode) {
            feedback.remove();
        }
    }, 3000);
}

// Enhanced notification system
function showNotification(type, message, duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'info' ? 'info' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 1050;
        min-width: 300px;
        max-width: 500px;
    `;
    
    const icon = type === 'info' ? 'fa-info-circle' : 
                 type === 'success' ? 'fa-check-circle' : 
                 type === 'warning' ? 'fa-exclamation-triangle' : 
                 'fa-exclamation-circle';
    
    notification.innerHTML = `
        <i class="fas ${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after specified duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
}

function handleFileSelection(fileInput) {
    console.log('=== handleFileSelection called ===');
    console.log('File input:', fileInput);
    console.log('Files count:', fileInput.files.length);
    
    const file = fileInput.files[0];
    if (!file) {
        console.log('No file selected, clearing preview');
        clearFilePreview();
        return;
    }
    
    console.log('File selected:', file.name, file.size, 'bytes', file.type);
    
    // Get content type from form
    const typeInput = document.querySelector('input[name="type"]');
    const contentType = typeInput ? typeInput.value : 'file';
    
    console.log('Content type:', contentType);
    
    // Get content type configuration
    const contentTypes = @json($contentTypes ?? []);
    const config = contentTypes[contentType] || {};
    
    console.log('Content type config:', config);
    
    // Check if FileUploadValidator is available
    console.log('FileUploadValidator available:', typeof FileUploadValidator);
    
    if (typeof FileUploadValidator === 'undefined') {
        console.error('FileUploadValidator is not loaded! Showing preview without validation.');
        // Show file preview without validation as fallback
        const fileInfo = {
            name: file.name,
            size: file.size,
            sizeFormatted: formatFileSize(file.size),
            type: file.type || 'Unknown',
            extension: getFileExtension(file.name),
            lastModified: file.lastModified,
            lastModifiedDate: new Date(file.lastModified).toLocaleString(),
            sizePercentage: Math.round((file.size / (config.max_file_size || 10485760)) * 100)
        };
        
        showFilePreview(file, config, fileInfo);
        showNotification('success', `File "${file.name}" selected successfully!`, 3000);
        console.log('=== handleFileSelection completed (fallback mode) ===');
        return true;
    }
    
    // Create validator instance using the comprehensive FileUploadValidator class
    let validator, result;
    
    try {
        validator = FileUploadValidator.forContentType(contentType, config);
        console.log('Validator created:', validator);
        
        result = validator.validateFile(file);
        console.log('Validation result:', result);
    } catch (error) {
        console.error('Error during validation:', error);
        // Show file preview without validation as fallback
        const fileInfo = {
            name: file.name,
            size: file.size,
            sizeFormatted: formatFileSize(file.size),
            type: file.type || 'Unknown',
            extension: getFileExtension(file.name),
            lastModified: file.lastModified,
            lastModifiedDate: new Date(file.lastModified).toLocaleString(),
            sizePercentage: Math.round((file.size / (config.max_file_size || 10485760)) * 100)
        };
        
        showFilePreview(file, config, fileInfo);
        showNotification('warning', `File "${file.name}" selected (validation error: ${error.message})`, 5000);
        console.log('=== handleFileSelection completed (error fallback) ===');
        return true;
    }
    
    if (!result.valid) {
        console.log('File validation failed:', result.errors);
        // Show enhanced client-side errors immediately with file information
        const summary = validator.getValidationSummary(result);
        
        const errorData = {
            message: 'File validation failed',
            errors: {
                file: result.errors
            },
            warnings: result.warnings,
            upload_guidance: {
                supported_types: {
                    extensions: config.allowed_extensions || [],
                    formatted: config.allowed_extensions ? 
                        config.allowed_extensions.map(ext => `.${ext}`).join(', ') : 
                        'No restrictions'
                },
                size_limits: {
                    max_size_bytes: config.max_file_size || (10 * 1024 * 1024),
                    max_size_formatted: validator.formatFileSize(config.max_file_size || (10 * 1024 * 1024))
                },
                recommendations: summary.recommendations
            },
            correlation_id: 'client-validation-' + Date.now(),
            file_info: result.fileInfo
        };
        
        displayEnhancedErrorMessages(errorData, fileInput);
        
        // Automatically clear invalid files with user feedback
        clearInvalidFile(fileInput, file.name, result.errors);
        return false;
    }
    
    // Show warnings if any
    if (result.warnings && result.warnings.length > 0) {
        showValidationWarnings(result.warnings);
    }
    
    // Show file preview/info with enhanced details
    showFilePreview(file, config, result.fileInfo);
    
    // Show success notification
    showNotification('success', `File "${file.name}" selected and validated successfully!`, 3000);
    
    console.log('=== handleFileSelection completed successfully ===');
    return true;
}

// Enhanced function to clear invalid files with user feedback
function clearInvalidFile(fileInput, fileName, errors) {
    console.log('=== clearInvalidFile called ===');
    console.log('Clearing file:', fileName);
    console.log('Errors:', errors);
    
    // Clear the file input
    fileInput.value = '';
    
    // Clear any existing preview
    clearFilePreview();
    
    // Show detailed error notification
    const errorMessage = `File "${fileName}" was automatically removed due to validation errors:\n• ${errors.join('\n• ')}`;
    showNotification('error', errorMessage, 8000);
    
    // Reset the drop zone visual state
    const dropZone = document.querySelector('.file-drop-zone');
    if (dropZone) {
        dropZone.classList.remove('drag-over');
        
        // Temporarily highlight the drop zone to show it's been reset
        dropZone.style.borderColor = '#dc3545';
        dropZone.style.backgroundColor = '#f8d7da';
        
        setTimeout(() => {
            dropZone.style.borderColor = '';
            dropZone.style.backgroundColor = '';
        }, 2000);
    }
    
    // Log the validation failure for debugging
    console.warn('File validation failed and file was cleared:', {
        fileName: fileName,
        errors: errors,
        timestamp: new Date().toISOString()
    });
    
    console.log('=== clearInvalidFile completed ===');
}

// Enhanced file preview with detailed information and improved image preview
function showFilePreview(file, config, fileInfo) {
    let fileInfoElement = document.getElementById('file-preview-info');
    
    if (!fileInfoElement) {
        // Create file info display if it doesn't exist
        const fileInput = document.getElementById('block-file');
        if (fileInput) {
            fileInfoElement = document.createElement('div');
            fileInfoElement.id = 'file-preview-info';
            fileInfoElement.className = 'mt-3 p-3 bg-light border rounded file-preview-container';
            fileInput.parentNode.insertBefore(fileInfoElement, fileInput.nextSibling);
        }
    }
    
    if (fileInfoElement) {
        const maxSize = config.max_file_size || (10 * 1024 * 1024);
        const sizePercentage = fileInfo.sizePercentage || Math.round((file.size / maxSize) * 100);
        
        // Determine file type icon
        const fileIcon = getFileTypeIcon(file.type, fileInfo.extension);
        
        // Check if file is an image for enhanced preview
        const isImage = file.type.startsWith('image/');
        const isVideo = file.type.startsWith('video/');
        const isAudio = file.type.startsWith('audio/');
        const isPDF = file.type === 'application/pdf';
        
        fileInfoElement.innerHTML = `
            <div class="file-selection-feedback">
                <div class="d-flex align-items-start">
                    <div class="file-icon me-3">
                        <i class="${fileIcon.icon} fa-2x ${fileIcon.color}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="file-details">
                            <div class="fw-bold text-dark d-flex align-items-center">
                                ${fileInfo.name}
                                ${isImage ? '<span class="badge bg-info ms-2"><i class="fas fa-image"></i> Image</span>' : ''}
                                ${isVideo ? '<span class="badge bg-warning ms-2"><i class="fas fa-video"></i> Video</span>' : ''}
                                ${isAudio ? '<span class="badge bg-success ms-2"><i class="fas fa-music"></i> Audio</span>' : ''}
                                ${isPDF ? '<span class="badge bg-danger ms-2"><i class="fas fa-file-pdf"></i> PDF</span>' : ''}
                            </div>
                            <div class="small text-muted mb-2">
                                <span class="badge bg-secondary me-1">${fileInfo.extension.toUpperCase()}</span>
                                ${fileInfo.sizeFormatted} • ${fileInfo.type || 'Unknown type'}
                                <br>
                                <i class="fas fa-clock me-1"></i>Modified: ${fileInfo.lastModifiedDate}
                            </div>
                            
                            <!-- File size indicator -->
                            <div class="file-size-indicator mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">File size</small>
                                    <small class="text-muted">${sizePercentage}% of limit</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar ${getSizeProgressBarClass(sizePercentage)}" 
                                         style="width: ${Math.min(sizePercentage, 100)}%"
                                         role="progressbar" 
                                         aria-valuenow="${sizePercentage}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                                <div class="small text-muted mt-1">
                                    Maximum allowed: ${formatFileSize(maxSize)}
                                    ${sizePercentage > 90 ? '<span class="text-warning ms-2"><i class="fas fa-exclamation-triangle"></i> Close to limit!</span>' : ''}
                                </div>
                            </div>
                            
                            <!-- Upload status indicator (initially hidden) -->
                            <div class="upload-status-indicator d-none" id="upload-status-${Date.now()}">
                                <div class="upload-progress mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Upload progress</small>
                                        <small class="upload-percentage text-muted">0%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                             style="width: 0%"
                                             role="progressbar" 
                                             aria-valuenow="0" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                    <div class="upload-speed small text-muted mt-1"></div>
                                </div>
                                
                                <div class="upload-status-message">
                                    <div class="status-preparing d-none">
                                        <i class="fas fa-cog fa-spin text-info me-2"></i>
                                        <span>Preparing upload...</span>
                                    </div>
                                    <div class="status-uploading d-none">
                                        <i class="fas fa-cloud-upload-alt text-primary me-2"></i>
                                        <span>Uploading file...</span>
                                    </div>
                                    <div class="status-processing d-none">
                                        <i class="fas fa-cog fa-spin text-info me-2"></i>
                                        <span>Processing on server...</span>
                                    </div>
                                    <div class="status-success d-none">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span>Upload completed successfully!</span>
                                    </div>
                                    <div class="status-error d-none">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        <span class="error-message">Upload failed</span>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2 retry-upload-btn">
                                            <i class="fas fa-redo"></i> Retry
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="file-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFileSelection()" title="Remove file">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Enhanced file preview section -->
                <div class="file-preview-section mt-3" id="file-preview-section">
                    <!-- Image preview with enhanced features -->
                    ${isImage ? `
                        <div class="image-preview-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="fas fa-image me-2"></i>Image Preview</h6>
                                <div class="image-preview-controls">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleImagePreviewSize()" title="Toggle size">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="image-preview-wrapper text-center p-3 bg-white border rounded" style="min-height: 200px;">
                                <img id="image-preview" src="" alt="Preview" 
                                     class="img-fluid rounded shadow-sm" 
                                     style="max-width: 100%; max-height: 300px; cursor: pointer;"
                                     onclick="openImagePreviewModal(this.src, '${fileInfo.name}')">
                                <div class="image-loading-spinner d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="mt-2 text-muted">Loading preview...</div>
                                </div>
                            </div>
                            <div class="image-info mt-2 small text-muted" id="image-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <i class="fas fa-ruler-combined me-1"></i>
                                        <span id="image-dimensions">Calculating dimensions...</span>
                                    </div>
                                    <div class="col-md-6">
                                        <i class="fas fa-palette me-1"></i>
                                        <span id="image-format">${fileInfo.extension.toUpperCase()} format</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Video preview -->
                    ${isVideo ? `
                        <div class="video-preview-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="fas fa-video me-2"></i>Video Preview</h6>
                            </div>
                            <div class="video-preview-wrapper text-center p-3 bg-white border rounded">
                                <video id="video-preview" controls class="rounded shadow-sm" style="max-width: 100%; max-height: 300px;">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Audio preview -->
                    ${isAudio ? `
                        <div class="audio-preview-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="fas fa-music me-2"></i>Audio Preview</h6>
                            </div>
                            <div class="audio-preview-wrapper text-center p-3 bg-white border rounded">
                                <audio id="audio-preview" controls class="w-100">
                                    Your browser does not support the audio tag.
                                </audio>
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- PDF preview placeholder -->
                    ${isPDF ? `
                        <div class="pdf-preview-container">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><i class="fas fa-file-pdf me-2"></i>PDF Document</h6>
                            </div>
                            <div class="pdf-preview-wrapper text-center p-4 bg-white border rounded">
                                <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                                <div class="h6">PDF Document Ready</div>
                                <div class="text-muted">PDF preview will be available after upload</div>
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Generic file preview -->
                    ${!isImage && !isVideo && !isAudio && !isPDF ? `
                        <div class="generic-file-preview">
                            <div class="text-center p-4 bg-white border rounded">
                                <i class="${fileIcon.icon} fa-4x ${fileIcon.color} mb-3"></i>
                                <div class="h6">File Ready for Upload</div>
                                <div class="text-muted">Preview not available for this file type</div>
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        // Load image preview with enhanced features
        if (isImage) {
            loadImagePreview(file);
        }
        
        // Load video preview
        if (isVideo) {
            loadVideoPreview(file);
        }
        
        // Load audio preview
        if (isAudio) {
            loadAudioPreview(file);
        }
        
        // Store file reference for upload progress tracking
        fileInfoElement.dataset.fileName = file.name;
        fileInfoElement.dataset.fileSize = file.size;
        fileInfoElement.dataset.fileType = file.type;
    }
}

// Enhanced image preview loading with dimensions and error handling
function loadImagePreview(file) {
    const imagePreview = document.getElementById('image-preview');
    const imageInfo = document.getElementById('image-info');
    const imageDimensions = document.getElementById('image-dimensions');
    const loadingSpinner = document.querySelector('.image-loading-spinner');
    
    if (!imagePreview) return;
    
    // Show loading spinner
    if (loadingSpinner) {
        loadingSpinner.classList.remove('d-none');
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            imagePreview.src = e.target.result;
            
            // Update dimensions
            if (imageDimensions) {
                imageDimensions.textContent = `${this.naturalWidth} × ${this.naturalHeight} pixels`;
            }
            
            // Hide loading spinner
            if (loadingSpinner) {
                loadingSpinner.classList.add('d-none');
            }
            
            // Add fade-in animation
            imagePreview.style.opacity = '0';
            imagePreview.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                imagePreview.style.opacity = '1';
            }, 100);
        };
        
        img.onerror = function() {
            if (imageDimensions) {
                imageDimensions.textContent = 'Unable to load image';
            }
            if (loadingSpinner) {
                loadingSpinner.classList.add('d-none');
            }
        };
        
        img.src = e.target.result;
    };
    
    reader.onerror = function() {
        if (imageDimensions) {
            imageDimensions.textContent = 'Error reading file';
        }
        if (loadingSpinner) {
            loadingSpinner.classList.add('d-none');
        }
    };
    
    reader.readAsDataURL(file);
}

// Load video preview
function loadVideoPreview(file) {
    const videoPreview = document.getElementById('video-preview');
    if (!videoPreview) return;
    
    const url = URL.createObjectURL(file);
    videoPreview.src = url;
    
    // Clean up object URL when video is loaded
    videoPreview.addEventListener('loadeddata', function() {
        // Video is loaded and ready
    });
    
    videoPreview.addEventListener('error', function() {
        videoPreview.innerHTML = '<div class="text-muted">Unable to preview this video format</div>';
    });
}

// Load audio preview
function loadAudioPreview(file) {
    const audioPreview = document.getElementById('audio-preview');
    if (!audioPreview) return;
    
    const url = URL.createObjectURL(file);
    audioPreview.src = url;
    
    audioPreview.addEventListener('error', function() {
        audioPreview.innerHTML = '<div class="text-muted">Unable to preview this audio format</div>';
    });
}

// Toggle image preview size
function toggleImagePreviewSize() {
    const imagePreview = document.getElementById('image-preview');
    if (!imagePreview) return;
    
    const currentMaxHeight = imagePreview.style.maxHeight;
    if (currentMaxHeight === '300px' || !currentMaxHeight) {
        imagePreview.style.maxHeight = '600px';
    } else {
        imagePreview.style.maxHeight = '300px';
    }
}

// Open image preview in modal
function openImagePreviewModal(src, filename) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-image me-2"></i>
                        ${filename}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${src}" alt="${filename}" class="img-fluid rounded shadow">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Clean up modal when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
}

// Show validation warnings
function showValidationWarnings(warnings) {
    if (warnings.length === 0) return;
    
    const warningHtml = warnings.map(warning => `
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${warning}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `).join('');
    
    // Find a container to show warnings
    const container = document.querySelector('.modal-body') || document.querySelector('.card-body');
    if (container) {
        const warningContainer = document.createElement('div');
        warningContainer.innerHTML = warningHtml;
        container.insertBefore(warningContainer, container.firstChild);
    }
}

function clearFileSelection() {
    const fileInput = document.getElementById('block-file');
    if (fileInput) {
        fileInput.value = '';
    }
    clearFilePreview();
}

function clearFilePreview() {
    const fileInfo = document.getElementById('file-preview-info');
    if (fileInfo) {
        fileInfo.remove();
    }
}

function getContentTypeConfig(contentType) {
    const contentTypes = @json($contentTypes ?? []);
    return contentTypes[contentType] || {};
}

// Helper function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Helper function to escape HTML characters
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Helper function to get file extension
function getFileExtension(filename) {
    if (!filename || typeof filename !== 'string') {
        return '';
    }
    
    const parts = filename.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
}

// STEP 7: INITIALIZATION
// DOM Initialization moved to central block at end of file

// STEP 8: SAVE CONTENT BLOCK
window.saveContentBlock = async function() {
    console.log('[ContentBuilder] Starting saveContentBlock (Async Multi-Upload)');
    
    const form = document.getElementById('content-edit-form');
    const saveButton = document.getElementById('save-content-block');
    const fileInput = document.getElementById('block-file');
    
    if (!form || !saveButton) return;

    // Get the content type
    const typeInput = document.querySelector('input[name="type"]');
    const type = typeInput ? typeInput.value : null;
    if (!type) return;

    // 1. Double-Save Prevention
    saveButton.disabled = true;

    // 2. Handle Text Blocks (Traditional Flow)
    if (type === 'text') {
        const contentInput = document.getElementById('block-content');
        if (currentEditorInstance && contentInput) {
            try {
                const outputData = await currentEditorInstance.save();
                contentInput.value = JSON.stringify(outputData);
            } catch (error) {
                console.error('EditorJS save failed:', error);
                saveButton.disabled = false;
                return;
            }
        }
    }

    // 3. Collect Files for Media Blocks
    const files = (fileInput && fileInput.files) ? Array.from(fileInput.files) : [];
    console.log('[ContentBuilder] [AsyncUpload] File count:', files.length);
    const isMedia = ['image', 'pdf', 'audio', 'video'].includes(type);

    // 4. Fallback for Empty Media (unless external URL is provided or editing existing block)
    const externalUrl = document.querySelector('input[name="external_url"]')?.value;
    const isEditingExisting = (form.dataset.isEdit === 'true' || form.dataset.editId);
    if (isMedia && files.length === 0 && !externalUrl && !isEditingExisting) {
        alert('Please select a file or enter a URL.');
        saveButton.disabled = false;
        return;
    }

    // 5. START ASYNC UPLOADS (for multiple files OR new bulk uploads)
    // CRITICAL FIX: Treat Optimistic blocks (temp-ID) as "New" blocks for async upload
    const isEditMode = (form.dataset.isEdit === 'true' || form.dataset.editId);
    let targetContentId = form.dataset.editId || form.dataset.contentId;
    const isTempBlock = targetContentId && String(targetContentId).startsWith('temp-');
    
    if (isMedia && files.length > 0) {
        console.log(`[ContentBuilder] [AsyncUpload] Triggered for ${files.length} files. isEditMode: ${isEditMode}, isTempBlock: ${isTempBlock}`);
        
        // REMOVE host block from UI to make room for async placeholders.
        if (window.pageBuilderInstance && targetContentId) {
            console.log(`[ContentBuilder] [AsyncUpload] Removing host block ${targetContentId} from UI.`);
            
            // 1. Always Remove from DOM instantly
            window.pageBuilderInstance.removeBlockNode(targetContentId);
            
            // 2. Trigger SERVER cleanup ONLY if it's a real existing block (Edit Mode)
            // If it's a Temp Block, it was never created on server (due to our addBlock fix). 
            if (!isTempBlock) {
                if (typeof window.pageBuilderInstance.deleteBlock === 'function') {
                    console.log(`[ContentBuilder] [AsyncUpload] Tracing: real block ${targetContentId} will be deleted on server to be replaced by new async upload.`);
                    window.pageBuilderInstance.deleteBlock(targetContentId);
                } else {
                    window.pageBuilderInstance.initialData = window.pageBuilderInstance.initialData.filter(b => b.id != targetContentId);
                }
            } else {
                console.log(`[ContentBuilder] [AsyncUpload] Tracing: ${targetContentId} is a local-only temp block. Skipping server delete.`);
                window.pageBuilderInstance.initialData = window.pageBuilderInstance.initialData.filter(b => b.id != targetContentId);
            }
        }

        files.forEach(file => {
            const correlationId = 'block-' + Math.random().toString(36).substr(2, 9);
            console.log(`[ContentBuilder] [AsyncUpload] Upload started for: ${file.name}, correlationId: ${correlationId}`);
            
            // Add Placeholder instantly
            if (window.pageBuilderInstance && typeof window.pageBuilderInstance.addPendingBlock === 'function') {
                window.pageBuilderInstance.addPendingBlock(type, file.name, correlationId);
                console.log(`[ContentBuilder] [AsyncUpload] Placeholder inserted for: ${correlationId}`);
            } else {
                console.warn('[ContentBuilder] [AsyncUpload] PageBuilder.addPendingBlock not available!');
            }
            
            // Start Background Upload with Promise tracking
            const uploadPromise = uploadFileInBackground(file, type, correlationId);
            
            // Store promise for resolution tracking (useful for "Save All" scenarios)
            if (window.pageBuilderInstance) {
                if (!window.pageBuilderInstance.blockCreationPromises) {
                    window.pageBuilderInstance.blockCreationPromises = new Map();
                }
                window.pageBuilderInstance.blockCreationPromises.set(correlationId, uploadPromise);
                console.log(`[ContentBuilder] [AsyncUpload] Promise created and tracked for: ${correlationId}`);
            }
        });

        // Safe Closing
        setTimeout(() => {
            const modal = document.getElementById('contentEditModal');
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) bsModal.hide();
            saveButton.disabled = false;
        }, 50);

        return; // Success! Background tasks started.
    }

    // 6. Traditional Single-Block Save (Fallback/Text/External URL)
    const formData = new FormData(form);
    const courseId = @json($course->id ?? 0);
    const moduleId = @json($module->id ?? 0);
    const subpageId = @json($subpage->id ?? 0);
    const isEdit = (form.dataset.isEdit === 'true' || form.dataset.editId);
    targetContentId = form.dataset.editId || form.dataset.contentId;

    // RACE CONDITION FIX: Wait for temp ID resolution
    if (targetContentId && targetContentId.toString().startsWith('temp-') && window.pageBuilderInstance) {
        console.log('[Save] detected temporary ID in form:', targetContentId);
        
        // Re-read from form in case it resolved between getting targetContentId and here
        targetContentId = form.dataset.editId || form.dataset.contentId;
        console.log('[Save] current ID after re-read:', targetContentId);
        
        if (targetContentId && targetContentId.toString().startsWith('temp-')) {
            const promise = window.pageBuilderInstance.blockCreationPromises?.get(targetContentId);
            if (promise) {
                console.log('[Save] resolution promise FOUND. Waiting...');
                try {
                    // Update UI to show we are waiting
                    const originalText = saveButton.innerHTML;
                    saveButton.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i>Creating...';
                    
                    const realBlock = await promise;
                    console.log('[Save] background creation RESOLVED to real ID:', realBlock.id);
                    targetContentId = realBlock.id;
                    
                    // Double-check form dataset updates
                    if (form.dataset.editId) form.dataset.editId = realBlock.id;
                    if (form.dataset.contentId) form.dataset.contentId = realBlock.id;

                    saveButton.innerHTML = originalText;
                } catch (e) {
                    console.error('Block creation failed during save wait:', e);
                    showAlert('danger', 'Block creation failed. Please close and try again.');
                    saveButton.disabled = false;
                    return;
                }
            } else {
                // If it's STILL a temp ID but no promise, it might be in the middle of resolving.
                // Or it might have failed. Let's try one last re-read after a micro-delay.
                console.warn('[Save] Temp ID found but no promise in map. Re-checking form...');
                await new Promise(r => setTimeout(r, 100));
                targetContentId = form.dataset.editId || form.dataset.contentId;
                
                if (targetContentId.toString().startsWith('temp-')) {
                    console.error('[Save] ID is STILL temporary after delay. Construction aborted to prevent 404.');
                    showAlert('danger', 'Background creation is taking too long. Please wait a moment and try again.');
                    saveButton.disabled = false;
                    return;
                }
                console.log('[Save] Successfully recovered real ID:', targetContentId);
            }
        } else {
            console.log('[Save] Temp ID already resolved to:', targetContentId);
        }
    }
    
    let apiUrl = `/api/v1/courses/${courseId}/modules/${moduleId}/subpages/${subpageId}/content-blocks`;
    let method = 'POST';
    
    if (isEdit) {
        apiUrl += `/${targetContentId}`;
        formData.append('_method', 'PUT');
    }

    // Ensure is_active is sent as 1/0 (Laravel boolean validation fix)
    const isActiveInput = form.querySelector('[name="is_active"]');
    if (isActiveInput) {
        formData.set('is_active', isActiveInput.checked ? '1' : '0');
    }

    try {
        const response = await fetch(apiUrl, {
            method: 'POST', // Always POST for Laravel with _method override
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();
        if (data.success) {
            const modal = document.getElementById('contentEditModal');
            bootstrap.Modal.getInstance(modal)?.hide();
            showAlert('success', 'Saved successfully.');
            
            // Auto-open edit modal for newly created blocks
            if (!isEdit && data.data && data.data.id) {
                // New block created: re-render and open edit modal after 20ms
                setTimeout(() => {
                    if (window.editContentBlock) {
                        editContentBlock(data.data.id);
                    } else {
                        window.location.reload();
                    }
                }, 20);
            } else {
                // Edit mode: reload to reflect changes
                setTimeout(() => window.location.reload(), 500);
            }
        } else {
            console.error('[Save] Server rejected request:', data);
            let errorMsg = data.message || 'Save failed.';
            
            if (response.status === 404) {
                errorMsg = 'This content block could not be found. It may have been deleted. Please refresh the page.';
            }

            if (data.errors) {
                console.group('Validation Errors:');
                Object.entries(data.errors).forEach(([field, messages]) => {
                    console.error(`${field}:`, messages.join(', '));
                });
                console.groupEnd();
            }
            showAlert('danger', errorMsg);
            saveButton.disabled = false;
        }
    } catch (err) {
        console.error('Save error:', err);
        saveButton.disabled = false;
    }
}

/**
 * Background Upload Worker
 * Rewritten to use Direct-to-R2 Pre-signed URLs for massive performance improvements.
 */
function uploadFileInBackground(file, type, correlationId) {
    return new Promise(async (resolve, reject) => {
        const form = document.getElementById('content-edit-form');
        
        // Form Data Collection (Metadata)
        const title = form ? (form.querySelector('[name="title"]')?.value || '') : '';
        const description = form ? (form.querySelector('[name="description"]')?.value || '') : '';
        const visibility = form ? (form.querySelector('[name="visibility"]')?.value || 'student') : 'student';
        const isActive = form ? (form.querySelector('[name="is_active"]')?.checked ? '1' : '0') : '1';
        const section = form ? (form.querySelector('[name="section"]')?.value || window.currentContentSection || 'main_content') : 'main_content';

        const courseId = @json($course->id ?? 0);
        const moduleId = @json($module->id ?? 0);
        const subpageId = @json($subpage->id ?? 0);
        
        const presignedApiUrl = `/api/v1/courses/${courseId}/modules/${moduleId}/subpages/${subpageId}/content-blocks/presigned-url`;
        const storeApiUrl = `/api/v1/courses/${courseId}/modules/${moduleId}/subpages/${subpageId}/content-blocks`;

        try {
            // STEP 1: Request Pre-signed URL from VPS
            window.pageBuilderInstance._updatePlaceholderProgress(correlationId, 0, 'Preparing...');
            
            const presignedPayload = {
                filename: file.name,
                content_type: file.type || 'application/octet-stream',
                file_size: file.size,
                topic: 'content-blocks',
                subpage_id: subpageId,
                block_type: type
            };

            const presignedResponse = await fetch(presignedApiUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(presignedPayload)
            });

            const presignedData = await presignedResponse.json();
            
            if (!presignedResponse.ok || !presignedData.success) {
                throw new Error(presignedData.message || 'Failed to generate upload URL.');
            }

            const { upload_url, path: r2_path } = presignedData.data;

            // STEP 2: Direct PUT to Cloudflare R2 Edge using XMLHttpRequest (for progress)
            window.pageBuilderInstance._updatePlaceholderProgress(correlationId, 5, 'Uploading direct to R2...');
            
            await new Promise((resolveUpload, rejectUpload) => {
                const xhr = new XMLHttpRequest();
                
                // 2-hour timeout for absolutely massive video files over slow connections
                const timeoutHandle = setTimeout(() => {
                    xhr.abort();
                    rejectUpload(new Error('Upload to R2 timed out'));
                }, 7200000);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        // Map R2 upload to 5% -> 90% of the total UI progress bar
                        const percentRaw = (e.loaded / e.total);
                        const percentScaled = 5 + (percentRaw * 85); 
                        window.pageBuilderInstance._updatePlaceholderProgress(correlationId, percentScaled, 'Uploading direct to R2...');
                    }
                };

                xhr.onreadystatechange = () => {
                    if (xhr.readyState === 4) {
                        clearTimeout(timeoutHandle);
                        // S3/R2 returns 200 on successful PUT
                        if (xhr.status >= 200 && xhr.status < 300) {
                            resolveUpload();
                        } else {
                            console.error('R2 Direct Upload Failed:', xhr.status, xhr.responseText);
                            rejectUpload(new Error(`Cloudflare R2 rejected upload (Status ${xhr.status})`));
                        }
                    }
                };

                xhr.open('PUT', upload_url, true);
                
                // IMPORTANT: The Content-Type must strictly match what was presigned
                xhr.setRequestHeader('Content-Type', file.type || 'application/octet-stream');
                
                xhr.send(file);
            });

            // STEP 3: Final POST to VPS Store Endpoint with `r2_path`
            window.pageBuilderInstance._updatePlaceholderProgress(correlationId, 95, 'Saving to database...');
            
            const storeFormData = new FormData();
            storeFormData.append('type', type);
            // Notice: We do NOT append 'file' here anymore. We pass the path.
            storeFormData.append('r2_path', r2_path);
            storeFormData.append('original_filename', file.name); // Pass original string for display
            
            storeFormData.append('title', title);
            storeFormData.append('description', description);
            storeFormData.append('visibility', visibility);
            storeFormData.append('is_active', isActive);
            storeFormData.append('section', section);

            const storeResponse = await fetch(storeApiUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: storeFormData
            });

            const storeData = await storeResponse.json();
            
            if (storeResponse.ok && storeData.success) {
                console.log(`[ContentBuilder] [AsyncUpload] Success for: ${correlationId}`);
                window.pageBuilderInstance.updatePendingBlock(correlationId, storeData.data);
                
                // Auto-open edit modal for the newly created block
                if (storeData.data && storeData.data.id && window.editContentBlock) {
                    setTimeout(() => {
                        editContentBlock(storeData.data.id, storeData.data);
                    }, 50);
                }
                
                resolve(storeData.data);
            } else {
                throw new Error(storeData.message || 'Failed to save block metadata to database.');
            }

        } catch (error) {
            console.error('[ContentBuilder] [AsyncUpload] Flow Error:', error);
            
            if (window.pageBuilderInstance) {
                window.pageBuilderInstance.removePendingBlock(correlationId);
            }
            
            showAlert('danger', error.message || `Upload workflow failed for ${file.name}`);
            reject(error);
        }
    });
}
    


/**
 * Set up retry mechanism for failed uploads
 * @param {HTMLElement} statusElement - Upload status element
 * @param {Function} retryCallback - Function to call for retry
 */
function setupRetryMechanism(statusElement, retryCallback) {
    if (!statusElement) return;
    
    const retryButton = statusElement.querySelector('.retry-upload-btn');
    if (retryButton) {
        // Remove existing event listeners
        const newRetryButton = retryButton.cloneNode(true);
        retryButton.parentNode.replaceChild(newRetryButton, retryButton);
        
        // Add new event listener
        newRetryButton.addEventListener('click', function() {
            console.log('Retry upload requested');
            
            // Hide error status and show preparing
            showUploadStatus(statusElement, 'preparing');
            
            // Reset progress
            updateUploadProgress(statusElement, 0);
            
            // Call retry function after short delay
            setTimeout(() => {
                retryCallback();
            }, 500);
        });
    }
}

// ENHANCED ERROR DISPLAY FUNCTIONS
function displayEnhancedErrorMessages(errorData, fileInput = null) {
    console.log('Displaying enhanced error messages:', errorData);
    
    // Extract file information if available
    const fileInfo = getFileInformation(fileInput);
    
    // Build comprehensive error message
    let errorMessage = errorData.message || 'Failed to create content block.';
    let errorDetails = [];
    let troubleshootingSteps = [];
    
    // Process validation errors with enhanced details
    if (errorData.errors) {
        console.log('Processing validation errors:', errorData.errors);
        
        // Handle file upload errors specifically
        if (errorData.errors.file) {
            const fileErrors = Array.isArray(errorData.errors.file) ? errorData.errors.file : [errorData.errors.file];
            
            // Add file information to error context
            if (fileInfo) {
                errorDetails.push(`File: ${fileInfo.name} (${fileInfo.sizeFormatted}, ${fileInfo.type})`);
            }
            
            // Process each file error with specific guidance
            fileErrors.forEach(error => {
                errorDetails.push(error);
                
                // Add specific troubleshooting based on error type
                const troubleshooting = getTroubleshootingForError(error, fileInfo);
                troubleshootingSteps.push(...troubleshooting);
            });
            
            // Add upload guidance from server if available
            if (errorData.upload_guidance) {
                addUploadGuidance(errorData.upload_guidance, errorDetails, troubleshootingSteps);
            }
        } else {
            // Handle other validation errors
            Object.entries(errorData.errors).forEach(([field, fieldErrors]) => {
                const errors = Array.isArray(fieldErrors) ? fieldErrors : [fieldErrors];
                errors.forEach(error => {
                    errorDetails.push(`${field}: ${error}`);
                });
            });
        }
    }
    
    // Add server configuration information if available
    if (errorData.server_info) {
        addServerConfigurationInfo(errorData.server_info, errorDetails);
    }
    
    // Add retry suggestions from server
    if (errorData.retry_suggestions && errorData.retry_suggestions.length > 0) {
        troubleshootingSteps.push(...errorData.retry_suggestions);
    }
    
    // Display the enhanced error message
    showEnhancedAlert('danger', errorMessage, {
        details: errorDetails,
        troubleshooting: troubleshootingSteps,
        correlationId: errorData.correlation_id,
        timestamp: errorData.timestamp,
        support: errorData.support
    });
}

function getFileInformation(fileInput) {
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        return null;
    }
    
    const file = fileInput.files[0];
    return {
        name: file.name,
        size: file.size,
        sizeFormatted: formatFileSize(file.size),
        type: file.type || 'Unknown type',
        extension: file.name.split('.').pop().toLowerCase(),
        lastModified: new Date(file.lastModified).toLocaleString()
    };
}

function getTroubleshootingForError(error, fileInfo) {
    const troubleshooting = [];
    const errorLower = error.toLowerCase();
    
    if (errorLower.includes('size') && errorLower.includes('exceed')) {
        troubleshooting.push('Try compressing the file or reducing its quality');
        if (fileInfo && fileInfo.type.startsWith('image/')) {
            troubleshooting.push('For images, consider resizing to smaller dimensions');
            troubleshooting.push('Use image compression tools to reduce file size');
        }
        if (fileInfo && fileInfo.type === 'application/pdf') {
            troubleshooting.push('For PDFs, try reducing image quality or removing unnecessary pages');
        }
    }
    
    if (errorLower.includes('type') || errorLower.includes('extension') || errorLower.includes('not allowed')) {
        troubleshooting.push('Ensure the file has the correct extension');
        troubleshooting.push('Try saving the file in a different supported format');
        if (fileInfo) {
            troubleshooting.push(`Current file type: ${fileInfo.type} (.${fileInfo.extension})`);
        }
    }
    
    if (errorLower.includes('corrupted') || errorLower.includes('invalid') || errorLower.includes('mime')) {
        troubleshooting.push('Try opening the file to verify it\'s not corrupted');
        troubleshooting.push('Re-save or re-export the file from its original application');
        troubleshooting.push('Ensure the file extension matches the actual file type');
    }
    
    if (errorLower.includes('server') || errorLower.includes('configuration') || errorLower.includes('limit')) {
        troubleshooting.push('Contact your administrator for server configuration assistance');
        troubleshooting.push('Try again later as this may be a temporary server issue');
    }
    
    if (errorLower.includes('space') || errorLower.includes('disk')) {
        troubleshooting.push('Server storage may be full - contact your administrator');
        troubleshooting.push('Try uploading a smaller file');
    }
    
    if (errorLower.includes('memory')) {
        troubleshooting.push('Server memory limit reached - try a smaller file');
        troubleshooting.push('Contact your administrator to increase memory limits');
    }
    
    return troubleshooting;
}

function addUploadGuidance(guidance, errorDetails, troubleshootingSteps) {
    if (guidance.supported_types && guidance.supported_types.formatted) {
        errorDetails.push(`Supported file types: ${guidance.supported_types.formatted}`);
    }
    
    if (guidance.size_limits) {
        if (guidance.size_limits.max_size_formatted) {
            errorDetails.push(`Maximum file size: ${guidance.size_limits.max_size_formatted}`);
        }
        if (guidance.size_limits.server_limit) {
            errorDetails.push(`Server upload limit: ${guidance.size_limits.server_limit}`);
        }
    }
    
    if (guidance.troubleshooting && guidance.troubleshooting.length > 0) {
        troubleshootingSteps.push(...guidance.troubleshooting);
    }
}

function addServerConfigurationInfo(serverInfo, errorDetails) {
    if (serverInfo.upload_limits) {
        errorDetails.push('Server Upload Limits:');
        Object.entries(serverInfo.upload_limits).forEach(([key, value]) => {
            errorDetails.push(`  ${key}: ${value}`);
        });
    }
    
    if (serverInfo.disk_space) {
        errorDetails.push(`Available disk space: ${serverInfo.disk_space.free_formatted || 'Unknown'}`);
    }
    
    if (serverInfo.recommendations && serverInfo.recommendations.length > 0) {
        errorDetails.push('Server Recommendations:');
        serverInfo.recommendations.forEach(rec => {
            errorDetails.push(`  ${rec}`);
        });
    }
}

function showEnhancedAlert(type, message, options = {}) {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.alert-notification');
    existingAlerts.forEach(alert => alert.remove());
    
    // Build enhanced alert HTML
    let alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show alert-notification position-fixed" 
             role="alert" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 400px; max-width: 600px;">
            <div class="d-flex align-items-start">
                <i class="fas fa-${getAlertIcon(type)} me-2 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>${message}</strong>
    `;
    
    // Add error details if present
    if (options.details && options.details.length > 0) {
        alertHtml += `
                    <div class="mt-2">
                        <small class="text-muted">Details:</small>
                        <ul class="mb-1 small">
        `;
        options.details.forEach(detail => {
            alertHtml += `<li>${detail}</li>`;
        });
        alertHtml += `</ul></div>`;
    }
    
    // Add troubleshooting steps if present
    if (options.troubleshooting && options.troubleshooting.length > 0) {
        // Remove duplicates and limit to most relevant steps
        const uniqueSteps = [...new Set(options.troubleshooting)].slice(0, 5);
        
        alertHtml += `
                    <div class="mt-2">
                        <small class="text-muted">Suggested solutions:</small>
                        <ul class="mb-1 small">
        `;
        uniqueSteps.forEach(step => {
            alertHtml += `<li>${step}</li>`;
        });
        alertHtml += `</ul></div>`;
    }
    
    // Add support information if present
    if (options.support && options.correlationId) {
        alertHtml += `
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            <strong>Support ID:</strong> ${options.correlationId.substring(0, 8)}...
                            <br>
                            ${options.support.contact_message || 'Contact support if this problem persists.'}
                        </small>
                    </div>
        `;
    }
    
    alertHtml += `
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Insert at top of body
    document.body.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after longer duration for enhanced alerts (10 seconds)
    setTimeout(() => {
        const alert = document.querySelector('.alert-notification');
        if (alert) {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }
    }, 10000);
}

// STEP 9: SHOW ALERT MESSAGES
function showAlert(type, message, duration = 5000) {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.alert-notification');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create alert
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show alert-notification position-fixed" 
             role="alert" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at top of body
    document.body.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after specified duration
    if (duration > 0) {
        setTimeout(() => {
            const alert = document.querySelector('.alert-notification');
            if (alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }
        }, duration);
    }
}

function getAlertIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'danger': return 'exclamation-triangle';
        case 'warning': return 'exclamation-circle';
        case 'info': return 'info-circle';
        default: return 'info-circle';
    }
}

// STEP 9: DELETE CONTENT BLOCK (Optimistic + Background)
function deleteContentBlock(contentId, contentTitle) {
    console.log('Delete content block requested:', contentId);
    
    // 1. Confirm deletion (since it's destructive)
    const title = contentTitle || 'this content';
    if (!confirm(`Are you sure you want to delete "${title}"?\n\nThis block will be moved to the Trash.`)) {
        return;
    }
    
    // 2. USE PAGEBUILDER OPTIMISTIC DELETE
    // This removes the DOM element instantly and starts background delete
    if (window.pageBuilderInstance && typeof window.pageBuilderInstance.deleteBlock === 'function') {
        window.pageBuilderInstance.deleteBlock(contentId);
        console.log('Optimistic delete triggered via PageBuilder');
        
        // Success notification (Optimistic)
        showAlert('success', 'Content block removed.');
        
        // 3. Handle empty states after a short delay for DOM to update
        setTimeout(() => {
            const container = document.getElementById('page-builder-holder');
            if (container && !container.querySelector('.page-block')) {
                // If the entire holder is empty (no more blocks)
                console.log('Canvas is now empty after delete');
                // You could trigger a re-render or show an empty state here if needed
            }
        }, 100);
        
        return;
    }

    // Fallback if PageBuilder instance is not ready (legacy mode)
    // [Legacy code removed for brevity - the above is the primary path]
    console.warn('PageBuilder instance not ready for optimistic delete. Falling back to slow delete.');
    performSlowDelete(contentId, contentTitle);
}

// Keeping the slow version as a backup helper if needed
async function performSlowDelete(contentId, contentTitle) {
    const courseId = @json($course->id ?? 0);
    const moduleId = @json($module->id ?? 0);
    const subpageId = @json($subpage->id ?? 0);
    const apiUrl = `/api/v1/courses/${courseId}/modules/${moduleId}/subpages/${subpageId}/content-blocks/${contentId}`;
    
    try {
        const response = await axios.post(apiUrl, { _method: 'DELETE' }, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (response.data.success) {
            const el = document.querySelector(`[data-id="${contentId}"]`);
            if (el) el.remove();
            showAlert('success', 'Deleted successfully.');
        }
    } catch (e) {
        console.error('Slow delete failed:', e);
        showAlert('danger', 'Failed to delete block.');
    }
}

// STEP 11: CONTENT BLOCK ACTIONS
async function editContentBlock(contentId, preloadedData = null) {
    console.log('Edit content block:', contentId);
    
    try {
        let block;

        // 1. Use Preloaded Data (Optimistic UI)
        if (preloadedData) {
             console.log('Using preloaded block data');
             block = preloadedData;
        } else if (contentId && String(contentId).startsWith('temp-') && window.pageBuilderInstance) {
            // NEW: Lookup local-only temp blocks from builder instance
            console.log('Looking up temporary block from local instance:', contentId);
            block = window.pageBuilderInstance.initialData.find(b => b.id == contentId);
            if (!block) {
                console.warn('Temporary block not found in initialData:', contentId);
                // Fallback to fetch just in case, though it will likely 404
            }
        }

        if (!block) {
            // 2. Fetch from Server
            const response = await fetch(`/api/v1/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/{{ $subpage->id }}/content-blocks/${contentId}`);
            const result = await response.json();
            
            if (!result.success) {
                showAlert('danger', result.message || 'Failed to load content block.');
                return;
            }
            block = result.data;
        }
        
        // Destroy any existing editor instance
        if (currentEditorInstance) {
            console.log('Destroying previous editor instance');
            currentEditorInstance.destroy();
            currentEditorInstance = null;
        }
        
        // Get content type configuration
        const contentTypes = @json($contentTypes ?? []);
        const typeConfig = contentTypes[block.type];
        
        if (!typeConfig) {
            showAlert('danger', 'Content type configuration not found.');
            return;
        }
        
        // Set modal title
        const modalTitle = document.getElementById('contentEditModalLabel');
        if (modalTitle) {
            modalTitle.textContent = `Edit ${typeConfig.label}`;
        }
        
        // Generate form with existing data
        const form = document.getElementById('content-edit-form');
        if (form) {
            const formContent = generateEditForm(block, typeConfig);
            form.innerHTML = formContent;
            
            // Store the content ID for update
            form.dataset.editId = contentId;
            form.dataset.isEdit = 'true';
        }
        
        // Open the content edit modal
        const modal = document.getElementById('contentEditModal');
        if (modal && window.bootstrap && window.bootstrap.Modal) {
            const bsModal = window.bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
            
            // Initialize EditorJS for text content after modal is shown
            if (block.type === 'text') {
                modal.addEventListener('shown.bs.modal', function initEditor() {
                    console.log('Modal shown, initializing EditorJS for edit...');
                    initializeEditorJSForEdit(block.content);
                    modal.removeEventListener('shown.bs.modal', initEditor);
                }, { once: true });
            }
        }
    } catch (error) {
        console.error('Error loading content block for edit:', error);
        showAlert('danger', 'Failed to load content block: ' + error.message);
    }
}

function generateEditForm(block, typeConfig) {
    const sections = @json($sections ?? []);
    const visibilityOptions = @json($visibilityOptions ?? []);
    
    let formHtml = `
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="block-title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="block-title" name="title" 
                           value="${escapeHtml(block.title || '')}" 
                           placeholder="Enter block title" required>
                </div>
                
                <div class="mb-3">
                    <label for="block-description" class="form-label">Description</label>
                    <textarea class="form-control" id="block-description" name="description" 
                              rows="3" placeholder="Optional description">${escapeHtml(block.description || '')}</textarea>
                </div>
    `;
    
    // Type-specific content fields
    switch (block.type) {
        case 'youtube':
            formHtml += `
                <div class="mb-3">
                    <label for="block-content" class="form-label">YouTube Video URL</label>
                    <input type="url" class="form-control" id="block-content" name="content" 
                           value="${escapeHtml(block.content || '')}"
                           placeholder="https://www.youtube.com/watch?v=..." required>
                    <div class="form-text">Paste the full YouTube video URL.</div>
                </div>
            `;
            break;

        case 'text':
            // Store the content in the hidden field so it's available even if EditorJS fails to initialize
            // IMPORTANT: Properly escape and encode the content to prevent HTML attribute issues
            let contentValue = '';
            if (block.content) {
                if (typeof block.content === 'string') {
                    contentValue = block.content;
                } else {
                    contentValue = JSON.stringify(block.content);
                }
            }
            
            // Double-encode for HTML attribute safety
            const safeContentValue = escapeHtml(contentValue);
            
            formHtml += `
                <div class="mb-3">
                    <label for="block-content" class="form-label">Content</label>
                    <div id="editorjs-holder" class="border rounded" style="min-height: 300px;"></div>
                    <input type="hidden" id="block-content" name="content" value="${safeContentValue}" required>
                    <div class="form-text">Use the rich text editor above to format your content.</div>
                    <small class="text-muted">Content is pre-loaded. Editor will initialize shortly.</small>
                </div>
            `;
            break;
            
        case 'image':
        case 'pdf':
        case 'audio':
        case 'video':
            const allowedExtensions = typeConfig.allowed_extensions || [];
            const maxSize = typeConfig.max_file_size || 10485760;
            
            formHtml += `
                <div class="mb-3">
                    <label class="form-label">Current File</label>
                    <div class="alert alert-info">
                        <i class="fas fa-file me-2"></i>
                        ${block.file_name || 'No file'}
                        ${block.formatted_file_size ? `(${block.formatted_file_size})` : ''}
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="block-file" class="form-label">Replace File (Optional)</label>
                    <div class="file-drop-zone" onclick="document.getElementById('block-file').click()">
                        <input type="file" class="file-input-hidden" id="block-file" name="file" 
                               onclick="event.stopPropagation()"
                               accept="${allowedExtensions.length > 0 ? allowedExtensions.map(ext => '.' + ext).join(',') : '*/*'}">
                        
                        <div class="file-drop-zone-content">
                            <div class="file-drop-zone-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-drop-zone-text">
                                Click to select a new file or drag and drop
                            </div>
                            <div class="file-drop-zone-subtext">
                                Allowed: ${allowedExtensions.length > 0 ? allowedExtensions.map(ext => `.${ext}`).join(', ') : 'All files'} 
                                (Max: ${formatFileSize(maxSize)})
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (typeConfig.supports_external_url) {
                formHtml += `
                    <div class="mb-3">
                        <label for="block-external-url" class="form-label">Or External URL</label>
                        <input type="url" class="form-control" id="block-external-url" name="external_url" 
                               value="${escapeHtml(block.external_url || '')}"
                               placeholder="https://example.com/file.jpg">
                    </div>
                `;
            }
            
            if (block.type === 'image') {
                formHtml += `
                    <div class="mb-3">
                        <label for="block-alt-text" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="block-alt-text" name="alt_text" 
                               value="${escapeHtml(block.alt_text || '')}"
                               placeholder="Describe the image for accessibility">
                    </div>
                `;
            }
            break;
    }
    
    formHtml += `
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="block-section" class="form-label">Section</label>
                    <select class="form-select" id="block-section" name="section" required>
    `;
    
    sections.forEach(s => {
        const selected = s.value === block.section ? 'selected' : '';
        formHtml += `<option value="${s.value}" ${selected}>${s.label}</option>`;
    });
    
    formHtml += `
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="block-visibility" class="form-label">Visibility</label>
                    <select class="form-select" id="block-visibility" name="visibility" required>
    `;
    
    visibilityOptions.forEach(v => {
        const selected = v.value === block.visibility ? 'selected' : '';
        formHtml += `<option value="${v.value}" ${selected}>${v.label}</option>`;
    });
    
    formHtml += `
                    </select>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="block-is-active" name="is_active" 
                               value="1" ${block.is_active ? 'checked' : ''}>
                        <label class="form-check-label" for="block-is-active">
                            Published
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="type" value="${block.type}">
    `;
    
    return formHtml;
}

async function initializeEditorJSForEdit(content) {
    try {
        console.log('Initializing EditorJS for edit with content:', content);
        
        // Wait for EditorManager to be available
        let attempts = 0;
        while (typeof window.EditorManager === 'undefined' && attempts < 50) {
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }
        
        if (typeof window.EditorManager === 'undefined') {
            console.error('EditorManager not found!');
            return;
        }
        
        // Parse existing content
        let parsedContent = null;
        if (content) {
            try {
                parsedContent = typeof content === 'string' ? JSON.parse(content) : content;
            } catch (e) {
                console.error('Failed to parse content:', e);
            }
        }
        
        // Create editor instance with existing content
        currentEditorInstance = new window.EditorManager('editorjs-holder', {
            placeholder: 'Start writing your content here...',
            minHeight: 300,
            data: parsedContent
        });
        
        await currentEditorInstance.init();
        console.log('✓ EditorJS initialized for edit');
        
    } catch (error) {
        console.error('Failed to initialize EditorJS for edit:', error);
    }
}

async function showVersionHistory(contentId) {
    console.log('Show version history for content block:', contentId);
    
    try {
        // Fetch version history
        const response = await fetch(`/api/v1/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/{{ $subpage->id }}/content-blocks/${contentId}/versions`);
        const result = await response.json();
        
        if (!result.success) {
            showAlert('danger', result.message || 'Failed to load version history.');
            return;
        }
        
        const versions = result.data.versions;
        const contentBlock = result.data.content_block;
        
        // Create version history modal content
        let modalContent = `
            <div class="mb-3">
                <h6>Content Block: ${escapeHtml(contentBlock.title || 'Untitled')}</h6>
                <p class="text-muted small">Type: ${contentBlock.type}</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Action</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        if (versions.length === 0) {
            modalContent += `
                <tr>
                    <td colspan="5" class="text-center text-muted">No version history available</td>
                </tr>
            `;
        } else {
            versions.forEach(version => {
                const date = new Date(version.created_at).toLocaleString();
                const userName = version.creator ? version.creator.name : 'Unknown';
                
                modalContent += `
                    <tr>
                        <td><span class="badge bg-primary">v${version.version_number}</span></td>
                        <td>${version.action_type}</td>
                        <td>${date}</td>
                        <td>${escapeHtml(userName)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="restoreVersion(${contentId}, ${version.version_number})">
                                <i class="fas fa-undo"></i> Restore
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        
        modalContent += `
                    </tbody>
                </table>
            </div>
        `;
        
        // Show in a modal
        showInfoModal('Version History', modalContent);
        
    } catch (error) {
        console.error('Error loading version history:', error);
        showAlert('danger', 'Failed to load version history: ' + error.message);
    }
}

async function restoreVersion(contentId, versionNumber) {
    if (!confirm(`Are you sure you want to restore to version ${versionNumber}? This will create a new version with the restored content.`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/v1/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/{{ $subpage->id }}/content-blocks/${contentId}/restore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ version_number: versionNumber })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', 'Version restored successfully!');
            // Reload the page to show updated content
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('danger', result.message || 'Failed to restore version.');
        }
    } catch (error) {
        console.error('Error restoring version:', error);
        showAlert('danger', 'Failed to restore version: ' + error.message);
    }
}

async function duplicateContentBlock(contentId) {
    console.log('Duplicate content block:', contentId);
    
    if (!confirm('Are you sure you want to duplicate this content block?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/v1/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/{{ $subpage->id }}/content-blocks/${contentId}/duplicate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', 'Content block duplicated successfully!');
            // Reload the page to show the duplicated block
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showAlert('danger', result.message || 'Failed to duplicate content block.');
        }
    } catch (error) {
        console.error('Error duplicating content block:', error);
        showAlert('danger', 'Failed to duplicate content block: ' + error.message);
    }
}

// Helper function to show info modal
function showInfoModal(title, content) {
    // Create or get modal
    let modal = document.getElementById('infoModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'infoModal';
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="infoModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="infoModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    document.getElementById('infoModalLabel').textContent = title;
    document.getElementById('infoModalBody').innerHTML = content;
    
    const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
    bsModal.show();
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// STEP 12: UTILITY FUNCTIONS
function updateSectionBlockCount(section) {
    if (!section) return;
    
    const badge = section.querySelector('.section-header .badge');
    const blocks = section.querySelectorAll('.content-block');
    
    if (badge) {
        badge.textContent = blocks.length;
    }
}

// Enhanced utility functions for upload progress and status indicators

/**
 * Get appropriate icon and color for file type
 * @param {string} mimeType - File MIME type
 * @param {string} extension - File extension
 * @returns {Object} Icon class and color
 */
function getFileTypeIcon(mimeType, extension) {
    const ext = extension.toLowerCase();
    
    // Image files
    if (mimeType.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(ext)) {
        return { icon: 'fas fa-image', color: 'text-success' };
    }
    
    // PDF files
    if (mimeType === 'application/pdf' || ext === 'pdf') {
        return { icon: 'fas fa-file-pdf', color: 'text-danger' };
    }
    
    // Audio files
    if (mimeType.startsWith('audio/') || ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a'].includes(ext)) {
        return { icon: 'fas fa-file-audio', color: 'text-info' };
    }
    
    // Video files
    if (mimeType.startsWith('video/') || ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'].includes(ext)) {
        return { icon: 'fas fa-file-video', color: 'text-primary' };
    }
    
    // Document files
    if (['doc', 'docx'].includes(ext)) {
        return { icon: 'fas fa-file-word', color: 'text-primary' };
    }
    if (['xls', 'xlsx'].includes(ext)) {
        return { icon: 'fas fa-file-excel', color: 'text-success' };
    }
    if (['ppt', 'pptx'].includes(ext)) {
        return { icon: 'fas fa-file-powerpoint', color: 'text-warning' };
    }
    
    // Archive files
    if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) {
        return { icon: 'fas fa-file-archive', color: 'text-secondary' };
    }
    
    // Text files
    if (mimeType.startsWith('text/') || ['txt', 'rtf'].includes(ext)) {
        return { icon: 'fas fa-file-alt', color: 'text-muted' };
    }
    
    // Default file icon
    return { icon: 'fas fa-file', color: 'text-muted' };
}

/**
 * Get progress bar class based on size percentage
 * @param {number} percentage - Size percentage
 * @returns {string} Bootstrap progress bar class
 */
function getSizeProgressBarClass(percentage) {
    if (percentage > 95) return 'bg-danger';
    if (percentage > 90) return 'bg-warning';
    if (percentage > 70) return 'bg-info';
    return 'bg-success';
}

/**
 * Update upload progress indicator
 * @param {HTMLElement} statusElement - Upload status element
 * @param {number} percentage - Upload percentage (0-100)
 * @param {Object} options - Additional options
 */
function updateUploadProgress(statusElement, percentage, options = {}) {
    if (!statusElement) return;
    
    const progressBar = statusElement.querySelector('.progress-bar');
    const percentageText = statusElement.querySelector('.upload-percentage');
    const speedText = statusElement.querySelector('.upload-speed');
    
    if (progressBar) {
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
    }
    
    if (percentageText) {
        percentageText.textContent = `${Math.round(percentage)}%`;
    }
    
    if (speedText && options.speed) {
        speedText.textContent = `Upload speed: ${options.speed}`;
    }
    
    if (options.timeRemaining && speedText) {
        speedText.innerHTML += ` • ETA: ${options.timeRemaining}`;
    }
}

/**
 * Show upload status message
 * @param {HTMLElement} statusElement - Upload status element
 * @param {string} status - Status type (preparing, uploading, processing, success, error)
 * @param {string} message - Optional custom message
 */
function showUploadStatus(statusElement, status, message = '') {
    if (!statusElement) return;
    
    // Hide all status messages
    const statusMessages = statusElement.querySelectorAll('[class*="status-"]');
    statusMessages.forEach(msg => msg.classList.add('d-none'));
    
    // Show the requested status
    const targetStatus = statusElement.querySelector(`.status-${status}`);
    if (targetStatus) {
        targetStatus.classList.remove('d-none');
        
        // Update custom message if provided
        if (message && status === 'error') {
            const errorMessage = targetStatus.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.textContent = message;
            }
        }
    }
}

/**
 * Calculate upload speed and time remaining
 * @param {number} bytesUploaded - Bytes uploaded so far
 * @param {number} totalBytes - Total file size
 * @param {number} startTime - Upload start timestamp
 * @returns {Object} Speed and time remaining information
 */
function calculateUploadMetrics(bytesUploaded, totalBytes, startTime) {
    const currentTime = Date.now();
    const elapsedTime = (currentTime - startTime) / 1000; // seconds
    
    if (elapsedTime === 0) {
        return { speed: '0 B/s', timeRemaining: 'Calculating...' };
    }
    
    const bytesPerSecond = bytesUploaded / elapsedTime;
    const remainingBytes = totalBytes - bytesUploaded;
    const remainingTime = remainingBytes / bytesPerSecond; // seconds
    
    return {
        speed: formatFileSize(bytesPerSecond) + '/s',
        timeRemaining: formatTime(remainingTime),
        bytesPerSecond
    };
}

/**
 * Format time duration in human-readable format
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time string
 */
function formatTime(seconds) {
    if (seconds < 60) {
        return `${Math.round(seconds)}s`;
    } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.round(seconds % 60);
        return `${minutes}m ${remainingSeconds}s`;
    } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }
}
</script>

@vite(['resources/js/editor/index.js'])

<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        // Move modals to body to prevent nested scroll issues
        document.querySelectorAll('.modal').forEach(modal => {
            document.body.appendChild(modal);
        });

        console.log('Checking for PageBuilder...');
        
        const initBuilder = () => {
            if (window.pageBuilderInstance) {
                console.warn('[PageBuilder] Instance already exists. Skipping re-initialization.');
                return;
            }

            if (window.PageBuilder) {
                console.log('[PageBuilder] Initializing primary instance...');
                const builder = new window.PageBuilder('page-builder-holder', {
                    initialData: @json(collect($contentBlocks)), 
                    saveLayoutEndpoint: '{{ route('api.content-blocks.update-layout', ['course' => $course->id, 'module' => $module->id, 'subpage' => $subpage->id]) }}',
                    saveContentBaseUrl: '{{ route('api.content-blocks.store', ['course' => $course->id, 'module' => $module->id, 'subpage' => $subpage->id]) }}',
                    csrfToken: '{{ csrf_token() }}',
                    onEditBlock: (id) => {
                        if (window.editContentBlock) window.editContentBlock(id);
                    },
                    onDeleteBlock: (id) => {
                        if (window.deleteContentBlock) window.deleteContentBlock(id);
                    },
                    onLayoutSaved: () => {
                       console.log('Layout saved automatically');
                    },
                    readOnly: false
                });
                window.pageBuilderInstance = builder;
                console.log('[PageBuilder] window.pageBuilderInstance set successfully.');

                // Bind Save All Button
                const saveBtn = document.getElementById('save-all-changes');
                if (saveBtn) {
                    // Remove existing listeners to avoid duplicates if re-initialized
                    const newBtn = saveBtn.cloneNode(true);
                    saveBtn.parentNode.replaceChild(newBtn, saveBtn);
                    
                    newBtn.addEventListener('click', async () => {
                        const originalText = newBtn.innerHTML;
                        newBtn.disabled = true;
                        newBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
                        
                        try {
                            await builder.saveLayout();
                            newBtn.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
                            newBtn.classList.remove('btn-light');
                            newBtn.classList.add('btn-success', 'text-white');
                            
                            setTimeout(() => {
                                newBtn.disabled = false;
                                newBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save All';
                                newBtn.classList.remove('btn-success', 'text-white');
                                newBtn.classList.add('btn-light');
                            }, 2000);
                        } catch (err) {
                            console.error(err);
                            newBtn.disabled = false;
                            newBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Error';
                            newBtn.classList.add('btn-danger', 'text-white');
                        }
                    });
                }
            } else {
                console.warn('PageBuilder class not found. Waiting...');
                setTimeout(() => {
                    if (window.PageBuilder && !window.pageBuilderInstance) initBuilder();
                }, 500);
            }
        };

        // Phase 5: Helper for Content Picker Modal
    window.addBlockType = async function(type, data = {}, settings = {}) {
        if (!window.pageBuilderInstance) return;
        
        const btn = event?.currentTarget;
        if (btn) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }

        try {
            await window.pageBuilderInstance.addBlock(type, data, settings);
            
            // Close Modal
            const modalEl = document.getElementById('contentTypeModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalHtml || 'Add';
            }
        }
    };

    // Delay slightly to ensure modules load
        setTimeout(initBuilder, 100);
    });
</script>
@endpush

@push('scripts')
@vite(['resources/js/editor/index.js'])
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="{{ asset('js/FileUploadValidator.js') }}"></script>
<script>
    // Enforce "Settings First" workflow via safeAddBlock
    window.safeAddBlock = async function(type, data = {}, settings = {}) {
        console.log('safeAddBlock called:', type);
        
        if (!window.pageBuilderInstance) {
            console.error('PageBuilder instance not found');
            return;
        }
        
        try {
            // Close the type selection modal
            const modalEl = document.getElementById('contentTypeModal');
            if (modalEl) {
                 const bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                 bsModal.hide();
            }

            // Default section if not provided
            const section = 'main_content'; // Use default

            // Add block with _openSettings flag
            // Note: data and settings args from button click (like youtube start time) are passed through
            await window.pageBuilderInstance.addBlock(type, data, { 
                ...settings,
                section: section, 
                _openSettings: true 
            });
            
        } catch (e) {
            console.error('Error adding block via new workflow:', e);
            alert('Failed to add block: ' + e.message);
        }
    };

    // --- UI Button Handlers & Event Delegation ---
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Final UI initialization running...');
        
        // 1. Add Content Block Button - Global Delegation
        document.addEventListener('click', function(e) {
            const addBtn = e.target.closest('[data-add-content]');
            if (addBtn) {
                e.preventDefault();
                console.log('✓ Add Content Block button clicked:', addBtn.dataset.section);
                window.currentContentSection = addBtn.dataset.section || 'main_content';
                openContentTypeModal();
            }
        });

        // 2. Save Content Block Button (Inside Edit Modal)
        const saveButton = document.getElementById('save-content-block');
        if (saveButton) {
            saveButton.onclick = function(e) {
                e.preventDefault();
                console.log('Save content block button clicked (primary)');
                if (typeof window.saveContentBlock === 'function') {
                    window.saveContentBlock();
                }
            };
        }

        // 3. View Trash Modal
        const viewTrashBtn = document.getElementById('view-trash');
        if (viewTrashBtn) {
            viewTrashBtn.addEventListener('click', function() {
                const modalEl = document.getElementById('trashModal');
                if (modalEl) {
                    const trashModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    trashModal.show();
                }
            });
        }
    });

    // --- Trash Management Utility Functions (Global) ---

    // Restore Deleted Block
    window.restoreBlock = function(blockId) {
        if (!confirm('Are you sure you want to restore this content block?')) return;
        
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Restoring...';

        fetch(`/api/v1/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/{{ $subpage->id }}/content-blocks/${blockId}/restore`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showAlert === 'function') {
                    showAlert('success', 'Content block restored successfully!');
                } else {
                    alert('Content block restored successfully!');
                }
                setTimeout(() => window.location.reload(), 1000);
            } else {
                if (typeof showAlert === 'function') {
                    showAlert('danger', 'Failed to restore block: ' + (data.message || 'Unknown error'));
                } else {
                    alert('Failed to restore block: ' + (data.message || 'Unknown error'));
                }
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(err => {
            console.error('Error restoring block:', err);
            if (typeof showAlert === 'function') {
                showAlert('danger', 'An error occurred while restoring the block.');
            } else {
                alert('An error occurred while restoring the block.');
            }
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    };

    // Permanently Delete Block
    window.permanentlyDeleteBlock = function(blockId, blockTitle) {
        if (!confirm(`WARNING: This will permanently delete "${blockTitle}" and all its associated files. This action CANNOT be undone.\n\nAre you absolutely sure?`)) {
            return;
        }

        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch(`/api/v1/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/{{ $subpage->id }}/content-blocks/${blockId}/force-delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`.trashed-block-card[data-id="${blockId}"]`)?.remove();
                if (typeof showAlert === 'function') {
                    showAlert('success', 'Block permanently deleted.');
                } else {
                    alert('Block permanently deleted.');
                }
                
                // Check if trash is now empty
                const container = document.getElementById('trash-blocks-container');
                if (container && container.querySelectorAll('.trashed-block-card').length === 0) {
                    container.innerHTML = `
                        <div id="empty-trash-message" class="text-center py-5">
                            <i class="fas fa-trash-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Trash is Empty</h5>
                            <p class="text-muted">No content blocks have been deleted yet.</p>
                        </div>
                    `;
                }
            } else {
                if (typeof showAlert === 'function') {
                    showAlert('danger', 'Failed to delete: ' + (data.message || 'Unknown error'));
                } else {
                    alert('Failed to delete: ' + (data.message || 'Unknown error'));
                }
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(err => {
            console.error('Error permanently deleting block:', err);
            if (typeof showAlert === 'function') {
                showAlert('danger', 'An error occurred during permanent deletion.');
            } else {
                alert('An error occurred during permanent deletion.');
            }
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    };

    // Empty All Content Trash
    window.emptyContentTrash = function() {
        if (!confirm('Permanently delete all items in trash? This action cannot be undone.')) {
            return;
        }

        const btn = document.getElementById('empty-content-trash-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
        }

        fetch(`/api/v1/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/{{ $subpage->id }}/content-blocks/empty-trash`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all trash cards
                document.querySelectorAll('.trashed-block-card').forEach(el => el.remove());

                // Show empty message
                const container = document.getElementById('trash-blocks-container');
                if (container) {
                    container.innerHTML = `
                        <div id="empty-trash-message" class="text-center py-5">
                            <i class="fas fa-trash-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Trash is Empty</h5>
                            <p class="text-muted">No content blocks have been deleted yet.</p>
                        </div>
                    `;
                }

                // Hide the Delete All button
                if (btn) btn.style.display = 'none';

                if (typeof showAlert === 'function') {
                    showAlert('success', 'Trash emptied successfully.');
                } else {
                    alert('Trash emptied successfully.');
                }
            } else {
                if (typeof showAlert === 'function') {
                    showAlert('danger', 'Failed: ' + (data.message || 'Unknown error'));
                } else {
                    alert('Failed: ' + (data.message || 'Unknown error'));
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete All';
                }
            }
        })
        .catch(err => {
            console.error('Error emptying trash:', err);
            if (typeof showAlert === 'function') {
                showAlert('danger', 'An error occurred while emptying trash.');
            } else {
                alert('An error occurred while emptying trash.');
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete All';
            }
        });
    };
</script>
@endpush

@push('styles')
<style>
    /* --- FIXED INTERACTION MODEL (Step 2588) --- */
    /* 1. Default: Drop slots are invisible to clicks */
    .column-drop-slot {
        pointer-events: none !important;
        z-index: 1; /* Below content */
    }

    /* 2. Drag Mode: Drop slots become interactive */
    body.is-pointer-dragging .column-drop-slot {
        pointer-events: auto !important;
        z-index: 9000; /* Above content during drag */
    }

    /* 3. Content stays clickable in normal mode */
    .page-block {
        position: relative;
        z-index: 10; /* Above slots */
    }

    /* 4. Drag Ghost high z-index */
    .pointer-drag-ghost {
        z-index: 9999 !important;
        pointer-events: none !important;
    }

    /* 5. Resize handles shouldn't block content clicks unless active */
    .resize-handle {
        z-index: 20; 
    }
</style>
@endpush
