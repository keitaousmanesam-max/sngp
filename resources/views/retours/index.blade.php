@extends('layouts.app')

@section('title', 'Retours')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card:hover { transform: translateY(-2px); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.attente { border-left-color: #F59E0B; }
    .stats-card.valide { border-left-color: #10B981; }
    .stats-card.rejete { border-left-color: #EF4444; }
    .stats-value { font-size: 28px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 12px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card .table { margin-bottom: 0; }
    .table-card thead { background: #F9FAFB; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; padding: 14px 20px; }
    .table-card tbody td { padding: 12px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F9FAFB; }
    .badge-statut { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-statut.en_attente { background: #FEF3C7; color: #92400E; }
    .badge-statut.valide { background: #D1FAE5; color: #065F46; }
    .badge-statut.rejete { background: #FEE2E2; color: #991B1B; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; text-decoration: none; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.validate { background: #D1FAE5; color: #065F46; }
    .action-btn.reject { background: #FEE2E2; color: #991B1B; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-undo me-2"></i>Retours</h1>
        <p class="text-muted mb-0">Gestion des retours de médicaments</p>
    </div>
    <a href="{{ route('retours.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau Retour
    </a>
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

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card total">
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            <div class="stats-label"><i class="fas fa-undo me-1"></i>Total</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card attente">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['en_attente'] }}</div>
            <div class="stats-label"><i class="fas fa-clock me-1"></i>En Attente</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card valide">
            <div class="stats-value" style="color: #10B981;">{{ $stats['valides'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Validés</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card rejete">
            <div class="stats-value" style="color: #EF4444;">{{ $stats['rejetes'] }}</div>
            <div class="stats-label"><i class="fas fa-times-circle me-1"></i>Rejetés</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('retours.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="N° retour, produit..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Validé</option>
                    <option value="rejete" {{ request('statut') == 'rejete' ? 'selected' : '' }}>Rejeté</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Date début</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filtrer</button>
                <a href="{{ route('retours.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 16px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $retours->total() }} retour(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Retour</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Montant</th>
                    <th>Motif</th>
                    <th>Demandé par</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($retours as $retour)
                <tr>
                    <td>
                        <span style="font-family: monospace; background: #F3F4F6; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 700;">
                            {{ $retour->numero_retour }}
                        </span>
                        @if($retour->vente)
                        <br><small class="text-muted">{{ $retour->vente->numero_vente }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $retour->produit->dci }}</div>
                        <small class="text-muted">{{ $retour->produit->forme_galenique }} {{ $retour->produit->dosage }}</small>
                    </td>
                    <td class="fw-semibold">{{ $retour->quantite }} unités</td>
                    <td style="color: #1E40AF;">
                        {{ $retour->montant > 0 ? number_format($retour->montant, 0, ',', ' ') . ' GNF' : '—' }}
                    </td>
                    <td style="font-size: 13px; max-width: 150px;">
                        {{ Str::limit($retour->motif, 50) }}
                    </td>
                    <td style="font-size: 13px;">
                        {{ $retour->user->prenom ?? '—' }} {{ $retour->user->nom ?? '' }}
                    </td>
                    <td style="font-size: 13px; color: #6B7280;">
                        {{ $retour->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <span class="badge-statut {{ $retour->statut }}">
                            @if($retour->statut == 'en_attente') <i class="fas fa-clock me-1"></i>En attente
                            @elseif($retour->statut == 'valide') <i class="fas fa-check-circle me-1"></i>Validé
                            @else <i class="fas fa-times-circle me-1"></i>Rejeté
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('retours.show', $retour) }}" class="action-btn view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($retour->statut == 'en_attente')
                            @can('validate', $retour)
                            <form method="POST" action="{{ route('retours.valider', $retour) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn validate" title="Valider"
                                    onclick="return confirm('Valider ce retour et remettre le stock ?')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <button class="action-btn reject" title="Rejeter"
                                onclick="ouvrirRejet({{ $retour->id }})">
                                <i class="fas fa-times"></i>
                            </button>
                            @endcan
                            <form method="POST" action="{{ route('retours.destroy', $retour) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="action-btn delete" title="Supprimer"
                                    onclick="return confirm('Supprimer ce retour ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div style="text-align: center; padding: 60px 20px; color: #9CA3AF;">
                            <i class="fas fa-undo" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucun retour trouvé</p>
                            <a href="{{ route('retours.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Enregistrer un retour
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($retours->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $retours->links() }}
    </div>
    @endif
</div>

<!-- Modal Rejet -->
<div class="modal fade" id="modalRejet" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div style="background: linear-gradient(135deg, #DC2626, #EF4444); padding: 24px; border-radius: 16px 16px 0 0;">
                <h5 style="color: white; margin: 0; font-weight: 700;">
                    <i class="fas fa-times-circle me-2"></i>Rejeter le Retour
                </h5>
            </div>
            <form method="POST" id="formRejet" action="">
                @csrf @method('PATCH')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motif du rejet <span style="color: #EF4444;">*</span></label>
                        <textarea name="motif_rejet" class="form-control" rows="3"
                            placeholder="Expliquez pourquoi ce retour est rejeté..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Rejeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function ouvrirRejet(retourId) {
        document.getElementById('formRejet').action = '/retours/' + retourId + '/rejeter';
        new bootstrap.Modal(document.getElementById('modalRejet')).show();
    }
</script>
@endpush