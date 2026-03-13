<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>@yield('title', 'Spica Admin')</title>
  @include('partials._head')
  @stack('styles')
</head>
<body class="auth-body">
  <div class="auth-wrapper">
    @yield('content')
  </div>
  @include('partials._scripts')
  @stack('scripts')
</body>

</html>