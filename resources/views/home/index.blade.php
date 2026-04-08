@extends('layouts.app')

@section('title', 'Dashboard - One For All')

@section('content')

{{-- Agent Status Cards --}}
<div class="row">
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <p class="card-title mb-0">Active Agents</p>
          <i class="mdi mdi-server-network text-success icon-lg"></i>
        </div>
        <h2 class="font-weight-bold mb-1">{{ $agentStats['active'] }}</h2>
        <p class="text-muted mb-0">Terhubung & berjalan normal</p>
      </div>
    </div>
  </div>
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <p class="card-title mb-0">Disconnected</p>
          <i class="mdi mdi-server-off text-danger icon-lg"></i>
        </div>
        <h2 class="font-weight-bold mb-1">{{ $agentStats['disconnected'] }}</h2>
        <p class="text-muted mb-0">Pernah terhubung, kini terputus</p>
      </div>
    </div>
  </div>
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <p class="card-title mb-0">Pending</p>
          <i class="mdi mdi-server text-warning icon-lg"></i>
        </div>
        <h2 class="font-weight-bold mb-1">{{ $agentStats['pending'] }}</h2>
        <p class="text-muted mb-0">Menunggu registrasi selesai</p>
      </div>
    </div>
  </div>
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <p class="card-title mb-0">Never Connected</p>
          <i class="mdi mdi-server-minus text-secondary icon-lg"></i>
        </div>
        <h2 class="font-weight-bold mb-1">{{ $agentStats['never_connected'] }}</h2>
        <p class="text-muted mb-0">Belum pernah terhubung sama sekali</p>
      </div>
    </div>
  </div>
</div>

{{-- Total Agents & Customers --}}
<div class="row">
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <p class="card-title mb-0">Total Agents</p>
          <i class="mdi mdi-server-network text-primary icon-lg"></i>
        </div>
        <h2 class="font-weight-bold mb-1">{{ $agentStats['total'] }}</h2>
        <p class="text-muted mb-0"><span class="text-success me-1"><i class="mdi mdi-arrow-up"></i>3</span> dari bulan lalu</p>
      </div>
    </div>
  </div>
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <p class="card-title mb-0">Total Customers</p>
          <i class="mdi mdi-account-group text-info icon-lg"></i>
        </div>
        <h2 class="font-weight-bold mb-1">124</h2>
        <p class="text-muted mb-0"><span class="text-success me-1"><i class="mdi mdi-arrow-up"></i>7</span> dari bulan lalu</p>
      </div>
    </div>
  </div>

  @php
    $total = $agentStats['total'] ?: 1; // avoid division by zero
    $activePct          = round($agentStats['active'] / $total * 100, 1);
    $disconnectedPct    = round($agentStats['disconnected'] / $total * 100, 1);
    $pendingPct         = round($agentStats['pending'] / $total * 100, 1);
    $neverConnectedPct  = round($agentStats['never_connected'] / $total * 100, 1);
  @endphp

  <div class="col-md-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title mb-1">Komposisi Status Agent</p>
        <p class="text-muted mb-3">Dari total 47 agent terdaftar</p>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-success">Active</span>
          <span class="font-weight-bold">{{ $agentStats['active'] }} ({{ $activePct }}%)</span>
        </div>
        <div class="progress mb-2" style="height:8px">
          <div class="progress-bar bg-success" role="progressbar" style="width:{{ $activePct }}%" aria-valuenow="{{ $activePct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-danger">Disconnected</span>
          <span class="font-weight-bold">{{ $agentStats['disconnected'] }} ({{ $disconnectedPct }}%)</span>
        </div>
        <div class="progress mb-2" style="height:8px">
          <div class="progress-bar bg-danger" role="progressbar" style="width:{{ $disconnectedPct }}%" aria-valuenow="{{ $disconnectedPct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-warning">Pending</span>
          <span class="font-weight-bold">{{ $agentStats['pending'] }} ({{ $pendingPct }}%)</span>
        </div>
        <div class="progress mb-2" style="height:8px">
          <div class="progress-bar bg-warning" role="progressbar" style="width:{{ $pendingPct }}%" aria-valuenow="{{ $pendingPct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-secondary">Never Connected</span>
          <span class="font-weight-bold">{{ $agentStats['never_connected'] }} ({{ $neverConnectedPct }}%)</span>
        </div>
        <div class="progress" style="height:8px">
          <div class="progress-bar bg-secondary" role="progressbar" style="width:{{ $neverConnectedPct }}%" aria-valuenow="{{ $neverConnectedPct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Alert Trend, Rule Level Distribution --}}
<div class="row">
  <div class="col-md-5 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <p class="card-title mb-0">Alert Trend (7 Hari)</p>
          <span class="badge badge-success badge-pill">Live</span>
        </div>
        <p class="text-muted mb-3">Pergerakan alert harian seluruh agent</p>
        <canvas id="alert-trend-chart" height="160"></canvas>
      </div>
    </div>
  </div>

  @php
    $total_alerts = $totalAlerts ?? 0;
    $critical_pct = $total_alerts > 0 ? round($alertSeverity['critical'] / $total_alerts * 100, 1) : 0;
    $high_pct = $total_alerts > 0 ? round($alertSeverity['high'] / $total_alerts * 100, 1) : 0;
    $medium_pct = $total_alerts > 0 ? round($alertSeverity['medium'] / $total_alerts * 100, 1) : 0;
    $low_pct = $total_alerts > 0 ? round($alertSeverity['low'] / $total_alerts * 100, 1) : 0;
  @endphp

  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title">Alerts by Rule Level</p>
        <p class="text-muted mb-1">
          Dari <strong>OpenSearch Indexer</strong> — aggregasi <code>rule.level</code>.
          Level 1–5 = Low, 6–8 = Medium, 9–11 = High, 12–15 = Critical.
        </p>
        <canvas id="severity-chart" height="180"></canvas>
        <div class="d-flex justify-content-around mt-3">
          <span class="badge badge-danger">Critical (12–15)</span>
          <span class="badge badge-warning">High (9–11)</span>
          <span class="badge badge-info">Medium (6–8)</span>
          <span class="badge badge-success">Low (1–5)</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title">Total Security Alerts</p>
        <h2 class="font-weight-bold mb-1">{{ number_format($totalAlerts) }}</h2>
        <p class="text-muted mb-3">Dari seluruh agent</p>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-danger">Critical</span>
          <span class="font-weight-bold">{{ number_format($alertSeverity['critical']) }}</span>
        </div>
        <div class="progress mb-3" style="height:6px">
          <div class="progress-bar bg-danger" role="progressbar" style="width:{{ $critical_pct }}%" aria-valuenow="{{ $critical_pct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-warning">High</span>
          <span class="font-weight-bold">{{ number_format($alertSeverity['high']) }}</span>
        </div>
        <div class="progress mb-3" style="height:6px">
          <div class="progress-bar bg-warning" role="progressbar" style="width:{{ $high_pct }}%" aria-valuenow="{{ $high_pct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-info">Medium</span>
          <span class="font-weight-bold">{{ number_format($alertSeverity['medium']) }}</span>
        </div>
        <div class="progress mb-3" style="height:6px">
          <div class="progress-bar bg-info" role="progressbar" style="width:{{ $medium_pct }}%" aria-valuenow="{{ $medium_pct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-success">Low</span>
          <span class="font-weight-bold">{{ number_format($alertSeverity['low']) }}</span>
        </div>
        <div class="progress" style="height:6px">
          <div class="progress-bar bg-success" role="progressbar" style="width:{{ $low_pct }}%" aria-valuenow="{{ $low_pct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- OS Distribution & Top Rules --}}
<div class="row">
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title">OS Distribution</p>
        <p class="text-muted mb-3">Sistem operasi dari seluruh agent</p>
        <canvas id="os-chart" height="200"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title">Top Rules Paling Sering Trigger</p>
        <p class="text-muted mb-3">Data dari OpenSearch — aggregasi <code>rule.id</code></p>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>Rule ID</th>
                <th>Description</th>
                <th>Level</th>
                <th>Count</th>
                <th>Trend</th>
              </tr>
            </thead>
            <tbody>
              @forelse($topRules as $rule)
              <tr>
                <td><span class="badge badge-secondary">{{ $rule['id'] }}</span></td>
                <td>{{ $rule['description'] }}</td>
                <td>
                  @php
                    $levelColor = 'success';
                    if ($rule['level'] >= 12) $levelColor = 'danger';
                    elseif ($rule['level'] >= 9) $levelColor = 'warning';
                    elseif ($rule['level'] >= 6) $levelColor = 'info';
                  @endphp
                  <span class="badge badge-{{ $levelColor }}">{{ $rule['level'] }}</span>
                </td>
                <td class="font-weight-bold">{{ number_format($rule['count']) }}</td>
                <td><i class="mdi mdi-trending-up text-danger"></i></td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No rules data available</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Top Agents by Alert --}}
<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <p class="card-title mb-0">Top Agents dengan Security Alert Terbanyak</p>
          <a href="#" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th>Agent ID</th>
                <th>Agent Name</th>
                <th>IP Address</th>
                <th>OS</th>
                <th>Status</th>
                <th>Total Alerts</th>
                <th>Alert Bar</th>
              </tr>
            </thead>
            <tbody>
              @php
                $maxAlerts = collect($topAgents)->max('alert_count') ?? 1;
              @endphp
              @forelse($topAgents as $agent)
              <tr>
                <td><span class="badge badge-secondary">{{ $agent['id'] }}</span></td>
                <td class="font-weight-bold">{{ $agent['name'] }}</td>
                <td>{{ $agent['ip'] }}</td>
                <td>
                  @php
                    $osIcon = 'mdi-linux';
                    if (stripos($agent['os'], 'windows') !== false) $osIcon = 'mdi-microsoft-windows';
                    elseif (stripos($agent['os'], 'macos') !== false) $osIcon = 'mdi-apple';
                    elseif (stripos($agent['os'], 'freebsd') !== false) $osIcon = 'mdi-freebsd';
                  @endphp
                  <i class="mdi {{ $osIcon }} me-1"></i> {{ $agent['os'] }}
                </td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="font-weight-bold text-danger">{{ number_format($agent['alert_count']) }}</td>
                <td style="min-width:150px">
                  <div class="progress" style="height:8px">
                    @php
                      $percentage = ($agent['alert_count'] / $maxAlerts) * 100;
                    @endphp
                    <div class="progress-bar bg-danger" role="progressbar" style="width:{{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No agent data available</td>
              </tr>
              @endforelse
            </tbody>
          </table>
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

@endsection

@push('scripts')
<script>
// Alert Trend Chart
const alertTrendData = @json($alertTrend ?? []);
const fallbackTrendData = [1420, 1835, 1230, 2105, 1784, 980, 1493];
const trendData = alertTrendData.length > 0 ? alertTrendData : fallbackTrendData;

// Generate date labels for the last 7 days ending today
function generateDateLabels(numDays = 7) {
  const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const today = new Date();
  const labels = [];
  
  // Start from 6 days ago (for 7 days total including today)
  const startDate = new Date(today);
  startDate.setDate(startDate.getDate() - (numDays - 1));
  
  for (let i = 0; i < numDays; i++) {
    const date = new Date(startDate);
    date.setDate(date.getDate() + i);
    const dayName = dayNames[date.getDay()];
    const day = String(date.getDate()).padStart(2, '0');
    const month = monthNames[date.getMonth()];
    labels.push(`${dayName} ${day} ${month}`);
  }
  
  return labels;
}

const dateLabels = generateDateLabels(trendData.length);

new Chart(document.getElementById('alert-trend-chart').getContext('2d'), {
  type: 'line',
  data: {
    labels: dateLabels,
    datasets: [{
      label: 'Total Alerts',
      data: trendData,
      borderColor: '#4B49AC',
      backgroundColor: 'rgba(75, 73, 172, 0.1)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#4B49AC',
      pointRadius: 4,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
      x: { grid: { display: false } }
    }
  }
});

// Severity Chart
const severityData = @json($alertSeverity);
new Chart(document.getElementById('severity-chart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: ['Critical (12–15)', 'High (9–11)', 'Medium (6–8)', 'Low (1–5)'],
    datasets: [{
      data: [severityData.critical, severityData.high, severityData.medium, severityData.low],
      backgroundColor: ['#FF4747', '#FFC542', '#17C1E8', '#82D616'],
      borderWidth: 0,
      hoverOffset: 6,
    }]
  },
  options: {
    responsive: true,
    cutout: '70%',
    plugins: { legend: { display: false } }
  }
});

new Chart(document.getElementById('os-chart').getContext('2d'), {
  type: 'bar',
  data: {
    labels: @json(array_keys($osDistribution ?? [])),
    datasets: [{
      label: 'Agents',
      data: @json(array_values($osDistribution ?? [])),
      backgroundColor: ['#4B49AC', '#7978E9', '#F3797E', '#FFC542', '#82D616'],
      borderRadius: 6,
      borderWidth: 0,
    }]
  },
  options: {
    responsive: true,
    indexAxis: 'y',
    plugins: { legend: { display: false } },
    scales: {
      x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
      y: { grid: { display: false } }
    }
  }
});
</script>
@endpush