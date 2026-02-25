@extends('layouts.app')

@section('title', 'Subpages - ' . $module->title)

@php
    $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
@endphp

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route($routePrefix . '.courses.index') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                    <i class="fas fa-book"></i> Courses
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route($routePrefix . '.courses.show', $course) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                    {{ $course->title }}
                </a>
            </li>
            <li class="breadcrumb-item active" style="color: #764ba2;">{{ $module->title }} - Subpages</li>
        </ol>
    </nav>
    <style>
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #667eea;
        }
        .breadcrumb-item a:hover {
            color: #764ba2 !important;
        }
    </style>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-2 fw-bold" style="color: #1a1a2e;">
                <i class="fas fa-file-alt me-2" style="color: #6366f1;"></i>
                {{ $module->title }} Subpages
            </h1>
            <p class="text-muted mb-0">
                Module Type: <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px;">{{ ucfirst(str_replace('_', ' ', $module->type)) }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route($routePrefix . '.courses.modules.subpages.create', [$course, $module]) }}" 
               class="btn btn-primary shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 500;">
                <i class="fas fa-plus me-2"></i>Add Subpage
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-dismissible fade show" role="alert" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: none; border-left: 4px solid #28a745; border-radius: 8px; box-shadow: 0 2px 8px rgba(40, 167, 69, 0.1);">
            <i class="fas fa-check-circle me-2" style="color: #28a745;"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Subpages List -->
    <div class="card shadow-sm" style="border: none; border-radius: 12px; overflow: hidden;">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 1.25rem 1.5rem;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white fw-bold">
                    <i class="fas fa-layer-group me-2"></i>
                    Subpages ({{ $subpages->count() }})
                </h5>
                @if($subpages->count() > 1)
                    <button class="btn btn-sm btn-light shadow-sm" id="reorderBtn" style="border-radius: 6px; font-weight: 500;">
                        <i class="fas fa-sort me-1"></i>Reorder
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($subpages->count() > 0)
                <div id="subpagesList" class="list-group list-group-flush">
                    @foreach($subpages as $subpage)
                        <div class="list-group-item subpage-item" data-id="{{ $subpage->id }}" style="border-left: none; border-right: none; padding: 1.25rem 1.5rem; transition: all 0.3s ease;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="drag-handle me-2" style="cursor: move; display: none; color: #6c757d;">
                                            <i class="fas fa-grip-vertical"></i>
                                        </span>
                                        <h6 class="mb-0 fw-bold" style="color: #1a1a2e;">
                                            <a href="{{ route($routePrefix . '.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                                               class="text-decoration-none" style="color: #1a1a2e;">
                                                {{ $subpage->title }}
                                            </a>
                                        </h6>
                                        <span class="ms-2">
                                            @if($subpage->is_active)
                                                <span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 500;">Active</span>
                                            @else
                                                <span class="badge bg-secondary" style="padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 500;">Inactive</span>
                                            @endif
                                            @if($subpage->trashed())
                                                <span class="badge bg-danger" style="padding: 0.25rem 0.6rem; border-radius: 6px; font-weight: 500;">Deleted</span>
                                            @endif
                                        </span>
                                    </div>
                                    @if($subpage->description)
                                        <p class="text-muted mb-2 small">{{ Str::limit($subpage->description, 100) }}</p>
                                    @endif
                                    <div class="small text-muted">
                                        <i class="fas fa-file-alt me-1" style="color: #6366f1;"></i>{{ $subpage->contents->count() }} content items
                                        <span class="ms-3">
                                            <i class="fas fa-clock me-1" style="color: #6366f1;"></i>Updated {{ $subpage->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="btn-group" role="group">
                                    @if(!$subpage->trashed())
                                        <a href="{{ route($routePrefix . '.courses.modules.subpages.content-builder', [$course, $module, $subpage]) }}" 
                                           class="btn btn-sm shadow-sm" title="Edit Content" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px 0 0 6px; padding: 0.4rem 0.75rem;">
                                            <i class="fas fa-puzzle-piece"></i>
                                        </a>
                                        <a href="{{ route($routePrefix . '.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Preview" style="border-color: #667eea; color: #667eea; border-radius: 0; padding: 0.4rem 0.75rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route($routePrefix . '.courses.modules.subpages.edit', [$course, $module, $subpage]) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Settings" style="border-radius: 0; padding: 0.4rem 0.75rem;">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-warning toggle-active-btn" 
                                                data-id="{{ $subpage->id }}" 
                                                data-active="{{ $subpage->is_active ? 'true' : 'false' }}"
                                                title="{{ $subpage->is_active ? 'Deactivate' : 'Activate' }}" style="border-radius: 0; padding: 0.4rem 0.75rem;">
                                            <i class="fas fa-{{ $subpage->is_active ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" 
                                                data-id="{{ $subpage->id }}"
                                                title="Delete" style="border-radius: 0 6px 6px 0; padding: 0.4rem 0.75rem;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <form method="POST" 
                                              action="{{ route($routePrefix . '.courses.modules.subpages.restore', [$course, $module, $subpage->id]) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success shadow-sm" style="border-radius: 6px; padding: 0.4rem 0.75rem;">
                                                <i class="fas fa-undo me-1"></i>Restore
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5" style="padding: 3rem 1.5rem;">
                    <div class="mb-4" style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-alt fa-2x text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="color: #1a1a2e;">No subpages yet</h5>
                    <p class="text-muted mb-4">Create your first subpage to organize your module content.</p>
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.create', [$course, $module]) }}" 
                       class="btn btn-primary shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 0.6rem 1.5rem; border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-plus me-2"></i>Create First Subpage
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <p class="mb-0" style="color: #1a1a2e;">Are you sure you want to delete this subpage? This action can be undone later.</p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 1rem 1.5rem;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 6px; padding: 0.5rem 1.25rem;">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger shadow-sm" style="border-radius: 6px; padding: 0.5rem 1.25rem; font-weight: 500;">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const routePrefix = '{{ $routePrefix }}';
    let isReorderMode = false;
    const reorderBtn = document.getElementById('reorderBtn');
    const subpagesList = document.getElementById('subpagesList');
    let sortable = null;

    // Reorder functionality
    if (reorderBtn) {
        reorderBtn.addEventListener('click', function() {
            isReorderMode = !isReorderMode;
            
            if (isReorderMode) {
                // Enable reorder mode
                reorderBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Order';
                reorderBtn.classList.remove('btn-light');
                reorderBtn.classList.add('btn-success');
                
                // Show drag handles
                document.querySelectorAll('.drag-handle').forEach(handle => {
                    handle.style.display = 'inline-block';
                });
                
                // Initialize sortable
                sortable = Sortable.create(subpagesList, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'sortable-ghost'
                });
            } else {
                // Save order and exit reorder mode
                const subpageIds = Array.from(subpagesList.children).map(item => item.dataset.id);
                
                fetch(`/${routePrefix}/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ subpage_ids: subpageIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to save order');
                    }
                });
            }
        });
    }

    // Toggle active status
    document.querySelectorAll('.toggle-active-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const subpageId = this.dataset.id;
            const isActive = this.dataset.active === 'true';
            
            fetch(`/${routePrefix}/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/${subpageId}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });

    // Delete functionality
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const subpageId = this.dataset.id;
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/${routePrefix}/courses/{{ $course->id }}/modules/{{ $module->id }}/subpages/${subpageId}`;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
});
</script>

<style>
.sortable-ghost {
    opacity: 0.4;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.subpage-item {
    transition: all 0.3s ease;
    border-bottom: 1px solid #e9ecef !important;
}

.subpage-item:last-child {
    border-bottom: none !important;
}

.subpage-item:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%);
    transform: translateX(4px);
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}


</style>
@endpush