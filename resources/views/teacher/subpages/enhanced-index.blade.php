@extends('layouts.app')

@section('title', 'Subpages - ' . $module->title)

@push('styles')
<style>
.subpage-search-container {
    position: sticky;
    top: 0;
    z-index: 100;
    background: white;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 0;
}

.subpage-item {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.subpage-item:hover {
    background-color: #f8f9fa;
    border-left-color: #007bff;
}

.subpage-item.collapsed .subpage-content {
    display: none;
}

.subpage-item.expanded .subpage-content {
    display: block;
}

.collapse-toggle {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.collapse-toggle.collapsed {
    transform: rotate(-90deg);
}

.quick-actions {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.floating-create-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.floating-create-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.pagination-info {
    font-size: 0.875rem;
    color: #6c757d;
}

.subpage-grid {
    display: grid;
    gap: 1rem;
}

.subpage-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    background: white;
    transition: all 0.2s ease;
}

.subpage-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.view-toggle {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 0.25rem;
}

.view-toggle .btn {
    border: none;
    background: transparent;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
}

.view-toggle .btn.active {
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.lazy-load-trigger {
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 0.1rem 0.2rem;
    border-radius: 0.2rem;
}

@media (max-width: 768px) {
    .subpage-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        bottom: 1rem;
        right: 1rem;
    }
    
    .floating-create-btn {
        width: 50px;
        height: 50px;
    }
}

@media (min-width: 769px) {
    .subpage-grid.grid-view {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.index') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                    <i class="fas fa-book"></i> Courses
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.show', $course) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
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

    <!-- Header with Stats -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $module->title }} Subpages</h1>
            <div class="d-flex align-items-center gap-3">
                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $module->type)) }}</span>
                <span class="pagination-info">
                    <i class="fas fa-file-alt"></i> {{ $totalSubpages }} total subpages
                </span>
                <span class="pagination-info">
                    <i class="fas fa-eye"></i> {{ $activeSubpages }} active
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <!-- View Toggle -->
            <div class="view-toggle">
                <button class="btn btn-sm active" data-view="list" title="List View">
                    <i class="fas fa-list"></i>
                </button>
                <button class="btn btn-sm" data-view="grid" title="Grid View">
                    <i class="fas fa-th"></i>
                </button>
            </div>
            
            <!-- Bulk Actions -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog"></i> Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" id="bulkActivate"><i class="fas fa-eye"></i> Activate Selected</a></li>
                    <li><a class="dropdown-item" href="#" id="bulkDeactivate"><i class="fas fa-eye-slash"></i> Deactivate Selected</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" id="bulkDelete"><i class="fas fa-trash"></i> Delete Selected</a></li>
                    <li><a class="dropdown-item" href="#" id="exportSubpages"><i class="fas fa-download"></i> Export List</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="subpage-search-container">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="Search subpages by title or description..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="sortBy">
                    <option value="order" {{ request('sort') === 'order' ? 'selected' : '' }}>Order</option>
                    <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Title</option>
                    <option value="updated" {{ request('sort') === 'updated' ? 'selected' : '' }}>Last Updated</option>
                    <option value="content_count" {{ request('sort') === 'content_count' ? 'selected' : '' }}>Content Count</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="collapseAll">
                    <label class="form-check-label" for="collapseAll">
                        Collapse All
                    </label>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Subpages Container -->
    <div id="subpagesContainer">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading subpages...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-5" style="display: none;">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No subpages found</h5>
            <p class="text-muted">Try adjusting your search criteria or create a new subpage.</p>
        </div>

        <!-- Subpages List/Grid -->
        <div id="subpagesList" class="subpage-grid">
            <!-- Content will be loaded here -->
        </div>

        <!-- Lazy Load Trigger -->
        <div id="lazyLoadTrigger" class="lazy-load-trigger" style="display: none;">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading more...</span>
            </div>
            <span class="ms-2 text-muted">Loading more subpages...</span>
        </div>
    </div>

    <!-- Floating Create Button -->
    <div class="quick-actions">
        <a href="{{ route('teacher.courses.modules.subpages.create', [$course, $module]) }}" 
           class="btn btn-primary floating-create-btn d-flex align-items-center justify-content-center"
           title="Create New Subpage">
            <i class="fas fa-plus fa-lg"></i>
        </a>
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Bulk Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="bulkActionMessage"></p>
                <div id="selectedSubpagesList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBulkAction">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>

</style>
@endpush

@push('scripts')
<script>
class SubpageManager {
    constructor() {
        this.currentPage = 1;
        this.hasMorePages = true;
        this.isLoading = false;
        this.searchTimeout = null;
        this.selectedSubpages = new Set();
        this.currentView = 'list';
        
        this.initializeEventListeners();
        this.loadSubpages();
        this.setupIntersectionObserver();
    }

    initializeEventListeners() {
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.resetAndSearch();
            }, 300);
        });

        // Clear search
        document.getElementById('clearSearch').addEventListener('click', () => {
            document.getElementById('searchInput').value = '';
            this.resetAndSearch();
        });

        // Filters
        document.getElementById('statusFilter').addEventListener('change', () => {
            this.resetAndSearch();
        });

        document.getElementById('sortBy').addEventListener('change', () => {
            this.resetAndSearch();
        });

        // View toggle
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchView(e.target.dataset.view);
            });
        });

        // Collapse all toggle
        document.getElementById('collapseAll').addEventListener('change', (e) => {
            this.toggleAllCollapse(e.target.checked);
        });

        // Bulk actions
        document.getElementById('bulkActivate').addEventListener('click', () => {
            this.showBulkActionModal('activate');
        });

        document.getElementById('bulkDeactivate').addEventListener('click', () => {
            this.showBulkActionModal('deactivate');
        });

        document.getElementById('bulkDelete').addEventListener('click', () => {
            this.showBulkActionModal('delete');
        });

        document.getElementById('confirmBulkAction').addEventListener('click', () => {
            this.executeBulkAction();
        });

        // Export functionality
        document.getElementById('exportSubpages').addEventListener('click', () => {
            this.exportSubpages();
        });
    }

    setupIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && this.hasMorePages && !this.isLoading) {
                    this.loadMoreSubpages();
                }
            });
        }, { threshold: 0.1 });

        observer.observe(document.getElementById('lazyLoadTrigger'));
    }

    resetAndSearch() {
        this.currentPage = 1;
        this.hasMorePages = true;
        this.selectedSubpages.clear();
        document.getElementById('subpagesList').innerHTML = '';
        this.loadSubpages();
    }

    async loadSubpages() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();

        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                search: document.getElementById('searchInput').value,
                status: document.getElementById('statusFilter').value,
                sort: document.getElementById('sortBy').value,
                per_page: 20
            });

            const response = await fetch(`{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            
            if (this.currentPage === 1) {
                document.getElementById('subpagesList').innerHTML = '';
            }

            this.renderSubpages(data.subpages);
            this.hasMorePages = data.has_more_pages;
            
            this.updateUI(data);
            
        } catch (error) {
            console.error('Error loading subpages:', error);
            this.showError('Failed to load subpages');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }

    async loadMoreSubpages() {
        this.currentPage++;
        await this.loadSubpages();
    }

    renderSubpages(subpages) {
        const container = document.getElementById('subpagesList');
        
        subpages.forEach(subpage => {
            const element = this.createSubpageElement(subpage);
            container.appendChild(element);
        });

        // Show/hide lazy load trigger
        document.getElementById('lazyLoadTrigger').style.display = 
            this.hasMorePages ? 'flex' : 'none';
    }

    createSubpageElement(subpage) {
        const isListView = this.currentView === 'list';
        const element = document.createElement('div');
        element.className = isListView ? 'subpage-item list-group-item' : 'subpage-card';
        element.dataset.id = subpage.id;

        const statusBadge = subpage.is_active 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        const deletedBadge = subpage.deleted_at 
            ? '<span class="badge bg-danger">Deleted</span>' 
            : '';

        element.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-2">
                        <input type="checkbox" class="form-check-input me-2 subpage-checkbox" 
                               value="${subpage.id}">
                        <i class="fas fa-chevron-down collapse-toggle me-2" 
                           onclick="this.closest('.subpage-item, .subpage-card').classList.toggle('collapsed')"></i>
                        <h6 class="mb-0">
                            <a href="${subpage.show_url}" class="text-decoration-none">
                                ${this.highlightSearchTerm(subpage.title)}
                            </a>
                        </h6>
                        <div class="ms-2">
                            ${statusBadge}
                            ${deletedBadge}
                        </div>
                    </div>
                    
                    <div class="subpage-content">
                        ${subpage.description ? `<p class="text-muted mb-2 small">${this.highlightSearchTerm(subpage.description.substring(0, 100))}${subpage.description.length > 100 ? '...' : ''}</p>` : ''}
                        
                        <div class="row g-2 small text-muted mb-2">
                            <div class="col-auto">
                                <i class="fas fa-file-alt"></i> ${subpage.contents_count} content items
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dumbbell"></i> ${subpage.exercises_count} exercises
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tasks"></i> ${subpage.assignments_count} assignments
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock"></i> Updated ${subpage.updated_at_human}
                            </div>
                        </div>

                        ${subpage.recent_activity ? `
                        <div class="recent-activity">
                            <small class="text-muted">Recent: ${subpage.recent_activity}</small>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="btn-group" role="group">
                    ${!subpage.deleted_at ? `
                        <a href="${subpage.show_url}" class="btn btn-sm btn-outline-primary" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="${subpage.edit_url}" class="btn btn-sm btn-outline-secondary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-warning toggle-active-btn" 
                                data-id="${subpage.id}" 
                                data-active="${subpage.is_active}" 
                                title="${subpage.is_active ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${subpage.is_active ? 'eye-slash' : 'eye'}"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-btn" 
                                data-id="${subpage.id}" 
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : `
                        <button class="btn btn-sm btn-outline-success restore-btn" 
                                data-id="${subpage.id}" 
                                title="Restore">
                            <i class="fas fa-undo"></i>
                        </button>
                    `}
                </div>
            </div>
        `;

        // Add event listeners
        this.attachSubpageEventListeners(element);

        return element;
    }

    attachSubpageEventListeners(element) {
        // Checkbox selection
        const checkbox = element.querySelector('.subpage-checkbox');
        checkbox.addEventListener('change', (e) => {
            if (e.target.checked) {
                this.selectedSubpages.add(e.target.value);
            } else {
                this.selectedSubpages.delete(e.target.value);
            }
        });

        // Toggle active status
        const toggleBtn = element.querySelector('.toggle-active-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                this.toggleSubpageStatus(e.target.dataset.id);
            });
        }

        // Delete button
        const deleteBtn = element.querySelector('.delete-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                this.deleteSubpage(e.target.dataset.id);
            });
        }

        // Restore button
        const restoreBtn = element.querySelector('.restore-btn');
        if (restoreBtn) {
            restoreBtn.addEventListener('click', (e) => {
                this.restoreSubpage(e.target.dataset.id);
            });
        }
    }

    highlightSearchTerm(text) {
        const searchTerm = document.getElementById('searchInput').value.trim();
        if (!searchTerm) return text;
        
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }

    switchView(view) {
        this.currentView = view;
        
        // Update button states
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === view);
        });

        // Update container class
        const container = document.getElementById('subpagesList');
        container.className = view === 'grid' ? 'subpage-grid grid-view' : 'subpage-grid';

        // Re-render current subpages with new view
        this.resetAndSearch();
    }

    toggleAllCollapse(collapsed) {
        document.querySelectorAll('.subpage-item, .subpage-card').forEach(item => {
            if (collapsed) {
                item.classList.add('collapsed');
            } else {
                item.classList.remove('collapsed');
            }
        });
    }

    showBulkActionModal(action) {
        if (this.selectedSubpages.size === 0) {
            alert('Please select at least one subpage');
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('bulkActionModal'));
        const messageEl = document.getElementById('bulkActionMessage');
        const listEl = document.getElementById('selectedSubpagesList');

        let message = '';
        switch (action) {
            case 'activate':
                message = `Activate ${this.selectedSubpages.size} selected subpage(s)?`;
                break;
            case 'deactivate':
                message = `Deactivate ${this.selectedSubpages.size} selected subpage(s)?`;
                break;
            case 'delete':
                message = `Delete ${this.selectedSubpages.size} selected subpage(s)? This action can be undone.`;
                break;
        }

        messageEl.textContent = message;
        
        // Show selected subpages
        const selectedTitles = Array.from(this.selectedSubpages).map(id => {
            const element = document.querySelector(`[data-id="${id}"]`);
            return element ? element.querySelector('h6 a').textContent : `Subpage ${id}`;
        });
        
        listEl.innerHTML = `<ul class="list-unstyled mb-0">${selectedTitles.map(title => `<li>• ${title}</li>`).join('')}</ul>`;

        document.getElementById('confirmBulkAction').dataset.action = action;
        modal.show();
    }

    async executeBulkAction() {
        const action = document.getElementById('confirmBulkAction').dataset.action;
        const subpageIds = Array.from(this.selectedSubpages);

        try {
            const response = await fetch(`{{ route('teacher.courses.modules.subpages.bulk-action', [$course, $module]) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    action: action,
                    subpage_ids: subpageIds
                })
            });

            const data = await response.json();
            
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('bulkActionModal')).hide();
                this.selectedSubpages.clear();
                this.resetAndSearch();
                this.showSuccess(data.message);
            } else {
                this.showError(data.message || 'Bulk action failed');
            }
        } catch (error) {
            console.error('Bulk action error:', error);
            this.showError('Failed to execute bulk action');
        }
    }

    async toggleSubpageStatus(subpageId) {
        try {
            const response = await fetch(`{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}/${subpageId}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.resetAndSearch();
                this.showSuccess(data.message);
            } else {
                this.showError('Failed to update subpage status');
            }
        } catch (error) {
            console.error('Toggle status error:', error);
            this.showError('Failed to update subpage status');
        }
    }

    async deleteSubpage(subpageId) {
        if (!confirm('Are you sure you want to delete this subpage? This action can be undone.')) {
            return;
        }

        try {
            const response = await fetch(`{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}/${subpageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                this.resetAndSearch();
                this.showSuccess('Subpage deleted successfully');
            } else {
                this.showError('Failed to delete subpage');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showError('Failed to delete subpage');
        }
    }

    async restoreSubpage(subpageId) {
        try {
            const response = await fetch(`{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}/${subpageId}/restore`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                this.resetAndSearch();
                this.showSuccess('Subpage restored successfully');
            } else {
                this.showError('Failed to restore subpage');
            }
        } catch (error) {
            console.error('Restore error:', error);
            this.showError('Failed to restore subpage');
        }
    }

    exportSubpages() {
        const params = new URLSearchParams({
            search: document.getElementById('searchInput').value,
            status: document.getElementById('statusFilter').value,
            sort: document.getElementById('sortBy').value,
            export: 'csv'
        });

        window.location.href = `{{ route('teacher.courses.modules.subpages.index', [$course, $module]) }}?${params}`;
    }

    updateUI(data) {
        // Update pagination info
        const totalElement = document.querySelector('.pagination-info');
        if (totalElement) {
            totalElement.innerHTML = `<i class="fas fa-file-alt"></i> ${data.total} total subpages`;
        }

        // Show/hide empty state
        const isEmpty = data.total === 0;
        document.getElementById('emptyState').style.display = isEmpty ? 'block' : 'none';
        document.getElementById('subpagesList').style.display = isEmpty ? 'none' : 'block';
    }

    showLoading() {
        document.getElementById('loadingState').style.display = 'block';
    }

    hideLoading() {
        document.getElementById('loadingState').style.display = 'none';
    }

    showSuccess(message) {
        // Create and show success toast
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toast);
        });
    }

    showError(message) {
        // Create and show error toast
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            document.body.removeChild(toast);
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SubpageManager();
});
</script>
@endpush