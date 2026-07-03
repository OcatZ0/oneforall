@extends('layouts.auth')

@section('title', 'Login - Dashboard One For All')

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

      @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
          <label class="form-label" for="email">Email atau Username</label>
          <input
            type="text"
            id="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="Email atau Username"
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