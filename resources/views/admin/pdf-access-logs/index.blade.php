@extends('layouts.app')

@section('title', 'PDF Access Logs')

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
                <i class="fas fa-file-pdf me-2"></i>PDF Access Logs
            </h1>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="refreshLogs()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
                <button class="btn btn-outline-success" onclick="exportLogs()">
                    <i class="fas fa-download me-1"></i>Export CSV
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Access Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">{{ number_format($stats['total']) }}</h5>
                <p class="card-text">Total Access Attempts</p>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">{{ number_format($stats['granted']) }}</h5>
                <p class="card-text">Access Granted</p>
                <small class="text-muted">{{ $stats['total'] > 0 ? number_format(($stats['granted'] / $stats['total']) * 100, 1) : 0 }}% success rate</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger">{{ number_format($stats['denied']) }}</h5>
                <p class="card-text">Access Denied</p>
                <small class="text-muted">Security blocks</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">{{ number_format($stats['unique_users']) }}</h5>
                <p class="card-text">Unique Users</p>
                <small class="text-muted">{{ number_format($stats['unique_content']) }} PDFs accessed</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <h5 class="mb-3">Filter Access Logs</h5>
                        <form method="GET" action="{{ route('admin.pdf-access-logs.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">User</label>
                                <select name="user_id" id="user_id" class="form-select form-select-sm">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="content_id" class="form-label">PDF Content</label>
                                <select name="content_id" id="content_id" class="form-select form-select-sm">
                                    <option value="">All PDFs</option>
                                    @foreach($contents as $content)
                                        <option value="{{ $content->id }}" {{ $contentId == $content->id ? 'selected' : '' }}>
                                            {{ Str::limit($content->title, 40) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label for="access_granted" class="form-label">Status</label>
                                <select name="access_granted" id="access_granted" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="1" {{ $accessGranted === '1' ? 'selected' : '' }}>Granted</option>
                                    <option value="0" {{ $accessGranted === '0' ? 'selected' : '' }}>Denied</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-filter me-1"></i>Apply Filters
                                </button>
                                <a href="{{ route('admin.pdf-access-logs.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-times me-1"></i>Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="15%">User</th>
                                <th width="20%">PDF Content</th>
                                <th width="15%">Timestamp</th>
                                <th width="12%">IP Address</th>
                                <th width="10%">Status</th>
                                <th width="23%">Failure Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <small class="text-muted">#{{ $log->id }}</small>
                                    </td>
                                    <td>
                                        @if($log->user)
                                            <div>
                                                <strong>{{ $log->user->name }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                            <span class="text-muted">User Deleted</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->content)
                                            <div>
                                                <strong>{{ Str::limit($log->content->title, 30) }}</strong>
                                            </div>
                                            <small class="text-muted">ID: {{ $log->content->id }}</small>
                                        @else
                                            <span class="text-muted">Content Deleted</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            {{ $log->accessed_at->format('Y-m-d') }}
                                        </div>
                                        <small class="text-muted">{{ $log->accessed_at->format('H:i:s') }}</small>
                                        <div>
                                            <small class="text-muted">{{ $log->accessed_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="small">{{ $log->ip_address }}</code>
                                    </td>
                                    <td>
                                        @if($log->access_granted)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Granted
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>Denied
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$log->access_granted && $log->failure_reason)
                                            <span class="badge bg-warning text-dark" title="{{ $log->failure_reason }}">
                                                {{ Str::limit(ucwords(str_replace('_', ' ', $log->failure_reason)), 30) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                                        <p>No access logs found for the selected criteria</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries
                            </small>
                            <select class="form-select form-select-sm d-inline-block w-auto ms-2" onchange="changePerPage(this.value)">
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 per page</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per page</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per page</option>
                            </select>
                        </div>
                        <nav>
                            {{ $logs->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Failure Reasons Legend -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Failure Reason Codes
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>expired_token</strong>
                        <p class="small text-muted">Session token has expired</p>
                    </div>
                    <div class="col-md-3">
                        <strong>invalid_signature</strong>
                        <p class="small text-muted">Token signature verification failed</p>
                    </div>
                    <div class="col-md-3">
                        <strong>malformed_token</strong>
                        <p class="small text-muted">Token format is invalid</p>
                    </div>
                    <div class="col-md-3">
                        <strong>insufficient_permissions</strong>
                        <p class="small text-muted">User lacks view permission</p>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>content_not_found</strong>
                        <p class="small text-muted">PDF content does not exist</p>
                    </div>
                    <div class="col-md-3">
                        <strong>user_mismatch</strong>
                        <p class="small text-muted">Token user doesn't match request</p>
                    </div>
                    <div class="col-md-3">
                        <strong>content_mismatch</strong>
                        <p class="small text-muted">Token content doesn't match request</p>
                    </div>
                    <div class="col-md-3">
                        <strong>file_not_found</strong>
                        <p class="small text-muted">PDF file missing from storage</p>
                    </div>
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

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.open(`{{ route('admin.pdf-access-logs.index') }}?${params.toString()}`);
}

function changePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}

// Auto-refresh every 60 seconds if on first page
@if(!request()->has('page') || request()->get('page') == 1)
setInterval(function() {
    if (document.visibilityState === 'visible') {
        // Only refresh if no filters are applied
        const hasFilters = {{ !empty($userId) || !empty($contentId) || !empty($dateFrom) || !empty($dateTo) || $accessGranted !== null ? 'true' : 'false' }};
        if (!hasFilters) {
            location.reload();
        }
    }
}, 60000);
@endif
</script>
@endpush
