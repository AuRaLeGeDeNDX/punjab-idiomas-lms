@extends('layouts.app')

@section('title', 'My Activity History')

@section('sidebar')
    @include('student.sidebar')
@endsection

@section('content')
<div class="creative-page-header fade-in-up">
    <h1><i class="fas fa-history me-2"></i>My Activity History</h1>
    <p>View a timeline of your interactions, course progress, and submissions.</p>
</div>

<div class="creative-card mb-4 fade-in-up stagger-1">
    <div class="creative-card-header">
        <h3><i class="fas fa-filter me-2"></i>Filters</h3>
        <a href="{{ route('student.logs.index') }}" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
    </div>
    <div class="creative-card-body">
        <form action="{{ route('student.logs.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="log_name" class="creative-form-label">Activity Type</label>
                <select name="log_name" id="log_name" class="creative-form-select" onchange="this.form.submit()">
                    <option value="">All Activities</option>
                    @foreach($logNames as $name)
                        <option value="{{ $name }}" {{ request('log_name') === $name ? 'selected' : '' }}>
                            {{ ucfirst($name) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="creative-card fade-in-up stagger-2">
    <div class="creative-card-body p-0">
        <div class="table-responsive">
            <table class="table creative-table mb-0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Activity</th>
                        <th>Type</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            {{ $log->created_at->format('d M Y, H:i:s') }}
                            <br>
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <strong>{{ $log->description }}</strong>
                        </td>
                        <td>
                            <span class="creative-badge 
                                {{ $log->log_name === 'auth' ? 'creative-badge-primary' : 
                                  ($log->log_name === 'course' ? 'creative-badge-success' : 
                                  ($log->log_name === 'file' ? 'creative-badge-info' : 
                                  ($log->log_name === 'submission' ? 'creative-badge-warning' : 'creative-badge-secondary'))) }}">
                                {{ ucfirst($log->log_name) }}
                            </span>
                        </td>
                        <td>
                            @if(count($log->properties) > 0)
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="fas fa-info-circle me-1"></i> Details available ({{ count($log->properties) }})
                                </button>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-history fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No activities found</h5>
                                <p class="text-muted mb-0">You haven't generated any activity data yet, or nothing matches your filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="creative-card-footer border-top">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
