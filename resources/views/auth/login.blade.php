@extends('layouts.auth')

@section('title', 'Login - Spica Admin')

@push('styles')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0d0f14;
    --surface: #13161e;
    --surface-2: #1a1e29;
    --border: rgba(255,255,255,0.07);
    --accent: #6c8cff;
    --accent-glow: rgba(108,140,255,0.25);
    --text: #eef0f6;
    --muted: #5a6070;
    --danger: #ff6b6b;
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

  /* Background blobs */
  .auth-wrapper::before {
    content: '';
    position: fixed;
    top: -20%;
    left: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(108,140,255,0.12) 0%, transparent 70%);
    pointer-events: none;
  }

  .auth-wrapper::after {
    content: '';
    position: fixed;
    bottom: -20%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(108,140,255,0.07) 0%, transparent 70%);
    pointer-events: none;
  }

  /* Grid texture */
  .auth-grid {
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(var(--border) 1px, transparent 1px),
      linear-gradient(90deg, var(--border) 1px, transparent 1px);
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
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 40px 36px 36px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.03);
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
    fill: white;
  }

  .auth-brand-name {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: var(--text);
    letter-spacing: -0.3px;
  }

  .auth-heading {
    font-family: 'Syne', sans-serif;
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
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .form-control::placeholder { color: var(--muted); }

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

  .btn-login {
    width: 100%;
    padding: 12px;
    margin-top: 8px;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 10px;
    font-family: 'Syne', sans-serif;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    letter-spacing: 0.2px;
    transition: opacity 0.2s, box-shadow 0.2s, transform 0.1s;
    box-shadow: 0 4px 20px var(--accent-glow);
  }

  .btn-login:hover {
    opacity: 0.9;
    box-shadow: 0 6px 28px var(--accent-glow);
  }

  .btn-login:active {
    transform: scale(0.99);
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
      <span class="auth-brand-name">Spica</span>
    </div>

    <h1 class="auth-heading">Welcome back</h1>
    <p class="auth-subheading">Sign in to your account</p>

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="form-group">
        <label class="form-label" for="email">Email or Username</label>
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
        <label class="form-label" for="password">Password</label>
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

      <button type="submit" class="btn-login">Sign In</button>
    </form>

    <div class="auth-footer">
      <a href="{{ url('auth/forgot-password') }}">Forgot your password?</a>
    </div>
  </div>
</div>
@endsection