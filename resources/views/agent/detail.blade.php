@extends('layouts.wazuh')

@section('title', 'Agent Detail - One For All')

@push('styles')
@include('partials._gridstack-styles')
<style>
  /* Allow dropdown to escape overflow clipping in the timerange card */
  #agent-detail-grid [gs-id="detail-timerange"] .grid-stack-item-content,
  #agent-detail-grid [gs-id="detail-timerange"] .card,
  #agent-detail-grid [gs-id="detail-timerange"] .card-body {
    overflow: visible !important;
  }
</style>
@endpush

@section('content')

@include('agent._nav', ['agent' => $agent, 'activeTab' => 'detail'])


@if($agent)

<div class="grid-stack" id="agent-detail-grid">

  {{-- META STRIP --}}
  <div class="grid-stack-item" gs-id="detail-meta" data-label="Agent Info" gs-x="0" gs-y="0" gs-w="10" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body py-3 small">
          <div class="d-grid gap-3 mb-0" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr))">
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">ID</div>
              <div class="fw-bold">{{ $agent->agent_id ?? 'N/A' }}</div>
            </div>
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Status</div>
              <div>
                @php
                  $statusColor = match($agent->status ?? 'unknown') {
                    'active'          => 'success',
                    'disconnected'    => 'danger',
                    'pending'         => 'warning',
                    'never_connected' => 'secondary',
                    default           => 'secondary',
                  };
                  $statusText = ucfirst(str_replace('_', ' ', $agent->status ?? 'unknown'));
                @endphp
                <span class="badge bg-{{ $statusColor }}"><span class="mdi mdi-circle me-1" style="font-size:7px"></span>{{ $statusText }}</span>
              </div>
            </div>
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Alamat IP</div>
              <div>{{ $agent->ip ?? 'N/A' }}</div>
            </div>
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Versi</div>
              <div>{{ $agent->version ?? 'N/A' }}</div>
            </div>
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Grup</div>
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
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Sistem Operasi</div>
              <div class="d-flex align-items-center">
                <i class="mdi {{ \App\Helpers\AgentHelper::getOSIcon($agent->os ?? '') }} text-primary"></i>
                {{ $agent->os ?? 'Unknown' }}
              </div>
            </div>
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Cluster Node</div>
              <div>{{ $agent->cluster_node ?? 'N/A' }}</div>
            </div>
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Tanggal Registrasi</div>
              <div>
                @if($agent->dateAdd)
                  {{ \Carbon\Carbon::parse($agent->dateAdd)->format('M d, Y @ H:i:s.000') }}
                @else
                  N/A
                @endif
              </div>
            </div>
            <div>
              <div class="text-uppercase text-muted fw-semibold" style="font-size:10px;letter-spacing:.05em">Koneksi Terakhir</div>
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
    </div>
  </div>

  {{-- TIME RANGE --}}
  <div class="grid-stack-item" gs-id="detail-timerange" data-label="Time Range" gs-x="10" gs-y="0" gs-w="2" gs-h="3">
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
              <span id="timeRangeLabel">24 Jam Terakhir</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="timeRangeDropdown">
              <li><a class="dropdown-item" href="#" onclick="updateChart('15m', event)">15 Menit Terakhir</a></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('30m', event)">30 Menit Terakhir</a></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('1h', event)">1 Jam Terakhir</a></li>
              <li><a class="dropdown-item active" href="#" onclick="updateChart('24h', event)">24 Jam Terakhir</a></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('7d', event)">7 Hari Terakhir</a></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('30d', event)">30 Hari Terakhir</a></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('90d', event)">90 Hari Terakhir</a></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('1y', event)">1 Tahun Terakhir</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('today', event)">Hari Ini</a></li>
              <li><a class="dropdown-item" href="#" onclick="updateChart('week', event)">Minggu Ini</a></li>
            </ul>
          </div>
          <button class="btn btn-outline-warning btn-sm" onclick="updateChart('24h', event)" title="Reset ke default (24 Jam Terakhir)">
            <span class="mdi mdi-restore me-1"></span> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- MITRE ATT&CK --}}
  <div class="grid-stack-item" gs-id="detail-mitre" data-label="MITRE ATT&CK" gs-x="0" gs-y="3" gs-w="3" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">MITRE ATT&amp;CK</span>
          <a href="https://attack.mitre.org/" target="_blank" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
        </div>
        <div class="card-body" data-mitre-container>
          <div class="d-flex flex-column align-items-center justify-content-center text-center py-5">
            <span class="mdi mdi-loading mdi-spin display-4 text-muted opacity-25 mb-3"></span>
            <p class="text-muted small mb-0">Memuat...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- COMPLIANCE --}}
  <div class="grid-stack-item" gs-id="detail-compliance" data-label="Compliance" gs-x="3" gs-y="3" gs-w="3" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">Kepatuhan</span>
          <select class="form-select form-select-sm w-auto" id="complianceSelect" onchange="updateComplianceData()">
            <option value="gdpr">GDPR</option>
            <option value="pci_dss">PCI DSS</option>
            <option value="nist_800_53">NIST 800-53</option>
            <option value="hipaa">HIPAA</option>
            <option value="gpg13">GPG13</option>
            <option value="tsc">TSC</option>
          </select>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div id="complianceChartContainer" style="width:100%;">
            <canvas id="complianceChart" style="max-width:600px; display:block; margin:auto;"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- FIM: Recent Events --}}
  <div class="grid-stack-item" gs-id="detail-fim" data-label="FIM Events" gs-x="6" gs-y="3" gs-w="6" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">FIM: Event Terbaru</span>
          <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px; min-width:900px;">
              <thead class="table-light">
                <tr>
                  <th style="width:15%; font-size:12px">Waktu</th>
                  <th style="width:35%; font-size:12px">Path</th>
                  <th style="width:10%; font-size:12px">Aksi</th>
                  <th style="width:20%; font-size:12px">Deskripsi Aturan</th>
                  <th style="width:10%; font-size:12px">Level Aturan</th>
                  <th style="width:10%; font-size:12px">ID Aturan</th>
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
                    <a href="#" class="text-primary text-decoration-none d-block text-truncate" style="width:100%" title="{{ $event['path'] }}">{{ $event['path'] }}</a>
                  </td>
                  <td>
                    @php
                      $actionColor = match(strtolower($event['action'] ?? '')) {
                        'deleted'  => 'danger',
                        'added'    => 'success',
                        'modified' => 'warning',
                        default    => 'secondary',
                      };
                    @endphp
                    <span class="badge bg-{{ $actionColor }}">{{ ucfirst($event['action'] ?? 'unknown') }}</span>
                  </td>
                  <td style="word-break:break-word; overflow-wrap:break-word;">{{ $event['description'] ?? 'Unknown' }}</td>
                  <td class="fw-bold text-center">{{ $event['level'] ?? 0 }}</td>
                  <td class="text-muted">{{ $event['rule_id'] ?? '' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <span class="mdi mdi-file-check-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada event FIM</span>
                    <span class="d-block small">Tidak ada perubahan file yang terdeteksi</span>
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

  {{-- EVENTS COUNT EVOLUTION --}}
  <div class="grid-stack-item" gs-id="detail-events" data-label="Events Count Evolution" gs-x="0" gs-y="13" gs-w="6" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Evolusi Jumlah Event</span>
        </div>
        <div class="card-body">
          <p class="mb-2 small"><span class="text-success me-1">●</span> Jumlah</p>
          <div id="eventsChartContainer">
            <canvas id="eventsChart" height="90"></canvas>
          </div>
          <p class="text-center mb-0 mt-1"><small class="text-muted" id="chartIntervalText">timestamp per hour</small></p>
        </div>
      </div>
    </div>
  </div>

  {{-- SCA: Latest Scans --}}
  <div class="grid-stack-item" gs-id="detail-sca" data-label="SCA Latest Scans" gs-x="6" gs-y="13" gs-w="6" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <span class="fw-semibold small">SCA: Pemindaian Terbaru</span>
          <a href="#" class="text-secondary small"><span class="mdi mdi-open-in-new"></span></a>
        </div>
        <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center flex-wrap gap-2">
          <a href="#" class="text-primary small fw-medium text-decoration-none">CIS Microsoft Windows Server 2022 Benchmark v1.0.0</a>
          <span class="badge bg-success" style="font-size:10px">cis_win2022</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:11px">
              <thead class="table-light">
                <tr>
                  <th>Kebijakan</th>
                  <th>Akhir pemindaian</th>
                  <th>Lulus</th>
                  <th>Gagal</th>
                  <th>Tidak berlaku</th>
                  <th>Skor</th>
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

@else

<div class="container-fluid py-5">
  <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
    <i class="mdi mdi-alert-circle-outline display-4"></i>
    <div>
      <h5 class="alert-heading mb-1">Agen Tidak Ditemukan</h5>
      <p class="mb-0">{{ $error ?? 'Tidak dapat memuat detail agen. Silakan coba lagi atau hubungi administrator.' }}</p>
      <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-danger mt-2">
        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Agen
      </a>
    </div>
  </div>
</div>

@endif

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack-all.js"></script>
<script>
// ── Global chart state ────────────────────────────────────────────────────────
let eventsChartInstance     = null;
let complianceChartInstance = null;
let currentTimeRange        = '24h';
let currentComplianceType   = 'gdpr';

const timeRangeLabels = {
  '15m':  '15 Menit Terakhir',
  '30m':  '30 Menit Terakhir',
  '1h':   '1 Jam Terakhir',
  '24h':  '24 Jam Terakhir',
  '7d':   '7 Hari Terakhir',
  '30d':  '30 Hari Terakhir',
  '90d':  '90 Hari Terakhir',
  '1y':   '1 Tahun Terakhir',
  'today':'Hari Ini',
  'week': 'Minggu Ini'
};

const intervalTexts = {
  '15m':  'timestamp per 3 menit',
  '30m':  'timestamp per 5 menit',
  '1h':   'timestamp per 10 menit',
  '24h':  'timestamp per jam',
  '7d':   'timestamp per 12 jam',
  '30d':  'timestamp per hari',
  '90d':  'timestamp per hari',
  '1y':   'timestamp per hari',
  'today':'timestamp per jam',
  'week': 'timestamp per hari'
};

function initEventsChart(labels, data) {
  const container = document.getElementById('eventsChartContainer');
  if (!container) return;

  if (labels.length === 0 || data.length === 0) {
    if (eventsChartInstance) { eventsChartInstance.destroy(); eventsChartInstance = null; }
    container.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center text-muted py-4 text-center" style="min-height:90px;">
      <span class="mdi mdi-chart-line-variant" style="font-size:2rem; opacity:0.3; margin-bottom:6px;"></span>
      <span class="fw-semibold mb-1 small">Tidak ada data</span>
      <span style="font-size:11px;">Tidak ada event keamanan dalam periode ini</span>
    </div>`;
    return;
  }

  if (!document.getElementById('eventsChart')) {
    container.innerHTML = '<canvas id="eventsChart" height="90"></canvas>';
  }

  const ctx = document.getElementById('eventsChart')?.getContext('2d');
  if (!ctx) return;

  if (eventsChartInstance) eventsChartInstance.destroy();

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
      animation: { duration: 750, easing: 'easeInOutQuart' },
      plugins: { legend: { display: false }, tooltip: { enabled: true } },
      interaction: { mode: 'index', intersect: false },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
        x: { ticks: { maxTicksLimit: 8, maxRotation: 0 }, grid: { display: false } }
      }
    }
  });
}

function initComplianceChart(data) {
  const container = document.getElementById('complianceChartContainer');
  if (!container) return;

  if (!data || data.length === 0) {
    if (complianceChartInstance) { complianceChartInstance.destroy(); complianceChartInstance = null; }
    container.innerHTML = `<div class="d-flex flex-column align-items-center justify-content-center text-muted py-4 text-center" style="min-height:200px;">
      <span class="mdi mdi-chart-line-variant" style="font-size:2.5rem; opacity:0.3; margin-bottom:8px;"></span>
      <span class="fw-semibold mb-1">Tidak ada data</span>
      <span class="small">Tidak ada data kepatuhan dalam periode ini</span>
    </div>`;
    return;
  }

  if (!document.getElementById('complianceChart')) {
    container.innerHTML = '<canvas id="complianceChart" style="max-width:600px; display:block; margin:auto;"></canvas>';
  }

  const ctx = document.getElementById('complianceChart')?.getContext('2d');
  if (!ctx) return;

  if (complianceChartInstance) complianceChartInstance.destroy();

  const labels    = data.map(item => item.name || 'Unknown').filter(l => l !== 'Unknown');
  const chartData = data.map(item => item.count || 0);
  if (labels.length === 0) return;

  const colors = ['#20c997','#0d6efd','#dc3545','#6f42c1','#d63384','#fd7e14','#20c997','#0dcaf0'];

  complianceChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
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
        legend: { display: true, position: 'bottom', labels: { boxWidth: 12, font: { size: 11 }, padding: 10 } },
        tooltip: { enabled: true }
      }
    }
  });
}

function updateChart(timeRange, event) {
  if (event) event.preventDefault();

  currentTimeRange = timeRange;
  document.getElementById('timeRangeLabel').textContent  = timeRangeLabels[timeRange] || 'Pilih rentang waktu';
  document.getElementById('chartIntervalText').textContent = intervalTexts[timeRange] || 'memuat...';

  const menu = document.getElementById('timeRangeDropdown')
                        ?.closest('.dropdown')
                        ?.querySelector('.dropdown-menu');
  if (menu) {
    menu.querySelectorAll('.dropdown-item').forEach(item => {
      const oc = item.getAttribute('onclick') || '';
      item.classList.toggle('active', oc.includes(`'${timeRange}'`));
    });
  }

  const agentId = '{{ $agent->agent_id ?? "" }}';
  if (!agentId) return;

  const url = '{{ route("agent.detail-chart-data", request()->route("id")) }}' + '?time_range=' + timeRange + '&compliance_type=' + currentComplianceType;

  fetch(url)
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(data => {
      if (data.success) {
        initEventsChart(data.events_evolution?.labels ?? [], data.events_evolution?.data ?? []);
        initComplianceChart(data.compliance_data ?? []);
        updateMitreTactics(data.mitre_tactics ?? []);
      }
    })
    .catch(error => {
      console.error('Error fetching chart data:', error);
      const errHtml = '<div style="display:flex;align-items:center;justify-content:center;height:90px;background:#fef2f2;border-radius:4px;color:#dc3545;font-size:13px;"><span class="mdi mdi-alert-circle-outline me-2"></span>Gagal memuat data</div>';
      const evCont = document.getElementById('eventsChartContainer');
      if (evCont) { if (eventsChartInstance) { eventsChartInstance.destroy(); eventsChartInstance = null; } evCont.innerHTML = errHtml; }
      const compCont = document.getElementById('complianceChartContainer');
      if (compCont) { if (complianceChartInstance) { complianceChartInstance.destroy(); complianceChartInstance = null; } compCont.innerHTML = errHtml; }
      const mitreCont = document.querySelector('[data-mitre-container]');
      if (mitreCont) mitreCont.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center text-center py-5"><span class="mdi mdi-alert-circle-outline display-4 text-muted opacity-25 mb-3"></span><p class="text-muted small mb-0">Gagal memuat data</p></div>';
    });
}

function updateComplianceData() {
  const sel = document.getElementById('complianceSelect');
  if (sel) { currentComplianceType = sel.value; updateChart(currentTimeRange); }
}

function updateMitreTactics(tactics) {
  const container = document.querySelector('[data-mitre-container]');
  if (!container) return;

  if (!tactics || tactics.length === 0) {
    container.innerHTML = `
      <div class="d-flex flex-column align-items-center justify-content-center text-center py-5">
        <span class="mdi mdi-sword-cross display-4 text-muted opacity-25 mb-3"></span>
        <span class="fw-semibold text-muted mb-1">Tidak ada taktik</span>
        <p class="text-muted small mb-0">Tidak ada taktik MITRE ATT&CK yang terdeteksi</p>
      </div>`;
  } else {
    container.innerHTML = tactics.map(t => `
      <div class="mb-2 pb-2" style="border-bottom: 1px solid #eee;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <small class="fw-semibold text-truncate" title="${t.tactic}">${t.tactic}</small>
          <span class="badge bg-danger">${t.count}</span>
        </div>
      </div>`).join('');
  }
}

function initializeCharts() {
  if (typeof Chart === 'undefined') { setTimeout(initializeCharts, 100); return; }
  updateChart('24h');
}

document.addEventListener('DOMContentLoaded', function () {
  initializeCharts();

  const dropdownBtn = document.getElementById('timeRangeDropdown');
  if (dropdownBtn && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
    new bootstrap.Dropdown(dropdownBtn);
  }
});

// ── GridStack ─────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'detail-meta',       x: 0, y: 0,  w: 10, h: 3  },
    { id: 'detail-timerange',  x: 10, y: 0, w: 2,  h: 3  },
    { id: 'detail-mitre',      x: 0, y: 3,  w: 3,  h: 10 },
    { id: 'detail-compliance', x: 3, y: 3,  w: 3,  h: 10 },
    { id: 'detail-fim',        x: 6, y: 3,  w: 6,  h: 10 },
    { id: 'detail-events',     x: 0, y: 13, w: 6,  h: 8  },
    { id: 'detail-sca',        x: 6, y: 13, w: 6,  h: 8  },
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
    if (eventsChartInstance)     eventsChartInstance.resize();
    if (complianceChartInstance) complianceChartInstance.resize();
  });

  // ── Edit mode ────────────────────────────────────────────────────────────
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
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 3, h: 8 };
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
      body: JSON.stringify({ layout: { items }, page: isMobileLayout ? 'agent-detail-mobile' : 'agent-detail' })
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
    if (eventsChartInstance)     eventsChartInstance.resize();
    if (complianceChartInstance) complianceChartInstance.resize();
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
