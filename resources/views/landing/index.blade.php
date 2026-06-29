@extends('layouts.landing')

@section('title', 'One For All - Security Monitoring Dashboard')

@section('content')

{{-- ══════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════ --}}
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">
      <div class="brand-icon">
        <i class="bi bi-shield-shaded" style="color:#fff;"></i>
      </div>
      <span>OneForAll</span>
    </a>

    <button class="navbar-toggler" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#navDrawer"
            aria-controls="navDrawer">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="d-none d-lg-flex align-items-center ms-auto gap-1">
      <a class="nav-link" href="#features">Fitur</a>
      <a class="nav-link" href="#about">Tentang</a>
      <a class="nav-link" href="#benefits">Keunggulan</a>
      <a href="/auth/login" class="btn-primary-custom ms-3" style="padding:10px 22px;font-size:0.83rem;">
        Mulai Sekarang <i class="bi bi-arrow-right"></i>
      </a>
    </div>
  </div>
</nav>

{{-- Right-side drawer (mobile) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="navDrawer" aria-labelledby="navDrawerLabel" data-bs-scroll="true">
  <div class="offcanvas-header">
    <a class="navbar-brand" href="#" style="margin:0;" id="navDrawerLabel">
      <div class="brand-icon" style="width:32px;height:32px;font-size:0.9rem;">
        <i class="bi bi-shield-shaded" style="color:#fff;"></i>
      </div>
      <span>OneForAll</span>
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <nav class="drawer-nav">
      <a href="#features">Fitur</a>
      <a href="#about">Tentang</a>
      <a href="#benefits">Keunggulan</a>
    </nav>
    <a href="/auth/login" class="btn-primary-custom drawer-cta">
      Mulai Sekarang <i class="bi bi-arrow-right"></i>
    </a>
  </div>
</div>


{{-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ --}}
<section id="hero" style="min-height:100vh; display:flex; align-items:center; padding-top:72px;">

  {{-- Orbs --}}
  <div class="orb" style="width:500px;height:500px;background:radial-gradient(circle,rgba(0,78,146,0.1),transparent 70%);top:-100px;left:-150px;"></div>
  <div class="orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(30,144,255,0.08),transparent 70%);top:20%;right:-100px;"></div>

  <div class="container position-relative z-1 pt-3">
    <div class="row align-items-center g-5">

      {{-- Left --}}
      <div class="col-lg-6">
        <div class="stat-pill mb-4 anim-1">
          <span class="dot"></span>
          System Active · Wazuh
        </div>

        <h1 class="anim-2" style="font-family:var(--font-display);font-weight:700;font-size:clamp(2.2rem,5vw,3.6rem);line-height:1.1;letter-spacing:-1px;margin-bottom:24px;color:var(--clr-text);">
          Security Monitoring<br>
          <span style="background:linear-gradient(90deg,var(--clr-accent2),var(--clr-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-weight:700;">
            Berbasis Wazuh
          </span>
        </h1>

        <p class="anim-3" style="color:var(--clr-muted);font-size:1.05rem;line-height:1.7;max-width:480px;margin-bottom:36px;">
          Monitor keamanan website secara realtime menggunakan Wazuh Agent dan Dashboard
          yang modern, aman, serta mudah digunakan untuk tim Anda.
        </p>

        <div class="d-flex flex-wrap gap-3 anim-4">
          <a href="/auth/login" class="btn-primary-custom">
            Lihat Dashboard <i class="bi bi-arrow-right"></i>
          </a>
          <a href="#about" class="btn-outline-custom">
            <i class="bi bi-play-circle"></i> Pelajari Lebih Lanjut
          </a>
        </div>

        {{-- Mini stats --}}
        <div class="d-flex flex-wrap gap-4 mt-5 anim-4">
          <div style="width:1px;background:var(--clr-border-strong);"></div>
          <div>
            <div style="font-family:var(--font-display);font-weight:700;font-size:1.8rem;color:var(--clr-success);">99.9%</div>
            <div style="font-size:0.8rem;color:var(--clr-muted);">Uptime SLA</div>
          </div>
          <div style="width:1px;background:var(--clr-border-strong);"></div>
          <div>
            <div style="font-family:var(--font-display);font-weight:700;font-size:1.8rem;color:var(--clr-accent2);">24/7</div>
            <div style="font-size:0.8rem;color:var(--clr-muted);">Monitoring</div>
          </div>
        </div>
      </div>

      {{-- Right: Dashboard mockup --}}
      <div class="col-lg-6 anim-img">
        <div style="position:relative;">
          {{-- Glow behind image --}}
          <div style="position:absolute;inset:-20px;background:radial-gradient(ellipse at 50% 50%,rgba(0,78,146,0.12),transparent 70%);border-radius:50%;filter:blur(30px);"></div>

          <div class="img-frame" style="position:relative;z-index:1;">
            <img src="{{ asset('images/wallpaper1.jpg') }}" alt="Dashboard Preview">
          </div>

          {{-- Floating badge: Threat --}}
          <div style="position:absolute;bottom:-18px;left:-24px;background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-md);padding:14px 18px;display:flex;align-items:center;gap:12px;box-shadow:0 8px 24px rgba(0,41,93,0.15);z-index:2;">
            <div style="width:38px;height:38px;background:rgba(192,57,43,0.1);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;">
              <i class="bi bi-shield-exclamation" style="color:var(--clr-danger);font-size:1.1rem;"></i>
            </div>
            <div>
              <div style="font-family:var(--font-mono);font-size:0.65rem;color:var(--clr-muted);margin-bottom:2px;font-weight:600;letter-spacing:1px;">ANCAMAN TERDETEKSI</div>
              <div style="font-weight:700;font-size:0.9rem;color:var(--clr-text);">Brute Force Attack</div>
            </div>
          </div>

          {{-- Floating badge: Status --}}
          <div style="position:absolute;top:-14px;right:-18px;background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-md);padding:12px 16px;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,41,93,0.12);z-index:2;">
            <div class="dot"></div>
            <span style="font-family:var(--font-mono);font-size:0.72rem;color:var(--clr-success);font-weight:600;">Semua Sistem Berjalan Normal</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


{{-- ══════════════════════════════════════════════
     FEATURES
══════════════════════════════════════════════ --}}
<section id="features" class="section-alt">
  <div class="orb" style="width:350px;height:350px;background:radial-gradient(circle,rgba(30,144,255,0.07),transparent 70%);top:50%;right:-80px;transform:translateY(-50%);"></div>

  <div class="container position-relative z-1">

    <div class="text-center mb-5 reveal">
      <div class="section-label">Platform</div>
      <h2 class="section-title">Fitur Utama</h2>
      <p style="color:var(--clr-muted);max-width:500px;margin:0 auto;">
        Solusi monitoring keamanan website yang lengkap, andal, dan mudah diintegrasikan.
      </p>
    </div>

    <div class="row g-4">

      <div class="col-md-4 reveal">
        <div class="glass-card p-4 h-100">
          <div class="feat-icon" style="background:rgba(0,78,146,0.1);">
            <i class="bi bi-speedometer2" style="color:var(--clr-accent2);"></i>
          </div>
          <span class="chip mb-3 d-inline-block" style="background:rgba(0,78,146,0.08);color:var(--clr-accent2);">Dashboard</span>
          <h4 style="font-family:var(--font-display);font-weight:700;font-size:1.15rem;margin-bottom:10px;">Wazuh Dashboard</h4>
          <p style="color:var(--clr-muted);font-size:0.875rem;line-height:1.65;margin:0;">
            Rekap data security website secara realtime dengan visualisasi yang intuitif dan mudah dipahami oleh semua level pengguna.
          </p>
        </div>
      </div>

      <div class="col-md-4 reveal" style="transition-delay:0.1s;">
        <div class="glass-card p-4 h-100">
          <div class="feat-icon" style="background:rgba(0,229,160,0.1);">
            <i class="bi bi-hdd-network" style="color:var(--clr-success);"></i>
          </div>
          <span class="chip mb-3 d-inline-block" style="background:rgba(0,229,160,0.1);color:var(--clr-success);">Agent</span>
          <h4 style="font-family:var(--font-display);font-weight:700;font-size:1.15rem;margin-bottom:10px;">Wazuh Agent</h4>
          <p style="color:var(--clr-muted);font-size:0.875rem;line-height:1.65;margin:0;">
            Agent dipasang pada server website untuk mengambil log dan aktivitas keamanan secara otomatis dan aman.
          </p>
        </div>
      </div>

      <div class="col-md-4 reveal" style="transition-delay:0.2s;">
        <div class="glass-card p-4 h-100">
          <div class="feat-icon" style="background:rgba(255,58,92,0.1);">
            <i class="bi bi-shield-check" style="color:var(--clr-danger);"></i>
          </div>
          <span class="chip mb-3 d-inline-block" style="background:rgba(255,58,92,0.1);color:var(--clr-danger);">Detection</span>
          <h4 style="font-family:var(--font-display);font-weight:700;font-size:1.15rem;margin-bottom:10px;">Threat Detection</h4>
          <p style="color:var(--clr-muted);font-size:0.875rem;line-height:1.65;margin:0;">
            Deteksi ancaman, malware, brute force, dan aktivitas mencurigakan secara otomatis secara realtime.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>


{{-- ══════════════════════════════════════════════
     ABOUT
══════════════════════════════════════════════ --}}
<section id="about">
  <div class="orb" style="width:400px;height:400px;background:radial-gradient(circle,rgba(0,78,146,0.08),transparent 70%);bottom:-50px;left:-100px;"></div>

  <div class="container position-relative z-1">
    <div class="row align-items-center g-5">

      <div class="col-lg-6 reveal">
        <div class="img-frame">
          <img src="{{ asset('images/wallpaper3.jpg') }}" alt="About Application">
        </div>
      </div>

      <div class="col-lg-6 reveal">
        <div class="section-label">Tentang</div>
        <h2 class="section-title">Platform Security<br>All-In-One</h2>
        <p style="color:var(--clr-muted);line-height:1.75;margin-bottom:16px;">
          Dashboard One For All merupakan platform monitoring keamanan berbasis Wazuh
          yang membantu perusahaan memonitor aktivitas website, server,
          dan ancaman keamanan secara realtime.
        </p>

        <div class="row g-3">
          <div class="col-6">
            <div class="stat-block">
              <h3 style="color:var(--clr-accent2);">24/7</h3>
              <p>Monitoring Aktif</p>
            </div>
          </div>
          <div class="col-6">
            <div class="stat-block">
              <h3 style="color:var(--clr-success);">Realtime</h3>
              <p>Alert System</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>


{{-- ══════════════════════════════════════════════
     BENEFITS
══════════════════════════════════════════════ --}}
<section id="benefits" class="section-alt">
  <div class="container position-relative z-1">

    <div class="text-center mb-5 reveal">
      <div class="section-label">Keunggulan</div>
      <h2 class="section-title">Kenapa Memilih Platform Ini?</h2>
      <p style="color:var(--clr-muted);max-width:480px;margin:0 auto;">
        Dirancang untuk keamanan enterprise dengan antarmuka yang tetap ramah pengguna.
      </p>
    </div>

    <div class="row g-4">

      <div class="col-md-3 col-sm-6 reveal">
        <div class="benefit-card">
          <div class="benefit-icon" style="background:rgba(200,145,0,0.1);">
            <i class="bi bi-lightning-charge-fill" style="color:#c89100;"></i>
          </div>
          <h5>Realtime</h5>
          <p>Data keamanan tampil secara realtime tanpa delay.</p>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 reveal" style="transition-delay:0.1s;">
        <div class="benefit-card">
          <div class="benefit-icon" style="background:rgba(0,78,146,0.1);">
            <i class="bi bi-sliders" style="color:var(--clr-accent2);"></i>
          </div>
          <h5>Customizable</h5>
          <p>Customer bebas memilih dan menyusun widget dashboard.</p>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 reveal" style="transition-delay:0.2s;">
        <div class="benefit-card">
          <div class="benefit-icon" style="background:rgba(0,229,160,0.1);">
            <i class="bi bi-person-lock" style="color:var(--clr-success);"></i>
          </div>
          <h5>Secure</h5>
          <p>Keamanan data dan monitoring terjamin dengan enkripsi.</p>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 reveal" style="transition-delay:0.3s;">
        <div class="benefit-card">
          <div class="benefit-icon" style="background:rgba(255,58,92,0.1);">
            <i class="bi bi-cloud-arrow-up-fill" style="color:var(--clr-danger);"></i>
          </div>
          <h5>Scalable</h5>
          <p>Mudah dikembangkan untuk puluhan hingga ratusan sistem.</p>
        </div>
      </div>

    </div>
  </div>
</section>


{{-- ══════════════════════════════════════════════
     CTA BANNER
══════════════════════════════════════════════ --}}
<section style="padding:80px 0;">
  <div class="container">
    <div class="glass-card reveal" style="padding:60px 48px;text-align:center;background:linear-gradient(135deg,rgba(0,78,146,0.06),rgba(30,144,255,0.04));border-color:var(--clr-border-strong);position:relative;overflow:hidden;">
      <div class="orb" style="width:300px;height:300px;background:radial-gradient(circle,rgba(0,78,146,0.08),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);"></div>
      <div class="position-relative z-1">
        <div class="section-label" style="justify-content:center;">Mulai Hari Ini</div>
        <h2 class="section-title mb-3">Siap Mengamankan Website Anda?</h2>
        <p style="color:var(--clr-muted);max-width:480px;margin:0 auto 32px;">
          Bergabunglah dengan ratusan perusahaan yang sudah mempercayakan keamanan website mereka kepada One For All.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
          <a href="/auth/login" class="btn-primary-custom">
            Coba Dashboard <i class="bi bi-arrow-right"></i>
          </a>
          <a href="#about" class="btn-outline-custom">
            <i class="bi bi-info-circle"></i> Pelajari Lebih
          </a>
        </div>
      </div>
    </div>
  </div>
</section>


{{-- ══════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════ --}}
<footer>
  <div class="container">
    <div class="row g-5 mb-5">

      {{-- Brand --}}
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-16" style="margin-bottom:16px;">
          <div class="brand-icon">
            <i class="bi bi-shield-shaded" style="color:#fff;"></i>
          </div>
          <span style="font-family:var(--font-display);font-weight:800;font-size:1.05rem;">OneForAll</span>
        </div>
        <p style="color:var(--clr-muted);font-size:0.875rem;line-height:1.7;margin-bottom:24px;">
          Platform monitoring keamanan berbasis Wazuh yang membantu memproteksi website
          dan server Anda dari ancaman siber secara realtime.
        </p>
      </div>

      {{-- Quick Links --}}
      <div class="col-lg-4 col-6">
        <h5>Platform</h5>
        <a href="#features" class="footer-link">Fitur Utama</a>
        <a href="#about" class="footer-link">Tentang</a>
        <a href="#benefits" class="footer-link">Keunggulan</a>
      </div>

      {{-- Contact --}}
      <div class="col-lg-4">
        <h5>Hubungi Kami</h5>
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;">
          <div style="display:flex;align-items:center;gap:10px;font-size:0.875rem;color:var(--clr-muted);">
            <i class="bi bi-envelope-fill" style="color:var(--clr-accent);"></i>
            dashboard.oneforall@gmail.com
          </div>
          <div style="display:flex;align-items:center;gap:10px;font-size:0.875rem;color:var(--clr-muted);">
            <i class="bi bi-telephone-fill" style="color:var(--clr-accent);"></i>
            +62 895-6035-12180
          </div>
          <div style="display:flex;align-items:center;gap:10px;font-size:0.875rem;color:var(--clr-muted);">
            <i class="bi bi-geo-alt-fill" style="color:var(--clr-accent);"></i>
            Indonesia
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="social-btn"><i class="bi bi-github"></i></a>
          <a href="#" class="social-btn"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
        </div>
      </div>

    </div>

    <hr class="hr-custom">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3">
      <p style="color:var(--clr-muted);font-size:0.8rem;margin:0;">
        © 2026 OneForAll — Wazuh Monitor System. Hak cipta dilindungi.
      </p>
    </div>
  </div>
</footer>

@endsection