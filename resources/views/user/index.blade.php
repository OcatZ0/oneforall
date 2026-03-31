@extends('layouts.app')

@section('title', 'Pengguna - One For All')

@section('content')

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="card-title mb-0">Pengguna</h4>
          <a href="{{ route('user.create') }}" class="btn btn-sm btn-primary">
            <i class="mdi mdi-plus mr-1"></i> Tambah Pengguna
          </a>
        </div>

        <!-- Search and Filter Form -->
        <form method="GET" action="{{ route('user') }}" id="filterForm" class="mb-3">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group" style="max-width:350px">
              <div class="input-group-prepend">
                <span class="input-group-text bg-white border-right-0">
                  <i class="mdi mdi-magnify text-muted"></i>
                </span>
              </div>
              <input type="text" id="searchInput" name="search" class="form-control border-left-0"
                placeholder="Cari username atau email..."
                value="{{ request('search') }}">
            </div>

            <select id="roleFilter" name="role" class="form-control form-select" style="width:180px">
              <option value="">Semua Role</option>
              <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
              <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
            </select>

            <a href="{{ route('user') }}" class="btn btn-sm btn-outline-secondary">
              <i class="mdi mdi-refresh mr-1"></i>Reset
            </a>
          </div>
        </form>

        <!-- Users Table -->
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
              @forelse($users as $user)
              <tr>
                <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                <td class="font-weight-bold">{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>
                  @if($user->peran === 'admin')
                    <span class="badge badge-danger">Admin</span>
                  @elseif($user->peran === 'customer')
                    <span class="badge badge-primary">Customer</span>
                  @else
                    <span class="badge badge-secondary">{{ ucfirst($user->peran) }}</span>
                  @endif
                </td>
                <td><span class="font-weight-bold">{{ $user->agents()->count() }}</span></td>
                <td>
                  @php
                    $agents = $user->agents()->limit(2)->get();
                    $agentCount = $user->agents()->count();
                    $moreCount = $agentCount - 2;
                  @endphp
                  @if($agentCount > 0)
                    @foreach($agents as $agent)
                      <span class="badge badge-secondary mr-1 me-1">{{ $agent->nama }}</span>
                    @endforeach
                    @if($moreCount > 0)
                      <span class="badge badge-secondary">+{{ $moreCount }}</span>
                    @endif
                  @else
                    <span class="text-muted font-italic">Tidak ada</span>
                  @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($user->tanggal_dibuat)->translatedFormat('d M Y') }}</td>
                <td class="text-nowrap">
                  <a href="/user/{{ $user->id_pengguna }}/edit" class="btn btn-sm btn-outline-primary mr-1 me-1">
                    <i class="mdi mdi-pencil"></i>
                  </a>
                  <a href="#" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-delete"></i>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="mdi mdi-information-outline mr-2"></i>Tidak ada pengguna yang ditemukan
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        @if($users->count() > 0)
        <div class="d-flex align-items-center justify-content-between mt-3">
          <div class="d-flex align-items-center">
            <span class="text-muted mr-2 me-2">Rows per page:</span>
            <form method="GET" action="{{ route('user') }}" class="d-inline" id="perPageForm">
              @foreach(request()->query() as $key => $value)
                @if($key !== 'per_page' && $key !== 'page')
                  <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
              @endforeach
              <input type="hidden" name="page" value="1">
              <select name="per_page" class="form-control form-select" style="width:90px" onchange="document.getElementById('perPageForm').submit()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
              </select>
            </form>
          </div>

          <!-- Pagination Links -->
          <div>
            {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
          </div>
        </div>

        <!-- Pagination Info -->
        <div class="text-muted text-sm mt-2">
          Menampilkan {{ ($users->currentPage() - 1) * $users->perPage() + 1 }} hingga
          {{ min($users->currentPage() * $users->perPage(), $users->total()) }} dari {{ $users->total() }} pengguna
        </div>
        @endif

      </div>
    </div>
  </div>
</div>

<script>
// Debounce function for search
function debounce(func, delay) {
  let timeoutId;
  return function(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(this, args), delay);
  };
}

// Auto-submit form on search input (debounced)
const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('input', debounce(function() {
    // Reset to page 1 when searching
    const form = document.getElementById('filterForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'page';
    input.value = '1';
    form.appendChild(input);
    form.submit();
  }, 500));
}

// Auto-submit form on role filter change
const roleFilter = document.getElementById('roleFilter');
if (roleFilter) {
  roleFilter.addEventListener('change', function() {
    const form = document.getElementById('filterForm');
    // Reset to page 1 when filtering
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'page';
    input.value = '1';
    form.appendChild(input);
    form.submit();
  });
}
</script>

@endsection