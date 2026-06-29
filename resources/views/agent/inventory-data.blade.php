@extends('layouts.wazuh')

@section('title', 'Inventory Data - One For All')

@push('styles')
@include('partials._gridstack-styles')
<style>
  .inv-table-card .card-header { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#fff; border-bottom:1px solid #e9ecef; }
  .inv-table-card .card-header .title { font-weight:600; font-size:14px; }
  .inv-table-card .search-row { padding:8px 14px; border-bottom:1px solid #f0f0f0; }
  .inv-table-card table { font-size:12px; margin-bottom:0; }
  .inv-table-card table thead th { font-size:11px; font-weight:600; color:#6c757d; border-bottom:1px solid #dee2e6; padding:6px 10px; white-space:nowrap; }
  .inv-table-card table tbody td { padding:6px 10px; vertical-align:middle; border-bottom:1px solid #f5f5f5; }
  .inv-table-card table tbody tr:hover { background:#f8f9fa; }
  .inv-pagination { display:flex; align-items:center; justify-content:space-between; padding:8px 14px; font-size:12px; border-top:1px solid #f0f0f0; flex-shrink:0; }
  .inv-pagination .page-btns button { border:1px solid #dee2e6; background:#fff; padding:2px 8px; font-size:12px; cursor:pointer; }
  .inv-pagination .page-btns button.active { background:#4B49AC; color:#fff; border-color:#4B49AC; }
  .inv-pagination .page-btns button:disabled { opacity:.4; cursor:default; }
  .inv-loading { text-align:center; padding:24px; color:#aaa; font-size:12px; }
  .inv-empty { text-align:center; padding:24px; color:#aaa; font-size:12px; }
  .per-page-select { border:1px solid #dee2e6; border-radius:4px; font-size:12px; padding:2px 6px; }
  .highlight { background:#fff3cd !important; }
  /* Spec items inside device specs card */
  .spec-item { display:inline-flex; flex-direction:column; min-width:130px; }
  .spec-label { font-size:11px; color:#6c757d; text-transform:uppercase; letter-spacing:.04em; }
  .spec-value { font-weight:600; color:#212529; font-size:13px; }
  /* inv-table-card inside GridStack fills height and scrolls correctly */
  .grid-stack-item-content .inv-table-card {
    margin-bottom: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  .grid-stack-item-content .inv-table-card .table-responsive {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
  }
  .grid-stack-item-content .inv-table-card .card-body {
    flex: 1;
    overflow: auto;
    min-height: 0;
  }
</style>
@endpush

@section('content')

@if(!$agent)
<div class="container-fluid py-5">
  <div class="alert alert-danger d-flex align-items-center gap-3">
    <i class="mdi mdi-alert-circle-outline display-4"></i>
    <div>
      <h5 class="alert-heading mb-1">Agen Tidak Ditemukan</h5>
      <p class="mb-0">Gagal memuat detail agen.</p>
      <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-danger mt-2"><i class="mdi mdi-arrow-left me-1"></i> Kembali ke Agen</a>
    </div>
  </div>
</div>
@else

@include('agent._nav', ['agent' => $agent, 'activeTab' => 'inventory'])

@php
  $hw = $hardware ?? [];
  $os = $osInfo   ?? [];
  $cpu      = $hw['cpu']['name']          ?? ($hw['cpu_name']     ?? 'N/A');
  $cores    = $hw['cpu']['cores']         ?? ($hw['cpu_cores']    ?? 'N/A');
  $ram      = $hw['ram']['total']         ?? ($hw['ram_total']    ?? null);
  $ramMb    = $ram ? number_format($ram / 1024, 2) . ' MB' : 'N/A';
  $board    = $hw['board_serial']         ?? 'N/A';
  $arch     = $os['os']['arch']           ?? ($os['architecture'] ?? 'N/A');
  $osName   = trim(($os['os']['name'] ?? '') . ' ' . ($os['os']['version'] ?? '')) ?: 'N/A';
  $osType   = strtolower($os['os']['platform'] ?? $os['os']['name'] ?? '');
  $hostname = $os['hostname']             ?? 'N/A';
  $lastScan = $os['scan']['time']         ?? ($hw['scan']['time'] ?? null);
  $lastScanFmt = $lastScan ? \Carbon\Carbon::parse($lastScan)->format('M j, Y @ H:i:s') : 'N/A';
  $isWindows = str_contains($osType, 'windows');
@endphp

{{-- GRIDSTACK --}}
<div class="grid-stack" id="inv-grid">

  {{-- DEVICE SPECS --}}
  <div class="grid-stack-item" gs-id="inv-specs" data-label="Spesifikasi Perangkat" gs-x="0" gs-y="0" gs-w="12" gs-h="4">
    <div class="grid-stack-item-content">
      <div class="card inv-table-card">
        <div class="card-header">
          <span class="title">Spesifikasi Perangkat</span>
        </div>
        <div class="card-body d-flex flex-wrap gap-4 align-items-center">
          <div class="spec-item"><span class="spec-label">Inti</span><span class="spec-value">{{ $cores }}</span></div>
          <div class="spec-item"><span class="spec-label">Memori</span><span class="spec-value">{{ $ramMb }}</span></div>
          <div class="spec-item"><span class="spec-label">Arsitektur</span><span class="spec-value">{{ $arch }}</span></div>
          <div class="spec-item"><span class="spec-label">Sistem Operasi</span><span class="spec-value">{{ $osName }}</span></div>
          <div class="spec-item"><span class="spec-label">CPU</span><span class="spec-value">{{ $cpu }}</span></div>
          <div class="spec-item"><span class="spec-label">Nama Host</span><span class="spec-value">{{ $hostname }}</span></div>
          <div class="spec-item"><span class="spec-label">Serial Board</span><span class="spec-value">{{ $board }}</span></div>
          <div class="spec-item"><span class="spec-label">Pemindaian Terakhir</span><span class="spec-value">{{ $lastScanFmt }}</span></div>
        </div>
      </div>
    </div>
  </div>

  {{-- NETWORK INTERFACES --}}
  <div class="grid-stack-item" gs-id="inv-netiface" data-label="Antarmuka Jaringan" gs-x="0" gs-y="4" gs-w="6" gs-h="9">
    <div class="grid-stack-item-content">
      @include('agent._inv-table', [
        'tableId'  => 'tbl-netiface',
        'type'     => 'netiface',
        'title'    => 'Antarmuka jaringan',
        'columns'  => ['Nama','MAC','Status','MTU','Tipe'],
        'fields'   => ['name','mac','state','mtu','type'],
      ])
    </div>
  </div>

  {{-- NETWORK PORTS --}}
  <div class="grid-stack-item" gs-id="inv-ports" data-label="Port Jaringan" gs-x="6" gs-y="4" gs-w="6" gs-h="9">
    <div class="grid-stack-item-content">
      @include('agent._inv-table', [
        'tableId'  => 'tbl-ports',
        'type'     => 'ports',
        'title'    => 'Port jaringan',
        'columns'  => ['Port lokal','IP lokal','Proses','Status','Protokol'],
        'fields'   => ['local_port','local_ip','process','state','protocol'],
      ])
    </div>
  </div>

  {{-- NETWORK SETTINGS --}}
  <div class="grid-stack-item" gs-id="inv-netaddr" data-label="Pengaturan Jaringan" gs-x="0" gs-y="13" gs-w="{{ $isWindows ? 8 : 12 }}" gs-h="9">
    <div class="grid-stack-item-content">
      @include('agent._inv-table', [
        'tableId'  => 'tbl-netaddr',
        'type'     => 'netaddr',
        'title'    => 'Pengaturan jaringan',
        'columns'  => ['Antarmuka','Alamat','Netmask','Protokol','Broadcast'],
        'fields'   => ['iface','address','netmask','proto','broadcast'],
      ])
    </div>
  </div>

  @if($isWindows)
  {{-- WINDOWS UPDATES --}}
  <div class="grid-stack-item" gs-id="inv-hotfixes" data-label="Windows Updates" gs-x="8" gs-y="13" gs-w="4" gs-h="9">
    <div class="grid-stack-item-content">
      @include('agent._inv-table', [
        'tableId'  => 'tbl-hotfixes',
        'type'     => 'hotfixes',
        'title'    => 'Pembaruan Windows',
        'columns'  => ['Kode pembaruan'],
        'fields'   => ['hotfix'],
      ])
    </div>
  </div>
  @endif

  {{-- PACKAGES --}}
  <div class="grid-stack-item" gs-id="inv-packages" data-label="Paket" gs-x="0" gs-y="22" gs-w="12" gs-h="10">
    <div class="grid-stack-item-content">
      @include('agent._inv-table', [
        'tableId'  => 'tbl-packages',
        'type'     => 'packages',
        'title'    => 'Paket',
        'columns'  => ['Nama','Arsitektur','Versi','Vendor'],
        'fields'   => ['name','architecture','version','vendor'],
      ])
    </div>
  </div>

  {{-- PROCESSES --}}
  <div class="grid-stack-item" gs-id="inv-processes" data-label="Proses" gs-x="0" gs-y="32" gs-w="12" gs-h="10">
    <div class="grid-stack-item-content">
      @include('agent._inv-table', [
        'tableId'  => 'tbl-processes',
        'type'     => 'processes',
        'title'    => 'Proses',
        'columns'  => ['Nama','PID','PID induk','Ukuran VM','Prioritas','NLWP','Perintah'],
        'fields'   => ['name','pid','ppid','vm_size','priority','nlwp','cmd'],
      ])
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

@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack-all.js"></script>
<script>
const INVENTORY_BASE = '{{ route("agent.inventory.data", [$agent->agent_id ?? 0, "__TYPE__"]) }}';

// state per table: { page, perPage, search }
const tableState = {};

function invEndpoint(type) {
  return INVENTORY_BASE.replace('__TYPE__', type);
}

function loadTable(tableId, type) {
  const s     = tableState[tableId];
  const tbody = document.querySelector(`#${tableId} tbody`);
  if (!tbody) return;

  tbody.innerHTML = '<tr><td colspan="20" class="inv-loading"><span class="mdi mdi-loading mdi-spin me-1"></span>Memuat...</td></tr>';

  const params = new URLSearchParams({ page: s.page, per_page: s.perPage, search: s.search || '' });
  fetch(`${invEndpoint(type)}?${params}`)
    .then(r => r.json())
    .then(data => {
      renderRows(tableId, type, data.data);
      renderFooter(tableId, type, data.total, data.page, data.perPage);
    })
    .catch(() => {
      tbody.innerHTML = '<tr><td colspan="20" class="inv-empty text-danger">Gagal memuat data.</td></tr>';
    });
}

function renderRows(tableId, type, rows) {
  const tbody = document.querySelector(`#${tableId} tbody`);
  if (!rows || rows.length === 0) {
    tbody.innerHTML = '<tr><td colspan="20" class="inv-empty">Tidak ada data.</td></tr>';
    return;
  }

  const fieldMap = {
    'tbl-netiface':  ['name','mac','state','mtu','type'],
    'tbl-ports':     ['local_port','local_ip','process','state','protocol'],
    'tbl-netaddr':   ['iface','address','netmask','proto','broadcast'],
    'tbl-hotfixes':  ['hotfix'],
    'tbl-packages':  ['name','architecture','version','vendor'],
    'tbl-processes': ['name','pid','ppid','vm_size','priority','nlwp','cmd'],
  };
  const fields = fieldMap[tableId] || [];
  const search = (tableState[tableId]?.search || '').toLowerCase();

  tbody.innerHTML = rows.map(row => {
    const cells = fields.map(f => {
      let val = row[f] ?? '';
      if (typeof val === 'object') val = JSON.stringify(val);
      val = String(val);
      if (search && val.toLowerCase().includes(search)) {
        val = val.replace(new RegExp(`(${search.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'), '<mark>$1</mark>');
      }
      return `<td>${val || '<span class="text-muted">-</span>'}</td>`;
    }).join('');
    return `<tr>${cells}</tr>`;
  }).join('');
}

function renderFooter(tableId, type, total, page, perPage) {
  const footer = document.querySelector(`#${tableId}-footer`);
  if (!footer) return;

  const totalPages = Math.max(1, Math.ceil(total / perPage));
  const from = total > 0 ? (page - 1) * perPage + 1 : 0;
  const to   = Math.min(page * perPage, total);

  let pages = [];
  for (let i = Math.max(1, page - 3); i <= Math.min(totalPages, page + 3); i++) pages.push(i);

  const pageBtns = pages.map(p =>
    `<button class="${p === page ? 'active' : ''}" onclick="goPage('${tableId}','${type}',${p})">${p}</button>`
  ).join('');

  footer.innerHTML = `
    <div class="d-flex align-items-center gap-1">
      <span>Baris per halaman:</span>
      <select class="per-page-select" onchange="changePerPage('${tableId}','${type}',this.value)">
        ${[10,25,50].map(n => `<option value="${n}" ${n==perPage?'selected':''}>${n}</option>`).join('')}
      </select>
    </div>
    <div class="page-btns d-flex align-items-center gap-1">
      <button ${page===1?'disabled':''} onclick="goPage('${tableId}','${type}',${page-1})">&#8249;</button>
      ${pageBtns}
      <button ${page===totalPages?'disabled':''} onclick="goPage('${tableId}','${type}',${page+1})">&#8250;</button>
    </div>`;
}

function goPage(tableId, type, page) {
  tableState[tableId].page = page;
  loadTable(tableId, type);
}

function changePerPage(tableId, type, perPage) {
  tableState[tableId].perPage = parseInt(perPage);
  tableState[tableId].page   = 1;
  loadTable(tableId, type);
}

function debounce(fn, ms) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

function initTable(tableId, type) {
  tableState[tableId] = { page: 1, perPage: 10, search: '' };

  const searchEl = document.getElementById(`${tableId}-search`);
  if (searchEl) {
    searchEl.addEventListener('input', debounce(() => {
      tableState[tableId].search = searchEl.value.trim();
      tableState[tableId].page   = 1;
      loadTable(tableId, type);
    }, 400));
  }

  loadTable(tableId, type);
}

document.addEventListener('DOMContentLoaded', () => {
  initTable('tbl-netiface',  'netiface');
  initTable('tbl-ports',     'ports');
  initTable('tbl-netaddr',   'netaddr');
  initTable('tbl-packages',  'packages');
  initTable('tbl-processes', 'processes');
  @if($isWindows)
  initTable('tbl-hotfixes',  'hotfixes');
  @endif
});

// ── GridStack ─────────────────────────────────────────────────────────────────
(function () {
  const DEFAULT_LAYOUT = [
    { id: 'inv-specs',     x: 0, y: 0,  w: 12, h: 4  },
    { id: 'inv-netiface',  x: 0, y: 4,  w: 6,  h: 9  },
    { id: 'inv-ports',     x: 6, y: 4,  w: 6,  h: 9  },
    { id: 'inv-netaddr',   x: 0, y: 13, w: {{ $isWindows ? 8 : 12 }}, h: 9 },
    @if($isWindows)
    { id: 'inv-hotfixes',  x: 8, y: 13, w: 4,  h: 9  },
    @endif
    { id: 'inv-packages',  x: 0, y: 22, w: 12, h: 10 },
    { id: 'inv-processes', x: 0, y: 32, w: 12, h: 10 },
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

  // ── Hidden cards state ──────────────────────────────────────────────────
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
      if (btn) { btn.querySelector('i').className = 'mdi mdi-eye'; btn.title = 'Show card'; }
    } else {
      hiddenCards.delete(id);
      el.classList.remove('gs-card-hidden');
      const btn = el.querySelector('.gs-hide-btn');
      if (btn) { btn.querySelector('i').className = 'mdi mdi-eye-off'; btn.title = 'Hide card'; }
    }
  }

  function addHideButtons() {
    document.querySelectorAll('.grid-stack-item').forEach(item => {
      if (item.querySelector('.gs-hide-btn')) return;
      const id       = item.getAttribute('gs-id');
      const isHidden = hiddenCards.has(id);
      const btn      = document.createElement('button');
      btn.className  = 'gs-hide-btn';
      btn.title      = isHidden ? 'Show card' : 'Hide card';
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

  // ── Load saved layout ───────────────────────────────────────────────────
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

  // ── Edit mode ────────────────────────────────────────────────────────────
  let editMode      = false;
  let editStartCols = null;
  const fabMain = document.getElementById('gs-fab-main');
  const fabIcon = document.getElementById('gs-fab-icon');
  const toolbar = document.getElementById('gs-edit-toolbar');

  function enterEdit() {
    editMode = true;
    if (!isMobileLayout) {
      editStartCols = grid.getColumn();
      grid.setStatic(false);
      grid.enableMove(true);
      grid.enableResize(true);
    }
    hiddenCards.forEach(id => {
      const el  = document.querySelector(`.grid-stack-item[gs-id="${id}"]`);
      if (!el) return;
      const pos = hiddenPositions[id] || { x: 0, y: 0, w: 6, h: 9 };
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
      body: JSON.stringify({ layout: { items }, page: isMobileLayout ? 'inventory-data-mobile' : 'inventory-data' })
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
@endpush
