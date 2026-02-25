@extends('layouts.app')

@section('title', $forum->title . ' - ' . $course->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('courses.forums.index', $course) }}">Forums</a></li>
                            <li class="breadcrumb-item active">{{ $forum->title }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">{{ $forum->title }}</h1>
                    @if($forum->description)
                        <p class="text-muted">{{ $forum->description }}</p>
                    @endif
                    @if($forum->is_locked)
                        <span class="badge bg-warning">
                            <i class="fas fa-lock"></i> Locked Forum
                        </span>
                    @endif
                </div>
                @if($forum->canUserPost(auth()->user()))
                    <a href="{{ route('courses.forums.topics.create', [$course, $forum]) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Topic
                    </a>
                @endif
            </div>

            @if($topics->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5>No Topics Yet</h5>
                        <p class="text-muted">Be the first to start a discussion in this forum.</p>
                        @if($forum->canUserPost(auth()->user()))
                            <a href="{{ route('courses.forums.topics.create', [$course, $forum]) }}" class="btn btn-primary">
                                Start First Topic
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Topic</th>
                                    <th class="text-center" width="100">Replies</th>
                                    <th class="text-center" width="100">Views</th>
                                    <th width="200">Last Post</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topics as $topic)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                @if($topic->is_pinned)
                                                    <i class="fas fa-thumbtack text-warning me-2 mt-1"></i>
                                                @endif
                                                @if($topic->is_locked)
                                                    <i class="fas fa-lock text-muted me-2 mt-1"></i>
                                                @endif
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('courses.forums.topics.show', [$course, $forum, $topic]) }}" class="text-decoration-none">
                                                            {{ $topic->title }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Started by {{ $topic->user->name }} • {{ $topic->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $topic->posts_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $topic->views_count }}</span>
                                        </td>
                                        <td>
                                            @php $latestPost = $topic->getLatestPost() @endphp
                                            @if($latestPost)
                                                <div class="small">
                                                    <div>{{ $latestPost->user->name }}</div>
                                                    <div class="text-muted">{{ $latestPost->created_at->diffForHumans() }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">No posts</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $topics->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection