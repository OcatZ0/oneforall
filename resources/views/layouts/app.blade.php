<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>@yield('title', 'Spica Admin')</title>
  @include('partials._head')
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

  @include('partials._scripts')
  @stack('scripts')
</body>

</html>