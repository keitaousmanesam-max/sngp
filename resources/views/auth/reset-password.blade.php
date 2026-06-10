@extends('layouts.auth')

@section('title', 'Réinitialiser le mot de passe')

@section('content')

    <h4 class="text-center fw-bold mb-2" style="color: #1B4F8A;">
        <i class="fas fa-key me-2"></i>Nouveau mot de passe
    </h4>
    <p class="text-center text-muted small mb-4">
        Définissez votre nouveau mot de passe de connexion.
    </p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <!-- Email (lecture seule) -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Adresse Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control bg-light" value="{{ $email }}" readonly>
            </div>
        </div>

        <!-- Nouveau mot de passe -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Nouveau mot de passe</label>
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
            <div class="text-danger small mt-1">
                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
            </div>
            @enderror
            <div class="form-text">
                <i class="fas fa-info-circle me-1"></i>
                Minimum 8 caractères avec au moins une majuscule, une minuscule et un chiffre.
            </div>
        </div>

        <!-- Confirmation -->
        <div class="mb-4">
            <label class="form-label fw-semibold">Confirmer le mot de passe</label>
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

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
            <i class="fas fa-save me-2"></i>Réinitialiser le mot de passe
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none" style="color: #1B4F8A; font-size: 14px;">
                <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
            </a>
        </div>
    </form>

@endsection

@section('scripts')
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
@endsection