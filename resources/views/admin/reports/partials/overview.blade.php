<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ $data['summary']['total_users'] ?? 0 }}</h4>
                <p class="card-text">Total Users</p>
                <small class="text-success">+{{ $data['summary']['new_users'] ?? 0 }} this period</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">{{ $data['summary']['total_courses'] ?? 0 }}</h4>
                <p class="card-text">Total Courses</p>
                <small class="text-info">{{ $data['summary']['published_courses'] ?? 0 }} published</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $data['summary']['total_enrollments'] ?? 0 }}</h4>
                <p class="card-text">Total Enrollments</p>
                <small class="text-success">{{ $data['summary']['active_enrollments'] ?? 0 }} active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning">{{ $data['summary']['completion_rate'] ?? 0 }}%</h4>
                <p class="card-text">Completion Rate</p>
                <small class="text-muted">This period</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">User Growth</h6>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Enrollment Trends</h6>
            </div>
            <div class="card-body">
                <canvas id="enrollmentTrendsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Popular Courses</h6>
            </div>
            <div class="card-body">
                <canvas id="coursePopularityChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">User Activity</h6>
            </div>
            <div class="card-body">
                <canvas id="userActivityChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function initializeCharts() {
    // User Growth Chart
    const userGrowthData = @json($data['charts']['user_growth'] ?? []);
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels: userGrowthData.map(item => item.date),
            datasets: [{
                label: 'New Users',
                data: userGrowthData.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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

    // Enrollment Trends Chart
    const enrollmentTrendsData = @json($data['charts']['enrollment_trends'] ?? []);
    new Chart(document.getElementById('enrollmentTrendsChart'), {
        type: 'line',
        data: {
            labels: enrollmentTrendsData.map(item => item.date),
            datasets: [{
                label: 'New Enrollments',
                data: enrollmentTrendsData.map(item => item.count),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
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

    // Course Popularity Chart
    const coursePopularityData = @json($data['charts']['course_popularity'] ?? []);
    new Chart(document.getElementById('coursePopularityChart'), {
        type: 'bar',
        data: {
            labels: coursePopularityData.map(item => item.title),
            datasets: [{
                label: 'Enrollments',
                data: coursePopularityData.map(item => item.enrollments_count),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
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

    // User Activity Chart
    const userActivityData = @json($data['charts']['user_activity'] ?? []);
    new Chart(document.getElementById('userActivityChart'), {
        type: 'line',
        data: {
            labels: userActivityData.map(item => item.date),
            datasets: [{
                label: 'Active Users',
                data: userActivityData.map(item => item.active_users),
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.2)',
                tension: 0.1
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