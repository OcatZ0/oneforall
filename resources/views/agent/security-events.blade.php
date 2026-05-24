@extends('layouts.wazuh')

@section('title', 'Security Events - One For All')

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
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small active" href="{{ route('agent.security-events', $agent->id_agent) }}">
          <span class="mdi mdi-format-list-bulleted"></span> Security events
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.integrity-monitoring', $agent->id_agent) }}">
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
          <span class="mdi mdi-target"></span> MITRE ATT&amp;CK
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="#">
          More <span class="mdi mdi-chevron-down"></span>
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
    outline: 2px dashed rgba(75, 73, 172, 0.4);
    outline-offset: -2px;
  }
  body.gs-edit-mode .grid-stack {
    background-image: linear-gradient(rgba(75,73,172,.04) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(75,73,172,.04) 1px, transparent 1px);
    background-size: calc(100% / 12) 60px;
  }

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

  .gs-tb-btn { padding: 6px 18px; border-radius: 20px; border: none; font-size: 13px; font-weight: 500; cursor: pointer; transition: opacity .15s; }
  .gs-tb-btn:hover { opacity: .82; }
  .gs-tb-btn-save   { background: #27ae60; color: #fff; }
  .gs-tb-btn-reset  { background: #f39c12; color: #fff; }
  .gs-tb-btn-cancel { background: #f0f0f0; color: #333; }

  .gs-card { height: 100%; display: flex; flex-direction: column; }
  .gs-card .card-body { flex: 1; overflow: auto; }

  /* Allow dropdown to escape overflow clipping in the timerange card */
  #security-events-grid [gs-id="se-timerange"] .grid-stack-item-content,
  #security-events-grid [gs-id="se-timerange"] .card,
  #security-events-grid [gs-id="se-timerange"] .card-body {
    overflow: visible !important;
  }

  @media (max-width: 767px) {
    #gs-fab, #gs-edit-toolbar { display: none !important; }
  }
</style>

<div class="grid-stack" id="security-events-grid">

  {{-- METRICS --}}
  <div class="grid-stack-item" gs-id="se-metrics" gs-x="0" gs-y="0" gs-w="9" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="row g-2 h-100 align-items-center">
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Total</div>
              <div class="display-6 fw-bold text-primary" id="metricTotal">{{ $metrics['total'] ?? 0 }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Level 12 or above</div>
              <div class="display-6 fw-bold text-danger" id="metricLevel12">{{ $metrics['level12'] ?? 0 }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Authentication failure</div>
              <div class="display-6 fw-bold text-warning" id="metricAuthFailure">{{ $metrics['auth_failure'] ?? 0 }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Authentication success</div>
              <div class="display-6 fw-bold text-success" id="metricAuthSuccess">{{ $metrics['auth_success'] ?? 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- TIME RANGE --}}
  <div class="grid-stack-item" gs-id="se-timerange" gs-x="9" gs-y="0" gs-w="3" gs-h="3">
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

  {{-- ALERT GROUPS EVOLUTION --}}
  <div class="grid-stack-item" gs-id="se-alert-groups" gs-x="0" gs-y="3" gs-w="6" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Alert groups evolution</span>
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
  <div class="grid-stack-item" gs-id="se-alerts" gs-x="6" gs-y="3" gs-w="6" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Alerts</span>
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
  <div class="grid-stack-item" gs-id="se-top-alerts" gs-x="0" gs-y="11" gs-w="4" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Top 5 alerts</span>
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
  <div class="grid-stack-item" gs-id="se-top-rule-groups" gs-x="4" gs-y="11" gs-w="4" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Top 5 rule groups</span>
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
  <div class="grid-stack-item" gs-id="se-top-pcidss" gs-x="8" gs-y="11" gs-w="4" gs-h="9">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Top 5 PCI DSS Requirements</span>
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
  <div class="grid-stack-item" gs-id="se-alerts-table" gs-x="0" gs-y="20" gs-w="12" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Alerts summary</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:20%">Time <span class="mdi mdi-arrow-up-down"></span></th>
                  <th style="width:15%">Rule ID</th>
                  <th style="width:35%">Description</th>
                  <th style="width:10%">Level</th>
                  <th style="width:10%">Count</th>
                  <th style="width:10%">Group</th>
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
                  <td colspan="6" class="text-center text-muted py-3">
                    <i class="mdi mdi-information-outline me-1"></i>No alerts found in the selected time range
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
            <span class="text-muted">Rows per page:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $ppUrl($pp) }}"
                 class="btn btn-sm {{ $perPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-2">
                {{ $pp }}
              </a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $from }}–{{ $to }} of {{ number_format($totalAlerts) }}</span>
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
  <div class="grid-stack-item" gs-id="se-groups-table" gs-x="0" gs-y="30" gs-w="12" gs-h="8">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">Groups summary</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:70%">Group</th>
                  <th style="width:30%">Count</th>
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
                  <td colspan="2" class="text-center text-muted py-3 small">No group data available</td>
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
            <span class="text-muted me-1">Rows:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $gPpUrl($pp) }}"
                 class="btn btn-sm py-0 px-2 {{ $groupsPerPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $pp }}</a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $gFrom }}–{{ $gTo }} of {{ number_format($totalGroups) }}</span>
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
  <button class="gs-tb-btn gs-tb-btn-save"   id="gs-save">  <i class="mdi mdi-content-save me-1"></i>Save</button>
  <button class="gs-tb-btn gs-tb-btn-reset"  id="gs-reset"> <i class="mdi mdi-restore me-1"></i>Reset</button>
  <button class="gs-tb-btn gs-tb-btn-cancel" id="gs-cancel"><i class="mdi mdi-close me-1"></i>Cancel</button>
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
const agentId        = '{{ $agent->id_agent ?? "" }}';

const securityData = {
  alertGroupsEvolution:   @json($alertGroupsEvolution   ?? ['labels' => [], 'datasets' => []]),
  alertsEvolutionByLevel: @json($alertsEvolutionByLevel ?? ['labels' => [], 'datasets' => []]),
  topAlerts:     @json($topAlerts     ?? ['labels' => [], 'data' => []]),
  topRuleGroups: @json($topRuleGroups ?? ['labels' => [], 'data' => []]),
  topPCIDSS:     @json($topPCIDSS     ?? ['labels' => [], 'data' => []]),
};

const timeRangeLabels = {
  '15m':  'Last 15 minutes',
  '30m':  'Last 30 minutes',
  '1h':   'Last 1 hour',
  '24h':  'Last 24 hours',
  '7d':   'Last 7 days',
  '30d':  'Last 30 days',
  '90d':  'Last 90 days',
  '1y':   'Last 1 year',
  'today':'Today',
  'week': 'This week'
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

const alertsEndpoint = '{{ route("agent.se.alerts", $agent->id_agent ?? "") }}';
const groupsEndpoint = '{{ route("agent.se.groups", $agent->id_agent ?? "") }}';

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
    }).join('') : '<tr><td colspan="6" class="text-center text-muted py-3">No alerts found in the selected time range</td></tr>';
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
    ).join('') : '<tr><td colspan="2" class="text-center text-muted py-3 small">No group data available</td></tr>';
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
    { id: 'se-metrics',       x: 0, y: 0,  w: 9,  h: 3  },
    { id: 'se-timerange',     x: 9, y: 0,  w: 3,  h: 3  },
    { id: 'se-alert-groups',  x: 0, y: 3,  w: 6,  h: 8  },
    { id: 'se-alerts',        x: 6, y: 3,  w: 6,  h: 8  },
    { id: 'se-top-alerts',    x: 0, y: 11, w: 4,  h: 9  },
    { id: 'se-top-rule-groups', x: 4, y: 11, w: 4, h: 9 },
    { id: 'se-top-pcidss',    x: 8, y: 11, w: 4,  h: 9  },
    { id: 'se-alerts-table',  x: 0, y: 20, w: 12, h: 10 },
    { id: 'se-groups-table',  x: 0, y: 30, w: 12, h: 8  },
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

  const savedLayout = @json($savedLayout ?? null);
  if (savedLayout && Array.isArray(savedLayout)) {
    grid.load(savedLayout);
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
    document.body.classList.add('gs-edit-mode');
    fabMain.classList.add('active');
    fabIcon.className = 'mdi mdi-pencil-off';
    toolbar.classList.add('visible');
  }

  function exitEdit() {
    editMode = false;
    grid.setStatic(true);
    document.body.classList.remove('gs-edit-mode');
    fabMain.classList.remove('active');
    fabIcon.className = 'mdi mdi-pencil';
    toolbar.classList.remove('visible');
  }

  fabMain.addEventListener('click', () => editMode ? exitEdit() : enterEdit());

  document.getElementById('gs-save').addEventListener('click', () => {
    const layout = grid.save(false);
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout, page: 'security-events' })
    })
    .then(r => r.json())
    .then(d => { if (d.success) exitEdit(); });
  });

  document.getElementById('gs-reset').addEventListener('click', () => {
    grid.load(DEFAULT_LAYOUT);
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });
})();
</script>
@endpush
