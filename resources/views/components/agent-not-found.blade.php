@props(['message' => 'Gagal memuat detail agen. Agen mungkin sudah tidak ada atau akses ditolak.'])

<div class="container-fluid py-5">
  <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
    <i class="mdi mdi-alert-circle-outline display-4"></i>
    <div>
      <h5 class="alert-heading mb-1">Agen Tidak Ditemukan</h5>
      <p class="mb-0">{{ $message }}</p>
      <a href="{{ route('agent') }}" class="btn btn-sm btn-outline-danger mt-2">
        <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Agen
      </a>
    </div>
  </div>
</div>
