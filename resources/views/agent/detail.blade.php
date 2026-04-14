@extends('layouts.wazuh')

@section('title', 'Agent Detail - One For All')

@section('content')
<!-- SECONDARY NAV -->
<div class="bg-dark border-bottom border-secondary">
  <div class="d-flex align-items-center px-3">
    <ul class="nav flex-nowrap overflow-auto">
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small active" href="{{ route('agent.detail', $agent->id_agent) }}">
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
<!-- MAIN CONTENT -->
@if($agent)
<div class="container-fluid">
 
<!-- AGENT META STRIP -->
<div class="row mb-3">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-3 small">
        <div class="d-grid gap-3 mb-0" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr))">
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">ID</div>
            <div class="fw-bold">{{ $agent->id_agent ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Status</div>
            <div>
              @php
                $statusColor = match($agent->status ?? 'unknown') {
                  'active' => 'success',
                  'disconnected' => 'danger',
                  'pending' => 'warning',
                  'never_connected' => 'secondary',
                  default => 'secondary',
                };
                $statusText = ucfirst(str_replace('_', ' ', $agent->status ?? 'unknown'));
              @endphp
              <span class="badge bg-{{ $statusColor }}"><span class="mdi mdi-circle me-1" style="font-size:7px"></span>{{ $statusText }}</span>
            </div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">IP Address</div>
            <div>{{ $agent->ip ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Version</div>
            <div>{{ $agent->version ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Groups</div>
            <div>
              @if($agent->group && $agent->group !== 'N/A')
                @foreach(explode(', ', $agent->group) as $grp)
                  <span class="badge bg-secondary">{{ trim($grp) }}</span>
                @endforeach
              @else
                <span class="badge bg-secondary">default</span>
              @endif
            </div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Operating System</div>
            <div class="d-flex align-items-center">
              <i class="mdi {{ \App\Http\Controllers\AgentController::getOSIcon($agent->os ?? '') }} text-primary"></i>
              {{ $agent->os ?? 'Unknown' }}
            </div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Cluster Node</div>
            <div>{{ $agent->cluster_node ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Registration Date</div>
            <div>
              @if($agent->dateAdd)
                {{ \Carbon\Carbon::parse($agent->dateAdd)->format('M d, Y @ H:i:s.000') }}
              @else
                N/A
              @endif
            </div>
          </div>
          <div>
            <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Last Keep Alive</div>
            <div>
              @if($agent->lastKeepAlive)
                {{ \Carbon\Carbon::parse($agent->lastKeepAlive)->format('M d, Y @ H:i:s.000') }}
              @else
                N/A
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dropdown di bawah card, pojok kanan -->
    <div class="d-flex justify-content-end mt-1" style="position: relative; z-index: 1050;">
      <div class="dropdown" style="position: relative;">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown"
          data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border: none; background: transparent; padding: 0; font-weight: normal;">
          <span id="timeRangeLabel">Last 24 hours</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="timeRangeDropdown" style="min-width:150px; z-index: 1050;">
          <a class="dropdown-item" href="#" onclick="updateChart('15m', event); return false;">Last 15 minutes</a>
          <a class="dropdown-item" href="#" onclick="updateChart('30m', event); return false;">Last 30 minutes</a>
          <a class="dropdown-item" href="#" onclick="updateChart('1h', event); return false;">Last 1 hour</a>
          <a class="dropdown-item active" href="#" onclick="updateChart('24h', event); return false;">Last 24 hours</a>
          <a class="dropdown-item" href="#" onclick="updateChart('7d', event); return false;">Last 7 days</a>
          <a class="dropdown-item" href="#" onclick="updateChart('30d', event); return false;">Last 30 days</a>
          <a class="dropdown-item" href="#" onclick="updateChart('90d', event); return false;">Last 90 days</a>
          <a class="dropdown-item" href="#" onclick="updateChart('1y', event); return false;">Last 1 year</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#" onclick="updateChart('today', event); return false;">Today</a>
          <a class="dropdown-item" href="#" onclick="updateChart('week', event); return false;">This week</a>
        </div>
      </div>
    </div>

  </div>
</div>

  <!-- Row 2: MITRE + Compliance + FIM -->
  <div class="row g-3 mb-3">
 
    <!-- MITRE -->
    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">MITRE</span>
          <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
        </div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-5">
          <span class="mdi mdi-chart-bar display-4 text-muted opacity-25 mb-3"></span>
          <h6 class="text-muted">No results</h6>
          <p class="text-muted small mb-0">No Mitre results were found in the selected time range.</p>
        </div>
      </div>
    </div>
 
    <!-- Compliance -->
    <div class="col-md-3">  
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">Compliance</span>
          <select class="form-select form-select-sm w-auto" id="complianceSelect" onchange="updateComplianceData()">
            <option value="gdpr">GDPR</option>
            <option value="pci_dss">PCI DSS</option>
            <option value="nist_800_53">NIST 800-53</option>
            <option value="hipaa">HIPAA</option>
            <option value="gpg13">GPG13</option>
            <option value="tsc">TSC</option>
          </select>
        </div>
        <div class="d-flex align-items-center justify-content-center" style="min-height:350px;">
          <canvas id="complianceChart" style="max-width:600px;"></canvas>
        </div>
      </div>
    </div>
 
    <!-- FIM: Recent Events -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">FIM: Recent events</span>
          <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px; min-width: 900px;">
            <thead class="table-light">
              <tr>
                <th style="width:15%; font-size:12px">Time</th>
                <th style="width:35%; font-size:12px">Path</th>
                <th style="width:10%; font-size:12px">Action</th>
                <th style="width:20%; font-size:12px">Rule description</th>
                <th style="width:10%; font-size:12px">Rule Level</th>
                <th style="width:10%; font-size:12px">Rule Id</th>
              </tr>
            </thead>
            <tbody>
              @forelse($fimEvents ?? [] as $event)
              <tr>
                <td class="text-muted" style="font-size:9px; white-space: pre-line;">
                  @if($event['timestamp'])
                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('M d, Y @\nH:i:s.v') }}
                  @else
                    N/A
                  @endif
                </td>
                <td>
                    <a href="#" class="text-primary text-decoration-none d-block text-truncate" style="width: 100%" title="{{ $event['path'] }}">{{ $event['path'] }}</a></td>
                <td>
                  @php
                    $actionColor = match(strtolower($event['action'] ?? '')) {
                      'deleted' => 'danger',
                      'added' => 'success',
                      'modified' => 'warning',
                      default => 'secondary',
                    };
                  @endphp
                  <span class="badge bg-{{ $actionColor }}">{{ ucfirst($event['action'] ?? 'unknown') }}</span>
                </td>
                <td style="word-break: break-word; overflow-wrap: break-word;">{{ $event['description'] ?? 'Unknown' }}</td>
                <td class="fw-bold text-center">{{ $event['level'] ?? 0 }}</td>
                <td class="text-muted">{{ $event['rule_id'] ?? '' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-3">
                  <i class="mdi mdi-information-outline me-1"></i>No FIM events found in the last 24 hours
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
 
  <!-- Row 3: Events Evolution + SCA -->
  <div class="row g-3">
 
    <!-- Events Count Evolution -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Events count evolution</span>
        </div>
        <div class="card-body">
          <p class="mb-2 small"><span class="text-success me-1">●</span> Count</p>
          <canvas id="eventsChart" height="90"></canvas>
          <p class="text-center mb-0 mt-1"><small class="text-muted" id="chartIntervalText">timestamp per hour</small></p>
        </div>
      </div>
    </div>
 
    <!-- SCA: Latest Scans -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">SCA: Lastest scans</span>
          <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
        </div>
        <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
          <a href="#" class="text-primary small fw-medium text-decoration-none">CIS Microsoft Windows Server 2022 Benchmark v1.0.0</a>
          <span class="badge bg-success" style="font-size:10px">cis_win2022</span>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0" style="font-size:11px">
            <thead class="table-light">
              <tr>
                <th>Policy</th>
                <th>End scan</th>
                <th>Passed</th>
                <th>Failed</th>
                <th>Not applica...</th>
                <th>Score</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-muted" style="max-width:140px;white-space:normal">CIS Microsoft Windows Server 2022 Benchmark v1.0.0</td>
                <td class="text-muted text-nowrap">Apr 11, 2026 @<br>03:39:44.000</td>
                <td class="text-success fw-bold">120</td>
                <td class="text-danger fw-bold">219</td>
                <td class="text-muted">3</td>
                <td>
                  <div class="d-flex align-items-center gap-1">
                    <div class="progress flex-grow-1" style="height:5px;min-width:40px">
                      <div class="progress-bar bg-warning" style="width:35%"></div>
                    </div>
                    <span class="fw-bold text-warning text-nowrap">35%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- <div class="card-footer d-flex justify-content-end align-items-center gap-1 py-2">
          <span class="text-muted small me-2">1–1 of 1</span>
          <button class="btn btn-sm btn-outline-secondary py-0 px-2">‹</button>
          <button class="btn btn-sm btn-primary py-0 px-2">1</button>
          <button class="btn btn-sm btn-outline-secondary py-0 px-2">›</button>
        </div> -->
      </div>
    </div>
 
  </div>
</div>
</div>

@else
<div class="container-fluid py-5">
  <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
    <i class="mdi mdi-alert-circle-outline display-4"></i>
    <div>
      <h5 class="alert-heading mb-1">Agent Not Found</h5>
      <p class="mb-0">{{ $error ?? 'Unable to load agent details. Please try again or contact the administrator.' }}</p>
      <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-danger mt-2">
        <i class="mdi mdi-arrow-left me-1"></i> Back to Agents
      </a>
    </div>
  </div>
</div>
@endif
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
// Global variables
let eventsChartInstance = null;
let complianceChartInstance = null;
let currentTimeRange = '24h';
let currentComplianceType = 'gdpr';

// Initialize dropdown when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  const dropdownBtn = document.getElementById('timeRangeDropdown');
  if (dropdownBtn) {
    // Initialize Bootstrap dropdown
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
      new bootstrap.Dropdown(dropdownBtn);
    }
  }
});

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

const intervalTexts = {
  '15m': 'timestamp per 3 minutes',
  '30m': 'timestamp per 5 minutes',
  '1h': 'timestamp per 10 minutes',
  '24h': 'timestamp per hour',
  '7d': 'timestamp per 12 hours',
  '30d': 'timestamp per day',
  '90d': 'timestamp per day',
  '1y': 'timestamp per day',
  'today': 'timestamp per hour',
  'week': 'timestamp per day'
};

// Initialize events chart
function initEventsChart(labels, data) {
  const chartContainer = document.getElementById('eventsChart')?.parentElement;
  if (!chartContainer) return;

  if (labels.length === 0 || data.length === 0) {
    // Destroy existing chart if any
    if (eventsChartInstance) {
      eventsChartInstance.destroy();
      eventsChartInstance = null;
    }
    
    // Show "No data available" message
    const eventsChart = document.getElementById('eventsChart');
    if (eventsChart && eventsChart.tagName === 'CANVAS') {
      const noDataDiv = document.createElement('div');
      noDataDiv.style.cssText = `
        display: flex;
        align-items: center;
        justify-content: center;
        height: 90px;
        background-color: #f8f9fa;
        border-radius: 4px;
        color: #6c757d;
        font-size: 14px;
        font-weight: 500;
      `;
      noDataDiv.textContent = 'No events data available';
      eventsChart.parentNode.replaceChild(noDataDiv, eventsChart);
    }
    return;
  }

  // Replace div back to canvas if needed
  const existingDiv = chartContainer.querySelector('div[style*="No events data"]');
  if (existingDiv) {
    const newCanvas = document.createElement('canvas');
    newCanvas.id = 'eventsChart';
    newCanvas.height = '90';
    existingDiv.parentNode.replaceChild(newCanvas, existingDiv);
  }

  const ctx = document.getElementById('eventsChart')?.getContext('2d');
  if (!ctx) return;

  // Destroy existing chart
  if (eventsChartInstance) {
    eventsChartInstance.destroy();
  }

  eventsChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Count',
        data,
        borderColor: '#20c997',
        backgroundColor: 'rgba(32,201,151,0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.3,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: '#20c997',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      animation: {
        duration: 750,
        easing: 'easeInOutQuart'
      },
      plugins: { 
        legend: { display: false },
        tooltip: { enabled: true }
      },
      interaction: { 
        mode: 'index',
        intersect: false
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 },
          grid: { color: 'rgba(0,0,0,0.05)' }
        },
        x: {
          ticks: { maxTicksLimit: 8, maxRotation: 0 },
          grid: { display: false }
        }
      }
    }
  });
}

// Initialize compliance chart
function initComplianceChart(data) {
  const chartContainer = document.getElementById('complianceChart')?.parentElement;
  if (!chartContainer) return;

  if (!data || data.length === 0) {
    // Destroy existing chart if any
    if (complianceChartInstance) {
      complianceChartInstance.destroy();
      complianceChartInstance = null;
    }
    
    // Show "No data available" message
    const complianceChart = document.getElementById('complianceChart');
    if (complianceChart && complianceChart.tagName === 'CANVAS') {
      const noDataDiv = document.createElement('div');
      noDataDiv.style.cssText = `
        display: flex;
        align-items: center;
        justify-content: center;
        height: 350px;
        background-color: #f8f9fa;
        border-radius: 4px;
        color: #6c757d;
        font-size: 14px;
        font-weight: 500;
      `;
      noDataDiv.textContent = 'No compliance data available';
      complianceChart.parentNode.replaceChild(noDataDiv, complianceChart);
    }
    return;
  }

  // Replace div back to canvas if needed
  const existingDiv = chartContainer.querySelector('div[style*="No compliance data"]');
  if (existingDiv) {
    const newCanvas = document.createElement('canvas');
    newCanvas.id = 'complianceChart';
    existingDiv.parentNode.replaceChild(newCanvas, existingDiv);
  }

  const ctx = document.getElementById('complianceChart')?.getContext('2d');
  if (!ctx) return;

  // Destroy existing chart
  if (complianceChartInstance) {
    complianceChartInstance.destroy();
  }

  // Prepare data for chart
  // Data structure: {name: 'compliance_framework', count: number}
  const labels = data.map(item => item.name || 'Unknown').filter(l => l !== 'Unknown');
  const chartData = data.map(item => item.count || 0);
  
  console.log('Compliance chart data:', { labels, chartData, rawData: data });
  
  if (labels.length === 0) {
    console.warn('No valid compliance labels found');
    return;
  }

  const colors = ['#20c997', '#0d6efd', '#dc3545', '#6f42c1', '#d63384', '#fd7e14', '#20c997', '#0dcaf0'];

  complianceChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        label: 'Alerts',
        data: chartData,
        backgroundColor: colors.slice(0, chartData.length),
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: { 
        legend: { 
          display: true,
          position: 'bottom',
          labels: {
            boxWidth: 12,
            font: { size: 11 },
            padding: 10
          }
        },
        tooltip: { enabled: true }
      }
    }
  });
}

// Update chart based on time range
function updateChart(timeRange, event) {
  if (event) {
    event.preventDefault();
  }

  console.log('updateChart called with:', { timeRange, currentComplianceType });

  currentTimeRange = timeRange;
  document.getElementById('timeRangeLabel').textContent = timeRangeLabels[timeRange] || 'Select time range';
  document.getElementById('chartIntervalText').textContent = intervalTexts[timeRange] || 'loading...';

  // Update active dropdown item
  const dropdown = document.querySelector('.dropdown');
  if (dropdown) {
    const items = dropdown.querySelectorAll('.dropdown-item');
    items.forEach(item => item.classList.remove('active'));
    
    // Get the label to match
    const timeLabel = timeRangeLabels[timeRange];
    if (timeLabel) {
      items.forEach(item => {
        if (item.textContent.includes(timeLabel)) {
          item.classList.add('active');
        }
      });
    }
  }

  // Fetch chart data
  const agentId = '{{ $agent->id_agent ?? "" }}';
  
  if (!agentId) {
    console.error('Agent ID is missing!');
    return;
  }
  
  // Construct URL using direct path
  const fullUrl = '/agent/' + agentId + '/chart-data?time_range=' + timeRange + '&compliance_type=' + currentComplianceType;
  
  console.log('Fetching chart data:', {
    agentId: agentId,
    timeRange: timeRange,
    complianceType: currentComplianceType,
    url: fullUrl
  });
  
  fetch(fullUrl)
    .then(r => {
      console.log('Response status:', r.status, r.statusText);
      if (!r.ok) {
        throw new Error(`HTTP ${r.status}: ${r.statusText}`);
      }
      return r.json();
    })
    .then(data => {
      if (data.success) {
        console.log('Chart data received:', data);
        
        // Update events chart
        const eventLabels = data.events_evolution?.labels ?? [];
        const eventData = data.events_evolution?.data ?? [];
        initEventsChart(eventLabels, eventData);
        
        // Update compliance chart if data available
        const complianceData = data.compliance_data ?? [];
        initComplianceChart(complianceData);
      } else {
        console.error('Error fetching chart data:', data.message);
      }
    })
    .catch(error => {
      console.error('Error fetching chart data:', error);
    });
}

// Update compliance chart based on compliance type
function updateComplianceData() {
  const complianceSelect = document.getElementById('complianceSelect');
  if (complianceSelect) {
    currentComplianceType = complianceSelect.value;
    updateChart(currentTimeRange);
  }
}

// Wait for Chart.js to load before initializing
function initializeChart() {
  if (typeof Chart === 'undefined') {
    setTimeout(initializeChart, 100);
    return;
  }

  // Initialize events chart with initial data
  @if(isset($eventsEvolution) && !empty($eventsEvolution['labels']))
    const initialLabels = @json($eventsEvolution['labels'] ?? []);
    const initialData = @json($eventsEvolution['data'] ?? []);
  @else
    const initialLabels = [];
    const initialData = [];
  @endif

  initEventsChart(initialLabels, initialData);

  // Initialize compliance chart with initial data
  @if(isset($complianceGdpr))
    const initialCompliance = @json($complianceGdpr ?? []);
  @else
    const initialCompliance = [];
  @endif

  initComplianceChart(initialCompliance);
}

// Initialize chart when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeChart);
} else {
  initializeChart();
}
</script>

@endsection
