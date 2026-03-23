@extends('layouts.app')

@section('title', __('Ajustes de Usuario'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="creative-card shadow-sm border-0 fade-in-up" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);">
                <div class="p-4 d-flex align-items-center gap-3">
                    <div class="icon-box rounded-lg bg-primary text-white shadow-sm">
                        <i class="fas fa-user-cog fs-4"></i>
                    </div>
                    <div>
                        <h1 class="h3 fw-bold text-white mb-1">{{ __('Configuración de Cuenta') }}</h1>
                        <p class="text-slate-300 mb-0 opacity-75">{{ __('Administra tu información personal y seguridad.') }}</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('profile.show') }}" class="creative-btn creative-btn-outline-primary shadow-sm bg-white-10">
                            <i class="fas fa-arrow-left me-2"></i> {{ __('Volver al Perfil') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 rounded-xl border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4 justify-content-center">
        <!-- Profile Info -->
        <div class="col-lg-8">
            <div class="creative-card fade-in-up stagger-1 h-100">
                <div class="creative-card-header mb-4">
                    <h3 class="h5 mb-0"><i class="fas fa-id-card me-2 text-primary"></i>{{ __('Información del Perfil') }}</h3>
                    <div class="header-subtitle">{{ __('Actualiza tu nombre y dirección de correo electrónico.') }}</div>
                </div>
                
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold text-dark">{{ __('Nombre Completo') }}</label>
                        <div class="input-group creative-input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="name" id="name" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        </div>
                        @error('name')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-5">
                        <label for="email" class="form-label fw-bold text-dark">{{ __('Correo Electrónico') }}</label>
                        <div class="input-group creative-input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1 text-info"></i> {{ __('Este correo se usará para todas las comunicaciones del sistema.') }}</div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="creative-btn creative-btn-primary w-100 py-3 shadow-md">
                            <i class="fas fa-save me-2"></i> {{ __('Guardar Cambios') }}
                        </button>
                    </div>
                </form>

                <div class="mt-5 p-4 rounded-xl bg-orange-50 border border-orange-200">
                    <div class="d-flex gap-3">
                        <div class="icon-box-sm rounded-circle bg-orange-200 text-orange-700">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-orange-900">{{ __('Seguridad de la cuenta') }}</h6>
                            <p class="small text-orange-800 mb-0 opacity-75">
                                {{ __('Por razones de seguridad, los cambios de contraseña deben ser gestionados por un administrador.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-slate-300 { color: #cbd5e1; }
    .bg-white-10 { background-color: rgba(255, 255, 255, 0.1); }
    .rounded-xl { border-radius: 1rem !important; }
    
    .creative-input-group {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    .creative-input-group:focus-within {
        border-color: #f97316;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1) !important;
    }
    .creative-input-group .form-control:focus {
        box-shadow: none !important;
    }
</style>
@endsection
