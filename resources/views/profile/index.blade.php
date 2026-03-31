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
              <span class="badge badge-{{ $user->peran === 'admin' ? 'danger' : 'primary' }}">{{ ucfirst($user->peran) }}</span>
            </div>
          </div>
          <div class="col-md-9">
            <table class="table table-borderless mb-0">
              <tbody>
                <tr>
                  <td class="text-muted font-weight-bold" style="width:160px">Username</td>
                  <td>{{ $user->username }}</td>
                </tr>
                <tr>
                  <td class="text-muted font-weight-bold">Email</td>
                  <td>{{ $user->email }}</td>
                </tr>
                <tr>
                  <td class="text-muted font-weight-bold">Role</td>
                  <td><span class="badge badge-{{ $user->peran === 'admin' ? 'danger' : 'primary' }}">{{ ucfirst($user->peran) }}</span></td>
                </tr>
                <tr>
                  <td class="text-muted font-weight-bold">Tanggal Dibuat</td>
                  <td>{{ \Carbon\Carbon::parse($user->tanggal_dibuat)->translatedFormat('d F Y') }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <hr>

        <div class="mt-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title mb-0">Agents Dimiliki <span class="badge badge-primary ml-2 ms-2">{{ count($agents) }}</span></h5>
          </div>
          @if(count($agents) > 0)
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th style="width:50px">#</th>
                  <th>Agent ID</th>
                  <th>Nama Agent</th>
                  <th>Deskripsi</th>
                  <th>Tanggal Dibuat</th>
                </tr>
              </thead>
              <tbody>
                @foreach($agents as $agent)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td class="font-weight-bold">{{ $agent->id_agent }}</td>
                  <td>{{ $agent->nama }}</td>
                  <td>{{ $agent->deskripsi }}</td>
                  <td>{{ \Carbon\Carbon::parse($agent->tanggal_dibuat)->translatedFormat('d M Y') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="alert alert-info">
            <i class="mdi mdi-information-outline mr-2"></i> Anda belum memiliki agent
          </div>
          @endif
        </div>

      </div>
    </div>

  </div>
</div>

@endsection