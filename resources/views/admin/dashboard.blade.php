@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/components/buttons.css',
    'resources/css/components/cards.css',
    'resources/css/components/forms.css',
    'resources/css/components/tables.css',
    'resources/css/components/alerts.css',
    'resources/css/components/modals.css',
    'resources/css/components/navigation.css'
])
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ filemtime(public_path('css/admin-dashboard.css')) }}">
<style>
/* Inline dark mode fallback for dashboard cards */
.dark .admin-dashboard,
.dark.admin-dashboard {
    --bg-subtle: #0f172a;
    --bg-card: #1e293b;
    --bg-hover: #334155;
    --border-color: rgba(255, 255, 255, 0.1);
    --text-primary: #f8fafc;
    --text-secondary: #cbd5e1;
    --text-muted: #94a3b8;
}
.dark .admin-dashboard .system-overview-card,
.dark .admin-dashboard .user-stats-card,
.dark .admin-dashboard .activity-card,
.dark .admin-dashboard .alerts-card,
.dark .admin-dashboard .quick-actions-card,
.dark .admin-dashboard .storage-card {
    background: #1e293b !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
}
.dark .admin-dashboard .user-stats-card .card-header,
.dark .admin-dashboard .activity-card .card-header,
.dark .admin-dashboard .alerts-card .card-header,
.dark .admin-dashboard .quick-actions-card .card-header,
.dark .admin-dashboard .storage-card .card-header {
    background: rgba(0, 0, 0, 0.2) !important;
    border-bottom-color: rgba(255, 255, 255, 0.1) !important;
}
.dark .admin-dashboard .card {
    background: #1e293b !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    color: #f8fafc !important;
}
.dark .admin-dashboard .card-header {
    background: rgba(0, 0, 0, 0.2) !important;
    border-bottom-color: rgba(255, 255, 255, 0.1) !important;
    color: #f8fafc !important;
}
.dark .admin-dashboard .card-body {
    background: transparent !important;
    color: #f8fafc !important;
}
.dark .admin-dashboard .quick-action-btn {
    background: rgba(255, 255, 255, 0.03) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    color: #f8fafc !important;
}
</style>
@endpush

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.courses.index') }}">All Courses</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            Management
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Users</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.logs.index') }}">System Logs</a></li>
            <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}">Reports</a></li>
        </ul>
    </li>
@endsection

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="admin-dashboard">
    <!-- Page Header -->
    <div class="creative-page-header fade-in-up">
        <h1><i class="fas fa-shield-alt me-2"></i>System Administration</h1>
        <p>Welcome back! Here's your system overview and recent activity.</p>
    </div>

    <!-- System Overview Stats -->
    <div class="stats-grid">
        <div class="creative-stat-card fade-in-up stagger-1">
            <div class="icon"><i class="fas fa-users"></i></div>
            <div class="number">{{ $totalUsersCount }}</div>
            <div class="label">Total Users</div>
            <div class="subtitle">{{ $activeUsersCount }} active</div>
        </div>
        
        <div class="creative-stat-card variant-success fade-in-up stagger-2">
            <div class="icon"><i class="fas fa-book"></i></div>
            <div class="number">{{ $totalCoursesCount }}</div>
            <div class="label">Total Courses</div>
            <div class="subtitle">{{ $publishedCoursesCount }} published</div>
        </div>
        
        <div class="creative-stat-card variant-info fade-in-up stagger-3">
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
            <div class="number">{{ $totalEnrollmentsCount }}</div>
            <div class="label">Total Enrollments</div>
            <div class="subtitle">{{ $activeEnrollmentsCount }} active</div>
        </div>
        
        <div class="creative-stat-card variant-warning fade-in-up stagger-4">
            <div class="icon"><i class="fas fa-server"></i></div>
            <div class="number">{{ $systemHealth }}%</div>
            <div class="label">System Health</div>
            <div class="subtitle">{{ $systemStatus }}</div>
        </div>
    </div>

    <div class="row">
        <!-- System Overview -->
        <div class="col-lg-8">
            <!-- User Statistics -->
            <div class="creative-card mb-4">
                <div class="creative-card-header">
                    <h3><i class="fas fa-users"></i> User Statistics</h3>
                    <button class="creative-btn creative-btn-outline" style="padding: 0.5rem 1rem;" onclick="refreshUserStats()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="creative-card-body">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                        <div style="text-align: center; padding: 1rem;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;">{{ $studentsCount }}</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-600); font-weight: 600;">Students</div>
                        </div>
                        <div style="text-align: center; padding: 1rem;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--color-success); margin-bottom: 0.5rem;">{{ $teachersCount }}</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-600); font-weight: 600;">Teachers</div>
                        </div>
                        <div style="text-align: center; padding: 1rem;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--color-warning); margin-bottom: 0.5rem;">{{ $adminsCount }}</div>
                            <div style="font-size: 0.875rem; color: var(--color-gray-600); font-weight: 600;">Administrators</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent System Activity -->
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3><i class="fas fa-history"></i> Recent System Activity</h3>
                </div>
                <div class="creative-card-body">
                    <div class="table-responsive">
                        <table class="creative-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Resource</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                    <tr>
                                        <td>{{ $activity['time'] }}</td>
                                        <td>{{ $activity['user'] }}</td>
                                        <td>{{ $activity['action'] }}</td>
                                        <td>{{ $activity['resource'] }}</td>
                                        <td>
                                            <span class="creative-badge creative-badge-{{ $activity['status_color'] }}">
                                                {{ $activity['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No recent activity</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="col-lg-4">
            <!-- System Alerts -->
            <div class="creative-card mb-4">
                <div class="creative-card-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> System Alerts</h3>
                </div>
                <div class="creative-card-body">
                    @forelse($systemAlerts as $alert)
                        <div class="alert alert-{{ $alert['type'] }} py-2 mb-2">
                            <small>
                                <strong>{{ $alert['title'] }}</strong><br>
                                {{ $alert['message'] }}
                                <div class="text-muted">{{ $alert['time'] }}</div>
                            </small>
                        </div>
                    @empty
                        <p class="text-muted text-center">No system alerts</p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="creative-card mb-4">
                <div class="creative-card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="creative-card-body">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="{{ route('admin.users.create') }}" class="creative-btn creative-btn-outline" style="width: 100%; text-decoration: none;">
                            <i class="fas fa-user-plus"></i> Add User
                        </a>
                        <button class="creative-btn creative-btn-outline" style="width: 100%;" onclick="createCourse()">
                            <i class="fas fa-plus"></i> Create Course
                        </button>
                        <a href="{{ route('admin.announcements.create') }}" class="creative-btn creative-btn-outline" style="width: 100%; text-decoration: none;">
                            <i class="fas fa-bullhorn"></i> Send Announcement
                        </a>
                        <a href="{{ route('admin.logs.index') }}" class="creative-btn creative-btn-outline" style="width: 100%; text-decoration: none;">
                            <i class="fas fa-list-alt"></i> View Logs
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="creative-btn creative-btn-outline" style="width: 100%; text-decoration: none;">
                            <i class="fas fa-chart-bar"></i> Generate Report
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="creative-btn creative-btn-outline" style="width: 100%; text-decoration: none;">
                            <i class="fas fa-cog"></i> System Settings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Storage Usage -->
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3><i class="fas fa-hdd"></i> Storage Usage</h3>
                </div>
                <div class="creative-card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small style="font-weight: 600; color: var(--color-gray-700);">Files</small>
                            <small style="font-weight: 600; color: var(--color-primary);">{{ $storageUsage['files_percentage'] }}%</small>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $storageUsage['files_percentage'] }}%; background: var(--gradient-primary);"></div>
                        </div>
                        <small class="text-muted">{{ $storageUsage['files_used'] }} / {{ $storageUsage['files_total'] }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small style="font-weight: 600; color: var(--color-gray-700);">Database</small>
                            <small style="font-weight: 600; color: var(--color-info);">{{ $storageUsage['db_percentage'] }}%</small>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $storageUsage['db_percentage'] }}%; background: var(--gradient-info);"></div>
                        </div>
                        <small class="text-muted">{{ $storageUsage['db_used'] }} / {{ $storageUsage['db_total'] }}</small>
                    </div>
                    
                    <button class="creative-btn creative-btn-outline" style="width: 100%; border-color: var(--color-danger); color: var(--color-danger);" onclick="cleanupStorage()">
                        <i class="fas fa-trash"></i> Cleanup Storage
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showUserManagement() {
    window.location.href = '#users';
}

function showSystemLogs() {
    window.location.href = '#logs';
}

function showReports() {
    window.location.href = '#reports';
}

function showSystemSettings() {
    window.location.href = '#settings';
}

function createCourse() {
    window.location.href = '{{ route("admin.courses.create") }}';
}

function refreshUserStats() {
    $.get('{{ route("admin.dashboard") }}?refresh=users', function(data) {
        location.reload();
    });
}

function generateReport() {
    // Implementation for generating system reports
    toastr.info('Report generation started...');
}

function cleanupStorage() {
    if (confirm('Are you sure you want to cleanup storage? This will remove temporary files.')) {
        $.post('/admin/storage/cleanup', function(data) {
            toastr.success('Storage cleanup completed');
            location.reload();
        });
    }
}

// Auto-refresh dashboard data every 2 minutes
setInterval(function() {
    $.get('{{ route("admin.dashboard") }}?ajax=1', function(data) {
        if (data.systemHealth !== undefined) {
            // Update system health indicator
            $('.card-body h5:contains("{{ $systemHealth }}")').text(data.systemHealth + '%');
        }
    });
}, 120000);
</script>
@endpush