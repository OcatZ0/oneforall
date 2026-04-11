<nav class="sidebar sidebar-offcanvas" id="sidebar">
      <ul class="nav">
        <li class="nav-item sidebar-category">
          <p>Navigation</p>
          <span></span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/">
            <i class="mdi mdi-view-quilt menu-icon"></i>
            <span class="menu-title">Dashboard</span>
          </a>
        </li>

        @if(Auth::check())
        <li class="nav-item sidebar-category">
          <p>Monitoring Wazuh</p>
          <span></span>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/agent">
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
        <li class="nav-item">
          <a class="nav-link" href="/user">
            <i class="mdi mdi-account menu-icon"></i>
            <span class="menu-title">Pengguna</span>
          </a>
        </li>
        @endif
      </ul>
    </nav>