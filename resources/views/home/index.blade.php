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

  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title">Alerts by Rule Level</p>
        <p class="text-muted mb-1">
          Dari <strong>Wazuh Indexer</strong> — aggregasi <code>rule.level</code>.
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
        <h2 class="font-weight-bold mb-1">12,847</h2>
        <p class="text-muted mb-3">Dari seluruh agent</p>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-danger">Critical</span>
          <span class="font-weight-bold">1,203</span>
        </div>
        <div class="progress mb-3" style="height:6px">
          <div class="progress-bar bg-danger" role="progressbar" style="width:9%" aria-valuenow="9" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-warning">High</span>
          <span class="font-weight-bold">3,451</span>
        </div>
        <div class="progress mb-3" style="height:6px">
          <div class="progress-bar bg-warning" role="progressbar" style="width:27%" aria-valuenow="27" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-info">Medium</span>
          <span class="font-weight-bold">5,782</span>
        </div>
        <div class="progress mb-3" style="height:6px">
          <div class="progress-bar bg-info" role="progressbar" style="width:45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-success">Low</span>
          <span class="font-weight-bold">2,411</span>
        </div>
        <div class="progress" style="height:6px">
          <div class="progress-bar bg-success" role="progressbar" style="width:19%" aria-valuenow="19" aria-valuemin="0" aria-valuemax="100"></div>
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
        <p class="text-muted mb-3">Data dari Wazuh Indexer — aggregasi <code>rule.id</code></p>
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
              <tr>
                <td><span class="badge badge-secondary">5501</span></td>
                <td>User successfully logged in</td>
                <td><span class="badge badge-success">3</span></td>
                <td class="font-weight-bold">2,341</td>
                <td><i class="mdi mdi-trending-up text-danger"></i></td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">40111</span></td>
                <td>Firewall Drop event</td>
                <td><span class="badge badge-info">8</span></td>
                <td class="font-weight-bold">1,892</td>
                <td><i class="mdi mdi-trending-up text-danger"></i></td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">1002</span></td>
                <td>Unknown problem somewhere in the system</td>
                <td><span class="badge badge-warning">10</span></td>
                <td class="font-weight-bold">1,204</td>
                <td><i class="mdi mdi-trending-down text-success"></i></td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">5402</span></td>
                <td>PAM: Login session opened</td>
                <td><span class="badge badge-success">3</span></td>
                <td class="font-weight-bold">987</td>
                <td><i class="mdi mdi-trending-neutral text-muted"></i></td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">31101</span></td>
                <td>Web server 400 error code</td>
                <td><span class="badge badge-info">6</span></td>
                <td class="font-weight-bold">763</td>
                <td><i class="mdi mdi-trending-up text-danger"></i></td>
              </tr>
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
              <tr>
                <td><span class="badge badge-secondary">001</span></td>
                <td class="font-weight-bold">web-server-prod</td>
                <td>192.168.1.10</td>
                <td><i class="mdi mdi-linux me-1"></i> Ubuntu 22.04</td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="font-weight-bold text-danger">3,241</td>
                <td style="min-width:150px">
                  <div class="progress" style="height:8px">
                    <div class="progress-bar bg-danger" role="progressbar" style="width:100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">008</span></td>
                <td class="font-weight-bold">db-server-01</td>
                <td>192.168.1.25</td>
                <td><i class="mdi mdi-linux me-1"></i> CentOS 7</td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="font-weight-bold text-warning">2,108</td>
                <td style="min-width:150px">
                  <div class="progress" style="height:8px">
                    <div class="progress-bar bg-warning" role="progressbar" style="width:65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">014</span></td>
                <td class="font-weight-bold">firewall-edge</td>
                <td>10.0.0.1</td>
                <td><i class="mdi mdi-microsoft-windows me-1"></i> Windows Server 2019</td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="font-weight-bold text-warning">1,874</td>
                <td style="min-width:150px">
                  <div class="progress" style="height:8px">
                    <div class="progress-bar bg-warning" role="progressbar" style="width:58%" aria-valuenow="58" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">022</span></td>
                <td class="font-weight-bold">mail-server</td>
                <td>192.168.2.5</td>
                <td><i class="mdi mdi-linux me-1"></i> Debian 11</td>
                <td><span class="badge badge-danger">Disconnected</span></td>
                <td class="font-weight-bold text-info">1,102</td>
                <td style="min-width:150px">
                  <div class="progress" style="height:8px">
                    <div class="progress-bar bg-info" role="progressbar" style="width:34%" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td><span class="badge badge-secondary">031</span></td>
                <td class="font-weight-bold">workstation-dev3</td>
                <td>192.168.3.11</td>
                <td><i class="mdi mdi-microsoft-windows me-1"></i> Windows 11</td>
                <td><span class="badge badge-success">Active</span></td>
                <td class="font-weight-bold text-success">892</td>
                <td style="min-width:150px">
                  <div class="progress" style="height:8px">
                    <div class="progress-bar bg-success" role="progressbar" style="width:28%" aria-valuenow="28" aria-valuemin="0" aria-valuemax="100"></div>
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
new Chart(document.getElementById('alert-trend-chart').getContext('2d'), {
  type: 'line',
  data: {
    labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    datasets: [{
      label: 'Total Alerts',
      data: [1420, 1835, 1230, 2105, 1784, 980, 1493],
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

new Chart(document.getElementById('severity-chart').getContext('2d'), {
  type: 'doughnut',
  data: {
    labels: ['Critical (12–15)', 'High (9–11)', 'Medium (6–8)', 'Low (1–5)'],
    datasets: [{
      data: [1203, 3451, 5782, 2411],
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
    labels: ['Linux', 'Windows', 'macOS', 'FreeBSD', 'Other'],
    datasets: [{
      label: 'Agents',
      data: [22, 14, 6, 3, 2],
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