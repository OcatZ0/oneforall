@extends('layouts.app')

@section('title', 'Profil - One For All')

@section('content')

<div class="row justify-content-center">
  <div class="col-md-12">

    <div class="card grid-margin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="card-title mb-0">Profile</h4>
          <a href="/auth/forgot-password" class="btn btn-sm btn-outline-warning">
            <i class="mdi mdi-lock-reset mr-1"></i> Ganti Password
          </a>
        </div>

        <div class="row mb-4">
          <div class="col-md-3 d-flex align-items-center justify-content-center">
            <div class="text-center">
              <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-2" style="width:160px;height:160px">
                <i class="mdi mdi-account text-white" style="font-size:5rem"></i>
              </div>
              <span class="badge badge-danger">Admin</span>
            </div>
          </div>
          <div class="col-md-9">
            <table class="table table-borderless mb-0">
              <tbody>
                <tr>
                  <td class="text-muted font-weight-bold" style="width:160px">Username</td>
                  <td>fadli</td>
                </tr>
                <tr>
                  <td class="text-muted font-weight-bold">Email</td>
                  <td>fadli@example.com</td>
                </tr>
                <tr>
                  <td class="text-muted font-weight-bold">Role</td>
                  <td><span class="badge badge-danger">Admin</span></td>
                </tr>
                <tr>
                  <td class="text-muted font-weight-bold">Tanggal Dibuat</td>
                  <td>01 Januari 2025</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <hr>

        <div class="mt-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title mb-0">Agents Dimiliki <span class="badge badge-primary ml-2 ms-2">7</span></h5>
          </div>
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th style="width:50px">#</th>
                  <th>Agent Name</th>
                  <th>IP Address</th>
                  <th>OS</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td class="font-weight-bold">web-server-prod</td>
                  <td>192.168.1.10</td>
                  <td><i class="mdi mdi-linux mr-1"></i> Ubuntu 22.04</td>
                  <td><span class="badge badge-success">Active</span></td>
                </tr>
                <tr>
                  <td>2</td>
                  <td class="font-weight-bold">db-server-01</td>
                  <td>192.168.1.25</td>
                  <td><i class="mdi mdi-linux mr-1"></i> CentOS 7</td>
                  <td><span class="badge badge-success">Active</span></td>
                </tr>
                <tr>
                  <td>3</td>
                  <td class="font-weight-bold">firewall-edge</td>
                  <td>10.0.0.1</td>
                  <td><i class="mdi mdi-microsoft-windows mr-1"></i> Windows Server 2019</td>
                  <td><span class="badge badge-success">Active</span></td>
                </tr>
                <tr>
                  <td>4</td>
                  <td class="font-weight-bold">mail-server</td>
                  <td>192.168.2.5</td>
                  <td><i class="mdi mdi-linux mr-1"></i> Debian 11</td>
                  <td><span class="badge badge-danger">Disconnected</span></td>
                </tr>
                <tr>
                  <td>5</td>
                  <td class="font-weight-bold">workstation-dev3</td>
                  <td>192.168.3.11</td>
                  <td><i class="mdi mdi-microsoft-windows mr-1"></i> Windows 11</td>
                  <td><span class="badge badge-success">Active</span></td>
                </tr>
                <tr>
                  <td>6</td>
                  <td class="font-weight-bold">backup-server</td>
                  <td>192.168.1.50</td>
                  <td><i class="mdi mdi-linux mr-1"></i> Ubuntu 20.04</td>
                  <td><span class="badge badge-warning">Pending</span></td>
                </tr>
                <tr>
                  <td>7</td>
                  <td class="font-weight-bold">proxy-server</td>
                  <td>192.168.1.99</td>
                  <td><i class="mdi mdi-linux mr-1"></i> Debian 12</td>
                  <td><span class="badge badge-success">Active</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

@endsection