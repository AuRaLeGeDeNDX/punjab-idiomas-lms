@extends('layouts.app')

@section('title', 'System Announcements')

@push('styles')
@vite(['resources/css/design-system.css', 'resources/css/components/buttons.css', 'resources/css/components/cards.css', 'resources/css/components/navigation.css'])
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="admin-dashboard">
<!-- Page Header -->
<div class="creative-page-header fade-in-up">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-bullhorn me-2"></i>System Announcements</h1>
            <p>Manage system-wide announcements and communications</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.announcements.trashed') }}" class="creative-btn creative-btn-outline" style="border-color: var(--color-warning); color: var(--color-warning);">
                <i class="fas fa-trash"></i> View Trash
            </a>
            <a href="{{ route('admin.announcements.create') }}" class="creative-btn creative-btn-primary">
                <i class="fas fa-plus"></i> Create Announcement
            </a>
        </div>
    </div>
</div>

@if($announcements->isEmpty())
    <div class="creative-card">
        <div class="creative-card-body text-center py-5">
            <i class="fas fa-bullhorn fa-3x mb-3" style="color: var(--color-gray-400);"></i>
            <h5 style="color: var(--color-gray-600);">No System Announcements</h5>
            <p style="color: var(--color-gray-500);">No system-wide announcements have been created yet.</p>
            <a href="{{ route('admin.announcements.create') }}" class="creative-btn creative-btn-primary">
                Create First Announcement
            </a>
        </div>
    </div>
@else
    <div class="row">
        @foreach($announcements as $announcement)
            <div class="col-12 mb-4">
                <div class="creative-card">
                    <div class="creative-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <h5 class="card-title mb-0">
                                        <a href="{{ route('admin.announcements.show', $announcement) }}" class="text-decoration-none">
                                            {{ $announcement->title }}
                                        </a>
                                    </h5>
                                    @if($announcement->priority === 'high')
                                        <span class="creative-badge creative-badge-danger">High Priority</span>
                                    @elseif($announcement->priority === 'medium')
                                        <span class="creative-badge creative-badge-warning">Medium Priority</span>
                                    @else
                                        <span class="creative-badge creative-badge-primary">Normal Priority</span>
                                    @endif
                                    
                                    <span class="creative-badge creative-badge-info">System-Wide</span>
                                    
                                    @if($announcement->isRecent())
                                        <span class="creative-badge creative-badge-success">New</span>
                                    @endif
                                </div>
                                
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-user"></i> Posted by {{ $announcement->user->name }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar"></i> {{ $announcement->published_at->format('M j, Y \a\t g:i A') }}
                                    @if($announcement->display_until)
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-clock"></i> Expires: {{ $announcement->display_until->format('M j, Y') }}
                                        @if($announcement->display_until->isPast())
                                            <span class="creative-badge creative-badge-danger ms-1">Expired</span>
                                        @endif
                                    @endif
                                </div>
                                
                                <p class="card-text">{{ Str::limit($announcement->message, 200) }}</p>
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.announcements.show', $announcement) }}">View</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.announcements.edit', $announcement) }}">Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this announcement?')">
                                                Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center">
        {{ $announcements->links() }}
    </div>
@endif
</div>
@endsection