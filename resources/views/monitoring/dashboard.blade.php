<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Monitoring Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .status-good { @apply bg-green-100 text-green-800 border-green-200; }
        .status-warning { @apply bg-yellow-100 text-yellow-800 border-yellow-200; }
        .status-critical { @apply bg-red-100 text-red-800 border-red-200; }
        .status-info { @apply bg-blue-100 text-blue-800 border-blue-200; }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .widget-card {
            @apply bg-white rounded-lg shadow-md border border-gray-200 p-6;
        }
        
        .metric-value {
            @apply text-2xl font-bold;
        }
        
        .metric-label {
            @apply text-sm text-gray-600;
        }
        
        .alert-item {
            @apply border-l-4 p-4 mb-3 rounded-r-lg;
        }
        
        .alert-critical {
            @apply border-red-500 bg-red-50;
        }
        
        .alert-warning {
            @apply border-yellow-500 bg-yellow-50;
        }
        
        .alert-info {
            @apply border-blue-500 bg-blue-50;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-chart-line mr-2 text-blue-600"></i>
                            Upload Monitoring Dashboard
                        </h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Period Selector -->
                        <select id="periodSelector" class="border border-gray-300 rounded-md px-3 py-2 bg-white">
                            <option value="hour" {{ $period === 'hour' ? 'selected' : '' }}>Last Hour</option>
                            <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Last 24 Hours</option>
                            <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Last Week</option>
                            <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Last Month</option>
                        </select>
                        
                        <!-- Refresh Button -->
                        <button id="refreshBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Refresh
                        </button>
                        
                        <!-- Auto-refresh Toggle -->
                        <label class="flex items-center">
                            <input type="checkbox" id="autoRefresh" class="mr-2" {{ $config['auto_refresh'] ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">Auto-refresh</span>
                        </label>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Status Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- System Health Card -->
                <div class="widget-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="metric-label">System Health</p>
                            <p class="metric-value" id="systemHealthValue">
                                {{ ucfirst($config['widgets']['system_health']['data']['overall_status'] ?? 'unknown') }}
                            </p>
                        </div>
                        <div class="text-3xl">
                            <i class="fas fa-heartbeat" id="systemHealthIcon"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-gray-500" id="systemHealthDetails">
                            Last updated: {{ now()->format('H:i:s') }}
                        </div>
                    </div>
                </div>

                <!-- Upload Stats Card -->
                <div class="widget-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="metric-label">Success Rate</p>
                            <p class="metric-value text-green-600" id="successRateValue">
                                {{ $config['widgets']['upload_stats']['data']['success_rate'] ?? 0 }}%
                            </p>
                        </div>
                        <div class="text-3xl text-green-600">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-gray-500">
                            <span id="totalUploads">{{ $config['widgets']['upload_stats']['data']['total_uploads'] ?? 0 }}</span> total uploads
                        </div>
                    </div>
                </div>

                <!-- Performance Summary Card -->
                <div class="widget-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="metric-label">Avg Duration</p>
                            <p class="metric-value" id="avgDurationValue">
                                {{ $config['widgets']['performance_summary']['data']['avg_duration']['formatted'] ?? '0s' }}
                            </p>
                        </div>
                        <div class="text-3xl text-blue-600">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-gray-500">
                            Memory: <span id="avgMemoryValue">{{ $config['widgets']['performance_summary']['data']['avg_memory']['formatted'] ?? '0 MB' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Active Alerts Card -->
                <div class="widget-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="metric-label">Active Alerts</p>
                            <p class="metric-value" id="activeAlertsCount">
                                {{ $config['widgets']['active_alerts']['data']['total_alerts'] ?? 0 }}
                            </p>
                        </div>
                        <div class="text-3xl" id="alertsIcon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-gray-500">
                            Critical: <span id="criticalAlertsCount" class="text-red-600">{{ $config['widgets']['active_alerts']['data']['critical_alerts'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Success Rate Chart -->
                <div class="widget-card">
                    <h3 class="text-lg font-semibold mb-4">Upload Success Rate</h3>
                    <div class="chart-container">
                        <canvas id="successRateChart"></canvas>
                    </div>
                </div>

                <!-- Performance Trends Chart -->
                <div class="widget-card">
                    <h3 class="text-lg font-semibold mb-4">Performance Trends</h3>
                    <div class="chart-container">
                        <canvas id="performanceTrendsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Resource Usage and Failure Patterns Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Resource Usage -->
                <div class="widget-card">
                    <h3 class="text-lg font-semibold mb-4">Resource Usage</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Disk Usage</span>
                                <span id="diskUsagePercent">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" id="diskUsageBar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Memory Usage</span>
                                <span id="memoryUsagePercent">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-purple-600 h-2 rounded-full" id="memoryUsageBar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failure Patterns -->
                <div class="widget-card">
                    <h3 class="text-lg font-semibold mb-4">Failure Patterns</h3>
                    <div class="chart-container">
                        <canvas id="failurePatternsChart"></canvas>
                    </div>
                </div>

                <!-- Upload Volume -->
                <div class="widget-card">
                    <h3 class="text-lg font-semibold mb-4">Upload Volume</h3>
                    <div class="chart-container">
                        <canvas id="uploadVolumeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recommendations and Recent Activity Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recommendations -->
                <div class="widget-card">
                    <h3 class="text-lg font-semibold mb-4">Performance Recommendations</h3>
                    <div id="recommendationsList" class="space-y-3">
                        @if(isset($config['widgets']['recommendations']['data']) && count($config['widgets']['recommendations']['data']) > 0)
                            @foreach($config['widgets']['recommendations']['data'] as $recommendation)
                                <div class="border-l-4 border-blue-500 pl-4 py-2">
                                    <h4 class="font-medium text-gray-900">{{ $recommendation['title'] ?? 'Recommendation' }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $recommendation['description'] ?? '' }}</p>
                                    @if(isset($recommendation['actions']) && is_array($recommendation['actions']))
                                        <ul class="text-xs text-gray-500 mt-2 list-disc list-inside">
                                            @foreach($recommendation['actions'] as $action)
                                                <li>{{ $action }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                                <p>No recommendations at this time</p>
                                <p class="text-sm">System is performing well</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="widget-card">
                    <h3 class="text-lg font-semibold mb-4">Recent Upload Activity</h3>
                    <div id="recentActivityList" class="space-y-3 max-h-96 overflow-y-auto">
                        @if(isset($config['widgets']['recent_activity']['data']['activities']))
                            @foreach($config['widgets']['recent_activity']['data']['activities'] as $activity)
                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        @if($activity['type'] === 'upload_success')
                                            <i class="fas fa-check-circle text-green-500"></i>
                                        @elseif($activity['type'] === 'upload_failure')
                                            <i class="fas fa-times-circle text-red-500"></i>
                                        @else
                                            <i class="fas fa-info-circle text-blue-500"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $activity['user'] ?? 'Unknown user' }}</p>
                                        @if(isset($activity['details']))
                                            <div class="text-xs text-gray-400 mt-1">
                                                @foreach($activity['details'] as $key => $value)
                                                    <span class="mr-3">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-shrink-0 text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-history text-4xl text-gray-400 mb-2"></i>
                                <p>No recent activity</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>

        <!-- Alerts Panel (Slide-out) -->
        <div id="alertsPanel" class="fixed inset-y-0 right-0 w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Active Alerts</h2>
                    <button id="closeAlertsPanel" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    <div id="alertsPanelContent">
                        <!-- Alerts will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay for alerts panel -->
        <div id="alertsPanelOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
    </div>

    <script>
        // Dashboard configuration from server
        const dashboardConfig = @json($config);
        const currentPeriod = '{{ $period }}';
        
        // Chart instances
        let charts = {};
        
        // Auto-refresh settings
        let autoRefreshInterval = null;
        const refreshIntervalMs = (dashboardConfig.refresh_interval || 30) * 1000;
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            initializeEventListeners();
            updateSystemStatus();
            
            if (dashboardConfig.auto_refresh) {
                startAutoRefresh();
            }
        });
        
        function initializeCharts() {
            // Success Rate Chart
            const successRateCtx = document.getElementById('successRateChart').getContext('2d');
            charts.successRate = new Chart(successRateCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Success Rate (%)',
                        data: [],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Performance Trends Chart
            const performanceCtx = document.getElementById('performanceTrendsChart').getContext('2d');
            charts.performance = new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Duration (s)',
                            data: [],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            yAxisID: 'y'
                        },
                        {
                            label: 'Memory (MB)',
                            data: [],
                            borderColor: '#8B5CF6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
            
            // Failure Patterns Chart
            const failureCtx = document.getElementById('failurePatternsChart').getContext('2d');
            charts.failurePatterns = new Chart(failureCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#EF4444',
                            '#F59E0B',
                            '#8B5CF6',
                            '#3B82F6',
                            '#10B981'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10
                            }
                        }
                    }
                }
            });
            
            // Upload Volume Chart
            const volumeCtx = document.getElementById('uploadVolumeChart').getContext('2d');
            charts.uploadVolume = new Chart(volumeCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Uploads',
                        data: [],
                        backgroundColor: '#3B82F6',
                        borderColor: '#2563EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Load initial chart data
            loadChartData();
        }
        
        function initializeEventListeners() {
            // Period selector
            document.getElementById('periodSelector').addEventListener('change', function() {
                const newPeriod = this.value;
                window.location.href = `?period=${newPeriod}`;
            });
            
            // Refresh button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                refreshDashboard();
            });
            
            // Auto-refresh toggle
            document.getElementById('autoRefresh').addEventListener('change', function() {
                if (this.checked) {
                    startAutoRefresh();
                } else {
                    stopAutoRefresh();
                }
            });
            
            // Alerts panel
            document.getElementById('activeAlertsCount').addEventListener('click', function() {
                showAlertsPanel();
            });
            
            document.getElementById('closeAlertsPanel').addEventListener('click', function() {
                hideAlertsPanel();
            });
            
            document.getElementById('alertsPanelOverlay').addEventListener('click', function() {
                hideAlertsPanel();
            });
        }
        
        function loadChartData() {
            // This would typically load data from the API
            // For now, use the data from the server-side config
            
            if (dashboardConfig.charts && dashboardConfig.charts.success_rate_chart) {
                updateSuccessRateChart(dashboardConfig.charts.success_rate_chart.data);
            }
            
            if (dashboardConfig.charts && dashboardConfig.charts.performance_trends_chart) {
                updatePerformanceTrendsChart(dashboardConfig.charts.performance_trends_chart.data);
            }
            
            if (dashboardConfig.charts && dashboardConfig.charts.failure_patterns_chart) {
                updateFailurePatternsChart(dashboardConfig.charts.failure_patterns_chart.data);
            }
            
            if (dashboardConfig.charts && dashboardConfig.charts.upload_volume_chart) {
                updateUploadVolumeChart(dashboardConfig.charts.upload_volume_chart.data);
            }
        }
        
        function updateSuccessRateChart(data) {
            if (!data || !data.series || !data.series[0]) return;
            
            const chartData = data.series[0].data;
            const labels = chartData.map(item => new Date(item.timestamp).toLocaleTimeString());
            const values = chartData.map(item => item.value);
            
            charts.successRate.data.labels = labels;
            charts.successRate.data.datasets[0].data = values;
            charts.successRate.update();
        }
        
        function updatePerformanceTrendsChart(data) {
            if (!data || !data.series) return;
            
            const labels = data.series[0].data.map(item => new Date(item.timestamp).toLocaleTimeString());
            
            charts.performance.data.labels = labels;
            charts.performance.data.datasets[0].data = data.series[0].data.map(item => item.value);
            
            if (data.series[1]) {
                charts.performance.data.datasets[1].data = data.series[1].data.map(item => item.value);
            }
            
            charts.performance.update();
        }
        
        function updateFailurePatternsChart(data) {
            if (!data || !data.data) return;
            
            const labels = data.data.map(item => item.name);
            const values = data.data.map(item => item.value);
            
            charts.failurePatterns.data.labels = labels;
            charts.failurePatterns.data.datasets[0].data = values;
            charts.failurePatterns.update();
        }
        
        function updateUploadVolumeChart(data) {
            if (!data || !data.series || !data.series[0]) return;
            
            const chartData = data.series[0].data;
            const labels = chartData.map(item => new Date(item.timestamp).toLocaleTimeString());
            const values = chartData.map(item => item.value);
            
            charts.uploadVolume.data.labels = labels;
            charts.uploadVolume.data.datasets[0].data = values;
            charts.uploadVolume.update();
        }
        
        function updateSystemStatus() {
            // Update system health indicator
            const systemHealth = dashboardConfig.widgets.system_health.data.overall_status;
            const healthIcon = document.getElementById('systemHealthIcon');
            
            healthIcon.className = 'fas fa-heartbeat';
            if (systemHealth === 'good') {
                healthIcon.classList.add('text-green-500');
            } else if (systemHealth === 'warning') {
                healthIcon.classList.add('text-yellow-500');
            } else if (systemHealth === 'critical') {
                healthIcon.classList.add('text-red-500');
            } else {
                healthIcon.classList.add('text-gray-500');
            }
            
            // Update resource usage bars
            if (dashboardConfig.charts && dashboardConfig.charts.resource_usage_chart) {
                const resourceData = dashboardConfig.charts.resource_usage_chart.data;
                if (resourceData.gauges) {
                    resourceData.gauges.forEach(gauge => {
                        if (gauge.name === 'Disk Usage') {
                            document.getElementById('diskUsagePercent').textContent = gauge.value + '%';
                            document.getElementById('diskUsageBar').style.width = gauge.value + '%';
                            
                            const bar = document.getElementById('diskUsageBar');
                            bar.className = 'h-2 rounded-full';
                            if (gauge.value > 90) {
                                bar.classList.add('bg-red-600');
                            } else if (gauge.value > 80) {
                                bar.classList.add('bg-yellow-600');
                            } else {
                                bar.classList.add('bg-blue-600');
                            }
                        } else if (gauge.name === 'Memory Usage') {
                            document.getElementById('memoryUsagePercent').textContent = gauge.value + '%';
                            document.getElementById('memoryUsageBar').style.width = gauge.value + '%';
                            
                            const bar = document.getElementById('memoryUsageBar');
                            bar.className = 'h-2 rounded-full';
                            if (gauge.value > 90) {
                                bar.classList.add('bg-red-600');
                            } else if (gauge.value > 80) {
                                bar.classList.add('bg-yellow-600');
                            } else {
                                bar.classList.add('bg-purple-600');
                            }
                        }
                    });
                }
            }
        }
        
        function refreshDashboard() {
            const refreshBtn = document.getElementById('refreshBtn');
            const originalText = refreshBtn.innerHTML;
            
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Refreshing...';
            refreshBtn.disabled = true;
            
            // Reload the page with current period
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
        
        function startAutoRefresh() {
            stopAutoRefresh(); // Clear any existing interval
            autoRefreshInterval = setInterval(() => {
                refreshDashboard();
            }, refreshIntervalMs);
        }
        
        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }
        
        function showAlertsPanel() {
            document.getElementById('alertsPanel').classList.remove('translate-x-full');
            document.getElementById('alertsPanelOverlay').classList.remove('hidden');
            loadActiveAlerts();
        }
        
        function hideAlertsPanel() {
            document.getElementById('alertsPanel').classList.add('translate-x-full');
            document.getElementById('alertsPanelOverlay').classList.add('hidden');
        }
        
        function loadActiveAlerts() {
            const alertsContent = document.getElementById('alertsPanelContent');
            alertsContent.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading alerts...</div>';
            
            // This would typically fetch from the API
            // For now, show the alerts from the config
            const alerts = dashboardConfig.widgets.active_alerts.data.recent_alerts || [];
            
            if (alerts.length === 0) {
                alertsContent.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                        <p>No active alerts</p>
                        <p class="text-sm">System is running smoothly</p>
                    </div>
                `;
                return;
            }
            
            let alertsHTML = '';
            alerts.forEach(alert => {
                const alertClass = `alert-${alert.severity || 'info'}`;
                alertsHTML += `
                    <div class="alert-item ${alertClass}">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-medium">${alert.type || 'Alert'}</h4>
                            <span class="text-xs px-2 py-1 rounded-full bg-white bg-opacity-50">
                                ${(alert.severity || 'info').toUpperCase()}
                            </span>
                        </div>
                        <p class="text-sm mb-2">${alert.message || 'No message'}</p>
                        <div class="text-xs text-gray-600">
                            ${new Date(alert.timestamp || Date.now()).toLocaleString()}
                        </div>
                    </div>
                `;
            });
            
            alertsContent.innerHTML = alertsHTML;
        }
        
        // Handle page visibility changes to pause/resume auto-refresh
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else if (document.getElementById('autoRefresh').checked) {
                startAutoRefresh();
            }
        });
    </script>
</body>
</html>