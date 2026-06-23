@extends('layouts.wazuh')

@section('title', 'Vulnerabilities - One For All')

@push('styles')
@include('partials._gridstack-styles')
<style>
  .vuln-stat-val { font-size: 2rem; font-weight: 700; line-height: 1; }
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

@include('agent._nav', ['agent' => $agent, 'activeTab' => 'vulnerabilities'])


<div class="grid-stack" id="vuln-grid">

  {{-- SEVERITY CHART --}}
  <div class="grid-stack-item" gs-id="vuln-severity" data-label="Tingkat Keparahan" gs-x="0" gs-y="0" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2 text-center">
          <span class="fw-semibold small text-uppercase text-muted tracking-wide">Tingkat Keparahan</span>
        </div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center">
          @php $totalSev = array_sum($severityCounts); @endphp
          @if($totalSev > 0)
          <div style="position:relative;width:100%;max-width:220px;height:180px;">
            <canvas id="vulnSeverityChart"></canvas>
          </div>
          <div class="d-flex flex-wrap gap-3 mt-3 justify-content-center small">
            <span><span class="mdi mdi-circle text-danger"></span> Kritis: <strong>{{ $severityCounts['Critical'] }}</strong></span>
            <span><span class="mdi mdi-circle" style="color:#fd7e14;"></span> Tinggi: <strong>{{ $severityCounts['High'] }}</strong></span>
            <span><span class="mdi mdi-circle text-warning"></span> Sedang: <strong>{{ $severityCounts['Medium'] }}</strong></span>
            <span><span class="mdi mdi-circle text-secondary"></span> Rendah: <strong>{{ $severityCounts['Low'] }}</strong></span>
          </div>
          @else
          <div class="d-flex flex-column align-items-center justify-content-center text-muted text-center">
            <span class="mdi mdi-bug-check-outline" style="font-size:3rem; opacity:0.3; margin-bottom:12px;"></span>
            <span class="fw-semibold mb-1">Tidak ada kerentanan</span>
            <span style="font-size:11px;">Agent ini bersih dari kerentanan yang terdeteksi</span>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- DETAILS --}}
  <div class="grid-stack-item" gs-id="vuln-details" data-label="Rincian" gs-x="4" gs-y="0" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2 text-center">
          <span class="fw-semibold small text-uppercase text-muted">Rincian</span>
        </div>
        <div class="card-body d-flex flex-column justify-content-center">
          <div class="row g-3 text-center mb-4">
            <div class="col-3">
              <div class="text-muted small mb-1">Kritis</div>
              <div class="vuln-stat-val text-danger">{{ $severityCounts['Critical'] }}</div>
            </div>
            <div class="col-3">
              <div class="text-muted small mb-1">Tinggi</div>
              <div class="vuln-stat-val" style="color:#fd7e14;">{{ $severityCounts['High'] }}</div>
            </div>
            <div class="col-3">
              <div class="text-muted small mb-1">Sedang</div>
              <div class="vuln-stat-val text-warning">{{ $severityCounts['Medium'] }}</div>
            </div>
            <div class="col-3">
              <div class="text-muted small mb-1">Rendah</div>
              <div class="vuln-stat-val text-secondary">{{ $severityCounts['Low'] }}</div>
            </div>
          </div>
          <hr class="my-2">
          <div class="row g-2 text-center small">
            <div class="col-6">
              <div class="text-muted mb-1">Pemindaian penuh terakhir</div>
              <div class="fw-semibold" style="font-size:11px;">
                @if($lastScan && !empty($lastScan['end']))
                  {{ \Carbon\Carbon::parse($lastScan['end'])->format('Y-m-d H:i') }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </div>
            </div>
            <div class="col-6">
              <div class="text-muted mb-1">Pemindaian parsial terakhir</div>
              <div class="fw-semibold" style="font-size:11px;">
                @if($lastScan && !empty($lastScan['start']))
                  {{ \Carbon\Carbon::parse($lastScan['start'])->format('Y-m-d H:i') }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- SUMMARY --}}
  <div class="grid-stack-item" gs-id="vuln-summary" data-label="Ringkasan" gs-x="8" gs-y="0" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
          <span class="fw-semibold small text-uppercase text-muted">Ringkasan</span>
          <select id="vuln-summary-field" class="form-select form-select-sm" style="width:auto;font-size:11px;">
            <option value="name">Nama</option>
            <option value="cve">CVE</option>
            <option value="version">Versi</option>
            <option value="cvss2">CVSS2 Score</option>
            <option value="cvss3">CVSS3 Score</option>
          </select>
        </div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center p-2">
          <div id="vuln-summary-chart-wrap" style="position:relative;width:100%;height:190px;display:none;">
            <canvas id="vulnSummaryChart"></canvas>
          </div>
          <div id="vuln-summary-empty" class="d-flex flex-column align-items-center justify-content-center text-muted text-center">
            <span class="mdi mdi-bug-check-outline d-block" style="font-size:3rem; opacity:0.3; margin-bottom:8px;"></span>
            <span class="fw-semibold mb-1">Tidak ada kerentanan</span>
            <span style="font-size:11px;" id="vuln-summary-empty-text">Tidak ada data Name yang ditemukan.</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- VULNERABILITIES TABLE --}}
  <div class="grid-stack-item" gs-id="vuln-table" data-label="Vulnerability List" gs-x="0" gs-y="8" gs-w="12" gs-h="12">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <span class="fw-semibold small">Kerentanan ({{ number_format($totalVulns) }})</span>
          {{-- Severity filter --}}
          @php $filterBase = array_merge(request()->query(), ['page' => 1]); @endphp
          <div id="vuln-severity-filter" class="btn-group btn-group-sm" role="group">
            <a href="?{{ http_build_query(array_merge($filterBase, ['severity' => ''])) }}"
               class="btn {{ !$severity ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
            <a href="?{{ http_build_query(array_merge($filterBase, ['severity' => 'Critical'])) }}"
               class="btn {{ $severity === 'Critical' ? 'btn-danger' : 'btn-outline-secondary' }}">Kritis</a>
            <a href="?{{ http_build_query(array_merge($filterBase, ['severity' => 'High'])) }}"
               class="btn {{ $severity === 'High' ? 'btn-warning text-white' : 'btn-outline-secondary' }}">Tinggi</a>
            <a href="?{{ http_build_query(array_merge($filterBase, ['severity' => 'Medium'])) }}"
               class="btn {{ $severity === 'Medium' ? 'btn-info text-white' : 'btn-outline-secondary' }}">Sedang</a>
            <a href="?{{ http_build_query(array_merge($filterBase, ['severity' => 'Low'])) }}"
               class="btn {{ $severity === 'Low' ? 'btn-secondary' : 'btn-outline-secondary' }}">Rendah</a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:18%">Nama</th>
                  <th style="width:12%">Versi</th>
                  <th style="width:10%">Arsitektur</th>
                  <th style="width:9%">Tingkat Keparahan</th>
                  <th style="width:13%">CVE</th>
                  <th style="width:8%" class="text-center">CVSS2</th>
                  <th style="width:8%" class="text-center">CVSS3</th>
                  <th style="width:22%">Waktu deteksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($vulnerabilities as $vuln)
                @php
                  $sev = $vuln['severity'] ?? 'None';
                  $sevColor = match($sev) {
                    'Critical' => 'danger',
                    'High'     => 'warning',
                    'Medium'   => 'info',
                    'Low'      => 'secondary',
                    default    => 'secondary',
                  };
                  $cvss2 = isset($vuln['cvss2_score']) ? number_format((float)$vuln['cvss2_score'], 1) : null;
                  $cvss3 = isset($vuln['cvss3_score']) ? number_format((float)$vuln['cvss3_score'], 1) : null;
                  $detectionTime = !empty($vuln['detection_time'])
                    ? \Carbon\Carbon::parse($vuln['detection_time'])->format('Y-m-d H:i:s')
                    : (!empty($vuln['published']) ? $vuln['published'] : null);
                @endphp
                <tr>
                  <td class="fw-semibold" title="{{ $vuln['name'] ?? '' }}">
                    {{ \Str::limit($vuln['name'] ?? 'N/A', 28) }}
                  </td>
                  <td class="text-muted font-monospace" style="font-size:10px;">
                    {{ \Str::limit($vuln['version'] ?? '—', 18) }}
                  </td>
                  <td class="text-muted">{{ $vuln['architecture'] ?? '—' }}</td>
                  <td>
                    <span class="badge bg-{{ $sevColor }} {{ in_array($sev, ['High','Medium']) ? 'text-white' : '' }}">{{ $sev }}</span>
                  </td>
                  <td class="font-monospace text-primary" style="font-size:10px;" title="{{ $vuln['title'] ?? '' }}">
                    {{ $vuln['cve'] ?? '—' }}
                  </td>
                  <td class="text-center">
                    @if($cvss2 !== null)
                      <span class="fw-semibold {{ (float)$cvss2 >= 9 ? 'text-danger' : ((float)$cvss2 >= 7 ? 'text-warning' : ((float)$cvss2 >= 4 ? 'text-info' : 'text-secondary')) }}">{{ $cvss2 }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-center">
                    @if($cvss3 !== null)
                      <span class="fw-semibold {{ (float)$cvss3 >= 9 ? 'text-danger' : ((float)$cvss3 >= 7 ? 'text-warning' : ((float)$cvss3 >= 4 ? 'text-info' : 'text-secondary')) }}">{{ $cvss3 }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td class="text-muted" style="font-size:10px;">{{ $detectionTime ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center py-5 text-muted">
                    <span class="mdi mdi-bug-check-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada kerentanan</span>
                    <span class="d-block small">{{ $severity ? 'Tidak ada kerentanan untuk severity: ' . $severity : 'Agent ini bersih dari kerentanan yang terdeteksi' }}</span>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @php
          $totalPages = $totalVulns > 0 ? (int) ceil($totalVulns / $perPage) : 1;
          $from       = $totalVulns > 0 ? ($page - 1) * $perPage + 1 : 0;
          $to         = min($page * $perPage, $totalVulns);
          $baseQuery  = request()->query();
          $pageUrl    = fn($p)  => '?' . http_build_query(array_merge($baseQuery, ['page' => $p,  'per_page' => $perPage]));
          $ppUrl      = fn($pp) => '?' . http_build_query(array_merge($baseQuery, ['page' => 1,   'per_page' => $pp]));
          $window     = collect(range(max(1, $page - 2), min($totalPages, $page + 2)));
        @endphp
        <div class="card-footer d-flex justify-content-between align-items-center py-2 small flex-wrap gap-2">
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-1">Baris:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $ppUrl($pp) }}"
                 class="btn btn-sm py-0 px-2 {{ $perPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $pp }}</a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $from }}–{{ $to }} dari {{ number_format($totalVulns) }}</span>
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
const vulnSeverityCounts = @json($severityCounts);
const vulnSummaryBatch   = @json($summaryBatch);

let vulnSeverityChartInstance = null;
let vulnSummaryChartInstance  = null;

function buildSummaryData(field) {
  const truncate = (s, n) => s && s.length > n ? s.substring(0, n) + '…' : (s || '—');
  let labels = [], values = [], label = 'Count';

  if (field === 'name' || field === 'version') {
    const grouped = {};
    vulnSummaryBatch.forEach(v => {
      const key = v[field] || '—';
      grouped[key] = (grouped[key] || 0) + 1;
    });
    const sorted = Object.entries(grouped).sort((a, b) => b[1] - a[1]).slice(0, 8);
    labels = sorted.map(([k]) => truncate(k, 20));
    values = sorted.map(([, c]) => c);
    label  = 'CVEs';
  } else if (field === 'cve') {
    const sorted = [...vulnSummaryBatch]
      .filter(v => v.cve)
      .sort((a, b) => (b.cvss3_score || b.cvss2_score || 0) - (a.cvss3_score || a.cvss2_score || 0))
      .slice(0, 8);
    labels = sorted.map(v => truncate(v.cve, 20));
    values = sorted.map(v => parseFloat(v.cvss3_score || v.cvss2_score || 0));
    label  = 'CVSS Score';
  } else if (field === 'cvss2') {
    const sorted = [...vulnSummaryBatch]
      .filter(v => v.cvss2_score != null)
      .sort((a, b) => b.cvss2_score - a.cvss2_score)
      .slice(0, 8);
    labels = sorted.map(v => truncate(v.cve || v.name || '—', 20));
    values = sorted.map(v => parseFloat(v.cvss2_score));
    label  = 'CVSS2 Score';
  } else if (field === 'cvss3') {
    const sorted = [...vulnSummaryBatch]
      .filter(v => v.cvss3_score != null)
      .sort((a, b) => b.cvss3_score - a.cvss3_score)
      .slice(0, 8);
    labels = sorted.map(v => truncate(v.cve || v.name || '—', 20));
    values = sorted.map(v => parseFloat(v.cvss3_score));
    label  = 'CVSS3 Score';
  }

  return { labels, values, label };
}

function updateSummaryChart(field) {
  const wrap  = document.getElementById('vuln-summary-chart-wrap');
  const empty = document.getElementById('vuln-summary-empty');
  const emptyText = document.getElementById('vuln-summary-empty-text');
  const labelMap = { name: 'Name', cve: 'CVE', version: 'Version', cvss2: 'CVSS2 Score', cvss3: 'CVSS3 Score' };

  const { labels, values, label } = buildSummaryData(field);

  if (labels.length === 0) {
    wrap.style.display  = 'none';
    empty.style.display = '';
    emptyText.textContent = `Tidak ada data ${labelMap[field] || field} yang ditemukan.`;
    if (vulnSummaryChartInstance) { vulnSummaryChartInstance.destroy(); vulnSummaryChartInstance = null; }
    return;
  }

  wrap.style.display  = '';
  empty.style.display = 'none';

  if (vulnSummaryChartInstance) {
    vulnSummaryChartInstance.data.labels = labels;
    vulnSummaryChartInstance.data.datasets[0].data  = values;
    vulnSummaryChartInstance.data.datasets[0].label = label;
    vulnSummaryChartInstance.update();
    return;
  }

  const ctx = document.getElementById('vulnSummaryChart')?.getContext('2d');
  if (!ctx) return;

  vulnSummaryChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ label, data: values, backgroundColor: 'rgba(75,73,172,0.7)', borderRadius: 3 }],
    },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { font: { size: 9 } }, grid: { color: 'rgba(0,0,0,.06)' } },
        y: { ticks: { font: { size: 9 } } },
      },
    },
  });
}

function initializeCharts() {
  if (typeof Chart === 'undefined') { setTimeout(initializeCharts, 100); return; }

  if (vulnSeverityChartInstance) { vulnSeverityChartInstance.destroy(); vulnSeverityChartInstance = null; }

  const totalSev = Object.values(vulnSeverityCounts).reduce((a, b) => a + b, 0);
  if (totalSev > 0) {
    const ctx = document.getElementById('vulnSeverityChart')?.getContext('2d');
    if (ctx) {
      vulnSeverityChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Critical', 'High', 'Medium', 'Low'],
          datasets: [{
            data: [
              vulnSeverityCounts['Critical'] || 0,
              vulnSeverityCounts['High']     || 0,
              vulnSeverityCounts['Medium']   || 0,
              vulnSeverityCounts['Low']      || 0,
            ],
            backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#adb5bd'],
            borderWidth: 2,
            borderColor: '#fff',
          }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          cutout: '60%',
          plugins: {
            legend: { display: true, position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } },
          },
        },
      });
    }
  }

  const field = document.getElementById('vuln-summary-field')?.value || 'name';
  updateSummaryChart(field);
}

document.addEventListener('DOMContentLoaded', () => {
  initializeCharts();
  document.getElementById('vuln-summary-field')?.addEventListener('change', e => {
    updateSummaryChart(e.target.value);
  });
});

// ── GridStack ──────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'vuln-severity', x: 0, y: 0,  w: 4,  h: 8  },
    { id: 'vuln-details',  x: 4, y: 0,  w: 4,  h: 8  },
    { id: 'vuln-summary',  x: 8, y: 0,  w: 4,  h: 8  },
    { id: 'vuln-table',    x: 0, y: 8,  w: 12, h: 12 },
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
    if (vulnSeverityChartInstance) vulnSeverityChartInstance.resize();
    if (vulnSummaryChartInstance)  vulnSummaryChartInstance.resize();
  });

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
      body: JSON.stringify({ layout, page: 'vulnerabilities' })
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
    if (vulnSeverityChartInstance) vulnSeverityChartInstance.resize();
    if (vulnSummaryChartInstance)  vulnSummaryChartInstance.resize();
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });
})();
</script>
@endpush
