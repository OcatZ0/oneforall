@extends('layouts.app')

@section('title', 'Pengguna - One For All')

@section('content')

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="card-title mb-0">Pengguna</h4>
          <a href="#" class="btn btn-sm btn-primary">
            <i class="mdi mdi-plus mr-1"></i> Tambah Pengguna
          </a>
        </div>

        <div class="d-flex align-items-center mb-3">
          <div class="input-group" style="max-width:350px">
            <div class="input-group-prepend">
              <span class="input-group-text bg-white border-right-0">
                <i class="mdi mdi-magnify text-muted"></i>
              </span>
            </div>
            <input type="text" class="form-control border-left-0" placeholder="Cari username atau email...">
          </div>
          <div class="ml-3 ms-3">
            <select class="form-control form-select" style="width:150px">
              <option value="">Semua Role</option>
              <option value="admin">Admin</option>
              <option value="operator">Operator</option>
              <option value="viewer">Viewer</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th style="width:50px">#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Total Agents</th>
                <th>Agents Assigned</th>
                <th>Tanggal Dibuat</th>
                <th style="width:100px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td class="font-weight-bold">fadli</td>
                <td>fadli@example.com</td>
                <td><span class="badge badge-danger">Admin</span></td>
                <td><span class="font-weight-bold">7</span></td>
                <td>
                  <span class="badge badge-secondary mr-1 me-1">web-server-prod</span>
                  <span class="badge badge-secondary mr-1 me-1">db-server-01</span>
                  <span class="badge badge-secondary">+5</span>
                </td>
                <td>01 Jan 2025</td>
                <td class="text-nowrap">
                  <a href="#" class="btn btn-sm btn-outline-primary mr-1 me-1"><i class="mdi mdi-pencil"></i></a>
                  <a href="#" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></a>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td class="font-weight-bold">budi</td>
                <td>budi@example.com</td>
                <td><span class="badge badge-warning">Operator</span></td>
                <td><span class="font-weight-bold">2</span></td>
                <td>
                  <span class="badge badge-secondary mr-1 me-1">firewall-edge</span>
                  <span class="badge badge-secondary">mail-server</span>
                </td>
                <td>05 Feb 2025</td>
                <td class="text-nowrap">
                  <a href="#" class="btn btn-sm btn-outline-primary mr-1 me-1"><i class="mdi mdi-pencil"></i></a>
                  <a href="#" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></a>
                </td>
              </tr>
              <tr>
                <td>3</td>
                <td class="font-weight-bold">siti</td>
                <td>siti@example.com</td>
                <td><span class="badge badge-info">Viewer</span></td>
                <td><span class="font-weight-bold">1</span></td>
                <td>
                  <span class="badge badge-secondary">workstation-dev3</span>
                </td>
                <td>12 Feb 2025</td>
                <td class="text-nowrap">
                  <a href="#" class="btn btn-sm btn-outline-primary mr-1 me-1"><i class="mdi mdi-pencil"></i></a>
                  <a href="#" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></a>
                </td>
              </tr>
              <tr>
                <td>4</td>
                <td class="font-weight-bold">andi</td>
                <td>andi@example.com</td>
                <td><span class="badge badge-warning">Operator</span></td>
                <td><span class="font-weight-bold">4</span></td>
                <td>
                  <span class="badge badge-secondary mr-1 me-1">db-server-01</span>
                  <span class="badge badge-secondary mr-1 me-1">mail-server</span>
                  <span class="badge badge-secondary">+2</span>
                </td>
                <td>20 Mar 2025</td>
                <td class="text-nowrap">
                  <a href="#" class="btn btn-sm btn-outline-primary mr-1 me-1"><i class="mdi mdi-pencil"></i></a>
                  <a href="#" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></a>
                </td>
              </tr>
              <tr>
                <td>5</td>
                <td class="font-weight-bold">rina</td>
                <td>rina@example.com</td>
                <td><span class="badge badge-info">Viewer</span></td>
                <td><span class="font-weight-bold">0</span></td>
                <td>
                  <span class="text-muted font-italic">Tidak ada</span>
                </td>
                <td>01 Apr 2025</td>
                <td class="text-nowrap">
                  <a href="#" class="btn btn-sm btn-outline-primary mr-1 me-1"><i class="mdi mdi-pencil"></i></a>
                  <a href="#" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></a>
                </td>
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
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>
            </ul>
          </nav>
        </div>

      </div>
    </div>
  </div>
</div>

@endsection