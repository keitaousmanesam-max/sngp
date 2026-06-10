@extends('layouts.app')

@section('title', 'Nouvel Utilisateur')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #1B4F8A; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #2E75B6; box-shadow: 0 0 0 3px rgba(46,117,182,0.15); }
    .required-star { color: #EF4444; margin-left: 2px; }
    .role-card { border: 2px solid #E5E7EB; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.2s; }
    .role-card:hover { border-color: #3B82F6; background: #EFF6FF; }
    .role-card.selected { border-color: #3B82F6; background: #EFF6FF; }
    .role-card input[type="radio"] { display: none; }
    .role-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 10px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur</h1>
        <p class="text-muted mb-0">Créer un nouveau compte utilisateur</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('utilisateurs.store') }}">
    @csrf
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Informations personnelles -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Informations Personnelles
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Prénom <span class="required-star">*</span></label>
                        <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                            placeholder="Ex: Mamadou" value="{{ old('prenom') }}">
                        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nom <span class="required-star">*</span></label>
                        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                            placeholder="Ex: Diallo" value="{{ old('nom') }}">
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Email <span class="required-star">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            placeholder="Ex: mamadou@pharmacie.gn" value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
                            placeholder="Ex: +224 620 000 000" value="{{ old('telephone') }}">
                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Rôle -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-shield-alt"></i> Rôle et Permissions
                </div>
                @error('role')<div class="alert alert-danger py-2 mb-3">{{ $message }}</div>@enderror
                <div class="row g-3">
                    @foreach($roles as $role)
                    @php
                        $roleConfig = [
                            'admin_national'      => ['icon' => 'fa-crown', 'color' => '#5B21B6', 'bg' => '#EDE9FE', 'label' => 'Administrateur National', 'desc' => 'Accès total au système national'],
                            'admin_pharmacie'     => ['icon' => 'fa-hospital', 'color' => '#1E40AF', 'bg' => '#DBEAFE', 'label' => 'Admin Pharmacie', 'desc' => 'Gestion complète d\'une pharmacie'],
                            'pharmacien'          => ['icon' => 'fa-user-md', 'color' => '#065F46', 'bg' => '#D1FAE5', 'label' => 'Pharmacien', 'desc' => 'Validation ventes et ordonnances'],
                            'caissier'            => ['icon' => 'fa-cash-register', 'color' => '#92400E', 'bg' => '#FEF3C7', 'label' => 'Caissier / Vendeur', 'desc' => 'Enregistrement des ventes'],
                            'gestionnaire_stock'  => ['icon' => 'fa-boxes', 'color' => '#9F1239', 'bg' => '#FFE4E6', 'label' => 'Gestionnaire Stock', 'desc' => 'Gestion des stocks et lots'],
                            'assistant_pharmacien'=> ['icon' => 'fa-user-nurse', 'color' => '#075985', 'bg' => '#E0F2FE', 'label' => 'Assistant Pharmacien', 'desc' => 'Préparation des ventes'],
                            'fournisseur'         => ['icon' => 'fa-truck', 'color' => '#374151', 'bg' => '#F3F4F6', 'label' => 'Fournisseur', 'desc' => 'Espace fournisseur dédié'],
                        ];
                        $config = $roleConfig[$role->name] ?? ['icon' => 'fa-user', 'color' => '#374151', 'bg' => '#F3F4F6', 'label' => $role->name, 'desc' => ''];
                    @endphp
                    <div class="col-12 col-md-6">
                        <label class="role-card {{ old('role') == $role->name ? 'selected' : '' }}" onclick="selectRole('{{ $role->name }}', this)">
                            <input type="radio" name="role" value="{{ $role->name }}" {{ old('role') == $role->name ? 'checked' : '' }}>
                            <div class="role-icon" style="background: {{ $config['bg'] }}; color: {{ $config['color'] }};">
                                <i class="fas {{ $config['icon'] }}"></i>
                            </div>
                            <div class="fw-semibold" style="color: #1F2937; font-size: 14px;">{{ $config['label'] }}</div>
                            <small style="color: #6B7280;">{{ $config['desc'] }}</small>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            @role('admin_national')
            <!-- Pharmacie (admin national uniquement) -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-hospital"></i> Pharmacie Associée
                </div>
                <select name="pharmacie_id" class="form-select @error('pharmacie_id') is-invalid @enderror">
                    <option value="">Aucune pharmacie (Admin National)</option>
                    @foreach($pharmacies as $pharmacie)
                    <option value="{{ $pharmacie->id }}" {{ old('pharmacie_id') == $pharmacie->id ? 'selected' : '' }}>
                        {{ $pharmacie->nom }} — {{ $pharmacie->region }}
                    </option>
                    @endforeach
                </select>
                @error('pharmacie_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endrole

        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-info-circle"></i> Informations
                </div>
                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                    <p style="font-size: 13px; color: #1E40AF; font-weight: 600; margin-bottom: 8px;">
                        <i class="fas fa-key me-1"></i> Mot de passe automatique
                    </p>
                    <p style="font-size: 13px; color: #374151; margin: 0;">
                        Un mot de passe temporaire sera généré automatiquement et affiché après la création.
                        L'utilisateur devra le changer à sa première connexion.
                    </p>
                </div>
                <p class="text-muted" style="font-size: 12px;">
                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                    Transmettez les identifiants à l'utilisateur de manière sécurisée.
                </p>
            </div>
            <div class="form-card">
                <button type="submit" class="btn btn-primary w-100 mb-3 py-2">
                    <i class="fas fa-save me-2"></i>Créer l'Utilisateur
                </button>
                <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
    function selectRole(roleName, card) {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        card.querySelector('input[type="radio"]').checked = true;
    }
</script>
@endpush