@extends('layouts.wazuh')

@section('title', 'Integrity Monitoring - One For All')

@section('content')

@if(!$agent)
<div class="container-fluid py-5">
  <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
    <i class="mdi mdi-alert-circle-outline display-4"></i>
    <div>
      <h5 class="alert-heading mb-1">Agent Not Found</h5>
      <p class="mb-0">Unable to load agent details. The agent may no longer exist or access has been denied.</p>
      <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-danger mt-2">
        <i class="mdi mdi-arrow-left me-1"></i> Back to Agents
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
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.detail', $agent->id_agent) }}">
          <span class="mdi mdi-home"></span> Details
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.security-events', $agent->id_agent) }}">
          <span class="mdi mdi-format-list-bulleted"></span> Security events
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small active" href="{{ route('agent.integrity-monitoring', $agent->id_agent) }}">
          <span class="mdi mdi-shield"></span> Integrity monitoring
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.sca', $agent->id_agent) }}">
          <span class="mdi mdi-clock-outline"></span> SCA
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.vulnerabilities', $agent->id_agent) }}">
          <span class="mdi mdi-bug"></span> Vulnerabilities
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.mitre-attack', $agent->id_agent) }}">
          <span class="mdi mdi-sword-cross"></span> MITRE ATT&amp;CK
        </a>
      </li>
    </ul>
    <div class="ms-auto d-flex gap-2 flex-shrink-0 py-1">
      <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent') }}" title="Back to Agents List">
        <span class="mdi mdi-arrow-left"></span> Back
      </a>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack.min.css"/>
<style>
  .grid-stack { background: transparent; }
  .grid-stack-item-content { overflow: auto; }

  body.gs-edit-mode .grid-stack-item-content {
    outline: 2px dashed rgba(75,73,172,0.4);
    outline-offset: -2px;
  }
  body.gs-edit-mode .grid-stack {
    background-image: linear-gradient(rgba(75,73,172,.04) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(75,73,172,.04) 1px, transparent 1px);
    background-size: calc(100% / 12) 60px;
  }

  #gs-fab {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  }
  #gs-fab-main {
    width: 48px; height: 48px; border-radius: 50%;
    background: #4B49AC; color: #fff; border: none;
    box-shadow: 0 4px 14px rgba(75,73,172,.45);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; cursor: pointer; transition: background .2s, transform .15s;
  }
  #gs-fab-main:hover { background: #3b3a8c; transform: scale(1.06); }
  #gs-fab-main.active { background: #e74c3c; }

  #gs-edit-toolbar {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
    z-index: 9998; display: none; align-items: center; gap: 8px;
    background: rgba(255,255,255,.97); padding: 8px 16px;
    border-radius: 32px; box-shadow: 0 4px 20px rgba(0,0,0,.18); white-space: nowrap;
  }
  #gs-edit-toolbar.visible { display: flex; }

  .gs-tb-btn { padding: 6px 18px; border-radius: 20px; border: none; font-size: 13px; font-weight: 500; cursor: pointer; transition: opacity .15s; }
  .gs-tb-btn:hover { opacity: .82; }
  .gs-tb-btn-save   { background: #27ae60; color: #fff; }
  .gs-tb-btn-reset  { background: #f39c12; color: #fff; }
  .gs-tb-btn-cancel { background: #f0f0f0; color: #333; }

  .gs-card { height: 100%; display: flex; flex-direction: column; }
  .gs-card .card-body { flex: 1; overflow: auto; }

  #integrity-monitoring-grid [gs-id="fim-timerange"] .grid-stack-item-content,
  #integrity-monitoring-grid [gs-id="fim-timerange"] .card,
  #integrity-monitoring-grid [gs-id="fim-timerange"] .card-body {
    overflow: visible !important;
  }

  /* ── Hide card button ── */
  .gs-hide-btn {
    display: none;
    position: absolute;
    top: 10px; right: 10px;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(231,76,60,0.1);
    border: 1px solid rgba(231,76,60,0.35);
    color: #e74c3c; font-size: 13px; cursor: pointer;
    align-items: center; justify-content: center;
    z-index: 100; transition: background .15s, color .15s, border-color .15s; line-height: 1;
  }
  .gs-hide-btn:hover { background: #e74c3c; color: #fff; }
  body.gs-edit-mode .gs-hide-btn { display: flex; }

  .gs-card-hidden .grid-stack-item-content { opacity: 0.25; pointer-events: none; filter: grayscale(0.4); }
  .gs-card-hidden .gs-hide-btn { pointer-events: all; background: rgba(39,174,96,0.1); border-color: rgba(39,174,96,0.35); color: #27ae60; }
  .gs-card-hidden .gs-hide-btn:hover { background: #27ae60; color: #fff; }

  @media (max-width: 767px) {
    #gs-fab, #gs-edit-toolbar { display: none !important; }
  }
</style>

<div class="grid-stack" id="integrity-monitoring-grid">

  {{-- METRICS --}}
  <div class="grid-stack-item" gs-id="fim-metrics" data-label="Metrics" gs-x="0" gs-y="0" gs-w="9" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="row g-2 h-100 align-items-center">
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Total events</div>
              <div class="display-6 fw-bold text-primary">{{ number_format($fimSummary['total']) }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Added</div>
              <div class="display-6 fw-bold text-success">{{ number_format($fimSummary['added']) }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Modified</div>
              <div class="display-6 fw-bold text-warning">{{ number_format($fimSummary['modified']) }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Deleted</div>
              <div class="display-6 fw-bold text-danger">{{ number_format($fimSummary['deleted']) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TIME RANGE --}}
  <div class="grid-stack-item" gs-id="fim-timerange" data-label="Time Range" gs-x="9" gs-y="0" gs-w="3" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Time Range</span>
        </div>
        <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown"
              data-bs-toggle="dropdown" aria-expanded="false">
              <span class="mdi mdi-calendar-outline me-1"></span>
              <span id="timeRangeLabel">{{ ['15m'=>'Last 15 minutes','30m'=>'Last 30 minutes','1h'=>'Last 1 hour','24h'=>'Last 24 hours','7d'=>'Last 7 days','30d'=>'Last 30 days','90d'=>'Last 90 days','1y'=>'Last 1 year','today'=>'Today','week'=>'This week'][$timeRange] ?? 'Last 24 hours' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="timeRangeDropdown">
              <li><a class="dropdown-item {{ $timeRange === '15m'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('15m',  event)">Last 15 minutes</a></li>
              <li><a class="dropdown-item {{ $timeRange === '30m'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('30m',  event)">Last 30 minutes</a></li>
              <li><a class="dropdown-item {{ $timeRange === '1h'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('1h',   event)">Last 1 hour</a></li>
              <li><a class="dropdown-item {{ $timeRange === '24h'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('24h',  event)">Last 24 hours</a></li>
              <li><a class="dropdown-item {{ $timeRange === '7d'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('7d',   event)">Last 7 days</a></li>
              <li><a class="dropdown-item {{ $timeRange === '30d'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('30d',  event)">Last 30 days</a></li>
              <li><a class="dropdown-item {{ $timeRange === '90d'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('90d',  event)">Last 90 days</a></li>
              <li><a class="dropdown-item {{ $timeRange === '1y'   ? 'active' : '' }}" href="#" onclick="updateTimeRange('1y',   event)">Last 1 year</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item {{ $timeRange === 'today' ? 'active' : '' }}" href="#" onclick="updateTimeRange('today', event)">Today</a></li>
              <li><a class="dropdown-item {{ $timeRange === 'week'  ? 'active' : '' }}" href="#" onclick="updateTimeRange('week',  event)">This week</a></li>
            </ul>
          </div>
          <button class="btn btn-outline-secondary btn-sm" onclick="refreshData()" title="Refresh data">
            <span class="mdi mdi-refresh me-1"></span> Refresh
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- FIM EVOLUTION --}}
  <div class="grid-stack-item" gs-id="fim-evolution" data-label="Events Evolution" gs-x="0" gs-y="3" gs-w="6" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Events evolution</span>
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
  <div class="grid-stack-item" gs-id="fim-top-rules" data-label="Top 5 Rule Descriptions" gs-x="6" gs-y="3" gs-w="6" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Top 5 rule descriptions</span>
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
  <div class="grid-stack-item" gs-id="fim-top-modified" data-label="Top 5 Modified Files" gs-x="0" gs-y="12" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Top 5 modified files</span>
        </div>
        <div class="card-body p-0">
          @if(count($fimTopModified) > 0)
          <ul class="list-group list-group-flush">
            @foreach($fimTopModified as $i => $file)
            <li class="list-group-item d-flex justify-content-between align-items-start px-3 py-2">
              <div class="d-flex align-items-center gap-2 overflow-hidden">
                <span class="badge bg-secondary rounded-pill">{{ $i + 1 }}</span>
                <span class="small text-truncate" style="max-width:200px;" title="{{ $file['path'] }}">{{ $file['path'] }}</span>
              </div>
              <span class="badge bg-warning text-dark ms-2 flex-shrink-0">{{ number_format($file['count']) }}</span>
            </li>
            @endforeach
          </ul>
          @else
          <div class="d-flex align-items-center justify-content-center h-100 text-muted small">No data available</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 DELETED FILES --}}
  <div class="grid-stack-item" gs-id="fim-top-deleted" data-label="Top 5 Deleted Files" gs-x="4" gs-y="12" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Top 5 deleted files</span>
        </div>
        <div class="card-body p-0">
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
          <div class="d-flex align-items-center justify-content-center h-100 text-muted small">No data available</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- TOP 5 ADDED FILES --}}
  <div class="grid-stack-item" gs-id="fim-top-added" data-label="Top 5 Added Files" gs-x="8" gs-y="12" gs-w="4" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Top 5 added files</span>
        </div>
        <div class="card-body p-0">
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
          <div class="d-flex align-items-center justify-content-center h-100 text-muted small">No data available</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FIM EVENTS TABLE --}}
  <div class="grid-stack-item" gs-id="fim-events-table" data-label="FIM Events" gs-x="0" gs-y="20" gs-w="12" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">FIM events</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:15%">Timestamp</th>
                  <th style="width:35%">Path</th>
                  <th style="width:10%">Action</th>
                  <th style="width:30%">Rule</th>
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
                <tr>
                  <td colspan="5" class="text-center text-muted py-3 small">No FIM events in this time range</td>
                </tr>
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
            <span class="text-muted me-1">Rows:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $ppUrl($pp) }}"
                 class="btn btn-sm py-0 px-2 {{ $perPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $pp }}</a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $from }}–{{ $to }} of {{ number_format($totalEvents) }}</span>
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
  <button id="gs-save"   class="gs-tb-btn gs-tb-btn-save">Save layout</button>
  <button id="gs-reset"  class="gs-tb-btn gs-tb-btn-reset">Reset</button>
  <button id="gs-cancel" class="gs-tb-btn gs-tb-btn-cancel">Cancel</button>
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
  '15m': 'Last 15 minutes', '30m': 'Last 30 minutes', '1h': 'Last 1 hour',
  '24h': 'Last 24 hours',   '7d':  'Last 7 days',      '30d': 'Last 30 days',
  '90d': 'Last 90 days',    '1y':  'Last 1 year',
  'today': 'Today',         'week': 'This week',
};

function updateTimeRange(timeRange, event) {
  if (event) event.preventDefault();
  currentTimeRange = timeRange;
  document.getElementById('timeRangeLabel').textContent = timeRangeLabels[timeRange] || 'Select time range';

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

function refreshData() {
  const url = new URL(window.location.href);
  url.searchParams.set('time_range', currentTimeRange);
  window.location.href = url.toString();
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
  if (el) el.innerHTML = `<div style="display:flex;align-items:center;justify-content:center;height:${height};background:#f8f9fa;border-radius:4px;color:#6c757d;font-size:14px;font-weight:500;">No data available</div>`;
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

const fimEndpoint = '{{ route("agent.fim.events", $agent->id_agent ?? "") }}';

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
    }).join('') : '<tr><td colspan="5" class="text-center text-muted py-3 small">No FIM events in this time range</td></tr>';
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
    if (fimEvolutionChartInstance) fimEvolutionChartInstance.resize();
    if (fimTopRulesChartInstance)  fimTopRulesChartInstance.resize();
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
      body: JSON.stringify({ layout, page: 'integrity-monitoring' })
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
    if (fimEvolutionChartInstance) fimEvolutionChartInstance.resize();
    if (fimTopRulesChartInstance)  fimTopRulesChartInstance.resize();
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });
})();
</script>
@endpush
