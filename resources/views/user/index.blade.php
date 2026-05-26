@extends('layouts.app')

@section('title', 'Pengguna - One For All')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack.min.css"/>
<style>
  .grid-stack { background: transparent; }
  .grid-stack-item-content { overflow: auto; }

  body.gs-edit-mode .grid-stack-item-content {
    outline: 2px dashed rgba(75, 73, 172, 0.4);
    outline-offset: -2px;
  }
  body.gs-edit-mode .grid-stack {
    background-image: linear-gradient(rgba(75,73,172,.04) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(75,73,172,.04) 1px, transparent 1px);
    background-size: calc(100% / 12) 60px;
  }

  #gs-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
  }
  #gs-fab-main {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #4B49AC;
    color: #fff;
    border: none;
    box-shadow: 0 4px 14px rgba(75,73,172,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    transition: background .2s, transform .15s;
  }
  #gs-fab-main:hover { background: #3b3a8c; transform: scale(1.06); }
  #gs-fab-main.active { background: #e74c3c; }

  #gs-edit-toolbar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9998;
    display: none;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.97);
    padding: 8px 16px;
    border-radius: 32px;
    box-shadow: 0 4px 20px rgba(0,0,0,.18);
    white-space: nowrap;
  }
  #gs-edit-toolbar.visible { display: flex; }

  .gs-tb-btn { padding: 6px 18px; border-radius: 20px; border: none; font-size: 13px; font-weight: 500; cursor: pointer; transition: opacity .15s; }
  .gs-tb-btn:hover { opacity: .82; }
  .gs-tb-btn-save   { background: #27ae60; color: #fff; }
  .gs-tb-btn-reset  { background: #f39c12; color: #fff; }
  .gs-tb-btn-cancel { background: #f0f0f0; color: #333; }

  .gs-card { height: 100%; display: flex; flex-direction: column; }
  .gs-card .card-body { flex: 1; overflow: auto; }

  /* ── Hide card button ── */
  .gs-hide-btn {
    display: none;
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(231,76,60,0.1);
    border: 1px solid rgba(231,76,60,0.35);
    color: #e74c3c;
    font-size: 13px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    z-index: 100;
    transition: background .15s, color .15s, border-color .15s;
    line-height: 1;
  }
  .gs-hide-btn:hover { background: #e74c3c; color: #fff; }
  body.gs-edit-mode .gs-hide-btn { display: flex; }

  /* ── Hidden card state (greyed out in edit mode) ── */
  .gs-card-hidden .grid-stack-item-content {
    opacity: 0.25;
    pointer-events: none;
    filter: grayscale(0.4);
  }
  .gs-card-hidden .gs-hide-btn {
    pointer-events: all;
    background: rgba(39,174,96,0.1);
    border-color: rgba(39,174,96,0.35);
    color: #27ae60;
  }
  .gs-card-hidden .gs-hide-btn:hover {
    background: #27ae60;
    color: #fff;
  }

  @media (max-width: 767px) {
    #gs-fab, #gs-edit-toolbar { display: none !important; }
  }
</style>
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
              <h3 class="font-weight-bold mb-0">{{ $userStats['total'] }}</h3>
              <small class="text-muted">Total</small>
            </div>
            <div>
              <h3 class="font-weight-bold mb-0 text-danger">{{ $userStats['admin'] }}</h3>
              <small class="text-muted">Admin</small>
            </div>
            <div>
              <h3 class="font-weight-bold mb-0 text-primary">{{ $userStats['customer'] }}</h3>
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
              <i class="mdi mdi-plus mr-1"></i> Tambah Pengguna
            </a>
          </div>

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
                <option value="admin"    {{ request('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
              </select>
              <a href="{{ route('user') }}" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-refresh mr-1"></i>Reset
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
                      $agents     = $user->agents()->limit(2)->get();
                      $agentCount = $user->agents()->count();
                      $moreCount  = $agentCount - 2;
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
    .then(d => { if (d.success) exitEdit(); });
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
@endpush
