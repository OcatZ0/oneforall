@php $logoutHref = $logoutHref ?? '#'; @endphp
<ul class="navbar-nav navbar-nav-right">
  <li class="nav-item dropdown me-2">
    <a class="nav-link dropdown-toggle d-flex align-items-center justify-content-center gap-2 border rounded px-3" id="profileDropdown" href="#" data-bs-toggle="dropdown">
      <i class="mdi mdi-account-circle mx-0"></i>
      <span>{{ Auth::user()->username }}</span>
    </a>
    <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="profileDropdown">
      <p class="mb-0 fw-normal float-start dropdown-header">Profil</p>
      <a class="dropdown-item preview-item" href="/profile">
        <div class="preview-thumbnail">
          <div class="preview-icon bg-success">
            <i class="mdi mdi-account mx-0"></i>
          </div>
        </div>
        <div class="preview-item-content">
          <h6 class="preview-subject fw-normal">Profil</h6>
          <p class="fw-light small-text mb-0 text-muted">Detail akun</p>
        </div>
      </a>
      <a class="dropdown-item preview-item" href="/auth/forgot-password">
        <div class="preview-thumbnail">
          <div class="preview-icon bg-warning">
            <i class="mdi mdi-lock mx-0"></i>
          </div>
        </div>
        <div class="preview-item-content">
          <h6 class="preview-subject fw-normal">Ubah Kata Sandi</h6>
          <p class="fw-light small-text mb-0 text-muted">Pengaturan keamanan</p>
        </div>
      </a>
      <a href="{{ $logoutHref }}" class="dropdown-item preview-item"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <div class="preview-thumbnail">
          <div class="preview-icon bg-danger">
            <i class="mdi mdi-logout mx-0"></i>
          </div>
        </div>
        <div class="preview-item-content">
          <h6 class="preview-subject fw-normal">Keluar</h6>
          <p class="fw-light small-text mb-0 text-muted">Akhiri sesi</p>
        </div>
      </a>

      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
      </form>
    </div>
  </li>
</ul>
