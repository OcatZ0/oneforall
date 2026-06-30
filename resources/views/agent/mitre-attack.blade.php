@extends('layouts.wazuh')

@section('title', 'MITRE ATT&CK - One For All')

@push('styles')
@include('partials._gridstack-styles')
<style>
  /* Allow dropdown to escape overflow clipping in the timerange card */
  #mitre-grid [gs-id="mitre-timerange"] .grid-stack-item-content,
  #mitre-grid [gs-id="mitre-timerange"] .card,
  #mitre-grid [gs-id="mitre-timerange"] .card-body {
    overflow: visible !important;
  }

  .no-data-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#aaa; font-size:12px; }
  .no-data-placeholder .mdi { font-size:2.5rem; margin-bottom:8px; }

  .level-badge {
    display:inline-block; width:22px; height:22px; line-height:22px;
    border-radius:4px; text-align:center; font-size:10px; font-weight:700; color:#fff;
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

@include('agent._nav', ['agent' => $agent, 'activeTab' => 'mitre-attack'])


<div class="grid-stack" id="mitre-grid">

  {{-- ROW 1: Alerts evolution | Time range + Top tactics --}}

  {{-- ALERTS EVOLUTION --}}
  <div class="grid-stack-item" gs-id="mitre-evolution" data-label="Evolusi Peringatan" gs-x="0" gs-y="0" gs-w="9" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Evolusi Peringatan dari Waktu ke Waktu</span>
        </div>
        <div class="card-body p-2">
          @if(count($evolution['datasets'] ?? []) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:160px;">
            <canvas id="mitreEvolutionChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-sword-cross" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada taktik</span>
            <div>Tidak ada taktik MITRE ATT&amp;CK yang terdeteksi</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TIME RANGE --}}
  <div class="grid-stack-item" gs-id="mitre-timerange" data-label="Rentang Waktu" gs-x="9" gs-y="0" gs-w="3" gs-h="3">
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

  {{-- TOP TACTICS PIE --}}
  <div class="grid-stack-item" gs-id="mitre-top-tactics" data-label="Taktik Teratas" gs-x="9" gs-y="3" gs-w="3" gs-h="5">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Taktik Teratas</span>
        </div>
        <div class="card-body p-2 d-flex align-items-center justify-content-center">
          @if(count($tactics) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:120px;">
            <canvas id="mitreTopTacticsChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-sword-cross" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada taktik</span>
            <div>Tidak ada taktik MITRE ATT&amp;CK yang terdeteksi</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ROW 2: Rule level by attack + MITRE attacks by tactic + Rule level by tactic --}}

  {{-- RULE LEVEL BY ATTACK --}}
  <div class="grid-stack-item" gs-id="mitre-level-by-attack" data-label="Level Aturan per Serangan" gs-x="0" gs-y="8" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Level Aturan per Serangan</span>
        </div>
        <div class="card-body p-2 d-flex align-items-center justify-content-center">
          @if(count($techniques) > 0 || count($ruleLevelCounts) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:160px;">
            <canvas id="mitreLevelByAttackChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-sword-cross" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada taktik</span>
            <div>Tidak ada taktik MITRE ATT&amp;CK yang terdeteksi</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- MITRE ATTACKS BY TACTIC (stacked bar) --}}
  <div class="grid-stack-item" gs-id="mitre-attacks-by-tactic" data-label="Serangan MITRE per Taktik" gs-x="4" gs-y="8" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Serangan MITRE per Taktik</span>
        </div>
        <div class="card-body p-2">
          @if(count($attacksByTactic['tactics'] ?? []) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:160px;">
            <canvas id="mitreAttacksByTacticChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-sword-cross" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada taktik</span>
            <div>Tidak ada taktik MITRE ATT&amp;CK yang terdeteksi</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- RULE LEVEL BY TACTIC --}}
  <div class="grid-stack-item" gs-id="mitre-level-by-tactic" data-label="Level Aturan per Taktik" gs-x="8" gs-y="8" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Level Aturan per Taktik</span>
        </div>
        <div class="card-body p-2 d-flex align-items-center justify-content-center">
          @if(count($tactics) > 0 || count($ruleLevelCounts) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:160px;">
            <canvas id="mitreLevelByTacticChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-sword-cross" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada taktik</span>
            <div>Tidak ada taktik MITRE ATT&amp;CK yang terdeteksi</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ROW 3: ALERTS TABLE --}}
  <div class="grid-stack-item" gs-id="mitre-alerts" data-label="Peringatan MITRE ATT&CK" gs-x="0" gs-y="16" gs-w="12" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Peringatan MITRE ATT&amp;CK ({{ number_format($totalAlerts) }})</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:17%">Waktu</th>
                  <th style="width:6%">Rule ID</th>
                  <th style="width:5%" class="text-center">Level</th>
                  <th style="width:29%">Deskripsi</th>
                  <th style="width:13%">Taktik</th>
                  <th style="width:16%">Teknik</th>
                  <th style="width:14%">ID Teknik</th>
                </tr>
              </thead>
              <tbody id="mitre-alerts-tbody">
                @forelse($alerts as $alert)
                @php
                  $level = (int)($alert['level'] ?? 0);
                  $levelColor = $level >= 12 ? '#dc3545' : ($level >= 9 ? '#fd7e14' : ($level >= 6 ? '#ffc107' : '#adb5bd'));
                @endphp
                <tr>
                  <td class="text-muted" style="font-size:10px;">
                    {{ !empty($alert['timestamp']) ? \Carbon\Carbon::parse($alert['timestamp'])->format('Y-m-d H:i:s') : '—' }}
                  </td>
                  <td class="font-monospace text-primary" style="font-size:10px;">{{ $alert['rule_id'] ?? '—' }}</td>
                  <td class="text-center">
                    <span class="level-badge" style="background:{{ $levelColor }};">{{ $level }}</span>
                  </td>
                  <td title="{{ $alert['description'] ?? '' }}">{{ \Str::limit($alert['description'] ?? '—', 65) }}</td>
                  <td>
                    @foreach(array_filter(explode(', ', $alert['tactic'] ?? '')) as $t)
                      <span class="badge bg-primary me-1 mb-1" style="font-size:9px;">{{ $t }}</span>
                    @endforeach
                  </td>
                  <td class="text-muted">{{ \Str::limit($alert['technique'] ?? '—', 30) }}</td>
                  <td class="font-monospace" style="font-size:10px;">{{ $alert['mitre_id'] ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="7" class="text-center py-5 text-muted">
                    <span class="mdi mdi-sword-cross d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada taktik</span>
                    <span class="d-block small">Tidak ada taktik MITRE ATT&amp;CK yang terdeteksi</span>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        <div id="mitre-alerts-footer"></div>
      </div>
    </div>
  </div>

</div>{{-- /grid-stack --}}

{{-- Floating pencil --}}
<div id="gs-fab">
  <button id="gs-fab-main" title="Edit layout"><i class="mdi mdi-pencil" id="gs-fab-icon"></i></button>
</div>
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
const mitreEvolutionData    = @json($evolution);
const mitreTacticsData      = @json($tactics);
const mitreTechniquesData   = @json($techniques);
const mitreAttacksByTactic  = @json($attacksByTactic);
const mitreRuleLevelCounts  = @json($ruleLevelCounts);

const PALETTE = ['#4B49AC','#7978E9','#dc3545','#20c997','#fd7e14','#ffc107','#6f42c1','#0d6efd','#17a2b8','#6c757d','#e91e63','#00bcd4'];

let chartInstances = {};

function destroyChart(key) {
  if (chartInstances[key]) { chartInstances[key].destroy(); delete chartInstances[key]; }
}

function initializeCharts() {
  if (typeof Chart === 'undefined') { setTimeout(initializeCharts, 100); return; }
  Object.keys(chartInstances).forEach(k => chartInstances[k]?.destroy());
  chartInstances = {};

  // 1. Evolution line chart
  if ((mitreEvolutionData.datasets || []).length > 0) {
    const ctx = document.getElementById('mitreEvolutionChart')?.getContext('2d');
    if (ctx) {
      chartInstances.evolution = new Chart(ctx, {
        type: 'line',
        data: {
          labels: mitreEvolutionData.labels,
          datasets: mitreEvolutionData.datasets,
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 6 } },
          },
          scales: {
            x: {
              ticks: { font: { size: 9 }, maxTicksLimit: 8, maxRotation: 0,
                callback: (val, i, ticks) => {
                  const lbl = mitreEvolutionData.labels[i] || '';
                  try { return new Date(lbl).toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' }); } catch { return lbl; }
                }
              },
              grid: { color: 'rgba(0,0,0,.05)' },
              title: { display: true, text: 'timestamp per 30 minutes', font: { size: 9 }, color: '#888' },
            },
            y: {
              beginAtZero: true,
              ticks: { font: { size: 9 } },
              grid: { color: 'rgba(0,0,0,.05)' },
              title: { display: true, text: 'Count', font: { size: 9 }, color: '#888' },
            },
          },
        },
      });
    }
  }

  // 2. Top tactics pie
  if (mitreTacticsData.length > 0) {
    const ctx = document.getElementById('mitreTopTacticsChart')?.getContext('2d');
    if (ctx) {
      chartInstances.topTactics = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: mitreTacticsData.map(d => d.tactic),
          datasets: [{ data: mitreTacticsData.map(d => d.count), backgroundColor: PALETTE, borderWidth: 2, borderColor: '#fff' }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 6 } } },
        },
      });
    }
  }

  // 3. Rule level by attack — outer ring: techniques, inner ring: levels
  if (mitreTechniquesData.length > 0 || mitreRuleLevelCounts.length > 0) {
    const ctx = document.getElementById('mitreLevelByAttackChart')?.getContext('2d');
    if (ctx) {
      const techLabels  = mitreTechniquesData.map(d => d.technique.length > 22 ? d.technique.substring(0,22)+'…' : d.technique);
      const techCounts  = mitreTechniquesData.map(d => d.count);
      const levelLabels = mitreRuleLevelCounts.map(d => String(d.level));
      const levelCounts = mitreRuleLevelCounts.map(d => d.count);
      const levelColors = mitreRuleLevelCounts.map((d, i) => PALETTE[(i + 6) % PALETTE.length]);

      chartInstances.levelByAttack = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: [...techLabels, ...levelLabels],
          datasets: [
            { label: 'Techniques', data: techCounts,  backgroundColor: PALETTE.slice(0, techCounts.length),  borderWidth: 2, borderColor: '#fff' },
            { label: 'Levels',     data: levelCounts, backgroundColor: levelColors, borderWidth: 2, borderColor: '#fff' },
          ],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          cutout: '40%',
          plugins: { legend: { display: true, position: 'right', labels: { font: { size: 9 }, boxWidth: 8, padding: 4 } } },
        },
      });
    }
  }

  // 4. MITRE attacks by tactic (stacked vertical bar)
  if ((mitreAttacksByTactic.tactics || []).length > 0) {
    const ctx = document.getElementById('mitreAttacksByTacticChart')?.getContext('2d');
    if (ctx) {
      const datasets = (mitreAttacksByTactic.datasets || []).map((ds, i) => ({
        ...ds,
        backgroundColor: PALETTE[i % PALETTE.length],
        borderRadius: 2,
      }));
      chartInstances.attacksByTactic = new Chart(ctx, {
        type: 'bar',
        data: { labels: mitreAttacksByTactic.tactics, datasets },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { display: true, position: 'right', labels: { font: { size: 9 }, boxWidth: 8, padding: 4 } },
          },
          scales: {
            x: {
              stacked: true,
              ticks: { font: { size: 9 }, maxRotation: 45 },
              grid: { color: 'rgba(0,0,0,.05)' },
              title: { display: true, text: 'rule.mitre.tactic: Descending', font: { size: 8 }, color: '#888' },
            },
            y: { stacked: true, beginAtZero: true, ticks: { font: { size: 9 } }, grid: { color: 'rgba(0,0,0,.05)' }, title: { display: true, text: 'Count', font: { size: 9 }, color: '#888' } },
          },
        },
      });
    }
  }

  // 5. Rule level by tactic — outer: tactics, inner: levels
  if (mitreTacticsData.length > 0 || mitreRuleLevelCounts.length > 0) {
    const ctx = document.getElementById('mitreLevelByTacticChart')?.getContext('2d');
    if (ctx) {
      const tacticLabels = mitreTacticsData.map(d => d.tactic);
      const tacticCounts = mitreTacticsData.map(d => d.count);
      const levelLabels  = mitreRuleLevelCounts.map(d => String(d.level));
      const levelCounts  = mitreRuleLevelCounts.map(d => d.count);
      const levelColors  = mitreRuleLevelCounts.map((d, i) => PALETTE[(i + 4) % PALETTE.length]);

      chartInstances.levelByTactic = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: [...tacticLabels, ...levelLabels],
          datasets: [
            { label: 'Tactics', data: tacticCounts,  backgroundColor: PALETTE.slice(0, tacticCounts.length), borderWidth: 2, borderColor: '#fff' },
            { label: 'Levels',  data: levelCounts,   backgroundColor: levelColors, borderWidth: 2, borderColor: '#fff' },
          ],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          cutout: '40%',
          plugins: { legend: { display: true, position: 'right', labels: { font: { size: 9 }, boxWidth: 8, padding: 4 } } },
        },
      });
    }
  }
}

// ── AJAX pagination helpers ────────────────────────────────────────────────────
function escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s != null ? String(s) : '';
  return d.innerHTML;
}

function renderPagination(footerId, total, page, perPage, loadFn) {
  const totalPages = total > 0 ? Math.ceil(total / perPage) : 1;
  const from = total > 0 ? (page - 1) * perPage + 1 : 0;
  const to   = Math.min(page * perPage, total);
  const btn  = (p, pp, label, disabled, active) =>
    `<button ${disabled ? 'disabled' : `onclick="${loadFn}(${p},${pp})"`} class="btn btn-sm py-0 px-2 ${active ? 'btn-primary' : 'btn-outline-secondary'}${disabled ? ' disabled' : ''}">${label}</button>`;
  const ppBtns  = [10, 25, 50].map(pp => btn(1, pp, pp, false, perPage === pp)).join('');
  const winBtns = [];
  for (let p = Math.max(1, page - 2); p <= Math.min(totalPages, page + 2); p++) {
    winBtns.push(btn(p, perPage, p, false, p === page));
  }
  document.getElementById(footerId).innerHTML = `
    <div class="d-flex justify-content-between align-items-center py-2 small flex-wrap gap-2 px-3 w-100">
      <div class="d-flex align-items-center gap-1"><span class="text-muted me-1">Rows:</span>${ppBtns}</div>
      <div class="d-flex align-items-center gap-1">
        <span class="text-muted me-2">${from}–${to} of ${total.toLocaleString()}</span>
        ${btn(1, perPage, '«', page <= 1, false)}
        ${btn(Math.max(1, page - 1), perPage, '‹', page <= 1, false)}
        ${winBtns.join('')}
        ${btn(Math.min(totalPages, page + 1), perPage, '›', page >= totalPages, false)}
        ${btn(totalPages, perPage, '»', page >= totalPages, false)}
      </div>
    </div>`;
}

const mitreAlertsEndpoint = '{{ route("agent.mitre.alerts", request()->route("id")) }}';

async function loadMitreAlerts(page, perPage) {
  const params = new URLSearchParams({ time_range: currentTimeRange, page, per_page: perPage });
  try {
    const res  = await fetch(`${mitreAlertsEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return;
    const json = await res.json();
    document.getElementById('mitre-alerts-tbody').innerHTML = json.data.length
      ? json.data.map(r => {
          const lvl = r.level || 0;
          const lc  = lvl >= 12 ? '#dc3545' : lvl >= 9 ? '#fd7e14' : lvl >= 6 ? '#ffc107' : '#adb5bd';
          const ts  = r.timestamp ? new Date(r.timestamp).toLocaleString('en-GB') : '—';
          const tactics = (r.tactic || '').split(', ').filter(Boolean)
            .map(t => `<span class="badge bg-primary me-1" style="font-size:9px;">${escHtml(t)}</span>`).join('');
          return `<tr>
            <td class="text-muted" style="font-size:10px;">${ts}</td>
            <td class="font-monospace text-primary" style="font-size:10px;">${escHtml(r.rule_id || '—')}</td>
            <td class="text-center"><span class="level-badge" style="background:${lc};">${lvl}</span></td>
            <td>${escHtml((r.description || '—').substring(0, 65))}</td>
            <td>${tactics}</td>
            <td class="text-muted">${escHtml((r.technique || '—').substring(0, 30))}</td>
            <td class="font-monospace" style="font-size:10px;">${escHtml(r.mitre_id || '—')}</td>
          </tr>`;
        }).join('')
      : `<tr><td colspan="7" class="text-center py-5 text-muted">
        <span class="mdi mdi-sword-cross d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
        <span class="d-block fw-semibold mb-1">Tidak ada taktik</span>
        <span class="d-block small">Tidak ada taktik MITRE ATT&CK yang terdeteksi</span>
      </td></tr>`;
    renderPagination('mitre-alerts-footer', json.total, json.page, json.perPage, 'loadMitreAlerts');
  } catch (e) { console.error('loadMitreAlerts failed', e); }
}

document.addEventListener('DOMContentLoaded', () => {
  initializeCharts();
  renderPagination('mitre-alerts-footer', {{ $totalAlerts }}, {{ $page }}, {{ $perPage }}, 'loadMitreAlerts');
});

// ── Time range (must be declared before loadMitreAlerts) ───────────────────────
let currentTimeRange = '{{ $timeRange ?? "24h" }}';

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
  const lbl = document.getElementById('timeRangeLabel');
  if (lbl) lbl.textContent = timeRangeLabels[timeRange] || 'Pilih rentang waktu';

  const menu = document.getElementById('timeRangeDropdown')?.closest('.dropdown')?.querySelector('.dropdown-menu');
  if (menu) {
    menu.querySelectorAll('.dropdown-item').forEach(item => {
      const oc = item.getAttribute('onclick') || '';
      item.classList.toggle('active', oc.includes(`'${timeRange}'`));
    });
  }
  refreshData();
}

function refreshData() {
  const url = new URL(window.location.href);
  url.searchParams.set('time_range', currentTimeRange);
  url.searchParams.set('page', '1');
  window.location.href = url.toString();
}

// ── GridStack ──────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'mitre-evolution',         x: 0, y: 0,  w: 9,  h: 8  },
    { id: 'mitre-timerange',         x: 9, y: 0,  w: 3,  h: 3  },
    { id: 'mitre-top-tactics',       x: 9, y: 3,  w: 3,  h: 5  },
    { id: 'mitre-level-by-attack',   x: 0, y: 8,  w: 4,  h: 8  },
    { id: 'mitre-attacks-by-tactic', x: 4, y: 8,  w: 4,  h: 8  },
    { id: 'mitre-level-by-tactic',   x: 8, y: 8,  w: 4,  h: 8  },
    { id: 'mitre-alerts',            x: 0, y: 16, w: 12, h: 10 },
  ];

  const grid = GridStack.init({
    column: 12, cellHeight: 60, margin: 8, float: false, staticGrid: true,
    resizable: { handles: 'se' },
    draggable: { handle: '.gs-drag-handle' },
    columnOpts: { breakpointForWindow: true, breakpoints: [{ w: 768, c: 1 }] },
  });

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
      const id = item.getAttribute('gs-id'), isHidden = hiddenCards.has(id);
      const btn = document.createElement('button');
      btn.className = 'gs-hide-btn';
      btn.title     = isHidden ? 'Tampilkan kartu' : 'Sembunyikan kartu';
      btn.innerHTML = `<i class="mdi mdi-${isHidden ? 'eye' : 'eye-off'}"></i>`;
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

  grid.on('resizestop', () => Object.values(chartInstances).forEach(c => c?.resize?.()));

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
      const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 4, h: 8 };
      el.setAttribute('gs-x', pos.x); el.setAttribute('gs-y', pos.y);
      el.setAttribute('gs-w', pos.w); el.setAttribute('gs-h', pos.h);
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
      body: JSON.stringify({ layout: { items }, page: isMobileLayout ? 'mitre-attack-mobile' : 'mitre-attack' })
    }).then(r => r.json()).then(d => { if (d.success) { exitEdit(); gsShowSavedToast(); } });
  });

  document.getElementById('gs-reset').addEventListener('click', () => {
    [...hiddenCards].forEach(id => {
      hiddenCards.delete(id); delete hiddenPositions[id];
      const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (el) el.classList.remove('gs-card-hidden');
    });
    grid.load(DEFAULT_LAYOUT.map(i => ({ ...i })));
    Object.values(chartInstances).forEach(c => c?.resize?.());
  });

  document.getElementById('gs-cancel').addEventListener('click', () => { exitEdit(); location.reload(); });

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
