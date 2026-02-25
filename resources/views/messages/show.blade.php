@extends('layouts.app')

@section('title', $message->subject)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('messages.index') }}">Messages</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($message->subject, 50) }}</li>
                    </ol>
                </nav>
                
                @if($message->recipient_id === auth()->id())
                    <a href="{{ route('messages.reply', $message) }}" class="btn btn-primary">
                        <i class="fas fa-reply"></i> Reply
                    </a>
                @endif
            </div>

            <!-- Main Message -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-1">{{ $message->subject }}</h4>
                            <div class="text-muted small">
                                <strong>From:</strong> {{ $message->sender->name }} 
                                <span class="mx-2">•</span>
                                <strong>To:</strong> {{ $message->recipient->name }}
                                <span class="mx-2">•</span>
                                {{ $message->sent_at->format('M j, Y \a\t g:i A') }}
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                @if($message->recipient_id === auth()->id())
                                    <li><a class="dropdown-item" href="{{ route('messages.reply', $message) }}">Reply</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <form action="{{ route('messages.destroy', $message) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                                            Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="message-content">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>
            </div>

            <!-- Conversation Thread -->
            @if($conversationThread->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Conversation History</h5>
                    </div>
                    <div class="card-body">
                        @foreach($conversationThread as $reply)
                            <div class="border-start border-3 border-secondary ps-3 mb-4 last:mb-0">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $reply->sender->name }}</strong>
                                        <small class="text-muted ms-2">{{ $reply->sent_at->format('M j, Y \a\t g:i A') }}</small>
                                    </div>
                                </div>
                                <div class="reply-content">
                                    {!! nl2br(e($reply->message)) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection