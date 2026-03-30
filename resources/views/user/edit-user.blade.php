@extends('layouts.app')

@section('title', 'Edit Pengguna - One For All')

@section('content')

@php
  // Fallback stub so the view renders safely when $user is not passed from the controller.
  // In production, remove this block — the controller's route model binding supplies $user.
  if (!isset($user)) {
    $user = (object)[
      'id'         => 1,
      'username'   => 'fadli',
      'email'      => 'fadli@example.com',
      'role'       => 'admin',
      'created_at' => '01 Januari 2025',
      'agents'     => collect([
        (object)['id' => 1],
        (object)['id' => 2],
        (object)['id' => 3],
        (object)['id' => 5],
        (object)['id' => 6],
        (object)['id' => 7],
      ]),
    ];
  }
@endphp

<div class="row justify-content-center">
  <div class="col-md-10">

    <form action="/users/{{ $user->id }}" method="POST">
      @csrf
      @method('PUT')

      <div class="row">

        {{-- LEFT COLUMN: Identity --}}
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-5">

              {{-- Avatar --}}
              <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mb-3"
                   style="width:110px;height:110px;flex-shrink:0">
                <i class="mdi mdi-account text-white" style="font-size:3.5rem"></i>
              </div>

              <h5 class="font-weight-bold mb-1">{{ $user->username ?? 'fadli' }}</h5>
              <p class="text-muted small mb-3">{{ $user->email ?? 'fadli@example.com' }}</p>
              @php
                $roleBadge = ['admin' => 'danger', 'operator' => 'warning', 'viewer' => 'info'];
                $roleLabel = ucfirst($user->role ?? 'admin');
                $badgeClass = $roleBadge[$user->role ?? 'admin'] ?? 'secondary';
              @endphp
              <span class="badge badge-{{ $badgeClass }} mb-4">{{ $roleLabel }}</span>

              <hr class="w-100">

              <div class="w-100 text-left mt-3">
                <p class="text-muted small mb-1 font-weight-bold">DIBUAT</p>
                <p class="mb-3">{{ $user->created_at ?? '01 Januari 2025' }}</p>

                <p class="text-muted small mb-1 font-weight-bold">ID PENGGUNA</p>
                <p class="mb-0 text-monospace">#{{ $user->id ?? '001' }}</p>
              </div>
            </div>
          </div>
        </div>

        {{-- RIGHT COLUMN: Form --}}
        <div class="col-md-8 mb-4">

          {{-- Account Info Card --}}
          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title mb-4">
                <i class="mdi mdi-account-edit mr-2 text-primary"></i>Informasi Akun
              </h5>

              <div class="form-group">
                <label class="font-weight-bold text-muted small">USERNAME</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                  </div>
                  <input type="text"
                         name="username"
                         class="form-control @error('username') is-invalid @enderror"
                         value="{{ old('username', $user->username ?? 'fadli') }}"
                         placeholder="Masukkan username">
                  @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group">
                <label class="font-weight-bold text-muted small">EMAIL</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                  </div>
                  <input type="email"
                         name="email"
                         class="form-control @error('email') is-invalid @enderror"
                         value="{{ old('email', $user->email ?? 'fadli@example.com') }}"
                         placeholder="Masukkan email">
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group mb-0">
                <label class="font-weight-bold text-muted small">ROLE</label>
                <div class="input-group" style="flex-wrap:nowrap">
                  <div class="input-group-prepend" style="display:flex">
                    <span class="input-group-text" style="align-items:center"><i class="mdi mdi-shield-outline"></i></span>
                  </div>
                  <select name="role" class="form-control form-select @error('role') is-invalid @enderror"
                          style="flex:1;min-width:0;height:auto">
                    <option value="admin"   {{ old('role', $user->role ?? '') === 'admin'    ? 'selected' : '' }}>Admin</option>
                    <option value="operator"{{ old('role', $user->role ?? '') === 'operator' ? 'selected' : '' }}>Operator</option>
                    <option value="viewer"  {{ old('role', $user->role ?? '') === 'viewer'   ? 'selected' : '' }}>Viewer</option>
                  </select>
                  @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <small class="text-muted">
                  <i class="mdi mdi-information-outline mr-1"></i>
                  Hanya admin yang dapat mengelola pengguna.
                </small>
              </div>
            </div>
          </div>

          {{-- Password Card --}}
          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title mb-1">
                <i class="mdi mdi-lock-outline mr-2 text-warning"></i>Ganti Password
              </h5>
              <p class="text-muted small mb-4">Kosongkan jika tidak ingin mengganti password.</p>

              <div class="form-group">
                <label class="font-weight-bold text-muted small">PASSWORD BARU</label>
                <div class="input-group" style="flex-wrap:nowrap">
                  <div class="input-group-prepend" style="display:flex">
                    <span class="input-group-text" style="align-items:center"><i class="mdi mdi-lock-outline"></i></span>
                  </div>
                  <input type="password"
                         name="password"
                         id="password"
                         class="form-control @error('password') is-invalid @enderror"
                         placeholder="Masukkan password baru"
                         style="flex:1;min-width:0">
                  <div class="input-group-append" style="display:flex">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1"
                            style="display:flex;align-items:center;padding:0 .75rem">
                      <i class="mdi mdi-eye-outline" id="toggleIcon"></i>
                    </button>
                  </div>
                </div>
                @error('password')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="form-group mb-0">
                <label class="font-weight-bold text-muted small">KONFIRMASI PASSWORD</label>
                <div class="input-group" style="flex-wrap:nowrap">
                  <div class="input-group-prepend" style="display:flex">
                    <span class="input-group-text" style="align-items:center"><i class="mdi mdi-lock-outline"></i></span>
                  </div>
                  <input type="password"
                         name="password_confirmation"
                         id="passwordConfirm"
                         class="form-control"
                         placeholder="Ulangi password baru"
                         style="flex:1;min-width:0">
                  <div class="input-group-append" style="display:flex">
                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm" tabindex="-1"
                            style="display:flex;align-items:center;padding:0 .75rem">
                      <i class="mdi mdi-eye-outline" id="toggleIconConfirm"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Agents Assignment Card --}}
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="card-title mb-0">
                  <i class="mdi mdi-server-network mr-2 text-info"></i>Agent Assignment
                </h5>
                <span class="badge badge-primary" id="selectedCount">0 dipilih</span>
              </div>
              <p class="text-muted small mb-3">Pilih agent yang dapat diakses oleh pengguna ini.</p>

              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-white border-right-0">
                    <i class="mdi mdi-magnify text-muted"></i>
                  </span>
                </div>
                <input type="text" id="agentSearch" class="form-control border-left-0" placeholder="Cari agent...">
              </div>

              <div style="max-height:260px;overflow-y:auto;" id="agentList">
                @php
                  $allAgents = [
                    ['id' => 1, 'name' => 'web-server-prod',  'ip' => '192.168.1.10', 'os' => 'Ubuntu 22.04',         'icon' => 'mdi-linux',             'status' => 'Active',       'badge' => 'success'],
                    ['id' => 2, 'name' => 'db-server-01',     'ip' => '192.168.1.25', 'os' => 'CentOS 7',             'icon' => 'mdi-linux',             'status' => 'Active',       'badge' => 'success'],
                    ['id' => 3, 'name' => 'firewall-edge',    'ip' => '10.0.0.1',     'os' => 'Windows Server 2019',  'icon' => 'mdi-microsoft-windows', 'status' => 'Active',       'badge' => 'success'],
                    ['id' => 4, 'name' => 'mail-server',      'ip' => '192.168.2.5',  'os' => 'Debian 11',            'icon' => 'mdi-linux',             'status' => 'Disconnected', 'badge' => 'danger'],
                    ['id' => 5, 'name' => 'workstation-dev3', 'ip' => '192.168.3.11', 'os' => 'Windows 11',           'icon' => 'mdi-microsoft-windows', 'status' => 'Active',       'badge' => 'success'],
                    ['id' => 6, 'name' => 'backup-server',    'ip' => '192.168.1.50', 'os' => 'Ubuntu 20.04',         'icon' => 'mdi-linux',             'status' => 'Pending',      'badge' => 'warning'],
                    ['id' => 7, 'name' => 'proxy-server',     'ip' => '192.168.1.99', 'os' => 'Debian 12',            'icon' => 'mdi-linux',             'status' => 'Active',       'badge' => 'success'],
                  ];
                  $assignedIds = isset($user->agents) ? collect($user->agents)->pluck('id')->toArray() : [];
                @endphp

                @foreach($allAgents as $agent)
                <div class="agent-item border rounded p-2 mb-2 d-flex align-items-center"
                     style="cursor:pointer;transition:background 0.15s"
                     onclick="toggleAgent(this, {{ $agent['id'] }})">
                  <input type="checkbox"
                         name="agents[]"
                         value="{{ $agent['id'] }}"
                         class="mr-3 me-3 agent-checkbox"
                         style="width:18px;height:18px;cursor:pointer"
                         {{ in_array($agent['id'], $assignedIds) ? 'checked' : '' }}
                         onclick="event.stopPropagation()">
                  <i class="mdi {{ $agent['icon'] }} mr-2 me-2 text-muted" style="font-size:1.2rem"></i>
                  <div class="flex-grow-1">
                    <div class="font-weight-bold" style="font-size:.9rem">{{ $agent['name'] }}</div>
                    <div class="text-muted" style="font-size:.78rem">{{ $agent['ip'] }} &middot; {{ $agent['os'] }}</div>
                  </div>
                  <span class="badge badge-{{ $agent['badge'] }} ml-2 ms-2">{{ $agent['status'] }}</span>
                </div>
                @endforeach
              </div>

              <div class="d-flex justify-content-end mt-2">
                <button type="button" class="btn btn-sm btn-link text-muted mr-2 me-2" onclick="selectAll()">Pilih Semua</button>
                <button type="button" class="btn btn-sm btn-link text-muted" onclick="clearAll()">Hapus Semua</button>
              </div>
            </div>
          </div>

          {{-- Action Buttons --}}
          <div class="d-flex align-items-center justify-content-between">
            <a href="/users" class="btn btn-outline-secondary">
              <i class="mdi mdi-arrow-left mr-1"></i> Kembali
            </a>
            <div>
              <button type="button" class="btn btn-outline-danger mr-2 me-2"
                      data-toggle="modal" data-target="#deleteModal">
                <i class="mdi mdi-delete mr-1"></i> Hapus Pengguna
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="mdi mdi-content-save mr-1"></i> Simpan Perubahan
              </button>
            </div>
          </div>

        </div>
      </div>
    </form>

  </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="mdi mdi-alert-circle text-danger mr-2"></i>Konfirmasi Hapus
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Apakah Anda yakin ingin menghapus pengguna
          <strong>{{ $user->username ?? 'fadli' }}</strong>?
        </p>
        <p class="text-muted small mb-0">
          <i class="mdi mdi-information-outline mr-1"></i>
          Tindakan ini tidak dapat dibatalkan. Semua data terkait pengguna ini akan dihapus secara permanen.
        </p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
        <form action="/users/{{ $user->id }}" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class="mdi mdi-delete mr-1"></i> Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  // ── Toggle password visibility ──────────────────────────────────────────────
  function setupToggle(btnId, inputId, iconId) {
    document.getElementById(btnId).addEventListener('click', function () {
      const input = document.getElementById(inputId);
      const icon  = document.getElementById(iconId);
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('mdi-eye-outline', 'mdi-eye-off-outline');
      } else {
        input.type = 'password';
        icon.classList.replace('mdi-eye-off-outline', 'mdi-eye-outline');
      }
    });
  }
  setupToggle('togglePassword',        'password',        'toggleIcon');
  setupToggle('togglePasswordConfirm', 'passwordConfirm', 'toggleIconConfirm');

  // ── Agent checkbox toggling ─────────────────────────────────────────────────
  function toggleAgent(row, id) {
    const cb = row.querySelector('.agent-checkbox');
    cb.checked = !cb.checked;
    updateRowStyle(row, cb.checked);
    updateSelectedCount();
  }

  function updateRowStyle(row, checked) {
    if (checked) {
      row.style.background = 'rgba(0,123,255,0.08)';
      row.style.borderColor = '#007bff';
    } else {
      row.style.background = '';
      row.style.borderColor = '';
    }
  }

  function updateSelectedCount() {
    const count = document.querySelectorAll('.agent-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count + ' dipilih';
  }

  function selectAll() {
    document.querySelectorAll('.agent-item').forEach(row => {
      const cb = row.querySelector('.agent-checkbox');
      cb.checked = true;
      updateRowStyle(row, true);
    });
    updateSelectedCount();
  }

  function clearAll() {
    document.querySelectorAll('.agent-item').forEach(row => {
      const cb = row.querySelector('.agent-checkbox');
      cb.checked = false;
      updateRowStyle(row, false);
    });
    updateSelectedCount();
  }

  // ── Agent search filter ─────────────────────────────────────────────────────
  document.getElementById('agentSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.agent-item').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  // ── Init: style pre-checked rows & count ───────────────────────────────────
  document.querySelectorAll('.agent-item').forEach(row => {
    const cb = row.querySelector('.agent-checkbox');
    if (cb.checked) updateRowStyle(row, true);
  });
  updateSelectedCount();
</script>
@endpush