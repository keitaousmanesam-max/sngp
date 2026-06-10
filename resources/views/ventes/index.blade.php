@extends('layouts.app')

@section('title', 'Ventes')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card:hover { transform: translateY(-2px); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.jour { border-left-color: #10B981; }
    .stats-card.ca_jour { border-left-color: #F59E0B; }
    .stats-card.ca_mois { border-left-color: #8B5CF6; }
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
    .badge-statut.finalisee { background: #D1FAE5; color: #065F46; }
    .badge-statut.annulee { background: #FEE2E2; color: #991B1B; }
    .badge-statut.en_attente { background: #FEF3C7; color: #92400E; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; text-decoration: none; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.cancel { background: #FEF2F2; color: #DC2626; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-shopping-cart me-2"></i>Ventes</h1>
        <p class="text-muted mb-0">Historique et gestion des ventes</p>
    </div>
    <a href="{{ route('ventes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvelle Vente
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
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total_ventes'] }}</div>
            <div class="stats-label"><i class="fas fa-shopping-cart me-1"></i>Total Ventes</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card jour">
            <div class="stats-value" style="color: #10B981;">{{ $stats['ventes_jour'] }}</div>
            <div class="stats-label"><i class="fas fa-calendar-day me-1"></i>Ventes Aujourd'hui</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card ca_jour">
            <div class="stats-value" style="color: #F59E0B; font-size: 20px;">{{ number_format($stats['ca_jour'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-coins me-1"></i>CA Aujourd'hui (GNF)</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card ca_mois">
            <div class="stats-value" style="color: #8B5CF6; font-size: 20px;">{{ number_format($stats['ca_mois'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-chart-line me-1"></i>CA Ce Mois (GNF)</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('ventes.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="N° vente, patient..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="finalisee" {{ request('statut') == 'finalisee' ? 'selected' : '' }}>Finalisée</option>
                    <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>Annulée</option>
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
                <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 16px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $ventes->total() }} vente(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Vente</th>
                    <th>Patient</th>
                    <th>Médicaments</th>
                    <th>Montant Total</th>
                    <th>Vendeur</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventes as $vente)
                <tr>
                    <td>
                        <span style="font-family: monospace; background: #F3F4F6; padding: 3px 8px; border-radius: 4px; font-size: 13px; font-weight: 700;">
                            {{ $vente->numero_vente }}
                        </span>
                        @if($vente->avec_ordonnance)
                        <br><span style="background: #DBEAFE; color: #1E40AF; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-top: 2px; display: inline-block;">
                            <i class="fas fa-prescription me-1"></i>Ordonnance
                        </span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $vente->nom_patient ?? 'Patient anonyme' }}</div>
                        @if($vente->telephone_patient)
                        <small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $vente->telephone_patient }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $vente->lignes->count() }}</span>
                        <small class="text-muted"> produit(s)</small>
                        <div style="font-size: 12px; color: #6B7280;">
                            {{ $vente->lignes->take(2)->pluck('produit.dci')->join(', ') }}
                            @if($vente->lignes->count() > 2)...@endif
                        </div>
                    </td>
                    <td class="fw-semibold" style="color: #065F46;">
                        {{ number_format($vente->montant_total, 0, ',', ' ') }} GNF
                    </td>
                    <td style="font-size: 13px;">
                        {{ $vente->user->prenom ?? '—' }} {{ $vente->user->nom ?? '' }}
                    </td>
                    <td style="font-size: 13px; color: #6B7280;">
                        {{ $vente->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <span class="badge-statut {{ $vente->statut }}">
                            @if($vente->statut == 'finalisee') <i class="fas fa-check-circle me-1"></i>Finalisée
                            @else <i class="fas fa-times-circle me-1"></i>Annulée
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('ventes.show', $vente) }}" class="action-btn view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($vente->statut == 'finalisee')
                            <form method="POST" action="{{ route('ventes.annuler', $vente) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn cancel" title="Annuler"
                                    onclick="return confirm('Annuler cette vente ?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div style="text-align: center; padding: 60px 20px; color: #9CA3AF;">
                            <i class="fas fa-shopping-cart" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucune vente trouvée</p>
                            <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Enregistrer une vente
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ventes->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $ventes->links() }}
    </div>
    @endif
</div>

@endsection