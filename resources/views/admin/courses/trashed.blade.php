@extends('layouts.app')

@section('title', 'Trashed Courses - Admin')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-trash me-2"></i>Trashed Courses</h1>
                        <p>Manage deleted courses</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.courses.index') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i>Back to Courses
                        </a>
                        @if($courses->count() > 0)
                        <form method="POST" action="{{ route('admin.courses.empty-trash') }}" onsubmit="return confirm('Are you sure you want to permanently delete ALL trashed courses? This cannot be undone!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="creative-btn creative-btn-outline" style="border-color: var(--color-danger); color: var(--color-danger);">
                                <i class="fas fa-trash-alt"></i>Empty Trash
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="creative-card">
                <div class="creative-card-body" style="padding: 0;">
                    @if($courses->count() > 0)
                        <div class="table-responsive">
                            <table class="creative-table">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Teacher</th>
                                        <th>Category</th>
                                        <th>Deleted At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courses as $course)
                                    <tr>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">{{ $course->title }}</h6>
                                            </div>
                                        </td>
                                        <td>
                                            @if($course->teacher)
                                                <div class="fw-medium">{{ $course->teacher->name }}</div>
                                            @else
                                                <span class="text-muted">No teacher assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="creative-badge creative-badge-primary">{{ $course->category ?? 'Uncategorized' }}</span>
                                        </td>
                                        <td>
                                            <small class="text-danger">{{ $course->deleted_at->format('M j, Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <form method="POST" action="{{ route('admin.courses.restore', $course->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem; border-color: var(--color-success); color: var(--color-success);" title="Restore">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.courses.force-delete', $course->id) }}" 
                                                      class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this course? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem; border-color: var(--color-danger); color: var(--color-danger);" title="Permanent Delete">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center" style="padding: 1.5rem; border-top: 1px solid var(--color-gray-200);">
                            <div>
                                {{ $courses->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-trash fa-3x mb-3" style="color: var(--color-gray-400);"></i>
                            <h5 style="color: var(--color-gray-600);">Trash is empty</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
