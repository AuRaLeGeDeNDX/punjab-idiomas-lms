@extends('layouts.app')

@section('title', 'Mensajes de Contacto - Admin')

@section('sidebar')
    @include('admin.sidebar')
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-envelope me-2"></i>Mensajes de Contacto</h1>
                        <p>Gestiona y responde a los mensajes recibidos desde la página web pública.</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="creative-card mb-4">
                <div class="creative-card-body">
                    <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="creative-form-label">Estado</label>
                            <select class="creative-form-input" id="status" name="status">
                                <option value="all">Todos los mensajes</option>
                                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>No leídos</option>
                                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Leídos (Sin responder)</option>
                                <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Respondidos</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="creative-btn creative-btn-outline-primary me-2">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.contact-messages.index') }}" class="creative-btn creative-btn-outline">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Messages Table -->
            <div class="creative-card p-0">
                <div class="table-responsive">
                    <table class="table creative-table mb-0">
                        <thead>
                            <tr>
                                <th>Remitente</th>
                                <th>Curso de Interés</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $msg)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ $msg->name }}</span>
                                        <small class="text-muted">{{ $msg->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    {{ $msg->course_interest ?: 'General' }}
                                </td>
                                <td>
                                    {{ $msg->created_at->format('d/m/Y H:i') }}
                                    <br>
                                    <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($msg->status === 'unread')
                                        <span class="badge bg-danger rounded-pill px-3">No leído</span>
                                    @elseif($msg->status === 'read')
                                        <span class="badge bg-warning rounded-pill px-3 text-dark">Leído</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-3">Respondido</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.contact-messages.show', $msg) }}" class="creative-btn creative-btn-outline-primary btn-action" title="Ver / Responder">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.contact-messages.destroy', $msg) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este mensaje de contacto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="creative-btn btn-action" style="color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.05);" title="Eliminar Mensaje" onmouseover="this.style.background='rgba(239, 68, 68, 0.1)';" onmouseout="this.style.background='rgba(239, 68, 68, 0.05)';">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-envelope-open-text fs-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">No hay mensajes</h5>
                                        <p class="text-muted mb-0">No se encontraron mensajes con los filtros actuales.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($messages->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $messages->links() }}
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
.creative-card { background: var(--color-surface); border-radius: 1rem; border: 1px solid var(--color-border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
.creative-card-body { padding: 1.5rem; }
.creative-form-label { font-weight: 600; color: var(--color-text); margin-bottom: 0.5rem; display: block; }
.creative-form-input { width: 100%; padding: 0.75rem 1rem; font-size: 1rem; border: 1px solid var(--color-border); border-radius: 0.5rem; background-color: var(--color-surface); color: var(--color-text); transition: all 0.3s ease; }
.creative-form-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); outline: none; }
.creative-table { margin: 0; }
.creative-table th { background-color: var(--color-background); font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 1rem 1.5rem; border-bottom: 2px solid var(--color-border); }
.creative-table td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid var(--color-border); color: var(--color-text); }
.btn-action { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; margin-right: 0.25rem; }
</style>
@endsection
