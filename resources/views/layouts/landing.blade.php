<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Landing Page - One For All')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>