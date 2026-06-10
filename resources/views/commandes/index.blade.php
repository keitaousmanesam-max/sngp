@extends('layouts.app')

@section('title', 'Commandes')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card:hover { transform: translateY(-2px); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.attente { border-left-color: #F59E0B; }
    .stats-card.cours { border-left-color: #8B5CF6; }
    .stats-card.finalisee { border-left-color: #10B981; }
    .stats-card.montant { border-left-color: #EF4444; }
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
    .badge-statut.envoyee { background: #DBEAFE; color: #1E40AF; }
    .badge-statut.en_traitement { background: #EDE9FE; color: #5B21B6; }
    .badge-statut.expediee { background: #FEF3C7; color: #92400E; }
    .badge-statut.finalisee { background: #D1FAE5; color: #065F46; }
    .badge-statut.annulee { background: #FEE2E2; color: #991B1B; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; text-decoration: none; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.edit { background: #F0FDF4; color: #16A34A; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
    .step-indicator { display: flex; align-items: center; gap: 4px; font-size: 11px; }
    .step { width: 8px; height: 8px; border-radius: 50%; background: #E5E7EB; }
    .step.done { background: #10B981; }
    .step.active { background: #3B82F6; }
    .step.cancelled { background: #EF4444; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-file-invoice me-2"></i>Commandes Fournisseurs</h1>
        <p class="text-muted mb-0">Gestion des commandes de médicaments</p>
    </div>
    <a href="{{ route('commandes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvelle Commande
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
    <div class="col-6 col-xl">
        <div class="stats-card total">
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            <div class="stats-label"><i class="fas fa-file-invoice me-1"></i>Total</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card attente">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['en_attente'] }}</div>
            <div class="stats-label"><i class="fas fa-clock me-1"></i>En Attente</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card cours">
            <div class="stats-value" style="color: #8B5CF6;">{{ $stats['en_cours'] }}</div>
            <div class="stats-label"><i class="fas fa-spinner me-1"></i>En Cours</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card finalisee">
            <div class="stats-value" style="color: #10B981;">{{ $stats['finalisees'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Finalisées</div>
        </div>
    </div>
    <div class="col-12 col-xl">
        <div class="stats-card montant">
            <div class="stats-value" style="color: #EF4444; font-size: 20px;">{{ number_format($stats['montant_total'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-coins me-1"></i>Montant Total (GNF)</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('commandes.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="N° commande, fournisseur..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="envoyee" {{ request('statut') == 'envoyee' ? 'selected' : '' }}>Envoyée</option>
                    <option value="en_traitement" {{ request('statut') == 'en_traitement' ? 'selected' : '' }}>En traitement</option>
                    <option value="expediee" {{ request('statut') == 'expediee' ? 'selected' : '' }}>Expédiée</option>
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
                <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 16px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $commandes->total() }} commande(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Fournisseur</th>
                    <th>Produits</th>
                    <th>Montant</th>
                    <th>Livraison Prévue</th>
                    <th>Progression</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commandes as $commande)
                @php
                    $steps = ['en_attente', 'envoyee', 'en_traitement', 'expediee', 'finalisee'];
                    $currentStep = array_search($commande->statut, $steps);
                @endphp
                <tr>
                    <td>
                        <span style="font-family: monospace; background: #F3F4F6; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 700;">
                            {{ $commande->numero_commande }}
                        </span>
                        <br><small class="text-muted">{{ $commande->created_at->format('d/m/Y') }}</small>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $commande->fournisseur->nom }}</div>
                        <small class="text-muted">{{ $commande->fournisseur->telephone }}</small>
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $commande->lignes->count() }}</span>
                        <small class="text-muted"> produit(s)</small>
                    </td>
                    <td class="fw-semibold" style="color: #1E40AF;">
                        {{ number_format($commande->montant_total, 0, ',', ' ') }} GNF
                    </td>
                    <td style="font-size: 13px; color: #6B7280;">
                        {{ $commande->date_livraison_prevue ? \Carbon\Carbon::parse($commande->date_livraison_prevue)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        @if($commande->statut !== 'annulee')
                        <div class="step-indicator">
                            @foreach($steps as $i => $step)
                            <div class="step {{ $currentStep !== false && $i < $currentStep ? 'done' : ($i == $currentStep ? 'active' : '') }}"
                                title="{{ ucfirst(str_replace('_', ' ', $step)) }}"></div>
                            @endforeach
                        </div>
                        @else
                        <span style="font-size: 11px; color: #EF4444;"><i class="fas fa-times me-1"></i>Annulée</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-statut {{ $commande->statut }}">
                            @if($commande->statut == 'en_attente') <i class="fas fa-clock me-1"></i>En attente
                            @elseif($commande->statut == 'envoyee') <i class="fas fa-paper-plane me-1"></i>Envoyée
                            @elseif($commande->statut == 'en_traitement') <i class="fas fa-cog me-1"></i>En traitement
                            @elseif($commande->statut == 'expediee') <i class="fas fa-truck me-1"></i>Expédiée
                            @elseif($commande->statut == 'finalisee') <i class="fas fa-check-circle me-1"></i>Finalisée
                            @else <i class="fas fa-times-circle me-1"></i>Annulée
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('commandes.show', $commande) }}" class="action-btn view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($commande->statut == 'en_attente')
                            <a href="{{ route('commandes.edit', $commande) }}" class="action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('commandes.destroy', $commande) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="action-btn delete" title="Supprimer"
                                    onclick="return confirm('Supprimer cette commande ?')">
                                    <i class="fas fa-trash"></i>
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
                            <i class="fas fa-file-invoice" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucune commande trouvée</p>
                            <a href="{{ route('commandes.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Créer une commande
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($commandes->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $commandes->links() }}
    </div>
    @endif
</div>

@endsection