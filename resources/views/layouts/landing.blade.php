<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'One For All - Security Monitoring')</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Syne:wght@400;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <style>
    /* ── CSS Variables ── */
    :root {
      --clr-bg:        #050b18;
      --clr-surface:   #0a1628;
      --clr-card:      #0e1e38;
      --clr-border:    rgba(255,255,255,0.07);
      --clr-accent:    #00d4ff;
      --clr-accent2:   #0057ff;
      --clr-danger:    #ff3a5c;
      --clr-success:   #00e5a0;
      --clr-text:      #e8edf5;
      --clr-muted:     rgba(232,237,245,0.55);
      --font-display:  'Syne', sans-serif;
      --font-body:     'Space Grotesk', sans-serif;
      --font-mono:     'JetBrains Mono', monospace;
      --radius-lg:     20px;
      --radius-xl:     32px;
      --transition:    0.35s cubic-bezier(0.4,0,0.2,1);
    }

    /* ── Base ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font-body);
      background: var(--clr-bg);
      color: var(--clr-text);
      overflow-x: hidden;
    }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--clr-bg); }
    ::-webkit-scrollbar-thumb { background: var(--clr-accent2); border-radius: 99px; }

    /* ── Noise overlay ── */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.035'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
      opacity: 0.4;
    }

    /* ── Grid lines background ── */
    body::after {
      content: '';
      position: fixed;
      inset: 0;
      background-image:
        linear-gradient(rgba(0,212,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,212,255,0.03) 1px, transparent 1px);
      background-size: 60px 60px;
      pointer-events: none;
      z-index: 0;
    }

    /* ── Navbar ── */
    .navbar {
      background: rgba(5,11,24,0.85) !important;
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
      background: rgba(5,11,24,0.97) !important;
      box-shadow: 0 4px 40px rgba(0,0,0,0.4);
    }
    .navbar-brand {
      font-family: var(--font-display);
      font-weight: 800;
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
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
      box-shadow: 0 0 18px rgba(0,212,255,0.35);
    }
    .nav-link {
      font-family: var(--font-body);
      font-weight: 500;
      font-size: 0.875rem;
      color: var(--clr-muted) !important;
      letter-spacing: 0.3px;
      padding: 0.5rem 1rem !important;
      border-radius: 8px;
      transition: var(--transition);
      position: relative;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: 4px; left: 50%; transform: translateX(-50%);
      width: 0; height: 2px;
      background: var(--clr-accent);
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
      font-weight: 500;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: var(--clr-accent);
      margin-bottom: 12px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }
    .section-label::before {
      content: '';
      width: 28px; height: 1px;
      background: var(--clr-accent);
    }
    .section-title {
      font-family: var(--font-display);
      font-weight: 800;
      font-size: clamp(1.9rem, 4vw, 2.8rem);
      line-height: 1.15;
      letter-spacing: -0.5px;
      margin-bottom: 18px;
      color: var(--clr-text);
    }
    .section-alt { background: rgba(255,255,255,0.015); }

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
      border-radius: 12px;
      border: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: var(--transition);
      box-shadow: 0 4px 24px rgba(0,87,255,0.35);
    }
    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 32px rgba(0,87,255,0.5);
      color: #fff;
    }
    .btn-outline-custom {
      background: transparent;
      color: var(--clr-text);
      font-family: var(--font-body);
      font-weight: 600;
      font-size: 0.9rem;
      padding: 13px 28px;
      border-radius: 12px;
      border: 1px solid var(--clr-border);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: var(--transition);
    }
    .btn-outline-custom:hover {
      border-color: var(--clr-accent);
      color: var(--clr-accent);
      box-shadow: 0 0 20px rgba(0,212,255,0.12);
    }

    /* ── Cards ── */
    .glass-card {
      background: var(--clr-card);
      border: 1px solid var(--clr-border);
      border-radius: var(--radius-lg);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }
    .glass-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, transparent 60%);
      pointer-events: none;
    }
    .glass-card:hover {
      border-color: rgba(0,212,255,0.25);
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(0,0,0,0.4), 0 0 0 1px rgba(0,212,255,0.1);
    }

    /* ── Feature icon ── */
    .feat-icon {
      width: 54px; height: 54px;
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
      margin-bottom: 20px;
    }

    /* ── Stats badge ── */
    .stat-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(0,212,255,0.08);
      border: 1px solid rgba(0,212,255,0.18);
      border-radius: 999px;
      padding: 8px 16px;
      font-family: var(--font-mono);
      font-size: 0.78rem;
      color: var(--clr-accent);
    }
    .stat-pill .dot {
      width: 6px; height: 6px;
      background: var(--clr-accent);
      border-radius: 50%;
      animation: pulse-dot 2s ease-in-out infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.7); }
    }

    /* ── Big stat boxes ── */
    .stat-block {
      background: var(--clr-card);
      border: 1px solid var(--clr-border);
      border-radius: var(--radius-lg);
      padding: 28px 24px;
      text-align: center;
    }
    .stat-block h3 {
      font-family: var(--font-display);
      font-size: 2.2rem;
      font-weight: 800;
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
      color: var(--clr-success);
      font-size: 1rem;
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* ── Image wrapper ── */
    .img-frame {
      border-radius: var(--radius-xl);
      overflow: hidden;
      position: relative;
      box-shadow: 0 30px 80px rgba(0,0,0,0.5);
    }
    .img-frame::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: var(--radius-xl);
      border: 1px solid rgba(255,255,255,0.08);
    }
    .img-frame img { display: block; width: 100%; }

    /* ── Badge chip ── */
    .chip {
      font-family: var(--font-mono);
      font-size: 0.7rem;
      padding: 4px 10px;
      border-radius: 6px;
      font-weight: 500;
    }

    /* ── Benefit cards ── */
    .benefit-card {
      background: var(--clr-card);
      border: 1px solid var(--clr-border);
      border-radius: var(--radius-lg);
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
      background: linear-gradient(90deg, transparent, var(--clr-accent), transparent);
      opacity: 0;
      transition: var(--transition);
    }
    .benefit-card:hover { transform: translateY(-6px); border-color: rgba(0,212,255,0.2); }
    .benefit-card:hover::after { opacity: 1; }
    .benefit-icon {
      width: 62px; height: 62px;
      border-radius: 16px;
      margin: 0 auto 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem;
    }
    .benefit-card h5 {
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 1.05rem;
      margin-bottom: 10px;
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
    .footer-link:hover { color: var(--clr-accent); padding-left: 4px; }
    .social-btn {
      width: 38px; height: 38px;
      border-radius: 10px;
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--clr-border);
      display: flex; align-items: center; justify-content: center;
      color: var(--clr-muted);
      font-size: 1rem;
      text-decoration: none;
      transition: var(--transition);
    }
    .social-btn:hover {
      background: rgba(0,212,255,0.1);
      border-color: var(--clr-accent);
      color: var(--clr-accent);
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
    .navbar-toggler { border-color: var(--clr-border); }
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28232,237,245,0.7%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* ── Divider line ── */
    .hr-custom { border-color: var(--clr-border); }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      section { padding: 80px 0; }
      section:first-of-type { padding-top: 120px; }
    }
  </style>
</head>

<body>
  @yield('content')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
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