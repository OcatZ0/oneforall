@extends('layouts.app')

@section('title', 'Log Aktivitas - One For All')

@push('styles')
@include('partials._gridstack-styles')
@endpush

@section('content')

<div class="grid-stack" id="actlog-grid">

  {{-- Filter --}}
  <div class="grid-stack-item" gs-id="actlog-filter" data-label="Filter" gs-x="0" gs-y="0" gs-w="12" gs-h="3">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">
          <p class="card-title mb-3">Filter Log Aktivitas</p>
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
    </div>
  </div>

  {{-- Log Table --}}
  <div class="grid-stack-item" gs-id="actlog-table" data-label="Tabel Log Aktivitas" gs-x="0" gs-y="5" gs-w="12" gs-h="19">
    <div class="grid-stack-item-content">
      <div class="card gs-card">
        <div class="card-body">

          <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <h4 class="card-title mb-0">Log Aktivitas</h4>
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

          <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead>
                <tr>
                  <th style="width:50px">#</th>
                  <th>Pengguna</th>
                  <th>Aktivitas</th>
                  <th class="text-nowrap">Waktu</th>
                </tr>
              </thead>
              <tbody>
                @forelse($logs as $log)
                @php
                  $activity = strtolower($log->activity);
                  if      (str_contains($activity, 'login'))    { $bgColor = 'success'; }
                  elseif  (str_contains($activity, 'logout'))   { $bgColor = 'warning'; }
                  elseif  (str_contains($activity, 'password')) { $bgColor = 'danger';  }
                  else                                          { $bgColor = 'secondary'; }

                  $peranBadge = match($log->user->role ?? '') {
                    'admin'    => 'bg-danger',
                    'customer' => 'bg-primary',
                    default    => 'bg-secondary',
                  };
                @endphp
                <tr>
                  <td>{{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}</td>
                  <td>
                    @if($log->user)
                      <span class="fw-semibold me-1">{{ $log->user->username }}</span>
                      <span class="badge {{ $peranBadge }}">{{ ucfirst($log->user->role ?? '-') }}</span>
                    @else
                      <span class="text-muted fst-italic">Pengguna dihapus</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-{{ $bgColor }} text-white fw-normal">
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
          <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
            <div class="text-muted small">
              Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}</strong> &ndash; <strong>{{ $logs->lastItem() ?? 0 }}</strong>
              dari <strong>{{ $logs->total() }}</strong> record
            </div>
            <div>{{ $logs->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
          </div>
          @else
          <div class="text-muted small mt-3">
            @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
              {{ $logs->total() }} record ditemukan
            @endif
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
    { id: 'actlog-filter', x: 0, y: 0, w: 12, h: 5  },
    { id: 'actlog-table',  x: 0, y: 5, w: 12, h: 19 },
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

  const hiddenCards     = new Set();
  const hiddenPositions = {};

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

  const savedLayout = @json($savedLayout ?? null);
  if (savedLayout && Array.isArray(savedLayout)) {
    grid.load(savedLayout.map(i => ({ id: i.id, x: i.x, y: i.y, w: i.w, h: i.h })), false);
    savedLayout.filter(i => i.hidden).forEach(i => {
      hiddenCards.add(i.id);
      hiddenPositions[i.id] = { x: i.x, y: i.y, w: i.w, h: i.h };
      const el = document.querySelector(`.grid-stack-item[gs-id="${i.id}"]`);
      if (!el) return;
      grid.removeWidget(el, false);
      el.style.display = 'none';
    });
  }

  let editMode  = false;
  const fabMain = document.getElementById('gs-fab-main');
  const fabIcon = document.getElementById('gs-fab-icon');
  const toolbar = document.getElementById('gs-edit-toolbar');

  function enterEdit() {
    editMode = true;
    grid.setStatic(false);
    hiddenCards.forEach(id => {
      const el  = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 12, h: 5 };
      el.setAttribute('gs-x', pos.x); el.setAttribute('gs-y', pos.y);
      el.setAttribute('gs-w', pos.w); el.setAttribute('gs-h', pos.h);
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
    const layout = grid.save(false);
    layout.forEach(i => { if (hiddenCards.has(i.id)) i.hidden = true; });
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout, page: 'activity-log' })
    })
    .then(r => r.json())
    .then(d => { if (d.success) { exitEdit(); gsShowSavedToast(); } });
  });

  document.getElementById('gs-reset').addEventListener('click', () => {
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
})();
</script>
@endpush
