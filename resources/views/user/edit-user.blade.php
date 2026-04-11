@extends('layouts.app')

@section('title', 'Edit Pengguna - One For All')

@section('content')

<div class="row justify-content-center">
  <div class="col-md-10">

    <div class="card grid-margin">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h4 class="card-title mb-0">Edit Pengguna: {{ $user->username }}</h4>
          <a href="{{ route('user') }}" class="btn btn-sm btn-outline-secondary">
            <i class="mdi mdi-arrow-left mr-1"></i> Kembali
          </a>
        </div>

        <form action="{{ route('user.update', $user->id_pengguna) }}" method="POST">
          @csrf
          @method('PUT')

          <!-- User Information -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="card-title mb-0">Informasi Pengguna</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                  <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror"
                    placeholder="Masukkan username" value="{{ old('username', $user->username) }}" required>
                  @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                  <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    placeholder="Masukkan email" value="{{ old('email', $user->email) }}" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label" for="peran">Role <span class="text-danger">*</span></label>
                  <select id="peran" name="peran" class="form-control form-select @error('peran') is-invalid @enderror" required>
                    <option value="admin" {{ old('peran', $user->peran) === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="customer" {{ old('peran', $user->peran) === 'customer' ? 'selected' : '' }}>Customer</option>
                  </select>
                  @error('peran')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label" for="tanggal_dibuat">Tanggal Dibuat</label>
                  <input type="text" id="tanggal_dibuat" class="form-control"
                    value="{{ \Carbon\Carbon::parse($user->tanggal_dibuat)->translatedFormat('d F Y H:i') }}" disabled>
                </div>
              </div>
            </div>
          </div>

          <!-- Agent Assignment -->
          <div class="card mb-4">
            <div class="card-header">
              <h6 class="card-title mb-0">Penugasan Agent Wazuh</h6>
              <p class="text-muted small mb-0">Pilih agent yang belum ditugaskan kepada pengguna lain</p>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label class="form-label" for="agents">Agents</label>
                <div id="agentsContainer" class="border rounded p-3" style="max-height: 400px; overflow-y: auto; padding-right: 1rem !important;">
                  @forelse($availableAgents as $agent)
                    <div class="form-check mb-3 pb-2 d-flex align-items-start" style="border-bottom: 1px solid #eee;">
                      <input type="checkbox" class="form-check-input flex-shrink-0 mt-1" id="agent_{{ $agent['id'] }}"
                        name="agents[]" value="{{ $agent['id'] }}"
                        {{ in_array($agent['id'], old('agents', $userAgentIds)) ? 'checked' : '' }}
                        {{ $agent['assigned'] && !in_array($agent['id'], $userAgentIds) ? 'disabled' : '' }}
                        style="width: 18px; height: 18px; cursor: pointer; margin-top: 2px; margin-left: 0;">
                      <label class="form-check-label flex-grow-1 ms-3" for="agent_{{ $agent['id'] }}" style="cursor: pointer;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                          <div style="flex: 1; min-width: 0;">
                            <strong class="d-block">{{ $agent['name'] }}</strong>
                            <small class="text-muted d-block">ID: {{ $agent['id'] }} | IP: {{ $agent['ip'] }}</small>
                          </div>
                          <div style="flex-shrink: 0;">
                            @if($agent['assigned'])
                              <span class="badge badge-{{ in_array($agent['id'], $userAgentIds) ? 'primary' : 'secondary' }}">
                                Assigned to: <strong>{{ $agent['assigned_to'] }}</strong>
                              </span>
                            @else
                              <span class="badge badge-success">Available</span>
                            @endif
                          </div>
                        </div>
                      </label>
                    </div>
                  @empty
                    <p class="text-muted">Tidak ada agent yang tersedia</p>
                  @endforelse
                </div>
                @error('agents')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="mdi mdi-check mr-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('user') }}" class="btn btn-outline-secondary">
              <i class="mdi mdi-close mr-1"></i> Batal
            </a>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>

@endsection
