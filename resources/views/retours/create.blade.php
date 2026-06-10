@extends('layouts.app')

@section('title', 'Nouveau Retour')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #92400E; padding-bottom: 12px; border-bottom: 2px solid #FEF3C7; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus, .form-select:focus { border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245,158,11,0.15); }
    .required-star { color: #EF4444; margin-left: 2px; }
    .produit-row { background: #F9FAFB; border-radius: 12px; padding: 16px; border: 1px solid #E5E7EB; margin-bottom: 12px; }
    .produit-row:hover { border-color: #F59E0B; }
    .remove-btn { width: 32px; height: 32px; border-radius: 8px; background: #FEF2F2; color: #DC2626; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .remove-btn:hover { background: #DC2626; color: white; }
    .total-card { background: linear-gradient(135deg, #92400E, #F59E0B); border-radius: 12px; padding: 20px; color: white; }
    .vente-details { background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 10px; padding: 16px; margin-top: 12px; display: none; }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('retours.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-undo me-2"></i>Nouveau Retour</h1>
        <p class="text-muted mb-0">Enregistrer un retour de médicaments</p>
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
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Données ventes pour JS -->
<script>
    const ventesData = {
        @foreach($ventes as $vente)
        "{{ $vente->id }}": {
            numero: "{{ $vente->numero_vente }}",
            patient: "{{ $vente->nom_patient ?? 'Patient anonyme' }}",
            date: "{{ $vente->created_at->format('d/m/Y') }}",
            montant: "{{ number_format($vente->montant_total, 0, ',', ' ') }}",
            lignes: [
                @foreach($vente->lignes as $ligne)
                {
                    produit_id: "{{ $ligne->produit_id }}",
                    dci: "{{ $ligne->produit->dci }}",
                    nom_commercial: "{{ $ligne->produit->nom_commercial ?? '' }}",
                    dosage: "{{ $ligne->produit->dosage }}",
                    unite: "{{ $ligne->produit->unite }}",
                    quantite: {{ $ligne->quantite }},
                    prix_unitaire: {{ $ligne->prix_unitaire }},
                },
                @endforeach
            ]
        },
        @endforeach
    };
</script>

<form method="POST" action="{{ route('retours.store') }}">
    @csrf
    <div class="row g-4">

        <!-- Colonne principale -->
        <div class="col-12 col-lg-8">

            <!-- Vente associée OBLIGATOIRE -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-receipt"></i> Vente d'Origine <span class="required-star">*</span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sélectionner la vente <span class="required-star">*</span></label>
                    <select name="vente_id" id="venteSelect"
                        class="form-select @error('vente_id') is-invalid @enderror"
                        onchange="chargerLignesVente(this)" required>
                        <option value="">— Sélectionner une vente —</option>
                        @foreach($ventes as $vente)
                        <option value="{{ $vente->id }}" {{ old('vente_id') == $vente->id ? 'selected' : '' }}>
                            {{ $vente->numero_vente }} —
                            {{ $vente->created_at->format('d/m/Y') }} —
                            {{ $vente->nom_patient ?? 'Patient anonyme' }} —
                            {{ number_format($vente->montant_total, 0, ',', ' ') }} GNF
                        </option>
                        @endforeach
                    </select>
                    @error('vente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    <!-- Détails vente -->
                    <div class="vente-details" id="venteDetails">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted">N° Vente</small>
                                <div class="fw-semibold" id="detailNumero"></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Patient</small>
                                <div class="fw-semibold" id="detailPatient"></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Date</small>
                                <div class="fw-semibold" id="detailDate"></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Montant</small>
                                <div class="fw-semibold" id="detailMontant"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produits -->
            <div class="form-card mb-4">
                <div class="form-section-title">
                    <i class="fas fa-pills"></i> Produits à Retourner
                </div>

                <div id="lignesContainer">
                    <div style="text-align: center; padding: 30px; color: #9CA3AF;" id="emptyMessage">
                        <i class="fas fa-arrow-up" style="font-size: 24px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                        Sélectionnez d'abord une vente pour voir les produits
                    </div>
                </div>

                <div id="ajouterBtn" style="display: none;">
                    <button type="button" class="btn btn-outline-warning btn-sm mt-3" onclick="ajouterLigne()">
                        <i class="fas fa-plus me-1"></i>Ajouter un produit de la vente
                    </button>
                </div>
            </div>

            <!-- Motif général -->
            <div class="form-card">
                <div class="form-section-title">
                    <i class="fas fa-comment-alt"></i> Motif Général du Retour
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif <span class="required-star">*</span></label>
                    <textarea name="motif" class="form-control @error('motif') is-invalid @enderror"
                        rows="3"
                        placeholder="Décrivez la raison générale du retour...">{{ old('motif') }}</textarea>
                    @error('motif')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Motifs fréquents :</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach(['Produits endommagés', 'Erreur de dispensation', 'Médicaments périmés', 'Allergie patient', 'Traitement arrêté', 'Surdosage prescrit'] as $motifRapide)
                        <button type="button" class="btn btn-outline-warning btn-sm"
                            onclick="document.querySelector('[name=motif]').value = '{{ $motifRapide }}'">
                            {{ $motifRapide }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        <!-- Colonne latérale -->
        <div class="col-12 col-lg-4">

            <!-- Résumé -->
            <div class="total-card mb-4">
                <div style="font-size: 13px; opacity: 0.8; margin-bottom: 8px;">
                    <i class="fas fa-undo me-1"></i>RÉSUMÉ DU RETOUR
                </div>
                <div id="nbProduits" style="font-size: 28px; font-weight: 700;">0 produit</div>
                <div style="font-size: 12px; opacity: 0.7; margin-top: 8px;" id="nbUnites">0 unité au total</div>
                <div style="font-size: 14px; font-weight: 600; margin-top: 8px;" id="montantTotal"></div>
            </div>

            <!-- Info -->
            <div class="form-card mb-4">
                <div class="form-section-title" style="color: #1E40AF; border-color: #DBEAFE;">
                    <i class="fas fa-info-circle"></i> Information
                </div>
                <div style="background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 10px; padding: 16px; margin-bottom: 12px;">
                    <p style="font-size: 13px; color: #92400E; margin: 0;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        La vente est <strong>obligatoire</strong> pour tracer le retour et déduire du bon stock.
                    </p>
                </div>
                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 16px;">
                    <p style="font-size: 13px; color: #1E40AF; margin: 0;">
                        <i class="fas fa-info-circle me-1"></i>
                        Le retour sera soumis à validation avant que le stock soit remis à jour.
                    </p>
                </div>
            </div>

            <!-- Boutons -->
            <div class="form-card">
                <button type="submit" class="btn btn-warning w-100 mb-3 py-2 fw-semibold">
                    <i class="fas fa-undo me-2"></i>Enregistrer le Retour
                </button>
                <a href="{{ route('retours.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    let ligneCount = 0;
    let venteSelectionnee = null;

    function chargerLignesVente(select) {
        const venteId = select.value;
        const container = document.getElementById('lignesContainer');
        const emptyMessage = document.getElementById('emptyMessage');
        const ajouterBtn = document.getElementById('ajouterBtn');
        const venteDetails = document.getElementById('venteDetails');

        if (!venteId) {
            container.innerHTML = '<div style="text-align: center; padding: 30px; color: #9CA3AF;" id="emptyMessage"><i class="fas fa-arrow-up" style="font-size: 24px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>Sélectionnez d\'abord une vente pour voir les produits</div>';
            ajouterBtn.style.display = 'none';
            venteDetails.style.display = 'none';
            calculerTotal();
            return;
        }

        const vente = ventesData[venteId];
        venteSelectionnee = vente;

        // Afficher détails vente
        document.getElementById('detailNumero').textContent = vente.numero;
        document.getElementById('detailPatient').textContent = vente.patient;
        document.getElementById('detailDate').textContent = vente.date;
        document.getElementById('detailMontant').textContent = vente.montant + ' GNF';
        venteDetails.style.display = 'block';

        // Vider le container
        container.innerHTML = '';
        ligneCount = 0;

        // Ajouter une ligne par produit de la vente
        vente.lignes.forEach(function(ligne) {
            ajouterLigneVente(ligne);
        });

        ajouterBtn.style.display = 'block';
        calculerTotal();
    }

    function ajouterLigneVente(ligneVente) {
        const container = document.getElementById('lignesContainer');
        const index = ligneCount;

        const nomProduit = ligneVente.dci + (ligneVente.nom_commercial ? ' (' + ligneVente.nom_commercial + ')' : '') + ' — ' + ligneVente.dosage;

        const html = '<div class="produit-row" id="ligne-' + index + '">' +
            '<input type="hidden" name="lignes[' + index + '][produit_id]" value="' + ligneVente.produit_id + '">' +
            '<div class="row g-2 align-items-end">' +
            '<div class="col-12 col-md-4">' +
            '<label class="form-label">Produit</label>' +
            '<div style="background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600;">' +
            '<i class="fas fa-pills me-2 text-warning"></i>' + nomProduit +
            '</div>' +
            '</div>' +
            '<div class="col-6 col-md-2">' +
            '<label class="form-label">Qté vendue</label>' +
            '<div style="background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; text-align: center;">' +
            ligneVente.quantite + ' ' + ligneVente.unite +
            '</div>' +
            '</div>' +
            '<div class="col-6 col-md-2">' +
            '<label class="form-label">Qté retour <span style="color:#EF4444">*</span></label>' +
            '<input type="number" name="lignes[' + index + '][quantite]" class="form-control quantite-input" min="0" max="' + ligneVente.quantite + '" value="0" onchange="calculerTotal()" style="text-align: center;">' +
            '</div>' +
            '<div class="col-12 col-md-3">' +
            '<label class="form-label">Motif spécifique</label>' +
            '<input type="text" name="lignes[' + index + '][motif_ligne]" class="form-control" placeholder="Optionnel...">' +
            '</div>' +
            '<div class="col-12 col-md-1 d-flex align-items-end justify-content-end">' +
            '<button type="button" class="remove-btn mb-1" onclick="supprimerLigne(' + index + ')" title="Retirer ce produit">' +
            '<i class="fas fa-times"></i>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>';

        container.insertAdjacentHTML('beforeend', html);
        ligneCount++;
    }

    function ajouterLigne() {
        if (!venteSelectionnee) return;

        const container = document.getElementById('lignesContainer');
        const index = ligneCount;

        // Construire options depuis les produits de la vente
        const options = venteSelectionnee.lignes.map(function(l) {
            const nom = l.dci + (l.nom_commercial ? ' (' + l.nom_commercial + ')' : '') + ' — ' + l.dosage;
            return '<option value="' + l.produit_id + '" data-max="' + l.quantite + '">' + nom + '</option>';
        }).join('');

        const html = '<div class="produit-row" id="ligne-' + index + '">' +
            '<div class="row g-2 align-items-end">' +
            '<div class="col-12 col-md-4">' +
            '<label class="form-label">Produit</label>' +
            '<select name="lignes[' + index + '][produit_id]" class="form-select">' + options + '</select>' +
            '</div>' +
            '<div class="col-6 col-md-2">' +
            '<label class="form-label">Qté retour <span style="color:#EF4444">*</span></label>' +
            '<input type="number" name="lignes[' + index + '][quantite]" class="form-control quantite-input" min="1" value="1" onchange="calculerTotal()">' +
            '</div>' +
            '<div class="col-6 col-md-4">' +
            '<label class="form-label">Motif spécifique</label>' +
            '<input type="text" name="lignes[' + index + '][motif_ligne]" class="form-control" placeholder="Optionnel...">' +
            '</div>' +
            '<div class="col-12 col-md-2 d-flex align-items-end gap-2">' +
            '<button type="button" class="remove-btn mb-1" onclick="supprimerLigne(' + index + ')"><i class="fas fa-times"></i></button>' +
            '</div></div></div>';

        container.insertAdjacentHTML('beforeend', html);
        ligneCount++;
        calculerTotal();
    }

    function supprimerLigne(index) {
        const ligne = document.getElementById('ligne-' + index);
        if (ligne) {
            ligne.remove();
            calculerTotal();
        }
    }

    function calculerTotal() {
        const lignes = document.querySelectorAll('.produit-row');
        let totalUnites = 0;
        let nbProduits = lignes.length;

        lignes.forEach(function(ligne) {
            const qInput = ligne.querySelector('.quantite-input');
            const quantite = parseInt(qInput ? qInput.value : 0) || 0;
            totalUnites += quantite;
        });

        document.getElementById('nbProduits').textContent = nbProduits + ' produit' + (nbProduits > 1 ? 's' : '');
        document.getElementById('nbUnites').textContent = totalUnites + ' unité' + (totalUnites > 1 ? 's' : '') + ' au total';
    }
</script>
@endpush