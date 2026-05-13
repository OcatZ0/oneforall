<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>@yield('title', 'Spica Admin')</title>
  @include('partials._head')
  @stack('styles')
  <style>
        body {
            font-family: 'Segoe UI', sans-serif;
        }

        /* ========== SEMUA SECTION SERAGAM ========== */
        .section-equal {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
            padding-bottom: 80px;
        }

        .hero {
            background: linear-gradient(135deg, #0d6efd, #001f3f);
            color: white;
        }

        .feature-card {
            transition: 0.3s;
            border: none;
            border-radius: 18px;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .dashboard-preview {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stats-box {
            background: #f8f9fa;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
        }

        .section-alt {
            background: #f8f9fa;
        }

        footer {
            background: #001f3f;
            color: white;
            padding: 20px 0;
        }


        .navbar {
            min-height: 70px;
        }


        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body>
  @yield('content')
  @include('partials._scripts')
  @stack('scripts')
</body>

</html>