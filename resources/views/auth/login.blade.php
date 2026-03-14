@extends('layouts.auth')

@section('title', 'Login - Spica Admin')

@push('styles')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #fffdf5;
    --surface: #ffffff;
    --surface-2: #fdf9ee;
    --border: rgba(180,140,0,0.15);
    --accent: #c9a000;
    --accent-glow: rgba(201,160,0,0.18);
    --accent-light: #f5c400;
    --text: #1a1400;
    --muted: #8a7a3a;
    --danger: #d94040;
  }

  body.auth-body {
    background: var(--bg);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    overflow: hidden;
  }

  .auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
  }

  .auth-wrapper::before {
    content: '';
    position: fixed;
    top: -20%;
    left: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(245,196,0,0.13) 0%, transparent 70%);
    pointer-events: none;
  }

  .auth-wrapper::after {
    content: '';
    position: fixed;
    bottom: -20%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(201,160,0,0.09) 0%, transparent 70%);
    pointer-events: none;
  }

  .auth-grid {
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(180,140,0,0.12) 1px, transparent 1px),
      linear-gradient(90deg, rgba(180,140,0,0.12) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
    mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 0%, transparent 100%);
  }

  .auth-card {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 400px;
    padding: 0 20px;
    animation: fadeUp 0.5s ease forwards;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .auth-card-inner {
    background: var(--surface);
    border: 1px solid rgba(180,140,0,0.2);
    border-radius: 16px;
    padding: 40px 36px 36px;
    box-shadow: 0 24px 64px rgba(180,140,0,0.1), 0 0 0 1px rgba(245,196,0,0.06);
  }

  .auth-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 32px;
  }

  .auth-brand-icon {
    width: 36px;
    height: 36px;
    background: var(--accent);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 20px var(--accent-glow);
  }

  .auth-brand-icon svg {
    width: 18px;
    height: 18px;
    fill: none;
    stroke: white;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .auth-brand-name {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: var(--text);
    letter-spacing: -0.3px;
  }

  .auth-heading {
    font-family: 'DM Sans', sans-serif;
    font-weight: 800;
    font-size: 26px;
    color: var(--text);
    letter-spacing: -0.5px;
    margin-bottom: 6px;
  }

  .auth-subheading {
    font-size: 14px;
    color: var(--muted);
    margin-bottom: 28px;
  }

  .form-group {
    margin-bottom: 16px;
  }

  .form-label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: var(--muted);
    margin-bottom: 7px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .form-control {
    width: 100%;
    background: var(--surface-2);
    border: 1px solid rgba(180,140,0,0.2);
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .form-control::placeholder { color: #bda860; }

  .form-control:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-glow);
  }

  .form-control.is-invalid {
    border-color: var(--danger);
  }

  .invalid-feedback {
    font-size: 12px;
    color: var(--danger);
    margin-top: 5px;
  }

  .auth-footer {
    text-align: center;
    margin-top: 20px;
  }

  .auth-footer a {
    font-size: 12px;
    color: var(--muted);
    text-decoration: none;
    transition: color 0.2s;
  }

  .auth-footer a:hover {
    color: var(--accent);
  }
</style>
@endpush

@section('content')
<div class="auth-grid"></div>

<div class="auth-card">
  <div class="auth-card-inner">
    <div class="auth-brand">
      <div class="auth-brand-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
      </div>
      <span class="auth-brand-name">One For All</span>
    </div>

    <h1 class="auth-heading">Selamat Datang</h1>
    <p class="auth-subheading">Masuk ke akun Anda</p>

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="form-group">
        <label class="form-label" for="email">Email atau Username</label>
        <input
          type="text"
          id="email"
          name="email"
          class="form-control @error('email') is-invalid @enderror"
          placeholder="you@example.com"
          value="{{ old('email') }}"
          autocomplete="username"
          autofocus
        >
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Kata Sandi</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control @error('password') is-invalid @enderror"
          placeholder="••••••••"
          autocomplete="current-password"
        >
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-warning w-100">Masuk</button>
    </form>

    <div class="auth-footer">
      <a href="{{ url('auth/forgot-password') }}">Lupa kata sandi Anda?</a>
    </div>
  </div>
</div>
@endsection