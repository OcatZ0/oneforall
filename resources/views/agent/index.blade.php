@extends('layouts.app')

@section('title', 'Agent - One For All')

@section('content')

{{-- Agent Statistics --}}
<div class="row grid-margin">
  <div class="col-md-3 stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title text-center">STATUS</p>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
          <span><span class="badge badge-success mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Active</span>
          <span class="font-weight-bold">{{ $stats['active'] }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
          <span><span class="badge badge-danger mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Disconnected</span>
          <span class="font-weight-bold">{{ $stats['disconnected'] }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
          <span><span class="badge badge-warning mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Pending</span>
          <span class="font-weight-bold">{{ $stats['pending'] }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span><span class="badge badge-secondary mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Never Connected</span>
          <span class="font-weight-bold">{{ $stats['never_connected'] }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6 stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title text-center">DETAILS</p>
        <div class="row text-center mb-3">
          <div class="col">
            <p class="text-muted mb-1">Active</p>
            <h4 class="text-success font-weight-bold">{{ $stats['active'] }}</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Disconnected</p>
            <h4 class="text-danger font-weight-bold">{{ $stats['disconnected'] }}</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Pending</p>
            <h4 class="text-warning font-weight-bold">{{ $stats['pending'] }}</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Never Connected</p>
            <h4 class="text-secondary font-weight-bold">{{ $stats['never_connected'] }}</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Coverage</p>
            <h4 class="text-success font-weight-bold">{{ $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100) : 0 }}%</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <p class="card-title mb-0">EVOLUTION</p>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown" 
              data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
              style="border: none; background: transparent; padding: 0; font-weight: normal;">
              <span id="timeRangeLabel">Last 24 hours</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="timeRangeDropdown" style="min-width: 150px;">
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
        <p class="mb-2"><span class="text-success mr-1">●</span> <small>active</small></p>
        <canvas id="evolution-chart" height="100"></canvas>
        <p class="text-center mb-0 mt-1"><small class="text-muted" id="chartIntervalText">timestamp per 10 minutes</small></p>
      </div>
    </div>
  </div>
</div>

{{-- Agents Table --}}
<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="card-title mb-0">Agents</h4>
          <div class="d-flex gap-2 flex-wrap justify-content-end">
            <button class="btn btn-sm btn-primary" onclick="location.reload()">
              <i class="mdi mdi-refresh mr-1"></i> Refresh
            </button>
            <button class="btn btn-sm btn-success" onclick="location.reload()">
              <i class="mdi mdi-refresh mr-1"></i> Update Data Agent
            </button>
          </div>
        </div>

        <!-- Search and Filter Form -->
        <form method="GET" action="{{ route('agent') }}" id="filterForm" class="mb-3">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group" style="max-width:400px">
              <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                  <i class="mdi mdi-magnify text-muted"></i>
                </span>
              </div>
              <input type="text" id="searchInput" name="search" class="form-control border-left-0"
                placeholder="Cari agent ID atau nama..."
                value="{{ request('search') }}">
            </div>

            <select id="statusFilter" name="status" class="form-control form-select" style="width:180px">
              <option value="">Semua Status</option>
              <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
              <option value="disconnected" {{ request('status') === 'disconnected' ? 'selected' : '' }}>Disconnected</option>
              <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="never_connected" {{ request('status') === 'never_connected' ? 'selected' : '' }}>Never Connected</option>
            </select>

            <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-secondary">
              <i class="mdi mdi-refresh mr-1"></i>Reset
            </a>
          </div>
        </form>

        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th style="width:50px">#</th>
                <th>ID</th>
                <th>Nama Agent</th>
                <th>IP Address</th>
                <th>Operating System</th>
                <th>Version</th>
                <th>Assigned To</th>
                <th>Cluster Node</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($agents as $agent)
              <tr>
                <td>{{ ($agents->currentPage() - 1) * $agents->perPage() + $loop->iteration }}</td>
                <td class="font-weight-bold">{{ $agent->id_agent }}</td>
                <td>{{ $agent->nama }}</td>
                <td>{{ $agent->ip }}</td>
                <td>
                  <i class="mdi {{ \App\Http\Controllers\AgentController::getOSIcon($agent->os) }} mr-1"></i>
                  <small>{{ $agent->os }}</small>
                </td>
                <td><small class="text-muted">{{ $agent->version }}</small></td>
                <td>
                  @if($agent->user)
                    <span class="badge badge-primary">{{ $agent->user->username }}</span>
                  @else
                    <span class="text-muted font-italic">Unassigned</span>
                  @endif
                </td>
                <td><small class="text-muted">{{ $agent->cluster_node }}</small></td>
                <td>
                  <span class="badge badge-{{ \App\Http\Controllers\AgentController::getStatusBadgeColor($agent->status) }}">
                    {{ \App\Http\Controllers\AgentController::formatStatus($agent->status) }}
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  <i class="mdi mdi-information-outline mr-2"></i>Tidak ada agent yang ditemukan
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        @if($agents->count() > 0)
        <div class="d-flex align-items-center justify-content-between mt-3">
          <div class="d-flex align-items-center">
            <span class="text-muted mr-2 me-2">Rows per page:</span>
            <form method="GET" action="{{ route('agent') }}" class="d-inline" id="perPageForm">
              @foreach(request()->query() as $key => $value)
                @if($key !== 'per_page' && $key !== 'page')
                  <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
              @endforeach
              <input type="hidden" name="page" value="1">
              <select name="per_page" class="form-control form-select" style="width:90px" onchange="document.getElementById('perPageForm').submit()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
              </select>
            </form>
          </div>

          <!-- Pagination Links -->
          <div>
            {{ $agents->appends(request()->query())->links('pagination::bootstrap-4') }}
          </div>
        </div>

        <!-- Pagination Info -->
        <div class="text-muted text-sm mt-2">
          Menampilkan {{ ($agents->currentPage() - 1) * $agents->perPage() + 1 }} hingga
          {{ min($agents->currentPage() * $agents->perPage(), $agents->total()) }} dari {{ $agents->total() }} agent
        </div>
        @endif

      </div>
    </div>
  </div>
</div>

<script>
// Debounce function for search
function debounce(func, delay) {
  let timeoutId;
  return function(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(this, args), delay);
  };
}

// Auto-submit form on search input (debounced)
const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('input', debounce(function() {
    const form = document.getElementById('filterForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'page';
    input.value = '1';
    form.appendChild(input);
    form.submit();
  }, 500));
}

// Auto-submit form on status filter change
const statusFilter = document.getElementById('statusFilter');
if (statusFilter) {
  statusFilter.addEventListener('change', function() {
    const form = document.getElementById('filterForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'page';
    input.value = '1';
    form.appendChild(input);
    form.submit();
  });
}

// Global chart instance
let evolutionChartInstance = null;

// Time range labels for dropdown
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

// Interval text for each time range
const intervalTexts = {
  '15m': 'timestamp per 1 minute',
  '30m': 'timestamp per 1 minute',
  '1h': 'timestamp per 2 minutes',
  '24h': 'timestamp per 10 minutes',
  '7d': 'timestamp per 1 hour',
  '30d': 'timestamp per 6 hours',
  '90d': 'timestamp per 12 hours',
  '1y': 'timestamp per 1 day',
  'today': 'timestamp per 30 minutes',
  'week': 'timestamp per 1 hour'
};

// Initialize chart
function initChart(labels, dataPoints) {
  const evolutionChart = document.getElementById('evolution-chart');
  console.log('initChart called with:', {
    labels_count: labels.length,
    data_points_count: dataPoints.length,
    chart_element_exists: !!evolutionChart,
    Chart_available: typeof Chart !== 'undefined'
  });
  
  if (evolutionChart && typeof Chart !== 'undefined') {
    // Destroy existing chart if any
    if (evolutionChartInstance) {
      console.log('Destroying existing chart instance');
      evolutionChartInstance.destroy();
    }
    
    console.log('Creating new chart with data:', {
      first_label: labels[0],
      last_label: labels[labels.length - 1],
      first_data: dataPoints[0],
      last_data: dataPoints[dataPoints.length - 1]
    });
    
    evolutionChartInstance = new Chart(evolutionChart.getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'active',
                data: dataPoints,
                borderColor: '#82D616',
                borderWidth: 2,
                fill: false,
                pointRadius: 0,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        title: ctx => ctx[0].label,
                        label: ctx => `Active agents: ${ctx.raw}`
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    ticks: { stepSize: 1 },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: {
                        maxTicksLimit: 8,       // only show 8 labels max
                        maxRotation: 0,         // no rotation
                        autoSkip: true,
                        font: { size: 10 }
                    },
                    grid: { display: false }
                }
            }
        }
    });
    
    console.log('Chart created successfully');
  } else {
    console.error('Chart initialization failed:', {
      chart_element_exists: !!evolutionChart,
      Chart_available: typeof Chart !== 'undefined'
    });
  }
}

// Update chart with selected time range
function updateChart(timeRange, event) {
  document.getElementById('timeRangeLabel').textContent = timeRangeLabels[timeRange] || 'Select time range';
  document.getElementById('chartIntervalText').textContent = intervalTexts[timeRange] || 'loading...';

  const evolutionChart = document.getElementById('evolution-chart');
  if (evolutionChart) evolutionChart.style.opacity = '0.5';

  fetch('{{ route("agent.chart-data") }}?time_range=' + timeRange)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const dataPoints = Array.isArray(data.data) ? data.data : (data.data.active ?? []);
        initChart(data.labels, dataPoints);

        document.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
        if (event?.target) event.target.classList.add('active');
      }
      if (evolutionChart) evolutionChart.style.opacity = '1';
    })
    .catch(error => {
      console.error('Error fetching chart data:', error);
      if (evolutionChart) evolutionChart.style.opacity = '1';
    });
}

// Evolution Chart - Initial load
document.addEventListener('DOMContentLoaded', function() {
  const labels = {!! $evolutionLabels ?? '[]' !!};
  const dataPoints = {!! $evolutionData ?? '[]' !!};
  
  console.log('Evolution Chart Data Loaded:', {
    labels: labels,
    dataLength: labels.length,
    data: dataPoints,
    dataLength: dataPoints.length,
    hasChartElement: !!document.getElementById('evolution-chart')
  });
  
  if (labels.length > 0 && dataPoints.length > 0) {
    console.log('Initializing evolution chart with valid data');
    initChart(labels, dataPoints);
  } else {
    console.warn('Chart initialization skipped - insufficient data', {
      labels_count: labels.length,
      data_count: dataPoints.length
    });
  }
});
</script>

@endsection