@extends('layouts.wazuh')

@section('title', 'Agent - One For All')

@push('styles')
@include('partials._gridstack-styles')
@endpush

@section('content')

<div class="grid-stack" id="agent-grid">

  {{-- STATUS --}}
  <div class="grid-stack-item" gs-id="agent-status" data-label="Status" gs-x="0" gs-y="0" gs-w="3" gs-h="7">
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
  <div class="grid-stack-item" gs-id="agent-details" data-label="Details" gs-x="3" gs-y="0" gs-w="3" gs-h="7">
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
  <div class="grid-stack-item" gs-id="agent-evolution" data-label="Evolution" gs-x="6" gs-y="0" gs-w="6" gs-h="7">
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
  <div class="grid-stack-item" gs-id="agent-table" data-label="Tabel Agent" gs-x="0" gs-y="7" gs-w="12" gs-h="14">
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

{{-- Sync Result Modal --}}
<div class="modal fade" id="syncResultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
    <div class="modal-content border-0 shadow">

      <div class="modal-header border-0 pb-0 pt-4 px-4">
        <div class="d-flex align-items-center gap-3">
          <div id="syncModalIconWrap" class="d-flex align-items-center justify-content-center rounded-circle"
               style="width:44px;height:44px;flex-shrink:0;">
            <i id="syncModalIcon" class="mdi fs-3"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0 fw-semibold" id="syncModalTitle"></h5>
            <p class="text-muted small mb-0" id="syncModalSubtitle"></p>
          </div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body px-4 py-3" id="syncModalBody"></div>

      <div class="modal-footer border-0 px-4 pt-0 pb-4 gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm"
                data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm" id="syncModalReload"
                style="background:#4B49AC;color:#fff;" onclick="location.reload()">
          <i class="mdi mdi-refresh me-1"></i>Reload Page
        </button>
      </div>

    </div>
  </div>
</div>

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

function showSyncModal(success, data, message) {
  const iconWrap  = document.getElementById('syncModalIconWrap');
  const icon      = document.getElementById('syncModalIcon');
  const title     = document.getElementById('syncModalTitle');
  const subtitle  = document.getElementById('syncModalSubtitle');
  const body      = document.getElementById('syncModalBody');
  const reloadBtn = document.getElementById('syncModalReload');

  if (success) {
    iconWrap.style.background = 'rgba(39,174,96,.12)';
    icon.className = 'mdi mdi-check-circle fs-3 text-success';
    title.textContent    = 'Sync Completed';
    subtitle.textContent = 'Agent data successfully updated from Wazuh';
    reloadBtn.style.display = '';

    const hasErrors = data.errors > 0;
    body.innerHTML = `
      <div class="row g-2 mb-3">
        <div class="col-6">
          <div class="rounded p-3 text-center" style="background:#f0fdf4;">
            <div class="fw-bold fs-5 text-success">${data.synced_new}</div>
            <div class="text-muted small">New agents</div>
          </div>
        </div>
        <div class="col-6">
          <div class="rounded p-3 text-center" style="background:#eff6ff;">
            <div class="fw-bold fs-5" style="color:#4B49AC">${data.updated_existing}</div>
            <div class="text-muted small">Updated</div>
          </div>
        </div>
        <div class="col-6">
          <div class="rounded p-3 text-center" style="background:#fef2f2;">
            <div class="fw-bold fs-5 text-danger">${data.deleted_obsolete}</div>
            <div class="text-muted small">Deleted</div>
          </div>
        </div>
        <div class="col-6">
          <div class="rounded p-3 text-center" style="background:#f8f9fa;">
            <div class="fw-bold fs-5 text-secondary">${data.total_processed}</div>
            <div class="text-muted small">Total processed</div>
          </div>
        </div>
      </div>
      ${hasErrors ? `
      <div class="alert alert-warning py-2 mb-0 d-flex align-items-center gap-2" role="alert">
        <i class="mdi mdi-alert-outline"></i>
        <span class="small">${data.errors} agent(s) encountered errors during sync.</span>
      </div>` : ''}`;
  } else {
    iconWrap.style.background = 'rgba(220,53,69,.1)';
    icon.className = 'mdi mdi-alert-circle fs-3 text-danger';
    title.textContent    = 'Sync Failed';
    subtitle.textContent = 'An error occurred during the sync process';
    reloadBtn.style.display = 'none';
    body.innerHTML = `
      <div class="alert alert-danger py-2 mb-0 d-flex align-items-center gap-2" role="alert">
        <i class="mdi mdi-alert-circle-outline"></i>
        <span class="small">${message || 'Unknown error'}</span>
      </div>`;
  }

  new bootstrap.Modal(document.getElementById('syncResultModal')).show();
}

function syncAgentsFromWazuh() {
  const syncBtn     = document.getElementById('syncBtn');
  const syncBtnText = document.getElementById('syncBtnText');
  syncBtn.disabled  = true;
  syncBtn.classList.remove('btn-success');
  syncBtn.classList.add('btn-secondary');
  const originalText = syncBtnText.textContent;
  syncBtnText.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Syncing...';

  fetch('{{ route("agent.sync") }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  })
  .then(r => r.json())
  .then(data => {
    syncBtn.disabled = false;
    syncBtn.classList.add('btn-success');
    syncBtn.classList.remove('btn-secondary');
    syncBtnText.textContent = originalText;

    if (data.success) {
      showSyncModal(true, data.data, null);
    } else {
      showSyncModal(false, null, data.message);
    }
  })
  .catch(error => {
    console.error('Sync error:', error);
    syncBtn.disabled = false;
    syncBtn.classList.add('btn-success');
    syncBtn.classList.remove('btn-secondary');
    syncBtnText.textContent = originalText;
    showSyncModal(false, null, error.message);
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

  // ── Hidden cards state ──────────────────────────────────────────────────
  const hiddenCards     = new Set();
  const hiddenPositions = {};

  function setCardHidden(id, hide) {
    const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
    if (!el) return;
    if (hide) {
      const node = el.gridstackNode;
      if (node) hiddenPositions[id] = { x: node.x, y: node.y, w: node.w, h: node.h };
      hiddenCards.add(id);
      el.classList.add('gs-card-hidden');
      const btn = el.querySelector('.gs-hide-btn');
      if (btn) { btn.querySelector('i').className = 'mdi mdi-eye'; btn.title = 'Tampilkan kartu'; }
    } else {
      hiddenCards.delete(id);
      el.classList.remove('gs-card-hidden');
      const btn = el.querySelector('.gs-hide-btn');
      if (btn) { btn.querySelector('i').className = 'mdi mdi-eye-off'; btn.title = 'Sembunyikan kartu'; }
    }
  }

  function addHideButtons() {
    document.querySelectorAll('.grid-stack-item').forEach(item => {
      if (item.querySelector('.gs-hide-btn')) return;
      const id       = item.getAttribute('gs-id');
      const isHidden = hiddenCards.has(id);
      const btn      = document.createElement('button');
      btn.className  = 'gs-hide-btn';
      btn.title      = isHidden ? 'Tampilkan kartu' : 'Sembunyikan kartu';
      btn.innerHTML  = `<i class="mdi mdi-${isHidden ? 'eye' : 'eye-off'}"></i>`;
      btn.addEventListener('click', e => { e.stopPropagation(); setCardHidden(id, !hiddenCards.has(id)); });
      item.appendChild(btn);
    });
  }

  // ── Load saved layout ───────────────────────────────────────────────────
  const savedLayout = @json($savedLayout ?? null);
  if (savedLayout && Array.isArray(savedLayout)) {
    grid.load(savedLayout.map(i => ({ id: i.id, x: i.x, y: i.y, w: i.w, h: i.h })), false);
    savedLayout.filter(i => i.hidden).forEach(i => {
      hiddenCards.add(i.id);
      hiddenPositions[i.id] = { x: i.x, y: i.y, w: i.w, h: i.h };
      const el = document.querySelector(`.grid-stack-item[gs-id="${i.id}"]`);
      if (!el) return;
      grid.removeWidget(el, false);
      el.style.display = 'none';
    });
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
    hiddenCards.forEach(id => {
      const el  = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 3, h: 7 };
      el.setAttribute('gs-x', pos.x);
      el.setAttribute('gs-y', pos.y);
      el.setAttribute('gs-w', pos.w);
      el.setAttribute('gs-h', pos.h);
      el.style.display = '';
      grid.makeWidget(el);
      el.classList.add('gs-card-hidden');
    });
    document.body.classList.add('gs-edit-mode');
    fabMain.classList.add('active');
    fabIcon.className = 'mdi mdi-pencil-off';
    toolbar.classList.add('visible');
    addHideButtons();
  }

  function exitEdit() {
    editMode = false;
    hiddenCards.forEach(id => {
      const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const node = el.gridstackNode;
      if (node) hiddenPositions[id] = { x: node.x, y: node.y, w: node.w, h: node.h };
      el.classList.remove('gs-card-hidden');
      grid.removeWidget(el, false);
      el.style.display = 'none';
    });
    grid.setStatic(true);
    document.body.classList.remove('gs-edit-mode');
    fabMain.classList.remove('active');
    fabIcon.className = 'mdi mdi-pencil';
    toolbar.classList.remove('visible');
  }

  fabMain.addEventListener('click', () => editMode ? exitEdit() : enterEdit());

  document.getElementById('gs-save').addEventListener('click', () => {
    const layout = grid.save(false);
    layout.forEach(i => { if (hiddenCards.has(i.id)) i.hidden = true; });
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout, page: 'agent' })
    })
    .then(r => r.json())
    .then(d => { if (d.success) exitEdit(); });
  });

  document.getElementById('gs-reset').addEventListener('click', () => {
    [...hiddenCards].forEach(id => {
      hiddenCards.delete(id);
      delete hiddenPositions[id];
      const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (el) el.classList.remove('gs-card-hidden');
    });
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
