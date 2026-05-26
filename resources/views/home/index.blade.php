@extends('layouts.app')

@section('title', 'Dashboard - One For All')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack.min.css"/>
<style>
  /* GridStack base overrides */
  .grid-stack { background: transparent; }
  .grid-stack-item-content { overflow: auto; }

  /* Edit mode visual cue */
  body.gs-edit-mode .grid-stack-item-content {
    outline: 2px dashed rgba(75, 73, 172, 0.4);
    outline-offset: -2px;
  }
  body.gs-edit-mode .grid-stack {
    background-image: linear-gradient(rgba(75,73,172,.04) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(75,73,172,.04) 1px, transparent 1px);
    background-size: calc(100% / 12) 60px;
  }

  /* Floating pencil FAB — draggable, no action buttons inside */
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

  /* Edit toolbar — fixed, centered at bottom, independent of FAB */
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

  .gs-tb-btn {
    padding: 6px 18px;
    border-radius: 20px;
    border: none;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: opacity .15s;
  }
  .gs-tb-btn:hover { opacity: .82; }
  .gs-tb-btn-save   { background: #27ae60; color: #fff; }
  .gs-tb-btn-reset  { background: #f39c12; color: #fff; }
  .gs-tb-btn-cancel { background: #f0f0f0; color: #333; }

  /* Cards always fill their grid item */
  .gs-card { height: 100%; display: flex; flex-direction: column; }
  .gs-card .card-body { flex: 1; overflow: auto; }

  /* ── Hide card button ── */
  .gs-hide-btn {
    display: none;
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(231,76,60,0.1);
    border: 1px solid rgba(231,76,60,0.35);
    color: #e74c3c;
    font-size: 13px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    z-index: 100;
    transition: background .15s, color .15s, border-color .15s;
    line-height: 1;
  }
  .gs-hide-btn:hover { background: #e74c3c; color: #fff; }
  body.gs-edit-mode .gs-hide-btn { display: flex; }

  /* ── Hidden card state (greyed out in edit mode) ── */
  .gs-card-hidden .grid-stack-item-content {
    opacity: 0.25;
    pointer-events: none;
    filter: grayscale(0.4);
  }
  .gs-card-hidden .gs-hide-btn {
    pointer-events: all;
    background: rgba(39,174,96,0.1);
    border-color: rgba(39,174,96,0.35);
    color: #27ae60;
  }
  .gs-card-hidden .gs-hide-btn:hover { background: #27ae60; color: #fff; }

  /* Hide edit controls on mobile — drag/resize not usable on touch */
  @media (max-width: 767px) {
    #gs-fab, #gs-edit-toolbar { display: none !important; }
  }
</style>
@endpush

@section('content')

{{-- GridStack wrapper --}}
<div class="grid-stack" id="dashboard-grid">

  {{-- Active Agents --}}
  <div class="grid-stack-item" gs-id="active-agents" data-label="Active Agents" gs-x="0" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Active Agents</p>
            <i class="mdi mdi-server-network text-success icon-lg"></i>
          </div>
          <h2 class="font-weight-bold mb-1">{{ $agentStats['active'] }}</h2>
          <p class="text-muted mb-0">Terhubung &amp; berjalan normal</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Disconnected --}}
  <div class="grid-stack-item" gs-id="disconnected" data-label="Disconnected" gs-x="3" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Disconnected</p>
            <i class="mdi mdi-server-off text-danger icon-lg"></i>
          </div>
          <h2 class="font-weight-bold mb-1">{{ $agentStats['disconnected'] }}</h2>
          <p class="text-muted mb-0">Pernah terhubung, kini terputus</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Pending --}}
  <div class="grid-stack-item" gs-id="pending" data-label="Pending" gs-x="6" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Pending</p>
            <i class="mdi mdi-server text-warning icon-lg"></i>
          </div>
          <h2 class="font-weight-bold mb-1">{{ $agentStats['pending'] }}</h2>
          <p class="text-muted mb-0">Menunggu registrasi selesai</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Never Connected --}}
  <div class="grid-stack-item" gs-id="never-connected" data-label="Never Connected" gs-x="9" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Never Connected</p>
            <i class="mdi mdi-server-minus text-secondary icon-lg"></i>
          </div>
          <h2 class="font-weight-bold mb-1">{{ $agentStats['never_connected'] }}</h2>
          <p class="text-muted mb-0">Belum pernah terhubung sama sekali</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Total Agents --}}
  <div class="grid-stack-item" gs-id="total-agents" data-label="Total Agents" gs-x="0" gs-y="4" gs-w="3" gs-h="5">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Total Agents</p>
            <i class="mdi mdi-server-network text-primary icon-lg"></i>
          </div>
          <h2 class="font-weight-bold mb-1">{{ $agentStats['total'] }}</h2>
          <p class="text-muted mb-0">
            @if($agentStats['change'] >= 0)
              <span class="text-success me-1"><i class="mdi mdi-arrow-up"></i>{{ $agentStats['change'] }}</span>
            @else
              <span class="text-danger me-1"><i class="mdi mdi-arrow-down"></i>{{ abs($agentStats['change']) }}</span>
            @endif
            dari bulan lalu
          </p>
        </div>
      </div>
    </div>
  </div>

  @if($customerStats)
  {{-- Total Customers (admin only) --}}
  <div class="grid-stack-item" gs-id="total-customers" data-label="Total Customers" gs-x="3" gs-y="4" gs-w="3" gs-h="5">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Total Customers</p>
            <i class="mdi mdi-account-group text-info icon-lg"></i>
          </div>
          <h2 class="font-weight-bold mb-1">{{ $customerStats['total'] }}</h2>
          <p class="text-muted mb-0">
            @if($customerStats['change'] >= 0)
              <span class="text-success me-1"><i class="mdi mdi-arrow-up"></i>{{ $customerStats['change'] }}</span>
            @else
              <span class="text-danger me-1"><i class="mdi mdi-arrow-down"></i>{{ abs($customerStats['change']) }}</span>
            @endif
            dari bulan lalu
          </p>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- Agent Composition --}}
  @php
    $compX = $customerStats ? 6 : 3;
    $compW = $customerStats ? 6 : 9;
    $total = $agentStats['total'] ?: 1;
    $activePct         = round($agentStats['active'] / $total * 100, 1);
    $disconnectedPct   = round($agentStats['disconnected'] / $total * 100, 1);
    $pendingPct        = round($agentStats['pending'] / $total * 100, 1);
    $neverConnectedPct = round($agentStats['never_connected'] / $total * 100, 1);
  @endphp
  <div class="grid-stack-item" gs-id="agent-composition" data-label="Komposisi Status Agent" gs-x="{{ $compX }}" gs-y="4" gs-w="{{ $compW }}" gs-h="5">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title mb-1">Komposisi Status Agent</p>
          <p class="text-muted mb-3">Dari total {{ $agentStats['total'] }} agent terdaftar</p>
          <div class="d-flex justify-content-between mb-1"><span class="text-success">Active</span><span class="font-weight-bold">{{ $agentStats['active'] }} ({{ $activePct }}%)</span></div>
          <div class="progress mb-2" style="height:8px"><div class="progress-bar bg-success" style="width:{{ $activePct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-danger">Disconnected</span><span class="font-weight-bold">{{ $agentStats['disconnected'] }} ({{ $disconnectedPct }}%)</span></div>
          <div class="progress mb-2" style="height:8px"><div class="progress-bar bg-danger" style="width:{{ $disconnectedPct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-warning">Pending</span><span class="font-weight-bold">{{ $agentStats['pending'] }} ({{ $pendingPct }}%)</span></div>
          <div class="progress mb-2" style="height:8px"><div class="progress-bar bg-warning" style="width:{{ $pendingPct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Never Connected</span><span class="font-weight-bold">{{ $agentStats['never_connected'] }} ({{ $neverConnectedPct }}%)</span></div>
          <div class="progress" style="height:8px"><div class="progress-bar bg-secondary" style="width:{{ $neverConnectedPct }}%"></div></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Alert Trend --}}
  <div class="grid-stack-item" gs-id="alert-trend" data-label="Alert Trend" gs-x="0" gs-y="8" gs-w="5" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <p class="card-title mb-0">Alert Trend (7 Hari)</p>
            <span class="badge badge-success badge-pill">Live</span>
          </div>
          <p class="text-muted mb-3">Pergerakan alert harian seluruh agent</p>
          <canvas id="alert-trend-chart"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Severity Chart --}}
  @php
    $total_alerts = $totalAlerts ?? 0;
    $critical_pct = $total_alerts > 0 ? round($alertSeverity['critical'] / $total_alerts * 100, 1) : 0;
    $high_pct     = $total_alerts > 0 ? round($alertSeverity['high']     / $total_alerts * 100, 1) : 0;
    $medium_pct   = $total_alerts > 0 ? round($alertSeverity['medium']   / $total_alerts * 100, 1) : 0;
    $low_pct      = $total_alerts > 0 ? round($alertSeverity['low']      / $total_alerts * 100, 1) : 0;
  @endphp
  <div class="grid-stack-item" gs-id="severity-chart" data-label="Severity Chart" gs-x="5" gs-y="8" gs-w="4" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title">Alerts by Rule Level</p>
          <p class="text-muted mb-1">Level 1–5 = Low, 6–8 = Medium, 9–11 = High, 12–15 = Critical.</p>
          <canvas id="severity-chart"></canvas>
          <div class="d-flex justify-content-around mt-3">
            <span class="badge badge-danger">Critical</span>
            <span class="badge badge-warning">High</span>
            <span class="badge badge-info">Medium</span>
            <span class="badge badge-success">Low</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Total Alerts --}}
  <div class="grid-stack-item" gs-id="total-alerts" data-label="Total Alerts" gs-x="9" gs-y="8" gs-w="3" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title">Total Security Alerts</p>
          <h2 class="font-weight-bold mb-1">{{ number_format($totalAlerts) }}</h2>
          <p class="text-muted mb-3">Dari seluruh agent</p>
          <div class="d-flex justify-content-between mb-1"><span class="text-danger">Critical</span><span class="font-weight-bold">{{ number_format($alertSeverity['critical']) }}</span></div>
          <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-danger" style="width:{{ $critical_pct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-warning">High</span><span class="font-weight-bold">{{ number_format($alertSeverity['high']) }}</span></div>
          <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-warning" style="width:{{ $high_pct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-info">Medium</span><span class="font-weight-bold">{{ number_format($alertSeverity['medium']) }}</span></div>
          <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-info" style="width:{{ $medium_pct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-success">Low</span><span class="font-weight-bold">{{ number_format($alertSeverity['low']) }}</span></div>
          <div class="progress" style="height:6px"><div class="progress-bar bg-success" style="width:{{ $low_pct }}%"></div></div>
        </div>
      </div>
    </div>
  </div>

  {{-- OS Distribution --}}
  <div class="grid-stack-item" gs-id="os-distribution" data-label="OS Distribution" gs-x="0" gs-y="15" gs-w="4" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title">OS Distribution</p>
          <p class="text-muted mb-3">Sistem operasi dari seluruh agent</p>
          <canvas id="os-chart"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Top Rules --}}
  <div class="grid-stack-item" gs-id="top-rules" data-label="Top Rules" gs-x="4" gs-y="15" gs-w="8" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title">Top Rules Paling Sering Trigger</p>
          <p class="text-muted mb-3">Data dari OpenSearch — aggregasi <code>rule.id</code></p>
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead><tr><th>Rule ID</th><th>Description</th><th>Level</th><th>Count</th></tr></thead>
              <tbody>
                @forelse($topRules as $rule)
                <tr>
                  <td><span class="badge badge-secondary">{{ $rule['id'] }}</span></td>
                  <td>{{ $rule['description'] }}</td>
                  <td>
                    @php $lc = $rule['level'] >= 12 ? 'danger' : ($rule['level'] >= 9 ? 'warning' : ($rule['level'] >= 6 ? 'info' : 'success')); @endphp
                    <span class="badge badge-{{ $lc }}">{{ $rule['level'] }}</span>
                  </td>
                  <td class="font-weight-bold">{{ number_format($rule['count']) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No data available</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Top Agents --}}
  <div class="grid-stack-item" gs-id="top-agents" data-label="Top Agents" gs-x="0" gs-y="22" gs-w="12" gs-h="7">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Top Agents dengan Security Alert Terbanyak</p>
            <a href="/agent" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
          </div>
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead><tr><th>Agent ID</th><th>Agent Name</th><th>IP</th><th>OS</th><th>Total Alerts</th><th>Bar</th></tr></thead>
              <tbody>
                @php $maxAlerts = collect($topAgents)->max('alert_count') ?? 1; @endphp
                @forelse($topAgents as $agent)
                <tr>
                  <td><span class="badge badge-secondary">{{ $agent['id'] }}</span></td>
                  <td class="font-weight-bold">{{ $agent['name'] }}</td>
                  <td>{{ $agent['ip'] }}</td>
                  <td>
                    @php
                      $osIcon = 'mdi-linux';
                      if (stripos($agent['os'], 'windows') !== false) $osIcon = 'mdi-microsoft-windows';
                      elseif (stripos($agent['os'], 'mac') !== false) $osIcon = 'mdi-apple';
                    @endphp
                    <i class="mdi {{ $osIcon }} me-1"></i>{{ $agent['os'] }}
                  </td>
                  <td class="font-weight-bold text-danger">{{ number_format($agent['alert_count']) }}</td>
                  <td style="min-width:120px">
                    <div class="progress" style="height:8px">
                      <div class="progress-bar bg-danger" style="width:{{ ($agent['alert_count'] / $maxAlerts) * 100 }}%"></div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No data available</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>{{-- /grid-stack --}}

{{-- Floating pencil (draggable) --}}
<div id="gs-fab">
  <button id="gs-fab-main" title="Edit layout">
    <i class="mdi mdi-pencil" id="gs-fab-icon"></i>
  </button>
</div>

{{-- Edit toolbar (fixed, centered bottom) --}}
<div id="gs-edit-toolbar">
  <button class="gs-tb-btn gs-tb-btn-save"   id="gs-save">  <i class="mdi mdi-content-save me-1"></i>Save</button>
  <button class="gs-tb-btn gs-tb-btn-reset"  id="gs-reset"> <i class="mdi mdi-restore me-1"></i>Reset</button>
  <button class="gs-tb-btn gs-tb-btn-cancel" id="gs-cancel"><i class="mdi mdi-close me-1"></i>Cancel</button>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack-all.js"></script>
<script>
(function () {
  // ── Default layout (matches HTML gs-* attrs) ──────────────────────────────
  const hasCustomers = {{ $customerStats ? 'true' : 'false' }};
  const DEFAULT_LAYOUT = [
    { id: 'active-agents',     x: 0,                   y: 0,  w: 3,                   h: 4 },
    { id: 'disconnected',      x: 3,                   y: 0,  w: 3,                   h: 4 },
    { id: 'pending',           x: 6,                   y: 0,  w: 3,                   h: 4 },
    { id: 'never-connected',   x: 9,                   y: 0,  w: 3,                   h: 4 },
    { id: 'total-agents',      x: 0,                   y: 4,  w: 3,                   h: 4 },
    ...(hasCustomers ? [{ id: 'total-customers', x: 3, y: 4,  w: 3,                   h: 4 }] : []),
    { id: 'agent-composition', x: hasCustomers ? 6 : 3, y: 4, w: hasCustomers ? 6 : 9, h: 8 },
    { id: 'alert-trend',       x: 0,                   y: 8,  w: 5,                   h: 7 },
    { id: 'severity-chart',    x: 5,                   y: 8,  w: 4,                   h: 7 },
    { id: 'total-alerts',      x: 9,                   y: 8,  w: 3,                   h: 7 },
    { id: 'os-distribution',   x: 0,                   y: 15, w: 4,                   h: 7 },
    { id: 'top-rules',         x: 4,                   y: 15, w: 8,                   h: 7 },
    { id: 'top-agents',        x: 0,                   y: 22, w: 12,                  h: 7 },
  ];

  // ── Init GridStack ─────────────────────────────────────────────────────────
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

  // ── Hidden cards state ─────────────────────────────────────────────────────
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

  // ── Load saved layout ──────────────────────────────────────────────────────
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

  // Chart references for resize
  const charts = {};

  // ── Charts ─────────────────────────────────────────────────────────────────
  function noData(canvasId) {
    const el = document.getElementById(canvasId);
    if (!el) return;
    el.style.display = 'none';
    const msg = document.createElement('div');
    msg.style.cssText = 'display:flex;align-items:center;justify-content:center;height:120px;color:#999;font-size:14px;';
    msg.textContent = 'No data available';
    el.parentElement.appendChild(msg);
  }

  function generateDateLabels(n) {
    const days   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const today  = new Date();
    return Array.from({length: n}, (_, i) => {
      const d = new Date(today); d.setDate(d.getDate() - (n - 1 - i));
      return `${days[d.getDay()]} ${String(d.getDate()).padStart(2,'0')} ${months[d.getMonth()]}`;
    });
  }

  const trendData = @json($alertTrend ?? []);
  if (trendData.length > 0) {
    charts.trend = new Chart(document.getElementById('alert-trend-chart'), {
      type: 'line',
      data: {
        labels: generateDateLabels(trendData.length),
        datasets: [{ label: 'Total Alerts', data: trendData, borderColor: '#4B49AC', backgroundColor: 'rgba(75,73,172,.1)', borderWidth: 2, fill: true, tension: .4, pointBackgroundColor: '#4B49AC', pointRadius: 4 }]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
    });
  } else { noData('alert-trend-chart'); }

  const sevData  = @json($alertSeverity);
  const sevTotal = sevData.critical + sevData.high + sevData.medium + sevData.low;
  if (sevTotal > 0) {
    charts.severity = new Chart(document.getElementById('severity-chart'), {
      type: 'doughnut',
      data: {
        labels: ['Critical','High','Medium','Low'],
        datasets: [{ data: [sevData.critical, sevData.high, sevData.medium, sevData.low], backgroundColor: ['#FF4747','#FFC542','#17C1E8','#82D616'], borderWidth: 0, hoverOffset: 6 }]
      },
      options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
    });
  } else { noData('severity-chart'); }

  const osLabels = @json(array_keys($osDistribution ?? []));
  const osData   = @json(array_values($osDistribution ?? []));
  if (osData.reduce((a,b)=>a+b,0) > 0) {
    charts.os = new Chart(document.getElementById('os-chart'), {
      type: 'bar',
      data: {
        labels: osLabels,
        datasets: [{ label: 'Agents', data: osData, backgroundColor: ['#4B49AC','#7978E9','#F3797E','#FFC542','#82D616'], borderRadius: 6, borderWidth: 0 }]
      },
      options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true }, y: { grid: { display: false } } } }
    });
  } else { noData('os-chart'); }

  // Resize charts when a grid item is resized
  grid.on('resizestop', () => {
    Object.values(charts).forEach(c => c.resize());
  });

  // ── Edit mode ──────────────────────────────────────────────────────────────
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
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 3, h: 4 };
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

  document.getElementById('gs-save').addEventListener('click', () => {
    const layout = grid.save(false);
    layout.forEach(i => { if (hiddenCards.has(i.id)) i.hidden = true; });
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout, page: 'home' })
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
    Object.values(charts).forEach(c => c.resize());
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });

  fabMain.addEventListener('click', () => editMode ? exitEdit() : enterEdit());

})();
</script>
@endpush
