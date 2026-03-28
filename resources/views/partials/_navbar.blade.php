<nav class="navbar col-lg-12 col-12 px-0 py-0 py-lg-4 d-flex flex-row">
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
          <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button>
          <div class="navbar-brand-wrapper">
            <a class="navbar-brand brand-logo" href="/"><img src="{{ asset('images/logo_dofa.png') }}" alt="DOFA Logo" style="display: block;height: 50px; width: auto;"></a>
            <a class="navbar-brand brand-logo-mini" href="/"><img src="{{ asset('images/logo_dofa.png') }}" alt="DOFA Logo" style="display: block;height: 82px; width: auto;"></a>
          </div>
          <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1">Selamat Datang, {{ session('user') }}</h4>
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item dropdown me-2">
              <a class="nav-link dropdown-toggle d-flex align-items-center justify-content-center gap-2 border rounded px-3" id="profileDropdown" href="#" data-bs-toggle="dropdown">
                <i class="mdi mdi-account-circle mx-0"></i>
                <span>{{ session('user') }}</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="profileDropdown">
                <p class="mb-0 font-weight-normal float-left dropdown-header">Profile</p>
                <a class="dropdown-item preview-item" href="#">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-success">
                      <i class="mdi mdi-account mx-0"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject font-weight-normal">My Profile</h6>
                    <p class="font-weight-light small-text mb-0 text-muted">Account details</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item" href="#">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-warning">
                      <i class="mdi mdi-lock mx-0"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject font-weight-normal">Change Password</h6>
                    <p class="font-weight-light small-text mb-0 text-muted">Security settings</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item" href="logout.php">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-danger">
                      <i class="mdi mdi-logout mx-0"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject font-weight-normal">Logout</h6>
                    <p class="font-weight-light small-text mb-0 text-muted">Sign out</p>
                  </div>
                </a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>