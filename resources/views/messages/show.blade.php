@extends('layouts.app')

@section('title', $message->subject)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-envelope-open-text me-2"></i> {{ Str::limit($message->subject, 50) }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                                <li class="breadcrumb-item"><a href="{{ route('messages.index') }}" class="text-white text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Volver a Mensajes</a></li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        @if($message->recipient_id === auth()->id())
                            <a href="{{ route('messages.reply', $message) }}" class="creative-btn creative-btn-outline">
                                <i class="fas fa-reply me-1"></i> Responder
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Message -->
            <div class="creative-card mb-4 fade-in-up" style="animation-delay: 0.1s;">
                <div class="creative-card-body border-bottom bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-2 fw-bold text-dark">{{ $message->subject }}</h4>
                            <div class="text-muted">
                                <span class="d-inline-flex align-items-center me-3"><i class="fas fa-user-circle me-1"></i> <strong>De:</strong> <span class="ms-1">{{ $message->sender->name }}</span></span>
                                <span class="d-inline-flex align-items-center me-3"><i class="fas fa-user me-1"></i> <strong>Para:</strong> <span class="ms-1">{{ $message->recipient->name }}</span></span>
                                <span class="d-inline-flex align-items-center"><i class="far fa-clock me-1"></i> {{ $message->sent_at->format('d M, Y H:i') }}</span>
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="creative-btn creative-btn-outline-primary dropdown-toggle" style="padding: 0.5rem 1rem;" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Opciones
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                @if($message->recipient_id === auth()->id())
                                    <li><a class="dropdown-item" href="{{ route('messages.reply', $message) }}"><i class="fas fa-reply me-2 text-primary"></i> Responder</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li>
                                    <form action="{{ route('messages.destroy', $message) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este mensaje?')">
                                            <i class="fas fa-trash-alt me-2"></i> Eliminar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="creative-card-body">
                    <div class="message-content fs-5 text-dark" style="line-height: 1.6;">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>
            </div>

            <!-- Conversation Thread -->
            @if($conversationThread->isNotEmpty())
                <div class="creative-card mt-5 fade-in-up" style="animation-delay: 0.2s;">
                    <div class="creative-card-body border-bottom bg-light">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i> Historial de Conversación</h5>
                    </div>
                    <div class="creative-card-body">
                        @foreach($conversationThread as $reply)
                            <div class="border-start border-4 border-info ps-4 py-2 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-circle text-muted me-1"></i> {{ $reply->sender->name }}</h6>
                                    <span class="badge bg-light text-muted">{{ $reply->sent_at->format('d M, Y H:i') }}</span>
                                </div>
                                <div class="text-secondary">
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

<style>
/* Reusing the dashboard styling blocks */
.creative-page-header { background: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary) 100%); color: white; padding: 2rem; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.15); }
.creative-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.3s ease; text-decoration: none; border: none; cursor: pointer; }
.creative-btn-primary { background-color: var(--color-accent); color: white; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); }
.creative-btn-primary:hover { background-color: var(--color-accent-hover); color: white; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4); }
.creative-btn-outline { background-color: transparent; border: 2px solid white; color: white; }
.creative-btn-outline:hover { background-color: rgba(255, 255, 255, 0.1); color: white; }
.creative-btn-outline-primary { border: 2px solid var(--color-primary); color: var(--color-primary); background: transparent; }
.creative-btn-outline-primary:hover { background: var(--color-primary); color: white; }
.creative-card { background: var(--color-surface); border-radius: 1rem; border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
.creative-card-body { padding: 1.5rem; }
.message-content { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
</style>
@endsection