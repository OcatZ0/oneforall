@extends('layouts.wazuh')

@section('title', 'Compliance - One For All')

@push('styles')
@include('partials._gridstack-styles')
<style>
  .no-data-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#aaa; font-size:12px; }
  .no-data-placeholder .mdi { font-size:2.5rem; margin-bottom:8px; }
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

{{-- SECONDARY NAV --}}
<div class="bg-dark border-bottom border-secondary">
  <div class="d-flex align-items-center px-3">
    <ul class="nav flex-nowrap overflow-auto">
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.detail', $agent->agent_id) }}">
          <span class="mdi mdi-home"></span> Detail
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.security-events', $agent->agent_id) }}">
          <span class="mdi mdi-format-list-bulleted"></span> Event Keamanan
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.integrity-monitoring', $agent->agent_id) }}">
          <span class="mdi mdi-shield"></span> Pemantauan Integritas
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.sca', $agent->agent_id) }}">
          <span class="mdi mdi-clock-outline"></span> SCA
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.vulnerabilities', $agent->agent_id) }}">
          <span class="mdi mdi-bug"></span> Kerentanan
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.mitre-attack', $agent->agent_id) }}">
          <span class="mdi mdi-sword-cross"></span> MITRE ATT&amp;CK
        </a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="mdi mdi-check-decagram"></span> {{ $typeLabels[$complianceType] ?? 'Kepatuhan' }}
        </a>
        <ul class="dropdown-menu dropdown-menu-dark">
          <li><a class="dropdown-item {{ $complianceType === 'pci_dss'     ? 'active' : '' }}" href="{{ route('agent.compliance', $agent->agent_id) }}?compliance_type=pci_dss">PCI DSS</a></li>
          <li><a class="dropdown-item {{ $complianceType === 'gdpr'        ? 'active' : '' }}" href="{{ route('agent.compliance', $agent->agent_id) }}?compliance_type=gdpr">GDPR</a></li>
          <li><a class="dropdown-item {{ $complianceType === 'hipaa'       ? 'active' : '' }}" href="{{ route('agent.compliance', $agent->agent_id) }}?compliance_type=hipaa">HIPAA</a></li>
          <li><a class="dropdown-item {{ $complianceType === 'nist_800_53' ? 'active' : '' }}" href="{{ route('agent.compliance', $agent->agent_id) }}?compliance_type=nist_800_53">NIST 800-53</a></li>
          <li><a class="dropdown-item {{ $complianceType === 'tsc'         ? 'active' : '' }}" href="{{ route('agent.compliance', $agent->agent_id) }}?compliance_type=tsc">TSC</a></li>
        </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.inventory', $agent->agent_id) }}">
          <span class="mdi mdi-database"></span> Data Inventaris
        </a>
      </li>
    </ul>
    <div class="ms-auto d-flex gap-2 flex-shrink-0 py-1">
      <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent') }}" title="Kembali ke Daftar Agen">
        <span class="mdi mdi-arrow-left"></span> Kembali
      </a>
    </div>
  </div>
</div>


@php
  $typeLabels = [
    'pci_dss'     => 'PCI DSS',
    'gdpr'        => 'GDPR',
    'hipaa'       => 'HIPAA',
    'nist_800_53' => 'NIST 800-53',
    'tsc'         => 'TSC',
  ];
  $currentTypeLabel = $typeLabels[$complianceType] ?? 'GDPR';
@endphp

<div class="grid-stack" id="comp-grid">

  {{-- TIME RANGE (top right, small) --}}
  <div class="grid-stack-item" gs-id="comp-controls" data-label="Rentang Waktu" gs-x="8" gs-y="0" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content" style="overflow:visible;">
      <div class="card gs-card" style="overflow:visible;">
        <div class="card-header py-2">
          <span class="fw-semibold small">Rentang Waktu</span>
        </div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2" style="overflow:visible;">
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown"
              data-bs-toggle="dropdown" aria-expanded="false">
              <span class="mdi mdi-calendar-outline me-1"></span>
              <span id="timeRangeLabel">{{ ['15m'=>'15 menit terakhir','30m'=>'30 menit terakhir','1h'=>'1 jam terakhir','24h'=>'24 jam terakhir','7d'=>'7 hari terakhir','30d'=>'30 hari terakhir','90d'=>'90 hari terakhir','1y'=>'1 tahun terakhir','today'=>'Hari ini','week'=>'Minggu ini'][$timeRange] ?? '24 jam terakhir' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="timeRangeDropdown">
              <li><a class="dropdown-item {{ $timeRange === '15m'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('15m',   event)">15 menit terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '30m'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('30m',   event)">30 menit terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '1h'    ? 'active' : '' }}" href="#" onclick="updateTimeRange('1h',    event)">1 jam terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '24h'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('24h',   event)">24 jam terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '7d'    ? 'active' : '' }}" href="#" onclick="updateTimeRange('7d',    event)">7 hari terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '30d'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('30d',   event)">30 hari terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '90d'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('90d',   event)">90 hari terakhir</a></li>
              <li><a class="dropdown-item {{ $timeRange === '1y'    ? 'active' : '' }}" href="#" onclick="updateTimeRange('1y',    event)">1 tahun terakhir</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item {{ $timeRange === 'today' ? 'active' : '' }}" href="#" onclick="updateTimeRange('today', event)">Hari ini</a></li>
              <li><a class="dropdown-item {{ $timeRange === 'week'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('week',  event)">Minggu ini</a></li>
            </ul>
          </div>
          <button class="btn btn-outline-warning btn-sm" onclick="resetFilters()" title="Reset ke default (24 jam terakhir)">
            <span class="mdi mdi-restore me-1"></span> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ROW 2: Three doughnuts --}}

  {{-- TOP 5 RULE GROUPS --}}
  <div class="grid-stack-item" gs-id="comp-top-groups" data-label="5 Grup Aturan Teratas" gs-x="0" gs-y="0" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 Grup Aturan Teratas</span>
        </div>
        <div class="card-body p-2 d-flex align-items-center justify-content-center">
          @if(count($topRuleGroups['labels'] ?? []) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:140px;">
            <canvas id="topRuleGroupsChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-chart-line-variant" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada data</span>
            <div>Tidak ada event keamanan dalam periode ini</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 RULES --}}
  <div class="grid-stack-item" gs-id="comp-top-rules" data-label="5 Aturan Teratas" gs-x="4" gs-y="0" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 Aturan Teratas</span>
        </div>
        <div class="card-body p-2 d-flex align-items-center justify-content-center">
          @if(count($topRules['labels'] ?? []) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:140px;">
            <canvas id="topRulesChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-chart-line-variant" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada data</span>
            <div>Tidak ada event keamanan dalam periode ini</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 COMPLIANCE REQUIREMENTS --}}
  <div class="grid-stack-item" gs-id="comp-top-requirements" data-label="5 Persyaratan Teratas" gs-x="0" gs-y="8" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">5 Persyaratan {{ $currentTypeLabel }} Teratas</span>
        </div>
        <div class="card-body p-2 d-flex align-items-center justify-content-center">
          @if(count($top5Compliance) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:140px;">
            <canvas id="topComplianceChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-chart-line-variant" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada data</span>
            <div>Tidak ada data {{ $currentTypeLabel }} dalam periode ini</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ROW 3: Bar chart + Rule level doughnut --}}

  {{-- REQUIREMENTS BAR CHART --}}
  <div class="grid-stack-item" gs-id="comp-requirements-bar" data-label="Bar Persyaratan" gs-x="4" gs-y="8" gs-w="5" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Persyaratan {{ $currentTypeLabel }}</span>
        </div>
        <div class="card-body p-2">
          @if(count($allCompliance) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:140px;">
            <canvas id="requirementsBarChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-chart-line-variant" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada data</span>
            <div>Tidak ada data {{ $currentTypeLabel }} dalam periode ini</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- RULE LEVEL DISTRIBUTION --}}
  <div class="grid-stack-item" gs-id="comp-rule-level" data-label="Distribusi Level Aturan" gs-x="9" gs-y="8" gs-w="3" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Distribusi Level Aturan</span>
        </div>
        <div class="card-body p-2 d-flex align-items-center justify-content-center">
          @if(count($ruleLevelDist) > 0)
          <div style="position:relative;width:100%;height:100%;min-height:140px;">
            <canvas id="ruleLevelChart"></canvas>
          </div>
          @else
          <div class="no-data-placeholder">
            <span class="mdi mdi-chart-line-variant" style="opacity:0.3;"></span>
            <span class="fw-semibold">Tidak ada data</span>
            <div>Tidak ada data dalam periode ini</div>
          </div>
          @endif
        </div>
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
const topRuleGroupsData  = @json($topRuleGroups);
const topRulesData       = @json($topRules);
const top5ComplianceData = @json($top5Compliance);
const allComplianceData  = @json($allCompliance);
const ruleLevelDistData  = @json($ruleLevelDist);

const PALETTE = ['#4B49AC','#7978E9','#dc3545','#20c997','#fd7e14','#ffc107','#6f42c1','#0d6efd','#17a2b8','#6c757d','#e91e63','#00bcd4'];

let chartInstances = {};

function initializeCharts() {
  if (typeof Chart === 'undefined') { setTimeout(initializeCharts, 100); return; }
  Object.values(chartInstances).forEach(c => c?.destroy());
  chartInstances = {};

  // 1. Top 5 rule groups — doughnut
  if ((topRuleGroupsData.labels || []).length > 0) {
    const ctx = document.getElementById('topRuleGroupsChart')?.getContext('2d');
    if (ctx) {
      chartInstances.ruleGroups = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: topRuleGroupsData.labels,
          datasets: [{ data: topRuleGroupsData.data, backgroundColor: PALETTE, borderWidth: 2, borderColor: '#fff' }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 6 } } },
        },
      });
    }
  }

  // 2. Top 5 rules — doughnut
  if ((topRulesData.labels || []).length > 0) {
    const ctx = document.getElementById('topRulesChart')?.getContext('2d');
    if (ctx) {
      chartInstances.topRules = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: topRulesData.labels,
          datasets: [{ data: topRulesData.data, backgroundColor: PALETTE, borderWidth: 2, borderColor: '#fff' }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 6 } } },
        },
      });
    }
  }

  // 3. Top 5 compliance requirements — doughnut
  if (top5ComplianceData.length > 0) {
    const ctx = document.getElementById('topComplianceChart')?.getContext('2d');
    if (ctx) {
      chartInstances.topCompliance = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: top5ComplianceData.map(d => d.name),
          datasets: [{ data: top5ComplianceData.map(d => d.count), backgroundColor: PALETTE, borderWidth: 2, borderColor: '#fff' }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 6 } } },
        },
      });
    }
  }

  // 4. All compliance requirements — bar chart
  if (allComplianceData.length > 0) {
    const ctx = document.getElementById('requirementsBarChart')?.getContext('2d');
    if (ctx) {
      chartInstances.requirementsBar = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: allComplianceData.map(d => d.name),
          datasets: [{
            label: 'Count',
            data: allComplianceData.map(d => d.count),
            backgroundColor: allComplianceData.map((_, i) => PALETTE[i % PALETTE.length]),
            borderRadius: 3,
            borderWidth: 0,
          }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: {
            legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 6 } },
          },
          scales: {
            x: {
              ticks: { font: { size: 9 }, maxRotation: 30 },
              grid: { color: 'rgba(0,0,0,.05)' },
              title: { display: true, text: '{{ Str::upper(str_replace("_", " ", $complianceType)) }} requirements', font: { size: 9 }, color: '#888' },
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

  // 5. Rule level distribution — doughnut with % in legend
  if (ruleLevelDistData.length > 0) {
    const ctx = document.getElementById('ruleLevelChart')?.getContext('2d');
    if (ctx) {
      const total    = ruleLevelDistData.reduce((s, d) => s + d.count, 0);
      const labels   = ruleLevelDistData.map(d => `${d.level} (${total > 0 ? ((d.count / total) * 100).toFixed(2) : 0}%)`);
      const counts   = ruleLevelDistData.map(d => d.count);
      const lvlColors = ruleLevelDistData.map(d => {
        const lv = d.level;
        return lv >= 12 ? '#dc3545' : lv >= 9 ? '#fd7e14' : lv >= 6 ? '#ffc107' : '#6f42c1';
      });

      chartInstances.ruleLevel = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{ data: counts, backgroundColor: lvlColors, borderWidth: 2, borderColor: '#fff' }],
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: true, position: 'right', labels: { font: { size: 10 }, boxWidth: 10, padding: 6 } } },
        },
      });
    }
  }
}

document.addEventListener('DOMContentLoaded', initializeCharts);

// ── Time range ─────────────────────────────────────────────────────────────────
let currentTimeRange = '{{ $timeRange }}';

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
  'week': 'Minggu ini',
};

function updateTimeRange(range, event) {
  if (event) event.preventDefault();
  currentTimeRange = range;
  const lbl = document.getElementById('timeRangeLabel');
  if (lbl) lbl.textContent = timeRangeLabels[range] || range;
  document.querySelectorAll('#timeRangeDropdown ~ .dropdown-menu .dropdown-item').forEach(item => {
    const oc = item.getAttribute('onclick') || '';
    item.classList.toggle('active', oc.includes(`'${range}'`));
  });
  const url = new URL(window.location.href);
  url.searchParams.set('compliance_type', '{{ $complianceType }}');
  url.searchParams.set('time_range', currentTimeRange);
  window.location.href = url.toString();
}

function resetFilters() {
  const url = new URL(window.location.href);
  url.searchParams.set('compliance_type', '{{ $complianceType }}');
  url.searchParams.set('time_range', '24h');
  window.location.href = url.toString();
}

// ── GridStack ──────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'comp-top-groups',       x: 0, y: 0, w: 4, h: 8 },
    { id: 'comp-top-rules',        x: 4, y: 0, w: 4, h: 8 },
    { id: 'comp-controls',         x: 8, y: 0, w: 4, h: 8 },
    { id: 'comp-top-requirements', x: 0, y: 8, w: 4, h: 8 },
    { id: 'comp-requirements-bar', x: 4, y: 8, w: 5, h: 8 },
    { id: 'comp-rule-level',       x: 9, y: 8, w: 3, h: 8 },
  ];

  const grid = GridStack.init({
    column: 12, cellHeight: 60, margin: 8, float: false, staticGrid: true,
    resizable: { handles: 'se' },
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
    });
  }

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

  grid.on('resizestop', () => Object.values(chartInstances).forEach(c => c?.resize?.()));

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
      body: JSON.stringify({ layout, page: 'compliance' })
    }).then(r => r.json()).then(d => { if (d.success) { exitEdit(); gsShowSavedToast(); } });
  });

  document.getElementById('gs-reset').addEventListener('click', () => {
    [...hiddenCards].forEach(id => {
      hiddenCards.delete(id); delete hiddenPositions[id];
      const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (el) el.classList.remove('gs-card-hidden');
    });
    grid.load(DEFAULT_LAYOUT);
    Object.values(chartInstances).forEach(c => c?.resize?.());
  });

  document.getElementById('gs-cancel').addEventListener('click', () => { exitEdit(); location.reload(); });
})();
</script>
@endpush
