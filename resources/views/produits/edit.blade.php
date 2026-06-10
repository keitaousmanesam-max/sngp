@extends('layouts.app')

@section('title', 'Modifier Produit')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #7C3AED; padding-bottom: 12px; border-bottom: 2px solid #EDE9FE; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #8B5CF6; box-shadow: 0 0 0 3px rgba(139,92,246,0.15); }
    .required-star { color: #EF4444; margin-left: 2px; }
    .maladie-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 8px; border: 1px solid #E5E7EB; cursor: pointer; transition: all 0.2s; }
    .maladie-item:hover { background: #EDE9FE; border-color: #8B5CF6; }
    .maladie-item.selected { background: #EDE9FE; border-color: #7C3AED; }
    .maladie-item input[type="checkbox"] { accent-color: #7C3AED; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('produits.show', $produit) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-edit me-2"></i>Modifier le Produit</h1>
        <p class="text-muted mb-0">{{ $produit->dci }} — {{ $produit->code_produit }}</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('produits.update', $produit) }}">
    @csrf @method('PUT')
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Identification -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-pills"></i> Identification du Produit
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">DCI <span class="required-star">*</span></label>
                        <input type="text" name="dci" class="form-control @error('dci') is-invalid @enderror"
                            value="{{ old('dci', $produit->dci) }}">
                        @error('dci')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nom Commercial</label>
                        <input type="text" name="nom_commercial" class="form-control @error('nom_commercial') is-invalid @enderror"
                            value="{{ old('nom_commercial', $produit->nom_commercial) }}">
                        @error('nom_commercial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Catégorie <span class="required-star">*</span></label>
                        <select name="categorie_id" class="form-select @error('categorie_id') is-invalid @enderror">
                            <option value="">Sélectionner</option>
                            @foreach($categories as $categorie)
                            <option value="{{ $categorie->id }}" {{ old('categorie_id', $produit->categorie_id) == $categorie->id ? 'selected' : '' }}>
                                {{ $categorie->nom }}
                            </option>
                            @endforeach
                        </select>
                        @error('categorie_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Caractéristiques -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-flask"></i> Caractéristiques Galéniques
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Forme Galénique <span class="required-star">*</span></label>
                        <select name="forme_galenique" class="form-select @error('forme_galenique') is-invalid @enderror">
                            @foreach(['Comprimé', 'Gélule', 'Sirop', 'Injectable', 'Crème', 'Pommade', 'Suppositoire', 'Sachet', 'Gouttes', 'Spray', 'Patch', 'Autre'] as $forme)
                            <option value="{{ $forme }}" {{ old('forme_galenique', $produit->forme_galenique) == $forme ? 'selected' : '' }}>{{ $forme }}</option>
                            @endforeach
                        </select>
                        @error('forme_galenique')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Dosage <span class="required-star">*</span></label>
                        <input type="text" name="dosage" class="form-control @error('dosage') is-invalid @enderror"
                            value="{{ old('dosage', $produit->dosage) }}">
                        @error('dosage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Unité <span class="required-star">*</span></label>
                        <select name="unite" class="form-select @error('unite') is-invalid @enderror">
                            @foreach(['Boîte', 'Flacon', 'Ampoule', 'Sachet', 'Comprimé', 'Tube', 'Seringue', 'Autre'] as $unite)
                            <option value="{{ $unite }}" {{ old('unite', $produit->unite) == $unite ? 'selected' : '' }}>{{ $unite }}</option>
                            @endforeach
                        </select>
                        @error('unite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Codes et Prix -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-barcode"></i> Codes et Prix
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Code Produit <span class="required-star">*</span></label>
                        <input type="text" name="code_produit" class="form-control @error('code_produit') is-invalid @enderror"
                            value="{{ old('code_produit', $produit->code_produit) }}">
                        @error('code_produit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Code-barres</label>
                        <input type="text" name="code_barre" class="form-control @error('code_barre') is-invalid @enderror"
                            value="{{ old('code_barre', $produit->code_barre) }}">
                        @error('code_barre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Prix Recommandé (GNF)</label>
                        <input type="number" name="prix_vente_recommande" class="form-control @error('prix_vente_recommande') is-invalid @enderror"
                            value="{{ old('prix_vente_recommande', $produit->prix_vente_recommande) }}" min="0">
                        @error('prix_vente_recommande')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Maladies -->
            @if($maladies->count() > 0)
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-virus"></i> Maladies Traitées
                </div>
                @php $maladiesSelectionnees = old('maladies', $produit->maladies->pluck('id')->toArray()); @endphp
                <div class="row g-2">
                    @foreach($maladies as $maladie)
                    <div class="col-12 col-md-6">
                        <label class="maladie-item {{ in_array($maladie->id, $maladiesSelectionnees) ? 'selected' : '' }}"
                            onclick="this.classList.toggle('selected')">
                            <input type="checkbox" name="maladies[]" value="{{ $maladie->id }}"
                                {{ in_array($maladie->id, $maladiesSelectionnees) ? 'checked' : '' }}>
                            <span style="font-size: 13px;">{{ $maladie->nom }}</span>
                            @if($maladie->code_cim10)
                            <span style="background: #FEF3C7; color: #92400E; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: auto;">
                                {{ $maladie->code_cim10 }}
                            </span>
                            @endif
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Description -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-sticky-note"></i> Description
                </div>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $produit->description) }}</textarea>
            </div>

        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">

            <!-- Statut -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-toggle-on"></i> Statut du Produit
                </div>
                <select name="statut" class="form-select">
                    <option value="actif" {{ old('statut', $produit->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ old('statut', $produit->statut) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="retire" {{ old('statut', $produit->statut) == 'retire' ? 'selected' : '' }}>Retiré du marché</option>
                </select>
            </div>

            <!-- Ordonnance -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-prescription"></i> Prescription
                </div>
                <div style="background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 16px;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="necessite_ordonnance"
                            id="necessite_ordonnance" value="1"
                            {{ old('necessite_ordonnance', $produit->necessite_ordonnance) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="necessite_ordonnance">
                            Nécessite une ordonnance
                        </label>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="form-card">
                <button type="submit" class="btn btn-primary w-100 mb-3 py-2">
                    <i class="fas fa-save me-2"></i>Enregistrer les Modifications
                </button>
                <a href="{{ route('produits.show', $produit) }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>

        </div>
    </div>
</form>

@endsection