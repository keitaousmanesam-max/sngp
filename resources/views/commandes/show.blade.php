@extends('layouts.app')

@section('title', 'Détails Commande')

@push('styles')
<style>
    .info-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .section-title { font-size: 15px; font-weight: 600; color: #1E40AF; padding-bottom: 12px; border-bottom: 2px solid #DBEAFE; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #F3F4F6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { color: #1F2937; font-weight: 600; text-align: right; }
    .badge-statut { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .badge-statut.en_attente { background: #FEF3C7; color: #92400E; }
    .badge-statut.envoyee { background: #DBEAFE; color: #1E40AF; }
    .badge-statut.en_traitement { background: #EDE9FE; color: #5B21B6; }
    .badge-statut.expediee { background: #FEF3C7; color: #92400E; }
    .badge-statut.finalisee { background: #D1FAE5; color: #065F46; }
    .badge-statut.annulee { background: #FEE2E2; color: #991B1B; }
    .timeline { position: relative; padding-left: 24px; }
    .timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #E5E7EB; }
    .timeline-item { position: relative; margin-bottom: 20px; }
    .timeline-dot { position: absolute; left: -20px; top: 4px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px #E5E7EB; background: #E5E7EB; }
    .timeline-dot.active { background: #3B82F6; box-shadow: 0 0 0 2px #3B82F6; }
    .timeline-dot.done { background: #10B981; box-shadow: 0 0 0 2px #10B981; }
    .timeline-dot.cancelled { background: #EF4444; box-shadow: 0 0 0 2px #EF4444; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h1 class="page-title mb-1">
            <i class="fas fa-file-invoice me-2"></i>{{ $commande->numero_commande }}
        </h1>
        <p class="text-muted mb-0">{{ $commande->fournisseur->nom }} — {{ $commande->created_at->format('d/m/Y à H:i') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($commande->statut == 'en_attente')
        <a href="{{ route('commandes.edit', $commande) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Modifier
        </a>
        <form method="POST" action="{{ route('commandes.statut', $commande) }}" class="d-inline">
            @csrf @method('PATCH')
            <input type="hidden" name="statut" value="envoyee">
            <button class="btn btn-primary btn-sm" onclick="return confirm('Envoyer cette commande au fournisseur ?')">
                <i class="fas fa-paper-plane me-1"></i>Envoyer
            </button>
        </form>
        @elseif($commande->statut == 'envoyee')
        <form method="POST" action="{{ route('commandes.statut', $commande) }}" class="d-inline">
            @csrf @method('PATCH')
            <input type="hidden" name="statut" value="en_traitement">
            <button class="btn btn-primary btn-sm">
                <i class="fas fa-cog me-1"></i>En traitement
            </button>
        </form>
        @elseif($commande->statut == 'en_traitement')
        <form method="POST" action="{{ route('commandes.statut', $commande) }}" class="d-inline">
            @csrf @method('PATCH')
            <input type="hidden" name="statut" value="expediee">
            <button class="btn btn-warning btn-sm">
                <i class="fas fa-truck me-1"></i>Expédiée
            </button>
        </form>
        @elseif($commande->statut == 'expediee')
        <a href="{{ route('commandes.reception', $commande) }}" class="btn btn-success btn-sm">
            <i class="fas fa-boxes me-1"></i>Réceptionner la livraison
        </a>
        @endif
        @if(!in_array($commande->statut, ['finalisee', 'annulee']))
        <form method="POST" action="{{ route('commandes.statut', $commande) }}" class="d-inline">
            @csrf @method('PATCH')
            <input type="hidden" name="statut" value="annulee">
            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Annuler cette commande ?')">
                <i class="fas fa-times me-1"></i>Annuler
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
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    <!-- Colonne gauche -->
    <div class="col-12 col-lg-4">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-info-circle"></i>Informations</div>
            <div class="info-row">
                <span class="info-label">Numéro</span>
                <span class="info-value" style="font-family: monospace; font-size: 12px;">{{ $commande->numero_commande }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fournisseur</span>
                <span class="info-value">{{ $commande->fournisseur->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut</span>
                <span class="badge-statut {{ $commande->statut }}">
                    @if($commande->statut == 'en_attente') <i class="fas fa-clock me-1"></i>En attente
                    @elseif($commande->statut == 'envoyee') <i class="fas fa-paper-plane me-1"></i>Envoyée
                    @elseif($commande->statut == 'en_traitement') <i class="fas fa-cog me-1"></i>En traitement
                    @elseif($commande->statut == 'expediee') <i class="fas fa-truck me-1"></i>Expédiée
                    @elseif($commande->statut == 'finalisee') <i class="fas fa-check-circle me-1"></i>Finalisée
                    @else <i class="fas fa-times-circle me-1"></i>Annulée
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Créée par</span>
                <span class="info-value">{{ $commande->user->prenom ?? '—' }} {{ $commande->user->nom ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date création</span>
                <span class="info-value">{{ $commande->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Livraison prévue</span>
                <span class="info-value">
                    {{ $commande->date_livraison_prevue ? \Carbon\Carbon::parse($commande->date_livraison_prevue)->format('d/m/Y') : '—' }}
                </span>
            </div>
            @if($commande->date_reception)
            <div class="info-row">
                <span class="info-label">Date réception</span>
                <span class="info-value" style="color: #10B981;">{{ \Carbon\Carbon::parse($commande->date_reception)->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Montant Total</span>
                <span class="info-value" style="color: #1E40AF; font-size: 16px;">
                    {{ number_format($commande->montant_total, 0, ',', ' ') }} GNF
                </span>
            </div>
            @if($commande->observations)
            <div style="padding: 10px 0; font-size: 14px;">
                <div class="info-label mb-1">Observations</div>
                <div style="background: #F9FAFB; border-radius: 8px; padding: 10px; color: #374151;">
                    {{ $commande->observations }}
                </div>
            </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="info-card">
            <div class="section-title"><i class="fas fa-history"></i>Progression</div>
            @php
                $etapes = [
                    'en_attente'    => ['label' => 'Commande créée', 'icon' => 'fa-plus-circle'],
                    'envoyee'       => ['label' => 'Envoyée au fournisseur', 'icon' => 'fa-paper-plane'],
                    'en_traitement' => ['label' => 'En cours de traitement', 'icon' => 'fa-cog'],
                    'expediee'      => ['label' => 'Expédiée', 'icon' => 'fa-truck'],
                    'finalisee'     => ['label' => 'Réceptionnée', 'icon' => 'fa-check-circle'],
                ];
                $statutsOrdre = array_keys($etapes);
                $indexActuel = array_search($commande->statut, $statutsOrdre);
            @endphp
            <div class="timeline">
                @foreach($etapes as $key => $etape)
                @php
                    $indexEtape = array_search($key, $statutsOrdre);
                    $isDone = $indexActuel !== false && $indexEtape < $indexActuel;
                    $isActive = $commande->statut === $key;
                    $dotClass = $isDone ? 'done' : ($isActive ? 'active' : '');
                @endphp
                <div class="timeline-item">
                    <div class="timeline-dot {{ $dotClass }}"></div>
                    <div style="font-size: 13px; font-weight: {{ $isActive ? '700' : '500' }}; color: {{ $isActive ? '#1E40AF' : ($isDone ? '#065F46' : '#9CA3AF') }}">
                        <i class="fas {{ $etape['icon'] }} me-1"></i>{{ $etape['label'] }}
                    </div>
                </div>
                @endforeach
                @if($commande->statut === 'annulee')
                <div class="timeline-item">
                    <div class="timeline-dot cancelled"></div>
                    <div style="font-size: 13px; font-weight: 700; color: #EF4444;">
                        <i class="fas fa-times-circle me-1"></i>Commande annulée
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Colonne droite — Lignes -->
    <div class="col-12 col-lg-8">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-pills"></i>Produits Commandés</div>
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead style="background: #F9FAFB;">
                        <tr>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; text-transform: uppercase; border: none;">Produit</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; text-transform: uppercase; border: none;">Qté commandée</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; text-transform: uppercase; border: none;">Qté reçue</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; text-transform: uppercase; border: none;">Prix unitaire</th>
                            <th style="padding: 12px 16px; font-size: 12px; color: #6B7280; font-weight: 600; text-transform: uppercase; border: none;">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->lignes as $ligne)
                        <tr>
                            <td style="padding: 12px 16px; border-color: #F3F4F6;">
                                <div class="fw-semibold">{{ $ligne->produit->dci }}</div>
                                @if($ligne->produit->nom_commercial)
                                <small class="text-muted">{{ $ligne->produit->nom_commercial }}</small>
                                @endif
                                <div>
                                    <span style="background: #EDE9FE; color: #5B21B6; padding: 2px 6px; border-radius: 4px; font-size: 11px;">
                                        {{ $ligne->produit->forme_galenique }} {{ $ligne->produit->dosage }}
                                    </span>
                                    @if($ligne->statut === 'partiel')
                                    <span style="background: #FEF3C7; color: #92400E; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 4px;">Partiel</span>
                                    @elseif($ligne->statut === 'rejete')
                                    <span style="background: #FEE2E2; color: #991B1B; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 4px;">Rejeté</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; font-weight: 600;">
                                {{ number_format($ligne->quantite_commandee) }} {{ $ligne->produit->unite }}
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; color: {{ $ligne->quantite_recue > 0 ? '#065F46' : '#9CA3AF' }}; font-weight: 600;">
                                @if($commande->statut === 'finalisee')
                                    {{ number_format($ligne->quantite_recue) }} {{ $ligne->produit->unite }}
                                @else
                                    <span style="color:#9CA3AF; font-size:12px;">À réceptionner</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; color: #6B7280;">
                                @if($commande->statut === 'finalisee')
                                    {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} GNF
                                @else
                                    <span style="color:#9CA3AF; font-size:12px;">À définir</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; border-color: #F3F4F6; font-weight: 600; color: #1E40AF;">
                                @if($commande->statut === 'finalisee')
                                    {{ number_format($ligne->montant_total, 0, ',', ' ') }} GNF
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #F9FAFB;">
                            <td colspan="4" style="padding: 14px 16px; font-weight: 700; text-align: right; border-color: #E5E7EB;">
                                TOTAL
                            </td>
                            <td style="padding: 14px 16px; font-weight: 700; font-size: 16px; color: #1E40AF; border-color: #E5E7EB;">
                                @if($commande->statut === 'finalisee')
                                    {{ number_format($commande->montant_total, 0, ',', ' ') }} GNF
                                @else
                                    <span style="color:#9CA3AF; font-size:13px;">Défini à la réception</span>
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