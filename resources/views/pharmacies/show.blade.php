@extends('layouts.app')

@section('title', 'Détails Pharmacie')

@push('styles')
<style>
    .info-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
        margin-bottom: 24px;
    }
    .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1B4F8A;
        padding-bottom: 12px;
        border-bottom: 2px solid #EFF6FF;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #F3F4F6;
        font-size: 14px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { color: #1F2937; font-weight: 600; text-align: right; }
    .stat-box {
        background: #F9FAFB;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 1px solid #F0F0F0;
    }
    .stat-box-value { font-size: 28px; font-weight: 700; }
    .stat-box-label { font-size: 12px; color: #6B7280; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-statut { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .badge-statut.active { background: #D1FAE5; color: #065F46; }
    .badge-statut.suspendue { background: #FEF3C7; color: #92400E; }
    .badge-statut.fermee { background: #FEE2E2; color: #991B1B; }
    .employe-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #F3F4F6;
    }
    .employe-item:last-child { border-bottom: none; }
    .employe-avatar {
        width: 38px; height: 38px; border-radius: 10px;
        background: linear-gradient(135deg, #1B4F8A, #2E75B6);
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; font-size: 14px; flex-shrink: 0;
    }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('pharmacies.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h1 class="page-title mb-1">
            <i class="fas fa-hospital me-2"></i>{{ $pharmacie->nom }}
        </h1>
        <p class="text-muted mb-0">Agrément : <strong>{{ $pharmacie->numero_agrement }}</strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pharmacies.edit', $pharmacie) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Modifier
        </a>
        @if($pharmacie->statut == 'active')
        <form method="POST" action="{{ route('pharmacies.suspendre', $pharmacie) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-warning btn-sm"
                onclick="return confirm('Suspendre cette pharmacie ?')">
                <i class="fas fa-pause me-1"></i>Suspendre
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('pharmacies.reactiver', $pharmacie) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-success btn-sm">
                <i class="fas fa-play me-1"></i>Réactiver
            </button>
        </form>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #3B82F6;">{{ $stats['total_ventes'] }}</div>
            <div class="stat-box-label">Ventes</div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #10B981;">{{ number_format($stats['ca_total'], 0, ',', ' ') }}</div>
            <div class="stat-box-label">CA Total (GNF)</div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #8B5CF6;">{{ $stats['lots_disponibles'] }}</div>
            <div class="stat-box-label">Lots Disponibles</div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #EF4444;">{{ $stats['lots_expires'] }}</div>
            <div class="stat-box-label">Lots Expirés</div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #F59E0B;">{{ $stats['total_employes'] }}</div>
            <div class="stat-box-label">Employés</div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #06B6D4;">{{ $stats['commandes_cours'] }}</div>
            <div class="stat-box-label">Commandes en cours</div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Colonne gauche -->
    <div class="col-12 col-lg-8">

        <!-- Informations générales -->
        <div class="info-card">
            <div class="section-title">
                <i class="fas fa-info-circle"></i> Informations Générales
            </div>
            <div class="info-row">
                <span class="info-label">Nom</span>
                <span class="info-value">{{ $pharmacie->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Numéro d'agrément</span>
                <span class="info-value" style="font-family: monospace; background: #F3F4F6; padding: 4px 10px; border-radius: 6px;">
                    {{ $pharmacie->numero_agrement }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Date d'agrément</span>
                <span class="info-value">{{ $pharmacie->date_agrement->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut</span>
                <span class="badge-statut {{ $pharmacie->statut }}">
                    @if($pharmacie->statut == 'active')
                        <i class="fas fa-check-circle me-1"></i>Active
                    @elseif($pharmacie->statut == 'suspendue')
                        <i class="fas fa-pause-circle me-1"></i>Suspendue
                    @else
                        <i class="fas fa-times-circle me-1"></i>Fermée
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Région</span>
                <span class="info-value">{{ $pharmacie->region }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Préfecture</span>
                <span class="info-value">{{ $pharmacie->prefecture }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Commune</span>
                <span class="info-value">{{ $pharmacie->commune ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Adresse</span>
                <span class="info-value">{{ $pharmacie->adresse }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone</span>
                <span class="info-value">{{ $pharmacie->telephone }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">{{ $pharmacie->email }}</span>
            </div>
            @if($pharmacie->observations)
            <div class="info-row">
                <span class="info-label">Observations</span>
                <span class="info-value">{{ $pharmacie->observations }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Enregistrée le</span>
                <span class="info-value">{{ $pharmacie->created_at->format('d/m/Y à H:i') }}</span>
            </div>
        </div>

    </div>

    <!-- Colonne droite -->
    <div class="col-12 col-lg-4">

        <!-- Employés -->
        <div class="info-card">
            <div class="section-title">
                <i class="fas fa-users"></i> Équipe ({{ $pharmacie->utilisateurs->count() }})
            </div>
            @forelse($pharmacie->utilisateurs as $employe)
            <div class="employe-item">
                <div class="employe-avatar">
                    {{ strtoupper(substr($employe->prenom, 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold" style="font-size: 14px;">
                        {{ $employe->prenom }} {{ $employe->nom }}
                    </div>
                    <small class="text-muted">
                        {{ $employe->getRoleNames()->first() ?? 'Aucun rôle' }}
                    </small>
                </div>
                <span class="badge {{ $employe->actif ? 'bg-success' : 'bg-danger' }} rounded-pill" style="font-size: 11px;">
                    {{ $employe->actif ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-users" style="font-size: 32px; opacity: 0.3;"></i>
                <p class="mt-2 mb-0" style="font-size: 13px;">Aucun employé enregistré</p>
            </div>
            @endforelse
        </div>

    </div>

</div>

@endsection