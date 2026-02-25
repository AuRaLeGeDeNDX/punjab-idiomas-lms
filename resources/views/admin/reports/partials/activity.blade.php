<!-- Activity Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-primary">{{ $data['summary']['daily_active_users'] ?? 0 }}</h4>
                <p class="card-text">Daily Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-success">{{ $data['summary']['weekly_active_users'] ?? 0 }}</h4>
                <p class="card-text">Weekly Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-info">{{ $data['summary']['monthly_active_users'] ?? 0 }}</h4>
                <p class="card-text">Monthly Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 class="text-warning">{{ $data['summary']['peak_activity_hour'] ?? 0 }}:00</h4>
                <p class="card-text">Peak Hour</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Most Accessed Courses -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Most Accessed Courses</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Access Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['most_accessed_courses'] ?? [] as $course)
                                <tr>
                                    <td>{{ $course['title'] }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $course['enrollments_count'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Engagement Metrics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Avg Courses per User</span>
                        <strong>{{ $data['user_engagement']['avg_courses_per_user'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Avg Completion Time</span>
                        <strong>{{ $data['user_engagement']['avg_completion_time'] ?? 'N/A' }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Retention Rate</span>
                        <strong>{{ $data['user_engagement']['retention_rate'] ?? 0 }}%</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Avg Session Duration</span>
                        <strong>{{ $data['summary']['avg_session_duration'] ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function initializeCharts() {
    // Activity charts would be implemented here if we had proper activity tracking data
    console.log('Activity charts would be rendered here with proper tracking data');
}
</script>
@endpush