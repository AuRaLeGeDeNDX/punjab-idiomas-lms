@extends('layouts.app')

@section('title', 'Courses - Admin')

@section('sidebar')
    @include('admin.sidebar')
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-mobile.css') }}?v={{ filemtime(public_path('css/admin-mobile.css')) }}">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-book me-2"></i>Course Management</h1>
                        <p>Manage all courses, enrollments, and course settings</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i>Back to Dashboard
                        </a>
                        <a href="{{ route('admin.courses.trashed') }}" class="creative-btn creative-btn-outline-warning">
                            <i class="fas fa-trash"></i>View Trash
                        </a>
                        <a href="{{ route('admin.courses.create') }}" class="creative-btn creative-btn-outline-primary">
                            <i class="fas fa-plus"></i>Create New Course
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="creative-card mb-4">
                <div class="creative-card-body">
                    <form method="GET" action="{{ route('admin.courses.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="creative-form-label">Search</label>
                            <input type="text" class="creative-form-input" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search courses...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="creative-form-label">Status</label>
                            <select class="creative-form-input" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="unpublished" {{ request('status') === 'unpublished' ? 'selected' : '' }}>Unpublished</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="teacher" class="creative-form-label">Teacher</label>
                            <select class="creative-form-input" id="teacher" name="teacher">
                                <option value="">All Teachers</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ request('teacher') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="creative-form-label">Category</label>
                            <select class="creative-form-input" id="category" name="category">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="creative-btn creative-btn-primary me-2">
                                <i class="fas fa-search"></i>Filter
                            </button>
                            <a href="{{ route('admin.courses.index') }}" class="creative-btn creative-btn-outline">
                                <i class="fas fa-times"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Actions -->
            @if($courses->count() > 0)
            <div class="creative-card mb-4">
                <div class="creative-card-body">
                    <form id="bulk-action-form" method="POST" action="{{ route('admin.courses.bulk-action') }}">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <select class="creative-form-input" name="action" required>
                                    <option value="">Select Action</option>
                                    <option value="publish">Publish Selected</option>
                                    <option value="unpublish">Unpublish Selected</option>
                                    <option value="delete">Delete Selected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="creative-btn creative-btn-outline" style="border-color: var(--color-warning); color: var(--color-warning);" onclick="return confirmBulkAction()">
                                    <i class="fas fa-cogs"></i>Apply to Selected
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <small style="color: var(--color-gray-500);">
                                    Select courses using checkboxes, then choose an action above.
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Courses Table -->
            <div class="creative-card">
                <div class="creative-card-body" style="padding: 0;">
                    @if($courses->count() > 0)
                        {{-- Desktop: Table view --}}
                        <div class="admin-mobile-table-desktop">
                            <div class="table-responsive">
                                <table class="creative-table">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="select-all" class="form-check-input">
                                            </th>
                                            <th>Course</th>
                                            <th>Teacher</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Enrollments</th>
                                            <th>Created</th>
                                            <th width="150">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($courses as $course)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" 
                                                       class="form-check-input course-checkbox">
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1">{{ $course->title }}</h6>
                                                    @if($course->description)
                                                        <small class="text-muted">{{ Str::limit($course->description, 80) }}</small>
                                                    @endif
                                                    @if($course->is_featured)
                                                        <span class="creative-badge creative-badge-warning ms-2">Featured</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($course->teacher)
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <div class="fw-medium">{{ $course->teacher->name }}</div>
                                                            <small class="text-muted">{{ $course->teacher->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No teacher assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($course->category)
                                                    <span class="creative-badge creative-badge-primary">{{ $course->category }}</span>
                                                @else
                                                    <span style="color: var(--color-gray-500);">Uncategorized</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($course->is_published)
                                                    <span class="creative-badge creative-badge-success">Published</span>
                                                @else
                                                    <span class="creative-badge creative-badge-primary">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="creative-badge creative-badge-info">{{ $course->enrollments_count ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $course->created_at->format('M j, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.courses.show', $course) }}" 
                                                       class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem;" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.courses.edit', $course) }}" 
                                                       class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem;" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" 
                                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem; border-color: var(--color-danger); color: var(--color-danger);" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Mobile: Card view --}}
                        <div class="admin-mobile-cards" style="padding: 1rem;">
                            @foreach($courses as $course)
                                <div class="admin-mobile-card">
                                    <div class="admin-mobile-card-header">
                                        <div class="admin-mobile-card-user">
                                            <div class="admin-mobile-card-info" style="width: 100%;">
                                                <div class="admin-mobile-card-name" style="white-space: normal;">{{ $course->title }}</div>
                                                @if($course->description)
                                                    <div class="admin-mobile-card-email" style="white-space: normal;">{{ Str::limit($course->description, 60) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="admin-mobile-card-meta">
                                        @if($course->is_published)
                                            <span class="creative-badge creative-badge-success">Published</span>
                                        @else
                                            <span class="creative-badge creative-badge-primary">Draft</span>
                                        @endif
                                        @if($course->is_featured)
                                            <span class="creative-badge creative-badge-warning">Featured</span>
                                        @endif
                                        @if($course->category)
                                            <span class="creative-badge creative-badge-primary">{{ $course->category }}</span>
                                        @endif
                                        <span class="admin-mobile-card-meta-item">
                                            <i class="fas fa-users"></i>
                                            {{ $course->enrollments_count ?? 0 }} enrolled
                                        </span>
                                        @if($course->teacher)
                                            <span class="admin-mobile-card-meta-item">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                                {{ $course->teacher->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="admin-mobile-card-actions">
                                        <a href="{{ route('admin.courses.show', $course) }}" class="creative-btn creative-btn-outline" title="View">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('admin.courses.edit', $course) }}" class="creative-btn creative-btn-outline" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="d-inline" style="flex: 1;"
                                              onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="creative-btn creative-btn-outline w-100" style="border-color: var(--color-danger); color: var(--color-danger);" title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center" style="padding: 1.5rem; border-top: 1px solid var(--color-gray-200);">
                            <div>
                                <small style="color: var(--color-gray-500);">
                                    Showing {{ $courses->firstItem() }} to {{ $courses->lastItem() }} of {{ $courses->total() }} courses
                                </small>
                            </div>
                            <div>
                                {{ $courses->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-graduation-cap fa-3x mb-3" style="color: var(--color-gray-400);"></i>
                            <h5 style="color: var(--color-gray-600);">No courses found</h5>
                            <p style="color: var(--color-gray-500);">
                                @if(request()->hasAny(['search', 'status', 'teacher', 'category']))
                                    Try adjusting your filters or 
                                    <a href="{{ route('admin.courses.index') }}">clear all filters</a>.
                                @else
                                    Get started by creating your first course.
                                @endif
                            </p>
                            @if(!request()->hasAny(['search', 'status', 'teacher', 'category']))
                                <a href="{{ route('admin.courses.create') }}" class="creative-btn creative-btn-primary">
                                    <i class="fas fa-plus"></i>Create First Course
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Select all functionality
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.course-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Update select-all when individual checkboxes change
    document.querySelectorAll('.course-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.course-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.course-checkbox:checked');
            const selectAll = document.getElementById('select-all');
            
            selectAll.checked = allCheckboxes.length === checkedCheckboxes.length;
            selectAll.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
        });
    });

    // Bulk action confirmation
    function confirmBulkAction() {
        const checkedBoxes = document.querySelectorAll('.course-checkbox:checked');
        const action = document.querySelector('select[name="action"]').value;
        
        if (checkedBoxes.length === 0) {
            alert('Please select at least one course.');
            return false;
        }
        
        if (!action) {
            alert('Please select an action.');
            return false;
        }
        
        const actionText = action === 'delete' ? 'delete' : action;
        return confirm(`Are you sure you want to ${actionText} ${checkedBoxes.length} selected course(s)?`);
    }

    // Add course IDs to bulk action form
    document.getElementById('bulk-action-form').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.course-checkbox:checked');
        
        // Remove existing hidden inputs
        this.querySelectorAll('input[name="course_ids[]"]').forEach(input => input.remove());
        
        // Add checked course IDs
        checkedBoxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'course_ids[]';
            hiddenInput.value = checkbox.value;
            this.appendChild(hiddenInput);
        });
    });
</script>
@endpush