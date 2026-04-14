@extends('layouts.wazuh')

@section('title', 'Agent Detail - One For All')

@section('content')

@if(!$agent)
<div class="container-fluid py-5">
  <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
    <i class="mdi mdi-alert-circle-outline display-4"></i>
    <div>
      <h5 class="alert-heading mb-1">Agent Not Found</h5>
      <p class="mb-0">Unable to load agent details. The agent may no longer exist or access has been denied.</p>
      <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-danger mt-2">
        <i class="mdi mdi-arrow-left me-1"></i> Back to Agents
      </a>
    </div>
  </div>
</div>
@else
<!-- SECONDARY NAV -->
<div class="bg-dark border-bottom border-secondary">
  <div class="d-flex align-items-center px-3">
    <ul class="nav flex-nowrap overflow-auto">
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.detail', $agent->id_agent) }}">
          <span class="mdi mdi-home"></span> Details
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small active" href="{{ route('agent.security-events', $agent->id_agent) }}">
          <span class="mdi mdi-format-list-bulleted"></span> Security events
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.integrity-monitoring', $agent->id_agent) }}">
          <span class="mdi mdi-shield"></span> Integrity monitoring
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.sca', $agent->id_agent) }}">
          <span class="mdi mdi-clock-outline"></span> SCA
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.vulnerabilities', $agent->id_agent) }}">
          <span class="mdi mdi-bug"></span> Vulnerabilities
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.mitre-attack', $agent->id_agent) }}">
          <span class="mdi mdi-target"></span> MITRE ATT&amp;CK
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="#">
          More <span class="mdi mdi-chevron-down"></span>
        </a>
      </li>
    </ul>
    <div class="ms-auto d-flex gap-2 flex-shrink-0 py-1">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent') }}" title="Back to Agents List">
            <span class="mdi mdi-arrow-left"></span> Back
        </a>
    </div>
  </div>
</div>

<div class="content-wrapper">
    <!-- TOOLBAR -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <!-- Date Range Picker -->
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="mdi mdi-calendar-outline"></span> <span id="timeRangeLabel">Last 24 hours</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="timeRangeDropdown">
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('15m', event)">Last 15 minutes</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('30m', event)">Last 30 minutes</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('1h', event)">Last 1 hour</a></li>
                <li><a class="dropdown-item active" href="#" onclick="updateTimeRange('24h', event)">Last 24 hours</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('7d', event)">Last 7 days</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('30d', event)">Last 30 days</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('90d', event)">Last 90 days</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('1y', event)">Last 1 year</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('today', event)">Today</a></li>
                <li><a class="dropdown-item" href="#" onclick="updateTimeRange('week', event)">This week</a></li>
            </ul>
        </div>
        <!-- Refresh Button -->
        <button class="btn btn-sm btn-outline-secondary" onclick="refreshData()" title="Refresh data">
            <span class="mdi mdi-refresh"></span>
        </button>
    </div>

    <!-- METRICS STRIP -->
    <div class="row mb-3 g-2">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-4 px-3 text-center">
                    <div class="text-muted fw-semibold small mb-3">Total</div>
                    <div class="display-5 fw-bold text-primary" id="metricTotal">{{ $metrics['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-4 px-3 text-center">
                    <div class="text-muted fw-semibold small mb-3">Level 12 or above</div>
                    <div class="display-5 fw-bold text-danger" id="metricLevel12">{{ $metrics['level12'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-4 px-3 text-center">
                    <div class="text-muted fw-semibold small mb-3">Authentication failure</div>
                    <div class="display-5 fw-bold text-warning" id="metricAuthFailure">{{ $metrics['auth_failure'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-4 px-3 text-center">
                    <div class="text-muted fw-semibold small mb-3">Authentication success</div>
                    <div class="display-5 fw-bold text-success" id="metricAuthSuccess">{{ $metrics['auth_success'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="row mb-3 g-3">
        <!-- Alert groups evolution -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Alert groups evolution</span>
                </div>
                <div class="card-body py-3" style="position: relative; height: 300px;">
                    <canvas id="alertGroupsChart"></canvas>
                    <p class="text-center mb-0 mt-2"><small class="text-muted">timestamp per 30 minutes</small></p>
                </div>
            </div>
        </div>
        <!-- Alerts -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Alerts</span>
                </div>
                <div class="card-body py-3" style="position: relative; height: 300px;">
                    <canvas id="alertsChart"></canvas>
                    <p class="text-center mb-0 mt-2"><small class="text-muted">timestamp per 30 minutes</small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 Cards -->
    <div class="row mb-3 g-3">
        <!-- Top 5 alerts -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Top 5 alerts</span>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height: 350px;">
                    <canvas id="top5AlertsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top 5 rule groups -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Top 5 rule groups</span>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height: 350px;">
                    <canvas id="top5RuleGroupsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top 5 PCI DSS Requirements -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Top 5 PCI DSS Requirements</span>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height: 350px;">
                    <canvas id="top5PCIDSSChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Alerts Table -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Alerts summary</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:20%">Time <span class="mdi mdi-arrow-up-down"></span></th>
                                <th style="width:15%">Rule ID</th>
                                <th style="width:35%">Description</th>
                                <th style="width:10%">Level</th>
                                <th style="width:10%">Count</th>
                                <th style="width:10%">Group</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAlerts ?? [] as $alert)
                            <tr>
                                <td class="text-muted" style="font-size:10px;">{{ $alert['timestamp'] ? \Carbon\Carbon::parse($alert['timestamp'])->format('M d, Y @<br>H:i:s') : 'N/A' }}</td>
                                <td><span class="badge bg-light text-dark">{{ $alert['rule_id'] }}</span></td>
                                <td><a href="#" class="text-primary text-decoration-none">{{ $alert['description'] }}</a></td>
                                <td>
                                    @php
                                        $level = $alert['level'];
                                        $levelColor = match(true) {
                                            $level >= 12 => 'danger',
                                            $level >= 9 => 'warning',
                                            $level >= 6 => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $levelColor }}">{{ $alert['level'] }}</span>
                                </td>
                                <td class="text-center fw-bold">{{ $alert['count'] }}</td>
                                <td><span class="badge bg-secondary">{{ $alert['groups'] }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">
                                    <i class="mdi mdi-information-outline me-1"></i>No alerts found in the selected time range
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center py-2 small">
                    <div>Rows per page: <select class="form-select form-select-sm d-inline-block w-auto">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select></div>
                    <div class="d-flex gap-1 align-items-center">
                        <span>1 – 10 of 361</span>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" title="Previous">‹</button>
                        <button class="btn btn-sm btn-primary py-0 px-2">1</button>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" title="Next">›</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups Summary Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">Groups summary</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40%">Group</th>
                                <th style="width:20%">Count</th>
                                <th style="width:20%">Trend</th>
                                <th style="width:20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-info">windows</span></td>
                                <td class="fw-bold text-primary">1500</td>
                                <td>
                                    <small class="text-success">
                                        <span class="mdi mdi-trending-up"></span> +12%
                                    </small>
                                </td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">windows_security</span></td>
                                <td class="fw-bold text-warning">900</td>
                                <td>
                                    <small class="text-danger">
                                        <span class="mdi mdi-trending-down"></span> -5%
                                    </small>
                                </td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">authentication_failed</span></td>
                                <td class="fw-bold text-danger">600</td>
                                <td>
                                    <small class="text-success">
                                        <span class="mdi mdi-trending-up"></span> +8%
                                    </small>
                                </td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-secondary">authentication_failure</span></td>
                                <td class="fw-bold text-secondary">400</td>
                                <td>
                                    <small class="text-success">
                                        <span class="mdi mdi-trending-up"></span> +3%
                                    </small>
                                </td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">ossec</span></td>
                                <td class="fw-bold text-success">200</td>
                                <td>
                                    <small class="text-muted">
                                        <span class="mdi mdi-minus"></span> No change
                                    </small>
                                </td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center py-2 small">
                    <div>Rows per page: <select class="form-select form-select-sm d-inline-block w-auto">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select></div>
                    <div class="d-flex gap-1 align-items-center">
                        <span>1 – 5 of 5</span>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2 disabled" title="Previous">‹</button>
                        <button class="btn btn-sm btn-primary py-0 px-2">1</button>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2 disabled" title="Next">›</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
let currentTimeRange = '{{ $timeRange ?? "24h" }}';
let chartInstances = {};
let agentId = '{{ $agent->id_agent ?? "" }}';

// Real data from OpenSearch
const securityData = {
  alertGroupsEvolution: @json($alertGroupsEvolution ?? ['labels' => [], 'datasets' => []]),
  alertsEvolutionByLevel: @json($alertsEvolutionByLevel ?? ['labels' => [], 'datasets' => []]),
  topAlerts: @json($topAlerts ?? ['labels' => [], 'data' => []]),
  topRuleGroups: @json($topRuleGroups ?? ['labels' => [], 'data' => []]),
  topPCIDSS: @json($topPCIDSS ?? ['labels' => [], 'data' => []]),
};

const timeRangeLabels = {
  '15m': 'Last 15 minutes',
  '30m': 'Last 30 minutes',
  '1h': 'Last 1 hour',
  '24h': 'Last 24 hours',
  '7d': 'Last 7 days',
  '30d': 'Last 30 days',
  '90d': 'Last 90 days',
  '1y': 'Last 1 year',
  'today': 'Today',
  'week': 'This week'
};

function updateTimeRange(timeRange, event) {
  if (event) {
    event.preventDefault();
  }
  
  currentTimeRange = timeRange;
  document.getElementById('timeRangeLabel').textContent = timeRangeLabels[timeRange] || 'Select time range';
  
  // Update active dropdown item
  const dropdown = document.querySelector('.dropdown-menu');
  if (dropdown) {
    const items = dropdown.querySelectorAll('a');
    items.forEach(item => item.classList.remove('active'));
    const timeLabel = timeRangeLabels[timeRange];
    if (timeLabel) {
      items.forEach(item => {
        if (item.textContent.trim().includes(timeLabel.substring(0, 15))) {
          item.classList.add('active');
        }
      });
    }
  }
  
  refreshData();
}

function refreshData() {
  console.log('Refreshing data for time range:', currentTimeRange);
  
  // Reload page with new time range
  const url = new URL(window.location.href);
  url.searchParams.set('time_range', currentTimeRange);
  window.location.href = url.toString();
}

function initializeCharts() {
  if (typeof Chart === 'undefined') {
    setTimeout(initializeCharts, 100);
    return;
  }

  // Destroy existing charts
  Object.values(chartInstances).forEach(chart => {
    if (chart && typeof chart.destroy === 'function') {
      chart.destroy();
    }
  });
  chartInstances = {};

  // Convert UTC timestamps to local browser timezone
  const convertLabelsToLocalTime = (labels) => {
    return labels.map(label => {
      try {
        const date = new Date(label);
        return date.toLocaleTimeString('en-US', { 
          hour: '2-digit', 
          minute: '2-digit', 
          hour12: false 
        });
      } catch (e) {
        return label;
      }
    });
  };

  // Alert Groups Evolution Chart
  const alertGroupsCtx = document.getElementById('alertGroupsChart')?.getContext('2d');
  if (alertGroupsCtx) {
    if (securityData.alertGroupsEvolution.labels.length > 0) {
      chartInstances.alertGroups = new Chart(alertGroupsCtx, {
        type: 'line',
        data: {
          labels: convertLabelsToLocalTime(securityData.alertGroupsEvolution.labels),
          datasets: securityData.alertGroupsEvolution.datasets.map((ds, idx) => ({
            ...ds,
            backgroundColor: (ds.backgroundColor || '').replace('1)', '0.1)'),
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 3,
          }))
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }
        }
      });
    }
  }

  // Alerts Chart (by severity level)
  const alertsCtx = document.getElementById('alertsChart')?.getContext('2d');
  if (alertsCtx) {
    if (securityData.alertsEvolutionByLevel.labels.length > 0) {
      chartInstances.alerts = new Chart(alertsCtx, {
        type: 'line',
        data: {
          labels: convertLabelsToLocalTime(securityData.alertsEvolutionByLevel.labels),
          datasets: securityData.alertsEvolutionByLevel.datasets.map((ds, idx) => ({
            ...ds,
            backgroundColor: (ds.backgroundColor || '').replace('1)', '0.1)'),
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 3,
          }))
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }
        }
      });
    }
  }

  // Top 5 Alerts Chart
  const top5AlertsCtx = document.getElementById('top5AlertsChart')?.getContext('2d');
  if (top5AlertsCtx) {
    if (securityData.topAlerts.labels.length > 0) {
      const colors = ['#0d6efd', '#20c997', '#0dcaf0', '#6f42c1', '#d63384'];
      chartInstances.top5Alerts = new Chart(top5AlertsCtx, {
        type: 'doughnut',
        data: {
          labels: securityData.topAlerts.labels,
          datasets: [{
            data: securityData.topAlerts.data,
            backgroundColor: colors.slice(0, securityData.topAlerts.data.length)
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 9 }, boxWidth: 10, padding: 6 } } }
        }
      });
    }
  }

  // Top 5 Rule Groups Chart
  const top5RuleGroupsCtx = document.getElementById('top5RuleGroupsChart')?.getContext('2d');
  if (top5RuleGroupsCtx) {
    if (securityData.topRuleGroups.labels.length > 0) {
      const colors = ['#fd7e14', '#ffc107', '#dc3545', '#ff6b6b', '#20c997'];
      chartInstances.top5RuleGroups = new Chart(top5RuleGroupsCtx, {
        type: 'doughnut',
        data: {
          labels: securityData.topRuleGroups.labels,
          datasets: [{
            data: securityData.topRuleGroups.data,
            backgroundColor: colors.slice(0, securityData.topRuleGroups.data.length)
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 9 }, boxWidth: 10, padding: 6 } } }
        }
      });
    }
  }

  // Top 5 PCI DSS Requirements Chart
  const top5PCIDSSCtx = document.getElementById('top5PCIDSSChart')?.getContext('2d');
  if (top5PCIDSSCtx) {
    if (securityData.topPCIDSS.labels.length > 0) {
      const colors = ['#20c997', '#0dcaf0', '#9d4edd', '#e0aaff', '#a8dadc'];
      chartInstances.top5PCIDSS = new Chart(top5PCIDSSCtx, {
        type: 'doughnut',
        data: {
          labels: securityData.topPCIDSS.labels,
          datasets: [{
            data: securityData.topPCIDSS.data,
            backgroundColor: colors.slice(0, securityData.topPCIDSS.data.length)
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 9 }, boxWidth: 10, padding: 6 } } }
        }
      });
    }
  }
}

// Initialize on page load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeCharts);
} else {
  initializeCharts();
}
</script>

@endif
@endsection