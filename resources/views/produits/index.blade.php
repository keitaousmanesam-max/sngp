@extends('layouts.app')

@section('title', 'Catalogue des Produits')

@push('styles')
<style>
    .stats-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; border-left: 4px solid transparent; transition: all 0.3s; }
    .stats-card:hover { transform: translateY(-2px); }
    .stats-card.total { border-left-color: #3B82F6; }
    .stats-card.actif { border-left-color: #10B981; }
    .stats-card.ordonnance { border-left-color: #F59E0B; }
    .stats-card.categories { border-left-color: #8B5CF6; }
    .stats-value { font-size: 32px; font-weight: 700; line-height: 1; }
    .stats-label { font-size: 13px; color: #6B7280; margin-top: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-card { background: white; border-radius: 12px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
    .table-card .table { margin-bottom: 0; }
    .table-card thead { background: #F9FAFB; }
    .table-card thead th { border: none; color: #6B7280; font-weight: 600; font-size: 12px; text-transform: uppercase; padding: 14px 20px; }
    .table-card tbody td { padding: 14px 20px; vertical-align: middle; border-color: #F3F4F6; font-size: 14px; }
    .table-card tbody tr:hover { background: #F9FAFB; }
    .produit-avatar { width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #7C3AED, #8B5CF6); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; flex-shrink: 0; }
    .action-btn { width: 30px; height: 30px; border-radius: 7px; border: none; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: all 0.2s; cursor: pointer; text-decoration: none; }
    .action-btn:hover { opacity: 0.8; transform: scale(1.1); }
    .action-btn.view { background: #EFF6FF; color: #2563EB; }
    .action-btn.edit { background: #F0FDF4; color: #16A34A; }
    .action-btn.delete { background: #FEF2F2; color: #DC2626; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-pills me-2"></i>Catalogue des Produits</h1>
        <p class="text-muted mb-0">Gestion des médicaments référencés dans le système</p>
    </div>
    <div class="d-flex gap-2">
        @role('admin_national')
        <a href="{{ route('categories.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-tags me-1"></i>Catégories
        </a>
        @endrole
        <a href="{{ route('produits.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau Produit
        </a>
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

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stats-card total">
            <div class="stats-value" style="color: #3B82F6;">{{ $stats['total'] }}</div>
            <div class="stats-label"><i class="fas fa-pills me-1"></i>Total Produits</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card actif">
            <div class="stats-value" style="color: #10B981;">{{ $stats['actifs'] }}</div>
            <div class="stats-label"><i class="fas fa-check-circle me-1"></i>Actifs</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card ordonnance">
            <div class="stats-value" style="color: #F59E0B;">{{ $stats['ordonnance'] }}</div>
            <div class="stats-label"><i class="fas fa-prescription me-1"></i>Sur Ordonnance</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stats-card categories">
            <div class="stats-value" style="color: #8B5CF6;">{{ $stats['categories'] }}</div>
            <div class="stats-label"><i class="fas fa-tags me-1"></i>Catégories</div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="filter-card">
    <form method="GET" action="{{ route('produits.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold small">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="DCI, nom, code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Catégorie</label>
                <select name="categorie_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('categorie_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nom }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="retire" {{ request('statut') == 'retire' ? 'selected' : '' }}>Retiré</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small">Ordonnance</label>
                <select name="ordonnance" class="form-select">
                    <option value="">Tous</option>
                    <option value="oui" {{ request('ordonnance') == 'oui' ? 'selected' : '' }}>Avec ordonnance</option>
                    <option value="non" {{ request('ordonnance') == 'non' ? 'selected' : '' }}>Sans ordonnance</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filtrer</button>
                <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </form>
</div>

<!-- Tableau -->
<div class="table-card">
    <div style="padding: 20px 24px; border-bottom: 1px solid #F3F4F6;">
        <h6 class="mb-0 fw-semibold" style="color: #374151;">{{ $produits->total() }} produit(s) trouvé(s)</h6>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Forme / Dosage</th>
                    <th>Code</th>
                    <th>Ordonnance</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produits as $produit)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="produit-avatar">
                                <i class="fas fa-pills"></i>
                            </div>
                            <div>
                                <div class="fw-semibold" style="color: #1F2937;">{{ $produit->dci }}</div>
                                @if($produit->nom_commercial)
                                <small class="text-muted">{{ $produit->nom_commercial }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="background: #EDE9FE; color: #5B21B6; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                            {{ $produit->categorie->nom ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 13px;">{{ $produit->forme_galenique }}</div>
                        <small class="text-muted">{{ $produit->dosage }} — {{ $produit->unite }}</small>
                    </td>
                    <td>
                        <span style="background: #F3F4F6; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-family: monospace;">
                            {{ $produit->code_produit }}
                        </span>
                    </td>
                    <td>
                        @if($produit->necessite_ordonnance)
                            <span style="background: #FEF3C7; color: #92400E; padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-prescription me-1"></i>Oui
                            </span>
                        @else
                            <span style="background: #D1FAE5; color: #065F46; padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                <i class="fas fa-check me-1"></i>Non
                            </span>
                        @endif
                    </td>
                    <td>
                        @php
                            $statutConfig = [
                                'actif'   => ['bg' => '#D1FAE5', 'color' => '#065F46', 'label' => 'Actif'],
                                'inactif' => ['bg' => '#FEF3C7', 'color' => '#92400E', 'label' => 'Inactif'],
                                'retire'  => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'label' => 'Retiré'],
                            ];
                            $config = $statutConfig[$produit->statut] ?? ['bg' => '#F3F4F6', 'color' => '#374151', 'label' => $produit->statut];
                        @endphp
                        <span style="background: {{ $config['bg'] }}; color: {{ $config['color'] }}; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                            {{ $config['label'] }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('produits.show', $produit) }}" class="action-btn view" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('produits.edit', $produit) }}" class="action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('produits.destroy', $produit) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="action-btn delete" title="Supprimer"
                                    onclick="return confirm('Supprimer ce produit ?')">
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
                            <i class="fas fa-pills" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                            <p class="mb-2 fw-semibold">Aucun produit trouvé</p>
                            <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Ajouter un produit
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($produits->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid #F3F4F6;">
        {{ $produits->links() }}
    </div>
    @endif
</div>

@endsection