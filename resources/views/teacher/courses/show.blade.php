@extends('layouts.app')

@section('title', $courseWithModules->title . ' - Teacher')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center responsive-card-list-item">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('teacher.courses.index') }}">Courses</a>
                                </li>
                                <li class="breadcrumb-item active text-truncate" style="max-width: 200px;">{{ $courseWithModules->title }}</li>
                            </ol>
                        </nav>
                        <h1 class="mb-0"><i class="fas fa-book me-2"></i>{{ $courseWithModules->title }}</h1>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            @if($courseWithModules->is_published)
                                <span class="creative-badge creative-badge-success creative-badge-sm">Published</span>
                            @else
                                <span class="creative-badge creative-badge-secondary creative-badge-sm">Draft</span>
                            @endif
                            @if($courseWithModules->is_featured)
                                <span class="creative-badge creative-badge-warning creative-badge-sm">Featured</span>
                            @endif
                            @if($courseWithModules->category)
                                <span class="creative-badge creative-badge-info creative-badge-sm">{{ $courseWithModules->category }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                        <button type="button" class="creative-btn creative-btn-primary" id="assign-students-btn">
                            <i class="fas fa-user-plus me-1"></i>Assign
                        </button>
                        <a href="{{ route('teacher.courses.edit', $courseWithModules) }}" class="creative-btn creative-btn-outline-secondary">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('teacher.courses.index') }}" class="creative-btn creative-btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Course Content -->
        <div class="col-lg-8">
            <!-- Course Information -->
            <div class="creative-card mb-4">
                <div class="creative-card-header">
                    <h3 class="mb-0"><i class="fas fa-info-circle me-2"></i>Course Information</h3>
                </div>
                <div class="creative-card-body">
                            @if($courseWithModules->description)
                                <div class="mb-3">
                                    <h6>Description</h6>
                                    <p class="text-muted">{{ $courseWithModules->description }}</p>
                                </div>
                            @endif

                            <div class="row">
                                @if($courseWithModules->difficulty_level)
                                <div class="col-md-6">
                                    <h6>Difficulty Level</h6>
                                    <p class="mb-3">
                                        <span class="badge bg-{{ $courseWithModules->difficulty_level === 'beginner' ? 'success' : ($courseWithModules->difficulty_level === 'intermediate' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($courseWithModules->difficulty_level) }}
                                        </span>
                                    </p>
                                </div>
                                @endif
                                @if($courseWithModules->duration_hours)
                                <div class="col-md-6">
                                    <h6>Duration</h6>
                                    <p class="mb-3">{{ $courseWithModules->duration_hours }} hours</p>
                                </div>
                                @endif
                            </div>

                            <div class="row">
                                @if($courseWithModules->max_students)
                                <div class="col-md-6">
                                    <h6>Maximum Students</h6>
                                    <p class="mb-3">{{ $courseWithModules->max_students }} students</p>
                                </div>
                                @endif
                                <div class="col-md-6">
                                    <h6>Current Enrollments</h6>
                                    <p class="mb-3">{{ $courseWithModules->enrollments_count ?? 0 }} students</p>
                                </div>
                            </div>

                            <!-- Dates -->
                            @if($courseWithModules->enrollment_start_date || $courseWithModules->enrollment_end_date || $courseWithModules->course_start_date || $courseWithModules->course_end_date)
                            <div class="row">
                                @if($courseWithModules->enrollment_start_date)
                                <div class="col-md-6">
                                    <h6>Enrollment Period</h6>
                                    <p class="mb-3">
                                        {{ $courseWithModules->enrollment_start_date->format('M j, Y') }}
                                        @if($courseWithModules->enrollment_end_date)
                                            - {{ $courseWithModules->enrollment_end_date->format('M j, Y') }}
                                        @endif
                                    </p>
                                </div>
                                @endif
                                @if($courseWithModules->course_start_date)
                                <div class="col-md-6">
                                    <h6>Course Period</h6>
                                    <p class="mb-3">
                                        {{ $courseWithModules->course_start_date->format('M j, Y') }}
                                        @if($courseWithModules->course_end_date)
                                            - {{ $courseWithModules->course_end_date->format('M j, Y') }}
                                        @endif
                                    </p>
                                </div>
                                @endif
                            </div>
                            @endif
                </div>
            </div>

            <!-- Course Modules -->
            <div class="creative-card mb-4">
                <div class="creative-card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="fas fa-book-open me-2"></i>Course Modules</h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="creative-btn creative-btn-warning creative-btn-sm" id="view-module-trash">
                            <i class="fas fa-trash-restore me-1"></i>Trash ({{ $trashedModules->count() }})
                        </button>
                        <a href="{{ route('teacher.modules.create', $courseWithModules) }}" class="creative-btn creative-btn-primary creative-btn-sm">
                            <i class="fas fa-plus me-1"></i>Add Module
                        </a>
                    </div>
                </div>
                <div class="creative-card-body">
                    @if($courseWithModules->modules && $courseWithModules->modules->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($courseWithModules->modules->sortBy('order') as $module)
                            <div class="list-group-item responsive-card-list-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <h5 class="mb-0">
                                                <a href="{{ route('teacher.modules.show', [$courseWithModules, $module]) }}" class="text-decoration-none">
                                                    {{ $module->title }}
                                                </a>
                                            </h5>
                                            <div class="d-flex gap-1">
                                                @if($module->is_published)
                                                    <span class="creative-badge creative-badge-success creative-badge-sm">Published</span>
                                                @else
                                                    <span class="creative-badge creative-badge-secondary creative-badge-sm">Draft</span>
                                                @endif
                                                @if($module->is_required)
                                                    <span class="creative-badge creative-badge-warning creative-badge-sm">Required</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($module->description)
                                            <p class="mb-2 text-muted small">{{ Str::limit($module->description, 120) }}</p>
                                        @endif
                                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                                            <span><i class="fas fa-sort-numeric-up me-1"></i>{{ $module->order }}</span>
                                            @if($module->duration_minutes)
                                                <span><i class="fas fa-clock me-1"></i>{{ $module->duration_minutes }}m</span>
                                            @endif
                                            @if($module->subpages)
                                                <span><i class="fas fa-file-alt me-1"></i>{{ $module->subpages->count() }} pages</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end ms-md-3">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('teacher.modules.show', [$courseWithModules, $module]) }}" 
                                               class="creative-btn creative-btn-outline-primary creative-btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('teacher.modules.edit', [$courseWithModules, $module]) }}" 
                                               class="creative-btn creative-btn-outline-secondary creative-btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('teacher.courses.modules.subpages.index', [$courseWithModules, $module]) }}" 
                                               class="creative-btn creative-btn-outline-info creative-btn-sm" title="Pages">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                            <form method="POST" action="{{ route('teacher.modules.destroy', [$courseWithModules, $module]) }}" 
                                                  class="d-inline" onsubmit="return confirm('Delete this module?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="creative-btn creative-btn-outline-danger creative-btn-sm" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No modules created yet</h5>
                            <p class="text-muted">Start building your course by adding modules.</p>
                            <a href="{{ route('teacher.modules.create', $courseWithModules) }}" class="creative-btn creative-btn-primary">
                                <i class="fas fa-plus me-2"></i>Create First Module
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Course Statistics -->
            <div class="creative-card mb-4">
                <div class="creative-card-header">
                    <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Course Statistics</h4>
                </div>
                <div class="creative-card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="creative-stat-card">
                                <div class="creative-stat-value text-primary">{{ $courseWithModules->enrollments_count ?? 0 }}</div>
                                <div class="creative-stat-label">Enrolled</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="creative-stat-card">
                                <div class="creative-stat-value text-info">{{ $courseWithModules->modules ? $courseWithModules->modules->count() : 0 }}</div>
                                <div class="creative-stat-label">Modules</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="creative-stat-card">
                                <div class="creative-stat-value text-success">{{ $courseWithModules->modules ? $courseWithModules->modules->where('is_published', true)->count() : 0 }}</div>
                                <div class="creative-stat-label">Published</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="creative-stat-card">
                                <div class="creative-stat-value text-warning">{{ $courseWithModules->modules ? $courseWithModules->modules->where('is_published', false)->count() : 0 }}</div>
                                <div class="creative-stat-label">Drafts</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Metadata -->
            <div class="creative-card mb-4">
                <div class="creative-card-header">
                    <h4 class="mb-0"><i class="fas fa-calendar me-2"></i>Course Details</h4>
                </div>
                <div class="creative-card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block"><i class="fas fa-plus-circle me-1"></i>Created</small>
                        <span>{{ $courseWithModules->created_at->format('M j, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block"><i class="fas fa-edit me-1"></i>Last Updated</small>
                        <span>{{ $courseWithModules->updated_at->format('M j, Y \a\t g:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="creative-card">
                <div class="creative-card-header">
                    <h4 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h4>
                </div>
                <div class="creative-card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="creative-btn creative-btn-primary w-100" id="assign-students-btn-sidebar">
                            <i class="fas fa-user-plus me-2"></i>Assign Students
                        </button>
                        <a href="{{ route('teacher.modules.create', $courseWithModules) }}" class="creative-btn creative-btn-outline w-100">
                            <i class="fas fa-plus me-2"></i>Add Module
                        </a>
                        <a href="{{ route('teacher.courses.edit', $courseWithModules) }}" class="creative-btn creative-btn-outline w-100">
                            <i class="fas fa-edit me-2"></i>Edit Course
                        </a>
                        @if($courseWithModules->is_published)
                            <form method="POST" action="{{ route('teacher.courses.unpublish', $courseWithModules) }}">
                                @csrf
                                <button type="submit" class="creative-btn creative-btn-outline w-100">
                                    <i class="fas fa-eye-slash me-2"></i>Unpublish Course
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('teacher.courses.publish', $courseWithModules) }}">
                                @csrf
                                <button type="submit" class="creative-btn creative-btn-primary w-100">
                                    <i class="fas fa-eye me-2"></i>Publish Course
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Modal Container -->
<div id="assign-students-modal-root"></div>

<!-- Module Trash Modal -->
<div class="modal fade" id="moduleTrashModal" tabindex="-1" aria-labelledby="moduleTrashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); border-radius: 12px 12px 0 0; padding: 1.25rem;">
                <h5 class="modal-title text-white fw-bold" id="moduleTrashModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Module Trash
                </h5>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    @if($trashedModules->isNotEmpty())
                    <button type="button" class="btn btn-danger btn-sm" id="empty-module-trash-btn" onclick="emptyModuleTrash()">
                        <i class="fas fa-trash me-1"></i>Delete All
                    </button>
                    @endif
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div id="trashed-modules-list">
                    @if($trashedModules->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-trash fa-3x text-muted mb-3 opacity-25"></i>
                            <h5 class="text-muted">Trash is empty</h5>
                            <p class="text-muted small">Deleted modules will appear here for recovery.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Module Title</th>
                                        <th>Deleted Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trashedModules as $module)
                                    <tr id="trashed-module-{{ $module->id }}">
                                        <td>
                                            <div class="fw-bold">{{ $module->title }}</div>
                                            <small class="text-muted">{{ Str::limit($module->description, 50) }}</small>
                                        </td>
                                        <td>{{ $module->deleted_at->format('M j, Y H:i') }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-success me-1" 
                                                    onclick="restoreModule({{ $module->id }})" title="Restore Module">
                                                <i class="fas fa-undo"></i> Restore
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="permanentlyDeleteModule({{ $module->id }}, '{{ addslashes($module->title) }}')" title="Delete Permanently">
                                                <i class="fas fa-times-circle"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="creative-btn creative-btn-outline" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/course-assignment.jsx')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking for assignment modal function...');
    
    const assignButton = document.getElementById('assign-students-btn');
    const assignButtonSidebar = document.getElementById('assign-students-btn-sidebar');

    const course = {
        id: {{ $courseWithModules->id }},
        title: @json($courseWithModules->title),
        description: @json($courseWithModules->description),
        teacher_name: @json($courseWithModules->teacher ? $courseWithModules->teacher->name : 'No teacher assigned'),
        max_students: {{ $courseWithModules->max_students ?? 'null' }},
        current_enrollment_count: {{ $courseWithModules->enrollments_count ?? 0 }}
    };

    function handleAssignmentComplete(result) {
        console.log('Assignment completed:', result);
        // Optionally reload the page to show updated enrollment stats
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    }

    // Wait for the assignment modal function to be available
    function initializeModal() {
        if (typeof window.initializeAssignmentModal === 'function') {
            console.log('Assignment modal function found, initializing...');
            
            const assignmentModal = window.initializeAssignmentModal({
                containerId: 'assign-students-modal-root',
                course: course,
                onClose: () => assignmentModal.close(),
                onAssignmentComplete: handleAssignmentComplete
            });

            if (assignButton) {
                assignButton.addEventListener('click', () => {
                    console.log('Assign button clicked');
                    assignmentModal.open();
                });
            }
            
            if (assignButtonSidebar) {
                assignButtonSidebar.addEventListener('click', () => {
                    console.log('Assign sidebar button clicked');
                    assignmentModal.open();
                });
            }
        } else {
            console.log('Assignment modal function not yet available, retrying...');
            setTimeout(initializeModal, 100);
        }
    }

    initializeModal();

    // --- Module Trash Management ---
    
    const viewTrashBtn = document.getElementById('view-module-trash');
    const trashModalEl = document.getElementById('moduleTrashModal');
    let trashModal = null;
    
    if (viewTrashBtn && trashModalEl) {
        trashModal = new bootstrap.Modal(trashModalEl);
        viewTrashBtn.addEventListener('click', function() {
            trashModal.show();
        });
    }

    // Restore Module
    window.restoreModule = function(moduleId) {
        if (!confirm('Are you sure you want to restore this module?')) return;

        fetch(`/teacher/courses/{{ $courseWithModules->id }}/modules/${moduleId}/restore`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show the restored module
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to restore module.');
        });
    };

    // Permanently Delete Module
    window.permanentlyDeleteModule = function(moduleId, moduleTitle) {
        if (!confirm(`WARNING: Are you sure you want to permanently delete "${moduleTitle}"?\n\nThis action cannot be undone and all associated subpages and content will be lost forever.`)) {
            return;
        }

        fetch(`/teacher/courses/{{ $courseWithModules->id }}/modules/${moduleId}/force-delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`trashed-module-${moduleId}`);
                if (row) row.remove();
                
                // Show success message or check if trash is empty
                const list = document.getElementById('trashed-modules-list');
                if (list && list.querySelectorAll('tbody tr').length === 0) {
                    list.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-trash fa-3x text-muted mb-3 opacity-25"></i>
                            <h5 class="text-muted">Trash is empty</h5>
                            <p class="text-muted small">Deleted modules will appear here for recovery.</p>
                        </div>
                    `;
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete module permanently.');
        });
    };

    // Empty All Module Trash
    window.emptyModuleTrash = function() {
        if (!confirm('Permanently delete all modules in trash? This action cannot be undone.')) {
            return;
        }

        const btn = document.getElementById('empty-module-trash-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
        }

        fetch(`/teacher/courses/{{ $courseWithModules->id }}/modules/empty-trash`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear the table and show empty message
                const list = document.getElementById('trashed-modules-list');
                if (list) {
                    list.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-trash fa-3x text-muted mb-3 opacity-25"></i>
                            <h5 class="text-muted">Trash is empty</h5>
                            <p class="text-muted small">Deleted modules will appear here for recovery.</p>
                        </div>
                    `;
                }

                // Hide the Delete All button
                if (btn) btn.style.display = 'none';

                alert('Module trash emptied successfully.');
            } else {
                alert('Failed: ' + (data.message || 'Unknown error'));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete All';
                }
            }
        })
        .catch(err => {
            console.error('Error emptying module trash:', err);
            alert('An error occurred while emptying module trash.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete All';
            }
        });
    };
});
</script>
@endpush