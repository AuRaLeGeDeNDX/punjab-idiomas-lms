<!-- Course Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ $data['summary']['total_courses'] ?? 0 }}</h4>
                <p class="card-text">Total Courses</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">{{ $data['summary']['published_courses'] ?? 0 }}</h4>
                <p class="card-text">Published</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning">{{ $data['summary']['draft_courses'] ?? 0 }}</h4>
                <p class="card-text">Drafts</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $data['summary']['avg_enrollments_per_course'] ?? 0 }}</h4>
                <p class="card-text">Avg Enrollments</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Popular Courses -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Most Popular Courses</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Enrollments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['popular_courses'] ?? [] as $course)
                                <tr>
                                    <td>{{ $course['title'] }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $course['enrollments_count'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No courses found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Distribution -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Enrollment Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="enrollmentDistributionChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function initializeCharts() {
    // Enrollment Distribution Chart
    const enrollmentDistribution = @json($data['enrollment_distribution'] ?? []);
    new Chart(document.getElementById('enrollmentDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(enrollmentDistribution),
            datasets: [{
                data: Object.values(enrollmentDistribution),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
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
}
</script>
@endpush