@extends('layouts.wazuh')

@section('title', 'SCA - One For All')

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
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.integrity-monitoring', $agent->id_agent) }}">
          <span class="mdi mdi-shield"></span> Integrity monitoring
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small active" href="{{ route('agent.sca', $agent->id_agent) }}">
          <span class="mdi mdi-clock-outline"></span> SCA
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-light px-3 py-2 d-flex align-items-center gap-1 small" href="{{ route('agent.vulnerabilities', $agent->id_agent) }}">
          <span class="mdi mdi-bug"></span> Vulnerabilities
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

  .policy-row { cursor: pointer; transition: background .15s; }
  .policy-row:hover { background: rgba(75,73,172,.06); }
  .policy-row.active-policy { background: rgba(75,73,172,.12); }

  .score-bar { height: 6px; border-radius: 3px; background: #e9ecef; overflow: hidden; }
  .score-bar-fill { height: 100%; border-radius: 3px; transition: width .4s; }

  @media (max-width: 767px) {
    #gs-fab, #gs-edit-toolbar { display: none !important; }
  }
</style>

<div class="grid-stack" id="sca-grid">

  {{-- METRICS --}}
  <div class="grid-stack-item" gs-id="sca-metrics" gs-x="0" gs-y="0" gs-w="12" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="row g-2 h-100 align-items-center">
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Policies scanned</div>
              <div class="display-6 fw-bold text-primary">{{ count($policies) }}</div>
            </div>
            <div class="col-3 text-center">
              <div class="text-muted fw-semibold small mb-2">Average score</div>
              <div class="display-6 fw-bold {{ $avgScore >= 75 ? 'text-success' : ($avgScore >= 50 ? 'text-warning' : 'text-danger') }}">
                {{ $avgScore }}%
              </div>
            </div>
            <div class="col-2 text-center">
              <div class="text-muted fw-semibold small mb-2">Total passed</div>
              <div class="display-6 fw-bold text-success">{{ number_format($totalPass) }}</div>
            </div>
            <div class="col-2 text-center">
              <div class="text-muted fw-semibold small mb-2">Total failed</div>
              <div class="display-6 fw-bold text-danger">{{ number_format($totalFail) }}</div>
            </div>
            <div class="col-2 text-center">
              <div class="text-muted fw-semibold small mb-2">Not applicable</div>
              <div class="display-6 fw-bold text-secondary">{{ number_format($totalNA) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- POLICIES TABLE --}}
  <div class="grid-stack-item" gs-id="sca-policies" gs-x="0" gs-y="3" gs-w="7" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2">
          <span class="fw-semibold small">SCA policies</span>
        </div>
        <div class="card-body p-0">
          @if(count($policies) > 0)
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:45%">Policy</th>
                  <th style="width:15%" class="text-center">Score</th>
                  <th style="width:12%" class="text-center">Pass</th>
                  <th style="width:12%" class="text-center">Fail</th>
                  <th style="width:16%">Last scan</th>
                </tr>
              </thead>
              <tbody>
                @foreach($policies as $policy)
                @php
                  $isActive  = $policy['policy_id'] === $policyId;
                  $score     = $policy['score'] ?? 0;
                  $scoreColor = $score >= 75 ? '#20c997' : ($score >= 50 ? '#ffc107' : '#dc3545');
                  $selectUrl  = '?' . http_build_query(array_merge(request()->query(), ['policy_id' => $policy['policy_id'], 'page' => 1, 'result' => '']));
                @endphp
                <tr class="policy-row {{ $isActive ? 'active-policy' : '' }}"
                    onclick="loadScaData('{{ $policy['policy_id'] }}', null, 1, {{ $perPage }}); return false;">
                  <td>
                    <div class="fw-semibold" title="{{ $policy['name'] }}">
                      {{ Str::limit($policy['name'], 45) }}
                    </div>
                    <div class="text-muted" style="font-size:10px;">{{ $policy['policy_id'] }}</div>
                  </td>
                  <td class="text-center align-middle">
                    <div class="fw-bold" style="color:{{ $scoreColor }}">{{ $score }}%</div>
                    <div class="score-bar mt-1">
                      <div class="score-bar-fill" style="width:{{ $score }}%;background:{{ $scoreColor }};"></div>
                    </div>
                  </td>
                  <td class="text-center align-middle">
                    <span class="badge bg-success">{{ $policy['pass'] ?? 0 }}</span>
                  </td>
                  <td class="text-center align-middle">
                    <span class="badge bg-danger">{{ $policy['fail'] ?? 0 }}</span>
                  </td>
                  <td class="align-middle text-muted">
                    {{ isset($policy['end_scan']) ? \Carbon\Carbon::parse($policy['end_scan'])->format('Y-m-d H:i') : 'N/A' }}
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
            <div class="text-center">
              <span class="mdi mdi-shield-off-outline d-block fs-1 mb-2"></span>
              No SCA policies found for this agent
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- SCORE CHART for selected policy --}}
  <div class="grid-stack-item" gs-id="sca-score-chart" gs-x="7" gs-y="3" gs-w="5" gs-h="10">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
          <span class="fw-semibold small">
            {{ $selectedPolicy ? Str::limit($selectedPolicy['name'], 35) : 'Policy score' }}
          </span>
          @if($selectedPolicy)
          <span class="badge
            {{ ($selectedPolicy['score'] ?? 0) >= 75 ? 'bg-success' : (($selectedPolicy['score'] ?? 0) >= 50 ? 'bg-warning text-dark' : 'bg-danger') }}">
            {{ $selectedPolicy['score'] ?? 0 }}%
          </span>
          @endif
        </div>
        <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
          @if($selectedPolicy)
          <div id="scaScoreChartContainer" style="position:relative;width:100%;max-width:260px;height:200px;">
            <canvas id="scaScoreChart"></canvas>
          </div>
          <div class="d-flex gap-3 mt-2 small">
            <span><span class="mdi mdi-circle text-success"></span> Passed: <strong>{{ $selectedPolicy['pass'] ?? 0 }}</strong></span>
            <span><span class="mdi mdi-circle text-danger"></span> Failed: <strong>{{ $selectedPolicy['fail'] ?? 0 }}</strong></span>
            <span><span class="mdi mdi-circle text-secondary"></span> N/A: <strong>{{ $selectedPolicy['not_applicable'] ?? 0 }}</strong></span>
          </div>
          @if(isset($selectedPolicy['description']))
          <p class="text-muted small mt-2 text-center px-2" style="font-size:10px;">{{ Str::limit($selectedPolicy['description'], 120) }}</p>
          @endif
          @else
          <div class="text-muted small text-center">
            <span class="mdi mdi-chart-donut d-block fs-1 mb-2"></span>
            Select a policy to view score
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- SCA CHECKS TABLE --}}
  <div class="grid-stack-item" gs-id="sca-checks" gs-x="0" gs-y="13" gs-w="12" gs-h="12">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold small">SCA checks</span>
            @if($selectedPolicy)
            <span class="badge bg-secondary small">{{ $selectedPolicy['name'] ?? $policyId }}</span>
            @endif
          </div>
          {{-- Result filter --}}
          @php
            $filterBase = array_merge(request()->query(), ['page' => 1]);
          @endphp
          <div id="sca-result-filter" class="btn-group btn-group-sm" role="group">
            <a href="?{{ http_build_query(array_merge($filterBase, ['result' => ''])) }}"
               class="btn {{ !$resultFilter ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
            <a href="?{{ http_build_query(array_merge($filterBase, ['result' => 'passed'])) }}"
               class="btn {{ $resultFilter === 'passed' ? 'btn-success' : 'btn-outline-secondary' }}">Passed</a>
            <a href="?{{ http_build_query(array_merge($filterBase, ['result' => 'failed'])) }}"
               class="btn {{ $resultFilter === 'failed' ? 'btn-danger' : 'btn-outline-secondary' }}">Failed</a>
            <a href="?{{ http_build_query(array_merge($filterBase, ['result' => 'not_applicable'])) }}"
               class="btn {{ $resultFilter === 'not_applicable' ? 'btn-secondary' : 'btn-outline-secondary' }}">N/A</a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0" style="font-size:11px;">
              <thead class="table-light">
                <tr>
                  <th style="width:5%">#</th>
                  <th style="width:45%">Title</th>
                  <th style="width:10%">Result</th>
                  <th style="width:40%">Reason / Remediation</th>
                </tr>
              </thead>
              <tbody id="sca-tbody">
                @forelse($checks as $check)
                @php
                  $resultColor = match($check['result'] ?? 'not_applicable') {
                    'passed'         => 'success',
                    'failed'         => 'danger',
                    'not_applicable' => 'secondary',
                    default          => 'secondary',
                  };
                  $detail = $check['reason'] ?? $check['remediation'] ?? $check['description'] ?? '';
                @endphp
                <tr>
                  <td class="text-muted">{{ $check['id'] ?? '' }}</td>
                  <td title="{{ $check['title'] ?? '' }}">
                    {{ Str::limit($check['title'] ?? 'N/A', 80) }}
                  </td>
                  <td><span class="badge bg-{{ $resultColor }}">{{ $check['result'] ?? 'n/a' }}</span></td>
                  <td class="text-muted" title="{{ $detail }}">{{ Str::limit($detail, 100) }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-3 small">
                    @if(!$policyId) Select a policy to view checks
                    @else No checks found{{ $resultFilter ? ' for filter: ' . $resultFilter : '' }}
                    @endif
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @php
          $totalPages = $totalChecks > 0 ? (int) ceil($totalChecks / $perPage) : 1;
          $from       = $totalChecks > 0 ? ($page - 1) * $perPage + 1 : 0;
          $to         = min($page * $perPage, $totalChecks);
          $baseQuery  = array_merge(request()->query(), []);
          $pageUrl    = fn($p)  => '?' . http_build_query(array_merge($baseQuery, ['page' => $p,  'per_page' => $perPage]));
          $ppUrl      = fn($pp) => '?' . http_build_query(array_merge($baseQuery, ['page' => 1,   'per_page' => $pp]));
          $window     = collect(range(max(1, $page - 2), min($totalPages, $page + 2)));
        @endphp
        <div id="sca-footer" class="card-footer d-flex justify-content-between align-items-center py-2 small flex-wrap gap-2">
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-1">Rows:</span>
            @foreach([10, 25, 50] as $pp)
              <a href="{{ $ppUrl($pp) }}"
                 class="btn btn-sm py-0 px-2 {{ $perPage === $pp ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $pp }}</a>
            @endforeach
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="text-muted me-2">{{ $from }}–{{ $to }} of {{ number_format($totalChecks) }}</span>
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
const scaData = {
  selectedPolicy: @json($selectedPolicy ?? null),
};

let scaScoreChartInstance = null;

function initializeCharts() {
  if (typeof Chart === 'undefined') { setTimeout(initializeCharts, 100); return; }
  if (scaScoreChartInstance) { scaScoreChartInstance.destroy(); scaScoreChartInstance = null; }

  if (scaData.selectedPolicy) {
    const pass = scaData.selectedPolicy.pass          || 0;
    const fail = scaData.selectedPolicy.fail          || 0;
    const na   = scaData.selectedPolicy.not_applicable || 0;

    if (pass + fail + na > 0) {
      const ctx = document.getElementById('scaScoreChart')?.getContext('2d');
      if (ctx) {
        scaScoreChartInstance = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Passed', 'Failed', 'Not applicable'],
            datasets: [{
              data: [pass, fail, na],
              backgroundColor: ['#20c997', '#dc3545', '#adb5bd'],
              borderWidth: 2,
              borderColor: '#fff',
            }],
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
              legend: { display: true, position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10, padding: 8 } },
            },
          },
        });
      }
    }
  }
}

// ── AJAX helpers ───────────────────────────────────────────────────────────────
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

const scaEndpoint = '{{ route("agent.sca.checks", $agent->id_agent ?? "") }}';
let currentPolicyId    = '{{ $policyId ?? "" }}';
let currentResultFilter = '{{ $resultFilter ?? "" }}';

async function loadScaData(policyId, resultFilter, page, perPage) {
  currentPolicyId     = policyId     ?? currentPolicyId;
  currentResultFilter = resultFilter !== null ? (resultFilter ?? '') : currentResultFilter;
  page    = page    || 1;
  perPage = perPage || 10;

  const params = new URLSearchParams({ policy_id: currentPolicyId, page, per_page: perPage });
  if (currentResultFilter) params.set('result', currentResultFilter);

  // Mark active policy row
  document.querySelectorAll('.policy-row').forEach(row => {
    row.classList.toggle('active-policy', row.getAttribute('data-policy-id') === currentPolicyId);
  });

  try {
    const res  = await fetch(`${scaEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return;
    const json = await res.json();

    // Update checks table
    const tbody = document.getElementById('sca-tbody');
    tbody.innerHTML = json.checks.length ? json.checks.map(r => {
      const rc  = {'passed':'success','failed':'danger','not_applicable':'secondary'}[r.result] || 'secondary';
      const detail = r.reason || r.remediation || r.description || '';
      return `<tr>
        <td class="text-muted">${escHtml(r.id||'')}</td>
        <td title="${escHtml(r.title||'')}">${escHtml((r.title||'N/A').substring(0,80))}</td>
        <td><span class="badge bg-${rc}">${escHtml(r.result||'n/a')}</span></td>
        <td class="text-muted" title="${escHtml(detail)}">${escHtml(detail.substring(0,100))}</td>
      </tr>`;
    }).join('') : `<tr><td colspan="4" class="text-center text-muted py-3 small">${currentPolicyId ? 'No checks found' + (currentResultFilter ? ' for filter: ' + currentResultFilter : '') : 'Select a policy to view checks'}</td></tr>`;

    renderPagination('sca-footer', json.total, json.page, json.perPage, 'loadScaPage');

    // Update score chart if policy changed
    if (json.selectedPolicy && scaScoreChartInstance) {
      const p = json.selectedPolicy;
      scaScoreChartInstance.data.datasets[0].data = [p.pass||0, p.fail||0, p.not_applicable||0];
      scaScoreChartInstance.update();
    }

    // Update result filter active state
    document.querySelectorAll('#sca-result-filter a').forEach(a => {
      const href = new URL(a.href, location.href);
      const r = href.searchParams.get('result') || '';
      a.className = a.className.replace(/btn-\w+/g, '').trim();
      a.classList.add('btn', r === (currentResultFilter||'')
        ? (r === 'passed' ? 'btn-success' : r === 'failed' ? 'btn-danger' : r === 'not_applicable' ? 'btn-secondary' : 'btn-primary')
        : 'btn-outline-secondary');
    });

  } catch(e) { console.error('loadScaData failed', e); }
}

function loadScaPage(page, perPage) {
  loadScaData(currentPolicyId, currentResultFilter || null, page, perPage);
}

document.addEventListener('DOMContentLoaded', () => {
  // Add data-policy-id to policy rows for active highlighting
  document.querySelectorAll('.policy-row').forEach(row => {
    const match = (row.getAttribute('onclick') || '').match(/'([^']+)'/);
    if (match) row.setAttribute('data-policy-id', match[1]);
  });

  // Intercept result filter link clicks
  document.getElementById('sca-result-filter')?.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    e.preventDefault();
    const u = new URL(a.href, location.href);
    const r = u.searchParams.get('result') || null;
    currentResultFilter = r || '';
    loadScaData(currentPolicyId, r, 1, 10);
  });

  // Intercept checks pagination link clicks
  document.getElementById('sca-footer')?.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    e.preventDefault();
    const u = new URL(a.href, location.href);
    loadScaPage(parseInt(u.searchParams.get('page')||1), parseInt(u.searchParams.get('per_page')||10));
  });
});

document.addEventListener('DOMContentLoaded', initializeCharts);

// ── GridStack ──────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'sca-metrics',     x: 0,  y: 0,  w: 12, h: 3  },
    { id: 'sca-policies',    x: 0,  y: 3,  w: 7,  h: 10 },
    { id: 'sca-score-chart', x: 7,  y: 3,  w: 5,  h: 10 },
    { id: 'sca-checks',      x: 0,  y: 13, w: 12, h: 12 },
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
    if (scaScoreChartInstance) scaScoreChartInstance.resize();
  });

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
      body: JSON.stringify({ layout, page: 'sca' })
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
