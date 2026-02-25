<!-- User Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ $data['summary']['total_users'] ?? 0 }}</h4>
                <p class="card-text">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">{{ $data['summary']['active_users'] ?? 0 }}</h4>
                <p class="card-text">Active Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $data['summary']['new_users'] ?? 0 }}</h4>
                <p class="card-text">New Users</p>
                <small class="text-muted">This period</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning">{{ $data['activity_status']['recent_login'] ?? 0 }}</h4>
                <p class="card-text">Recent Logins</p>
                <small class="text-muted">Last 7 days</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Users by Role -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Users by Role</h6>
            </div>
            <div class="card-body">
                <canvas id="usersByRoleChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Activity Status -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Activity Status</h6>
            </div>
            <div class="card-body">
                <canvas id="activityStatusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Registration Trends -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Registration Trends</h6>
            </div>
            <div class="card-body">
                <canvas id="registrationTrendsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Active Users -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Most Active Users</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($data['top_active_users'] ?? [] as $user)
                        <div class="list-group-item d-flex justify-content-between align-items-start px-0">
                            <div>
                                <h6 class="mb-1">{{ $user['name'] }}</h6>
                                <small class="text-muted">{{ $user['email'] }}</small>
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($user['last_login_at'])->diffForHumans() }}
                            </small>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            No active users found
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function initializeCharts() {
    // Users by Role Chart
    const usersByRole = @json($data['users_by_role'] ?? []);
    new Chart(document.getElementById('usersByRoleChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(usersByRole).map(role => role.charAt(0).toUpperCase() + role.slice(1)),
            datasets: [{
                data: Object.values(usersByRole),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 205, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Activity Status Chart
    const activityStatus = @json($data['activity_status'] ?? []);
    new Chart(document.getElementById('activityStatusChart'), {
        type: 'pie',
        data: {
            labels: ['Active', 'Inactive', 'Never Logged In', 'Recent Login'],
            datasets: [{
                data: [
                    activityStatus.active || 0,
                    activityStatus.inactive || 0,
                    activityStatus.never_logged_in || 0,
                    activityStatus.recent_login || 0
                ],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Registration Trends Chart
    const registrationData = @json($data['registrations_by_day'] ?? []);
    new Chart(document.getElementById('registrationTrendsChart'), {
        type: 'line',
        data: {
            labels: registrationData.map(item => item.date),
            datasets: [{
                label: 'New Registrations',
                data: registrationData.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>
@endpush