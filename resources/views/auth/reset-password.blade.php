@extends('layouts.auth')

@section('title', 'Reset Kata Sandi - Dashboard One For All')

@push('styles')
<style>
  .input-wrap { position: relative; }

  .form-control { padding-right: 40px; }

  .toggle-password {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 0; display: flex; align-items: center;
  }
  .toggle-password:hover { color: var(--text); }
  .toggle-password svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
</style>
@endpush

@section('content')
<div class="auth-grid"></div>
<div class="auth-card">
  <div class="auth-card-inner">

    <div class="auth-brand">
      <img src="{{ asset('images/logo_dofa.png') }}" alt="DOFA Logo" style="display:block;height:82px;width:auto;">
      <span class="auth-brand-name">Dashboard One For All</span>
    </div>

    <h1 class="auth-heading">Reset Kata Sandi</h1>
    <p class="auth-subheading">Masukkan kata sandi baru Anda di bawah ini.</p>

    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      <div class="form-group">
        <label class="form-label" for="password">Kata Sandi Baru</label>
        <div class="input-wrap">
          <input type="password" id="password" name="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Minimal 8 karakter" autocomplete="new-password">
          <button type="button" class="toggle-password" onclick="toggleVisibility('password', this)">
            <svg id="eye-password" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
        <div class="input-wrap">
          <input type="password" id="password_confirmation" name="password_confirmation"
            class="form-control @error('password_confirmation') is-invalid @enderror"
            placeholder="Ulangi kata sandi baru" autocomplete="new-password">
          <button type="button" class="toggle-password" onclick="toggleVisibility('password_confirmation', this)">
            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        @error('password_confirmation')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      @error('email')
        <div class="invalid-feedback" style="margin-bottom:12px;">{{ $message }}</div>
      @enderror

      <button type="submit" class="btn-submit">Simpan Kata Sandi Baru</button>
    </form>

    <div class="auth-footer">
      <a href="{{ route('login') }}">
        <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Kembali ke halaman masuk
      </a>
    </div>

  </div>
</div>

<script>
function toggleVisibility(fieldId, btn) {
  const input = document.getElementById(fieldId);
  const isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  btn.querySelector('svg').style.opacity = isHidden ? '1' : '0.4';
}
</script>
@endsection
