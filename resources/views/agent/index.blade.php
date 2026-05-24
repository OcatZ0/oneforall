@extends('layouts.wazuh')

@section('title', 'Agent - One For All')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack.min.css"/>
<style>
  .grid-stack { background: transparent; }
  .grid-stack-item-content { overflow: auto; }

  body.gs-edit-mode .grid-stack-item-content {
    outline: 2px dashed rgba(75, 73, 172, 0.4);
    outline-offset: -2px;
  }
  body.gs-edit-mode .grid-stack {
    background-image: linear-gradient(rgba(75,73,172,.04) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(75,73,172,.04) 1px, transparent 1px);
    background-size: calc(100% / 12) 60px;
  }

  #gs-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
  }
  #gs-fab-main {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #4B49AC;
    color: #fff;
    border: none;
    box-shadow: 0 4px 14px rgba(75,73,172,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    transition: background .2s, transform .15s;
  }
  #gs-fab-main:hover { background: #3b3a8c; transform: scale(1.06); }
  #gs-fab-main.active { background: #e74c3c; }

  #gs-edit-toolbar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9998;
    display: none;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.97);
    padding: 8px 16px;
    border-radius: 32px;
    box-shadow: 0 4px 20px rgba(0,0,0,.18);
    white-space: nowrap;
  }
  #gs-edit-toolbar.visible { display: flex; }

  .gs-tb-btn { padding: 6px 18px; border-radius: 20px; border: none; font-size: 13px; font-weight: 500; cursor: pointer; transition: opacity .15s; }
  .gs-tb-btn:hover { opacity: .82; }
  .gs-tb-btn-save   { background: #27ae60; color: #fff; }
  .gs-tb-btn-reset  { background: #f39c12; color: #fff; }
  .gs-tb-btn-cancel { background: #f0f0f0; color: #333; }

  .gs-card { height: 100%; display: flex; flex-direction: column; }
  .gs-card .card-body { flex: 1; overflow: auto; }

  @media (max-width: 767px) {
    #gs-fab, #gs-edit-toolbar { display: none !important; }
  }
</style>

<div class="grid-stack" id="agent-grid">

  {{-- STATUS --}}
  <div class="grid-stack-item" gs-id="agent-status" gs-x="0" gs-y="0" gs-w="3" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
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
  </div>

  {{-- DETAILS --}}
  <div class="grid-stack-item" gs-id="agent-details" gs-x="3" gs-y="0" gs-w="3" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title text-center">DETAILS</p>
          <div class="row text-center mb-3">
            <div class="col-6">
              <p class="text-muted mb-1">Active</p>
              <h4 class="text-success font-weight-bold">{{ $stats['active'] }}</h4>
            </div>
            <div class="col-6">
              <p class="text-muted mb-1">Disconnected</p>
              <h4 class="text-danger font-weight-bold">{{ $stats['disconnected'] }}</h4>
            </div>
            <div class="col-6">
              <p class="text-muted mb-1">Pending</p>
              <h4 class="text-warning font-weight-bold">{{ $stats['pending'] }}</h4>
            </div>
            <div class="col-6">
              <p class="text-muted mb-1">Never Connected</p>
              <h4 class="text-secondary font-weight-bold">{{ $stats['never_connected'] }}</h4>
            </div>
            <div class="col-12">
              <p class="text-muted mb-1">Coverage</p>
              <h4 class="text-success font-weight-bold">{{ $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100) : 0 }}%</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- EVOLUTION --}}
  <div class="grid-stack-item" gs-id="agent-evolution" gs-x="6" gs-y="0" gs-w="6" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
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

  {{-- AGENTS TABLE --}}
  <div class="grid-stack-item" gs-id="agent-table" gs-x="0" gs-y="7" gs-w="12" gs-h="14">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="card-title mb-0">Agents</h4>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
              <button class="btn btn-sm btn-primary" onclick="location.reload()">
                <i class="mdi mdi-refresh mr-1"></i> Refresh
              </button>
              @if(auth()->user()->peran === 'admin')
              <button class="btn btn-sm btn-success" id="syncBtn" onclick="syncAgentsFromWazuh()">
                <i class="mdi mdi-refresh mr-1"></i><span id="syncBtnText">Update Data Agent</span>
              </button>
              @endif
            </div>
          </div>

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
                <option value="active"          {{ request('status') === 'active'          ? 'selected' : '' }}>Active</option>
                <option value="disconnected"    {{ request('status') === 'disconnected'    ? 'selected' : '' }}>Disconnected</option>
                <option value="pending"         {{ request('status') === 'pending'         ? 'selected' : '' }}>Pending</option>
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
                <tr onclick="window.location='{{ route('agent.detail', $agent->id_agent) }}'" style="cursor: pointer;">
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
            <div>{{ $agents->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
          </div>
          <div class="text-muted text-sm mt-2">
            Menampilkan {{ ($agents->currentPage() - 1) * $agents->perPage() + 1 }} hingga
            {{ min($agents->currentPage() * $agents->perPage(), $agents->total()) }} dari {{ $agents->total() }} agent
          </div>
          @endif

        </div>
      </div>
    </div>
  </div>

</div>{{-- /grid-stack --}}

{{-- Floating pencil --}}
<div id="gs-fab">
  <button id="gs-fab-main" title="Edit layout">
    <i class="mdi mdi-pencil" id="gs-fab-icon"></i>
  </button>
</div>

{{-- Edit toolbar --}}
<div id="gs-edit-toolbar">
  <button class="gs-tb-btn gs-tb-btn-save"   id="gs-save">  <i class="mdi mdi-content-save me-1"></i>Save</button>
  <button class="gs-tb-btn gs-tb-btn-reset"  id="gs-reset"> <i class="mdi mdi-restore me-1"></i>Reset</button>
  <button class="gs-tb-btn gs-tb-btn-cancel" id="gs-cancel"><i class="mdi mdi-close me-1"></i>Cancel</button>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack-all.js"></script>
<script>
// ── Global chart state (accessible from HTML onclick handlers) ────────────────
let evolutionChartInstance = null;

const timeRangeLabels = {
  '15m': 'Last 15 minutes',
  '30m': 'Last 30 minutes',
  '1h':  'Last 1 hour',
  '24h': 'Last 24 hours',
  '7d':  'Last 7 days',
  '30d': 'Last 30 days',
  '90d': 'Last 90 days',
  '1y':  'Last 1 year',
  'today': 'Today',
  'week':  'This week'
};

const intervalTexts = {
  '15m':  'timestamp per 1 minute',
  '30m':  'timestamp per 1 minute',
  '1h':   'timestamp per 2 minutes',
  '24h':  'timestamp per 10 minutes',
  '7d':   'timestamp per 1 hour',
  '30d':  'timestamp per 6 hours',
  '90d':  'timestamp per 12 hours',
  '1y':   'timestamp per 1 day',
  'today':'timestamp per 30 minutes',
  'week': 'timestamp per 1 hour'
};

function initChart(labels, dataPoints) {
  const evolutionChart = document.getElementById('evolution-chart');
  if (evolutionChart && typeof Chart !== 'undefined') {
    if (evolutionChartInstance) evolutionChartInstance.destroy();
    const maxValue = dataPoints.length > 0 ? Math.max(...dataPoints) : 1;
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
          pointRadius: 4,
          pointHoverRadius: 7,
          pointBackgroundColor: '#82D616',
          pointHoverBackgroundColor: '#82D616',
          pointBorderWidth: 0,
          pointHoverBorderWidth: 0,
          tension: 0.3,
        }]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
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
            max: maxValue === 0 ? 1 : maxValue,
            grace: 0,
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              precision: 0,
              callback: value => Number.isInteger(value) ? value : null
            },
            grid: { color: 'rgba(0,0,0,0.05)' }
          },
          x: {
            ticks: { maxTicksLimit: 8, maxRotation: 0, autoSkip: true, font: { size: 10 } },
            grid: { display: false }
          }
        }
      }
    });
  }
}

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
        const labels = data.labels ?? [];
        if (labels.length > 0 && dataPoints.length > 0) {
          initChart(labels, dataPoints);
        } else {
          const chartContainer = evolutionChart?.parentNode;
          if (chartContainer && evolutionChart) {
            const noDataDiv = document.createElement('div');
            noDataDiv.id = 'evolution-chart';
            noDataDiv.style.cssText = 'display:flex;align-items:center;justify-content:center;height:100px;background-color:#f8f9fa;border-radius:4px;color:#6c757d;font-size:14px;font-weight:500;';
            noDataDiv.textContent = 'No data available';
            chartContainer.replaceChild(noDataDiv, evolutionChart);
          }
        }
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

function syncAgentsFromWazuh() {
  const syncBtn     = document.getElementById('syncBtn');
  const syncBtnText = document.getElementById('syncBtnText');
  syncBtn.disabled  = true;
  syncBtn.classList.remove('btn-success');
  syncBtn.classList.add('btn-secondary');
  const originalText = syncBtnText.textContent;
  syncBtnText.textContent = 'Syncing...';

  fetch('{{ route("agent.sync") }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const s = data.data;
      alert(`✓ Sync completed!\n\nCreated: ${s.synced_new}\nUpdated: ${s.updated_existing}\nTotal processed: ${s.total_processed}\nErrors: ${s.errors}`);
      setTimeout(() => location.reload(), 1000);
    } else {
      alert(`✗ Sync failed!\n\n${data.message}`);
      syncBtn.disabled = false;
      syncBtn.classList.add('btn-success');
      syncBtn.classList.remove('btn-secondary');
      syncBtnText.textContent = originalText;
    }
  })
  .catch(error => {
    console.error('Sync error:', error);
    alert(`✗ Sync error!\n\n${error.message}`);
    syncBtn.disabled = false;
    syncBtn.classList.add('btn-success');
    syncBtn.classList.remove('btn-secondary');
    syncBtnText.textContent = originalText;
  });
}

document.addEventListener('DOMContentLoaded', function () {
  const labels     = {!! $evolutionLabels ?? '[]' !!};
  const dataPoints = {!! $evolutionData ?? '[]' !!};

  if (labels.length > 0 && dataPoints.length > 0) {
    initChart(labels, dataPoints);
  } else {
    const evolutionChart = document.getElementById('evolution-chart');
    if (evolutionChart) {
      const noDataDiv = document.createElement('div');
      noDataDiv.style.cssText = `display:flex;align-items:center;justify-content:center;height:${evolutionChart.height}px;background-color:#f8f9fa;border-radius:4px;color:#6c757d;font-size:14px;font-weight:500;`;
      noDataDiv.textContent = 'No data available';
      evolutionChart.parentNode.replaceChild(noDataDiv, evolutionChart);
    }
  }
});

// ── GridStack ─────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'agent-status',    x: 0, y: 0, w: 3,  h: 7  },
    { id: 'agent-details',   x: 3, y: 0, w: 3,  h: 7  },
    { id: 'agent-evolution', x: 6, y: 0, w: 6,  h: 7  },
    { id: 'agent-table',     x: 0, y: 7, w: 12, h: 14 },
  ];

  const grid = GridStack.init({
    column: 12,
    cellHeight: 60,
    margin: 8,
    float: false,
    staticGrid: true,
    resizable: { handles: 'se' },
    columnOpts: {
      breakpointForWindow: true,
      breakpoints: [{ w: 768, c: 1 }],
    },
  });

  const savedLayout = @json($savedLayout ?? null);
  if (savedLayout && Array.isArray(savedLayout)) {
    grid.load(savedLayout);
  }

  grid.on('resizestop', () => {
    if (evolutionChartInstance) evolutionChartInstance.resize();
  });

  // ── Edit mode ────────────────────────────────────────────────────────────
  let editMode  = false;
  const fabMain = document.getElementById('gs-fab-main');
  const fabIcon = document.getElementById('gs-fab-icon');
  const toolbar = document.getElementById('gs-edit-toolbar');

  function enterEdit() {
    editMode = true;
    grid.setStatic(false);
    document.body.classList.add('gs-edit-mode');
    fabMain.classList.add('active');
    fabIcon.className = 'mdi mdi-pencil-off';
    toolbar.classList.add('visible');
  }

  function exitEdit() {
    editMode = false;
    grid.setStatic(true);
    document.body.classList.remove('gs-edit-mode');
    fabMain.classList.remove('active');
    fabIcon.className = 'mdi mdi-pencil';
    toolbar.classList.remove('visible');
  }

  fabMain.addEventListener('click', () => editMode ? exitEdit() : enterEdit());

  document.getElementById('gs-save').addEventListener('click', () => {
    const layout = grid.save(false);
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout, page: 'agent' })
    })
    .then(r => r.json())
    .then(d => { if (d.success) exitEdit(); });
  });

  document.getElementById('gs-reset').addEventListener('click', () => {
    grid.load(DEFAULT_LAYOUT);
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });

  // ── Search & filter debounce ─────────────────────────────────────────────
  function debounce(fn, ms) {
    let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
  }

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', debounce(() => {
      const form = document.getElementById('filterForm');
      const p = document.createElement('input');
      p.type = 'hidden'; p.name = 'page'; p.value = '1';
      form.appendChild(p);
      form.submit();
    }, 500));
  }

  const statusFilter = document.getElementById('statusFilter');
  if (statusFilter) {
    statusFilter.addEventListener('change', () => {
      const form = document.getElementById('filterForm');
      const p = document.createElement('input');
      p.type = 'hidden'; p.name = 'page'; p.value = '1';
      form.appendChild(p);
      form.submit();
    });
  }
})();
</script>
@endpush
