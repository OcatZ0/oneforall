<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>@yield('title', 'Spica Admin')</title>
  @include('partials._head')
</head>
<body class="bg-light">
  <div class="container-scroller d-flex">

    @include('partials._sidebar')

    <div class="container-fluid page-body-wrapper">
      @include('partials._topbar-wazuh', ['agent' => $agent ?? null])
      <div class="main-panel">
        @yield('content')
      </div>
    </div>
  </div>

  {{-- GridStack save toast --}}
  <div id="gs-saved-toast" aria-live="polite" style="
    position:fixed; bottom:88px; left:50%; transform:translateX(-50%);
    z-index:10000; display:none; align-items:center; gap:8px;
    background:#27ae60; color:#fff; padding:10px 20px;
    border-radius:24px; box-shadow:0 4px 16px rgba(0,0,0,.18);
    font-size:13px; font-weight:500; white-space:nowrap; pointer-events:none;">
    <span class="mdi mdi-check-circle"></span> Tata letak disimpan
  </div>
  <script>
  function gsShowSavedToast() {
    const t = document.getElementById('gs-saved-toast');
    if (!t) return;
    t.style.display = 'flex';
    clearTimeout(t._hideTimer);
    t._hideTimer = setTimeout(() => { t.style.display = 'none'; }, 2000);
  }
  </script>
  @include('partials._scripts')
  @stack('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof bootstrap === 'undefined' || !bootstrap.Dropdown) return;
    document.querySelectorAll('.nav .dropdown-toggle').forEach(function (el) {
      var existing = bootstrap.Dropdown.getInstance(el);
      if (existing) existing.dispose();
      new bootstrap.Dropdown(el, {
        popperConfig: function (defaultConfig) {
          return Object.assign({}, defaultConfig, { strategy: 'fixed' });
        }
      });
    });
  });
  </script>
</body>

</html>