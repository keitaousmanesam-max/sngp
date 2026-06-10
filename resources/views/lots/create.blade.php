@extends('layouts.app')

@section('title', 'Nouveau Lot')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #7C3AED; padding-bottom: 12px; border-bottom: 2px solid #EDE9FE; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.15); }
    .required-star { color: #EF4444; margin-left: 2px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('lots.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-plus-circle me-2"></i>Nouveau Lot</h1>
        <p class="text-muted mb-0">Réception d'un nouveau lot de médicaments</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('lots.store') }}">
    @csrf
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Produit -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-pills"></i> Produit
                </div>
                <div class="mb-3">
                    <label class="form-label">Produit <span class="required-star">*</span></label>
                    <select name="produit_id" class="form-select @error('produit_id') is-invalid @enderror" onchange="remplirPrix(this)">
                        <option value="">Sélectionner un produit</option>
                        @foreach($produits as $produit)
                        <option value="{{ $produit->id }}"
                            data-prix="{{ $produit->prix_vente_recommande ?? '' }}"
                            {{ old('produit_id') == $produit->id ? 'selected' : '' }}>
                            {{ $produit->dci }}
                            @if($produit->nom_commercial) — {{ $produit->nom_commercial }} @endif
                            ({{ $produit->dosage }})
                        </option>
                        @endforeach
                    </select>
                    @error('produit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Identification du lot -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-barcode"></i> Identification du Lot
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Numéro de Lot <span class="required-star">*</span></label>
                        <input type="text" name="numero_lot"
                            class="form-control @error('numero_lot') is-invalid @enderror"
                            placeholder="Ex: LOT-2026-001"
                            value="{{ old('numero_lot') }}">
                        @error('numero_lot')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Fournisseur</label>
                        <select name="fournisseur_id" class="form-select @error('fournisseur_id') is-invalid @enderror">
                            <option value="">Aucun fournisseur</option>
                            @foreach(\App\Models\Fournisseur::where('statut', 'valide')->orderBy('nom')->get() as $fournisseur)
                            <option value="{{ $fournisseur->id }}" {{ old('fournisseur_id') == $fournisseur->id ? 'selected' : '' }}>
                                {{ $fournisseur->nom }}
                            </option>
                            @endforeach
                        </select>
                        @error('fournisseur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Date de Fabrication</label>
                        <input type="date" name="date_fabrication"
                            class="form-control @error('date_fabrication') is-invalid @enderror"
                            value="{{ old('date_fabrication') }}">
                        @error('date_fabrication')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Date d'Expiration <span class="required-star">*</span></label>
                        <input type="date" name="date_expiration"
                            class="form-control @error('date_expiration') is-invalid @enderror"
                            value="{{ old('date_expiration') }}"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        @error('date_expiration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Quantité et Prix -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-coins"></i> Quantité et Prix
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Quantité Initiale <span class="required-star">*</span></label>
                        <input type="number" name="quantite_initiale"
                            class="form-control @error('quantite_initiale') is-invalid @enderror"
                            placeholder="Ex: 100" min="1"
                            value="{{ old('quantite_initiale') }}">
                        @error('quantite_initiale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Prix d'Achat (GNF) <span class="required-star">*</span></label>
                        <input type="number" name="prix_achat"
                            class="form-control @error('prix_achat') is-invalid @enderror"
                            placeholder="Ex: 5000" min="0"
                            value="{{ old('prix_achat') }}">
                        @error('prix_achat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Prix de Vente (GNF) <span class="required-star">*</span></label>
                        <input type="number" name="prix_vente" id="prix_vente"
                            class="form-control @error('prix_vente') is-invalid @enderror"
                            placeholder="Ex: 8000" min="0"
                            value="{{ old('prix_vente') }}">
                        @error('prix_vente')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-info-circle"></i> Méthode FEFO
                </div>
                <div style="background: #EDE9FE; border: 1px solid #C4B5FD; border-radius: 10px; padding: 16px;">
                    <p style="font-size: 13px; color: #5B21B6; font-weight: 600; margin-bottom: 8px;">
                        <i class="fas fa-sort-amount-up me-1"></i>First Expired, First Out
                    </p>
                    <p style="font-size: 12px; color: #374151; margin: 0;">
                        Les lots sont automatiquement triés par date d'expiration. Les lots expirant en premier seront proposés en priorité lors des ventes.
                    </p>
                </div>
            </div>
            <div class="form-card">
                <button type="submit" class="btn btn-primary w-100 mb-3 py-2">
                    <i class="fas fa-save me-2"></i>Enregistrer le Lot
                </button>
                <a href="{{ route('lots.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
    function remplirPrix(select) {
        const prix = select.options[select.selectedIndex].dataset.prix;
        if (prix) {
            document.getElementById('prix_vente').value = prix;
        }
    }
</script>
@endpush