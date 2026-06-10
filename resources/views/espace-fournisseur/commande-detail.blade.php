@extends('layouts.app')

@section('title', 'Commande ' . $commande->numero_commande)

@push('styles')
<style>
    .detail-header { background: linear-gradient(135deg, #1E3A8A, #3B82F6); border-radius: 16px; padding: 28px 32px; color: white; margin-bottom: 28px; }
    .info-card { background: white; border-radius: 16px; padding: 24px 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .section-title { font-size: 14px; font-weight: 700; color: #1E3A8A; padding-bottom: 12px; border-bottom: 2px solid #DBEAFE; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid #F3F4F6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { font-weight: 600; color: #1F2937; text-align: right; }

    .badge-statut { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .badge-statut.en_attente    { background: #F3F4F6; color: #374151; }
    .badge-statut.envoyee       { background: #DBEAFE; color: #1E40AF; }
    .badge-statut.en_traitement { background: #EDE9FE; color: #5B21B6; }
    .badge-statut.expediee      { background: #FEF3C7; color: #92400E; }
    .badge-statut.finalisee     { background: #D1FAE5; color: #065F46; }
    .badge-statut.annulee       { background: #FEE2E2; color: #991B1B; }

    .timeline { position: relative; padding-left: 28px; }
    .timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #E5E7EB; }
    .timeline-item { position: relative; margin-bottom: 20px; }
    .tl-dot { position: absolute; left: -24px; top: 3px; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px #E5E7EB; background: #E5E7EB; }
    .tl-dot.done   { background: #10B981; box-shadow: 0 0 0 2px #10B981; }
    .tl-dot.active { background: #3B82F6; box-shadow: 0 0 0 2px #3B82F6; }
    .tl-dot.cancel { background: #EF4444; box-shadow: 0 0 0 2px #EF4444; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="detail-header">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="{{ route('fournisseur.espace.commandes') }}" style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:white; padding:6px 12px; border-radius:8px; text-decoration:none; font-size:13px;">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
                <span style="font-family:monospace; font-size:20px; font-weight:700;">{{ $commande->numero_commande }}</span>
            </div>
            <div style="font-size:15px; font-weight:600; margin-bottom:4px;">
                <i class="fas fa-hospital me-2"></i>{{ $commande->pharmacie->nom ?? '—' }}
            </div>
            <div style="font-size:13px; opacity:0.8;">
                <i class="fas fa-calendar me-1"></i>{{ $commande->created_at->format('d/m/Y à H:i') }}
                @if($commande->date_livraison_prevue)
                &nbsp;·&nbsp;<i class="fas fa-truck me-1"></i>Livraison prévue le {{ \Carbon\Carbon::parse($commande->date_livraison_prevue)->format('d/m/Y') }}
                @endif
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <span class="badge-statut {{ $commande->statut }}">
                @if($commande->statut=='en_attente') <i class="fas fa-clock me-1"></i>En attente
                @elseif($commande->statut=='envoyee') <i class="fas fa-paper-plane me-1"></i>Nouvelle commande
                @elseif($commande->statut=='en_traitement') <i class="fas fa-cog me-1"></i>En traitement
                @elseif($commande->statut=='expediee') <i class="fas fa-truck me-1"></i>Expédiée
                @elseif($commande->statut=='finalisee') <i class="fas fa-check-circle me-1"></i>Finalisée
                @else <i class="fas fa-times-circle me-1"></i>Annulée
                @endif
            </span>
            @if($commande->statut == 'envoyee')
            <form method="POST" action="{{ route('fournisseur.espace.commande.statut', $commande) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="statut" value="en_traitement">
                <button style="background:#10B981; color:white; border:none; border-radius:10px; padding:10px 20px; font-weight:700; cursor:pointer; font-size:14px;">
                    <i class="fas fa-check me-2"></i>Confirmer la commande
                </button>
            </form>
            @elseif($commande->statut == 'en_traitement')
            <form method="POST" action="{{ route('fournisseur.espace.commande.statut', $commande) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="statut" value="expediee">
                <button style="background:#7C3AED; color:white; border:none; border-radius:10px; padding:10px 20px; font-weight:700; cursor:pointer; font-size:14px;">
                    <i class="fas fa-truck me-2"></i>Marquer comme expédiée
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    <!-- Colonne gauche : infos + timeline -->
    <div class="col-12 col-lg-4">

        <div class="info-card">
            <div class="section-title"><i class="fas fa-info-circle"></i>Informations</div>
            <div class="info-row">
                <span class="info-label">Pharmacie</span>
                <span class="info-value">{{ $commande->pharmacie->nom ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ville</span>
                <span class="info-value">{{ $commande->pharmacie->ville ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Téléphone</span>
                <span class="info-value">{{ $commande->pharmacie->telephone ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Livraison prévue</span>
                <span class="info-value">
                    {{ $commande->date_livraison_prevue ? \Carbon\Carbon::parse($commande->date_livraison_prevue)->format('d/m/Y') : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Nb. produits</span>
                <span class="info-value">{{ $commande->lignes->count() }} ligne(s)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Montant total</span>
                <span class="info-value" style="color:#1E3A8A; font-size:15px;">
                    @if($commande->montant_total > 0)
                        {{ number_format($commande->montant_total, 0, ',', ' ') }} GNF
                    @else
                        <span style="color:#9CA3AF;">À définir</span>
                    @endif
                </span>
            </div>
            @if($commande->observations)
            <div style="padding:10px 0; font-size:14px;">
                <div class="info-label mb-1">Observations</div>
                <div style="background:#F9FAFB; border-radius:8px; padding:10px; color:#374151; font-size:13px;">
                    {{ $commande->observations }}
                </div>
            </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="info-card">
            <div class="section-title"><i class="fas fa-stream"></i>Progression</div>
            @php
                $etapes = [
                    'en_attente'    => ['label' => 'Commande créée',            'icon' => 'fa-plus-circle'],
                    'envoyee'       => ['label' => 'Envoyée au fournisseur',     'icon' => 'fa-paper-plane'],
                    'en_traitement' => ['label' => 'Confirmée par le fournisseur','icon' => 'fa-cog'],
                    'expediee'      => ['label' => 'Expédiée',                   'icon' => 'fa-truck'],
                    'finalisee'     => ['label' => 'Réceptionnée',               'icon' => 'fa-check-circle'],
                ];
                $ordre = array_keys($etapes);
                $idxActuel = array_search($commande->statut, $ordre);
            @endphp
            <div class="timeline">
                @foreach($etapes as $key => $etape)
                @php
                    $idx = array_search($key, $ordre);
                    $done   = $idxActuel !== false && $idx < $idxActuel;
                    $active = $commande->statut === $key;
                @endphp
                <div class="timeline-item">
                    <div class="tl-dot {{ $done ? 'done' : ($active ? 'active' : '') }}"></div>
                    <div style="font-size:13px; font-weight:{{ $active ? '700' : '500' }}; color:{{ $active ? '#1E3A8A' : ($done ? '#065F46' : '#9CA3AF') }}">
                        <i class="fas {{ $etape['icon'] }} me-1"></i>{{ $etape['label'] }}
                    </div>
                </div>
                @endforeach
                @if($commande->statut === 'annulee')
                <div class="timeline-item">
                    <div class="tl-dot cancel"></div>
                    <div style="font-size:13px; font-weight:700; color:#EF4444;">
                        <i class="fas fa-times-circle me-1"></i>Commande annulée
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Colonne droite : liste produits -->
    <div class="col-12 col-lg-8">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-pills"></i>Produits Commandés</div>

            @if($commande->statut === 'envoyee')
            <div class="alert mb-4" style="background:#FFF7ED; border:1px solid #FED7AA; color:#9A3412; font-size:13px;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Cette commande attend votre confirmation. Vérifiez les produits et quantités avant de confirmer.
            </div>
            @endif

            <div class="table-responsive">
                <table class="table" style="margin-bottom:0;">
                    <thead style="background:#F8FAFF;">
                        <tr>
                            <th style="padding:12px 16px; font-size:12px; color:#6B7280; font-weight:700; text-transform:uppercase; border:none;">Produit</th>
                            <th style="padding:12px 16px; font-size:12px; color:#6B7280; font-weight:700; text-transform:uppercase; border:none;">Qté commandée</th>
                            <th style="padding:12px 16px; font-size:12px; color:#6B7280; font-weight:700; text-transform:uppercase; border:none;">Qté reçue</th>
                            <th style="padding:12px 16px; font-size:12px; color:#6B7280; font-weight:700; text-transform:uppercase; border:none;">Prix unitaire</th>
                            <th style="padding:12px 16px; font-size:12px; color:#6B7280; font-weight:700; text-transform:uppercase; border:none;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->lignes as $ligne)
                        <tr>
                            <td style="padding:14px 16px; border-color:#F3F4F6;">
                                <div style="font-weight:700; color:#1F2937;">{{ $ligne->produit->dci }}</div>
                                @if($ligne->produit->nom_commercial)
                                <small style="color:#9CA3AF;">{{ $ligne->produit->nom_commercial }}</small>
                                @endif
                                <div>
                                    <span style="background:#EDE9FE; color:#5B21B6; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:600;">
                                        {{ $ligne->produit->forme_galenique }} {{ $ligne->produit->dosage }}
                                    </span>
                                </div>
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; font-weight:700; color:#1F2937;">
                                {{ $ligne->quantite_commandee }} {{ $ligne->produit->unite }}
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; color:#9CA3AF;">
                                @if($commande->statut === 'finalisee')
                                    <span style="font-weight:700; color:#065F46;">{{ $ligne->quantite_recue }} {{ $ligne->produit->unite }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; color:#6B7280;">
                                @if($ligne->prix_unitaire > 0)
                                    {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} GNF
                                @else
                                    <span style="color:#9CA3AF; font-size:12px;">À définir</span>
                                @endif
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; font-weight:700; color:#1E3A8A;">
                                @if($ligne->montant_total > 0)
                                    {{ number_format($ligne->montant_total, 0, ',', ' ') }} GNF
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#F8FAFF;">
                            <td colspan="4" style="padding:14px 16px; font-weight:700; text-align:right; border-color:#E5E7EB; font-size:13px; color:#374151;">
                                TOTAL
                            </td>
                            <td style="padding:14px 16px; font-weight:800; font-size:16px; color:#1E3A8A; border-color:#E5E7EB; font-family:monospace;">
                                @if($commande->montant_total > 0)
                                    {{ number_format($commande->montant_total, 0, ',', ' ') }} GNF
                                @else
                                    <span style="color:#9CA3AF; font-size:13px; font-family:inherit; font-weight:500;">Défini à la réception</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
