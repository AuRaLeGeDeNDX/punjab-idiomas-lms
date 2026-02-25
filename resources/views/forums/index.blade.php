@extends('layouts.app')

@section('title', 'Course Forums - ' . $course->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Course Forums</h1>
                    <p class="text-muted">{{ $course->title }}</p>
                </div>
                @can('update', $course)
                    <a href="{{ route('courses.forums.create', $course) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Forum
                    </a>
                @endcan
            </div>

            @if($forums->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5>No Forums Available</h5>
                        <p class="text-muted">No discussion forums have been created for this course yet.</p>
                        @can('update', $course)
                            <a href="{{ route('courses.forums.create', $course) }}" class="btn btn-primary">
                                Create First Forum
                            </a>
                        @endcan
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach($forums as $forum)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">
                                            <a href="{{ route('courses.forums.show', [$course, $forum]) }}" class="text-decoration-none">
                                                {{ $forum->title }}
                                            </a>
                                        </h5>
                                        @if($forum->is_locked)
                                            <span class="badge bg-warning">
                                                <i class="fas fa-lock"></i> Locked
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($forum->description)
                                        <p class="card-text text-muted small">{{ Str::limit($forum->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="row text-center small text-muted">
                                        <div class="col-6">
                                            <strong>{{ $forum->topics->count() }}</strong><br>
                                            Topics
                                        </div>
                                        <div class="col-6">
                                            <strong>{{ $forum->getTotalPostsCount() }}</strong><br>
                                            Posts
                                        </div>
                                    </div>
                                    
                                    @if($forum->topics->isNotEmpty())
                                        <hr>
                                        <div class="small">
                                            <strong>Latest:</strong>
                                            @php $latestTopic = $forum->topics->first() @endphp
                                            <a href="{{ route('courses.forums.topics.show', [$course, $forum, $latestTopic]) }}" class="text-decoration-none">
                                                {{ Str::limit($latestTopic->title, 30) }}
                                            </a>
                                            <div class="text-muted">
                                                by {{ $latestTopic->user->name }} • {{ $latestTopic->updated_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('courses.forums.show', [$course, $forum]) }}" class="btn btn-sm btn-outline-primary">
                                            View Forum
                                        </a>
                                        @can('update', $course)
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('courses.forums.edit', [$course, $forum]) }}">Edit</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('courses.forums.destroy', [$course, $forum]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this forum?')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection