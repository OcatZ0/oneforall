<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'One For All - Security Monitoring')</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    /* ── CSS Variables ── */
    :root {
      --clr-bg:        #f8faff;
      --clr-surface:   #f0f6ff;
      --clr-card:      #ffffff;
      --clr-border:    #d6e8ff;
      --clr-border-strong: #a8cfff;
      --clr-accent:    #1e90ff;
      --clr-accent2:   #004E92;
      --clr-danger:    #c0392b;
      --clr-success:   #2e8b57;
      --clr-text:      #00295D;
      --clr-muted:     #6a7c99;
      --font-display:  'DM Sans', sans-serif;
      --font-body:     'DM Sans', sans-serif;
      --font-mono:     'DM Sans', sans-serif;
      --radius-sm:     6px;
      --radius-md:     12px;
      --radius-lg:     18px;
      --radius-xl:     18px;
      --transition:    0.35s cubic-bezier(0.4,0,0.2,1);
    }

    /* ── Base ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; overflow-x: hidden; }

    body {
      font-family: var(--font-body);
      background: var(--clr-bg);
      color: var(--clr-text);
      overflow-x: hidden;
      padding-right: 0 !important;
    }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--clr-surface); }
    ::-webkit-scrollbar-thumb { background: var(--clr-accent2); border-radius: 99px; }

    /* ── Grid lines background ── */
    body::after {
      content: '';
      position: fixed;
      inset: 0;
      background-image:
        linear-gradient(rgba(0,78,146,0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,78,146,0.025) 1px, transparent 1px);
      background-size: 60px 60px;
      pointer-events: none;
      z-index: 0;
    }

    /* ── Navbar ── */
    .navbar {
      background: rgba(248,250,255,0.92) !important;
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--clr-border);
      min-height: 72px;
      position: fixed !important;
      top: 0; left: 0; right: 0;
      z-index: 1000;
      transition: var(--transition);
    }
    .navbar.scrolled {
      background: rgba(255,255,255,0.98) !important;
      box-shadow: 0 4px 20px rgba(0,41,93,0.1);
    }
    .navbar-brand {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 1.15rem;
      letter-spacing: -0.3px;
      color: var(--clr-text) !important;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .brand-icon {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, var(--clr-accent2), var(--clr-accent));
      border-radius: 0px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
      box-shadow: 0 0 18px rgba(0,78,146,0.25);
    }
    .nav-link {
      font-family: var(--font-body);
      font-weight: 500;
      font-size: 0.875rem;
      color: var(--clr-muted) !important;
      letter-spacing: 0.3px;
      padding: 0.5rem 1rem !important;
      border-radius: 0px;
      transition: var(--transition);
      position: relative;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: 4px; left: 50%; transform: translateX(-50%);
      width: 0; height: 2px;
      background: var(--clr-accent2);
      border-radius: 99px;
      transition: var(--transition);
    }
    .nav-link:hover { color: var(--clr-text) !important; }
    .nav-link:hover::after { width: 60%; }

    /* ── Sections ── */
    section {
      position: relative;
      z-index: 1;
      padding: 120px 0;
    }
    section:first-of-type { padding-top: 160px; }

    .section-label {
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--clr-accent2);
      margin-bottom: 12px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
    .section-label::before {
      content: '';
      width: 28px; height: 1px;
      background: var(--clr-accent2);
    }
    .section-title {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: clamp(1.9rem, 4vw, 2.8rem);
      line-height: 1.15;
      letter-spacing: -0.5px;
      margin-bottom: 18px;
      color: var(--clr-text);
    }
    .section-alt { background: var(--clr-surface); }

    /* ── Glow orbs ── */
    .orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      pointer-events: none;
      z-index: 0;
    }

    /* ── Buttons ── */
    .btn-primary-custom {
      background: linear-gradient(135deg, var(--clr-accent2), var(--clr-accent));
      color: #fff;
      font-family: var(--font-body);
      font-weight: 600;
      font-size: 0.9rem;
      padding: 14px 30px;
      border-radius: 0px;
      border: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: var(--transition);
      box-shadow: 0 4px 16px rgba(0,78,146,0.3);
    }
    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,78,146,0.4);
      color: #fff;
    }
    .btn-outline-custom {
      background: transparent;
      color: var(--clr-text);
      font-family: var(--font-body);
      font-weight: 600;
      font-size: 0.9rem;
      padding: 13px 28px;
      border-radius: 0px;
      border: 1.5px solid var(--clr-border-strong);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: var(--transition);
    }
    .btn-outline-custom:hover {
      border-color: var(--clr-accent2);
      color: var(--clr-accent2);
      box-shadow: 0 4px 16px rgba(0,78,146,0.1);
    }

    /* ── Cards ── */
    .glass-card {
      background: var(--clr-card);
      border: 1px solid var(--clr-border);
      border-radius: 0px;
      box-shadow: 0 6px 20px rgba(0,41,93,0.08);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }
    .glass-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(0,78,146,0.02) 0%, transparent 60%);
      pointer-events: none;
    }
    .glass-card:hover {
      border-color: var(--clr-border-strong);
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0,41,93,0.15);
    }

    /* ── Feature icon ── */
    .feat-icon {
      width: 54px; height: 54px;
      border-radius: 0px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
      margin-bottom: 20px;
    }

    /* ── Stats badge ── */
    .stat-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(0,78,146,0.08);
      border: 1px solid rgba(0,78,146,0.2);
      border-radius: 0px;
      padding: 8px 16px;
      font-family: var(--font-mono);
      font-size: 0.78rem;
      font-weight: 500;
      color: var(--clr-accent2);
    }
    .stat-pill .dot {
      width: 6px; height: 6px;
      background: var(--clr-success);
      border-radius: 50%;
      animation: pulse-dot 2s ease-in-out infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.7); }
    }

    /* ── Big stat boxes ── */
    .stat-block {
      background: var(--clr-surface);
      border: 1px solid var(--clr-border);
      border-radius: 0px;
      padding: 28px 24px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,41,93,0.06);
    }
    .stat-block h3 {
      font-family: var(--font-display);
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 4px;
    }
    .stat-block p { color: var(--clr-muted); font-size: 0.85rem; margin: 0; }

    /* ── Check list ── */
    .check-list { list-style: none; padding: 0; }
    .check-list li {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 14px 0;
      border-bottom: 1px solid var(--clr-border);
      color: var(--clr-muted);
      font-size: 0.93rem;
    }
    .check-list li:last-child { border-bottom: none; }
    .check-list li .bi {
      color: var(--clr-accent2);
      font-size: 1rem;
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* ── Image wrapper ── */
    .img-frame {
      border-radius: 0px;
      overflow: hidden;
      position: relative;
      box-shadow: 0 20px 50px rgba(0,41,93,0.18);
    }
    .img-frame::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 0px;
      border: 1px solid var(--clr-border);
    }
    .img-frame img { display: block; width: 100%; }

    /* ── Badge chip ── */
    .chip {
      font-family: var(--font-mono);
      font-size: 0.7rem;
      padding: 4px 10px;
      border-radius: 0px;
      font-weight: 500;
    }

    /* ── Benefit cards ── */
    .benefit-card {
      background: var(--clr-card);
      border: 1px solid var(--clr-border);
      border-radius: 0px;
      box-shadow: 0 4px 14px rgba(0,41,93,0.07);
      padding: 36px 28px;
      text-align: center;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100%;
    }
    .benefit-card::after {
      content: '';
      position: absolute;
      bottom: 0; left: 50%; transform: translateX(-50%);
      width: 60%; height: 2px;
      background: linear-gradient(90deg, transparent, var(--clr-accent2), transparent);
      opacity: 0;
      transition: var(--transition);
    }
    .benefit-card:hover { transform: translateY(-4px); border-color: var(--clr-border-strong); box-shadow: 0 10px 28px rgba(0,41,93,0.12); }
    .benefit-card:hover::after { opacity: 1; }
    .benefit-icon {
      width: 62px; height: 62px;
      border-radius: 0px;
      margin: 0 auto 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem;
    }
    .benefit-card h5 {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 1.05rem;
      margin-bottom: 10px;
      color: var(--clr-text);
    }
    .benefit-card p { color: var(--clr-muted); font-size: 0.875rem; margin: 0; }

    /* ── Footer ── */
    footer {
      background: var(--clr-surface);
      border-top: 1px solid var(--clr-border);
      padding: 64px 0 32px;
      position: relative;
      z-index: 1;
    }
    footer h5 {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 0.95rem;
      color: var(--clr-text);
      margin-bottom: 20px;
    }
    .footer-link {
      display: block;
      color: var(--clr-muted);
      text-decoration: none;
      font-size: 0.875rem;
      margin-bottom: 10px;
      transition: var(--transition);
    }
    .footer-link:hover { color: var(--clr-accent2); padding-left: 4px; }
    .social-btn {
      width: 38px; height: 38px;
      border-radius: 0px;
      background: rgba(0,78,146,0.05);
      border: 1px solid var(--clr-border);
      display: flex; align-items: center; justify-content: center;
      color: var(--clr-muted);
      font-size: 1rem;
      text-decoration: none;
      transition: var(--transition);
    }
    .social-btn:hover {
      background: rgba(0,78,146,0.1);
      border-color: var(--clr-accent2);
      color: var(--clr-accent2);
      transform: translateY(-2px);
    }

    /* ── Hero animations ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to   { opacity: 1; }
    }
    .anim-1 { animation: fadeUp 0.7s ease both; }
    .anim-2 { animation: fadeUp 0.7s 0.15s ease both; }
    .anim-3 { animation: fadeUp 0.7s 0.3s ease both; }
    .anim-4 { animation: fadeUp 0.7s 0.45s ease both; }
    .anim-img { animation: fadeIn 0.9s 0.3s ease both; }

    /* ── Navbar toggler ── */
    .navbar-toggler { border-color: var(--clr-border-strong); }
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%280,41,93,0.7%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* ── Divider line ── */
    .hr-custom { border-color: var(--clr-border); }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      section { padding: 80px 0; }
      section:first-of-type { padding-top: 120px; }
    }

    /* ── Offcanvas Drawer ── */
    .offcanvas.offcanvas-end {
      width: 300px;
      background: var(--clr-bg);
      border-left: 1px solid var(--clr-border);
    }
    .offcanvas-header {
      height: 72px;
      padding: 0 24px;
      border-bottom: 1px solid var(--clr-border);
    }
    .offcanvas-header .btn-close {
      filter: invert(15%) sepia(50%) saturate(2000%) hue-rotate(200deg) brightness(60%);
      opacity: 0.6;
    }
    .offcanvas-header .btn-close:hover { opacity: 1; }
    .offcanvas-body {
      padding: 32px 24px;
      display: flex;
      flex-direction: column;
    }
    .drawer-nav a {
      display: flex;
      align-items: center;
      font-weight: 600;
      font-size: 0.95rem;
      color: var(--clr-text);
      text-decoration: none;
      padding: 16px 0;
      border-bottom: 1px solid var(--clr-border);
      transition: color 0.2s, padding-left 0.2s;
    }
    .drawer-nav a:first-child { border-top: 1px solid var(--clr-border); }
    .drawer-nav a:hover { color: var(--clr-accent2); padding-left: 6px; }
    .drawer-cta {
      margin-top: auto;
      width: 100%;
      justify-content: center;
      padding: 14px 24px;
      font-size: 0.95rem;
    }
  </style>
</head>

<body>
  @yield('content')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
      window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 40);
      });
    }

    // Close offcanvas when a drawer link is clicked
    document.querySelectorAll('.drawer-nav a').forEach(a => {
      a.addEventListener('click', () => {
        const drawer = bootstrap.Offcanvas.getInstance(document.getElementById('navDrawer'));
        if (drawer) drawer.hide();
      });
    });

    // Intersection observer for scroll reveal
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });

    document.querySelectorAll('.reveal').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(28px)';
      el.style.transition = '0.6s cubic-bezier(0.4,0,0.2,1)';
      observer.observe(el);
    });
  </script>
</body>
</html>