<!-- Enrollment Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ $data['summary']['total_enrollments'] ?? 0 }}</h4>
                <p class="card-text">Total Enrollments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">{{ $data['summary']['active_enrollments'] ?? 0 }}</h4>
                <p class="card-text">Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $data['summary']['completed_enrollments'] ?? 0 }}</h4>
                <p class="card-text">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning">{{ $data['summary']['avg_progress'] ?? 0 }}%</h4>
                <p class="card-text">Avg Progress</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Enrollment Trends -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Enrollment Trends</h6>
            </div>
            <div class="card-body">
                <canvas id="enrollmentTrendsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Status Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="statusDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function initializeCharts() {
    // Enrollment Trends Chart
    const enrollmentTrends = @json($data['enrollments_by_day'] ?? []);
    new Chart(document.getElementById('enrollmentTrendsChart'), {
        type: 'line',
        data: {
            labels: enrollmentTrends.map(item => item.date),
            datasets: [{
                label: 'New Enrollments',
                data: enrollmentTrends.map(item => item.count),
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

    // Status Distribution Chart
    const statusDistribution = @json($data['enrollments_by_status'] ?? []);
    new Chart(document.getElementById('statusDistributionChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(statusDistribution).map(status => status.charAt(0).toUpperCase() + status.slice(1)),
            datasets: [{
                data: Object.values(statusDistribution),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)'
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
}
</script>
@endpush