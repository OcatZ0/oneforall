<div class="card inv-table-card">
  <div class="card-header">
    <span class="title">{{ $title }} <span id="{{ $tableId }}-count" class="text-muted fw-normal small"></span></span>
  </div>
  <div class="search-row">
    <input type="text" id="{{ $tableId }}-search" class="form-control form-control-sm" placeholder="Cari...">
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0" id="{{ $tableId }}">
      <thead>
        <tr>
          @foreach($columns as $col)
            <th>{{ $col }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="{{ count($columns) }}" class="inv-loading"><span class="mdi mdi-loading mdi-spin me-1"></span>Memuat...</td></tr>
      </tbody>
    </table>
  </div>
  <div class="inv-pagination" id="{{ $tableId }}-footer"></div>
</div>
