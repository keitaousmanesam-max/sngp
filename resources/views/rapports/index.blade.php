@extends('layouts.app')

@section('title', 'Rapports')

@push('styles')
<style>
    /* ===== Stats ===== */
    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        border: 1px solid #EAECF0;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 14px 14px 0 0;
    }
    .stat-blue::before   { background: linear-gradient(90deg, #1E3A8A, #3B82F6); }
    .stat-green::before  { background: linear-gradient(90deg, #065F46, #10B981); }
    .stat-purple::before { background: linear-gradient(90deg, #5B21B6, #8B5CF6); }
    .stat-amber::before  { background: linear-gradient(90deg, #92400E, #F59E0B); }
    .stat-red::before    { background: linear-gradient(90deg, #991B1B, #EF4444); }
    .stat-teal::before   { background: linear-gradient(90deg, #0F766E, #14B8A6); }

    .stat-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }
    .stat-value  { font-size: 21px; font-weight: 700; line-height: 1.2; }
    .stat-label  { font-size: 10.5px; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

    /* ===== Report Cards ===== */
    .report-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        border: 1px solid #EAECF0;
        overflow: hidden;
        transition: transform 0.25s, box-shadow 0.25s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .report-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }

    .rc-header {
        padding: 18px 22px 15px;
        border-bottom: 1px solid #F3F4F6;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .rc-icon {
        width: 46px; height: 46px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }
    .rc-title { font-size: 14.5px; font-weight: 700; color: #111827; margin-bottom: 3px; }
    .rc-desc  { font-size: 12px; color: #6B7280; line-height: 1.5; }

    .rc-body { padding: 16px 22px 20px; flex: 1; display: flex; flex-direction: column; }

    /* Date filter */
    .date-filter { background: #F9FAFB; border-radius: 10px; padding: 13px 14px; margin-bottom: 12px; }
    .date-filter .form-label { font-size: 10.5px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }

    .date-shortcuts { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 10px; }
    .ds {
        font-size: 10.5px;
        padding: 3px 9px;
        border-radius: 20px;
        border: 1px solid #E5E7EB;
        background: white;
        color: #374151;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
        user-select: none;
    }
    .ds:hover { background: #1E3A8A; color: white; border-color: #1E3A8A; }

    /* Info badge */
    .info-badge {
        display: flex; align-items: center; gap: 8px;
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 12.5px;
        font-weight: 500;
        margin-bottom: 14px;
    }

    /* Export buttons */
    .export-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 9px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        flex: 1;
    }
    .export-btn:hover { filter: brightness(0.9); transform: translateY(-1px); }
    .export-btn:active { transform: translateY(0); filter: brightness(0.85); }
    .export-btn-pdf   { background: #EF4444; color: white; }
    .export-btn-excel { background: #16A34A; color: white; }
    .export-btn.loading { opacity: 0.7; pointer-events: none; }
    .export-btn .spin-ico { display: none; width: 13px; height: 13px; border: 2px solid rgba(255,255,255,0.35); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
    .export-btn.loading .spin-ico  { display: inline-block; }
    .export-btn.loading .btn-ico   { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Toast */
    #export-toast {
        position: fixed; bottom: 24px; right: 24px;
        background: #1E3A8A; color: white;
        padding: 11px 18px; border-radius: 10px;
        font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.18);
        z-index: 9999;
        transform: translateY(80px);
        opacity: 0;
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
    }
    #export-toast.show { transform: translateY(0); opacity: 1; }

    /* Section headings */
    .s-heading {
        font-size: 11px;
        font-weight: 700;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .s-heading::after { content: ''; flex: 1; height: 1px; background: #E5E7EB; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-chart-bar me-2"></i>Rapports & Exports</h1>
        <p class="text-muted mb-0 small">Générez et exportez les données du système en PDF ou Excel</p>
    </div>
    <span class="badge bg-light text-secondary border px-3 py-2" style="font-size:12px;">
        <i class="fas fa-calendar-alt me-1"></i>{{ now()->format('d/m/Y') }}
    </span>
</div>

<!-- ===== Stats rapides ===== -->
<div class="s-heading">Vue d'ensemble — {{ now()->translatedFormat('F Y') }}</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="stat-card stat-blue">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#DBEAFE;color:#1E40AF;"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <div class="stat-value" style="color:#1E3A8A;">{{ $stats['ventes_mois'] }}</div>
                    <div class="stat-label">Ventes mois</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="stat-card stat-green">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#D1FAE5;color:#065F46;"><i class="fas fa-coins"></i></div>
                <div>
                    <div class="stat-value" style="color:#065F46;font-size:15px;">{{ number_format($stats['ca_mois'], 0, ',', ' ') }}</div>
                    <div class="stat-label">CA mois (GNF)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="stat-card stat-purple">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#EDE9FE;color:#5B21B6;"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <div class="stat-value" style="color:#5B21B6;">{{ $stats['commandes_mois'] }}</div>
                    <div class="stat-label">Commandes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="stat-card stat-amber">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#FEF3C7;color:#92400E;"><i class="fas fa-undo"></i></div>
                <div>
                    <div class="stat-value" style="color:#B45309;">{{ $stats['retours_mois'] }}</div>
                    <div class="stat-label">Retours mois</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="stat-card stat-red">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#FEE2E2;color:#991B1B;"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-value" style="color:#EF4444;">{{ $stats['lots_expires'] }}</div>
                    <div class="stat-label">Lots expirés</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-xl-2">
        <div class="stat-card stat-teal">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#CCFBF1;color:#0F766E;"><i class="fas fa-warehouse"></i></div>
                <div>
                    <div class="stat-value" style="color:#0F766E;font-size:15px;">{{ number_format($stats['valeur_stock'], 0, ',', ' ') }}</div>
                    <div class="stat-label">Valeur stock</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Cartes rapports ===== -->
<div class="s-heading">Générer un rapport</div>
<div class="row g-4">

    <!-- Ventes -->
    <div class="col-12 col-lg-6">
        <div class="report-card">
            <div class="rc-header">
                <div class="rc-icon" style="background:#D1FAE5;color:#065F46;"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <div class="rc-title">Rapport des Ventes</div>
                    <div class="rc-desc">Liste complète des ventes avec montants, produits et vendeurs sur une période donnée.</div>
                </div>
            </div>
            <div class="rc-body">
                <div class="date-filter">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Date début</label>
                            <input type="date" id="ventes_debut" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date fin</label>
                            <input type="date" id="ventes_fin" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="date-shortcuts">
                        <span class="ds" onclick="setRange('ventes','today')">Aujourd'hui</span>
                        <span class="ds" onclick="setRange('ventes','week')">Cette semaine</span>
                        <span class="ds" onclick="setRange('ventes','month')">Ce mois</span>
                        <span class="ds" onclick="setRange('ventes','last_month')">Mois précédent</span>
                        <span class="ds" onclick="setRange('ventes','year')">Cette année</span>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button onclick="doExport('ventes','pdf',this)" class="export-btn export-btn-pdf">
                        <i class="fas fa-file-pdf btn-ico"></i><span class="spin-ico"></span>PDF
                    </button>
                    <button onclick="doExport('ventes','excel',this)" class="export-btn export-btn-excel">
                        <i class="fas fa-file-excel btn-ico"></i><span class="spin-ico"></span>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stocks -->
    <div class="col-12 col-lg-6">
        <div class="report-card">
            <div class="rc-header">
                <div class="rc-icon" style="background:#DBEAFE;color:#1E40AF;"><i class="fas fa-boxes"></i></div>
                <div>
                    <div class="rc-title">Rapport des Stocks</div>
                    <div class="rc-desc">Inventaire complet des stocks avec quantités, prix d'achat, valeurs et dates d'expiration.</div>
                </div>
            </div>
            <div class="rc-body">
                <div class="info-badge" style="background:#EFF6FF;color:#1E40AF;">
                    <i class="fas fa-calendar-check"></i>
                    <span>Instantané du stock à ce jour : <strong>{{ now()->format('d/m/Y') }}</strong></span>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button onclick="doExport('stocks','pdf',this)" class="export-btn export-btn-pdf">
                        <i class="fas fa-file-pdf btn-ico"></i><span class="spin-ico"></span>PDF
                    </button>
                    <button onclick="doExport('stocks','excel',this)" class="export-btn export-btn-excel">
                        <i class="fas fa-file-excel btn-ico"></i><span class="spin-ico"></span>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Commandes -->
    <div class="col-12 col-lg-6">
        <div class="report-card">
            <div class="rc-header">
                <div class="rc-icon" style="background:#EDE9FE;color:#5B21B6;"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <div class="rc-title">Rapport des Commandes</div>
                    <div class="rc-desc">Liste des commandes fournisseurs avec statuts et montants sur une période donnée.</div>
                </div>
            </div>
            <div class="rc-body">
                <div class="date-filter">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Date début</label>
                            <input type="date" id="commandes_debut" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date fin</label>
                            <input type="date" id="commandes_fin" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="date-shortcuts">
                        <span class="ds" onclick="setRange('commandes','today')">Aujourd'hui</span>
                        <span class="ds" onclick="setRange('commandes','week')">Cette semaine</span>
                        <span class="ds" onclick="setRange('commandes','month')">Ce mois</span>
                        <span class="ds" onclick="setRange('commandes','last_month')">Mois précédent</span>
                        <span class="ds" onclick="setRange('commandes','year')">Cette année</span>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button onclick="doExport('commandes','pdf',this)" class="export-btn export-btn-pdf">
                        <i class="fas fa-file-pdf btn-ico"></i><span class="spin-ico"></span>PDF
                    </button>
                    <button onclick="doExport('commandes','excel',this)" class="export-btn export-btn-excel">
                        <i class="fas fa-file-excel btn-ico"></i><span class="spin-ico"></span>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Retours -->
    <div class="col-12 col-lg-6">
        <div class="report-card">
            <div class="rc-header">
                <div class="rc-icon" style="background:#FEF3C7;color:#92400E;"><i class="fas fa-undo"></i></div>
                <div>
                    <div class="rc-title">Rapport des Retours</div>
                    <div class="rc-desc">Liste des retours de médicaments avec motifs, quantités et montants remboursés.</div>
                </div>
            </div>
            <div class="rc-body">
                <div class="date-filter">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Date début</label>
                            <input type="date" id="retours_debut" class="form-control form-control-sm" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date fin</label>
                            <input type="date" id="retours_fin" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="date-shortcuts">
                        <span class="ds" onclick="setRange('retours','today')">Aujourd'hui</span>
                        <span class="ds" onclick="setRange('retours','week')">Cette semaine</span>
                        <span class="ds" onclick="setRange('retours','month')">Ce mois</span>
                        <span class="ds" onclick="setRange('retours','last_month')">Mois précédent</span>
                        <span class="ds" onclick="setRange('retours','year')">Cette année</span>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button onclick="doExport('retours','pdf',this)" class="export-btn export-btn-pdf">
                        <i class="fas fa-file-pdf btn-ico"></i><span class="spin-ico"></span>PDF
                    </button>
                    <button onclick="doExport('retours','excel',this)" class="export-btn export-btn-excel">
                        <i class="fas fa-file-excel btn-ico"></i><span class="spin-ico"></span>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lots Expirés -->
    <div class="col-12 col-lg-6">
        <div class="report-card">
            <div class="rc-header">
                <div class="rc-icon" style="background:#FEE2E2;color:#991B1B;"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="rc-title">Lots Expirés / Proches Expiration</div>
                    <div class="rc-desc">Liste des lots expirés et expirant dans les 90 prochains jours avec quantités disponibles.</div>
                </div>
            </div>
            <div class="rc-body">
                <div class="info-badge" style="background:#FEF2F2;color:#DC2626;">
                    <i class="fas fa-exclamation-triangle"></i>
                    @if($stats['lots_expires'] > 0)
                        <span><strong>{{ $stats['lots_expires'] }}</strong> lot(s) expiré(s) détecté(s) au {{ now()->format('d/m/Y') }}</span>
                    @else
                        <span>Aucun lot expiré à ce jour : <strong>{{ now()->format('d/m/Y') }}</strong></span>
                    @endif
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button onclick="doExport('lots-expires','pdf',this)" class="export-btn export-btn-pdf">
                        <i class="fas fa-file-pdf btn-ico"></i><span class="spin-ico"></span>PDF
                    </button>
                    <button onclick="doExport('lots-expires','excel',this)" class="export-btn export-btn-excel">
                        <i class="fas fa-file-excel btn-ico"></i><span class="spin-ico"></span>Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Toast -->
<div id="export-toast">
    <i class="fas fa-spinner fa-spin"></i>
    <span id="toast-msg">Génération en cours...</span>
</div>

@endsection

@push('scripts')
<script>
    const LABELS = { ventes: 'Ventes', stocks: 'Stocks', commandes: 'Commandes', retours: 'Retours', 'lots-expires': 'Lots Expirés' };

    function doExport(type, format, btn) {
        let url = '/rapports/' + type + '/' + format;
        const p = new URLSearchParams();
        if (type === 'ventes')    { p.append('date_debut', document.getElementById('ventes_debut').value);    p.append('date_fin', document.getElementById('ventes_fin').value); }
        if (type === 'commandes') { p.append('date_debut', document.getElementById('commandes_debut').value); p.append('date_fin', document.getElementById('commandes_fin').value); }
        if (type === 'retours')   { p.append('date_debut', document.getElementById('retours_debut').value);   p.append('date_fin', document.getElementById('retours_fin').value); }
        if (p.toString()) url += '?' + p.toString();

        if (btn) { btn.classList.add('loading'); setTimeout(() => btn.classList.remove('loading'), 3500); }

        const toast = document.getElementById('export-toast');
        document.getElementById('toast-msg').textContent = 'Génération du rapport ' + (LABELS[type] || type) + ' (' + format.toUpperCase() + ')…';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);

        window.open(url, '_blank');
    }

    function setRange(prefix, range) {
        const t = new Date();
        let s = new Date(t), e = new Date(t);
        if (range === 'week')       { s.setDate(t.getDate() - ((t.getDay() + 6) % 7)); }
        else if (range === 'month') { s = new Date(t.getFullYear(), t.getMonth(), 1); }
        else if (range === 'last_month') { s = new Date(t.getFullYear(), t.getMonth() - 1, 1); e = new Date(t.getFullYear(), t.getMonth(), 0); }
        else if (range === 'year')  { s = new Date(t.getFullYear(), 0, 1); }
        const fmt = d => d.toISOString().split('T')[0];
        document.getElementById(prefix + '_debut').value = fmt(s);
        document.getElementById(prefix + '_fin').value   = fmt(e);
    }
</script>
@endpush
