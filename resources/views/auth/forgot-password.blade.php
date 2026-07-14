@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi - Dashboard One For All')

@section('content')
<div class="auth-grid"></div>
  <div class="auth-card">
    <div class="auth-card-inner">

      <div class="auth-brand">
        <img src="{{ asset('images/logo_dofa.png') }}" alt="DOFA Logo" style="display: block; height: 82px; width: auto;">
        <span class="auth-brand-name">Dashboard One For All</span>
      </div>

      <h1 class="auth-heading">Lupa Kata Sandi</h1>
      <p class="auth-subheading">Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>

      @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="you@example.com"
            value="{{ old('email') }}"
            autocomplete="email"
            autofocus
          >
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <button type="submit" class="btn-submit">Kirim Tautan Reset</button>
      </form>

      <div class="auth-footer">
        <a href="{{ route('login') }}">
          <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Kembali ke halaman masuk
        </a>
      </div>

    </div>
  </div>
@endsection