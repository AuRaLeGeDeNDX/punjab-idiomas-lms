@extends('layouts.app')

@section('title', $topic->title . ' - ' . $forum->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('courses.forums.index', $course) }}">Forums</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.forums.show', [$course, $forum]) }}">{{ $forum->title }}</a></li>
                    <li class="breadcrumb-item active">{{ $topic->title }}</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ $topic->title }}</h1>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        @if($topic->is_pinned)
                            <span class="badge bg-warning">
                                <i class="fas fa-thumbtack"></i> Pinned
                            </span>
                        @endif
                        @if($topic->is_locked)
                            <span class="badge bg-secondary">
                                <i class="fas fa-lock"></i> Locked
                            </span>
                        @endif
                        <small class="text-muted">
                            {{ $topic->views_count }} views • {{ $topic->getRepliesCount() }} replies
                        </small>
                    </div>
                </div>
                @if($topic->canUserReply(auth()->user()))
                    <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#replyForm">
                        <i class="fas fa-reply"></i> Reply
                    </button>
                @endif
            </div>

            <!-- Original Topic Post -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                {{ strtoupper(substr($topic->user->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-0">{{ $topic->user->name }}</h6>
                                    <small class="text-muted">{{ $topic->created_at->format('M j, Y \a\t g:i A') }}</small>
                                </div>
                                @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Teacher') || $topic->user_id === auth()->id())
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($topic->user_id === auth()->id() || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Teacher'))
                                                <li><a class="dropdown-item" href="#" onclick="editTopic({{ $topic->id }})">Edit</a></li>
                                            @endif
                                            @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Teacher'))
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="#" onclick="togglePin({{ $topic->id }})">
                                                    {{ $topic->is_pinned ? 'Unpin' : 'Pin' }} Topic
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="toggleLock({{ $topic->id }})">
                                                    {{ $topic->is_locked ? 'Unlock' : 'Lock' }} Topic
                                                </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="topic-content">
                                {!! nl2br(e($topic->content)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posts/Replies -->
            @if($posts->isNotEmpty())
                @foreach($posts as $post)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($post->user->name, 0, 2)) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-0">{{ $post->user->name }}</h6>
                                            <small class="text-muted">
                                                {{ $post->created_at->format('M j, Y \a\t g:i A') }}
                                                @if($post->is_edited)
                                                    • <em>edited {{ $post->edited_at->diffForHumans() }}</em>
                                                @endif
                                            </small>
                                        </div>
                                        @if($post->canUserEdit(auth()->user()))
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('courses.forums.posts.edit', [$course, $forum, $topic, $post]) }}">Edit</a></li>
                                                    @if($post->canUserDelete(auth()->user()))
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('courses.forums.posts.destroy', [$course, $forum, $topic, $post]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="post-content">
                                        {!! nl2br(e($post->content)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-center mt-4">
                    {{ $posts->links() }}
                </div>
            @endif

            <!-- Reply Form -->
            @if($topic->canUserReply(auth()->user()))
                <div class="collapse mt-4" id="replyForm">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Post Reply</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('courses.forums.topics.replies.store', [$course, $forum, $topic]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="content" class="form-label">Your Reply</label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" name="content" rows="5" required>{{ old('content') }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#replyForm">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Post Reply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection