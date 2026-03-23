@extends('layouts.app')

@section('title', 'System Settings')

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
                <i class="fas fa-cog me-2"></i>System Settings
            </h1>
            <div class="d-flex gap-2">
                <button class="creative-btn creative-btn-outline-primary" onclick="backupSettings()">
                    <i class="fas fa-save me-1"></i>Backup
                </button>
                <button class="creative-btn creative-btn-outline-warning" onclick="optimizeSystem()">
                    <i class="fas fa-rocket me-1"></i>Optimize
                </button>
                <button class="creative-btn creative-btn-outline-info" onclick="showSystemInfo()">
                    <i class="fas fa-info-circle me-1"></i>System Info
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Settings Form -->
    <div class="col-lg-8">
        <form id="settings-form">
            @csrf
            
            <!-- Application Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-globe me-2"></i>Application Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_name" class="form-label">Application Name</label>
                                <input type="text" class="form-control" id="app_name" name="app_name" 
                                       value="{{ $settings['app_name'] }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_url" class="form-label">Application URL</label>
                                <input type="url" class="form-control" id="app_url" name="app_url" 
                                       value="{{ $settings['app_url'] }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="app_timezone" name="app_timezone" required>
                                    <option value="UTC" {{ $settings['app_timezone'] === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ $settings['app_timezone'] === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ $settings['app_timezone'] === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ $settings['app_timezone'] === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ $settings['app_timezone'] === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    <option value="Europe/London" {{ $settings['app_timezone'] === 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Europe/Paris" {{ $settings['app_timezone'] === 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                    <option value="Asia/Tokyo" {{ $settings['app_timezone'] === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                           name="maintenance_mode" {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="maintenance_mode">
                                        Maintenance Mode
                                    </label>
                                </div>
                                <small class="text-muted">Enable to put the site in maintenance mode</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_driver" class="form-label">Mail Driver</label>
                                <select class="form-select" id="mail_driver" name="mail_driver" required>
                                    <option value="smtp" {{ $settings['mail_driver'] === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ $settings['mail_driver'] === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="mailgun" {{ $settings['mail_driver'] === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses" {{ $settings['mail_driver'] === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_host" class="form-label">Mail Host</label>
                                <input type="text" class="form-control" id="mail_host" name="mail_host" 
                                       value="{{ $settings['mail_host'] }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="mail_port" class="form-label">Mail Port</label>
                                <input type="number" class="form-control" id="mail_port" name="mail_port" 
                                       value="{{ $settings['mail_port'] }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="mail_username" class="form-label">Mail Username</label>
                                <input type="text" class="form-control" id="mail_username" name="mail_username" 
                                       value="{{ $settings['mail_username'] }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="mail_encryption" class="form-label">Encryption</label>
                                <select class="form-select" id="mail_encryption" name="mail_encryption">
                                    <option value="">None</option>
                                    <option value="tls" {{ $settings['mail_encryption'] === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ $settings['mail_encryption'] === 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_from_address" class="form-label">From Address</label>
                                <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" 
                                       value="{{ $settings['mail_from_address'] }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_from_name" class="form-label">From Name</label>
                                <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" 
                                       value="{{ $settings['mail_from_name'] }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-server me-2"></i>System Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cache_driver" class="form-label">Cache Driver</label>
                                <select class="form-select" id="cache_driver" name="cache_driver" required>
                                    <option value="file" {{ $settings['cache_driver'] === 'file' ? 'selected' : '' }}>File</option>
                                    <option value="redis" {{ $settings['cache_driver'] === 'redis' ? 'selected' : '' }}>Redis</option>
                                    <option value="memcached" {{ $settings['cache_driver'] === 'memcached' ? 'selected' : '' }}>Memcached</option>
                                    <option value="database" {{ $settings['cache_driver'] === 'database' ? 'selected' : '' }}>Database</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="session_driver" class="form-label">Session Driver</label>
                                <select class="form-select" id="session_driver" name="session_driver" required>
                                    <option value="file" {{ $settings['session_driver'] === 'file' ? 'selected' : '' }}>File</option>
                                    <option value="cookie" {{ $settings['session_driver'] === 'cookie' ? 'selected' : '' }}>Cookie</option>
                                    <option value="database" {{ $settings['session_driver'] === 'database' ? 'selected' : '' }}>Database</option>
                                    <option value="redis" {{ $settings['session_driver'] === 'redis' ? 'selected' : '' }}>Redis</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="queue_driver" class="form-label">Queue Driver</label>
                                <select class="form-select" id="queue_driver" name="queue_driver" required>
                                    <option value="sync" {{ $settings['queue_driver'] === 'sync' ? 'selected' : '' }}>Sync</option>
                                    <option value="database" {{ $settings['queue_driver'] === 'database' ? 'selected' : '' }}>Database</option>
                                    <option value="redis" {{ $settings['queue_driver'] === 'redis' ? 'selected' : '' }}>Redis</option>
                                    <option value="sqs" {{ $settings['queue_driver'] === 'sqs' ? 'selected' : '' }}>Amazon SQS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Upload Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>File Upload Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="file_max_size" class="form-label">Max File Size (MB)</label>
                                <input type="number" class="form-control" id="file_max_size" name="file_max_size" 
                                       value="{{ $settings['file_max_size'] }}" min="1" max="2048" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="allowed_file_types" class="form-label">Allowed File Types</label>
                                <input type="text" class="form-control" id="allowed_file_types" name="allowed_file_types" 
                                       value="{{ $settings['allowed_file_types'] }}" required>
                                <small class="text-muted">Comma-separated list (e.g., jpg,png,pdf,doc)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="registration_enabled" 
                                           name="registration_enabled" {{ $settings['registration_enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="registration_enabled">
                                        Allow User Registration
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_verification" 
                                           name="email_verification" {{ $settings['email_verification'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_verification">
                                        Require Email Verification
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="password_reset_enabled" 
                                           name="password_reset_enabled" {{ $settings['password_reset_enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="password_reset_enabled">
                                        Allow Password Reset
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_login_attempts" class="form-label">Max Login Attempts</label>
                                <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                       value="{{ $settings['max_login_attempts'] }}" min="1" max="10" required>
                            </div>
                            <div class="mb-3">
                                <label for="lockout_duration" class="form-label">Lockout Duration (minutes)</label>
                                <input type="number" class="form-control" id="lockout_duration" name="lockout_duration" 
                                       value="{{ $settings['lockout_duration'] }}" min="1" max="60" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="creative-btn creative-btn-outline-secondary" onclick="resetForm()">Reset</button>
                <button type="submit" class="creative-btn creative-btn-outline-primary">
                    <i class="fas fa-save me-1"></i>Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- System Actions -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">System Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="creative-btn creative-btn-outline-primary" onclick="clearCache('all')">
                        <i class="fas fa-broom me-2"></i>Clear All Cache
                    </button>
                    <button class="creative-btn creative-btn-outline-info" onclick="clearCache('config')">
                        <i class="fas fa-cog me-2"></i>Clear Config Cache
                    </button>
                    <button class="creative-btn creative-btn-outline-success" onclick="clearCache('view')">
                        <i class="fas fa-eye me-2"></i>Clear View Cache
                    </button>
                    <button class="creative-btn creative-btn-outline-warning" onclick="optimizeSystem()">
                        <i class="fas fa-rocket me-2"></i>Optimize System
                    </button>
                    <button class="creative-btn creative-btn-outline-danger" onclick="toggleMaintenance()">
                        <i class="fas fa-tools me-2"></i>Toggle Maintenance
                    </button>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">System Status</h6>
            </div>
            <div class="card-body" id="system-status">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Backup & Restore</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="creative-btn creative-btn-outline-primary" onclick="backupSettings()">
                        <i class="fas fa-save me-2"></i>Create Backup
                    </button>
                    <button class="creative-btn creative-btn-outline-secondary" onclick="showBackups()">
                        <i class="fas fa-history me-2"></i>View Backups
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Handle checkboxes
    data.maintenance_mode = document.getElementById('maintenance_mode').checked;
    data.registration_enabled = document.getElementById('registration_enabled').checked;
    data.email_verification = document.getElementById('email_verification').checked;
    data.password_reset_enabled = document.getElementById('password_reset_enabled').checked;
    
    fetch('{{ route('admin.settings.update') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            toastr.success(data.message);
        } else {
            toastr.error(data.error || 'Failed to update settings');
        }
    })
    .catch(error => {
        console.error('Settings save error:', error);
        toastr.error('Network error: Could not reach the server. Please ensure the application is running.');
    });
});

function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('settings-form').reset();
    }
}

function clearCache(type) {
    fetch('{{ route('admin.settings.clear-cache') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ type: type })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            toastr.success(data.message);
        } else {
            toastr.error(data.error || 'Failed to clear cache');
        }
    })
    .catch(error => {
        console.error('Clear cache error:', error);
        toastr.error('Network error: Could not reach the server. Please ensure the application is running.');
    });
}

function optimizeSystem() {
    if (confirm('This will optimize the system by caching configurations. Continue?')) {
        fetch('{{ route('admin.settings.optimize') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                toastr.success(data.message);
            } else {
                toastr.error(data.error || 'Optimization failed');
            }
        })
        .catch(error => {
            console.error('Optimize error:', error);
            toastr.error('Network error: Could not reach the server. Please ensure the application is running.');
        });
    }
}

function toggleMaintenance() {
    const isEnabled = document.getElementById('maintenance_mode').checked;
    const action = isEnabled ? 'disable' : 'enable';
    
    if (confirm(`Are you sure you want to ${action} maintenance mode?`)) {
        fetch('{{ route('admin.settings.toggle-maintenance') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                enable: !isEnabled,
                message: 'System maintenance in progress',
                retry: 60
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                toastr.success(data.message);
                document.getElementById('maintenance_mode').checked = data.maintenance_mode;
            } else {
                toastr.error(data.error || 'Failed to toggle maintenance mode');
            }
        })
        .catch(error => {
            console.error('Maintenance toggle error:', error);
            toastr.error('Network error: Could not reach the server. Please ensure the application is running.');
        });
    }
}

function backupSettings() {
    fetch('{{ route('admin.settings.backup') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            toastr.success(data.message);
            if (data.download_url) {
                window.open(data.download_url);
            }
        } else {
            toastr.error(data.error || 'Backup creation failed');
        }
    })
    .catch(error => {
        console.error('Backup error:', error);
        toastr.error('Network error: Could not reach the server. Please ensure the application is running.');
    });
}

function showSystemInfo() {
    fetch('{{ route('admin.settings.system-info') }}', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        let html = '<div class="table-responsive"><table class="table table-sm">';
        for (const [key, value] of Object.entries(data)) {
            html += `<tr><td><strong>${key.replace(/_/g, ' ').toUpperCase()}</strong></td><td>${value}</td></tr>`;
        }
        html += '</table></div>';
        
        // Show in modal
        const modal = `
            <div class="modal fade" id="systemInfoModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">System Information</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${html}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modal);
        const systemInfoModal = new bootstrap.Modal(document.getElementById('systemInfoModal'));
        systemInfoModal.show();
        
        document.getElementById('systemInfoModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    })
    .catch(error => {
        console.error('System info error:', error);
        toastr.error('Network error: Could not reach the server. Please ensure the application is running.');
    });
}

function showBackups() {
    toastr.info('Backup management feature coming soon!');
}

function loadSystemStatus() {
    // This would load current system status
    document.getElementById('system-status').innerHTML = `
        <div class="mb-2">
            <div class="d-flex justify-content-between">
                <span>Application</span>
                <span class="badge bg-success">Running</span>
            </div>
        </div>
        <div class="mb-2">
            <div class="d-flex justify-content-between">
                <span>Database</span>
                <span class="badge bg-success">Connected</span>
            </div>
        </div>
        <div class="mb-2">
            <div class="d-flex justify-content-between">
                <span>Cache</span>
                <span class="badge bg-success">Active</span>
            </div>
        </div>
        <div class="mb-2">
            <div class="d-flex justify-content-between">
                <span>Queue</span>
                <span class="badge bg-warning">Sync</span>
            </div>
        </div>
    `;
}

// Load system status on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSystemStatus();
});
</script>
@endpush