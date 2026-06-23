@extends('layouts.wazuh')

@section('title', 'Security Events - One For All')

@push('styles')
@include('partials._gridstack-styles')
<style>
  /* Allow dropdown to escape overflow clipping in the timerange card */
  #security-events-grid [gs-id="se-timerange"] .grid-stack-item-content,
  #security-events-grid [gs-id="se-timerange"] .card,
  #security-events-grid [gs-id="se-timerange"] .card-body {
    overflow: visible !important;
  }
</style>
@endpush

@section('content')

@if(!$agent)
<div class="container-fluid py-5">
  <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
    <i class="mdi mdi-alert-circle-outline display-4"></i>
    <div>
      <h5 class="alert-heading mb-1">Agen Tidak Ditemukan</h5>
      <p class="mb-0">Gagal memuat detail agen. Agen mungkin sudah tidak ada atau akses ditolak.</p>
      <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-danger mt-2">
        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Agen
      </a>
    </div>
  </div>
</div>
@else

@include('agent._nav', ['agent' => $agent, 'activeTab' => 'security-events'])


<div class="grid-stack" id="security-events-grid">

  {{-- METRICS --}}
  <div class="grid-stack-item" gs-id="se-metrics" data-label="Metrik" gs-x="0" gs-y="0" gs-w="9" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="row g-2 h-100 align-items-center">
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Total</div>
              <div class="display-6 fw-bold text-primary" id="metricTotal">{{ $metrics['total'] ?? 0 }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Level 12 ke atas</div>
              <div class="display-6 fw-bold text-danger" id="metricLevel12">{{ $metrics['level12'] ?? 0 }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Kegagalan Autentikasi</div>
              <div class="display-6 fw-bold text-warning" id="metricAuthFailure">{{ $metrics['auth_failure'] ?? 0 }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Autentikasi Berhasil</div>
              <div class="display-6 fw-bold text-success" id="metricAuthSuccess">{{ $metrics['auth_success'] ?? 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TIME RANGE --}}
  <div class="grid-stack-item" gs-id="se-timerange" data-label="Rentang Waktu" gs-x="9" gs-y="0" gs-w="3" gs-h="3">
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

  {{-- ALERT GROUPS EVOLUTION --}}
  <div class="grid-stack-item" gs-id="se-alert-groups" data-label="Evolusi Grup Peringatan" gs-x="0" gs-y="3" gs-w="6" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Evolusi Grup Peringatan</span>
        </div>
        <div class="card-body">
          <div id="alertGroupsContainer" style="position:relative; height:100%;">
            <canvas id="alertGroupsChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ALERTS BY SEVERITY --}}
  <div class="grid-stack-item" gs-id="se-alerts" data-label="Peringatan per Tingkat Keparahan" gs-x="6" gs-y="3" gs-w="6" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Peringatan</span>
        </div>
        <div class="card-body">
          <div id="alertsContainer" style="position:relative; height:100%;">
            <canvas id="alertsChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 ALERTS --}}
  <div class="grid-stack-item" gs-id="se-top-alerts" data-label="5 Peringatan Teratas" gs-x="0" gs-y="11" gs-w="4" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 Peringatan Teratas</span>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div id="top5AlertsContainer" style="position:relative; width:100%; height:100%;">
            <canvas id="top5AlertsChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 RULE GROUPS --}}
  <div class="grid-stack-item" gs-id="se-top-rule-groups" data-label="5 Grup Aturan Teratas" gs-x="4" gs-y="11" gs-w="4" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 Grup Aturan Teratas</span>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div id="top5RuleGroupsContainer" style="position:relative; width:100%; height:100%;">
            <canvas id="top5RuleGroupsChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 PCI DSS --}}
  <div class="grid-stack-item" gs-id="se-top-pcidss" data-label="5 Persyaratan PCI DSS Teratas" gs-x="8" gs-y="11" gs-w="4" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 Persyaratan PCI DSS Teratas</span>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div id="top5PCIDSSContainer" style="position:relative; width:100%; height:100%;">
            <canvas id="top5PCIDSSChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ALERTS SUMMARY TABLE --}}
  <div class="grid-stack-item" gs-id="se-alerts-table" data-label="Ringkasan Peringatan" gs-x="0" gs-y="20" gs-w="12" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Ringkasan Peringatan</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:20%">Waktu <span class="mdi mdi-arrow-up-down"></span></th>
                  <th style="width:15%">Rule ID</th>
                  <th style="width:35%">Deskripsi</th>
                  <th style="width:10%">Level</th>
                  <th style="width:10%">Jumlah</th>
                  <th style="width:10%">Grup</th>
                </tr>
              </thead>
              <tbody id="alerts-tbody">
                @forelse($recentAlerts ?? [] as $alert)
                <tr>
                  <td class="text-muted" style="font-size:10px;">{{ $alert['timestamp'] ? \Carbon\Carbon::parse($alert['timestamp'])->format('M d, Y @ H:i:s') : 'N/A' }}</td>
                  <td><span class="badge bg-light text-dark">{{ $alert['rule_id'] }}</span></td>
                  <td><a href="#" class="text-primary text-decoration-none">{{ $alert['description'] }}</a></td>
                  <td>
                    @php
                      $level = $alert['level'];
                      $levelColor = match(true) {
                        $level >= 12 => 'danger',
                        $level >= 9  => 'warning',
                        $level >= 6  => 'info',
                        default      => 'secondary',
                      };
                    @endphp
                    <span class="badge bg-{{ $levelColor }}">{{ $alert['level'] }}</span>
                  </td>
                  <td class="text-center fw-bold">{{ $alert['count'] }}</td>
                  <td><span class="badge bg-secondary">{{ $alert['groups'] }}</span></td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <span class="mdi mdi-monitor-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada alert</span>
                    <span class="d-block small">Tidak ada event keamanan dalam periode ini</span>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @php
          $totalPages  = $totalAlerts > 0 ? (int) ceil($totalAlerts / $perPage) : 1;
          $from        = $totalAlerts > 0 ? ($page - 1) * $perPage + 1 : 0;
          $to          = min($page * $perPage, $totalAlerts);
          $baseQuery   = array_merge(request()->query(), []);

          $pageUrl = fn($p) => '?' . http_build_query(array_merge($baseQuery, ['page' => $p, 'per_page' => $perPage]));
          $ppUrl   = fn($pp) => '?' . http_build_query(array_merge($baseQuery, ['page' => 1,  'per_page' => $pp]));

          $window  = collect(range(max(1, $page - 2), min($totalPages, $page + 2)));
        @endphp
        <div id="alerts-footer" class="card-footer d-flex justify-content-between align-items-center py-2 small flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted">Baris per halaman:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $ppUrl($pp) }}"
                 class="btn btn-sm {{ $perPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-2">
                {{ $pp }}
              </a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $from }}–{{ $to }} dari {{ number_format($totalAlerts) }}</span>
            <a href="{{ $pageUrl(1) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $page <= 1 ? 'disabled' : '' }}"
               title="First">«</a>
            <a href="{{ $pageUrl(max(1, $page - 1)) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $page <= 1 ? 'disabled' : '' }}"
               title="Previous">‹</a>
            @foreach($window as $p)
              <a href="{{ $pageUrl($p) }}"
                 class="btn btn-sm {{ $p === $page ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-2">
                {{ $p }}
              </a>
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

  {{-- GROUPS SUMMARY TABLE --}}
  <div class="grid-stack-item" gs-id="se-groups-table" data-label="Ringkasan Grup" gs-x="0" gs-y="30" gs-w="12" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Ringkasan Grup</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:70%">Grup</th>
                  <th style="width:30%">Jumlah</th>
                </tr>
              </thead>
              <tbody id="groups-tbody">
                @forelse($groupsSummary as $grp)
                <tr>
                  <td><span class="badge bg-secondary">{{ $grp['group'] }}</span></td>
                  <td class="fw-bold">{{ number_format($grp['count']) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="2" class="text-center py-5 text-muted">
                    <span class="mdi mdi-monitor-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada alert</span>
                    <span class="d-block small">Tidak ada event keamanan dalam periode ini</span>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @php
          $gTotalPages = $totalGroups > 0 ? (int) ceil($totalGroups / $groupsPerPage) : 1;
          $gFrom       = $totalGroups > 0 ? ($groupsPage - 1) * $groupsPerPage + 1 : 0;
          $gTo         = min($groupsPage * $groupsPerPage, $totalGroups);
          $gBase       = array_merge(request()->query(), []);
          $gPageUrl    = fn($p)  => '?' . http_build_query(array_merge($gBase, ['groups_page' => $p,  'groups_per_page' => $groupsPerPage]));
          $gPpUrl      = fn($pp) => '?' . http_build_query(array_merge($gBase, ['groups_page' => 1,   'groups_per_page' => $pp]));
          $gWindow     = collect(range(max(1, $groupsPage - 2), min($gTotalPages, $groupsPage + 2)));
        @endphp
        <div id="groups-footer" class="card-footer d-flex justify-content-between align-items-center py-2 small flex-wrap gap-2">
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-1">Baris:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $gPpUrl($pp) }}"
                 class="btn btn-sm py-0 px-2 {{ $groupsPerPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $pp }}</a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $gFrom }}–{{ $gTo }} dari {{ number_format($totalGroups) }}</span>
            <a href="{{ $gPageUrl(1) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $groupsPage <= 1 ? 'disabled' : '' }}"
               title="First">«</a>
            <a href="{{ $gPageUrl(max(1, $groupsPage - 1)) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $groupsPage <= 1 ? 'disabled' : '' }}"
               title="Prev">‹</a>
            @foreach($gWindow as $p)
              <a href="{{ $gPageUrl($p) }}"
                 class="btn btn-sm py-0 px-2 {{ $p === $groupsPage ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $p }}</a>
            @endforeach
            <a href="{{ $gPageUrl(min($gTotalPages, $groupsPage + 1)) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $groupsPage >= $gTotalPages ? 'disabled' : '' }}"
               title="Next">›</a>
            <a href="{{ $gPageUrl($gTotalPages) }}"
               class="btn btn-sm btn-outline-secondary py-0 px-2 {{ $groupsPage >= $gTotalPages ? 'disabled' : '' }}"
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
  <button class="gs-tb-btn gs-tb-btn-save"   id="gs-save">  <i class="mdi mdi-content-save me-1"></i>Simpan</button>
  <button class="gs-tb-btn gs-tb-btn-reset"  id="gs-reset"> <i class="mdi mdi-restore me-1"></i>Reset</button>
  <button class="gs-tb-btn gs-tb-btn-cancel" id="gs-cancel"><i class="mdi mdi-close me-1"></i>Batal</button>
</div>

@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack-all.js"></script>
<script>
// ── Global state ──────────────────────────────────────────────────────────────
let currentTimeRange = '{{ $timeRange ?? "24h" }}';
let chartInstances   = {};
const agentId        = '{{ $agent->agent_id ?? "" }}';

const securityData = {
  alertGroupsEvolution:   @json($alertGroupsEvolution   ?? ['labels' => [], 'datasets' => []]),
  alertsEvolutionByLevel: @json($alertsEvolutionByLevel ?? ['labels' => [], 'datasets' => []]),
  topAlerts:     @json($topAlerts     ?? ['labels' => [], 'data' => []]),
  topRuleGroups: @json($topRuleGroups ?? ['labels' => [], 'data' => []]),
  topPCIDSS:     @json($topPCIDSS     ?? ['labels' => [], 'data' => []]),
};

const timeRangeLabels = {
  '15m':  '15 menit terakhir',
  '30m':  '30 menit terakhir',
  '1h':   '1 jam terakhir',
  '24h':  '24 jam terakhir',
  '7d':   '7 hari terakhir',
  '30d':  '30 hari terakhir',
  '90d':  '90 hari terakhir',
  '1y':   '1 tahun terakhir',
  'today':'Hari ini',
  'week': 'Minggu ini'
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

const seChartDataEndpoint = '{{ route("agent.se.chart-data", request()->route("id")) }}';

async function refreshData() {
  // Update URL so page refresh re-loads with the selected time range
  const url = new URL(window.location.href);
  url.searchParams.set('time_range', currentTimeRange);
  history.replaceState({}, '', url.toString());

  // Dim metrics card while loading
  const metricsCard = document.querySelector('[gs-id="se-metrics"] .card');
  if (metricsCard) metricsCard.style.opacity = '0.5';

  try {
    const res = await fetch(`${seChartDataEndpoint}?time_range=${encodeURIComponent(currentTimeRange)}`, {
      headers: { 'Accept': 'application/json' }
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();

    // Update metrics
    const m = json.metrics || {};
    document.getElementById('metricTotal').textContent       = m.total       ?? 0;
    document.getElementById('metricLevel12').textContent     = m.level12     ?? 0;
    document.getElementById('metricAuthFailure').textContent = m.auth_failure ?? 0;
    document.getElementById('metricAuthSuccess').textContent = m.auth_success ?? 0;

    // Update chart data and re-render
    securityData.alertGroupsEvolution   = json.alertGroupsEvolution   ?? { labels: [], datasets: [] };
    securityData.alertsEvolutionByLevel = json.alertsEvolutionByLevel ?? { labels: [], datasets: [] };
    securityData.topAlerts              = json.topAlerts     ?? { labels: [], data: [] };
    securityData.topRuleGroups          = json.topRuleGroups ?? { labels: [], data: [] };
    securityData.topPCIDSS              = json.topPCIDSS     ?? { labels: [], data: [] };

    initializeCharts();

    // Refresh tables
    loadAlerts(1, 10);
    loadGroups(1, 10);
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
    <span class="mdi mdi-monitor-outline" style="font-size:2.5rem; opacity:0.3; margin-bottom:8px;"></span>
    <span class="fw-semibold mb-1">Tidak ada alert</span>
    <span class="small">Tidak ada event keamanan dalam periode ini</span>
  </div>`;
}

function initializeCharts() {
  if (typeof Chart === 'undefined') { setTimeout(initializeCharts, 100); return; }

  Object.values(chartInstances).forEach(c => c?.destroy?.());
  chartInstances = {};

  // Alert groups evolution
  if (securityData.alertGroupsEvolution.labels.length > 0) {
    const ctx = document.getElementById('alertGroupsChart')?.getContext('2d');
    if (ctx) {
      chartInstances.alertGroups = new Chart(ctx, {
        type: 'line',
        data: {
          labels: convertLabelsToLocalTime(securityData.alertGroupsEvolution.labels),
          datasets: securityData.alertGroupsEvolution.datasets.map(ds => ({
            ...ds,
            backgroundColor: (ds.backgroundColor || '').replace('1)', '0.1)'),
            fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3,
          }))
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }
        }
      });
    }
  } else {
    showNoData('alertGroupsContainer');
  }

  // Alerts by severity
  if (securityData.alertsEvolutionByLevel.labels.length > 0) {
    const ctx = document.getElementById('alertsChart')?.getContext('2d');
    if (ctx) {
      chartInstances.alerts = new Chart(ctx, {
        type: 'line',
        data: {
          labels: convertLabelsToLocalTime(securityData.alertsEvolutionByLevel.labels),
          datasets: securityData.alertsEvolutionByLevel.datasets.map(ds => ({
            ...ds,
            backgroundColor: (ds.backgroundColor || '').replace('1)', '0.1)'),
            fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3,
          }))
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } } },
          scales: { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } }
        }
      });
    }
  } else {
    showNoData('alertsContainer');
  }

  // Top 5 alerts doughnut
  const colors1 = ['#0d6efd','#20c997','#0dcaf0','#6f42c1','#d63384'];
  if (securityData.topAlerts.labels.length > 0) {
    const ctx = document.getElementById('top5AlertsChart')?.getContext('2d');
    if (ctx) {
      chartInstances.top5Alerts = new Chart(ctx, {
        type: 'doughnut',
        data: { labels: securityData.topAlerts.labels, datasets: [{ data: securityData.topAlerts.data, backgroundColor: colors1.slice(0, securityData.topAlerts.data.length) }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 9 }, boxWidth: 10, padding: 6 } } } }
      });
    }
  } else {
    showNoData('top5AlertsContainer');
  }

  // Top 5 rule groups doughnut
  const colors2 = ['#fd7e14','#ffc107','#dc3545','#ff6b6b','#20c997'];
  if (securityData.topRuleGroups.labels.length > 0) {
    const ctx = document.getElementById('top5RuleGroupsChart')?.getContext('2d');
    if (ctx) {
      chartInstances.top5RuleGroups = new Chart(ctx, {
        type: 'doughnut',
        data: { labels: securityData.topRuleGroups.labels, datasets: [{ data: securityData.topRuleGroups.data, backgroundColor: colors2.slice(0, securityData.topRuleGroups.data.length) }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 9 }, boxWidth: 10, padding: 6 } } } }
      });
    }
  } else {
    showNoData('top5RuleGroupsContainer');
  }

  // Top 5 PCI DSS doughnut
  const colors3 = ['#20c997','#0dcaf0','#9d4edd','#e0aaff','#a8dadc'];
  if (securityData.topPCIDSS.labels.length > 0) {
    const ctx = document.getElementById('top5PCIDSSChart')?.getContext('2d');
    if (ctx) {
      chartInstances.top5PCIDSS = new Chart(ctx, {
        type: 'doughnut',
        data: { labels: securityData.topPCIDSS.labels, datasets: [{ data: securityData.topPCIDSS.data, backgroundColor: colors3.slice(0, securityData.topPCIDSS.data.length) }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 9 }, boxWidth: 10, padding: 6 } } } }
      });
    }
  } else {
    showNoData('top5PCIDSSContainer');
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

const alertsEndpoint = '{{ route("agent.se.alerts", request()->route("id")) }}';
const groupsEndpoint = '{{ route("agent.se.groups", request()->route("id")) }}';

async function loadAlerts(page, perPage) {
  const params = new URLSearchParams({ time_range: currentTimeRange, page, per_page: perPage });
  history.replaceState({}, '', '?' + new URLSearchParams(Object.fromEntries(new URL(window.location.href).searchParams)).toString().replace(/page=\d+|per_page=\d+/g,'').replace(/&&/g,'&').replace(/^&|&$/g,'') + `&page=${page}&per_page=${perPage}`);
  try {
    const res  = await fetch(`${alertsEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return;
    const json = await res.json();
    const tbody = document.getElementById('alerts-tbody');
    tbody.innerHTML = json.data.length ? json.data.map(r => {
      const lvl = r.level || 0;
      const lc  = lvl>=12?'danger':lvl>=9?'warning':lvl>=6?'info':'secondary';
      const ts  = r.timestamp ? new Date(r.timestamp).toLocaleString('en-GB') : 'N/A';
      return `<tr>
        <td class="text-muted" style="font-size:10px;">${ts}</td>
        <td><span class="badge bg-light text-dark">${escHtml(r.rule_id)}</span></td>
        <td>${escHtml(r.description)}</td>
        <td><span class="badge bg-${lc}">${lvl}</span></td>
        <td class="text-center fw-bold">${r.count||1}</td>
        <td><span class="badge bg-secondary">${escHtml(r.groups)}</span></td>
      </tr>`;
    }).join('') : `<tr><td colspan="6" class="text-center py-5 text-muted">
      <span class="mdi mdi-monitor-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
      <span class="d-block fw-semibold mb-1">Tidak ada alert</span>
      <span class="d-block small">Tidak ada event keamanan dalam periode ini</span>
    </td></tr>`;
    renderPagination('alerts-footer', json.total, json.page, json.perPage, 'loadAlerts');
  } catch(e) { console.error('loadAlerts failed', e); }
}

async function loadGroups(page, perPage) {
  const params = new URLSearchParams({ time_range: currentTimeRange, page, per_page: perPage });
  try {
    const res  = await fetch(`${groupsEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return;
    const json = await res.json();
    const tbody = document.getElementById('groups-tbody');
    tbody.innerHTML = json.data.length ? json.data.map(r =>
      `<tr><td><span class="badge bg-secondary">${escHtml(r.group)}</span></td><td class="fw-bold">${r.count.toLocaleString()}</td></tr>`
    ).join('') : `<tr><td colspan="2" class="text-center py-5 text-muted">
      <span class="mdi mdi-monitor-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
      <span class="d-block fw-semibold mb-1">Tidak ada alert</span>
      <span class="d-block small">Tidak ada event keamanan dalam periode ini</span>
    </td></tr>`;
    renderPagination('groups-footer', json.total, json.page, json.perPage, 'loadGroups');
  } catch(e) { console.error('loadGroups failed', e); }
}

// Intercept pagination link clicks → AJAX instead of full reload
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('alerts-footer').addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    e.preventDefault();
    const u = new URL(a.href, location.href);
    loadAlerts(parseInt(u.searchParams.get('page')||1), parseInt(u.searchParams.get('per_page')||10));
  });
  document.getElementById('groups-footer').addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    e.preventDefault();
    const u = new URL(a.href, location.href);
    loadGroups(parseInt(u.searchParams.get('groups_page')||1), parseInt(u.searchParams.get('groups_per_page')||10));
  });
});

document.addEventListener('DOMContentLoaded', initializeCharts);

// ── GridStack ─────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'se-metrics',         x: 0, y: 0,  w: 9,  h: 3  },
    { id: 'se-timerange',       x: 9, y: 0,  w: 3,  h: 3  },
    { id: 'se-alert-groups',    x: 0, y: 3,  w: 6,  h: 8  },
    { id: 'se-alerts',          x: 6, y: 3,  w: 6,  h: 8  },
    { id: 'se-top-alerts',      x: 0, y: 11, w: 4,  h: 9  },
    { id: 'se-top-rule-groups', x: 4, y: 11, w: 4,  h: 9  },
    { id: 'se-top-pcidss',      x: 8, y: 11, w: 4,  h: 9  },
    { id: 'se-alerts-table',    x: 0, y: 20, w: 12, h: 10 },
    { id: 'se-groups-table',    x: 0, y: 30, w: 12, h: 8  },
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
    Object.values(chartInstances).forEach(c => c?.resize?.());
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
      body: JSON.stringify({ layout, page: 'security-events' })
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
    Object.values(chartInstances).forEach(c => c?.resize?.());
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });
})();
</script>
@endpush
