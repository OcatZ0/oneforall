@extends('layouts.app')

@section('title', 'Pengguna - One For All')

@push('styles')
@include('partials._gridstack-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
@endpush

@section('content')

<div class="grid-stack" id="user-grid">

  {{-- Ringkasan Pengguna --}}
  <div class="grid-stack-item" gs-id="user-stats" data-label="Ringkasan Pengguna" gs-x="0" gs-y="0" gs-w="12" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title mb-3">Ringkasan Pengguna</p>
          <div class="d-flex justify-content-around text-center">
            <div>
              <h3 class="fw-bold mb-0">{{ $userStats['total'] }}</h3>
              <small class="text-muted">Total</small>
            </div>
            <div>
              <h3 class="fw-bold mb-0 text-danger">{{ $userStats['admin'] }}</h3>
              <small class="text-muted">Admin</small>
            </div>
            <div>
              <h3 class="fw-bold mb-0 text-primary">{{ $userStats['customer'] }}</h3>
              <small class="text-muted">Customer</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel Pengguna --}}
  <div class="grid-stack-item" gs-id="user-table" data-label="Tabel Pengguna" gs-x="0" gs-y="4" gs-w="12" gs-h="18">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="card-title mb-0">Pengguna</h4>
            <a href="{{ route('user.create') }}" class="btn btn-sm btn-primary">
              <i class="mdi mdi-plus me-1"></i> Tambah Pengguna
            </a>
          </div>

          <form method="GET" action="{{ route('user') }}" id="filterForm" class="mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="input-group" style="max-width:350px">
                <span class="input-group-text bg-white border-end-0">
                  <i class="mdi mdi-magnify text-muted"></i>
                </span>
                <input type="text" id="searchInput" name="search" class="form-control border-start-0"
                  placeholder="Cari username atau email..."
                  value="{{ request('search') }}">
              </div>
              <select id="roleFilter" name="role" class="form-control form-select" style="width:180px">
                <option value="">Semua Role</option>
                <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
              </select>
              <a href="{{ route('user') }}" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-refresh me-1"></i>Reset
              </a>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th style="width:50px">#</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Peran</th>
                  <th>Total Agent</th>
                  <th>Agents</th>
                  <th>Tanggal Dibuat</th>
                  <th style="width:100px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse($users as $user)
                <tr>
                  <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                  <td class="fw-bold">{{ $user->username }}</td>
                  <td>{{ $user->email }}</td>
                  <td>
                    @if($user->role === 'admin')
                      <span class="badge bg-danger">Admin</span>
                    @elseif($user->role === 'customer')
                      <span class="badge bg-primary">Customer</span>
                    @else
                      <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                    @endif
                  </td>
                  <td><span class="fw-bold">{{ $user->agents()->count() }}</span></td>
                  <td>
                    @php
                      $agents     = $user->agents()->limit(2)->get();
                      $agentCount = $user->agents()->count();
                      $moreCount  = $agentCount - 2;
                    @endphp
                    @if($agentCount > 0)
                      @foreach($agents as $agent)
                        <span class="badge bg-secondary me-1">{{ $agent->name }}</span>
                      @endforeach
                      @if($moreCount > 0)
                        <span class="badge bg-secondary">+{{ $moreCount }}</span>
                      @endif
                    @else
                      <span class="text-muted fst-italic">Tidak ada</span>
                    @endif
                  </td>
                  <td>{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d M Y') }}</td>
                  <td class="text-nowrap">
                    <a href="/user/{{ $user->id }}/edit" class="btn btn-sm btn-outline-primary me-1">
                      <i class="mdi mdi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                      onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->username) }}')">
                      <i class="mdi mdi-delete"></i>
                    </button>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="8" class="text-center py-5 text-muted">
                    <span class="mdi mdi-account-off-outline d-block" style="font-size:2.5rem; opacity:0.35; margin-bottom:8px;"></span>
                    <span class="d-block fw-semibold mb-1">Tidak ada pengguna</span>
                    <span class="d-block small">Coba ubah filter pencarian</span>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if($users->count() > 0)
          <div class="d-flex align-items-center justify-content-between mt-3">
            <div class="d-flex align-items-center">
              <span class="text-muted me-2">Rows per page:</span>
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
            <div>{{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
          </div>
          <div class="text-muted text-sm mt-2">
            Menampilkan {{ ($users->currentPage() - 1) * $users->perPage() + 1 }} hingga
            {{ min($users->currentPage() * $users->perPage(), $users->total()) }} dari {{ $users->total() }} pengguna
          </div>
          @endif

        </div>
      </div>
    </div>
  </div>

</div>{{-- /grid-stack --}}

{{-- Floating pencil --}}
<div id="gs-fab">
  <button id="gs-fab-main" title="Edit layout">
    <i class="mdi mdi-pencil" id="gs-fab-icon"></i>
  </button>
</div>

{{-- Edit toolbar --}}
<div id="gs-edit-toolbar">
  <button class="gs-tb-btn gs-tb-btn-save"   id="gs-save">  <i class="mdi mdi-content-save me-1"></i>Save</button>
  <button class="gs-tb-btn gs-tb-btn-reset"  id="gs-reset"> <i class="mdi mdi-restore me-1"></i>Reset</button>
  <button class="gs-tb-btn gs-tb-btn-cancel" id="gs-cancel"><i class="mdi mdi-close me-1"></i>Cancel</button>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="deleteUserModalLabel">
          <i class="mdi mdi-alert-circle text-danger me-1"></i> Hapus Pengguna
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-3">
        <p class="mb-0">Hapus pengguna <strong id="deleteUserName"></strong>? Semua agent yang ditugaskan akan dilepas. Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">
          <i class="mdi mdi-delete me-1"></i>Hapus
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack-all.js"></script>
<script>
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'user-stats', x: 0, y: 0, w: 12, h: 4  },
    { id: 'user-table', x: 0, y: 4, w: 12, h: 14 },
  ];

  const grid = GridStack.init({
    column: 12,
    cellHeight: 60,
    margin: 8,
    float: false,
    staticGrid: true,
    resizable: { handles: 'se' },
    columnOpts: {
      breakpointForWindow: true,
      breakpoints: [{ w: 768, c: 1 }],
    },
  });

  // ── Hidden cards state ─────────────────────────────────────────────────────
  const hiddenCards    = new Set();
  const hiddenPositions = {}; // id -> { x, y, w, h } — saved original positions

  // In edit mode: toggle greyed class (card stays in grid).
  // exitEdit removes hidden cards from engine; enterEdit restores them at saved pos.
  function setCardHidden(id, hide) {
    const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
    if (!el) return;
    if (hide) {
      const node = el.gridstackNode;
      if (node) hiddenPositions[id] = { x: node.x, y: node.y, w: node.w, h: node.h };
      hiddenCards.add(id);
      el.classList.add('gs-card-hidden');
      const btn = el.querySelector('.gs-hide-btn');
      if (btn) { btn.querySelector('i').className = 'mdi mdi-eye'; btn.title = 'Tampilkan kartu'; }
    } else {
      hiddenCards.delete(id);
      el.classList.remove('gs-card-hidden');
      const btn = el.querySelector('.gs-hide-btn');
      if (btn) { btn.querySelector('i').className = 'mdi mdi-eye-off'; btn.title = 'Sembunyikan kartu'; }
    }
  }

  // Add hide button to every grid item (idempotent)
  function addHideButtons() {
    document.querySelectorAll('.grid-stack-item').forEach(item => {
      if (item.querySelector('.gs-hide-btn')) return;
      const id       = item.getAttribute('gs-id');
      const isHidden = hiddenCards.has(id);
      const btn      = document.createElement('button');
      btn.className  = 'gs-hide-btn';
      btn.title      = isHidden ? 'Tampilkan kartu' : 'Sembunyikan kartu';
      btn.innerHTML  = `<i class="mdi mdi-${isHidden ? 'eye' : 'eye-off'}"></i>`;
      btn.addEventListener('click', e => { e.stopPropagation(); setCardHidden(id, !hiddenCards.has(id)); });
      item.appendChild(btn);
    });
  }

  // ── Load saved layout ──────────────────────────────────────────────────────
  const savedLayout = @json($savedLayout ?? null);
  if (savedLayout && Array.isArray(savedLayout)) {
    // Load all items at their saved positions first
    grid.load(savedLayout.map(i => ({ id: i.id, x: i.x, y: i.y, w: i.w, h: i.h })), false);
    // Then remove hidden ones from engine so visible cards fill the space
    savedLayout.filter(i => i.hidden).forEach(i => {
      hiddenCards.add(i.id);
      hiddenPositions[i.id] = { x: i.x, y: i.y, w: i.w, h: i.h };
      const el = document.querySelector(`.grid-stack-item[gs-id="${i.id}"]`);
      if (!el) return;
      grid.removeWidget(el, false);
      el.style.display = 'none';
    });
  }

  // ── Edit mode ──────────────────────────────────────────────────────────────
  let editMode  = false;
  const fabMain = document.getElementById('gs-fab-main');
  const fabIcon = document.getElementById('gs-fab-icon');
  const toolbar = document.getElementById('gs-edit-toolbar');

  function enterEdit() {
    editMode = true;
    grid.setStatic(false);
    // Restore hidden cards at their saved positions (pushes other cards aside)
    hiddenCards.forEach(id => {
      const el  = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 12, h: 4 };
      el.setAttribute('gs-x', pos.x);
      el.setAttribute('gs-y', pos.y);
      el.setAttribute('gs-w', pos.w);
      el.setAttribute('gs-h', pos.h);
      el.style.display = '';
      grid.makeWidget(el);
      el.classList.add('gs-card-hidden');
    });
    document.body.classList.add('gs-edit-mode');
    fabMain.classList.add('active');
    fabIcon.className = 'mdi mdi-pencil-off';
    toolbar.classList.add('visible');
    addHideButtons();
  }

  function exitEdit() {
    editMode = false;
    // Remove hidden cards from engine — visible cards fill the freed space
    hiddenCards.forEach(id => {
      const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const node = el.gridstackNode;
      if (node) hiddenPositions[id] = { x: node.x, y: node.y, w: node.w, h: node.h };
      el.classList.remove('gs-card-hidden');
      grid.removeWidget(el, false);
      el.style.display = 'none';
    });
    grid.setStatic(true);
    document.body.classList.remove('gs-edit-mode');
    fabMain.classList.remove('active');
    fabIcon.className = 'mdi mdi-pencil';
    toolbar.classList.remove('visible');
  }

  fabMain.addEventListener('click', () => editMode ? exitEdit() : enterEdit());

  document.getElementById('gs-save').addEventListener('click', () => {
    // In edit mode all cards (including hidden) are widgets — save all positions
    const layout = grid.save(false);
    layout.forEach(i => { if (hiddenCards.has(i.id)) i.hidden = true; });
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout, page: 'user' })
    })
    .then(r => r.json())
    .then(d => { if (d.success) { exitEdit(); gsShowSavedToast(); } });
  });

  document.getElementById('gs-reset').addEventListener('click', () => {
    // In edit mode: clear hidden state (all cards are already widgets)
    [...hiddenCards].forEach(id => {
      hiddenCards.delete(id);
      delete hiddenPositions[id];
      const el = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (el) el.classList.remove('gs-card-hidden');
    });
    grid.load(DEFAULT_LAYOUT);
  });

  document.getElementById('gs-cancel').addEventListener('click', () => {
    exitEdit();
    location.reload();
  });

  // ── Search debounce & role filter ─────────────────────────────────────────
  function debounce(fn, ms) {
    let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
  }

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', debounce(() => {
      const form = document.getElementById('filterForm');
      const p = document.createElement('input');
      p.type = 'hidden'; p.name = 'page'; p.value = '1';
      form.appendChild(p);
      form.submit();
    }, 500));
  }

  const roleFilter = document.getElementById('roleFilter');
  if (roleFilter) {
    roleFilter.addEventListener('change', () => {
      const form = document.getElementById('filterForm');
      const p = document.createElement('input');
      p.type = 'hidden'; p.name = 'page'; p.value = '1';
      form.appendChild(p);
      form.submit();
    });
  }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script>
const notyf = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' },
  ripple: false,
  dismissible: true,
});

// Show success toast after page reload post-delete
const _pendingSuccess = sessionStorage.getItem('userDeleteSuccess');
if (_pendingSuccess) {
  sessionStorage.removeItem('userDeleteSuccess');
  notyf.success(_pendingSuccess);
}

let _deleteUserId = null;
const _deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

function openDeleteModal(id, username) {
  _deleteUserId = id;
  document.getElementById('deleteUserName').textContent = username;
  _deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
  if (!_deleteUserId) return;

  const btn = document.getElementById('confirmDeleteBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="mdi mdi-loading mdi-spin me-1"></span>Menghapus...';

  fetch(`/user/${_deleteUserId}`, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  })
  .then(r => r.json())
  .then(data => {
    _deleteModal.hide();
    btn.disabled = false;
    btn.innerHTML = '<i class="mdi mdi-delete me-1"></i>Hapus';

    if (data.success) {
      sessionStorage.setItem('userDeleteSuccess', data.message);
      location.reload();
    } else {
      notyf.error(data.message || 'Gagal menghapus pengguna.');
    }
  })
  .catch(() => {
    _deleteModal.hide();
    btn.disabled = false;
    btn.innerHTML = '<i class="mdi mdi-delete me-1"></i>Hapus';
    notyf.error('Terjadi kesalahan. Coba lagi.');
  });
});
</script>
@endpush
