@extends('layouts.app')

@section('title', 'Réception Livraison — ' . $commande->numero_commande)

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #065F46; padding-bottom: 12px; border-bottom: 2px solid #D1FAE5; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #10B981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
    .ligne-card { background: #F9FAFB; border-radius: 12px; padding: 20px; border: 1px solid #E5E7EB; margin-bottom: 16px; }
    .ligne-header { font-weight: 700; font-size: 15px; color: #1F2937; margin-bottom: 4px; }
    .ligne-sub { font-size: 12px; color: #6B7280; margin-bottom: 16px; }
    .qte-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background: #DBEAFE; color: #1E40AF; }
    .total-card { background: linear-gradient(135deg, #065F46, #10B981); border-radius: 12px; padding: 20px; color: white; position: sticky; top: 20px; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('commandes.show', $commande) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-boxes me-2"></i>Réception de Livraison</h1>
        <p class="text-muted mb-0">
            Commande <strong>{{ $commande->numero_commande }}</strong> —
            Fournisseur : <strong>{{ $commande->fournisseur->nom }}</strong>
        </p>
    </div>
</div>

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

<div class="alert mb-4" style="background:#ECFDF5; border:1px solid #6EE7B7; color:#065F46;">
    <i class="fas fa-info-circle me-2"></i>
    Saisissez pour chaque produit : la <strong>quantité effectivement reçue</strong>, le <strong>prix unitaire</strong> figurant sur le bon de livraison, et les informations du lot (numéro, dates).
</div>

<form method="POST" action="{{ route('commandes.reception.store', $commande) }}">
    @csrf
    <div class="row g-4">

        <!-- Lignes produits -->
        <div class="col-12 col-lg-8">
            @foreach($commande->lignes as $ligne)
            <div class="ligne-card">
                <div class="ligne-header">
                    {{ $ligne->produit->dci }}
                    @if($ligne->produit->nom_commercial)
                        <span style="font-weight:400; color:#6B7280;">({{ $ligne->produit->nom_commercial }})</span>
                    @endif
                </div>
                <div class="ligne-sub">
                    {{ $ligne->produit->forme_galenique }} — {{ $ligne->produit->dosage }} — {{ $ligne->produit->unite }}
                    &nbsp;|&nbsp;
                    <span class="qte-badge"><i class="fas fa-shopping-cart me-1"></i>Commandé : {{ $ligne->quantite_commandee }} {{ $ligne->produit->unite }}</span>
                </div>

                <div class="row g-3">
                    <!-- Quantité reçue -->
                    <div class="col-6 col-md-3">
                        <label class="form-label">Qté reçue <span class="text-danger">*</span></label>
                        <input type="number"
                            name="lignes[{{ $ligne->id }}][quantite_recue]"
                            class="form-control quantite-input"
                            min="0"
                            max="{{ $ligne->quantite_commandee }}"
                            value="{{ old('lignes.' . $ligne->id . '.quantite_recue', $ligne->quantite_commandee) }}"
                            onchange="calculerTotal()">
                        @error('lignes.' . $ligne->id . '.quantite_recue')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Prix unitaire -->
                    <div class="col-6 col-md-3">
                        <label class="form-label">Prix unitaire (GNF) <span class="text-danger">*</span></label>
                        <input type="number"
                            name="lignes[{{ $ligne->id }}][prix_unitaire]"
                            class="form-control prix-input"
                            min="0"
                            placeholder="0"
                            value="{{ old('lignes.' . $ligne->id . '.prix_unitaire') }}"
                            onchange="calculerTotal()">
                        @error('lignes.' . $ligne->id . '.prix_unitaire')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Numéro de lot -->
                    <div class="col-12 col-md-3">
                        <label class="form-label">Numéro de lot <span class="text-danger">*</span></label>
                        <input type="text"
                            name="lignes[{{ $ligne->id }}][numero_lot]"
                            class="form-control"
                            placeholder="Ex: LOT-2024-001"
                            value="{{ old('lignes.' . $ligne->id . '.numero_lot') }}">
                        @error('lignes.' . $ligne->id . '.numero_lot')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date expiration -->
                    <div class="col-6 col-md-3">
                        <label class="form-label">Date d'expiration <span class="text-danger">*</span></label>
                        <input type="date"
                            name="lignes[{{ $ligne->id }}][date_expiration]"
                            class="form-control"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            value="{{ old('lignes.' . $ligne->id . '.date_expiration') }}">
                        @error('lignes.' . $ligne->id . '.date_expiration')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Date fabrication -->
                    <div class="col-6 col-md-3">
                        <label class="form-label">Date fabrication <span class="text-muted" style="font-weight:400;">(optionnel)</span></label>
                        <input type="date"
                            name="lignes[{{ $ligne->id }}][date_fabrication]"
                            class="form-control"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('lignes.' . $ligne->id . '.date_fabrication') }}">
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">
            <div class="total-card mb-4">
                <div style="font-size: 13px; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fas fa-receipt me-1"></i>MONTANT TOTAL RÉCEPTION
                </div>
                <div id="montantTotal" style="font-size: 30px; font-weight: 700; font-family: monospace;">
                    0 GNF
                </div>
                <div style="font-size: 12px; opacity: 0.7; margin-top: 8px;">
                    Calculé selon les prix et quantités saisis
                </div>
            </div>

            <div class="form-card">
                <button type="submit" class="btn btn-success w-100 mb-3 py-2 fw-semibold"
                    onclick="return confirm('Confirmer la réception ? Les lots seront ajoutés au stock.')">
                    <i class="fas fa-check-circle me-2"></i>Confirmer la Réception
                </button>
                <a href="{{ route('commandes.show', $commande) }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function calculerTotal() {
        const lignes = document.querySelectorAll('.ligne-card');
        let total = 0;
        lignes.forEach(function(ligne) {
            const qte   = parseFloat(ligne.querySelector('.quantite-input')?.value || 0);
            const prix  = parseFloat(ligne.querySelector('.prix-input')?.value || 0);
            total += qte * prix;
        });
        document.getElementById('montantTotal').textContent =
            new Intl.NumberFormat('fr-FR').format(total) + ' GNF';
    }
</script>
@endpush
