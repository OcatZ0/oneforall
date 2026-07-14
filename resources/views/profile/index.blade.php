@extends('layouts.app')

@section('title', 'Profil - One For All')

@section('content')

<div class="row justify-content-center">
  <div class="col-md-12">

    <div class="card grid-margin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="card-title mb-0">Profil</h4>
          @php $openPasswordForm = session('password_success') || $errors->hasAny(['current_password', 'new_password']) || request()->boolean('change_password'); @endphp
          <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="collapse" data-bs-target="#changePasswordForm" aria-expanded="{{ $openPasswordForm ? 'true' : 'false' }}">
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
        <div class="collapse mb-4 {{ $openPasswordForm ? 'show' : '' }}" id="changePasswordForm">
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

          <div class="input-group mb-3">
            <input type="text" id="logSearch" class="form-control" placeholder="Cari aktivitas...">
            <button class="btn btn-secondary" id="logSearchClear" type="button" style="display:none;">
              <i class="mdi mdi-close"></i>
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th style="width:50px">#</th>
                  <th>Aktivitas</th>
                  <th>Tanggal</th>
                </tr>
              </thead>
              <tbody id="log-tbody">
                <tr><td colspan="3" class="text-center text-muted py-4">Memuat...</td></tr>
              </tbody>
            </table>
          </div>

          <div id="log-pagination-footer" class="mt-3"></div>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
const logsEndpoint = '{{ route('profile.logs') }}';
let currentPage = 1;
let currentSearch = '';

async function loadLogs(page = 1, search = currentSearch) {
  currentPage = page;
  currentSearch = search;

  const params = new URLSearchParams({ page });
  if (search) params.set('search', search);

  const tbody = document.getElementById('log-tbody');
  tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Memuat...</td></tr>';

  try {
    const res  = await fetch(logsEndpoint + '?' + params.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();

    if (data.error) {
      tbody.innerHTML = `<tr><td colspan="3" class="text-center text-danger py-4">${data.error}</td></tr>`;
      return;
    }

    if (!data.logs.length) {
      const msg = search
        ? `Tidak ada aktivitas yang ditemukan untuk pencarian "<strong>${search}</strong>"`
        : 'Belum ada aktivitas';
      tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-4">${msg}</td></tr>`;
      document.getElementById('log-pagination-footer').innerHTML = '';
      return;
    }

    tbody.innerHTML = data.logs.map((log, i) => `
      <tr>
        <td>${data.from + i}</td>
        <td>${log.activity}</td>
        <td><span class="text-muted" title="${log.created_at_formatted}">${log.created_at_formatted}</span></td>
      </tr>
    `).join('');

    renderLogsPagination(data);
  } catch {
    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
  }
}

function renderLogsPagination(data) {
  const footer = document.getElementById('log-pagination-footer');
  if (data.totalPages <= 1 && data.total === 0) { footer.innerHTML = ''; return; }

  const info = `<span class="text-muted small">Menampilkan ${data.from}–${data.to} dari ${data.total} aktivitas</span>`;

  if (data.totalPages <= 1) { footer.innerHTML = `<div>${info}</div>`; return; }

  const prev = `<button class="btn btn-sm btn-outline-secondary" ${data.page <= 1 ? 'disabled' : ''}
    onclick="loadLogs(${data.page - 1})"><i class="mdi mdi-chevron-left"></i></button>`;
  const next = `<button class="btn btn-sm btn-outline-secondary" ${data.page >= data.totalPages ? 'disabled' : ''}
    onclick="loadLogs(${data.page + 1})"><i class="mdi mdi-chevron-right"></i></button>`;

  const pages = [];
  for (let p = 1; p <= data.totalPages; p++) {
    if (p === 1 || p === data.totalPages || Math.abs(p - data.page) <= 1) {
      pages.push(`<button class="btn btn-sm ${p === data.page ? 'btn-primary' : 'btn-outline-secondary'}"
        onclick="loadLogs(${p})">${p}</button>`);
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...');
    }
  }

  footer.innerHTML = `
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      ${info}
      <div class="d-flex align-items-center gap-1">
        ${prev}${pages.map(p => p === '...' ? `<span class="px-1">…</span>` : p).join('')}${next}
      </div>
    </div>`;
}

// Search with debounce
let _searchTimer;
const searchInput = document.getElementById('logSearch');
const clearBtn    = document.getElementById('logSearchClear');

searchInput.addEventListener('input', () => {
  clearBtn.style.display = searchInput.value ? '' : 'none';
  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(() => loadLogs(1, searchInput.value.trim()), 400);
});

clearBtn.addEventListener('click', () => {
  searchInput.value = '';
  clearBtn.style.display = 'none';
  loadLogs(1, '');
});

loadLogs(1);
</script>

@endsection