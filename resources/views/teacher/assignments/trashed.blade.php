@extends('layouts.app')

@section('title', 'Trashed Assignments - ' . $subpage->title)

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/creative-professional.css'
])
@endpush

@php
    $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
@endphp

@section('content')
<div class="container-fluid">
    <!-- Page Header with Breadcrumb -->
    <div class="creative-page-header fade-in-up">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route($routePrefix . '.dashboard') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                                Assignments
                            </a>
                        </li>
                        <li class="breadcrumb-item active" style="color: #764ba2;">Trash</li>
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
                <h1><i class="fas fa-trash me-2"></i>Trashed Assignments</h1>
                <p>{{ $subpage->title }} - {{ $module->title }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" 
                   class="creative-btn creative-btn-outline">
                    <i class="fas fa-arrow-left me-2"></i>Back to Assignments
                </a>
                @if($assignments->count() > 0)
                <form method="POST" action="{{ route($routePrefix . '.courses.modules.subpages.assignments.empty-trash', [$course, $module, $subpage]) }}" onsubmit="return confirm('Are you sure you want to permanently delete ALL trashed assignments? This cannot be undone!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="creative-btn creative-btn-outline" style="border-color: var(--color-danger); color: var(--color-danger);">
                        <i class="fas fa-trash-alt me-2"></i>Empty Trash
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($assignments->count() > 0)
        <div class="creative-card fade-in-up stagger-1">
            <div class="creative-card-body p-0">
                <div class="table-responsive">
                    <table class="creative-table">
                        <thead>
                            <tr>
                                <th>Assignment Title</th>
                                <th>Deleted Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $assignment->title }}</div>
                                    </td>
                                    <td>
                                        <span class="text-danger">{{ $assignment->deleted_at->format('M j, Y H:i') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <form method="POST" action="{{ route($routePrefix . '.courses.modules.subpages.assignments.restore', [$course, $module, $subpage, $assignment->id]) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem; border-color: var(--color-success); color: var(--color-success);" title="Restore">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route($routePrefix . '.courses.modules.subpages.assignments.force-delete', [$course, $module, $subpage, $assignment->id]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this assignment? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="creative-btn creative-btn-outline ms-2" style="padding: 0.5rem 0.75rem; border-color: var(--color-danger); color: var(--color-danger);" title="Permanent Delete">
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
            </div>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $assignments->links() }}
        </div>
    @else
        <div class="creative-card text-center py-5 fade-in-up">
            <i class="fas fa-trash fa-3x text-muted mb-3 opacity-25"></i>
            <h4 class="text-muted">Trash is empty</h4>
        </div>
    @endif
</div>
@endsection
