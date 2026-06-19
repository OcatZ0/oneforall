@extends('layouts.app')

@section('title', 'Profil - One For All')

@section('content')

<div class="row justify-content-center">
  <div class="col-md-12">

    <div class="card grid-margin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="card-title mb-0">Profil</h4>
          <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="collapse" data-bs-target="#changePasswordForm" aria-expanded="false">
            <i class="mdi mdi-lock-reset me-1"></i> Ganti Password
          </button>
        </div>

        <div class="row mb-4">
          <div class="col-md-3 d-flex align-items-center justify-content-center">
            <div class="text-center">
              <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-2" style="width:160px;height:160px">
                <i class="mdi mdi-account text-white" style="font-size:5rem"></i>
              </div>
              <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">{{ ucfirst($user->role) }}</span>
            </div>
          </div>
          <div class="col-md-9">
            <table class="table table-borderless mb-0">
              <tbody>
                <tr>
                  <td class="text-muted fw-bold" style="width:160px">Username</td>
                  <td>{{ $user->username }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-bold">Email</td>
                  <td>{{ $user->email }}</td>
                </tr>
                <tr>
                  <td class="text-muted fw-bold">Role</td>
                  <td><span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">{{ ucfirst($user->role) }}</span></td>
                </tr>
                <tr>
                  <td class="text-muted fw-bold">Tanggal Dibuat</td>
                  <td>{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d F Y') }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <hr>

        {{-- Collapsible Password Change Form --}}
        <div class="collapse mb-4 {{ session('password_success') || $errors->hasAny(['current_password', 'new_password']) ? 'show' : '' }}" id="changePasswordForm">
          <div class="card card-body border">
            <h5 class="card-title mb-3">Ganti Password</h5>

            @if(session('password_success'))
              <div class="alert alert-success">
                <i class="mdi mdi-check-circle-outline me-1"></i> {{ session('password_success') }}
              </div>
            @endif

            <form method="POST" action="{{ route('profile.change-password') }}">
              @csrf

              <div class="mb-3">
                <label for="current_password" class="form-label">Password Saat Ini</label>
                <input type="password" id="current_password" name="current_password"
                       class="form-control @error('current_password') is-invalid @enderror"
                       autocomplete="current-password">
                @error('current_password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" id="new_password" name="new_password"
                       class="form-control @error('new_password') is-invalid @enderror"
                       autocomplete="new-password">
                @error('new_password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                       class="form-control @error('new_password') is-invalid @enderror"
                       autocomplete="new-password">
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="mdi mdi-content-save me-1"></i> Simpan Password
                </button>
                <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-toggle="collapse" data-bs-target="#changePasswordForm">
                  Batal
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="mt-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title mb-0">Agents Dimiliki <span class="badge bg-primary ms-2">{{ count($agents) }}</span></h5>
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
                  <td class="fw-bold">{{ $agent->agent_id }}</td>
                  <td>{{ $agent->name }}</td>
                  <td>{{ $agent->description }}</td>
                  <td>{{ \Carbon\Carbon::parse($agent->created_at)->translatedFormat('d M Y') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="d-flex flex-column align-items-center justify-content-center text-muted py-5 text-center">
            <span class="mdi mdi-server-off" style="font-size:3rem; opacity:0.3; margin-bottom:12px;"></span>
            <span class="fw-semibold mb-1">Belum ada agent</span>
            <span class="small">Hubungi admin untuk mendapatkan akses agent</span>
          </div>
          @endif
        </div>

        <hr class="my-4">

        <div class="mt-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="card-title mb-0">Log Aktivitas</h5>
          </div>

          <!-- Search Form -->
          <form method="GET" action="{{ route('profile') }}" class="mb-3">
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Cari aktivitas..." value="{{ $search }}">
              <button class="btn btn-primary" type="submit">
                <i class="mdi mdi-magnify me-1"></i> Cari
              </button>
              @if($search)
                <a href="{{ route('profile') }}" class="btn btn-secondary">
                  <i class="mdi mdi-close me-1"></i> Reset
                </a>
              @endif
            </div>
          </form>

          @if($logs->count() > 0)
          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th style="width:50px">#</th>
                  <th>Aktivitas</th>
                  <th>Tanggal</th>
                </tr>
              </thead>
              <tbody>
                @foreach($logs as $log)
                <tr>
                  <td>{{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}</td>
                  <td>{{ $log->activity }}</td>
                  <td>
                    <span class="text-muted" title="{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') }}">
                      {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y H:i') }}
                    </span>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex align-items-center justify-content-between mt-3">
            <div class="text-muted small">
              @php
                $from = ($logs->currentPage() - 1) * $logs->perPage() + 1;
                $to = min($logs->currentPage() * $logs->perPage(), $logs->total());
              @endphp
              Menampilkan {{ $from }} hingga {{ $to }} dari {{ $logs->total() }} aktivitas
            </div>
            {{ $logs->appends(request()->query())->links() }}
          </div>
          @else
          <div class="d-flex flex-column align-items-center justify-content-center text-muted py-5 text-center">
            @if($search)
            <span class="mdi mdi-magnify" style="font-size:3rem; opacity:0.3; margin-bottom:12px;"></span>
            <span class="fw-semibold mb-1">Tidak ada aktivitas</span>
            <span class="small">Tidak ada aktivitas yang ditemukan untuk pencarian "{{ $search }}"</span>
            @else
            <span class="mdi mdi-history" style="font-size:3rem; opacity:0.3; margin-bottom:12px;"></span>
            <span class="fw-semibold mb-1">Belum ada aktivitas</span>
            <span class="small">Log aktivitas akan muncul setelah ada tindakan</span>
            @endif
          </div>
          @endif
        </div>

      </div>
    </div>

  </div>
</div>

@endsection