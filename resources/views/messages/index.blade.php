@extends('layouts.app')

@section('title', 'Mensajes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-comments me-2"></i> Mensajes Internos</h1>
                        <p class="mb-0">Comunícate con profesores, estudiantes y administradores.</p>
                    </div>
                    <div>
                        <a href="{{ route('messages.create') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-plus me-1"></i> Nuevo Mensaje
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs creative-tabs mb-4 fade-in-up" style="animation-delay: 0.1s;">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'inbox' ? 'active' : '' }}" href="{{ route('messages.index', ['tab' => 'inbox']) }}">
                        <i class="fas fa-inbox me-1"></i> Bandeja de Entrada
                        @if($tab === 'inbox' && $unreadCount > 0)
                            <span class="badge bg-danger ms-1 rounded-pill">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'sent' ? 'active' : '' }}" href="{{ route('messages.index', ['tab' => 'sent']) }}">
                        <i class="fas fa-paper-plane me-1"></i> Enviados
                    </a>
                </li>
            </ul>

            <!-- Messages List -->
            <div class="creative-card fade-in-up" style="animation-delay: 0.2s;">
                @if($messages->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($messages as $message)
                            <a href="{{ route('messages.show', $message) }}" class="list-group-item list-group-item-action p-4 {{ $tab === 'inbox' && !$message->isRead() ? 'bg-light border-start border-4 border-primary' : '' }}" style="{{ $tab === 'inbox' && !$message->isRead() ? 'border-left-color: var(--color-primary) !important;' : '' }}">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                    <h5 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                                        @if($tab === 'inbox' && !$message->isRead())
                                            <span class="badge bg-primary rounded-circle p-1" style="width: 10px; height: 10px;"></span>
                                        @endif
                                        {{ $message->subject }}
                                        @if($message->isReply())
                                            <span class="badge bg-secondary ms-2" style="font-size: 0.7em;">Respuesta</span>
                                        @endif
                                    </h5>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i>{{ $message->sent_at->format('d M, Y H:i') }}</small>
                                </div>
                                <div class="mb-2 text-muted">
                                    <i class="fas fa-user-circle me-1"></i> 
                                    <strong>{{ $tab === 'inbox' ? 'De: ' . $message->sender->name : 'Para: ' . $message->recipient->name }}</strong>
                                </div>
                                <p class="mb-1 text-secondary text-truncate" style="max-width: 90%;">
                                    {{ \Illuminate\Support\Str::limit($message->message, 150) }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                    
                    @if($messages->hasPages())
                        <div class="creative-card-body border-top">
                            {{ $messages->appends(['tab' => $tab])->links() }}
                        </div>
                    @endif
                @else
                    <div class="creative-card-body text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-envelope-open-text text-muted mb-4" style="font-size: 4rem;"></i>
                            <h4 class="text-muted fw-bold">{{ $tab === 'inbox' ? 'Bandeja de entrada vacía' : 'No hay mensajes enviados' }}</h4>
                            <p class="text-muted mb-4">{{ $tab === 'inbox' ? 'Aquí aparecerán los mensajes que recibas de otros usuarios.' : 'Aquí aparecerán los mensajes que envíes.' }}</p>
                            @if($tab === 'inbox')
                                <a href="{{ route('messages.create') }}" class="creative-btn creative-btn-primary mt-2">
                                    <i class="fas fa-paper-plane me-2"></i> Enviar tu primer mensaje
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
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
.creative-card { background: var(--color-surface); border-radius: 1rem; border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
.creative-card-body { padding: 1.5rem; }
.creative-tabs { border-bottom: 2px solid var(--color-border); }
.creative-tabs .nav-link { color: var(--color-text-muted); font-weight: 600; border: none; border-bottom: 2px solid transparent; padding: 1rem 1.5rem; transition: all 0.3s ease; background: transparent; }
.creative-tabs .nav-link:hover { color: var(--color-primary); border-bottom-color: rgba(79, 70, 229, 0.3); }
.creative-tabs .nav-link.active { color: var(--color-primary); border-bottom-color: var(--color-primary); background: transparent; }
.list-group-item-action:hover { background-color: rgba(79, 70, 229, 0.02); }
</style>
@endsection