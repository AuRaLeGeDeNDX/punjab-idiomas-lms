@extends('layouts.app')

@section('title', 'System Logs')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.users.index') }}">Users</a>
    </li>
@endsection

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-list-alt me-2"></i>System Logs
            </h1>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="refreshLogs()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
                <button class="btn btn-outline-success" onclick="downloadLogs()">
                    <i class="fas fa-download me-1"></i>Download
                </button>
                <button class="btn btn-outline-info" onclick="checkSystemHealth()">
                    <i class="fas fa-heartbeat me-1"></i>Health Check
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Log Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">{{ $logStats['total'] ?? 0 }}</h5>
                <p class="card-text">Total Entries</p>
                <small class="text-muted">{{ $logStats['file_size'] ?? '0 B' }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger">{{ $logStats['by_level']['error'] ?? 0 }}</h5>
                <p class="card-text">Errors</p>
                <small class="text-muted">Critical issues</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">{{ $logStats['by_level']['warning'] ?? 0 }}</h5>
                <p class="card-text">Warnings</p>
                <small class="text-muted">Potential issues</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">{{ $logStats['by_level']['info'] ?? 0 }}</h5>
                <p class="card-text">Info</p>
                <small class="text-muted">General information</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Log Filters and Content -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Log Entries</h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" {{ $logLevel === 'all' ? 'selected' : '' }}>All Levels</option>
                                <option value="emergency" {{ $logLevel === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="alert" {{ $logLevel === 'alert' ? 'selected' : '' }}>Alert</option>
                                <option value="critical" {{ $logLevel === 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="error" {{ $logLevel === 'error' ? 'selected' : '' }}>Error</option>
                                <option value="warning" {{ $logLevel === 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="notice" {{ $logLevel === 'notice' ? 'selected' : '' }}>Notice</option>
                                <option value="info" {{ $logLevel === 'info' ? 'selected' : '' }}>Info</option>
                                <option value="debug" {{ $logLevel === 'debug' ? 'selected' : '' }}>Debug</option>
                            </select>
                            <input type="date" name="date" value="{{ $logDate }}" class="form-control form-control-sm" onchange="this.form.submit()">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search logs..." class="form-control form-control-sm">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th width="15%">Time</th>
                                <th width="10%">Level</th>
                                <th width="75%">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs['data'] ?? [] as $log)
                                <tr>
                                    <td>
                                        <small>
                                            {{ $log['timestamp'] }}<br>
                                            <span class="text-muted">{{ $log['formatted_time'] }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ getLevelColor($log['level']) }}">
                                            {{ strtoupper($log['level']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="font-monospace">{{ $log['message'] }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        No log entries found for the selected criteria
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(isset($logs['last_page']) && $logs['last_page'] > 1)
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ count($logs['data']) }} of {{ $logs['total'] }} entries
                        </small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                @for($i = 1; $i <= $logs['last_page']; $i++)
                                    <li class="page-item {{ $i == $logs['current_page'] ? 'active' : '' }}">
                                        <a class="page-link" href="?page={{ $i }}&level={{ $logLevel }}&date={{ $logDate }}&search={{ $search }}">{{ $i }}</a>
                                    </li>
                                @endfor
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Log Files Sidebar -->
    <div class="col-lg-3">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Available Log Files</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($logFiles as $file)
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $file['name'] }}</h6>
                                <small class="text-muted">
                                    {{ $file['size'] }}<br>
                                    {{ $file['modified_human'] }}
                                </small>
                            </div>
                            <div class="btn-group-vertical btn-group-sm">
                                <a href="{{ route('admin.logs.download', $file['name']) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button class="btn btn-outline-danger btn-sm" onclick="clearLogFile('{{ $file['name'] }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted">
                            No log files found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">System Health</h6>
            </div>
            <div class="card-body" id="system-health">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Checking system health...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshLogs() {
    location.reload();
}

function downloadLogs() {
    const level = '{{ $logLevel }}';
    const date = '{{ $logDate }}';
    const search = '{{ $search }}';
    
    window.open(`{{ route('admin.logs.index') }}?download=1&level=${level}&date=${date}&search=${search}`);
}

function clearLogFile(filename) {
    if (confirm(`Are you sure you want to clear the log file "${filename}"? This action cannot be undone.`)) {
        fetch(`{{ route('admin.logs.index') }}/${filename}/clear`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.error || 'Failed to clear log file');
            }
        })
        .catch(error => {
            toastr.error('An error occurred while clearing the log file');
        });
    }
}

function checkSystemHealth() {
    const healthContainer = document.getElementById('system-health');
    
    fetch('{{ route('admin.logs.health') }}', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        let html = `<div class="text-center mb-3">
            <span class="badge bg-${getHealthColor(data.status)} fs-6">${data.status.toUpperCase()}</span>
        </div>`;
        
        for (const [check, result] of Object.entries(data.checks)) {
            const icon = result.status ? 'check-circle text-success' : 'times-circle text-danger';
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-capitalize">${check.replace('_', ' ')}</span>
                    <i class="fas fa-${icon}"></i>
                </div>
                <small class="text-muted d-block mb-2">${result.message}</small>
            `;
        }
        
        healthContainer.innerHTML = html;
    })
    .catch(error => {
        healthContainer.innerHTML = '<div class="text-center text-danger">Failed to load health status</div>';
    });
}

function getHealthColor(status) {
    switch (status) {
        case 'healthy': return 'success';
        case 'warning': return 'warning';
        case 'critical': return 'danger';
        default: return 'secondary';
    }
}

// Auto-refresh logs every 30 seconds
setInterval(function() {
    if (document.visibilityState === 'visible') {
        checkSystemHealth();
    }
}, 30000);

// Load system health on page load
document.addEventListener('DOMContentLoaded', function() {
    checkSystemHealth();
});
</script>
@endpush

@php
function getLevelColor($level) {
    return match(strtolower($level)) {
        'emergency', 'alert', 'critical', 'error' => 'danger',
        'warning' => 'warning',
        'notice', 'info' => 'info',
        'debug' => 'secondary',
        default => 'primary'
    };
}
@endphp