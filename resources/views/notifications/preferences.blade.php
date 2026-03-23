@extends('layouts.app')

@section('title', __('Ajustes de Notificación'))

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up mb-4 shadow-lg overflow-hidden position-relative">
                <div class="header-overlay position-absolute top-0 start-0 w-100 h-100"></div>
                <div class="d-flex justify-content-between align-items-center position-relative z-index-1">
                    <div class="d-flex align-items-center gap-4">
                        <div class="header-icon-box shadow-colored">
                            <i class="fas fa-bell-on"></i>
                        </div>
                        <div>
                            <h1 class="h2 fw-bold text-white mb-1">{{ __('Preferencias de Notificación') }}</h1>
                            <p class="text-white-50 mb-0 d-none d-md-block">{{ __('Personaliza cómo y cuándo quieres que te informemos sobre las novedades.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-lg mb-4 fade-in-up" role="alert">
                    <div class="d-flex align-items-center p-1">
                        <div class="alert-icon-box bg-success-light text-success rounded-circle me-3">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="fw-semibold">{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Preference Form -->
            <form method="POST" action="{{ route('notifications.preferences.update') }}" class="preference-form">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Channels Part -->
                    <div class="col-lg-4 d-flex">
                        <div class="creative-card flex-grow-1 fade-in-up stagger-1 h-100">
                            <div class="creative-card-header mb-4">
                                <h3 class="h5 mb-0"><i class="fas fa-broadcast-tower me-2"></i>{{ __('Canal de Envío') }}</h3>
                                <div class="header-subtitle">{{ __('¿Cómo te notificamos?') }}</div>
                            </div>
                            
                            <div class="channel-options px-1">
                                <!-- In-App Preference -->
                                <div class="preference-item py-3 px-3 rounded-lg border transition-base active-hover shadow-sm bg-white">
                                    <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0">
                                        <div class="me-3">
                                            <label class="form-check-label h6 fw-bold mb-1 d-block" for="database_notifications">
                                                <i class="fas fa-mobile-android me-2 text-primary"></i>{{ __('App / Web') }}
                                            </label>
                                            <p class="small text-muted mb-0 lh-sm">{{ __('Alertas visuales dentro de la plataforma.') }}</p>
                                        </div>
                                        <input class="form-check-input ms-0 creative-switch" type="checkbox" name="database_notifications" id="database_notifications" value="1" {{ $preferences->database_notifications ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-top mt-4 px-1">
                                <button type="button" 
                                        onclick="if(confirm('¿Seguro कि quieres borrar todos tus ajustes personalizados?')) { document.getElementById('reset-form').submit(); }"
                                        class="creative-btn creative-btn-outline-secondary w-100 py-2">
                                    <i class="fas fa-history me-2"></i> {{ __('Restablecer') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Types Part -->
                    <div class="col-lg-8">
                        <div class="creative-card fade-in-up stagger-2 border-0 shadow-lg">
                            <div class="creative-card-header mb-4">
                                <h3 class="h5 mb-0"><i class="fas fa-layer-group me-2"></i>{{ __('¿Qué quieres saber?') }}</h3>
                                <div class="header-subtitle">{{ __('Selecciona los eventos que te interesan.') }}</div>
                            </div>

                            <div class="row g-3">
                                @php
                                    $typesConfig = [
                                        ['id' => 'assignment_published', 'icon' => 'fas fa-file-upload', 'title' => 'Nuevas Tareas', 'desc' => 'Actividades publicadas.'],
                                        ['id' => 'assignment_reminder', 'icon' => 'fas fa-alarm-clock', 'title' => 'Recordatorios', 'desc' => 'Plazos de entrega próximos.'],
                                        ['id' => 'grade_published', 'icon' => 'fas fa-graduation-cap', 'title' => 'Notas', 'desc' => 'Resultados de correcciones.'],
                                        ['id' => 'course_announcement', 'icon' => 'fas fa-bullhorn', 'title' => 'Anuncios', 'desc' => 'Noticias de tus profesores.'],
                                        ['id' => 'course_update', 'icon' => 'fas fa-pencil-paintbrush', 'title' => 'Cambios', 'desc' => 'Actualización de contenido.'],
                                        ['id' => 'direct_message', 'icon' => 'fas fa-paper-plane', 'title' => 'Mensajería', 'desc' => 'Chats y mensajes directos.'],
                                        ['id' => 'forum_reply', 'icon' => 'fas fa-comments-alt', 'title' => 'Foros', 'desc' => 'Respuestas a tus debates.'],
                                        ['id' => 'system_alert', 'icon' => 'fas fa-shield-check', 'title' => 'Sistema', 'desc' => 'Mantenimiento y avisos.'],
                                    ];
                                @endphp

                                @foreach($typesConfig as $config)
                                <div class="col-sm-6">
                                    <div class="type-card p-3 rounded-lg border h-100 transition-base shadow-sm bg-white" onclick="document.getElementById('{{ $config['id'] }}').click()">
                                        <div class="form-check custom-check ps-0 d-flex align-items-start gap-2">
                                            <div class="check-box-wrapper pt-1">
                                                <input class="form-check-input ms-0 me-2 shadow-none" type="checkbox" name="{{ $config['id'] }}" id="{{ $config['id'] }}" value="1" {{ $preferences->{$config['id']} ? 'checked' : '' }} onclick="event.stopPropagation()">
                                            </div>
                                            <div class="flex-grow-1">
                                                <label class="form-check-label d-block fw-bold h6 mb-1 text-dark" for="{{ $config['id'] }}" onclick="event.stopPropagation()">
                                                    <i class="{{ $config['icon'] }} text-primary-light me-1 small"></i> {{ __($config['title']) }}
                                                </label>
                                                <p class="small text-muted mb-0">{{ __($config['desc']) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="mt-5 pt-3 border-top d-flex justify-content-end gap-3">
                                <a href="{{ url()->previous() }}" class="creative-btn creative-btn-outline-secondary py-2">
                                    {{ __('Descartar') }}
                                </a>
                                <button type="submit" class="creative-btn creative-btn-primary px-5 py-2 fw-bold text-uppercase tracking-wider">
                                    <i class="fas fa-save me-2"></i> {{ __('Guardar Ajustes') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Hidden Reset Form -->
            <form id="reset-form" method="POST" action="{{ route('notifications.preferences.reset') }}" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>

<style>
    /* Premium Header */
    .creative-page-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.25rem;
        padding: 2.5rem 2rem;
        position: relative;
    }
    .header-overlay {
        background-image: radial-gradient(circle at 10% 20%, rgba(249, 115, 22, 0.15) 0%, transparent 40%);
    }
    .header-icon-box {
        background: var(--gradient-primary);
        color: white;
        width: 65px;
        height: 65px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    /* Cards */
    .creative-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 2rem;
        border-radius: 1.25rem;
    }
    .header-subtitle {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    /* Selection Items */
    .preference-item, .type-card {
        border-color: #f1f5f9 !important;
        cursor: pointer;
    }
    .preference-item:hover, .type-card:hover {
        border-color: #f9731633 !important;
        background-color: #fff9f5 !important;
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05) !important;
    }

    /* Input Styling */
    .creative-switch {
        width: 3.25rem !important;
        height: 1.75rem !important;
        background-color: #e2e8f0;
        border-color: transparent !important;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .form-check-input:checked.creative-switch {
        background-color: var(--color-primary);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    }

    .custom-check .form-check-input {
        width: 1.4rem;
        height: 1.4rem;
        border: 2px solid #cbd5e1;
        border-radius: 0.4rem;
        cursor: pointer;
    }
    .custom-check .form-check-input:checked {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
    }

    /* Utilities */
    .rounded-lg { border-radius: 1rem !important; }
    .shadow-colored { box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3); }
    .active-hover:hover .text-primary { color: var(--color-primary-dark) !important; }
    .tracking-wider { letter-spacing: 0.05em; }
    
    .text-primary-light { color: #f97316; opacity: 0.7; }

    @media (max-width: 991px) {
        .col-lg-4, .col-lg-8 { width: 100%; }
        .creative-page-header { padding: 1.5rem; }
        .header-icon-box { width: 50px; height: 50px; font-size: 1.25rem; }
    }
</style>
@endsection


