@extends('layouts.app')

@section('title', 'Nouvelle Commande')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #1E40AF; padding-bottom: 12px; border-bottom: 2px solid #DBEAFE; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    .produit-row { background: #F9FAFB; border-radius: 12px; padding: 16px; border: 1px solid #E5E7EB; margin-bottom: 12px; }
    .produit-row:hover { border-color: #3B82F6; }
    .remove-btn { width: 32px; height: 32px; border-radius: 8px; background: #FEF2F2; color: #DC2626; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .remove-btn:hover { background: #DC2626; color: white; }
    .total-card { background: linear-gradient(135deg, #1E3A8A, #3B82F6); border-radius: 12px; padding: 20px; color: white; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-file-invoice me-2"></i>Nouvelle Commande</h1>
        <p class="text-muted mb-0">Créer une commande auprès d'un fournisseur</p>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="fas fa-exclamation-circle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('commandes.store') }}">
    @csrf
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Fournisseur -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-truck"></i> Fournisseur
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Fournisseur <span style="color: #EF4444;">*</span></label>
                        <select name="fournisseur_id" class="form-select @error('fournisseur_id') is-invalid @enderror">
                            <option value="">Sélectionner un fournisseur</option>
                            @foreach($fournisseurs as $fournisseur)
                            <option value="{{ $fournisseur->id }}" {{ old('fournisseur_id') == $fournisseur->id ? 'selected' : '' }}>
                                {{ $fournisseur->nom }}
                            </option>
                            @endforeach
                        </select>
                        @error('fournisseur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Date de Livraison Prévue</label>
                        <input type="date" name="date_livraison_prevue"
                            class="form-control @error('date_livraison_prevue') is-invalid @enderror"
                            value="{{ old('date_livraison_prevue') }}"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        @error('date_livraison_prevue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Produits -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-pills"></i> Produits à Commander
                </div>

                <div class="alert mb-3" style="background:#EFF6FF; border:1px solid #BFDBFE; color:#1E40AF; font-size:13px;">
                    <i class="fas fa-info-circle me-2"></i>
                    Le prix unitaire sera saisi à la <strong>réception de la livraison</strong>, une fois le bon de livraison du fournisseur disponible.
                </div>

                <div id="lignesContainer">
                    <div class="produit-row" id="ligne-0">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-7">
                                <label class="form-label">Produit <span style="color: #EF4444;">*</span></label>
                                <select name="lignes[0][produit_id]" class="form-select produit-select">
                                    <option value="">Sélectionner un produit</option>
                                    @foreach($produits as $produit)
                                    <option value="{{ $produit->id }}">
                                        {{ $produit->dci }}
                                        @if($produit->nom_commercial) ({{ $produit->nom_commercial }}) @endif
                                        — {{ $produit->dosage }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-8 col-md-3">
                                <label class="form-label">Quantité commandée <span style="color: #EF4444;">*</span></label>
                                <input type="number" name="lignes[0][quantite]" class="form-control quantite-input"
                                    min="1" value="1">
                            </div>
                            <div class="col-4 col-md-2 d-flex align-items-end justify-content-end">
                                <!-- supprimer ligne : masqué sur ligne 0, affiché sur les suivantes -->
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="ajouterLigne()">
                    <i class="fas fa-plus me-1"></i>Ajouter un produit
                </button>
            </div>

            <!-- Observations -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-sticky-note"></i> Observations
                </div>
                <textarea name="observations" class="form-control" rows="3"
                    placeholder="Instructions ou remarques pour le fournisseur...">{{ old('observations') }}</textarea>
            </div>

        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">

            <!-- Résumé -->
            <div class="total-card mb-4">
                <div style="font-size: 13px; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fas fa-boxes me-1"></i>RÉSUMÉ COMMANDE
                </div>
                <div id="nbLignes" style="font-size: 28px; font-weight: 700; font-family: monospace;">
                    0 produit(s)
                </div>
                <div style="font-size: 12px; opacity: 0.7; margin-top: 8px;">
                    Prix définis à la réception
                </div>
            </div>

            <!-- Boutons -->
            <div class="form-card">
                <button type="submit" class="btn btn-primary w-100 mb-3 py-2 fw-semibold">
                    <i class="fas fa-save me-2"></i>Créer la Commande
                </button>
                <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    let ligneCount = 1;

    function ajouterLigne() {
        const container = document.getElementById('lignesContainer');
        const index = ligneCount;

        const options = Array.from(document.querySelector('.produit-select').options)
            .map(function(o) {
                return '<option value="' + o.value + '">' + o.text + '</option>';
            }).join('');

        const html = '<div class="produit-row" id="ligne-' + index + '">' +
            '<div class="row g-2 align-items-end">' +
            '<div class="col-12 col-md-7">' +
            '<label class="form-label">Produit</label>' +
            '<select name="lignes[' + index + '][produit_id]" class="form-select">' + options + '</select>' +
            '</div>' +
            '<div class="col-8 col-md-3">' +
            '<label class="form-label">Quantité commandée</label>' +
            '<input type="number" name="lignes[' + index + '][quantite]" class="form-control quantite-input" min="1" value="1" onchange="compterLignes()">' +
            '</div>' +
            '<div class="col-4 col-md-2 d-flex align-items-end justify-content-end">' +
            '<button type="button" class="remove-btn mb-1" onclick="supprimerLigne(' + index + ')"><i class="fas fa-times"></i></button>' +
            '</div></div></div>';

        container.insertAdjacentHTML('beforeend', html);
        ligneCount++;
        compterLignes();
    }

    function supprimerLigne(index) {
        const ligne = document.getElementById('ligne-' + index);
        if (ligne) {
            ligne.remove();
            compterLignes();
        }
    }

    function compterLignes() {
        const lignes = document.querySelectorAll('.produit-row');
        let nb = 0;
        lignes.forEach(function(ligne) {
            const sel = ligne.querySelector('select');
            if (sel && sel.value) nb++;
        });
        document.getElementById('nbLignes').textContent = nb + ' produit(s)';
    }
</script>
@endpush