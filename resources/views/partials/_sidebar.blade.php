<nav class="sidebar sidebar-offcanvas" id="sidebar">
      <ul class="nav">
        <li class="nav-item sidebar-category">
          <p>Navigation</p>
          <span></span>
        </li>
        <li class="nav-item {{ Route::is('home', 'dashboard') ? 'active' : '' }}">
          <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="mdi mdi-view-quilt menu-icon"></i>
            <span class="menu-title">Dashboard</span>
          </a>
        </li>

        @if(Auth::check())
        <li class="nav-item sidebar-category">
          <p>Monitoring Wazuh</p>
          <span></span>
        </li>
        <li class="nav-item {{ Route::is('agent', 'agent.*') ? 'active' : '' }}">
          <a class="nav-link" href="{{ route('agent') }}">
            <i class="mdi mdi-laptop menu-icon"></i>
            <span class="menu-title">Agents</span>
          </a>
        </li>
        @endif

        @if(Auth::check() && Auth::user()->peran === 'admin')
        <li class="nav-item sidebar-category">
          <p>Manajemen Pengguna</p>
          <span></span>
        </li>
        <li class="nav-item {{ Route::is('user', 'user.*', 'edit-user') ? 'active' : '' }}">
          <a class="nav-link" href="{{ route('user') }}">
            <i class="mdi mdi-account menu-icon"></i>
            <span class="menu-title">Pengguna</span>
          </a>
        </li>
        @endif
      </ul>
    </nav>