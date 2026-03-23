@extends('layouts.app')

@section('title', __('Mi Perfil'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="creative-card shadow-lg border-0 fade-in-up" style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); overflow: hidden;">
                <div class="card-body p-0 position-relative">
                    <!-- Decorative Background elements -->
                    <div class="position-absolute top-0 end-0 p-3 opacity-10">
                        <i class="fas fa-user-circle fa-10x text-white"></i>
                    </div>
                    
                    <div class="p-4 p-md-5 d-flex flex-column flex-md-row align-items-center gap-4 position-relative">
                        <!-- Avatar Shield -->
                        <div class="avatar-shield rotate-in" style="width: 120px; height: 120px; background: linear-gradient(45deg, #f97316, #fb923c); border-radius: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(249, 115, 22, 0.3);">
                            <span class="text-white fw-bold fs-1">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        
                        <div class="text-center text-md-start">
                            <h1 class="display-5 fw-bold text-white mb-2">{{ $user->name }}</h1>
                            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2 mb-3">
                                <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-bold">
                                    <i class="fas fa-shield-alt me-1"></i> {{ $user->getRoleNames()->first() }}
                                </span>
                                <span class="badge rounded-pill bg-info text-white px-3 py-2">
                                    <i class="fas fa-calendar-alt me-1"></i> {{ __('Miembro desde') }} {{ $user->created_at->format('M Y') }}
                                </span>
                            </div>
                            <p class="text-slate-300 fs-5 mb-0 opacity-75">
                                <i class="fas fa-envelope me-2"></i> {{ $user->email }}
                            </p>
                        </div>
                        
                        <div class="ms-md-auto mt-4 mt-md-0">
                            <a href="{{ route('profile.settings') }}" class="creative-btn creative-btn-outline-primary px-4 py-3">
                                <i class="fas fa-user-edit me-2"></i> {{ __('Editar Perfil') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="row g-4">
        <!-- Main Stats / Info -->
        <div class="col-lg-8">
            <div class="creative-card fade-in-up stagger-1 h-100">
                <div class="creative-card-header mb-4">
                    <h3 class="h4 mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>{{ __('Información Detallada') }}</h3>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="p-4 rounded-xl border bg-white shadow-sm transition-base active-hover h-100">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-box-sm rounded-lg bg-primary-subtle text-primary">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">{{ __('Nombre Completo') }}</h6>
                                    <p class="h5 mb-0 fw-bold">{{ $user->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-xl border bg-white shadow-sm transition-base active-hover h-100">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-box-sm rounded-lg bg-info-subtle text-info">
                                    <i class="fas fa-at"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">{{ __('Correo Electrónico') }}</h6>
                                    <p class="h5 mb-0 fw-bold">{{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-xl border bg-white shadow-sm transition-base active-hover h-100">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-box-sm rounded-lg bg-warning-subtle text-warning">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">{{ __('Rol del Sistema') }}</h6>
                                    <p class="h5 mb-0 fw-bold text-dark">{{ $user->getRoleNames()->first() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 rounded-xl border bg-white shadow-sm transition-base active-hover h-100">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-box-sm rounded-lg bg-success-subtle text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-bold mb-1">{{ __('Estado de Cuenta') }}</h6>
                                    <p class="h5 mb-0 fw-bold text-success">{{ __('Activa') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-4 rounded-xl bg-slate-100 border-start border-primary border-4">
                    <div class="d-flex gap-3">
                        <i class="fas fa-lightbulb text-warning fs-4"></i>
                        <div>
                            <h6 class="fw-bold mb-1">{{ __('Consejo de seguridad') }}</h6>
                            <p class="small text-muted mb-0">{{ __('Mantén tu contraseña segura y no la compartas con nadie. Te recomendamos cambiarla cada pocos meses para mayor seguridad.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions/Stats -->
        <div class="col-lg-4">
            <div class="creative-card fade-in-up stagger-2 mb-4">
                <div class="creative-card-header mb-4">
                    <h3 class="h5 mb-0"><i class="fas fa-tasks me-2 text-primary"></i>{{ __('Actividad Reciente') }}</h3>
                </div>
                
                <div class="list-group list-group-flush gap-2">
                    <div class="list-group-item border-0 px-0 d-flex align-items-center gap-3 bg-transparent">
                        <div class="rounded-circle bg-primary-subtle text-primary p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sign-in-alt small"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="small mb-0 fw-bold">{{ __('Último inicio de sesión') }}</p>
                            <small class="text-muted">{{ __('Hace unos momentos') }}</small>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0 d-flex align-items-center gap-3 bg-transparent">
                        <div class="rounded-circle bg-info-subtle text-info p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-edit small"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="small mb-0 fw-bold">{{ __('Perfil actualizado') }}</p>
                            <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="creative-card fade-in-up stagger-3 bg-primary text-white border-0 shadow-lg">
                <div class="p-4">
                    <h4 class="h5 fw-bold mb-3"><i class="fas fa-cog me-2"></i>{{ __('Configuración Rápida') }}</h4>
                    <p class="small opacity-75 mb-4">{{ __('Accede rápidamente a tus ajustes de seguridad y preferencias del sistema.') }}</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('profile.settings') }}" class="btn btn-light fw-bold py-2">
                            {{ __('Ajustes de Seguridad') }}
                        </a>
                        <a href="{{ route('notifications.preferences') }}" class="btn btn-outline-light py-2">
                            {{ __('Preferencias de Notificación') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-shield {
        transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .creative-card:hover .avatar-shield {
        transform: scale(1.05) rotate(5deg);
    }
    .text-slate-300 { color: #cbd5e1; }
    .bg-primary-subtle { background-color: rgba(249, 115, 22, 0.1); }
    .bg-info-subtle { background-color: rgba(14, 165, 233, 0.1); }
    .bg-warning-subtle { background-color: rgba(234, 88, 12, 0.1); }
    .bg-success-subtle { background-color: rgba(34, 197, 94, 0.1); }
    .rounded-xl { border-radius: 1rem !important; }
</style>
@endsection
