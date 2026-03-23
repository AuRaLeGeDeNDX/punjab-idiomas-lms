@extends('layouts.app')

@section('title', 'Nuevo Mensaje')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-pen-fancy me-2"></i> Redactar Mensaje</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0" style="background: transparent; padding: 0;">
                                <li class="breadcrumb-item"><a href="{{ route('messages.index') }}" class="text-white text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Volver a Mensajes</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="creative-card fade-in-up" style="animation-delay: 0.1s;">
                <div class="creative-card-body">
                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="recipient_id" class="form-label fw-bold"><i class="fas fa-user-plus me-1 text-primary"></i> Destinatario <span class="text-danger">*</span></label>
                            <select class="form-select border-2 @error('recipient_id') is-invalid @enderror" 
                                    id="recipient_id" name="recipient_id" required style="border-radius: 0.5rem; padding: 0.75rem;">
                                <option value="">Selecciona el destinatario...</option>
                                @foreach($availableRecipients as $user)
                                    <option value="{{ $user->id }}" {{ (old('recipient_id') == $user->id || (isset($recipient) && $recipient->id == $user->id)) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->getRoleNames()->first()) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('recipient_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="subject" class="form-label fw-bold"><i class="fas fa-heading me-1 text-primary"></i> Asunto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2 @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject') }}" required placeholder="Escribe el asunto del mensaje" style="border-radius: 0.5rem; padding: 0.75rem;">
                            @error('subject')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold"><i class="fas fa-align-left me-1 text-primary"></i> Mensaje <span class="text-danger">*</span></label>
                            <textarea class="form-control border-2 @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="8" required placeholder="Escribe tu mensaje aquí..." style="border-radius: 0.5rem; padding: 0.75rem;">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-4">
                            <a href="{{ route('messages.index') }}" class="btn btn-light px-4 py-2 font-weight-bold" style="border-radius: 0.5rem;">
                                Cancelar
                            </a>
                            <button type="submit" class="creative-btn creative-btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Enviar Mensaje
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
.creative-btn-outline { background-color: transparent; border: 2px solid white; color: white; }
.creative-btn-outline:hover { background-color: rgba(255, 255, 255, 0.1); color: white; }
.creative-card { background: var(--color-surface); border-radius: 1rem; border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
.creative-card-body { padding: 1.5rem; }
.form-control:focus, .form-select:focus { border-color: var(--color-primary); box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25); }
</style>
@endsection