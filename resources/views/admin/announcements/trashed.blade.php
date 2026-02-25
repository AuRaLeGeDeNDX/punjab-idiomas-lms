@extends('layouts.app')

@section('title', 'Trashed Announcements')

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
            <h1><i class="fas fa-trash me-2"></i>Trashed Announcements</h1>
            <p>Manage deleted announcements</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.announcements.index') }}" class="creative-btn creative-btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Announcements
            </a>
            @if(!$announcements->isEmpty())
            <form method="POST" action="{{ route('admin.announcements.empty-trash') }}" onsubmit="return confirm('Are you sure you want to permanently delete ALL trashed announcements? This cannot be undone!')">
                @csrf
                @method('DELETE')
                <button type="submit" class="creative-btn creative-btn-outline" style="border-color: var(--color-danger); color: var(--color-danger);">
                    <i class="fas fa-trash-alt"></i> Empty Trash
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if($announcements->isEmpty())
    <div class="creative-card">
        <div class="creative-card-body text-center py-5">
            <i class="fas fa-trash fa-3x mb-3" style="color: var(--color-gray-400);"></i>
            <h5 style="color: var(--color-gray-600);">Trash is empty</h5>
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
                                    <h5 class="card-title mb-0 text-decoration-line-through">
                                        {{ $announcement->title }}
                                    </h5>
                                </div>
                                
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-user"></i> Posted by {{ $announcement->user->name }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar-times"></i> Deleted at: <span class="text-danger">{{ $announcement->deleted_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            
                            <div class="btn-group">
                                <form action="{{ route('admin.announcements.restore', $announcement->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="creative-btn creative-btn-outline" style="padding: 0.5rem; border-color: var(--color-success); color: var(--color-success);" title="Restore">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.announcements.force-delete', $announcement->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="creative-btn creative-btn-outline ms-2" style="padding: 0.5rem; border-color: var(--color-danger); color: var(--color-danger);" title="Permanent Delete">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
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
