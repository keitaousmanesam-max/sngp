@extends('layouts.auth')

@section('title', 'Mot de passe oublié')

@section('content')

    <h4 class="text-center fw-bold mb-2" style="color: #1B4F8A;">
        <i class="fas fa-unlock-alt me-2"></i>Mot de passe oublié
    </h4>
    <p class="text-center text-muted small mb-4">
        Entrez votre adresse email et nous vous enverrons un lien de réinitialisation.
    </p>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-4">
            <label class="form-label fw-semibold">Adresse Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="admin@sngp.gouv.gn"
                    value="{{ old('email') }}" autofocus>
            </div>
            @error('email')
            <div class="text-danger small mt-1">
                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
            </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
            <i class="fas fa-paper-plane me-2"></i>Envoyer le lien de réinitialisation
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none" style="color: #1B4F8A; font-size: 14px;">
                <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
            </a>
        </div>
    </form>

@endsection