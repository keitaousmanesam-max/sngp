@extends('layouts.app')

@section('title', 'Modifier mon mot de passe')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-md-6">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title mb-1"><i class="fas fa-key me-2"></i>Modifier mon Mot de Passe</h1>
                <p class="text-muted mb-0">Changer votre mot de passe de connexion</p>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;">
            <form method="POST" action="{{ route('password.modifier') }}">
                @csrf

                <!-- Mot de passe actuel -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Mot de passe actuel <span style="color: #EF4444;">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="current_password" id="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            placeholder="••••••••">
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleVisi('current_password', 'icon0')">
                            <i class="fas fa-eye" id="icon0"></i>
                        </button>
                    </div>
                    @error('current_password')
                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nouveau mot de passe -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Nouveau mot de passe <span style="color: #EF4444;">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="password" id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••">
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleVisi('password', 'icon1')">
                            <i class="fas fa-eye" id="icon1"></i>
                        </button>
                    </div>
                    @error('password')
                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Minimum 8 caractères avec au moins une majuscule, une minuscule et un chiffre.
                    </div>
                </div>

                <!-- Confirmation -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirmer le nouveau mot de passe <span style="color: #EF4444;">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control"
                            placeholder="••••••••">
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleVisi('password_confirmation', 'icon2')">
                            <i class="fas fa-eye" id="icon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-save me-2"></i>Enregistrer le nouveau mot de passe
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleVisi(fieldId, iconId) {
        const input = document.getElementById(fieldId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endpush