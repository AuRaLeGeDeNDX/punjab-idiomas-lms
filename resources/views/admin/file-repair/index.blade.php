@extends('layouts.admin')

@section('title', 'File Repair Dashboard')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">File Repair Dashboard</h1>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#batchRepairModal">
                        <i class="fas fa-tools"></i> Batch Repair
                    </button>
                </div>
            </div>

            @if(isset($error))
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $error }}
                </div>
            @endif

            <!-- Statistics Cards -->
            @if($stats)
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ number_format($stats['total_content_files']) }}</h4>
                                    <p class="card-text">Total Content Files</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ number_format($stats['files_with_issues']) }}</h4>
                                    <p class="card-text">Files with Issues</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ number_format($stats['recent_repairs']) }}</h4>
                                    <p class="card-text">Recent Repairs</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-wrench fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ number_format($stats['success_rate'], 1) }}%</h4>
                                    <p class="card-text">Success Rate</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <!-- Storage Configuration Status -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cog"></i> Storage Configuration Status
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($storageStatus)
                                <div class="mb-3">
                                    <span class="badge badge-{{ $storageStatus->isValid() ? 'success' : 'danger' }}">
                                        {{ $storageStatus->isValid() ? 'Valid' : 'Invalid' }}
                                    </span>
                                </div>
                                
                                @if(!$storageStatus->getErrors()->isEmpty())
                                    <div class="alert alert-warning">
                                        <strong>Configuration Issues:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach($storageStatus->getErrors() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                @if(!$storageStatus->getWarnings()->isEmpty())
                                    <div class="alert alert-info">
                                        <strong>Warnings:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach($storageStatus->getWarnings() as $warning)
                                                <li>{{ $warning }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-danger">
                                    Unable to load storage configuration status.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Storage Distribution -->
                @if($stats && isset($stats['storage_distribution']))
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie"></i> Storage Distribution
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="storageDistributionChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Recent Repair Operations -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history"></i> Recent Repair Operations
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="generateMissingFilesReport()">
                                <i class="fas fa-file-export"></i> Generate Report
                            </button>
                        </div>
                        <div class="card-body">
                            @if(count($recentRepairs) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Description</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentRepairs as $repair)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-{{ $repair['type'] === 'batch' ? 'primary' : 'secondary' }}">
                                                        {{ ucfirst($repair['type']) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $repair['status'] === 'success' ? 'success' : ($repair['status'] === 'partial_success' ? 'warning' : 'danger') }}">
                                                        {{ str_replace('_', ' ', ucfirst($repair['status'])) }}
                                                    </span>
                                                </td>
                                                <td>{{ $repair['description'] }}</td>
                                                <td>{{ \Carbon\Carbon::parse($repair['created_at'])->diffForHumans() }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="viewRepairDetails('{{ $repair['correlation_id'] }}')">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent repair operations found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Individual Content Repair -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-search"></i> Individual Content Repair
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="individualRepairForm" class="row g-3">
                                <div class="col-md-8">
                                    <label for="contentId" class="form-label">Content ID</label>
                                    <input type="number" class="form-control" id="contentId" name="content_id" 
                                           placeholder="Enter content ID to diagnose or repair" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary" onclick="diagnoseContent()">
                                            <i class="fas fa-stethoscope"></i> Diagnose
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="repairContent()">
                                            <i class="fas fa-wrench"></i> Repair
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <div id="individualRepairResults" class="mt-3" style="display: none;">
                                <!-- Results will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Batch Repair Modal -->
<div class="modal fade" id="batchRepairModal" tabindex="-1" aria-labelledby="batchRepairModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchRepairModalLabel">Batch Repair Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="batchRepairForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contentTypes" class="form-label">Content Types</label>
                                <select class="form-select" id="contentTypes" name="content_types[]" multiple>
                                    <option value="image">Image</option>
                                    <option value="document">Document</option>
                                    <option value="video">Video</option>
                                    <option value="audio">Audio</option>
                                </select>
                                <div class="form-text">Leave empty to include all types</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="storageDisk" class="form-label">Storage Disk</label>
                                <select class="form-select" id="storageDisk" name="storage_disk">
                                    <option value="">All Disks</option>
                                    <option value="public">Public</option>
                                    <option value="protected">Protected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="limit" class="form-label">Limit</label>
                                <input type="number" class="form-control" id="limit" name="limit" 
                                       value="100" min="1" max="1000">
                                <div class="form-text">Maximum number of records to process</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="createdAfter" class="form-label">Created After</label>
                                <input type="date" class="form-control" id="createdAfter" name="created_after">
                                <div class="form-text">Only process files created after this date</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dryRun" name="dry_run" checked>
                            <label class="form-check-label" for="dryRun">
                                Dry Run (Preview changes without applying them)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="startBatchRepair()">
                    <i class="fas fa-play"></i> Start Repair
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Repair Results Modal -->
<div class="modal fade" id="repairResultsModal" tabindex="-1" aria-labelledby="repairResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repairResultsModalLabel">Repair Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="repairResultsContent">
                    <!-- Results will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize storage distribution chart
@if($stats && isset($stats['storage_distribution']))
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('storageDistributionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Public', 'Protected', 'Unspecified'],
            datasets: [{
                data: [
                    {{ $stats['storage_distribution']['public'] }},
                    {{ $stats['storage_distribution']['protected'] }},
                    {{ $stats['storage_distribution']['unspecified'] }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
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
});
@endif

// Refresh dashboard
function refreshDashboard() {
    window.location.reload();
}

// Diagnose individual content
function diagnoseContent() {
    const contentId = document.getElementById('contentId').value;
    if (!contentId) {
        alert('Please enter a content ID');
        return;
    }
    
    const resultsDiv = document.getElementById('individualRepairResults');
    resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Diagnosing...</div>';
    resultsDiv.style.display = 'block';
    
    fetch(`/admin/file-repair/diagnose/${contentId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDiagnosticResults(data);
        } else {
            resultsDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = `<div class="alert alert-danger">Network error: ${error.message}</div>`;
    });
}

// Repair individual content
function repairContent() {
    const contentId = document.getElementById('contentId').value;
    if (!contentId) {
        alert('Please enter a content ID');
        return;
    }
    
    if (!confirm('Are you sure you want to repair this content? This action cannot be undone.')) {
        return;
    }
    
    const resultsDiv = document.getElementById('individualRepairResults');
    resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Repairing...</div>';
    resultsDiv.style.display = 'block';
    
    fetch(`/admin/file-repair/repair/${contentId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayRepairResults(data);
        } else {
            resultsDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = `<div class="alert alert-danger">Network error: ${error.message}</div>`;
    });
}

// Display diagnostic results
function displayDiagnosticResults(data) {
    const diagnostic = data.diagnostic;
    let html = `
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Diagnostic Results for Content ID: ${data.content_id}</h6>
                <small class="text-muted">Correlation ID: ${data.correlation_id}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>File Status</h6>
                        <ul class="list-unstyled">
                            <li><strong>File Exists:</strong> 
                                <span class="badge badge-${diagnostic.file_exists ? 'success' : 'danger'}">
                                    ${diagnostic.file_exists ? 'Yes' : 'No'}
                                </span>
                            </li>
                            <li><strong>Has Inconsistencies:</strong> 
                                <span class="badge badge-${diagnostic.has_inconsistencies ? 'warning' : 'success'}">
                                    ${diagnostic.has_inconsistencies ? 'Yes' : 'No'}
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Actual Location</h6>
                        ${diagnostic.actual_location ? `
                            <ul class="list-unstyled">
                                <li><strong>Path:</strong> ${diagnostic.actual_location.path}</li>
                                <li><strong>Disk:</strong> ${diagnostic.actual_location.disk}</li>
                                <li><strong>Size:</strong> ${diagnostic.actual_location.size} bytes</li>
                            </ul>
                        ` : '<p class="text-muted">No file found</p>'}
                    </div>
                </div>
                
                ${diagnostic.inconsistencies.length > 0 ? `
                    <div class="mt-3">
                        <h6>Inconsistencies</h6>
                        <ul class="list-group">
                            ${diagnostic.inconsistencies.map(inc => `<li class="list-group-item">${inc}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
                
                ${diagnostic.recommendations.length > 0 ? `
                    <div class="mt-3">
                        <h6>Recommendations</h6>
                        <ul class="list-group">
                            ${diagnostic.recommendations.map(rec => `<li class="list-group-item">${rec}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('individualRepairResults').innerHTML = html;
}

// Display repair results
function displayRepairResults(data) {
    const repair = data.repair_result;
    let html = `
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Repair Results for Content ID: ${data.content_id}</h6>
                <small class="text-muted">Correlation ID: ${data.correlation_id}</small>
            </div>
            <div class="card-body">
                <div class="alert alert-${data.success ? 'success' : 'danger'}">
                    <strong>${data.success ? 'Success' : 'Failed'}:</strong> ${repair.description}
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Repair Details</h6>
                        <ul class="list-unstyled">
                            <li><strong>Action:</strong> ${repair.action}</li>
                            <li><strong>Has Changes:</strong> 
                                <span class="badge badge-${repair.has_changes ? 'info' : 'secondary'}">
                                    ${repair.has_changes ? 'Yes' : 'No'}
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        ${repair.error ? `
                            <h6>Error Details</h6>
                            <div class="alert alert-danger">
                                ${repair.error}
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                ${repair.changes && Object.keys(repair.changes).length > 0 ? `
                    <div class="mt-3">
                        <h6>Changes Made</h6>
                        <pre class="bg-light p-3 rounded">${JSON.stringify(repair.changes, null, 2)}</pre>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('individualRepairResults').innerHTML = html;
}

// Start batch repair
function startBatchRepair() {
    const form = document.getElementById('batchRepairForm');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key.endsWith('[]')) {
            const arrayKey = key.slice(0, -2);
            if (!data[arrayKey]) data[arrayKey] = [];
            data[arrayKey].push(value);
        } else {
            data[key] = value;
        }
    }
    
    // Handle checkboxes
    data.dry_run = document.getElementById('dryRun').checked;
    
    // Close modal and show progress
    const modal = bootstrap.Modal.getInstance(document.getElementById('batchRepairModal'));
    modal.hide();
    
    // Show results modal with loading
    const resultsModal = new bootstrap.Modal(document.getElementById('repairResultsModal'));
    document.getElementById('repairResultsModalLabel').textContent = 'Batch Repair Progress';
    document.getElementById('repairResultsContent').innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
            <p>Processing batch repair...</p>
        </div>
    `;
    resultsModal.show();
    
    fetch('/admin/file-repair/batch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayBatchRepairResults(data);
        } else {
            document.getElementById('repairResultsContent').innerHTML = `
                <div class="alert alert-danger">Error: ${data.error}</div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('repairResultsContent').innerHTML = `
            <div class="alert alert-danger">Network error: ${error.message}</div>
        `;
    });
}

// Display batch repair results
function displayBatchRepairResults(data) {
    const result = data.batch_result;
    const html = `
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4>${result.total_processed}</h4>
                        <p class="mb-0">Total Processed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4>${result.successful_repairs}</h4>
                        <p class="mb-0">Successful</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4>${result.failed_repairs}</h4>
                        <p class="mb-0">Failed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4>${result.success_rate}%</h4>
                        <p class="mb-0">Success Rate</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="alert alert-${data.dry_run ? 'info' : 'success'}">
                <strong>${data.dry_run ? 'Dry Run Completed' : 'Batch Repair Completed'}</strong>
                <br>
                ${data.dry_run ? 'No changes were made. This was a preview of what would happen.' : 'All repairs have been applied.'}
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>Summary</h6>
                    <ul class="list-unstyled">
                        <li><strong>Duration:</strong> ${result.duration} seconds</li>
                        <li><strong>Database Updates:</strong> ${result.database_updates}</li>
                        <li><strong>Files Not Found:</strong> ${result.files_not_found}</li>
                        <li><strong>Overall Status:</strong> 
                            <span class="badge badge-${result.overall_status === 'success' ? 'success' : 'warning'}">
                                ${result.overall_status}
                            </span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Operation Details</h6>
                    <ul class="list-unstyled">
                        <li><strong>Correlation ID:</strong> <code>${data.correlation_id}</code></li>
                        <li><strong>Timestamp:</strong> ${data.timestamp}</li>
                        <li><strong>Mode:</strong> ${data.dry_run ? 'Dry Run' : 'Live Repair'}</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('repairResultsContent').innerHTML = html;
}

// Generate missing files report
function generateMissingFilesReport() {
    if (!confirm('Generate a missing files report? This may take a few minutes for large datasets.')) {
        return;
    }
    
    fetch('/admin/file-repair/missing-files-report', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            export_format: 'json'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show report in modal
            const modal = new bootstrap.Modal(document.getElementById('repairResultsModal'));
            document.getElementById('repairResultsModalLabel').textContent = 'Missing Files Report';
            document.getElementById('repairResultsContent').innerHTML = `
                <div class="alert alert-info">
                    <strong>Report Generated:</strong> ${data.report.summary.missing_files} missing files found out of ${data.report.summary.total_checked} checked.
                </div>
                <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">${JSON.stringify(data.report, null, 2)}</pre>
            `;
            modal.show();
        } else {
            alert('Error generating report: ' + data.error);
        }
    })
    .catch(error => {
        alert('Network error: ' + error.message);
    });
}

// View repair details
function viewRepairDetails(correlationId) {
    fetch(`/admin/file-repair/status/${correlationId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = new bootstrap.Modal(document.getElementById('repairResultsModal'));
            document.getElementById('repairResultsModalLabel').textContent = 'Repair Operation Details';
            document.getElementById('repairResultsContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Operation Status</h6>
                        <ul class="list-unstyled">
                            <li><strong>Status:</strong> 
                                <span class="badge badge-${data.status.status === 'completed' ? 'success' : 'info'}">
                                    ${data.status.status}
                                </span>
                            </li>
                            <li><strong>Progress:</strong> ${data.status.progress}%</li>
                            <li><strong>Message:</strong> ${data.status.message}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Timing</h6>
                        <ul class="list-unstyled">
                            <li><strong>Started:</strong> ${data.status.started_at}</li>
                            <li><strong>Completed:</strong> ${data.status.completed_at || 'In progress'}</li>
                            <li><strong>Correlation ID:</strong> <code>${correlationId}</code></li>
                        </ul>
                    </div>
                </div>
            `;
            modal.show();
        } else {
            alert('Error loading repair details: ' + data.error);
        }
    })
    .catch(error => {
        alert('Network error: ' + error.message);
    });
}
</script>
@endpush