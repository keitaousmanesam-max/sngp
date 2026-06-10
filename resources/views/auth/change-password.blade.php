@extends('layouts.guest')

@section('title', 'Changement de Mot de Passe')

@section('content')
<div class="login-container">
    <div class="login-card">
        
        <!-- Header -->
        <div class="login-header">
            <div class="logo-container">
                <i class="fas fa-lock logo-icon"></i>
            </div>
            <h1>Changement de Mot de Passe</h1>
            <p>Première connexion - Sécurité obligatoire</p>
        </div>
        
        <!-- Body -->
        <div class="login-body">
            
            <!-- Message d'information -->
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Important !</strong><br>
                Pour des raisons de sécurité, vous devez changer votre mot de passe temporaire avant de continuer.
            </div>
            
            <!-- Exigences du mot de passe -->
            <div class="alert alert-info mb-4" role="alert">
                <strong><i class="fas fa-info-circle me-2"></i>Exigences du mot de passe :</strong>
                <ul class="mb-0 mt-2 small">
                    <li>Au moins <strong>8 caractères</strong></li>
                    <li>Au moins <strong>1 lettre majuscule</strong> (A-Z)</li>
                    <li>Au moins <strong>1 lettre minuscule</strong> (a-z)</li>
                    <li>Au moins <strong>1 chiffre</strong> (0-9)</li>
                    <li>Au moins <strong>1 caractère spécial</strong> (@, #, $, !, %, etc.)</li>
                    <li>Différent du mot de passe temporaire actuel</li>
                </ul>
            </div>
            
            <!-- Messages d'erreur -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Erreurs détectées :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <!-- Formulaire -->
            <form method="POST" action="{{ route('password.change') }}" id="changePasswordForm">
                @csrf
                
                <!-- Mot de passe actuel -->
                <div class="mb-3">
                    <label for="current_password" class="form-label">
                        <i class="fas fa-key me-1"></i> Mot de Passe Actuel (Temporaire)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control @error('current_password') is-invalid @enderror" 
                            id="current_password" 
                            name="current_password" 
                            placeholder="Mot de passe temporaire"
                            required
                            autofocus
                        >
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                            <i class="fas fa-eye" id="eye-current_password"></i>
                        </button>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Nouveau mot de passe -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i> Nouveau Mot de Passe
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password" 
                            name="password" 
                            placeholder="Nouveau mot de passe sécurisé"
                            required
                            oninput="checkPasswordStrength()"
                        >
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="eye-password"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Indicateur de force -->
                    <div class="mt-2">
                        <div class="progress" style="height: 5px;">
                            <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="strength-text" class="text-muted"></small>
                    </div>
                </div>
                
                <!-- Confirmation -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-check-double me-1"></i> Confirmer le Nouveau Mot de Passe
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            placeholder="Confirmer le mot de passe"
                            required
                        >
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye" id="eye-password_confirmation"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Bouton -->
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-check me-2"></i>
                    Changer Mon Mot de Passe
                </button>
                
            </form>
            
            <!-- Déconnexion -->
            <div class="text-center mt-3">
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted text-decoration-none">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Se déconnecter
                    </button>
                </form>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="login-footer">
            <p class="mb-1">
                <i class="fas fa-shield-alt me-1"></i>
                Sécurité Renforcée
            </p>
            <small>
                Système National de Gestion Pharmaceutique<br>
                République de Guinée
            </small>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle visibility du mot de passe
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById('eye-' + inputId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Vérification de la force du mot de passe
    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        let strength = 0;
        let feedback = '';
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        switch(strength) {
            case 0:
            case 1:
                strengthBar.style.width = '20%';
                strengthBar.className = 'progress-bar bg-danger';
                feedback = 'Très faible';
                break;
            case 2:
                strengthBar.style.width = '40%';
                strengthBar.className = 'progress-bar bg-warning';
                feedback = 'Faible';
                break;
            case 3:
                strengthBar.style.width = '60%';
                strengthBar.className = 'progress-bar bg-info';
                feedback = 'Moyen';
                break;
            case 4:
                strengthBar.style.width = '80%';
                strengthBar.className = 'progress-bar bg-primary';
                feedback = 'Fort';
                break;
            case 5:
                strengthBar.style.width = '100%';
                strengthBar.className = 'progress-bar bg-success';
                feedback = 'Très fort';
                break;
        }
        
        strengthText.textContent = feedback;
    }
    
    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 8000);
</script>
@endpush