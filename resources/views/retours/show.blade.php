@extends('layouts.app')

@section('title', 'Retour ' . $retour->numero_retour)

@push('styles')
<style>
    .detail-header { background: linear-gradient(135deg, #92400E, #F59E0B); border-radius: 16px; padding: 28px 32px; color: white; margin-bottom: 28px; }
    .info-card { background: white; border-radius: 16px; padding: 24px 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .section-title { font-size: 14px; font-weight: 700; color: #92400E; padding-bottom: 12px; border-bottom: 2px solid #FEF3C7; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid #F3F4F6; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { font-weight: 600; color: #1F2937; text-align: right; }
    .badge-statut { padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .badge-statut.en_attente { background: #FEF3C7; color: #92400E; }
    .badge-statut.valide     { background: #D1FAE5; color: #065F46; }
    .badge-statut.rejete     { background: #FEE2E2; color: #991B1B; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="detail-header">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="{{ route('retours.index') }}" style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:white; padding:6px 12px; border-radius:8px; text-decoration:none; font-size:13px;">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
                <span style="font-family:monospace; font-size:20px; font-weight:700;">{{ $retour->numero_retour }}</span>
            </div>
            <div style="font-size:13px; opacity:0.85;">
                <i class="fas fa-calendar me-1"></i>{{ $retour->created_at->format('d/m/Y à H:i') }}
                @if($retour->demandePar)
                    &nbsp;·&nbsp;<i class="fas fa-user me-1"></i>{{ $retour->demandePar->prenom }} {{ $retour->demandePar->nom }}
                @endif
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <span class="badge-statut {{ $retour->statut }}">
                @if($retour->statut === 'en_attente') <i class="fas fa-clock me-1"></i>En attente
                @elseif($retour->statut === 'valide')  <i class="fas fa-check-circle me-1"></i>Validé
                @else <i class="fas fa-times-circle me-1"></i>Rejeté
                @endif
            </span>
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

    <!-- Colonne gauche -->
    <div class="col-12 col-lg-4">

        <div class="info-card">
            <div class="section-title"><i class="fas fa-info-circle"></i>Informations</div>
            <div class="info-row">
                <span class="info-label">N° Retour</span>
                <span class="info-value" style="font-family:monospace;">{{ $retour->numero_retour }}</span>
            </div>
            @if($retour->vente)
            <div class="info-row">
                <span class="info-label">Vente associée</span>
                <span class="info-value">
                    <a href="{{ route('ventes.show', $retour->vente) }}" style="color:#F59E0B; font-weight:600;">
                        {{ $retour->vente->numero_vente }}
                    </a>
                </span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Statut</span>
                <span class="badge-statut {{ $retour->statut }}">
                    @if($retour->statut === 'en_attente') En attente
                    @elseif($retour->statut === 'valide') Validé
                    @else Rejeté @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Montant remboursé</span>
                <span class="info-value" style="color:#92400E; font-size:15px;">
                    {{ number_format($retour->montant_rembourse ?? 0, 0, ',', ' ') }} GNF
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Demandé par</span>
                <span class="info-value">{{ $retour->demandePar?->prenom }} {{ $retour->demandePar?->nom ?? '—' }}</span>
            </div>
            @if($retour->validePar)
            <div class="info-row">
                <span class="info-label">Validé/Rejeté par</span>
                <span class="info-value">{{ $retour->validePar->prenom }} {{ $retour->validePar->nom }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Le</span>
                <span class="info-value">{{ $retour->valide_le?->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($retour->motif_rejet)
            <div style="padding:10px 0; font-size:14px;">
                <div class="info-label mb-1">Motif de rejet</div>
                <div style="background:#FEF2F2; border-radius:8px; padding:10px; color:#991B1B; font-size:13px;">
                    {{ $retour->motif_rejet }}
                </div>
            </div>
            @endif
        </div>

        @if($retour->motif)
        <div class="info-card">
            <div class="section-title"><i class="fas fa-comment-alt"></i>Motif du Retour</div>
            <p style="font-size:14px; color:#374151; margin:0;">{{ $retour->motif }}</p>
        </div>
        @endif

        <!-- Actions validation -->
        @if($retour->statut === 'en_attente')
        @hasanyrole('admin_pharmacie|admin_national')
        <div class="info-card">
            <div class="section-title"><i class="fas fa-gavel"></i>Actions</div>
            <form method="POST" action="{{ route('retours.valider', $retour) }}" class="mb-2">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Valider ce retour et remettre le stock à jour ?')">
                    <i class="fas fa-check me-2"></i>Valider le Retour
                </button>
            </form>
            <form method="POST" action="{{ route('retours.rejeter', $retour) }}">
                @csrf @method('PATCH')
                <div class="mb-2">
                    <input type="text" name="motif_rejet" class="form-control form-control-sm" placeholder="Motif de rejet (optionnel)">
                </div>
                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Rejeter ce retour ?')">
                    <i class="fas fa-times me-2"></i>Rejeter le Retour
                </button>
            </form>
        </div>
        @endhasanyrole
        @endif

    </div>

    <!-- Colonne droite : lignes produits -->
    <div class="col-12 col-lg-8">
        <div class="info-card">
            <div class="section-title"><i class="fas fa-pills"></i>Produits Retournés</div>
            <div class="table-responsive">
                <table class="table" style="margin-bottom:0;">
                    <thead style="background:#FEF3C7;">
                        <tr>
                            <th style="padding:12px 16px; font-size:12px; color:#92400E; font-weight:700; border:none;">Produit</th>
                            <th style="padding:12px 16px; font-size:12px; color:#92400E; font-weight:700; border:none;">Quantité</th>
                            <th style="padding:12px 16px; font-size:12px; color:#92400E; font-weight:700; border:none;">Prix unitaire</th>
                            <th style="padding:12px 16px; font-size:12px; color:#92400E; font-weight:700; border:none;">Sous-total</th>
                            <th style="padding:12px 16px; font-size:12px; color:#92400E; font-weight:700; border:none;">Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($retour->lignes as $ligne)
                        <tr>
                            <td style="padding:14px 16px; border-color:#F3F4F6;">
                                <div style="font-weight:700; color:#1F2937;">{{ $ligne->produit->dci ?? '—' }}</div>
                                @if($ligne->produit?->nom_commercial)
                                <small style="color:#9CA3AF;">{{ $ligne->produit->nom_commercial }}</small>
                                @endif
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; font-weight:700; color:#1F2937;">
                                {{ $ligne->quantite }} {{ $ligne->produit?->unite }}
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; color:#6B7280;">
                                {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} GNF
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; font-weight:700; color:#92400E;">
                                {{ number_format($ligne->sous_total, 0, ',', ' ') }} GNF
                            </td>
                            <td style="padding:14px 16px; border-color:#F3F4F6; font-size:13px; color:#6B7280;">
                                {{ $ligne->motif_ligne ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucun produit</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="background:#FEF3C7;">
                            <td colspan="3" style="padding:14px 16px; font-weight:700; text-align:right; border-color:#E5E7EB; font-size:13px; color:#374151;">
                                TOTAL REMBOURSÉ
                            </td>
                            <td colspan="2" style="padding:14px 16px; font-weight:800; font-size:16px; color:#92400E; border-color:#E5E7EB; font-family:monospace;">
                                {{ number_format($retour->montant_rembourse ?? 0, 0, ',', ' ') }} GNF
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
