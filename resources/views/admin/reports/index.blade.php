@extends('layouts.app')

@section('title', 'System Reports')

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
                <i class="fas fa-chart-bar me-2"></i>System Reports
            </h1>
            <div class="btn-group">
                <button class="btn btn-outline-primary" onclick="refreshReports()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
                <button class="btn btn-outline-success" onclick="exportReport()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-outline-info" onclick="scheduleReport()">
                    <i class="fas fa-clock me-1"></i>Schedule
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Report Navigation -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <ul class="nav nav-pills" id="report-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $reportType === 'overview' ? 'active' : '' }}" 
                                        onclick="switchReport('overview')" type="button">
                                    <i class="fas fa-tachometer-alt me-1"></i>Overview
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $reportType === 'users' ? 'active' : '' }}" 
                                        onclick="switchReport('users')" type="button">
                                    <i class="fas fa-users me-1"></i>Users
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $reportType === 'courses' ? 'active' : '' }}" 
                                        onclick="switchReport('courses')" type="button">
                                    <i class="fas fa-book me-1"></i>Courses
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $reportType === 'enrollments' ? 'active' : '' }}" 
                                        onclick="switchReport('enrollments')" type="button">
                                    <i class="fas fa-user-graduate me-1"></i>Enrollments
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $reportType === 'activity' ? 'active' : '' }}" 
                                        onclick="switchReport('activity')" type="button">
                                    <i class="fas fa-chart-line me-1"></i>Activity
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="type" value="{{ $reportType }}">
                            <select name="range" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="7" {{ $dateRange === '7' ? 'selected' : '' }}>Last 7 days</option>
                                <option value="30" {{ $dateRange === '30' ? 'selected' : '' }}>Last 30 days</option>
                                <option value="90" {{ $dateRange === '90' ? 'selected' : '' }}>Last 90 days</option>
                                <option value="365" {{ $dateRange === '365' ? 'selected' : '' }}>Last year</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Content -->
<div id="report-content">
    @if($reportType === 'overview')
        @include('admin.reports.partials.overview', ['data' => $data])
    @elseif($reportType === 'users')
        @include('admin.reports.partials.users', ['data' => $data])
    @elseif($reportType === 'courses')
        @include('admin.reports.partials.courses', ['data' => $data])
    @elseif($reportType === 'enrollments')
        @include('admin.reports.partials.enrollments', ['data' => $data])
    @elseif($reportType === 'activity')
        @include('admin.reports.partials.activity', ['data' => $data])
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function switchReport(type) {
    const url = new URL(window.location);
    url.searchParams.set('type', type);
    window.location.href = url.toString();
}

function refreshReports() {
    location.reload();
}

function exportReport() {
    const type = '{{ $reportType }}';
    const range = '{{ $dateRange }}';
    
    // Show export options modal
    const modal = `
        <div class="modal fade" id="exportModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="exportForm">
                            <div class="mb-3">
                                <label class="form-label">Format</label>
                                <select name="format" class="form-select" required>
                                    <option value="csv">CSV</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Range</label>
                                <select name="range" class="form-select">
                                    <option value="7">Last 7 days</option>
                                    <option value="30" selected>Last 30 days</option>
                                    <option value="90">Last 90 days</option>
                                    <option value="365">Last year</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="downloadReport()">Download</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modal);
    const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
    exportModal.show();
    
    // Clean up modal after hiding
    document.getElementById('exportModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function downloadReport() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    const format = formData.get('format');
    const range = formData.get('range');
    const type = '{{ $reportType }}';
    
    window.open(`{{ url('admin/reports') }}/${type}/download?format=${format}&range=${range}`);
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
}

function scheduleReport() {
    toastr.info('Report scheduling feature coming soon!');
}

// Initialize charts if they exist
document.addEventListener('DOMContentLoaded', function() {
    // This will be called by individual report partials
    if (typeof initializeCharts === 'function') {
        initializeCharts();
    }
});
</script>
@endpush