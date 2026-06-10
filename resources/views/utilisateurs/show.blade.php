@extends('layouts.app')

@section('title', 'Détails Utilisateur')

@push('styles')
<style>
    .info-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .section-title { font-size: 15px; font-weight: 600; color: #1B4F8A; padding-bottom: 12px; border-bottom: 2px solid #EFF6FF; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #F3F4F6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { color: #1F2937; font-weight: 600; text-align: right; }
    .user-avatar-large { width: 80px; height: 80px; border-radius: 20px; background: linear-gradient(135deg, #1B4F8A, #2E75B6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 28px; margin: 0 auto 16px; }
    .badge-role { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .badge-role.admin_national { background: #EDE9FE; color: #5B21B6; }
    .badge-role.admin_pharmacie { background: #DBEAFE; color: #1E40AF; }
    .badge-role.pharmacien { background: #D1FAE5; color: #065F46; }
    .badge-role.caissier { background: #FEF3C7; color: #92400E; }
    .badge-role.gestionnaire_stock { background: #FFE4E6; color: #9F1239; }
    .badge-role.assistant_pharmacien { background: #E0F2FE; color: #075985; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h1 class="page-title mb-1">
            <i class="fas fa-user me-2"></i>{{ $utilisateur->prenom }} {{ $utilisateur->nom }}
        </h1>
        <p class="text-muted mb-0">{{ $utilisateur->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('utilisateurs.edit', $utilisateur) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Modifier
        </a>
        @if($utilisateur->actif)
        <form method="POST" action="{{ route('utilisateurs.desactiver', $utilisateur) }}">
            @csrf @method('PATCH')
            <button class="btn btn-warning btn-sm" onclick="return confirm('Désactiver ce compte ?')">
                <i class="fas fa-pause me-1"></i>Désactiver
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('utilisateurs.activer', $utilisateur) }}">
            @csrf @method('PATCH')
            <button class="btn btn-success btn-sm">
                <i class="fas fa-play me-1"></i>Activer
            </button>
        </form>
        @endif
        <form method="POST" action="{{ route('utilisateurs.reinitialiser-mot-de-passe', $utilisateur) }}">
            @csrf @method('PATCH')
            <button class="btn btn-outline-secondary btn-sm" onclick="return confirm('Réinitialiser le mot de passe ?')">
                <i class="fas fa-key me-1"></i>Réinitialiser MDP
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    <!-- Colonne gauche -->
    <div class="col-12 col-lg-4">
        <div class="info-card text-center">
            <div class="user-avatar-large">
                {{ strtoupper(substr($utilisateur->prenom, 0, 1)) }}{{ strtoupper(substr($utilisateur->nom, 0, 1)) }}
            </div>
            <h5 class="fw-bold mb-1">{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</h5>
            <p class="text-muted mb-3" style="font-size: 13px;">{{ $utilisateur->email }}</p>
            @php $role = $utilisateur->getRoleNames()->first() ?? 'aucun'; @endphp
            <span class="badge-role {{ $role }}">
                {{ ucfirst(str_replace('_', ' ', $role)) }}
            </span>
            <hr class="my-3">
            <div class="text-start">
                <div class="info-row">
                    <span class="info-label">Statut</span>
                    <span class="info-value">
                        @if($utilisateur->bloque_le)
                            <span class="badge bg-danger">Bloqué</span>
                        @elseif($utilisateur->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-warning text-dark">Inactif</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Première connexion</span>
                    <span class="info-value">
                        @if($utilisateur->premiere_connexion)
                            <span class="badge bg-warning text-dark">En attente</span>
                        @else
                            <span class="badge bg-success">Effectuée</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tentatives échouées</span>
                    <span class="info-value" style="color: {{ $utilisateur->tentatives_connexion > 0 ? '#EF4444' : '#10B981' }}">
                        {{ $utilisateur->tentatives_connexion }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Colonne droite -->
    <div class="col-12 col-lg-8">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-info-circle"></i>Informations du Compte</div>
            <div class="info-row">
                <span class="info-label">Prénom</span>
                <span class="info-value">{{ $utilisateur->prenom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nom</span>
                <span class="info-value">{{ $utilisateur->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">{{ $utilisateur->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone</span>
                <span class="info-value">{{ $utilisateur->telephone ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pharmacie</span>
                <span class="info-value">{{ $utilisateur->pharmacie->nom ?? 'Admin National' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Rôle</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dernière connexion</span>
                <span class="info-value">{{ $utilisateur->derniere_connexion ? $utilisateur->derniere_connexion->format('d/m/Y à H:i') : 'Jamais' }}</span>
            </div>
            @if($utilisateur->bloque_le)
            <div class="info-row">
                <span class="info-label">Bloqué le</span>
                <span class="info-value" style="color: #EF4444;">{{ $utilisateur->bloque_le->format('d/m/Y à H:i') }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Créé le</span>
                <span class="info-value">{{ $utilisateur->created_at->format('d/m/Y à H:i') }}</span>
            </div>
        </div>
    </div>

</div>

@endsection