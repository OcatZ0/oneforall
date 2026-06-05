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
            <span><span class="badge bg-success me-2">&nbsp;&nbsp;&nbsp;</span> Active</span>
            <span class="fw-bold">{{ $stats['active'] }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
            <span><span class="badge bg-danger me-2">&nbsp;&nbsp;&nbsp;</span> Disconnected</span>
            <span class="fw-bold">{{ $stats['disconnected'] }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
            <span><span class="badge bg-warning text-dark me-2">&nbsp;&nbsp;&nbsp;</span> Pending</span>
            <span class="fw-bold">{{ $stats['pending'] }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span><span class="badge bg-secondary me-2">&nbsp;&nbsp;&nbsp;</span> Never Connected</span>
            <span class="fw-bold">{{ $stats['never_connected'] }}</span>
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
              <h4 class="text-success fw-bold">{{ $stats['active'] }}</h4>
            </div>
            <div class="col-6">
              <p class="text-muted mb-1">Disconnected</p>
              <h4 class="text-danger fw-bold">{{ $stats['disconnected'] }}</h4>
            </div>
            <div class="col-6">
              <p class="text-muted mb-1">Pending</p>
              <h4 class="text-warning fw-bold">{{ $stats['pending'] }}</h4>
            </div>
            <div class="col-6">
              <p class="text-muted mb-1">Never Connected</p>
              <h4 class="text-secondary fw-bold">{{ $stats['never_connected'] }}</h4>
            </div>
            <div class="col-12">
              <p class="text-muted mb-1">Coverage</p>
              <h4 class="text-success fw-bold">{{ $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100) : 0 }}%</h4>
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
          <p class="mb-2"><span class="text-success me-1">●</span> <small>active</small></p>
          <div id="evolution-chart-container" style="position:relative;">
            <canvas id="evolution-chart" height="100"></canvas>
          </div>
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
                <i class="mdi mdi-refresh me-1"></i> Refresh
              </button>
              @if(auth()->user()->peran === 'admin')
              <button class="btn btn-sm btn-success" id="syncBtn" onclick="syncAgentsFromWazuh()">
                <i class="mdi mdi-refresh me-1"></i><span id="syncBtnText">Update Data Agent</span>
              </button>
              @endif
            </div>
          </div>

          <form method="GET" action="{{ route('agent') }}" id="filterForm" class="mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="input-group" style="max-width:400px">
                <span class="input-group-text bg-white border-end-0">
                  <i class="mdi mdi-magnify text-muted"></i>
                </span>
                <input type="text" id="searchInput" name="search" class="form-control border-start-0"
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
                <i class="mdi mdi-refresh me-1"></i>Reset
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
              <tbody id="agent-tbody">
                @forelse($agents as $agent)
                <tr onclick="window.location='{{ route('agent.detail', $agent->id_agent) }}'" style="cursor: pointer;">
                  <td>{{ ($agents->currentPage() - 1) * $agents->perPage() + $loop->iteration }}</td>
                  <td class="fw-bold">{{ $agent->id_agent }}</td>
                  <td>{{ $agent->nama }}</td>
                  <td>{{ $agent->ip }}</td>
                  <td>
                    <i class="mdi {{ \App\Http\Controllers\AgentController::getOSIcon($agent->os) }} me-1"></i>
                    <small>{{ $agent->os }}</small>
                  </td>
                  <td><small class="text-muted">{{ $agent->version }}</small></td>
                  <td>
                    @if($agent->user)
                      <span class="badge bg-primary">{{ $agent->user->username }}</span>
                    @else
                      <span class="text-muted fst-italic">Unassigned</span>
                    @endif
                  </td>
                  <td><small class="text-muted">{{ $agent->cluster_node }}</small></td>
                  <td>
                    <span class="badge bg-{{ \App\Http\Controllers\AgentController::getStatusBadgeColor($agent->status) }}">
                      {{ \App\Http\Controllers\AgentController::formatStatus($agent->status) }}
                    </span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="9" class="text-center py-5 text-muted">
                    <span class="mdi mdi-server-network-off d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada agent</span>
                    <span class="d-block small">Coba ubah filter pencarian atau tambahkan agent baru</span>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div id="agent-pagination-footer">
          @if($agents->count() > 0)
          <div class="d-flex align-items-center justify-content-between mt-3">
            <div class="d-flex align-items-center">
              <span class="text-muted me-2">Rows per page:</span>
              <div class="d-flex gap-1">
                @foreach([10, 25, 50] as $pp)
                <button onclick="loadAgents(1, {{ $pp }})"
                  class="btn btn-sm py-0 px-2 {{ (int) request('per_page', 10) === $pp ? 'btn-primary' : 'btn-outline-secondary' }}">
                  {{ $pp }}
                </button>
                @endforeach
              </div>
            </div>
            <div class="d-flex align-items-center gap-1">
              <button {{ $agents->currentPage() <= 1 ? 'disabled' : '' }} onclick="loadAgents(1, {{ $agents->perPage() }})" class="btn btn-sm py-0 px-2 btn-outline-secondary{{ $agents->currentPage() <= 1 ? ' disabled' : '' }}">«</button>
              <button {{ $agents->currentPage() <= 1 ? 'disabled' : '' }} onclick="loadAgents({{ max(1, $agents->currentPage() - 1) }}, {{ $agents->perPage() }})" class="btn btn-sm py-0 px-2 btn-outline-secondary{{ $agents->currentPage() <= 1 ? ' disabled' : '' }}">‹</button>
              @for($p = max(1, $agents->currentPage() - 2); $p <= min($agents->lastPage(), $agents->currentPage() + 2); $p++)
              <button onclick="loadAgents({{ $p }}, {{ $agents->perPage() }})" class="btn btn-sm py-0 px-2 {{ $p === $agents->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $p }}</button>
              @endfor
              <button {{ $agents->currentPage() >= $agents->lastPage() ? 'disabled' : '' }} onclick="loadAgents({{ min($agents->lastPage(), $agents->currentPage() + 1) }}, {{ $agents->perPage() }})" class="btn btn-sm py-0 px-2 btn-outline-secondary{{ $agents->currentPage() >= $agents->lastPage() ? ' disabled' : '' }}">›</button>
              <button {{ $agents->currentPage() >= $agents->lastPage() ? 'disabled' : '' }} onclick="loadAgents({{ $agents->lastPage() }}, {{ $agents->perPage() }})" class="btn btn-sm py-0 px-2 btn-outline-secondary{{ $agents->currentPage() >= $agents->lastPage() ? ' disabled' : '' }}">»</button>
            </div>
          </div>
          <div class="text-muted small mt-2" id="agent-count-text">
            Menampilkan {{ ($agents->currentPage() - 1) * $agents->perPage() + 1 }}–{{ min($agents->currentPage() * $agents->perPage(), $agents->total()) }} dari {{ $agents->total() }} agent
          </div>
          @endif
          </div>

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
  const container = document.getElementById('evolution-chart-container');
  if (!container) return;

  if (labels.length === 0 || dataPoints.length === 0) {
    if (evolutionChartInstance) { evolutionChartInstance.destroy(); evolutionChartInstance = null; }
    container.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center text-muted py-4 text-center" style="min-height:100px;">
      <span class="mdi mdi-chart-line-variant" style="font-size:2.5rem; opacity:0.3; margin-bottom:8px;"></span>
      <span class="fw-semibold mb-1">Tidak ada data</span>
      <span class="small">Tidak ada data evolution dalam periode ini</span>
    </div>`;
    return;
  }

  if (!document.getElementById('evolution-chart')) {
    container.innerHTML = '<canvas id="evolution-chart" height="100"></canvas>';
  }

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

  const container = document.getElementById('evolution-chart-container');
  if (container) container.style.opacity = '0.5';

  fetch('{{ route("agent.chart-data") }}?time_range=' + timeRange)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const dataPoints = Array.isArray(data.data) ? data.data : (data.data.active ?? []);
        const labels = data.labels ?? [];
        initChart(labels, dataPoints);
        document.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
        if (event?.target) event.target.classList.add('active');
      }
      if (container) container.style.opacity = '1';
    })
    .catch(error => {
      console.error('Error fetching chart data:', error);
      if (container) container.style.opacity = '1';
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
  initChart(labels, dataPoints);
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
    .then(d => { if (d.success) { exitEdit(); gsShowSavedToast(); } });
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

})();

// ── AJAX search & filter ──────────────────────────────────────────────────────
const searchEndpoint = '{{ route("agent.search") }}';
let searchPage    = {{ (int) request('page', 1) }};
let searchPerPage = {{ (int) request('per_page', 10) }};

function escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s != null ? String(s) : '';
  return d.innerHTML;
}

function getOsIcon(os) {
  if (!os) return 'mdi-help-circle-outline';
  const l = os.toLowerCase();
  if (l.includes('windows')) return 'mdi-microsoft-windows';
  if (l.includes('ubuntu') || l.includes('debian') || l.includes('linux') || l.includes('centos') || l.includes('rhel') || l.includes('fedora')) return 'mdi-linux';
  if (l.includes('mac') || l.includes('darwin')) return 'mdi-apple';
  return 'mdi-help-circle-outline';
}

function getStatusColor(status) {
  return { active: 'success', disconnected: 'danger', pending: 'warning', never_connected: 'secondary' }[status] || 'secondary';
}

function getStatusLabel(status) {
  return { active: 'Active', disconnected: 'Disconnected', pending: 'Pending', never_connected: 'Never Connected' }[status] || status;
}

function renderPaginationFooter(data) {
  const totalPages = data.totalPages || 1;
  const page = data.page;
  const perPage = data.perPage;
  const btn = (p, pp, label, disabled, active) =>
    `<button ${disabled ? 'disabled' : `onclick="loadAgents(${p},${pp})"`} class="btn btn-sm py-0 px-2 ${active ? 'btn-primary' : 'btn-outline-secondary'}${disabled ? ' disabled' : ''}">${label}</button>`;
  const ppBtns = [10, 25, 50].map(pp => btn(1, pp, pp, false, perPage === pp)).join('');
  const winBtns = [];
  for (let p = Math.max(1, page - 2); p <= Math.min(totalPages, page + 2); p++) winBtns.push(btn(p, perPage, p, false, p === page));

  return `<div class="d-flex align-items-center justify-content-between mt-3">
    <div class="d-flex align-items-center">
      <span class="text-muted me-2">Rows per page:</span>
      <div class="d-flex gap-1">${ppBtns}</div>
    </div>
    <div class="d-flex align-items-center gap-1">
      ${btn(1, perPage, '«', page <= 1, false)}
      ${btn(Math.max(1, page - 1), perPage, '‹', page <= 1, false)}
      ${winBtns.join('')}
      ${btn(Math.min(totalPages, page + 1), perPage, '›', page >= totalPages, false)}
      ${btn(totalPages, perPage, '»', page >= totalPages, false)}
    </div>
  </div>
  <div class="text-muted small mt-2" id="agent-count-text">Menampilkan ${data.from}–${data.to} dari ${data.total} agent</div>`;
}

async function loadAgents(page, perPage) {
  searchPage    = page    || searchPage;
  searchPerPage = perPage || searchPerPage;

  const search = document.getElementById('searchInput')?.value || '';
  const status = document.getElementById('statusFilter')?.value || '';
  const params = new URLSearchParams({ page: searchPage, per_page: searchPerPage });
  if (search) params.set('search', search);
  if (status) params.set('status', status);

  // Update URL without reload
  const url = new URL(window.location.href);
  url.searchParams.set('page', searchPage);
  url.searchParams.set('per_page', searchPerPage);
  if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
  if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
  window.history.replaceState({}, '', url);

  const tbody = document.getElementById('agent-tbody');
  const footer = document.getElementById('agent-pagination-footer');
  if (tbody) tbody.style.opacity = '0.5';

  try {
    const res  = await fetch(`${searchEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    if (data.error) throw new Error(data.error);

    if (tbody) {
      if (data.agents.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">
          <span class="mdi mdi-server-network-off d-block" style="font-size:2.5rem;opacity:.35;margin-bottom:8px;"></span>
          <span class="d-block fw-semibold mb-1">Tidak ada agent</span>
          <span class="d-block small">Coba ubah filter pencarian atau tambahkan agent baru</span>
        </td></tr>`;
      } else {
        tbody.innerHTML = data.agents.map((a, i) => {
          const rowNum = (searchPage - 1) * searchPerPage + i + 1;
          const osIcon = getOsIcon(a.os);
          const statusColor = getStatusColor(a.status);
          const statusLabel = getStatusLabel(a.status);
          const userBadge = a.user
            ? `<span class="badge bg-primary">${escHtml(a.user.username)}</span>`
            : `<span class="text-muted fst-italic">Unassigned</span>`;
          return `<tr onclick="window.location='/agent/${escHtml(a.id_agent)}/detail'" style="cursor:pointer;">
            <td>${rowNum}</td>
            <td class="fw-bold">${escHtml(a.id_agent)}</td>
            <td>${escHtml(a.nama)}</td>
            <td>${escHtml(a.ip)}</td>
            <td><i class="mdi ${osIcon} me-1"></i><small>${escHtml(a.os)}</small></td>
            <td><small class="text-muted">${escHtml(a.version)}</small></td>
            <td>${userBadge}</td>
            <td><small class="text-muted">${escHtml(a.cluster_node)}</small></td>
            <td><span class="badge bg-${statusColor}">${statusLabel}</span></td>
          </tr>`;
        }).join('');
      }
      tbody.style.opacity = '1';
    }

    if (footer) footer.innerHTML = renderPaginationFooter(data);

  } catch (e) {
    console.error('loadAgents error', e);
    if (tbody) tbody.style.opacity = '1';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const searchInput  = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');

  function debounce(fn, ms) {
    let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
  }

  if (searchInput) {
    searchInput.addEventListener('input', debounce(() => loadAgents(1, searchPerPage), 400));
  }
  if (statusFilter) {
    statusFilter.addEventListener('change', () => loadAgents(1, searchPerPage));
  }
});
</script>
@endpush
