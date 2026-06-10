@extends('layouts.fournisseur')

@section('title', 'Espace Fournisseur')

@push('styles')
<style>
    .hero-banner {
        background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 60%, #60A5FA 100%);
        border-radius: 20px; padding: 36px 40px; color: white; margin-bottom: 28px;
        position: relative; overflow: hidden;
    }
    .hero-banner::before {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 200px; height: 200px; border-radius: 50%;
        background: rgba(255,255,255,0.07);
    }
    .hero-banner::after {
        content: ''; position: absolute; bottom: -60px; right: 80px;
        width: 140px; height: 140px; border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .hero-title { font-size: 26px; font-weight: 700; margin-bottom: 6px; }
    .hero-sub { font-size: 14px; opacity: 0.85; }
    .hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 20px; padding: 6px 14px; font-size: 13px; font-weight: 600; margin-top: 14px; }

    .kpi-card { background: white; border-radius: 16px; padding: 22px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-top: 4px solid transparent; transition: transform 0.2s; }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
    .kpi-card.blue  { border-top-color: #3B82F6; }
    .kpi-card.amber { border-top-color: #F59E0B; }
    .kpi-card.purple{ border-top-color: #8B5CF6; }
    .kpi-card.green { border-top-color: #10B981; }
    .kpi-card.teal  { border-top-color: #0D9488; }
    .kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 12px; }
    .kpi-icon.blue   { background: #EFF6FF; color: #3B82F6; }
    .kpi-icon.amber  { background: #FFFBEB; color: #F59E0B; }
    .kpi-icon.purple { background: #F5F3FF; color: #8B5CF6; }
    .kpi-icon.green  { background: #ECFDF5; color: #10B981; }
    .kpi-icon.teal   { background: #F0FDFA; color: #0D9488; }
    .kpi-value { font-size: 30px; font-weight: 700; line-height: 1; color: #1F2937; }
    .kpi-label { font-size: 12px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }

    .section-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .section-header { font-size: 15px; font-weight: 700; color: #1E3A8A; padding-bottom: 14px; border-bottom: 2px solid #DBEAFE; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; justify-content: space-between; }

    .commande-item { display: flex; align-items: center; gap: 16px; padding: 14px 0; border-bottom: 1px solid #F3F4F6; }
    .commande-item:last-child { border-bottom: none; padding-bottom: 0; }
    .commande-num { font-family: monospace; font-size: 13px; font-weight: 700; color: #1E3A8A; background: #EFF6FF; padding: 4px 8px; border-radius: 6px; }
    .pharmacie-name { font-size: 14px; font-weight: 600; color: #1F2937; }
    .commande-meta { font-size: 12px; color: #9CA3AF; }

    .badge-statut { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-statut.envoyee       { background: #DBEAFE; color: #1E40AF; }
    .badge-statut.en_traitement { background: #EDE9FE; color: #5B21B6; }
    .badge-statut.expediee      { background: #FEF3C7; color: #92400E; }
    .badge-statut.finalisee     { background: #D1FAE5; color: #065F46; }
    .badge-statut.annulee       { background: #FEE2E2; color: #991B1B; }
    .badge-statut.en_attente    { background: #F3F4F6; color: #374151; }

    .urgence-card { border-radius: 12px; padding: 16px 20px; border: 1px solid; margin-bottom: 10px; display: flex; align-items: center; gap: 16px; }
    .urgence-card.envoyee   { background: #EFF6FF; border-color: #BFDBFE; }
    .urgence-card.en_traitement { background: #F5F3FF; border-color: #DDD6FE; }
    .action-pill { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
    .action-pill:hover { opacity: 0.85; transform: scale(1.02); }
    .pill-confirmer { background: #1E3A8A; color: white; }
    .pill-expedier  { background: #7C3AED; color: white; }
    .empty-state { text-align: center; padding: 40px 20px; color: #9CA3AF; }
</style>
@endpush

@section('content')

<!-- Hero Banner -->
<div class="hero-banner">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <div class="hero-title">
                <i class="fas fa-building me-2"></i>{{ $fournisseur->nom }}
            </div>
            <div class="hero-sub">
                <i class="fas fa-map-marker-alt me-1"></i>{{ $fournisseur->ville ?? '' }}{{ $fournisseur->ville && $fournisseur->pays ? ', ' : '' }}{{ $fournisseur->pays }}
                &nbsp;·&nbsp;
                <i class="fas fa-phone me-1"></i>{{ $fournisseur->telephone }}
                &nbsp;·&nbsp;
                <i class="fas fa-envelope me-1"></i>{{ $fournisseur->email }}
            </div>
            <div class="hero-badge">
                <i class="fas fa-check-circle"></i> Fournisseur Agréé SNGP
            </div>
        </div>
        <div style="text-align:right; flex-shrink:0;">
            <div style="font-size:13px; opacity:0.7; margin-bottom:4px;">Chiffre d'affaires total</div>
            <div style="font-size:28px; font-weight:800; font-family:monospace;">
                {{ number_format($stats['ca_total'], 0, ',', ' ') }}
            </div>
            <div style="font-size:13px; opacity:0.7;">GNF</div>
        </div>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="fas fa-file-invoice"></i></div>
            <div class="kpi-value">{{ $stats['total'] }}</div>
            <div class="kpi-label">Total Commandes</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="kpi-card amber">
            <div class="kpi-icon amber"><i class="fas fa-bell"></i></div>
            <div class="kpi-value" style="color:{{ $stats['nouvelles'] > 0 ? '#F59E0B' : '#1F2937' }}">{{ $stats['nouvelles'] }}</div>
            <div class="kpi-label">Nouvelles</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="kpi-card purple">
            <div class="kpi-icon purple"><i class="fas fa-cog"></i></div>
            <div class="kpi-value">{{ $stats['en_cours'] }}</div>
            <div class="kpi-label">En Traitement</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="kpi-card teal">
            <div class="kpi-icon teal"><i class="fas fa-truck"></i></div>
            <div class="kpi-value">{{ $stats['expediees'] }}</div>
            <div class="kpi-label">Expédiées</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-value">{{ $stats['finalisees'] }}</div>
            <div class="kpi-label">Finalisées</div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    <!-- Commandes à traiter -->
    <div class="col-12 col-lg-7">
        <div class="section-card">
            <div class="section-header">
                <span><i class="fas fa-bell me-2" style="color:#F59E0B;"></i>Commandes à Traiter</span>
                @if($commandes_actives->count() > 0)
                <span style="background:#FEF3C7; color:#92400E; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700;">
                    {{ $commandes_actives->count() }} en attente d'action
                </span>
                @endif
            </div>

            @forelse($commandes_actives as $cmd)
            <div class="urgence-card {{ $cmd->statut }}">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="commande-num">{{ $cmd->numero_commande }}</span>
                        <span class="badge-statut {{ $cmd->statut }}">
                            @if($cmd->statut == 'envoyee') <i class="fas fa-paper-plane me-1"></i>Nouvelle
                            @else <i class="fas fa-cog me-1"></i>En traitement
                            @endif
                        </span>
                    </div>
                    <div class="pharmacie-name">{{ $cmd->pharmacie->nom ?? '—' }}</div>
                    <div class="commande-meta">
                        <i class="fas fa-calendar me-1"></i>{{ $cmd->created_at->format('d/m/Y') }}
                        @if($cmd->date_livraison_prevue)
                        &nbsp;·&nbsp;<i class="fas fa-clock me-1"></i>Livraison prévue : {{ \Carbon\Carbon::parse($cmd->date_livraison_prevue)->format('d/m/Y') }}
                        @endif
                        &nbsp;·&nbsp;{{ $cmd->lignes_count ?? $cmd->lignes->count() }} produit(s)
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 align-items-end">
                    @if($cmd->statut == 'envoyee')
                    <form method="POST" action="{{ route('fournisseur.espace.commande.statut', $cmd) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="statut" value="en_traitement">
                        <button class="action-pill pill-confirmer">
                            <i class="fas fa-check me-1"></i>Confirmer
                        </button>
                    </form>
                    @elseif($cmd->statut == 'en_traitement')
                    <form method="POST" action="{{ route('fournisseur.espace.commande.statut', $cmd) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="statut" value="expediee">
                        <button class="action-pill pill-expedier">
                            <i class="fas fa-truck me-1"></i>Expédier
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('fournisseur.espace.commande.show', $cmd) }}"
                       style="font-size:12px; color:#6B7280; text-decoration:none;">
                        Voir détail →
                    </a>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-check-double" style="font-size:40px; opacity:0.25; display:block; margin-bottom:12px;"></i>
                Aucune commande en attente d'action.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Historique récent + Infos -->
    <div class="col-12 col-lg-5">

        <!-- Informations société -->
        <div class="section-card" style="margin-bottom:20px;">
            <div class="section-header">
                <span><i class="fas fa-building me-2"></i>Mon Profil</span>
            </div>
            <div style="font-size:14px;">
                @foreach([
                    ['label' => 'Registre commerce', 'value' => $fournisseur->numero_registre ?? '—'],
                    ['label' => 'Adresse', 'value' => $fournisseur->adresse],
                    ['label' => 'Ville', 'value' => ($fournisseur->ville ?? '—') . ', ' . $fournisseur->pays],
                    ['label' => 'Validé le', 'value' => $fournisseur->valide_le ? $fournisseur->valide_le->format('d/m/Y') : '—'],
                ] as $info)
                <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #F3F4F6; gap:12px;">
                    <span style="color:#6B7280; font-weight:500; flex-shrink:0;">{{ $info['label'] }}</span>
                    <span style="font-weight:600; color:#1F2937; text-align:right;">{{ $info['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Historique récent -->
        <div class="section-card">
            <div class="section-header">
                <span><i class="fas fa-history me-2"></i>Activité Récente</span>
                <a href="{{ route('fournisseur.espace.commandes') }}" style="font-size:13px; color:#3B82F6; text-decoration:none; font-weight:600;">Tout voir →</a>
            </div>
            @forelse($commandes_recentes as $cmd)
            <div class="commande-item">
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="commande-num">{{ $cmd->numero_commande }}</span>
                        <span class="badge-statut {{ $cmd->statut }}">
                            @if($cmd->statut=='envoyee') Nouvelle
                            @elseif($cmd->statut=='en_traitement') En traitement
                            @elseif($cmd->statut=='expediee') Expédiée
                            @elseif($cmd->statut=='finalisee') Finalisée
                            @elseif($cmd->statut=='annulee') Annulée
                            @else En attente
                            @endif
                        </span>
                    </div>
                    <div class="pharmacie-name" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cmd->pharmacie->nom ?? '—' }}</div>
                    <div class="commande-meta">{{ $cmd->created_at->format('d/m/Y') }}</div>
                </div>
                <div style="text-align:right; flex-shrink:0;">
                    @if($cmd->montant_total > 0)
                    <div style="font-size:13px; font-weight:700; color:#1E3A8A; font-family:monospace;">
                        {{ number_format($cmd->montant_total, 0, ',', ' ') }} <span style="font-weight:400; font-size:11px;">GNF</span>
                    </div>
                    @else
                    <span style="font-size:12px; color:#9CA3AF;">À définir</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">Aucune commande pour le moment.</div>
            @endforelse
        </div>

    </div>
</div>

@endsection
