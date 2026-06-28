@extends('layouts.app')

@section('title', 'Dashboard - One For All')

@push('styles')
@include('partials._gridstack-styles')
@endpush

@section('content')

{{-- GridStack wrapper --}}
<div class="grid-stack" id="dashboard-grid">

  {{-- Active Agents --}}
  <div class="grid-stack-item" gs-id="active-agents" data-label="Agen Aktif" gs-x="0" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Agen Aktif</p>
            <i class="mdi mdi-server-network text-success icon-lg"></i>
          </div>
          <h2 class="fw-bold mb-1">{{ $agentStats['active'] }}</h2>
          <p class="text-muted mb-0">Terhubung &amp; berjalan normal</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Disconnected --}}
  <div class="grid-stack-item" gs-id="disconnected" data-label="Terputus" gs-x="3" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Terputus</p>
            <i class="mdi mdi-server-off text-danger icon-lg"></i>
          </div>
          <h2 class="fw-bold mb-1">{{ $agentStats['disconnected'] }}</h2>
          <p class="text-muted mb-0">Pernah terhubung, kini terputus</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Pending --}}
  <div class="grid-stack-item" gs-id="pending" data-label="Menunggu" gs-x="6" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Menunggu</p>
            <i class="mdi mdi-server text-warning icon-lg"></i>
          </div>
          <h2 class="fw-bold mb-1">{{ $agentStats['pending'] }}</h2>
          <p class="text-muted mb-0">Menunggu registrasi selesai</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Never Connected --}}
  <div class="grid-stack-item" gs-id="never-connected" data-label="Tidak Pernah Terhubung" gs-x="9" gs-y="0" gs-w="3" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Tidak Pernah Terhubung</p>
            <i class="mdi mdi-server-minus text-secondary icon-lg"></i>
          </div>
          <h2 class="fw-bold mb-1">{{ $agentStats['never_connected'] }}</h2>
          <p class="text-muted mb-0">Belum pernah terhubung sama sekali</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Total Agents --}}
  <div class="grid-stack-item" gs-id="total-agents" data-label="Total Agen" gs-x="0" gs-y="4" gs-w="3" gs-h="5">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Total Agen</p>
            <i class="mdi mdi-server-network text-primary icon-lg"></i>
          </div>
          <h2 class="fw-bold mb-1">{{ $agentStats['total'] }}</h2>
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
  <div class="grid-stack-item" gs-id="total-customers" data-label="Total Pelanggan" gs-x="3" gs-y="4" gs-w="3" gs-h="5">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="card-title mb-0">Total Pelanggan</p>
            <i class="mdi mdi-account-group text-info icon-lg"></i>
          </div>
          <h2 class="fw-bold mb-1">{{ $customerStats['total'] }}</h2>
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
          <div class="d-flex justify-content-between mb-1"><span class="text-success">Aktif</span><span class="fw-bold">{{ $agentStats['active'] }} ({{ $activePct }}%)</span></div>
          <div class="progress mb-2" style="height:8px"><div class="progress-bar bg-success" style="width:{{ $activePct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-danger">Terputus</span><span class="fw-bold">{{ $agentStats['disconnected'] }} ({{ $disconnectedPct }}%)</span></div>
          <div class="progress mb-2" style="height:8px"><div class="progress-bar bg-danger" style="width:{{ $disconnectedPct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-warning">Menunggu</span><span class="fw-bold">{{ $agentStats['pending'] }} ({{ $pendingPct }}%)</span></div>
          <div class="progress mb-2" style="height:8px"><div class="progress-bar bg-warning" style="width:{{ $pendingPct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Tidak Pernah Terhubung</span><span class="fw-bold">{{ $agentStats['never_connected'] }} ({{ $neverConnectedPct }}%)</span></div>
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
            <p class="card-title mb-0">Tren Peringatan (7 Hari)</p>
            <span class="badge bg-success badge-pill">Live</span>
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
          <p class="card-title">Peringatan per Level Aturan</p>
          <p class="text-muted mb-1">Level 1–5 = Rendah, 6–8 = Sedang, 9–11 = Tinggi, 12–15 = Kritis.</p>
          <canvas id="severity-chart"></canvas>
          <div class="d-flex justify-content-around mt-3">
            <span class="badge bg-danger">Kritis</span>
            <span class="badge bg-warning text-white">Tinggi</span>
            <span class="badge bg-info text-white">Sedang</span>
            <span class="badge bg-success">Rendah</span>
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
          <p class="card-title">Total Peringatan Keamanan</p>
          <h2 class="fw-bold mb-1">{{ number_format($totalAlerts) }}</h2>
          <p class="text-muted mb-3">Dari seluruh agent</p>
          <div class="d-flex justify-content-between mb-1"><span class="text-danger">Kritis</span><span class="fw-bold">{{ number_format($alertSeverity['critical']) }}</span></div>
          <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-danger" style="width:{{ $critical_pct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-warning">Tinggi</span><span class="fw-bold">{{ number_format($alertSeverity['high']) }}</span></div>
          <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-warning" style="width:{{ $high_pct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-info">Sedang</span><span class="fw-bold">{{ number_format($alertSeverity['medium']) }}</span></div>
          <div class="progress mb-3" style="height:6px"><div class="progress-bar bg-info" style="width:{{ $medium_pct }}%"></div></div>
          <div class="d-flex justify-content-between mb-1"><span class="text-success">Rendah</span><span class="fw-bold">{{ number_format($alertSeverity['low']) }}</span></div>
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
          <p class="card-title">Distribusi OS</p>
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
              <thead><tr><th>Rule ID</th><th>Deskripsi</th><th>Level</th><th>Jumlah</th></tr></thead>
              <tbody>
                @forelse($topRules as $rule)
                <tr>
                  <td><span class="badge bg-secondary">{{ $rule['id'] }}</span></td>
                  <td>{{ $rule['description'] }}</td>
                  <td>
                    @php $lc = $rule['level'] >= 12 ? 'danger' : ($rule['level'] >= 9 ? 'warning' : ($rule['level'] >= 6 ? 'info' : 'success')); @endphp
                    <span class="badge bg-{{ $lc }} {{ in_array($lc, ['warning','info']) ? 'text-dark' : '' }}">{{ $rule['level'] }}</span>
                  </td>
                  <td class="fw-bold">{{ number_format($rule['count']) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center py-5 text-muted">
                    <span class="mdi mdi-shield-check-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada alert</span>
                    <span class="d-block small">Tidak ada event keamanan dalam periode ini</span>
                  </td>
                </tr>
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
              <thead><tr><th>ID Agen</th><th>Nama Agen</th><th>IP</th><th>OS</th><th>Total Peringatan</th><th>Proporsi</th></tr></thead>
              <tbody>
                @php $maxAlerts = collect($topAgents)->max('alert_count') ?? 1; @endphp
                @forelse($topAgents as $agent)
                <tr>
                  <td><span class="badge bg-secondary">{{ $agent['id'] }}</span></td>
                  <td class="fw-bold">{{ $agent['name'] }}</td>
                  <td>{{ $agent['ip'] }}</td>
                  <td>
                    @php
                      $osIcon = 'mdi-linux';
                      if (stripos($agent['os'], 'windows') !== false) $osIcon = 'mdi-microsoft-windows';
                      elseif (stripos($agent['os'], 'mac') !== false) $osIcon = 'mdi-apple';
                    @endphp
                    <i class="mdi {{ $osIcon }} me-1"></i>{{ $agent['os'] }}
                  </td>
                  <td class="fw-bold text-danger">{{ number_format($agent['alert_count']) }}</td>
                  <td style="min-width:120px">
                    <div class="progress" style="height:8px">
                      <div class="progress-bar bg-danger" style="width:{{ ($agent['alert_count'] / $maxAlerts) * 100 }}%"></div>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <span class="mdi mdi-server-network-off d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada agent</span>
                    <span class="d-block small">Belum ada agent dengan alert keamanan</span>
                  </td>
                </tr>
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
  <button class="gs-tb-btn gs-tb-btn-save"   id="gs-save">  <i class="mdi mdi-content-save me-1"></i>Simpan</button>
  <button class="gs-tb-btn gs-tb-btn-reset"  id="gs-reset"> <i class="mdi mdi-restore me-1"></i>Reset</button>
  <button class="gs-tb-btn gs-tb-btn-cancel" id="gs-cancel"><i class="mdi mdi-close me-1"></i>Batal</button>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/drag-drop-touch@1.3.1/DragDropTouch.js"></script>
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
    draggable: { handle: '.gs-drag-handle' },
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
      if (!item.querySelector('.gs-drag-handle')) {
        const dragHandle = document.createElement('div');
        dragHandle.className = 'gs-drag-handle';
        dragHandle.title = 'Seret untuk memindahkan';
        dragHandle.innerHTML = '<i class="mdi mdi-drag"></i>';
        item.appendChild(dragHandle);
      }
    });
  }

  // ── Load saved layout ──────────────────────────────────────────────────────
  const isMobileLayout = window.innerWidth <= 768;
  // Separate DB rows for mobile/desktop so saves don't overwrite each other
  const savedLayout = isMobileLayout
    ? @json($savedLayoutMobile ?? null)
    : @json($savedLayout ?? null);

  function applyLoadedLayout() {
    if (!savedLayout || Array.isArray(savedLayout) || !savedLayout.items) return;
    const items = savedLayout.items ?? [];
    if (isMobileLayout) {
      // grid.load() corrupts internal responsive state in 1-col mode.
      // Use direct grid.update() per item, sorted by saved y to preserve order.
      grid.batchUpdate();
      [...items].sort((a, b) => a.y - b.y).forEach(item => {
        const el = document.querySelector(`.grid-stack-item[gs-id="${item.id}"]`);
        if (el && el.gridstackNode) grid.update(el, { x: 0, y: item.y, w: 1, h: item.h });
      });
      grid.batchUpdate(false);
    } else {
      grid.load(items.map(i => ({ id: i.id, x: i.x, y: i.y, w: i.w, h: i.h })), false);
    }
    items.filter(i => i.hidden).forEach(i => {
      hiddenCards.add(i.id);
      hiddenPositions[i.id] = { x: i.x, y: i.y, w: i.w, h: i.h };
      const el = document.querySelector(`.grid-stack-item[gs-id="${i.id}"]`);
      if (!el) return;
      grid.removeWidget(el, false);
      el.style.display = 'none';
    });
  }

  // Defer to next frame: ResizeObserver fires before rAF per browser spec,
  // so GridStack's 1-col responsive switch is guaranteed to happen first
  requestAnimationFrame(applyLoadedLayout);

  // Chart references for resize
  const charts = {};

  // ── Charts ─────────────────────────────────────────────────────────────────
  function noData(canvasId) {
    const el = document.getElementById(canvasId);
    if (!el) return;
    el.style.display = 'none';
    const msg = document.createElement('div');
    msg.className = 'd-flex flex-column align-items-center justify-content-center text-muted text-center';
    msg.style.cssText = 'height:120px;';
    msg.innerHTML = `<span class="mdi mdi-chart-line-variant" style="font-size:2.5rem; opacity:0.3; margin-bottom:8px;"></span>
      <span class="fw-semibold mb-1">Tidak ada data</span>
      <span class="small">Tidak ada data untuk ditampilkan</span>`;
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
  let editMode      = false;
  let editStartCols = null;
  const fabMain = document.getElementById('gs-fab-main');
  const fabIcon = document.getElementById('gs-fab-icon');
  const toolbar = document.getElementById('gs-edit-toolbar');

  function enterEdit() {
    editMode      = true;
    editStartCols = grid.getColumn();
    grid.setStatic(false);
    grid.enableMove(true);
    grid.enableResize(true);
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
    grid.enableMove(false);
    grid.enableResize(false);
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
    if (grid.getColumn() !== editStartCols) {
      gsShowErrorToast('Ukuran layar berubah saat edit. Halaman akan dimuat ulang.');
      setTimeout(() => { exitEdit(); location.reload(); }, 2500);
      return;
    }
    const cols = grid.getColumn();
    let items;
    if (cols === 1) {
      // grid.save() returns pre-responsive 12-col node values in responsive mode.
      // Read actual visual order via rendered bounding rect instead.
      let yPos = 0;
      items = [...document.querySelectorAll('.grid-stack-item')]
        .filter(el => el.gridstackNode)
        .sort((a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top)
        .map(el => {
          const id = el.getAttribute('gs-id');
          const h  = el.gridstackNode.h || parseInt(el.getAttribute('gs-h') || '4');
          const item = { id, x: 0, y: yPos, w: 1, h };
          yPos += h;
          return item;
        });
    } else {
      items = grid.save(false);
    }
    items.forEach(i => { if (hiddenCards.has(i.id)) i.hidden = true; });
    const layout = { items };
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout, page: isMobileLayout ? 'home-mobile' : 'home' })
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
    Object.values(charts).forEach(c => c.resize());
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });

  // Re-apply move/resize after GridStack's responsive column change resets static state
  let _resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(_resizeTimer);
    _resizeTimer = setTimeout(() => {
      if (editMode) {
        grid.enableMove(true);
        grid.enableResize(true);
      }
    }, 150);
  });

  fabMain.addEventListener('click', () => editMode ? exitEdit() : enterEdit());

})();
</script>
@endpush
