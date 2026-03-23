@extends('layouts.app')

@section('title', 'Responder a: ' . $message->subject)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-reply me-2"></i> Responder Mensaje</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                                <li class="breadcrumb-item"><a href="{{ route('messages.show', $message) }}" class="text-white text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Volver al Mensaje</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Original Message Snippet -->
            <div class="creative-card mb-4 fade-in-up" border="0" style="animation-delay: 0.1s; border-left: 4px solid var(--color-info) !important;">
                <div class="creative-card-body bg-light">
                    <h5 class="fw-bold mb-3"><i class="fas fa-quote-left me-2 text-muted"></i>Mensaje Original</h5>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-secondary me-2">De: {{ $message->sender->name }}</span>
                        <span class="text-muted small"><i class="far fa-clock me-1"></i> {{ $message->sent_at->format('d M, Y H:i') }}</span>
                    </div>
                    <p class="fw-bold mb-2">Asunto: {{ $message->subject }}</p>
                    <div class="text-secondary fst-italic ps-3 border-start border-2 border-secondary">
                        {!! nl2br(e(Str::limit($message->message, 150))) !!}
                    </div>
                </div>
            </div>

            <!-- Reply Form Card -->
            <div class="creative-card fade-in-up" style="animation-delay: 0.2s;">
                <div class="creative-card-body">
                    <form action="{{ route('messages.reply.store', $message) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4 d-none">
                            <label class="form-label fw-bold">Para</label>
                            <input type="text" class="form-control border-2 bg-light" value="{{ $message->sender->name }}" readonly style="border-radius: 0.5rem; padding: 0.75rem;">
                        </div>
                        
                        <div class="mb-4 d-none">
                            <label class="form-label fw-bold">Asunto</label>
                            <input type="text" class="form-control border-2 bg-light" value="Re: {{ $message->subject }}" readonly style="border-radius: 0.5rem; padding: 0.75rem;">
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold"><i class="fas fa-reply-all me-1 text-primary"></i> Tu Respuesta <span class="text-danger">*</span></label>
                            <textarea class="form-control border-2 @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="8" required placeholder="Escribe tu respuesta aquí..." style="border-radius: 0.5rem; padding: 0.75rem;" autofocus>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-4">
                            <a href="{{ route('messages.show', $message) }}" class="btn btn-light px-4 py-2 font-weight-bold" style="border-radius: 0.5rem;">
                                Cancelar
                            </a>
                            <button type="submit" class="creative-btn creative-btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Enviar Respuesta
                            </button>
                        </div>
                    </form>
                </div>
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
.creative-card { background: var(--color-surface); border-radius: 1rem; border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
.creative-card-body { padding: 1.5rem; }
.form-control:focus { border-color: var(--color-primary); box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25); }
</style>
@endsection