@extends('layouts.app')

@section('title', 'Détails Fournisseur')

@push('styles')
<style>
    .info-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .section-title { font-size: 15px; font-weight: 600; color: #059669; padding-bottom: 12px; border-bottom: 2px solid #ECFDF5; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #F3F4F6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { color: #1F2937; font-weight: 600; text-align: right; }
    .stat-box { background: #F9FAFB; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #F0F0F0; }
    .stat-box-value { font-size: 28px; font-weight: 700; }
    .stat-box-label { font-size: 12px; color: #6B7280; margin-top: 4px; text-transform: uppercase; }
    .badge-statut { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .badge-statut.valide { background: #D1FAE5; color: #065F46; }
    .badge-statut.en_attente { background: #FEF3C7; color: #92400E; }
    .badge-statut.suspendu { background: #FEE2E2; color: #991B1B; }
    .badge-statut.rejete { background: #F3F4F6; color: #374151; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('fournisseurs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h1 class="page-title mb-1"><i class="fas fa-truck me-2"></i>{{ $fournisseur->nom }}</h1>
        <p class="text-muted mb-0">{{ $fournisseur->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('fournisseurs.edit', $fournisseur) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Modifier
        </a>
        @if($fournisseur->statut == 'en_attente')
        <form method="POST" action="{{ route('fournisseurs.valider', $fournisseur) }}">
            @csrf @method('PATCH')
            <button class="btn btn-success btn-sm" onclick="return confirm('Valider ce fournisseur ?')">
                <i class="fas fa-check me-1"></i>Valider
            </button>
        </form>
        <form method="POST" action="{{ route('fournisseurs.rejeter', $fournisseur) }}">
            @csrf @method('PATCH')
            <button class="btn btn-danger btn-sm" onclick="return confirm('Rejeter ce fournisseur ?')">
                <i class="fas fa-times me-1"></i>Rejeter
            </button>
        </form>
        @elseif($fournisseur->statut == 'valide')
        <form method="POST" action="{{ route('fournisseurs.suspendre', $fournisseur) }}">
            @csrf @method('PATCH')
            <button class="btn btn-warning btn-sm" onclick="return confirm('Suspendre ce fournisseur ?')">
                <i class="fas fa-pause me-1"></i>Suspendre
            </button>
        </form>
        @elseif($fournisseur->statut == 'suspendu')
        <form method="POST" action="{{ route('fournisseurs.reactiver', $fournisseur) }}">
            @csrf @method('PATCH')
            <button class="btn btn-success btn-sm">
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
    <div class="col-6 col-xl-3">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #3B82F6;">{{ $stats['total_commandes'] }}</div>
            <div class="stat-box-label">Total Commandes</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #F59E0B;">{{ $stats['commandes_en_cours'] }}</div>
            <div class="stat-box-label">En Cours</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #10B981;">{{ $stats['commandes_finalisees'] }}</div>
            <div class="stat-box-label">Finalisées</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #8B5CF6; font-size: 20px;">{{ number_format($stats['montant_total'], 0, ',', ' ') }}</div>
            <div class="stat-box-label">Montant Total (GNF)</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-info-circle"></i>Informations Générales</div>
            <div class="info-row">
                <span class="info-label">Nom</span>
                <span class="info-value">{{ $fournisseur->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Numéro de registre</span>
                <span class="info-value">{{ $fournisseur->numero_registre ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut</span>
                <span class="badge-statut {{ $fournisseur->statut }}">
                    @if($fournisseur->statut == 'valide') <i class="fas fa-check-circle me-1"></i>Validé
                    @elseif($fournisseur->statut == 'en_attente') <i class="fas fa-clock me-1"></i>En attente
                    @elseif($fournisseur->statut == 'suspendu') <i class="fas fa-ban me-1"></i>Suspendu
                    @else <i class="fas fa-times-circle me-1"></i>Rejeté
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone</span>
                <span class="info-value">{{ $fournisseur->telephone }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">{{ $fournisseur->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ville</span>
                <span class="info-value">{{ $fournisseur->ville ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pays</span>
                <span class="info-value">{{ $fournisseur->pays }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Adresse</span>
                <span class="info-value">{{ $fournisseur->adresse }}</span>
            </div>
            @if($fournisseur->valide_le)
            <div class="info-row">
                <span class="info-label">Validé le</span>
                <span class="info-value">{{ $fournisseur->valide_le->format('d/m/Y à H:i') }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Enregistré le</span>
                <span class="info-value">{{ $fournisseur->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-file-invoice"></i>Dernières Commandes</div>
            @forelse($fournisseur->commandes->take(5) as $commande)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #F3F4F6; font-size: 14px;">
                <div>
                    <div class="fw-semibold">{{ $commande->numero_commande }}</div>
                    <small class="text-muted">{{ $commande->pharmacie->nom ?? '—' }}</small>
                </div>
                <div class="text-end">
                    <div style="font-size: 13px;">{{ number_format($commande->montant_total, 0, ',', ' ') }} GNF</div>
                    <small class="text-muted">{{ $commande->created_at->format('d/m/Y') }}</small>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-file-invoice" style="font-size: 32px; opacity: 0.3;"></i>
                <p class="mt-2 mb-0" style="font-size: 13px;">Aucune commande</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection