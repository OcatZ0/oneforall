@extends('layouts.app')

@section('title', 'Agent - One For All')

@section('content')

{{-- Status, Details, Evolution --}}
<div class="row grid-margin">
  <div class="col-md-3 stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title text-center">STATUS</p>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
          <span><span class="badge badge-success mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Active</span>
          <span class="font-weight-bold">1</span>
        </div>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
          <span><span class="badge badge-danger mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Disconnected</span>
          <span class="font-weight-bold">0</span>
        </div>
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
          <span><span class="badge badge-warning mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Pending</span>
          <span class="font-weight-bold">0</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span><span class="badge badge-secondary mr-2 me-2">&nbsp;&nbsp;&nbsp;</span> Never Connected</span>
          <span class="font-weight-bold">0</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6 stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title text-center">DETAILS</p>
        <div class="row text-center mb-3">
          <div class="col">
            <p class="text-muted mb-1">Active</p>
            <h4 class="text-success font-weight-bold">1</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Disconnected</p>
            <h4 class="text-danger font-weight-bold">0</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Pending</p>
            <h4 class="text-warning font-weight-bold">0</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Never Connected</p>
            <h4 class="text-secondary font-weight-bold">0</h4>
          </div>
          <div class="col">
            <p class="text-muted mb-1">Coverage</p>
            <h4 class="text-success font-weight-bold">100%</h4>
          </div>
        </div>
        <hr class="mt-0">
        <div class="row">
          <div class="col">
            <p class="text-muted mb-0">Last registered agent</p>
            <a href="#">windows-10</a>
          </div>
          <div class="col">
            <p class="text-muted mb-0">Most active agent</p>
            <a href="#">windows-10</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <p class="card-title mb-0">EVOLUTION</p>
          <small class="text-muted">Last 24 hours</small>
        </div>
        <p class="mb-2"><span class="text-success mr-1">●</span> <small>active</small></p>
        <canvas id="evolution-chart" height="100"></canvas>
        <p class="text-center mb-0 mt-1"><small class="text-muted">timestamp per 10 minutes</small></p>
      </div>
    </div>
  </div>
</div>

{{-- Agents Table --}}
<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="card-title mb-0">Agents</h4>
          <div class="d-flex">
            <button class="btn btn-sm btn-success mr-2 me-2">
              <i class="mdi mdi-refresh mr-1"></i> Refresh
            </button>
            <button class="btn btn-sm btn-primary">
              <i class="mdi mdi-download mr-1"></i> Export
            </button>
          </div>
        </div>

        <div class="d-flex align-items-center mb-3">
          <div class="input-group" style="max-width:400px">
            <div class="input-group-prepend">
              <span class="input-group-text bg-white border-right-0">
                <i class="mdi mdi-magnify text-muted"></i>
              </span>
            </div>
            <input type="text" class="form-control border-left-0" placeholder="Search agents...">
          </div>
          <div class="ml-3 ms-3">
            <select class="form-control form-select">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="disconnected">Disconnected</option>
              <option value="pending">Pending</option>
              <option value="never_connected">Never Connected</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th>ID <i class="mdi mdi-arrow-up text-muted"></i></th>
                <th>Name</th>
                <th>IP Address</th>
                <th>Group(s)</th>
                <th>Operating System</th>
                <th>Cluster Node</th>
                <th>Version</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>001</td>
                <td class="font-weight-bold">windows-10</td>
                <td>192.168.200.169</td>
                <td><span class="badge badge-secondary">default</span></td>
                <td><i class="mdi mdi-microsoft-windows text-primary mr-1"></i> Microsoft Windows Server 2022 Datacenter 10.0.20348.469</td>
                <td>node01</td>
                <td>v4.7.5</td>
                <td><span class="badge badge-success">active</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-3">
          <div class="d-flex align-items-center">
            <span class="text-muted mr-2 me-2">Rows per page:</span>
            <select class="form-control form-select" style="width:70px">
              <option>10</option>
              <option>25</option>
              <option>50</option>
            </select>
          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const labels = [];
const dataPoints = [];
const now = new Date();
for (let i = 144; i >= 0; i--) {
  const t = new Date(now - i * 10 * 60 * 1000);
  labels.push(t.getHours().toString().padStart(2,'0') + ':' + t.getMinutes().toString().padStart(2,'0'));
  dataPoints.push(1);
}
new Chart(document.getElementById('evolution-chart').getContext('2d'), {
  type: 'line',
  data: {
    labels: labels,
    datasets: [{
      data: dataPoints,
      borderColor: '#82D616',
      borderWidth: 2,
      fill: false,
      pointRadius: 0,
      tension: 0,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { min: 0, max: 2, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
      x: { ticks: { maxTicksLimit: 6, font: { size: 10 } }, grid: { display: false } }
    }
  }
});
</script>
@endpush