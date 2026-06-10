@extends('layouts.app')

@section('title', 'Nouvelle Vente')

@push('styles')
<style>
    .form-card { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .form-section-title { font-size: 15px; font-weight: 600; color: #065F46; padding-bottom: 12px; border-bottom: 2px solid #D1FAE5; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .form-control:focus { border-color: #10B981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
    .produit-row { background: #F9FAFB; border-radius: 12px; padding: 16px; border: 1px solid #E5E7EB; margin-bottom: 12px; transition: border-color 0.2s; }
    .produit-row:hover { border-color: #10B981; }
    .remove-btn { width: 32px; height: 32px; border-radius: 8px; background: #FEF2F2; color: #DC2626; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; flex-shrink: 0; }
    .remove-btn:hover { background: #DC2626; color: white; }
    .total-card { background: linear-gradient(135deg, #065F46, #10B981); border-radius: 12px; padding: 20px; color: white; }
    .stock-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; }
    .stock-ok  { background: #D1FAE5; color: #065F46; }
    .stock-low { background: #FEF3C7; color: #92400E; }
    .stock-out { background: #FEE2E2; color: #991B1B; }
    .prix-achat-info { font-size: 11px; color: #6B7280; margin-top: 4px; }

    /* Champ de recherche */
    .med-wrap { position: relative; }
    .med-wrap .search-ico { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 13px; pointer-events: none; z-index: 1; }
    .med-wrap input.form-control { padding-left: 34px; }

    /* Carte produit sélectionné */
    .sel-card { display: flex; align-items: center; justify-content: space-between; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 9px 12px; }
    .sel-card .clear-btn { background: none; border: none; color: #EF4444; font-size: 22px; line-height: 1; cursor: pointer; padding: 0; }
    .sel-card .clear-btn:hover { color: #B91C1C; }

    /* Dropdown résultats recherche (inline, par ligne) */
    .sres {
        position: absolute;
        top: calc(100% + 3px);
        left: 0; right: 0;
        z-index: 10000;
        background: white;
        border: 1px solid #D1D5DB;
        border-radius: 10px;
        box-shadow: 0 10px 35px rgba(0,0,0,0.16);
        max-height: 300px;
        overflow-y: auto;
    }
    .sres::-webkit-scrollbar { width: 5px; }
    .sres::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
    .sres-item {
        padding: 9px 14px;
        cursor: pointer;
        border-bottom: 1px solid #F3F4F6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }
    .sres-item:last-child { border-bottom: none; }
    .sres-item:hover { background: #F0FDF4; }
    .sres-item.rupture { opacity: 0.65; }
    .sres-empty { padding: 16px; text-align: center; color: #9CA3AF; font-size: 13px; }
    .sres mark { background: #D1FAE5; color: #065F46; border-radius: 2px; padding: 0 1px; font-weight: 700; font-style: normal; }
    .badge-stk { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-shopping-cart me-2"></i>Nouvelle Vente</h1>
        <p class="text-muted mb-0">Enregistrer une vente de médicaments</p>
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
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ===== SOURCE DE DONNÉES : select caché, lu par JS via le DOM ===== --}}
<select id="produits-source" style="display:none;" aria-hidden="true">
    @foreach($produits as $p)
    @php
        $lot   = $p->lots->first();
        $stock = (int)$p->lots->sum('quantite_disponible');
        $pv    = (float)($p->prix_vente_recommande ?? ($lot ? $lot->prix_achat_unitaire : 0) ?? 0);
        $pa    = (float)($lot ? ($lot->prix_achat_unitaire ?? 0) : 0);
    @endphp
    <option value="{{ $p->id }}"
        data-dci="{{ $p->dci }}"
        data-nom="{{ $p->nom_commercial ?? '' }}"
        data-dosage="{{ $p->dosage ?? '' }}"
        data-stock="{{ $stock }}"
        data-pv="{{ $pv }}"
        data-pa="{{ $pa }}"
        data-ordo="{{ $p->necessite_ordonnance ? '1' : '0' }}">{{ $p->dci }}</option>
    @endforeach
</select>

<form method="POST" action="{{ route('ventes.store') }}" id="formVente">
    @csrf
    <div class="row g-4">

        <div class="col-12 col-lg-8">

            <div class="form-card mb-4">
                <div class="form-section-title"><i class="fas fa-pills"></i> Médicaments</div>

                <div id="lignesContainer">
                    <!-- Ligne 0 -->
                    <div class="produit-row" id="ligne-0">
                        <div class="row g-2 align-items-end">

                            <div class="col-12 col-md-5">
                                <label class="form-label">Médicament <span style="color:#EF4444;">*</span></label>
                                {{-- Champ de recherche visible --}}
                                <div class="med-wrap" id="wrap-0">
                                    <i class="fas fa-search search-ico"></i>
                                    <input type="text" id="sinput-0" class="form-control"
                                        placeholder="Taper pour rechercher..."
                                        autocomplete="off">
                                    <div class="sres" id="sres-0" style="display:none;"></div>
                                </div>
                                {{-- Carte produit sélectionné --}}
                                <div id="selbox-0" style="display:none;">
                                    <div class="sel-card">
                                        <div>
                                            <div class="fw-semibold text-success" id="selname-0" style="font-size:13px;"></div>
                                            <div id="selstk-0" style="font-size:11px;color:#6B7280;"></div>
                                        </div>
                                        <button type="button" class="clear-btn" onclick="gddClear(0)" title="Changer">&#215;</button>
                                    </div>
                                </div>
                                <input type="hidden" name="lignes[0][produit_id]" id="pid-0" class="pid-inp">
                            </div>

                            <div class="col-6 col-md-2">
                                <label class="form-label">Quantité <span style="color:#EF4444;">*</span></label>
                                <input type="number" name="lignes[0][quantite]" class="form-control qte-inp" min="1" value="1" onchange="calculerTotal()">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Prix Vente (GNF) <span style="color:#EF4444;">*</span></label>
                                <input type="number" name="lignes[0][prix_vente]" class="form-control prix-inp" min="0" placeholder="0" onchange="calculerTotal()">
                                <div class="prix-achat-info" id="pai-0"></div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                                <div class="fw-semibold text-success sous-total" style="font-size:13px;padding-bottom:8px;">0 GNF</div>
                            </div>
                        </div>
                        <div class="mt-2" id="si-0"></div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-success btn-sm mt-3" onclick="ajouterLigne()">
                    <i class="fas fa-plus me-1"></i>Ajouter un médicament
                </button>
            </div>

            <!-- Patient -->
            <div class="form-card">
                <div class="form-section-title"><i class="fas fa-user"></i> Informations Patient (Optionnel)</div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nom du Patient</label>
                        <input type="text" name="nom_patient" class="form-control" placeholder="Ex: Mamadou Diallo" value="{{ old('nom_patient') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone_patient" class="form-control" placeholder="Ex: +224 620 000 000" value="{{ old('telephone_patient') }}">
                    </div>
                </div>
                <div class="mt-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="avec_ordonnance" id="avec_ordonnance" value="1" onchange="toggleOrdo(this)">
                        <label class="form-check-label fw-semibold" for="avec_ordonnance"><i class="fas fa-prescription me-1"></i>Vente avec ordonnance</label>
                    </div>
                </div>
            </div>

            <!-- Ordonnance -->
            <div class="form-card mt-4" id="ordonnanceSection" style="display:none; border:2px solid #F59E0B;">
                <div class="form-section-title" style="color:#92400E; border-color:#FDE68A;">
                    <i class="fas fa-file-medical"></i> Informations Ordonnance
                    <span id="ordonnanceBadgeRequired" class="badge ms-2" style="background:#EF4444;display:none;">Obligatoire</span>
                </div>
                <div id="ordonnanceAlert" class="alert mb-3" style="background:#FEF3C7;border:1px solid #F59E0B;color:#92400E;display:none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Ordonnance obligatoire :</strong> un ou plusieurs médicaments sélectionnés nécessitent une ordonnance médicale.
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Prénom Médecin <span class="text-danger" id="req_medecin_prenom"></span></label>
                        <input type="text" name="ordonnance_medecin_prenom" id="ordonnance_medecin_prenom" class="form-control" placeholder="Ex: Mamadou" value="{{ old('ordonnance_medecin_prenom') }}">
                        @error('ordonnance_medecin_prenom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nom Médecin <span class="text-danger" id="req_medecin_nom"></span></label>
                        <input type="text" name="ordonnance_medecin_nom" id="ordonnance_medecin_nom" class="form-control" placeholder="Ex: Diallo" value="{{ old('ordonnance_medecin_nom') }}">
                        @error('ordonnance_medecin_nom')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Date Prescription <span class="text-danger" id="req_date_prescription"></span></label>
                        <input type="date" name="ordonnance_date_prescription" id="ordonnance_date_prescription" class="form-control" value="{{ old('ordonnance_date_prescription') }}">
                        @error('ordonnance_date_prescription')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Numéro Ordonnance</label>
                        <input type="text" name="ordonnance_numero" class="form-control" placeholder="Ex: ORD-2024-001" value="{{ old('ordonnance_numero') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Établissement de Soin</label>
                        <input type="text" name="ordonnance_etablissement" class="form-control" placeholder="Ex: CHU Donka" value="{{ old('ordonnance_etablissement') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Référence Patient</label>
                        <input type="text" name="ordonnance_patient_reference" class="form-control" placeholder="Ex: dossier médical n°..." value="{{ old('ordonnance_patient_reference') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observations</label>
                        <textarea name="ordonnance_observations" class="form-control" rows="2" placeholder="Remarques éventuelles...">{{ old('ordonnance_observations') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-4">
            <div class="total-card mb-4">
                <div style="font-size:13px;opacity:.8;margin-bottom:8px;"><i class="fas fa-receipt me-1"></i>MONTANT TOTAL</div>
                <div id="montantTotal" style="font-size:36px;font-weight:700;font-family:monospace;">0 GNF</div>
                <div style="font-size:12px;opacity:.7;margin-top:8px;" id="nbLignes">0 médicament(s)</div>
            </div>
            <div class="form-card mb-4">
                <div class="form-section-title" style="color:#1E40AF;border-color:#DBEAFE;">
                    <i class="fas fa-money-bill-wave"></i> Paiement
                </div>
                <label class="form-label">Montant Payé (GNF)</label>
                <input type="number" name="montant_paye" id="montantPaye" class="form-control" min="0" placeholder="Laisser vide = montant exact">
                <div class="form-text" id="monnaieInfo"></div>
            </div>
            <div class="form-card">
                <button type="submit" class="btn btn-success w-100 mb-3 py-2 fw-semibold">
                    <i class="fas fa-check-circle me-2"></i>Finaliser la Vente
                </button>
                <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-2"></i>Annuler
                </a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function() {

    /* =========================================================
       1. CHARGER LES PRODUITS DEPUIS LE DOM
    ========================================================= */
    var PRODUITS = [];
    var src = document.getElementById('produits-source');
    if (src) {
        for (var i = 0; i < src.options.length; i++) {
            var o = src.options[i];
            if (!o.value) continue;
            PRODUITS.push({
                id:         parseInt(o.value),
                dci:        o.getAttribute('data-dci')    || '',
                nom:        o.getAttribute('data-nom')    || '',
                dosage:     o.getAttribute('data-dosage') || '',
                stock:      parseInt(o.getAttribute('data-stock') || '0'),
                prixVente:  parseFloat(o.getAttribute('data-pv')  || '0'),
                prixAchat:  parseFloat(o.getAttribute('data-pa')  || '0'),
                ordonnance: parseInt(o.getAttribute('data-ordo')  || '0')
            });
        }
    }

    /* =========================================================
       2. MOTEUR DE RECHERCHE + RENDU DU DROPDOWN
    ========================================================= */
    function searchProduits(query) {
        var q = (query || '').trim().toLowerCase();
        if (!q) return PRODUITS.slice(0, 10);
        var sw = [], co = [];
        for (var i = 0; i < PRODUITS.length; i++) {
            var p   = PRODUITS[i];
            var dci = p.dci.toLowerCase();
            var nom = p.nom.toLowerCase();
            if (dci.indexOf(q) === 0) {
                sw.push(p);
            } else if (dci.indexOf(q) >= 0 || nom.indexOf(q) >= 0 || (p.dci + ' ' + p.nom + ' ' + p.dosage).toLowerCase().indexOf(q) >= 0) {
                co.push(p);
            }
        }
        return sw.concat(co).slice(0, 12);
    }

    function renderDropdown(resEl, query, idx) {
        var q       = (query || '').trim();
        var results = searchProduits(q);

        if (!results.length) {
            resEl.innerHTML = '<div class="sres-empty">'
                + (q ? '<i class="fas fa-search me-1"></i>Aucun résultat pour &laquo;&nbsp;' + esc(q) + '&nbsp;&raquo;' : 'Aucun médicament en stock')
                + '</div>';
        } else {
            resEl.innerHTML = results.map(function(p) {
                var sc  = p.stock > 10 ? 'stock-ok' : p.stock > 0 ? 'stock-low' : 'stock-out';
                var st  = p.stock > 0 ? p.stock + ' unit.' : 'Rupture';
                var nm  = '<strong>' + hl(p.dci, q) + '</strong>'
                    + (p.nom    ? ' <span style="color:#6B7280;">' + hl(p.nom, q) + '</span>' : '')
                    + (p.dosage ? ' <span style="color:#9CA3AF;font-size:11px;"> — ' + esc(p.dosage) + '</span>' : '');
                var ordo = p.ordonnance
                    ? '<div style="font-size:10px;color:#92400E;margin-top:2px;"><i class="fas fa-file-medical" style="margin-right:3px;"></i>Ordonnance requise</div>'
                    : '';
                return '<div class="sres-item' + (p.stock === 0 ? ' rupture' : '') + '" data-id="' + p.id + '" data-idx="' + idx + '">'
                    + '<div style="flex:1;min-width:0;">' + nm + ordo + '</div>'
                    + '<span class="badge-stk ' + sc + '">' + st + '</span>'
                    + '</div>';
            }).join('');
        }
        resEl.style.display = 'block';
    }

    /* =========================================================
       3. ATTACHER LES ÉVÉNEMENTS SUR UNE LIGNE
    ========================================================= */
    var closeTimers = {};

    function attachRow(idx) {
        var inp = document.getElementById('sinput-' + idx);
        var res = document.getElementById('sres-'   + idx);
        if (!inp || !res) return;

        /* Afficher / filtrer au focus */
        inp.addEventListener('focus', function() {
            clearTimeout(closeTimers[idx]);
            renderDropdown(res, inp.value, idx);
        });

        /* Filtrer à chaque frappe */
        inp.addEventListener('input', function() {
            clearTimeout(closeTimers[idx]);
            renderDropdown(res, inp.value, idx);
        });

        /* Masquer après perte de focus (délai pour laisser le clic passer) */
        inp.addEventListener('blur', function() {
            closeTimers[idx] = setTimeout(function() {
                res.style.display = 'none';
            }, 220);
        });

        /* Sélection via clic sur un item */
        res.addEventListener('mousedown', function(e) {
            e.preventDefault(); /* empêche blur avant click */
            var item = e.target.closest('[data-id]');
            if (item) selectProduit(parseInt(item.getAttribute('data-id')), idx);
        });
    }

    /* Attacher la première ligne au chargement */
    attachRow(0);

    /* =========================================================
       4. SÉLECTIONNER UN PRODUIT
    ========================================================= */
    function selectProduit(pid, idx) {
        var p = null;
        for (var i = 0; i < PRODUITS.length; i++) { if (PRODUITS[i].id === pid) { p = PRODUITS[i]; break; } }
        if (!p) return;

        var res = document.getElementById('sres-' + idx);
        if (res) res.style.display = 'none';

        document.getElementById('wrap-'    + idx).style.display = 'none';
        document.getElementById('selbox-'  + idx).style.display = 'block';
        document.getElementById('selname-' + idx).textContent   =
            p.dci + (p.nom ? ' (' + p.nom + ')' : '') + (p.dosage ? ' — ' + p.dosage : '');
        document.getElementById('selstk-'  + idx).textContent   = 'Stock : ' + p.stock + ' unités';
        document.getElementById('pid-'     + idx).value         = pid;

        var row = document.getElementById('ligne-' + idx);
        if (row) {
            row.querySelector('.prix-inp').value = p.prixVente;
            var pai = document.getElementById('pai-' + idx);
            if (pai) pai.innerHTML = p.prixAchat > 0
                ? '<i class="fas fa-info-circle" style="margin-right:3px;"></i>Prix achat&nbsp;: ' + fmt(p.prixAchat) + ' GNF' : '';
            var si = document.getElementById('si-' + idx);
            if (si) {
                var ob = p.ordonnance
                    ? ' <span class="stock-badge" style="background:#FEF3C7;color:#92400E;"><i class="fas fa-file-medical" style="margin-right:3px;"></i>Ordonnance</span>' : '';
                si.innerHTML = p.stock > 10
                    ? '<span class="stock-badge stock-ok"><i class="fas fa-check-circle" style="margin-right:3px;"></i>Stock&nbsp;: ' + p.stock + ' unités</span>' + ob
                    : p.stock > 0
                    ? '<span class="stock-badge stock-low"><i class="fas fa-exclamation-triangle" style="margin-right:3px;"></i>Stock faible&nbsp;: ' + p.stock + ' unités</span>' + ob
                    : '<span class="stock-badge stock-out"><i class="fas fa-times-circle" style="margin-right:3px;"></i>Rupture de stock</span>' + ob;
            }
        }
        calculerTotal();
        checkOrdo();
    }

    /* =========================================================
       5. VIDER UNE LIGNE (bouton ×)
    ========================================================= */
    window.gddClear = function(idx) {
        var res = document.getElementById('sres-' + idx);
        if (res) res.style.display = 'none';
        document.getElementById('wrap-'   + idx).style.display = 'block';
        document.getElementById('selbox-' + idx).style.display = 'none';
        document.getElementById('pid-'    + idx).value = '';
        var inp = document.getElementById('sinput-' + idx);
        if (inp) { inp.value = ''; inp.focus(); }
        var row = document.getElementById('ligne-' + idx);
        if (row) {
            row.querySelector('.prix-inp').value = '';
            var pai = document.getElementById('pai-' + idx); if (pai) pai.innerHTML = '';
            var si  = document.getElementById('si-'  + idx); if (si)  si.innerHTML  = '';
        }
        calculerTotal();
        checkOrdo();
    };

    /* =========================================================
       6. AJOUTER / SUPPRIMER UNE LIGNE
    ========================================================= */
    var ligneCount = 1;

    window.ajouterLigne = function() {
        var idx = ligneCount;
        var h = '<div class="produit-row" id="ligne-' + idx + '">'
            + '<div class="row g-2 align-items-end">'
            + '<div class="col-12 col-md-5">'
            + '<label class="form-label">Médicament</label>'
            + '<div class="med-wrap" id="wrap-' + idx + '">'
            + '<i class="fas fa-search search-ico"></i>'
            + '<input type="text" id="sinput-' + idx + '" class="form-control" placeholder="Taper pour rechercher..." autocomplete="off">'
            + '<div class="sres" id="sres-' + idx + '" style="display:none;"></div>'
            + '</div>'
            + '<div id="selbox-' + idx + '" style="display:none;"><div class="sel-card">'
            + '<div><div class="fw-semibold text-success" id="selname-' + idx + '" style="font-size:13px;"></div>'
            + '<div id="selstk-' + idx + '" style="font-size:11px;color:#6B7280;"></div></div>'
            + '<button type="button" class="clear-btn" onclick="gddClear(' + idx + ')">&#215;</button>'
            + '</div></div>'
            + '<input type="hidden" name="lignes[' + idx + '][produit_id]" id="pid-' + idx + '" class="pid-inp">'
            + '</div>'
            + '<div class="col-6 col-md-2"><label class="form-label">Quantité</label>'
            + '<input type="number" name="lignes[' + idx + '][quantite]" class="form-control qte-inp" min="1" value="1" onchange="calculerTotal()"></div>'
            + '<div class="col-6 col-md-3"><label class="form-label">Prix Vente (GNF)</label>'
            + '<input type="number" name="lignes[' + idx + '][prix_vente]" class="form-control prix-inp" min="0" placeholder="0" onchange="calculerTotal()">'
            + '<div class="prix-achat-info" id="pai-' + idx + '"></div></div>'
            + '<div class="col-12 col-md-2 d-flex align-items-end gap-2">'
            + '<div class="fw-semibold text-success sous-total" style="font-size:13px;padding-bottom:8px;">0 GNF</div>'
            + '<button type="button" class="remove-btn mb-1" onclick="supprimerLigne(' + idx + ')"><i class="fas fa-times"></i></button>'
            + '</div></div>'
            + '<div class="mt-2" id="si-' + idx + '"></div></div>';

        document.getElementById('lignesContainer').insertAdjacentHTML('beforeend', h);
        ligneCount++;
        attachRow(idx);
        calculerTotal();
        setTimeout(function() {
            var inp = document.getElementById('sinput-' + idx);
            if (inp) inp.focus();
        }, 50);
    };

    window.supprimerLigne = function(idx) {
        var el = document.getElementById('ligne-' + idx);
        if (el) { el.remove(); calculerTotal(); checkOrdo(); }
    };

    /* =========================================================
       7. ORDONNANCE
    ========================================================= */
    function checkOrdo() {
        var requise = false;
        document.querySelectorAll('.pid-inp').forEach(function(inp) {
            if (!inp.value) return;
            var pid = parseInt(inp.value);
            for (var j = 0; j < PRODUITS.length; j++) {
                if (PRODUITS[j].id === pid && PRODUITS[j].ordonnance) { requise = true; }
            }
        });
        var sec  = document.getElementById('ordonnanceSection');
        var alrt = document.getElementById('ordonnanceAlert');
        var bdge = document.getElementById('ordonnanceBadgeRequired');
        var chk  = document.getElementById('avec_ordonnance');
        var flds = ['ordonnance_medecin_prenom', 'ordonnance_medecin_nom', 'ordonnance_date_prescription'];
        if (requise) {
            sec.style.display = alrt.style.display = '';
            bdge.style.display = '';
            chk.checked = true; chk.disabled = true;
            flds.forEach(function(id) { document.getElementById(id).setAttribute('required', 'required'); });
            document.getElementById('req_medecin_prenom').textContent    = '*';
            document.getElementById('req_medecin_nom').textContent       = '*';
            document.getElementById('req_date_prescription').textContent = '*';
        } else {
            bdge.style.display = alrt.style.display = 'none';
            chk.disabled = false;
            flds.forEach(function(id) { document.getElementById(id).removeAttribute('required'); });
            document.getElementById('req_medecin_prenom').textContent    = '';
            document.getElementById('req_medecin_nom').textContent       = '';
            document.getElementById('req_date_prescription').textContent = '';
            if (!chk.checked) sec.style.display = 'none';
        }
    }

    window.toggleOrdo = function(cb) {
        var sec = document.getElementById('ordonnanceSection');
        if (cb.checked) { sec.style.display = ''; return; }
        var requise = false;
        document.querySelectorAll('.pid-inp').forEach(function(inp) {
            if (!inp.value) return;
            var pid = parseInt(inp.value);
            for (var j = 0; j < PRODUITS.length; j++) {
                if (PRODUITS[j].id === pid && PRODUITS[j].ordonnance) requise = true;
            }
        });
        if (!requise) sec.style.display = 'none';
    };

    /* =========================================================
       8. CALCUL DU TOTAL
    ========================================================= */
    window.calculerTotal = function() {
        var total = 0, nb = 0;
        document.querySelectorAll('.produit-row').forEach(function(row) {
            var q  = parseFloat((row.querySelector('.qte-inp')  || {}).value || 0);
            var p  = parseFloat((row.querySelector('.prix-inp') || {}).value || 0);
            var st = q * p;
            total += st;
            var ste = row.querySelector('.sous-total');
            if (ste) ste.textContent = fmt(st) + ' GNF';
            var pid = row.querySelector('.pid-inp');
            if (pid && pid.value) nb++;
        });
        document.getElementById('montantTotal').textContent = fmt(total) + ' GNF';
        document.getElementById('nbLignes').textContent     = nb + ' médicament(s)';
        var paye = parseFloat((document.getElementById('montantPaye') || {}).value || 0);
        var info = document.getElementById('monnaieInfo');
        if (paye > 0 && info) {
            var m = paye - total;
            info.innerHTML = m >= 0
                ? '<span class="text-success fw-semibold">Monnaie à rendre&nbsp;: ' + fmt(m) + ' GNF</span>'
                : '<span class="text-danger fw-semibold">Insuffisant&nbsp;: ' + fmt(Math.abs(m)) + ' GNF manquant</span>';
        }
    };

    var mpEl = document.getElementById('montantPaye');
    if (mpEl) mpEl.addEventListener('input', calculerTotal);

    /* =========================================================
       9. UTILITAIRES
    ========================================================= */
    function fmt(n) { return new Intl.NumberFormat('fr-FR').format(n); }

    function esc(s) {
        return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function hl(text, query) {
        if (!query || !text) return esc(text || '');
        var t    = text.toLowerCase();
        var q    = query.toLowerCase().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var re   = new RegExp(q, 'gi');
        var r    = '', last = 0, m;
        while ((m = re.exec(t)) !== null) {
            r   += esc(text.slice(last, m.index));
            r   += '<mark>' + esc(text.slice(m.index, m.index + m[0].length)) + '</mark>';
            last = m.index + m[0].length;
        }
        return r + esc(text.slice(last));
    }

})();
</script>
@endpush
