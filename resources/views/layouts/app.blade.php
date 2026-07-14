<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>@yield('title', 'Spica Admin')</title>
  @include('partials._head')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>
<body>
  <div class="container-scroller d-flex">

    @include('partials._sidebar')

    <div class="container-fluid page-body-wrapper">
      @include('partials._navbar')
      <div class="main-panel">
        <div class="content-wrapper">
          @yield('content')
        </div>
        @include('partials._footer')
      </div>
    </div>
  </div>

  @include('partials._gs-saved-toast')
  @include('partials._scripts')
  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
  <script>
  const notyf = new Notyf({
    duration: 3000,
    position: { x: 'right', y: 'top' },
    ripple: false,
    dismissible: true,
  });
  </script>
  @include('partials._wazuh-status-notyf')
  @stack('scripts')
</body>

</html>