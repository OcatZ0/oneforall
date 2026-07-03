@extends('layouts.wazuh')

@section('title', 'Integrity Monitoring - One For All')

@push('styles')
@include('partials._gridstack-styles')
<style>
  #integrity-monitoring-grid [gs-id="fim-timerange"] .grid-stack-item-content,
  #integrity-monitoring-grid [gs-id="fim-timerange"] .card,
  #integrity-monitoring-grid [gs-id="fim-timerange"] .card-body {
    overflow: visible !important;
  }
</style>
@endpush

@section('content')

@if(!$agent)
<x-agent-not-found />
@else

@include('agent._nav', ['agent' => $agent, 'activeTab' => 'integrity-monitoring'])


<div class="grid-stack" id="integrity-monitoring-grid">

  {{-- METRICS --}}
  <div class="grid-stack-item" gs-id="fim-metrics" data-label="Metrik" gs-x="0" gs-y="0" gs-w="9" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="row g-2 h-100 align-items-center">
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Total event</div>
              <div class="display-6 fw-bold text-primary" id="fimMetricTotal">{{ number_format($fimSummary['total']) }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Ditambahkan</div>
              <div class="display-6 fw-bold text-success" id="fimMetricAdded">{{ number_format($fimSummary['added']) }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Dimodifikasi</div>
              <div class="display-6 fw-bold text-warning" id="fimMetricModified">{{ number_format($fimSummary['modified']) }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Dihapus</div>
              <div class="display-6 fw-bold text-danger" id="fimMetricDeleted">{{ number_format($fimSummary['deleted']) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TIME RANGE --}}
  <div class="grid-stack-item" gs-id="fim-timerange" data-label="Rentang Waktu" gs-x="9" gs-y="0" gs-w="3" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Rentang Waktu</span>
        </div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown"
              data-bs-toggle="dropdown" aria-expanded="false">
              <span class="mdi mdi-calendar-outline me-1"></span>
              <span id="timeRangeLabel">{{ ['15m'=>'15 menit terakhir','30m'=>'30 menit terakhir','1h'=>'1 jam terakhir','24h'=>'24 jam terakhir','7d'=>'7 hari terakhir','30d'=>'30 hari terakhir','90d'=>'90 hari terakhir','1y'=>'1 tahun terakhir','today'=>'Hari ini','week'=>'Minggu ini'][$timeRange] ?? '24 jam terakhir' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="timeRangeDropdown">
              <li><a class="dropdown-item {{ $timeRange === '15m'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('15m',  event)">15 menit terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '30m'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('30m',  event)">30 menit terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '1h'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('1h',   event)">1 jam terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '24h'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('24h',  event)">24 jam terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '7d'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('7d',   event)">7 hari terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '30d'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('30d',  event)">30 hari terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '90d'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('90d',  event)">90 hari terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '1y'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('1y',   event)">1 tahun terakhir</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item {{ $timeRange === 'today' ? 'active' : '' }}" href="#" onclick="updateTimeRange('today', event)">Hari ini</a></li>
              <li><a class="dropdown-item {{ $timeRange === 'week'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('week',  event)">Minggu ini</a></li>
            </ul>
          </div>
          <button class="btn btn-outline-warning btn-sm" onclick="updateTimeRange('24h', event)" title="Reset ke default (24 jam terakhir)">
            <span class="mdi mdi-restore me-1"></span> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- FIM EVOLUTION --}}
  <div class="grid-stack-item" gs-id="fim-evolution" data-label="Evolusi Event" gs-x="0" gs-y="3" gs-w="6" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Evolusi Event</span>
        </div>
        <div class="card-body p-2">
          <div id="fimEvolutionContainer" style="position:relative;height:100%;">
            <canvas id="fimEvolutionChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 RULE DESCRIPTIONS --}}
  <div class="grid-stack-item" gs-id="fim-top-rules" data-label="5 Deskripsi Aturan Teratas" gs-x="6" gs-y="3" gs-w="6" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 Deskripsi Aturan Teratas</span>
        </div>
        <div class="card-body p-2">
          <div id="fimTopRulesContainer" style="position:relative;height:100%;">
            <canvas id="fimTopRulesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 MODIFIED FILES --}}
  <div class="grid-stack-item" gs-id="fim-top-modified" data-label="5 File Termodifikasi Teratas" gs-x="0" gs-y="12" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 File Termodifikasi Teratas</span>
        </div>
        <div class="card-body p-0" id="fimTopModifiedBody">
          @if(count($fimTopModified) > 0)
          <ul class="list-group list-group-flush">
            @foreach($fimTopModified as $i => $file)
            <li class="list-group-item d-flex justify-content-between align-items-start px-3 py-2">
              <div class="d-flex align-items-center gap-2 overflow-hidden">
                <span class="badge bg-secondary rounded-pill">{{ $i + 1 }}</span>
                <span class="small text-truncate" style="max-width:200px;" title="{{ $file['path'] }}">{{ $file['path'] }}</span>
              </div>
              <span class="badge bg-warning text-white ms-2 flex-shrink-0">{{ number_format($file['count']) }}</span>
            </li>
            @endforeach
          </ul>
          @else
          <div class="d-flex flex-column align-items-center justify-content-center text-muted py-4 text-center">
            <span class="mdi mdi-file-check-outline" style="font-size:2rem; opacity:0.3; margin-bottom:6px;"></span>
            <span class="fw-semibold small mb-1">Tidak ada event FIM</span>
            <span style="font-size:11px;">Tidak ada perubahan file yang terdeteksi</span>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 DELETED FILES --}}
  <div class="grid-stack-item" gs-id="fim-top-deleted" data-label="5 File Terhapus Teratas" gs-x="4" gs-y="12" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 File Terhapus Teratas</span>
        </div>
        <div class="card-body p-0" id="fimTopDeletedBody">
          @if(count($fimTopDeleted) > 0)
          <ul class="list-group list-group-flush">
            @foreach($fimTopDeleted as $i => $file)
            <li class="list-group-item d-flex justify-content-between align-items-start px-3 py-2">
              <div class="d-flex align-items-center gap-2 overflow-hidden">
                <span class="badge bg-secondary rounded-pill">{{ $i + 1 }}</span>
                <span class="small text-truncate" style="max-width:200px;" title="{{ $file['path'] }}">{{ $file['path'] }}</span>
              </div>
              <span class="badge bg-danger ms-2 flex-shrink-0">{{ number_format($file['count']) }}</span>
            </li>
            @endforeach
          </ul>
          @else
          <div class="d-flex flex-column align-items-center justify-content-center text-muted py-4 text-center">
            <span class="mdi mdi-file-check-outline" style="font-size:2rem; opacity:0.3; margin-bottom:6px;"></span>
            <span class="fw-semibold small mb-1">Tidak ada event FIM</span>
            <span style="font-size:11px;">Tidak ada perubahan file yang terdeteksi</span>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 ADDED FILES --}}
  <div class="grid-stack-item" gs-id="fim-top-added" data-label="5 File Ditambahkan Teratas" gs-x="8" gs-y="12" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 File Ditambahkan Teratas</span>
        </div>
        <div class="card-body p-0" id="fimTopAddedBody">
          @if(count($fimTopAdded) > 0)
          <ul class="list-group list-group-flush">
            @foreach($fimTopAdded as $i => $file)
            <li class="list-group-item d-flex justify-content-between align-items-start px-3 py-2">
              <div class="d-flex align-items-center gap-2 overflow-hidden">
                <span class="badge bg-secondary rounded-pill">{{ $i + 1 }}</span>
                <span class="small text-truncate" style="max-width:200px;" title="{{ $file['path'] }}">{{ $file['path'] }}</span>
              </div>
              <span class="badge bg-success ms-2 flex-shrink-0">{{ number_format($file['count']) }}</span>
            </li>
            @endforeach
          </ul>
          @else
          <div class="d-flex flex-column align-items-center justify-content-center text-muted py-4 text-center">
            <span class="mdi mdi-file-check-outline" style="font-size:2rem; opacity:0.3; margin-bottom:6px;"></span>
            <span class="fw-semibold small mb-1">Tidak ada event FIM</span>
            <span style="font-size:11px;">Tidak ada perubahan file yang terdeteksi</span>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FIM EVENTS TABLE --}}
  <div class="grid-stack-item" gs-id="fim-events-table" data-label="Event FIM" gs-x="0" gs-y="20" gs-w="12" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Event FIM</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:15%">Waktu</th>
                  <th style="width:35%">Path</th>
                  <th style="width:10%">Aksi</th>
                  <th style="width:30%">Aturan</th>
                  <th style="width:10%">Level</th>
                </tr>
              </thead>
              <tbody id="fim-tbody">
                @forelse($fimEvents as $event)
                @php
                  $actionColor = match($event['action']) {
                    'added'    => 'success',
                    'modified' => 'warning',
                    'deleted'  => 'danger',
                    default    => 'secondary',
                  };
                  $levelColor = $event['level'] >= 12 ? 'danger' : ($event['level'] >= 9 ? 'warning' : ($event['level'] >= 6 ? 'info' : 'secondary'));
                @endphp
                <tr>
                  <td class="text-muted">{{ \Carbon\Carbon::parse($event['timestamp'])->format('Y-m-d H:i:s') }}</td>
                  <td class="text-truncate" style="max-width:250px;" title="{{ $event['path'] }}">
                    <span class="mdi mdi-file-outline me-1 text-muted"></span>{{ $event['path'] }}
                  </td>
                  <td><span class="badge bg-{{ $actionColor }}">{{ $event['action'] }}</span></td>
                  <td class="text-truncate" style="max-width:200px;" title="{{ $event['description'] }}">
                    <span class="text-muted me-1">#{{ $event['rule_id'] }}</span>{{ $event['description'] }}
                  </td>
                  <td><span class="badge bg-{{ $levelColor }}">{{ $event['level'] }}</span></td>
                </tr>
                @empty
                <x-empty-state-row colspan="5" icon="mdi-file-check-outline" title="Tidak ada event FIM" subtitle="Tidak ada perubahan file yang terdeteksi" />
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @php
          $totalPages = $totalEvents > 0 ? (int) ceil($totalEvents / $perPage) : 1;
          $from       = $totalEvents > 0 ? ($page - 1) * $perPage + 1 : 0;
          $to         = min($page * $perPage, $totalEvents);
          $baseQuery  = array_merge(request()->query(), []);
          $pageUrl    = fn($p)  => '?' . http_build_query(array_merge($baseQuery, ['page' => $p,  'per_page' => $perPage]));
          $ppUrl      = fn($pp) => '?' . http_build_query(array_merge($baseQuery, ['page' => 1,   'per_page' => $pp]));
          $window     = collect(range(max(1, $page - 2), min($totalPages, $page + 2)));
        @endphp
        <div id="fim-footer" class="card-footer d-flex justify-content-between align-items-center py-2 small flex-wrap gap-2">
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-1">Baris:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $ppUrl($pp) }}"
                 class="btn btn-sm py-0 px-2 {{ $perPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $pp }}</a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $from }}–{{ $to }} dari {{ number_format($totalEvents) }}</span>
            <a href="{{ $pageUrl(1) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $page <= 1 ? 'disabled' : '' }}"
               title="First">«</a>
            <a href="{{ $pageUrl(max(1, $page - 1)) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $page <= 1 ? 'disabled' : '' }}"
               title="Prev">‹</a>
            @foreach($window as $p)
              <a href="{{ $pageUrl($p) }}"
                 class="btn btn-sm py-0 px-2 {{ $p === $page ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $p }}</a>
            @endforeach
            <a href="{{ $pageUrl(min($totalPages, $page + 1)) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $page >= $totalPages ? 'disabled' : '' }}"
               title="Next">›</a>
            <a href="{{ $pageUrl($totalPages) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $page >= $totalPages ? 'disabled' : '' }}"
               title="Last">»</a>
          </div>
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
  <button id="gs-save"   class="gs-tb-btn gs-tb-btn-save">Simpan</button>
  <button id="gs-reset"  class="gs-tb-btn gs-tb-btn-reset">Reset</button>
  <button id="gs-cancel" class="gs-tb-btn gs-tb-btn-cancel">Batal</button>
</div>

@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack-all.js"></script>
<script>
// ── Data from PHP ──────────────────────────────────────────────────────────────
const fimData = {
  evolution: @json($fimEvolution ?? ['labels' => [], 'datasets' => []]),
  topRules:  @json($fimTopRules  ?? ['labels' => [], 'data'     => []]),
};

let currentTimeRange = '{{ $timeRange ?? "24h" }}';
let fimEvolutionChartInstance = null;
let fimTopRulesChartInstance  = null;

// ── Helpers ────────────────────────────────────────────────────────────────────
const timeRangeLabels = {
  '15m': '15 menit terakhir', '30m': '30 menit terakhir', '1h': '1 jam terakhir',
  '24h': '24 jam terakhir',   '7d':  '7 hari terakhir',   '30d': '30 hari terakhir',
  '90d': '90 hari terakhir',  '1y':  '1 tahun terakhir',
  'today': 'Hari ini',        'week': 'Minggu ini',
};

function updateTimeRange(timeRange, event) {
  if (event) event.preventDefault();
  currentTimeRange = timeRange;
  document.getElementById('timeRangeLabel').textContent = timeRangeLabels[timeRange] || 'Pilih rentang waktu';

  const menu = document.getElementById('timeRangeDropdown')
                       ?.closest('.dropdown')
                       ?.querySelector('.dropdown-menu');
  if (menu) {
    menu.querySelectorAll('.dropdown-item').forEach(item => {
      const oc = item.getAttribute('onclick') || '';
      item.classList.toggle('active', oc.includes(`'${timeRange}'`));
    });
  }
  refreshData();
}

const fimChartDataEndpoint = '{{ route("agent.fim.chart-data", request()->route("id")) }}';

function renderTopFilesList(containerId, files, badgeClass) {
  const el = document.getElementById(containerId);
  if (!el) return;
  if (!files || files.length === 0) {
    el.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center text-muted py-4 text-center">
      <span class="mdi mdi-file-check-outline" style="font-size:2rem; opacity:0.3; margin-bottom:6px;"></span>
      <span class="fw-semibold small mb-1">Tidak ada event FIM</span>
      <span style="font-size:11px;">Tidak ada perubahan file yang terdeteksi</span>
    </div>`;
    return;
  }
  const items = files.map((file, i) => `
    <li class="list-group-item d-flex justify-content-between align-items-start px-3 py-2">
      <div class="d-flex align-items-center gap-2 overflow-hidden">
        <span class="badge bg-secondary rounded-pill">${i + 1}</span>
        <span class="small text-truncate" style="max-width:200px;" title="${escHtml(file.path)}">${escHtml(file.path)}</span>
      </div>
      <span class="badge ${badgeClass} ms-2 flex-shrink-0">${Number(file.count).toLocaleString()}</span>
    </li>`).join('');
  el.innerHTML = `<ul class="list-group list-group-flush">${items}</ul>`;
}

async function refreshData() {
  // Update URL so page refresh re-loads with the selected time range
  const url = new URL(window.location.href);
  url.searchParams.set('time_range', currentTimeRange);
  history.replaceState({}, '', url.toString());

  // Dim metrics card while loading
  const metricsCard = document.querySelector('[gs-id="fim-metrics"] .card');
  if (metricsCard) metricsCard.style.opacity = '0.5';

  try {
    const res = await fetch(`${fimChartDataEndpoint}?time_range=${encodeURIComponent(currentTimeRange)}`, {
      headers: { 'Accept': 'application/json' }
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();

    // Update metrics
    const s = json.fimSummary || {};
    document.getElementById('fimMetricTotal').textContent    = Number(s.total    ?? 0).toLocaleString();
    document.getElementById('fimMetricAdded').textContent    = Number(s.added    ?? 0).toLocaleString();
    document.getElementById('fimMetricModified').textContent = Number(s.modified ?? 0).toLocaleString();
    document.getElementById('fimMetricDeleted').textContent  = Number(s.deleted  ?? 0).toLocaleString();

    // Update chart data
    fimData.evolution = json.fimEvolution ?? { labels: [], datasets: [] };
    fimData.topRules  = json.fimTopRules  ?? { labels: [], data:     [] };

    // Re-render charts
    initializeCharts();

    // Re-render top-files lists
    renderTopFilesList('fimTopModifiedBody', json.fimTopModified, 'bg-warning text-dark');
    renderTopFilesList('fimTopDeletedBody',  json.fimTopDeleted,  'bg-danger');
    renderTopFilesList('fimTopAddedBody',    json.fimTopAdded,    'bg-success');

    // Refresh events table
    loadFimEvents(1, 10);
  } catch (e) {
    console.error('refreshData failed', e);
  } finally {
    if (metricsCard) metricsCard.style.opacity = '';
  }
}

function convertLabelsToLocalTime(labels) {
  return labels.map(label => {
    try {
      const d = new Date(label);
      return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
    } catch (e) { return label; }
  });
}

function showNoData(containerId, height = '200px') {
  const el = document.getElementById(containerId);
  if (el) el.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center text-muted text-center" style="height:${height};">
    <span class="mdi mdi-file-check-outline" style="font-size:2.5rem; opacity:0.3; margin-bottom:8px;"></span>
    <span class="fw-semibold mb-1">Tidak ada event FIM</span>
    <span class="small">Tidak ada perubahan file yang terdeteksi</span>
  </div>`;
}

// ── Charts ─────────────────────────────────────────────────────────────────────
function initializeCharts() {
  if (typeof Chart === 'undefined') { setTimeout(initializeCharts, 100); return; }

  if (fimEvolutionChartInstance) { fimEvolutionChartInstance.destroy(); fimEvolutionChartInstance = null; }
  if (fimTopRulesChartInstance)  { fimTopRulesChartInstance.destroy();  fimTopRulesChartInstance  = null; }

  // FIM evolution line chart
  if (fimData.evolution.labels && fimData.evolution.labels.length > 0) {
    const ctx = document.getElementById('fimEvolutionChart')?.getContext('2d');
    if (ctx) {
      fimEvolutionChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: convertLabelsToLocalTime(fimData.evolution.labels),
          datasets: fimData.evolution.datasets.map(ds => ({
            ...ds, fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3,
          })),
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } } },
          scales: {
            y: { beginAtZero: true, stacked: false, ticks: { precision: 0, font: { size: 10 } } },
            x: { ticks: { font: { size: 10 } } },
          },
        },
      });
    }
  } else {
    showNoData('fimEvolutionContainer');
  }

  // Top rules horizontal bar chart
  if (fimData.topRules.labels && fimData.topRules.labels.length > 0) {
    const ctx = document.getElementById('fimTopRulesChart')?.getContext('2d');
    if (ctx) {
      fimTopRulesChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: fimData.topRules.labels,
          datasets: [{
            label: 'Count',
            data: fimData.topRules.data,
            backgroundColor: ['#4B49AC','#7da0fa','#f3797e','#ffc107','#20c997'],
          }],
        },
        options: {
          indexAxis: 'y',
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } },
            y: { ticks: { font: { size: 10 } } },
          },
        },
      });
    }
  } else {
    showNoData('fimTopRulesContainer');
  }
}

// ── AJAX table helpers ─────────────────────────────────────────────────────────
function escHtml(s) { const d = document.createElement('div'); d.textContent = s != null ? String(s) : ''; return d.innerHTML; }

function renderPagination(footerId, total, page, perPage, loadFn) {
  const totalPages = total > 0 ? Math.ceil(total / perPage) : 1;
  const from = total > 0 ? (page - 1) * perPage + 1 : 0;
  const to   = Math.min(page * perPage, total);
  const btn  = (p, pp, label, disabled, active) =>
    `<button ${disabled ? 'disabled' : `onclick="${loadFn}(${p},${pp})"`} class="btn btn-sm py-0 px-2 ${active ? 'btn-primary' : 'btn-outline-secondary'}${disabled ? ' disabled' : ''}">${label}</button>`;
  const ppBtns = [10,25,50].map(pp => btn(1, pp, pp, false, perPage===pp)).join('');
  const winBtns = [];
  for (let p = Math.max(1,page-2); p <= Math.min(totalPages,page+2); p++) winBtns.push(btn(p, perPage, p, false, p===page));
  document.getElementById(footerId).innerHTML = `
    <div class="d-flex justify-content-between align-items-center py-2 small flex-wrap gap-2 px-3 w-100">
      <div class="d-flex align-items-center gap-1"><span class="text-muted me-1">Rows:</span>${ppBtns}</div>
      <div class="d-flex align-items-center gap-1">
        <span class="text-muted me-2">${from}–${to} of ${total.toLocaleString()}</span>
        ${btn(1, perPage, '«', page<=1, false)}
        ${btn(Math.max(1,page-1), perPage, '‹', page<=1, false)}
        ${winBtns.join('')}
        ${btn(Math.min(totalPages,page+1), perPage, '›', page>=totalPages, false)}
        ${btn(totalPages, perPage, '»', page>=totalPages, false)}
      </div>
    </div>`;
}

const fimEndpoint = '{{ route("agent.fim.events", request()->route("id")) }}';

async function loadFimEvents(page, perPage) {
  const params = new URLSearchParams({ time_range: currentTimeRange, page, per_page: perPage });
  try {
    const res  = await fetch(`${fimEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return;
    const json = await res.json();
    const tbody = document.getElementById('fim-tbody');
    tbody.innerHTML = json.data.length ? json.data.map(r => {
      const ac = {'added':'success','modified':'warning','deleted':'danger'}[r.action] || 'secondary';
      const lv = r.level||0; const lc = lv>=12?'danger':lv>=9?'warning':lv>=6?'info':'secondary';
      const ts = r.timestamp ? new Date(r.timestamp).toLocaleString('en-GB') : '-';
      return `<tr>
        <td class="text-muted">${ts}</td>
        <td class="text-truncate" style="max-width:250px;" title="${escHtml(r.path)}">
          <span class="mdi mdi-file-outline me-1 text-muted"></span>${escHtml(r.path)}
        </td>
        <td><span class="badge bg-${ac}">${escHtml(r.action)}</span></td>
        <td class="text-truncate" style="max-width:200px;" title="${escHtml(r.description)}">
          <span class="text-muted me-1">#${escHtml(r.rule_id)}</span>${escHtml(r.description)}
        </td>
        <td><span class="badge bg-${lc}">${lv}</span></td>
      </tr>`;
    }).join('') : emptyStateRow(5, 'mdi-file-check-outline', 'Tidak ada event FIM', 'Tidak ada perubahan file yang terdeteksi');
    renderPagination('fim-footer', json.total, json.page, json.perPage, 'loadFimEvents');
  } catch(e) { console.error('loadFimEvents failed', e); }
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('fim-footer').addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    e.preventDefault();
    const u = new URL(a.href, location.href);
    loadFimEvents(parseInt(u.searchParams.get('page')||1), parseInt(u.searchParams.get('per_page')||10));
  });
});

document.addEventListener('DOMContentLoaded', initializeCharts);

// ── GridStack ──────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'fim-metrics',      x: 0, y: 0,  w: 9,  h: 3  },
    { id: 'fim-timerange',    x: 9, y: 0,  w: 3,  h: 3  },
    { id: 'fim-evolution',    x: 0, y: 3,  w: 6,  h: 9  },
    { id: 'fim-top-rules',    x: 6, y: 3,  w: 6,  h: 9  },
    { id: 'fim-top-modified', x: 0, y: 12, w: 4,  h: 8  },
    { id: 'fim-top-deleted',  x: 4, y: 12, w: 4,  h: 8  },
    { id: 'fim-top-added',    x: 8, y: 12, w: 4,  h: 8  },
    { id: 'fim-events-table', x: 0, y: 20, w: 12, h: 10 },
  ];

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
      if (!item.querySelector('.gs-drag-handle')) {
        const dragHandle = document.createElement('div');
        dragHandle.className = 'gs-drag-handle';
        dragHandle.title = 'Seret untuk memindahkan';
        dragHandle.innerHTML = '<i class="mdi mdi-drag"></i>';
        item.appendChild(dragHandle);
      }
    });
  }

  // ── Load saved layout ───────────────────────────────────────────────────
  const isMobileLayout = window.innerWidth <= 768;
  const savedLayout = isMobileLayout
    ? @json($savedLayoutMobile ?? null)
    : @json($savedLayout ?? null);

  function applyLoadedLayout() {
    if (!savedLayout || Array.isArray(savedLayout) || !savedLayout.items) return;
    const items = savedLayout.items ?? [];
    if (isMobileLayout) {
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
  requestAnimationFrame(applyLoadedLayout);

  grid.on('resizestop', () => {
    if (fimEvolutionChartInstance) fimEvolutionChartInstance.resize();
    if (fimTopRulesChartInstance)  fimTopRulesChartInstance.resize();
  });

  // ── Edit mode ──────────────────────────────────────────────────────────────
  let editMode      = false;
  let editStartCols = null;
  const fabMain = document.getElementById('gs-fab-main');
  const fabIcon = document.getElementById('gs-fab-icon');
  const toolbar = document.getElementById('gs-edit-toolbar');

  function enterEdit() {
    editMode = true;
    if (!isMobileLayout) {
      editStartCols = grid.getColumn();
      grid.enableResize(true);
    }
    grid.setStatic(false);
    grid.enableMove(true);
    hiddenCards.forEach(id => {
      const el  = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 4, h: 8 };
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

  fabMain.addEventListener('click', () => editMode ? exitEdit() : enterEdit());

  document.getElementById('gs-save').addEventListener('click', () => {
    if (!isMobileLayout && grid.getColumn() !== editStartCols) {
      gsShowErrorToast('Ukuran layar berubah saat edit. Halaman akan dimuat ulang.');
      setTimeout(() => { exitEdit(); location.reload(); }, 2500);
      return;
    }
    let items;
    if (isMobileLayout) {
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
      items = grid.save(false).map(({ id, x, y, w, h }) => ({ id, x, y, w, h }));
    }
    items.forEach(i => { if (hiddenCards.has(i.id)) i.hidden = true; });
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout: { items }, page: isMobileLayout ? 'integrity-monitoring-mobile' : 'integrity-monitoring' })
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
    grid.load(DEFAULT_LAYOUT.map(i => ({ ...i })));
    if (fimEvolutionChartInstance) fimEvolutionChartInstance.resize();
    if (fimTopRulesChartInstance)  fimTopRulesChartInstance.resize();
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });

  let _resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(_resizeTimer);
    _resizeTimer = setTimeout(() => {
      if (editMode && window.innerWidth > 768) {
        grid.enableMove(true);
        grid.enableResize(true);
      }
    }, 150);
  });
})();
</script>
@endpush
