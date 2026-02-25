@extends('layouts.app')

@section('title', $announcement->title . ' - ' . $course->title)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('courses.announcements.index', $course) }}">Announcements</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($announcement->title, 50) }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="h4 mb-2">{{ $announcement->title }}</h1>
                            <div class="d-flex align-items-center gap-3">
                                @if($announcement->priority === 'high')
                                    <span class="badge bg-danger">High Priority</span>
                                @elseif($announcement->priority === 'medium')
                                    <span class="badge bg-warning">Medium Priority</span>
                                @else
                                    <span class="badge bg-secondary">Normal Priority</span>
                                @endif
                                
                                @if($announcement->isRecent())
                                    <span class="badge bg-success">New</span>
                                @endif
                            </div>
                        </div>
                        
                        @can('update', $course)
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('courses.announcements.edit', [$course, $announcement]) }}">Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('courses.announcements.destroy', [$course, $announcement]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this announcement?')">
                                                Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endcan
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="mb-4 text-muted small">
                        <i class="fas fa-user"></i> Posted by {{ $announcement->user->name }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar"></i> {{ $announcement->published_at->format('M j, Y \a\t g:i A') }}
                        <span class="mx-2">•</span>
                        <i class="fas fa-book"></i> {{ $course->title }}
                    </div>
                    
                    <div class="announcement-content">
                        {!! nl2br(e($announcement->message)) !!}
                    </div>
                </div>
                
                <div class="card-footer bg-transparent">
                    <a href="{{ route('courses.announcements.index', $course) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Announcements
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection