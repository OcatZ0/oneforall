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
          <form id="filterForm" onsubmit="event.preventDefault(); loadLogs(1, logsPerPage);">
            <div class="row g-2 align-items-end">
              <div class="col-12 col-md-4">
                <label class="form-label small text-muted mb-1">Cari Aktivitas</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="mdi mdi-magnify text-muted"></i>
                  </span>
                  <input type="text" id="searchInput" name="search" class="form-control border-start-0"
                    placeholder="Cari kata kunci aktivitas..."
                    value="{{ $search }}">
                </div>
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">Pengguna</label>
                <select name="user_id" id="userFilter" class="form-control form-select">
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
                <input type="date" id="dateFromInput" name="date_from" class="form-control" value="{{ $dateFrom }}">
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" id="dateToInput" name="date_to" class="form-control" value="{{ $dateTo }}">
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
              <span class="text-muted small">Baris per halaman:</span>
              <select name="per_page" class="form-control form-select" style="width:90px"
                onchange="loadLogs(1, +this.value)">
                <option value="25"  {{ $perPage == 25  ? 'selected' : '' }}>25</option>
                <option value="50"  {{ $perPage == 50  ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
              </select>
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
              <tbody id="actlog-tbody">
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

          <div id="actlog-pagination-footer"></div>

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
  <button class="gs-tb-btn gs-tb-btn-save"   id="gs-save">  <i class="mdi mdi-content-save me-1"></i>Simpan</button>
  <button class="gs-tb-btn gs-tb-btn-reset"  id="gs-reset"> <i class="mdi mdi-restore me-1"></i>Reset</button>
  <button class="gs-tb-btn gs-tb-btn-cancel" id="gs-cancel"><i class="mdi mdi-close me-1"></i>Batal</button>
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
    draggable: { handle: '.gs-drag-handle' },
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
      if (!item.querySelector('.gs-drag-handle')) {
        const dragHandle = document.createElement('div');
        dragHandle.className = 'gs-drag-handle';
        dragHandle.title = 'Seret untuk memindahkan';
        dragHandle.innerHTML = '<i class="mdi mdi-drag"></i>';
        item.appendChild(dragHandle);
      }
    });
  }

  const isMobileLayout = window.innerWidth <= 768;
  const savedLayout = isMobileLayout
    ? @json($savedLayoutMobile ?? null)
    : @json($savedLayout ?? null);

  function applyLoadedLayout() {
    if (!savedLayout || Array.isArray(savedLayout) || !savedLayout.items) return;
    const items = savedLayout.items ?? [];
    if (isMobileLayout) {
      grid.batchUpdate();
      [...items].sort((a, b) => a.y - b.y).forEach(item => {
        const el = document.querySelector(`.grid-stack-item[gs-id="${item.id}"]`);
        if (el && el.gridstackNode) grid.update(el, { x: 0, y: item.y, w: 1, h: item.h });
      });
      grid.batchUpdate(false);
    } else {
      grid.load(items.map(i => ({ id: i.id, x: i.x, y: i.y, w: i.w, h: i.h })), false);
    }
    items.filter(i => i.hidden).forEach(i => {
      hiddenCards.add(i.id);
      hiddenPositions[i.id] = { x: i.x, y: i.y, w: i.w, h: i.h };
      const el = document.querySelector(`.grid-stack-item[gs-id="${i.id}"]`);
      if (!el) return;
      grid.removeWidget(el, false);
      el.style.display = 'none';
    });
  }
  requestAnimationFrame(applyLoadedLayout);

  let editMode      = false;
  let editStartCols = null;
  const fabMain = document.getElementById('gs-fab-main');
  const fabIcon = document.getElementById('gs-fab-icon');
  const toolbar = document.getElementById('gs-edit-toolbar');

  function enterEdit() {
    editMode = true;
    if (!isMobileLayout) {
      editStartCols = grid.getColumn();
      grid.enableResize(true);
    }
    grid.setStatic(false);
    grid.enableMove(true);
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
    grid.enableMove(false);
    grid.enableResize(false);
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
    if (!isMobileLayout && grid.getColumn() !== editStartCols) {
      gsShowErrorToast('Ukuran layar berubah saat edit. Halaman akan dimuat ulang.');
      setTimeout(() => { exitEdit(); location.reload(); }, 2500);
      return;
    }
    let items;
    if (isMobileLayout) {
      let yPos = 0;
      items = [...document.querySelectorAll('.grid-stack-item')]
        .filter(el => el.gridstackNode)
        .sort((a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top)
        .map(el => {
          const id = el.getAttribute('gs-id');
          const h  = el.gridstackNode.h || parseInt(el.getAttribute('gs-h') || '4');
          const item = { id, x: 0, y: yPos, w: 1, h };
          yPos += h;
          return item;
        });
    } else {
      items = grid.save(false).map(({ id, x, y, w, h }) => ({ id, x, y, w, h }));
    }
    items.forEach(i => { if (hiddenCards.has(i.id)) i.hidden = true; });
    fetch('{{ route("dashboard.layout") }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ layout: { items }, page: isMobileLayout ? 'activity-log-mobile' : 'activity-log' })
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

  let _resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(_resizeTimer);
    _resizeTimer = setTimeout(() => {
      if (editMode && window.innerWidth > 768) {
        grid.enableMove(true);
        grid.enableResize(true);
      }
    }, 150);
  });
})();
</script>

<script>
// ── AJAX filter & pagination ───────────────────────────────────────────────────
const logsEndpoint = '{{ route("activity-log.search") }}';
let logsPage    = {{ (int) request('page', 1) }};
let logsPerPage = {{ $perPage }};

const initialLogsData = {
  total:      {{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $logs->total() : 0 }},
  page:       {{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $logs->currentPage() : 1 }},
  perPage:    {{ $perPage }},
  totalPages: {{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $logs->lastPage() : 1 }},
  from:       {{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($logs->firstItem() ?? 0) : 0 }},
  to:         {{ $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($logs->lastItem() ?? 0) : 0 }},
};

function escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s != null ? String(s) : '';
  return d.innerHTML;
}

function activityBadgeColor(activity) {
  const a = (activity || '').toLowerCase();
  if (a.includes('login'))    return 'success';
  if (a.includes('logout'))   return 'warning';
  if (a.includes('password')) return 'danger';
  return 'secondary';
}

function roleBadgeClass(role) {
  return { admin: 'bg-danger', customer: 'bg-primary' }[role] || 'bg-secondary';
}

function renderLogsPagination(data) {
  if (!data.total) return `<div class="text-muted small mt-3">0 record ditemukan</div>`;

  const { total, page, perPage, totalPages, from, to } = data;

  if (total <= perPage) {
    return `<div class="text-muted small mt-3">${total} record ditemukan</div>`;
  }

  const btn = (p, label, disabled, active) =>
    `<button ${disabled ? 'disabled' : `onclick="loadLogs(${p}, ${perPage})"`} class="btn btn-sm py-0 px-2 ${active ? 'btn-primary' : 'btn-outline-secondary'}${disabled ? ' disabled' : ''}">${label}</button>`;

  const winBtns = [];
  for (let p = Math.max(1, page - 2); p <= Math.min(totalPages, page + 2); p++) {
    winBtns.push(btn(p, p, false, p === page));
  }

  return `
    <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
      <div class="text-muted small">
        Menampilkan <strong>${from}</strong> &ndash; <strong>${to}</strong>
        dari <strong>${total}</strong> record
      </div>
      <div class="d-flex gap-1 flex-wrap">
        ${btn(1,              '«', page <= 1,          false)}
        ${btn(Math.max(1, page - 1), '‹', page <= 1,  false)}
        ${winBtns.join('')}
        ${btn(Math.min(totalPages, page + 1), '›', page >= totalPages, false)}
        ${btn(totalPages,     '»', page >= totalPages, false)}
      </div>
    </div>`;
}

async function loadLogs(page, perPage) {
  logsPage    = page    || logsPage;
  logsPerPage = +perPage || logsPerPage;

  const search   = document.getElementById('searchInput')?.value   || '';
  const userId   = document.getElementById('userFilter')?.value    || '';
  const dateFrom = document.getElementById('dateFromInput')?.value || '';
  const dateTo   = document.getElementById('dateToInput')?.value   || '';

  const params = new URLSearchParams({ page: logsPage, per_page: logsPerPage });
  if (search)   params.set('search',    search);
  if (userId)   params.set('user_id',   userId);
  if (dateFrom) params.set('date_from', dateFrom);
  if (dateTo)   params.set('date_to',   dateTo);


  const tbody  = document.getElementById('actlog-tbody');
  const footer = document.getElementById('actlog-pagination-footer');
  if (tbody) tbody.style.opacity = '0.5';

  try {
    const res  = await fetch(`${logsEndpoint}?${params}`, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    if (data.error) throw new Error(data.error);

    if (tbody) {
      if (!data.logs || data.logs.length === 0) {
        tbody.innerHTML = `<tr>
          <td colspan="4" class="text-center py-5 text-muted">
            <span class="mdi mdi-history d-block" style="font-size:2.5rem;opacity:.35;margin-bottom:8px;"></span>
            <span class="d-block fw-semibold mb-1">Belum ada aktivitas</span>
            <span class="d-block small">Log aktivitas akan muncul di sini setelah ada tindakan pengguna</span>
          </td>
        </tr>`;
      } else {
        tbody.innerHTML = data.logs.map((log, i) => {
          const rowNum    = (logsPage - 1) * logsPerPage + i + 1;
          const color     = activityBadgeColor(log.activity);
          const userCell  = log.username
            ? `<span class="fw-semibold me-1">${escHtml(log.username)}</span><span class="badge ${roleBadgeClass(log.role)}">${escHtml(log.role ? log.role.charAt(0).toUpperCase() + log.role.slice(1) : '-')}</span>`
            : `<span class="text-muted fst-italic">Pengguna dihapus</span>`;
          const timeCell  = log.created_at_human
            ? `<span title="${escHtml(log.created_at_formatted)}">${escHtml(log.created_at_human)}</span>`
            : `<span class="text-muted">-</span>`;
          return `<tr>
            <td>${rowNum}</td>
            <td>${userCell}</td>
            <td><span class="badge bg-${color} text-white fw-normal">${escHtml(log.activity)}</span></td>
            <td class="text-nowrap">${timeCell}</td>
          </tr>`;
        }).join('');
      }
      tbody.style.opacity = '1';
    }

    if (footer) footer.innerHTML = renderLogsPagination(data);

  } catch (e) {
    console.error('loadLogs error', e);
    if (tbody) tbody.style.opacity = '1';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const footer = document.getElementById('actlog-pagination-footer');
  if (footer) footer.innerHTML = renderLogsPagination(initialLogsData);

  function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

  const searchInput  = document.getElementById('searchInput');
  const userFilter   = document.getElementById('userFilter');
  const dateFromInput = document.getElementById('dateFromInput');
  const dateToInput   = document.getElementById('dateToInput');

  if (searchInput)   searchInput.addEventListener('input',   debounce(() => loadLogs(1, logsPerPage), 400));
  if (userFilter)    userFilter.addEventListener('change',   () => loadLogs(1, logsPerPage));
  if (dateFromInput) dateFromInput.addEventListener('change', () => loadLogs(1, logsPerPage));
  if (dateToInput)   dateToInput.addEventListener('change',   () => loadLogs(1, logsPerPage));
});
</script>
@endpush
