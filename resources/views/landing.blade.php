<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
                <img src="{{ asset('images/logo_dofa.png') }}" alt="Logo" width="45" height="45">
                <span>Dashboard OneForAll</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#frontoffice">FrontOffice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard">BackOffice</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#benefits">Keunggulan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero section-equal">
        <div class="container">
            <div class="row align-items-center">

                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Security Monitoring Dashboard Berbasis Wazuh
                    </h1>
                    <p class="lead mb-4">
                        Monitor keamanan website secara realtime menggunakan Wazuh Agent dan Dashboard yang modern, aman, dan mudah digunakan.
                    </p>
                </div>

                <div class="col-lg-6 text-center">
                    <img src="{{ asset('images/homepage/walpaper1.jpg') }}"
                        class="img-fluid rounded-4 shadow-lg"
                        alt="Dashboard">
                </div>

            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="section-equal" id="features">
        <div class="container">

            <div class="text-center mb-5">
                <h2 class="section-title">Fitur Utama</h2>
                <p class="text-muted">
                    Solusi monitoring keamanan website yang lengkap
                </p>
            </div>

            <div class="row g-4">

                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="mb-3 text-primary">
                            <i class="bi bi-speedometer2 fs-1"></i>
                        </div>
                        <h4>Wazuh Dashboard</h4>
                        <p class="text-muted">
                            Menampilkan rekap data security website secara realtime dan mudah dipahami.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="mb-3 text-success">
                            <i class="bi bi-hdd-network fs-1"></i>
                        </div>
                        <h4>Wazuh Agent</h4>
                        <p class="text-muted">
                            Agent dipasang pada server website untuk mengambil log dan aktivitas keamanan.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="mb-3 text-danger">
                            <i class="bi bi-shield-check fs-1"></i>
                        </div>
                        <h4>Threat Detection</h4>
                        <p class="text-muted">
                            Deteksi ancaman, malware, brute force, dan aktivitas mencurigakan secara otomatis.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section id="about" class="section-equal section-alt">

        <div class="container">

            <div class="row align-items-center">

                <div class="col-lg-6 text-center">
                    <img src="{{ asset('images/homepage/walpaper3.jpg') }}"
                        class="img-fluid rounded shadow"
                        alt="Application">
                </div>

                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">
                        Tentang Aplikasi
                    </h2>
                    <p class="text-muted">
                        Dashboard OneForAll merupakan platform monitoring keamanan berbasis Wazuh
                        yang membantu perusahaan memonitor aktivitas website, server,
                        dan ancaman keamanan secara realtime.
                    </p>
                    <p class="text-muted">
                        Sistem ini terdiri dari FrontOffice untuk customer dan BackOffice
                        untuk administrator dalam mengelola seluruh agent dan data keamanan.
                    </p>

                    <div class="row mt-4 g-3">
                        <div class="col-6">
                            <div class="bg-primary text-white text-center rounded p-3">
                                <h4 class="fw-bold">24/7</h4>
                                <p class="mb-0">Monitoring</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-success text-white text-center rounded p-3">
                                <h4 class="fw-bold">Realtime</h4>
                                <p class="mb-0">Alert System</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="section-equal" id="frontoffice">
        <div class="container">

            <div class="row align-items-center">

                <div class="col-lg-6">
                    <h2 class="section-title">
                        Website FrontOffice
                    </h2>
                    <p class="text-muted">
                        Customer dapat melihat data security website mereka secara realtime melalui dashboard interaktif.
                    </p>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Monitoring keamanan realtime
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Custom widget dashboard
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Statistik ancaman keamanan
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            Notifikasi dan alert
                        </li>
                    </ul>
                </div>

                <div class="col-lg-6">
                    <div class="dashboard-preview">
                        <img src="{{ asset('images/homepage/walpaper2.jpg') }}" class="img-fluid"
                            alt="FrontOffice">
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="section-equal section-alt" id="dashboard">
        <div class="container">

            <div class="row align-items-center flex-lg-row-reverse">

                <div class="col-lg-6">
                    <h2 class="section-title">
                        Website BackOffice
                    </h2>
                    <p class="text-muted">
                        Admin dapat mengelola seluruh Wazuh Agent dan menentukan agent dimiliki oleh customer tertentu.
                    </p>

                    <div class="row g-3 mt-3">
                        <div class="col-6">
                            <div class="stats-box">
                                <h3 class="fw-bold text-primary">250+</h3>
                                <p class="mb-0">Active Agents</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-box">
                                <h3 class="fw-bold text-danger">99.9%</h3>
                                <p class="mb-0">Threat Detection</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="dashboard-preview">
                        <img src="{{ asset('images/homepage/walpaper4.jpg') }}" class="img-fluid"
                            class="img-fluid"
                            alt="BackOffice">
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="section-equal" id="benefits">
        <div class="container">

            <div class="text-center mb-5">
                <h2 class="section-title">
                    Kenapa Memilih Platform Ini?
                </h2>
            </div>

            <div class="row g-4">

                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm p-4 h-100">
                        <i class="bi bi-lightning-charge-fill fs-1 text-warning"></i>
                        <h5 class="mt-3">Realtime</h5>
                        <p class="text-muted">
                            Data keamanan tampil secara realtime.
                        </p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm p-4 h-100">
                        <i class="bi bi-sliders fs-1 text-primary"></i>
                        <h5 class="mt-3">Customizable</h5>
                        <p class="text-muted">
                            Customer bebas memilih data dashboard.
                        </p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm p-4 h-100">
                        <i class="bi bi-person-lock fs-1 text-success"></i>
                        <h5 class="mt-3">Secure</h5>
                        <p class="text-muted">
                            Keamanan data dan monitoring terjamin.
                        </p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-center border-0 shadow-sm p-4 h-100">
                        <i class="bi bi-cloud-arrow-up-fill fs-1 text-danger"></i>
                        <h5 class="mt-3">Scalable</h5>
                        <p class="text-muted">
                            Mudah dikembangkan untuk banyak client.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <footer class="pt-5 pb-3">
        <div class="container">
            <div class="row mb-4">

                <!-- Kolom 1: Brand & Deskripsi -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold text-white mb-3">
                        <i class="bi bi-shield-lock-fill me-2"></i>Dashboard OneForAll
                    </h5>
                    <p class="text-white-50 small">
                        Platform monitoring keamanan berbasis Wazuh yang membantu memproteksi website dan server Anda dari ancaman siber secara realtime.
                    </p>
                </div>


                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#features" class="text-white-50 text-decoration-none">Fitur Utama</a></li>
                        <li class="mb-2"><a href="#about" class="text-white-50 text-decoration-none">Tentang Aplikasi</a></li>
                        <li class="mb-2"><a href="#frontoffice" class="text-white-50 text-decoration-none">FrontOffice</a></li>
                        <li class="mb-2"><a href="#dashboard" class="text-white-50 text-decoration-none">BackOffice</a></li>
                    </ul>
                </div>


                <div class="col-lg-4">
                    <h5 class="fw-bold text-white mb-3">Hubungi Kami</h5>
                    <ul class="list-unstyled text-white-50 small">
                        <li class="mb-2">
                            <i class="bi bi-envelope-fill me-2"></i> support@oneforall.id
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone-fill me-2"></i> +62
                        </li>
                    </ul>


                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-github"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-white-50 fs-5"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>

            </div>


            <div class="row">
                <div class="col-12">
                    <hr class="border-secondary">
                    <p class="text-center text-white-50 small mb-0">
                        © 2026 Wazuh Monitor System. All Rights Reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>