@extends('layouts.app')

@section('title', 'Ver Mensaje - Admin')

@section('sidebar')
    @include('admin.sidebar')
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-xl-10 mx-auto">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <a href="{{ route('admin.contact-messages.index') }}" class="text-decoration-none text-muted me-2">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    Detalles del Mensaje
                </h2>
                <div>
                    @if($message->status === 'unread')
                        <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">No leído</span>
                    @elseif($message->status === 'read')
                        <span class="badge bg-warning rounded-pill px-3 py-2 fs-6 text-dark">Leído</span>
                    @else
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-6">Respondido</span>
                    @endif
                </div>
            </div>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Message Details -->
                <div class="col-md-7">
                    <div class="creative-card mb-4">
                        <div class="creative-card-body">
                            <h4 class="mb-4 text-primary"><i class="fas fa-user-circle me-2"></i>Datos del Remitente</h4>
                            
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted fw-bold">Nombre</div>
                                <div class="col-sm-8">{{ $message->name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted fw-bold">Correo</div>
                                <div class="col-sm-8">
                                    <a href="mailto:{{ $message->email }}" class="text-decoration-none">{{ $message->email }}</a>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted fw-bold">Curso de Interés</div>
                                <div class="col-sm-8">{{ $message->course_interest ?: 'General' }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted fw-bold">Fecha de Envío</div>
                                <div class="col-sm-8">{{ $message->created_at->format('d/m/Y H:i') }} ({{ $message->created_at->diffForHumans() }})</div>
                            </div>

                            <hr class="my-4">

                            <h4 class="mb-3 text-primary"><i class="fas fa-comment-dots me-2"></i>Mensaje</h4>
                            <div class="p-4 bg-light rounded-3 shadow-sm" style="white-space: pre-wrap; font-size: 1.05rem;">{{ $message->message }}</div>
                        </div>
                    </div>
                </div>

                <!-- Reply Section -->
                <div class="col-md-5">
                    <div class="creative-card h-100">
                        <div class="creative-card-body d-flex flex-column">
                            <h4 class="mb-4 text-primary"><i class="fas fa-reply me-2"></i>Respuesta</h4>

                            @if($message->status === 'replied')
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> Este mensaje ya fue respondido el {{ $message->replied_at->format('d/m/Y H:i') }} por {{ $message->replier->name ?? 'un administrador' }}.
                                </div>
                                <div class="p-3 bg-light rounded-3 border flex-grow-1" style="white-space: pre-wrap;">{{ $message->reply_message }}</div>
                            @else
                                <form action="{{ route('admin.contact-messages.reply', $message) }}" method="POST" class="d-flex flex-column flex-grow-1">
                                    @csrf
                                    <div class="mb-3 flex-grow-1 d-flex flex-column">
                                        <label for="reply_message" class="creative-form-label">Escribe tu respuesta</label>
                                        <textarea class="creative-form-input flex-grow-1" id="reply_message" name="reply_message" rows="10" required placeholder="Redacta la respuesta. Al enviar, se enviará automáticamente un correo electrónico a {{ $message->email }} con este texto.">{{ old('reply_message') }}</textarea>
                                        <div class="form-text text-muted mt-2">
                                            <i class="fas fa-envelope me-1"></i> Se enviará un correo a <strong>{{ $message->email }}</strong>
                                        </div>
                                    </div>
                                    <button type="submit" class="creative-btn creative-btn-primary w-100 justify-content-center mt-3" onclick="return confirm('¿Estás seguro de enviar este correo electrónico?')">
                                        <i class="fas fa-paper-plane me-2"></i> Enviar Respuesta
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Reusing the dashboard styling blocks */
.creative-card { background: var(--color-surface); border-radius: 1rem; border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
.creative-card-body { padding: 1.5rem; }
.creative-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.3s ease; text-decoration: none; border: none; cursor: pointer; }
.creative-btn-primary { background-color: var(--color-accent); color: white; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); }
.creative-btn-primary:hover { background-color: var(--color-accent-hover); color: white; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4); }
.creative-form-label { font-weight: 600; color: var(--color-text); margin-bottom: 0.5rem; display: block; }
.creative-form-input { width: 100%; padding: 0.75rem 1rem; font-size: 1rem; border: 1px solid var(--color-border); border-radius: 0.5rem; background-color: var(--color-surface); color: var(--color-text); transition: all 0.3s ease; resize: none; }
.creative-form-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); outline: none; }
</style>
@endsection
