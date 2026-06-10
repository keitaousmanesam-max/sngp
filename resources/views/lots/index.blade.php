@extends('layouts.app')

@section('title', 'Stocks & Lots')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card:hover { transform: translateY(-2px); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.disponible { border-left-color: #10B981; }
    .stats-card.expire { border-left-color: #EF4444; }
    .stats-card.proche { border-left-color: #F59E0B; }
    .stats-card.valeur { border-left-color: #8B5CF6; }
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
    .badge-statut.disponible { background: #D1FAE5; color: #065F46; }
    .badge-statut.epuise { background: #FEF3C7; color: #92400E; }
    .badge-statut.expire { background: #FEE2E2; color: #991B1B; }
    .badge-statut.retire { background: #F3F4F6; color: #374151; }
    .expiration-ok { color: #065F46; font-weight: 600; }
    .expiration-proche { color: #D97706; font-weight: 600; }
    .expiration-expire { color: #DC2626; font-weight: 600; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; text-decoration: none; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.edit { background: #F0FDF4; color: #16A34A; }
    .action-btn.adjust { background: #F5F3FF; color: #7C3AED; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
    .fefo-badge { background: linear-gradient(135deg, #1E3A8A, #3B82F6); color: white; padding: 3px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-boxes me-2"></i>Stocks & Lots</h1>
        <p class="text-muted mb-0">
            Gestion des lots — Méthode <span class="fefo-badge">FEFO</span>
            <small class="ms-2 text-muted">(First Expired, First Out)</small>
        </p>
    </div>
    <a href="{{ route('lots.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau Lot
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

<!-- Alertes -->
@if($stats['expires'] > 0)
<div class="alert alert-danger mb-4">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>{{ $stats['expires'] }} lot(s) expiré(s)</strong> — Ces lots doivent être retirés immédiatement de la vente.
</div>
@endif
@if($stats['expiration_proche'] > 0)
<div class="alert alert-warning mb-4">
    <i class="fas fa-clock me-2"></i>
    <strong>{{ $stats['expiration_proche'] }} lot(s)</strong> expirent dans moins de 30 jours.
</div>
@endif

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl">
        <div class="stats-card total">
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total_lots'] }}</div>
            <div class="stats-label"><i class="fas fa-boxes me-1"></i>Total Lots</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card disponible">
            <div class="stats-value" style="color: #10B981;">{{ $stats['disponibles'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Disponibles</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card expire">
            <div class="stats-value" style="color: #EF4444;">{{ $stats['expires'] }}</div>
            <div class="stats-label"><i class="fas fa-times-circle me-1"></i>Expirés</div>
        </div>
    </div>
    <div class="col-6 col-xl">
        <div class="stats-card proche">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['expiration_proche'] }}</div>
            <div class="stats-label"><i class="fas fa-clock me-1"></i>Expiration Proche</div>
        </div>
    </div>
    <div class="col-12 col-xl">
        <div class="stats-card valeur">
            <div class="stats-value" style="color: #8B5CF6; font-size: 20px;">{{ number_format($stats['valeur_stock'], 0, ',', ' ') }}</div>
            <div class="stats-label"><i class="fas fa-coins me-1"></i>Valeur Stock (GNF)</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('lots.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="N° lot, produit..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Produit</label>
                <select name="produit_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($produits as $produit)
                    <option value="{{ $produit->id }}" {{ request('produit_id') == $produit->id ? 'selected' : '' }}>
                        {{ $produit->dci }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="disponible" {{ request('statut') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="epuise" {{ request('statut') == 'epuise' ? 'selected' : '' }}>Épuisé</option>
                    <option value="expire" {{ request('statut') == 'expire' ? 'selected' : '' }}>Expiré</option>
                    <option value="retire" {{ request('statut') == 'retire' ? 'selected' : '' }}>Retiré</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Expiration</label>
                <select name="expiration" class="form-select">
                    <option value="">Tous</option>
                    <option value="expire" {{ request('expiration') == 'expire' ? 'selected' : '' }}>Expirés</option>
                    <option value="proche" {{ request('expiration') == 'proche' ? 'selected' : '' }}>Proche (30j)</option>
                    <option value="ok" {{ request('expiration') == 'ok' ? 'selected' : '' }}>OK</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filtrer</button>
                <a href="{{ route('lots.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 16px 24px; border-bottom: 1px solid #F3F4F6; display: flex; justify-content: space-between; align-items: center;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $lots->total() }} lot(s) — Triés par FEFO</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>N° Lot</th>
                    <th>Date Expiration</th>
                    <th>Qté Disponible</th>
                    <th>Prix Achat Unitaire</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lots as $lot)
                @php
                    $joursRestants = now()->diffInDays($lot->date_expiration, false);
                    $expirationClass = $joursRestants < 0 ? 'expiration-expire' : ($joursRestants <= 30 ? 'expiration-proche' : 'expiration-ok');
                    $expirationIcon = $joursRestants < 0 ? 'fa-times-circle' : ($joursRestants <= 30 ? 'fa-exclamation-circle' : 'fa-check-circle');
                @endphp
                <tr>
                    <td>
                        <div class="fw-semibold" style="color: #1F2937;">{{ $lot->produit->dci }}</div>
                        @if($lot->produit->nom_commercial)
                        <small class="text-muted">{{ $lot->produit->nom_commercial }}</small>
                        @endif
                        <div>
                            <span style="background: #EDE9FE; color: #5B21B6; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                                {{ $lot->produit->categorie->nom ?? '—' }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <span style="font-family: monospace; background: #F3F4F6; padding: 3px 8px; border-radius: 4px; font-size: 13px;">
                            {{ $lot->numero_lot }}
                        </span>
                    </td>
                    <td>
                        <div class="{{ $expirationClass }}">
                            <i class="fas {{ $expirationIcon }} me-1"></i>
                            {{ $lot->date_expiration->format('d/m/Y') }}
                        </div>
                        <small class="text-muted">
                            @if($joursRestants < 0)
                                Expiré depuis {{ abs($joursRestants) }}j
                            @elseif($joursRestants == 0)
                                Expire aujourd'hui
                            @else
                                Dans {{ $joursRestants }}j
                            @endif
                        </small>
                    </td>
                    <td>
                        <span class="fw-semibold" style="font-size: 16px; color: {{ $lot->quantite_disponible <= 10 ? '#EF4444' : '#1F2937' }}">
                            {{ number_format($lot->quantite_disponible) }}
                        </span>
                        @if($lot->quantite_disponible <= 10 && $lot->quantite_disponible > 0)
                        <br><small style="color: #EF4444;"><i class="fas fa-exclamation-triangle me-1"></i>Stock critique</small>
                        @endif
                    </td>
                    <td style="color: #6B7280;">{{ number_format($lot->prix_achat_unitaire, 0, ',', ' ') }} GNF</td>
                    <td>
                        <span class="badge-statut {{ $lot->statut }}">
                            @if($lot->statut == 'disponible') <i class="fas fa-check-circle me-1"></i>Disponible
                            @elseif($lot->statut == 'epuise') <i class="fas fa-minus-circle me-1"></i>Épuisé
                            @elseif($lot->statut == 'expire') <i class="fas fa-times-circle me-1"></i>Expiré
                            @else <i class="fas fa-ban me-1"></i>Retiré
                            @endif
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('lots.show', $lot) }}" class="action-btn view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('lots.edit', $lot) }}" class="action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="action-btn adjust" title="Ajustement stock"
                                onclick="ouvrirAjustement({{ $lot->id }}, '{{ $lot->numero_lot }}', {{ $lot->quantite_disponible }})">
                                <i class="fas fa-sliders-h"></i>
                            </button>
                            <form method="POST" action="{{ route('lots.destroy', $lot) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="action-btn delete" title="Supprimer"
                                    onclick="return confirm('Supprimer ce lot ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div style="text-align: center; padding: 60px 20px; color: #9CA3AF;">
                            <i class="fas fa-boxes" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucun lot trouvé</p>
                            <a href="{{ route('lots.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Ajouter un lot
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lots->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $lots->links() }}
    </div>
    @endif
</div>

<!-- Modal Ajustement Stock -->
<div class="modal fade" id="modalAjustement" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div style="background: linear-gradient(135deg, #5B21B6, #7C3AED); padding: 24px; border-radius: 16px 16px 0 0;">
                <h5 style="color: white; margin: 0; font-weight: 700;">
                    <i class="fas fa-sliders-h me-2"></i>Ajustement de Stock
                </h5>
                <p style="color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 13px;" id="modalLotInfo"></p>
            </div>
            <form method="POST" id="formAjustement" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type de mouvement <span style="color: #EF4444;">*</span></label>
                        <select name="type" class="form-select" id="typeAjustement" onchange="updateTypeInfo()">
                            <option value="entree">Entrée — Ajouter du stock</option>
                            <option value="sortie">Sortie — Retirer du stock</option>
                            <option value="ajustement">Ajustement — Correction d'inventaire</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantité <span style="color: #EF4444;">*</span></label>
                        <input type="number" name="quantite" class="form-control" min="1" placeholder="Ex: 50" required>
                        <div class="form-text" id="stockActuelInfo"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motif <span style="color: #EF4444;">*</span></label>
                        <input type="text" name="motif" class="form-control" placeholder="Ex: Réception commande, Casse, Inventaire..." required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function ouvrirAjustement(lotId, numeroLot, stockActuel) {
        document.getElementById('formAjustement').action = '/lots/' + lotId + '/ajustement';
        document.getElementById('modalLotInfo').textContent = 'Lot : ' + numeroLot + ' — Stock actuel : ' + stockActuel + ' unités';
        document.getElementById('stockActuelInfo').textContent = 'Stock actuel : ' + stockActuel + ' unités';
        new bootstrap.Modal(document.getElementById('modalAjustement')).show();
    }

    function updateTypeInfo() {
        const type = document.getElementById('typeAjustement').value;
        const info = document.getElementById('stockActuelInfo');
        if (type === 'entree') {
            info.style.color = '#065F46';
            info.innerHTML = '<i class="fas fa-plus-circle me-1"></i>Les unités seront ajoutées au stock';
        } else {
            info.style.color = '#DC2626';
            info.innerHTML = '<i class="fas fa-minus-circle me-1"></i>Les unités seront retirées du stock';
        }
    }
</script>
@endpush