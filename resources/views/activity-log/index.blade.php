@extends('layouts.app')

@section('title', 'Log Aktivitas - One For All')

@section('content')

<div class="container-fluid px-4 py-3">

  {{-- Page Header --}}
  <div class="mb-4">
    <h4 class="fw-bold mb-1">Log Aktivitas</h4>
    <p class="text-muted mb-0">Seluruh aktivitas pengguna di sistem</p>
  </div>

  {{-- Filter Card --}}
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('activity-log') }}" id="filterForm">
        <div class="row g-2 align-items-end">
          <div class="col-12 col-md-4">
            <label class="form-label small text-muted mb-1">Cari Aktivitas</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="mdi mdi-magnify text-muted"></i>
              </span>
              <input type="text" name="search" class="form-control border-start-0"
                placeholder="Cari kata kunci aktivitas..."
                value="{{ $search }}">
            </div>
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Pengguna</label>
            <select name="user_id" class="form-control form-select">
              <option value="">Semua Pengguna</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                  {{ $user->username }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
          </div>
          <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
              <i class="mdi mdi-filter me-1"></i>Filter
            </button>
            <a href="{{ route('activity-log') }}" class="btn btn-outline-secondary">
              <i class="mdi mdi-refresh"></i>
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Summary + Per-page --}}
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="text-muted small">
      @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
        Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}</strong> &ndash; <strong>{{ $logs->lastItem() ?? 0 }}</strong>
        dari <strong>{{ $logs->total() }}</strong> record ditemukan
      @else
        Tidak ada data
      @endif
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="text-muted small">Rows per page:</span>
      <form method="GET" action="{{ route('activity-log') }}" id="perPageForm">
        @foreach(request()->query() as $key => $value)
          @if($key !== 'per_page' && $key !== 'page')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endif
        @endforeach
        <input type="hidden" name="page" value="1">
        <select name="per_page" class="form-control form-select" style="width:90px"
          onchange="document.getElementById('perPageForm').submit()">
          <option value="25"  {{ $perPage == 25  ? 'selected' : '' }}>25</option>
          <option value="50"  {{ $perPage == 50  ? 'selected' : '' }}>50</option>
          <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
      </form>
    </div>
  </div>

  {{-- Table Card --}}
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead>
            <tr>
              <th style="width:50px" class="px-3 py-3">#</th>
              <th class="py-3">Pengguna</th>
              <th class="py-3">Aktivitas</th>
              <th class="py-3 text-nowrap">Waktu</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
            @php
              $color = 'secondary';
              if (str_contains(strtolower($log->activity), 'login'))    $color = 'success';
              elseif (str_contains(strtolower($log->activity), 'logout'))   $color = 'warning';
              elseif (str_contains(strtolower($log->activity), 'password')) $color = 'danger';

              $peranBadge = 'bg-secondary';
              if (isset($log->user->role)) {
                  if ($log->user->role === 'admin')    $peranBadge = 'bg-danger';
                  elseif ($log->user->role === 'customer') $peranBadge = 'bg-primary';
              }
            @endphp
            <tr>
              <td class="px-3">
                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
              </td>
              <td>
                @if($log->user)
                  <span class="fw-semibold me-1">{{ $log->user->username }}</span>
                  <span class="badge {{ $peranBadge }}">{{ ucfirst($log->user->role ?? '-') }}</span>
                @else
                  <span class="text-muted fst-italic">Pengguna dihapus</span>
                @endif
              </td>
              <td>
                <span class="badge bg-{{ $color }} bg-opacity-15 text-{{ $color }} border border-{{ $color }} border-opacity-25 fw-normal">
                  {{ $log->activity }}
                </span>
              </td>
              <td class="text-nowrap">
                @if($log->created_at)
                  <span title="{{ $log->created_at->format('d M Y H:i:s') }}">
                    {{ $log->created_at->diffForHumans() }}
                  </span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center py-5 text-muted">
                <span class="mdi mdi-history d-block" style="font-size:2.5rem;opacity:.35;margin-bottom:8px;"></span>
                <span class="d-block fw-semibold mb-1">Belum ada aktivitas</span>
                <span class="d-block small">Log aktivitas akan muncul di sini setelah ada tindakan pengguna</span>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->hasPages())
      <div class="px-3 py-3 border-top">
        {{ $logs->links() }}
      </div>
      @endif
    </div>
  </div>

</div>

@endsection
