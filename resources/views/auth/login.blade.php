@extends('layouts.auth')

@section('title', 'Login - Dashboard One For All')

@push('styles')
<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg-start: #000428;
    --bg-mid: #00295D;
    --bg-end: #004E92;
    --card-bg: #3A538A;
    --card-border: rgba(255,255,255,0.1);
    --input-bg: rgba(255,255,255,0.12);
    --input-border: rgba(255,255,255,0.15);
    --input-focus-border: rgba(255,255,255,0.45);
    --text: #ffffff;
    --text-muted: rgba(255,255,255,0.55);
    --placeholder: rgba(255,255,255,0.35);
    --danger: #ff6b6b;
    --btn-bg: rgba(200,215,240,0.75);
    --btn-hover: rgba(220,230,250,0.88);
    --btn-text: #ffffff;
    --label-color: rgba(255,255,255,0.6);
  }

  body.auth-body {
    min-height: 100vh;
    overflow: hidden;
  }

  .auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--bg-start) 0%, var(--bg-mid) 50%, var(--bg-end) 100%);
    position: relative;
  }

  .auth-grid {
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
    mask-image: radial-gradient(ellipse 85% 85% at 50% 50%, black 0%, transparent 100%);
    -webkit-mask-image: radial-gradient(ellipse 85% 85% at 50% 50%, black 0%, transparent 100%);
  }

  .auth-card {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 420px;
    padding: 0 20px;
    animation: fadeUp 0.45s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  }

  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .auth-card-inner {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 0px;
    padding: 40px 40px 36px;
    box-shadow:
      0 32px 80px rgba(0,4,40,0.5),
      0 0 0 1px rgba(255,255,255,0.06),
      inset 0 1px 0 rgba(255,255,255,0.1);
  }

  .auth-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 28px;
  }

  .auth-brand-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -0.3px;
    line-height: 1.2;
  }

  .auth-heading {
    font-size: 28px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.6px;
    margin-bottom: 4px;
  }

  .auth-subheading {
    font-size: 14px;
    color: var(--text-muted);
    margin-bottom: 28px;
  }

  .form-group {
    margin-bottom: 16px;
  }

  .form-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--label-color);
    margin-bottom: 8px;
    letter-spacing: 0.8px;
    text-transform: uppercase;
  }

  .form-control {
    width: 100%;
    background: var(--input-bg);
    border: 1px solid var(--input-border);
    border-radius: 0px;
    padding: 12px 16px;
    font-size: 14px;
    color: var(--text);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    -webkit-appearance: none;
  }

  .form-control::placeholder {
    color: var(--placeholder);
  }

  .form-control:focus {
    border-color: var(--input-focus-border);
    color: var(--text);
    background: rgba(255,255,255,0.16);
    box-shadow: 0 0 0 3px rgba(255,255,255,0.07);
  }

  .form-control.is-invalid {
    border-color: var(--danger);
  }

  .invalid-feedback {
    font-size: 12px;
    color: var(--danger);
    margin-top: 5px;
  }

  .btn-submit {
    width: 100%;
    background: var(--btn-bg);
    border: none;
    border-radius: 0px;
    padding: 13px;
    font-size: 14px;
    font-weight: 600;
    color: var(--btn-text);
    cursor: pointer;
    transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
    margin-top: 8px;
    letter-spacing: 0.2px;
  }

  .btn-submit:hover {
    background: var(--btn-hover);
    box-shadow: 0 4px 16px rgba(0,4,40,0.3);
  }

  .btn-submit:active {
    transform: scale(0.99);
  }

  .auth-footer {
    text-align: center;
    margin-top: 18px;
  }

  .auth-footer a {
    font-size: 13px;
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.2s;
  }

  .auth-footer a:hover {
    color: rgba(255,255,255,0.85);
  }
</style>
@endpush

@section('content')
<div class="auth-grid"></div>
  <div class="auth-card">
    <div class="auth-card-inner">

      <div class="auth-brand">
        <img src="{{ asset('images/logo_dofa.png') }}" alt="DOFA Logo" style="display: block;height: 82px; width: auto;">
        <span class="auth-brand-name">Dashboard One For All</span>
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
            placeholder="Admin"
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
            placeholder="••••••"
            autocomplete="current-password"
          >
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <button type="submit" class="btn-submit">Masuk</button>
      </form>

      <div class="auth-footer">
        <a href="{{ url('auth/forgot-password') }}">Lupa kata sandi Anda?</a>
      </div>

    </div>
  </div>
@endsection