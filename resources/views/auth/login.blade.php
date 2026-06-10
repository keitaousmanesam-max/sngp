@extends('layouts.guest')

@section('title', 'Connexion')

@section('content')
<div class="login-container">
    <div class="login-card">

        <!-- Header -->
        <div class="login-header">
            <div class="logo-container">
                <i class="fas fa-hospital-user logo-icon"></i>
            </div>
            <h1>SNGP</h1>
            <p>Système National de Gestion Pharmaceutique</p>
            <small style="opacity: 0.8;">République de Guinée</small>
        </div>

        <!-- Body -->
        <div class="login-body">

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Erreur !</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1"></i> Adresse Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" name="email" value="{{ old('email') }}"
                            placeholder="exemple@sngp.gouv.gn" required autofocus>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Mot de passe -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i> Mot de Passe
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Se souvenir de moi -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                </div>

                <!-- Bouton connexion -->
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
                </button>

                <!-- Mot de passe oublié -->
                <div class="text-center mt-3">
                    <a href="{{ route('password.request') }}" class="text-decoration-none"
                        style="font-size: 13px; color: #6B7280;">
                        <i class="fas fa-unlock-alt me-1"></i>Mot de passe oublié ?
                    </a>
                </div>

            </form>

        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p class="mb-1">
                <i class="fas fa-shield-alt me-1"></i>Connexion Sécurisée
            </p>
            <small>
                Ministère de la Santé et de l'Hygiène Publique<br>
                Direction Nationale des Pharmacies et du Médicament (DNPM)
            </small>
        </div>

    </div>

    <div class="text-center mt-4">
        <small style="color: white; opacity: 0.9;">
            © {{ date('Y') }} SNGP - République de Guinée. Tous droits réservés.
        </small>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);
</script>
@endpush