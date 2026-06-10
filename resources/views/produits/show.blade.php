@extends('layouts.app')

@section('title', 'Détails Produit')

@push('styles')
<style>
    .info-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .section-title { font-size: 15px; font-weight: 600; color: #7C3AED; padding-bottom: 12px; border-bottom: 2px solid #EDE9FE; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #F3F4F6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { color: #1F2937; font-weight: 600; text-align: right; }
    .stat-box { background: #F9FAFB; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #F0F0F0; }
    .stat-box-value { font-size: 28px; font-weight: 700; }
    .stat-box-label { font-size: 12px; color: #6B7280; margin-top: 4px; text-transform: uppercase; }
    .maladie-badge { display: inline-flex; align-items: center; gap: 6px; background: #EDE9FE; color: #5B21B6; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin: 4px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h1 class="page-title mb-1"><i class="fas fa-pills me-2"></i>{{ $produit->dci }}</h1>
        @if($produit->nom_commercial)
        <p class="text-muted mb-0">{{ $produit->nom_commercial }} — {{ $produit->code_produit }}</p>
        @else
        <p class="text-muted mb-0">{{ $produit->code_produit }}</p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('produits.edit', $produit) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Modifier
        </a>
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
            <div class="stat-box-value" style="color: #3B82F6;">{{ $stats['total_lots'] }}</div>
            <div class="stat-box-label">Total Lots</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #10B981;">{{ $stats['lots_disponibles'] }}</div>
            <div class="stat-box-label">Lots Disponibles</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #EF4444;">{{ $stats['lots_expires'] }}</div>
            <div class="stat-box-label">Lots Expirés</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-box">
            <div class="stat-box-value" style="color: #8B5CF6;">{{ number_format($stats['total_vendu']) }}</div>
            <div class="stat-box-label">Unités Vendues</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-info-circle"></i>Informations du Produit</div>
            <div class="info-row">
                <span class="info-label">DCI</span>
                <span class="info-value">{{ $produit->dci }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nom commercial</span>
                <span class="info-value">{{ $produit->nom_commercial ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Catégorie</span>
                <span class="info-value">{{ $produit->categorie->nom ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Forme galénique</span>
                <span class="info-value">{{ $produit->forme_galenique }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dosage</span>
                <span class="info-value">{{ $produit->dosage }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Unité</span>
                <span class="info-value">{{ $produit->unite }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Code produit</span>
                <span class="info-value" style="font-family: monospace;">{{ $produit->code_produit }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Code-barres</span>
                <span class="info-value" style="font-family: monospace;">{{ $produit->code_barre ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Prix recommandé</span>
                <span class="info-value">
                    {{ $produit->prix_vente_recommande ? number_format($produit->prix_vente_recommande, 0, ',', ' ') . ' GNF' : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Nécessite ordonnance</span>
                <span class="info-value">
                    @if($produit->necessite_ordonnance)
                        <span style="background: #FEF3C7; color: #92400E; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <i class="fas fa-prescription me-1"></i>Oui
                        </span>
                    @else
                        <span style="background: #D1FAE5; color: #065F46; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <i class="fas fa-check me-1"></i>Non
                        </span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut</span>
                <span class="info-value">
                    @php
                        $statutConfig = [
                            'actif'   => ['bg' => '#D1FAE5', 'color' => '#065F46', 'label' => 'Actif'],
                            'inactif' => ['bg' => '#FEF3C7', 'color' => '#92400E', 'label' => 'Inactif'],
                            'retire'  => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'label' => 'Retiré'],
                        ];
                        $config = $statutConfig[$produit->statut] ?? ['bg' => '#F3F4F6', 'color' => '#374151', 'label' => $produit->statut];
                    @endphp
                    <span style="background: {{ $config['bg'] }}; color: {{ $config['color'] }}; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        {{ $config['label'] }}
                    </span>
                </span>
            </div>
            @if($produit->description)
            <div class="info-row">
                <span class="info-label">Description</span>
                <span class="info-value" style="max-width: 60%;">{{ $produit->description }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-virus"></i>Maladies Traitées</div>
            @forelse($produit->maladies as $maladie)
            <span class="maladie-badge">
                <i class="fas fa-virus"></i>
                {{ $maladie->nom }}
                @if($maladie->code_cim10)
                <span style="background: rgba(91,33,182,0.2); padding: 1px 6px; border-radius: 4px; font-size: 10px;">
                    {{ $maladie->code_cim10 }}
                </span>
                @endif
            </span>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-virus" style="font-size: 32px; opacity: 0.3;"></i>
                <p class="mt-2 mb-0" style="font-size: 13px;">Aucune maladie associée</p>
            </div>
            @endforelse
        </div>

        <div class="info-card">
            <div class="section-title"><i class="fas fa-boxes"></i>Derniers Lots</div>
            @forelse($produit->lots->take(5) as $lot)
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #F3F4F6; font-size: 13px;">
                <div>
                    <div class="fw-semibold">{{ $lot->numero_lot }}</div>
                    <small class="text-muted">Exp: {{ $lot->date_expiration->format('d/m/Y') }}</small>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">{{ $lot->quantite_disponible }} unités</div>
                    <span style="background: {{ $lot->statut == 'disponible' ? '#D1FAE5' : '#FEE2E2' }}; color: {{ $lot->statut == 'disponible' ? '#065F46' : '#991B1B' }}; padding: 2px 8px; border-radius: 10px; font-size: 11px;">
                        {{ ucfirst($lot->statut) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="fas fa-boxes" style="font-size: 32px; opacity: 0.3;"></i>
                <p class="mt-2 mb-0" style="font-size: 13px;">Aucun lot</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@endsection