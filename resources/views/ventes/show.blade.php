@extends('layouts.app')

@section('title', 'Reçu — ' . $vente->numero_vente)

@push('styles')
<style>
    .info-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; margin-bottom: 20px; }
    .section-title { font-size: 14px; font-weight: 700; color: #065F46; padding-bottom: 10px; border-bottom: 2px solid #D1FAE5; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid #F3F4F6; font-size: 13px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6B7280; font-weight: 500; }
    .info-value { color: #1F2937; font-weight: 600; text-align: right; }

    /* Ticket */
    .ticket { background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .ticket-header { text-align: center; padding-bottom: 14px; border-bottom: 2px dashed #E5E7EB; margin-bottom: 14px; }
    .ticket-line { display: flex; justify-content: space-between; align-items: flex-start; padding: 9px 0; border-bottom: 1px solid #F9FAFB; font-size: 13px; gap: 8px; }
    .ticket-total { display: flex; justify-content: space-between; padding: 12px 0; border-top: 2px solid #1F2937; margin-top: 8px; font-weight: 700; font-size: 16px; }

    /* Actions */
    .action-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
    .action-title { font-size: 12px; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
    .action-btn { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; width: 100%; margin-bottom: 8px; text-decoration: none; }
    .action-btn:last-child { margin-bottom: 0; }
    .action-btn:hover { transform: translateX(3px); text-decoration: none; }
    .action-btn .ico { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .btn-pdf     { background: #FEF2F2; color: #991B1B; }
    .btn-print   { background: #EFF6FF; color: #1E40AF; }
    .btn-email   { background: #F0FDF4; color: #065F46; }
    .btn-wa      { background: #F0FDF4; color: #166534; }
    .btn-annuler { background: #FEF2F2; color: #991B1B; border: 1px solid #FCA5A5; }

    /* Modal email */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: none; align-items: center; justify-content: center; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: white; border-radius: 16px; padding: 28px; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
    .modal-title { font-size: 16px; font-weight: 700; color: #1F2937; margin-bottom: 4px; }
    .modal-sub { font-size: 13px; color: #6B7280; margin-bottom: 20px; }

    @media print {
        .no-print { display: none !important; }
        .ticket { box-shadow: none; border: none; padding: 0; }
        body { padding: 0; }
    }
</style>
@endpush

@section('content')

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4 no-print">
    <a href="{{ route('ventes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="flex-grow-1">
        <h1 class="page-title mb-1"><i class="fas fa-receipt me-2"></i>{{ $vente->numero_vente }}</h1>
        <p class="text-muted mb-0 small">{{ $vente->created_at->format('d/m/Y à H:i') }}</p>
    </div>
    @if($vente->statut == 'completee')
    <span style="background:#D1FAE5;color:#065F46;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;">
        <i class="fas fa-check-circle me-1"></i>Finalisée
    </span>
    @else
    <span style="background:#FEE2E2;color:#991B1B;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;">
        <i class="fas fa-times-circle me-1"></i>Annulée
    </span>
    @endif
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 no-print">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('success_email'))
<div class="alert alert-success alert-dismissible fade show mb-4 no-print">
    <i class="fas fa-envelope-circle-check me-2"></i>{{ session('success_email') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error_email'))
<div class="alert alert-danger alert-dismissible fade show mb-4 no-print">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error_email') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    <!-- Colonne gauche — Infos + Actions -->
    <div class="col-12 col-lg-4 no-print">

        <!-- Infos vente -->
        <div class="info-card">
            <div class="section-title"><i class="fas fa-info-circle"></i>Informations</div>
            <div class="info-row">
                <span class="info-label">Numéro</span>
                <span class="info-value" style="font-family:monospace;">{{ $vente->numero_vente }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Patient</span>
                <span class="info-value">{{ $vente->nom_patient ?? 'Anonyme' }}</span>
            </div>
            @if($vente->telephone_patient)
            <div class="info-row">
                <span class="info-label">Téléphone</span>
                <span class="info-value">{{ $vente->telephone_patient }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Vendeur</span>
                <span class="info-value">{{ $vente->user->prenom ?? '—' }} {{ $vente->user->nom ?? '' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ordonnance</span>
                <span class="info-value">
                    @if($vente->avec_ordonnance)
                        <span style="background:#DBEAFE;color:#1E40AF;padding:2px 10px;border-radius:20px;font-size:11px;">Oui</span>
                    @else
                        <span class="text-muted fw-normal">Non</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Total</span>
                <span class="info-value" style="color:#065F46;font-size:17px;">
                    {{ number_format($vente->montant_total, 0, ',', ' ') }} GNF
                </span>
            </div>
            @if($vente->montant_paye && $vente->montant_paye != $vente->montant_total)
            <div class="info-row">
                <span class="info-label">Payé</span>
                <span class="info-value">{{ number_format($vente->montant_paye, 0, ',', ' ') }} GNF</span>
            </div>
            <div class="info-row">
                <span class="info-label">Monnaie rendue</span>
                <span class="info-value" style="color:#10B981;">
                    {{ number_format($vente->montant_paye - $vente->montant_total, 0, ',', ' ') }} GNF
                </span>
            </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="action-card">
            <div class="action-title">Exporter & Partager</div>

            <!-- PDF -->
            <a href="{{ route('ventes.recu.pdf', $vente) }}" class="action-btn btn-pdf" target="_blank">
                <div class="ico" style="background:#FEE2E2;"><i class="fas fa-file-pdf"></i></div>
                <div>
                    <div>Télécharger PDF</div>
                    <div style="font-size:11px;font-weight:400;opacity:.7;">Format A4 / thermique 80mm</div>
                </div>
            </a>

            <!-- Imprimer -->
            <button onclick="window.print()" class="action-btn btn-print" style="background:#EFF6FF;color:#1E40AF;">
                <div class="ico" style="background:#DBEAFE;"><i class="fas fa-print"></i></div>
                <div>
                    <div>Imprimer</div>
                    <div style="font-size:11px;font-weight:400;opacity:.7;">Impression directe</div>
                </div>
            </button>

            <!-- Email -->
            <button onclick="document.getElementById('modalEmail').classList.add('show')" class="action-btn btn-email">
                <div class="ico" style="background:#D1FAE5;"><i class="fas fa-envelope"></i></div>
                <div>
                    <div>Envoyer par Email</div>
                    <div style="font-size:11px;font-weight:400;opacity:.7;">Reçu envoyé au client</div>
                </div>
            </button>

            <!-- WhatsApp -->
            <a id="btnWhatsapp" href="#" onclick="partagerWhatsapp(event)" class="action-btn btn-wa" style="background:#DCFCE7;color:#15803D;">
                <div class="ico" style="background:#BBF7D0;"><i class="fab fa-whatsapp"></i></div>
                <div>
                    <div>Partager WhatsApp</div>
                    <div style="font-size:11px;font-weight:400;opacity:.7;">Envoyer le résumé</div>
                </div>
            </a>

            @if($vente->statut == 'completee')
            <div style="border-top:1px solid #F3F4F6;margin:12px 0;"></div>
            <form method="POST" action="{{ route('ventes.annuler', $vente) }}">
                @csrf @method('PATCH')
                <button type="submit" class="action-btn btn-annuler"
                    onclick="return confirm('Annuler cette vente et remettre les médicaments en stock ?')"
                    style="background:#FEF2F2;color:#991B1B;border:1px solid #FCA5A5;">
                    <div class="ico" style="background:#FEE2E2;"><i class="fas fa-times-circle"></i></div>
                    <div>Annuler la vente</div>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Colonne droite — Ticket -->
    <div class="col-12 col-lg-8">
        <div class="ticket">
            <div class="ticket-header">
                <div style="font-size:22px;font-weight:700;color:#1E3A8A;">
                    <i class="fas fa-hospital-user me-2"></i>SNGP
                </div>
                <div style="font-size:12px;color:#6B7280;margin-top:3px;">Système National de Gestion Pharmaceutique</div>
                @if(isset($vente->pharmacie) && $vente->pharmacie)
                <div style="font-size:12px;font-weight:600;color:#374151;margin-top:3px;">{{ $vente->pharmacie->nom }}</div>
                @endif
                <div style="font-size:11px;color:#9CA3AF;margin-top:3px;">{{ $vente->created_at->format('d/m/Y à H:i') }}</div>
                <div style="font-family:monospace;font-size:14px;font-weight:700;margin-top:8px;color:#374151;background:#F9FAFB;display:inline-block;padding:4px 16px;border-radius:6px;">
                    {{ $vente->numero_vente }}
                </div>
            </div>

            <!-- Lignes -->
            <div style="margin-bottom:12px;">
                @foreach($vente->lignes as $ligne)
                <div class="ticket-line">
                    <div style="flex:1;">
                        <div class="fw-semibold" style="color:#1F2937;">{{ $ligne->produit->dci }}</div>
                        @if($ligne->produit->nom_commercial)
                        <small class="text-muted">({{ $ligne->produit->nom_commercial }}) {{ $ligne->produit->dosage }}</small>
                        @endif
                        <div style="font-size:12px;color:#6B7280;margin-top:2px;">
                            {{ $ligne->quantite }} × {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} GNF
                        </div>
                    </div>
                    <div class="fw-semibold" style="color:#065F46;white-space:nowrap;">
                        {{ number_format($ligne->montant_total, 0, ',', ' ') }} GNF
                    </div>
                </div>
                @endforeach
            </div>

            <div class="ticket-total">
                <span>TOTAL</span>
                <span style="color:#065F46;">{{ number_format($vente->montant_total, 0, ',', ' ') }} GNF</span>
            </div>

            @if($vente->montant_paye)
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;color:#6B7280;">
                <span>Payé</span>
                <span>{{ number_format($vente->montant_paye, 0, ',', ' ') }} GNF</span>
            </div>
            @if($vente->montant_paye > $vente->montant_total)
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;color:#10B981;font-weight:600;">
                <span>Monnaie rendue</span>
                <span>{{ number_format($vente->montant_paye - $vente->montant_total, 0, ',', ' ') }} GNF</span>
            </div>
            @endif
            @endif

            @if($vente->nom_patient)
            <div style="background:#F9FAFB;border-radius:8px;padding:10px 14px;margin-top:14px;font-size:12px;color:#6B7280;">
                <i class="fas fa-user me-1"></i>
                Patient : <strong>{{ $vente->nom_patient }}</strong>
                @if($vente->telephone_patient) · {{ $vente->telephone_patient }}@endif
            </div>
            @endif

            <div style="text-align:center;margin-top:18px;padding-top:14px;border-top:1px dashed #E5E7EB;">
                <p style="font-size:11px;color:#9CA3AF;margin:0;line-height:1.6;">
                    Merci de votre visite — République de Guinée<br>
                    Ministère de la Santé et de l'Hygiène Publique<br>
                    <span style="font-family:monospace;">{{ $vente->numero_vente }}</span>
                </p>
            </div>
        </div>
    </div>

</div>

<!-- ===== Modal Email ===== -->
<div class="modal-overlay no-print" id="modalEmail" onclick="if(event.target===this) this.classList.remove('show')">
    <div class="modal-box">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="modal-title"><i class="fas fa-envelope me-2 text-success"></i>Envoyer le reçu par email</div>
                <div class="modal-sub">Le reçu de <strong>{{ $vente->numero_vente }}</strong> sera envoyé à l'adresse indiquée.</div>
            </div>
            <button onclick="document.getElementById('modalEmail').classList.remove('show')"
                style="background:none;border:none;font-size:20px;color:#9CA3AF;cursor:pointer;line-height:1;">×</button>
        </div>

        <form method="POST" action="{{ route('ventes.envoyer.email', $vente) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:13px;">Adresse email du destinataire</label>
                <input type="email" name="email" class="form-control"
                    placeholder="exemple@email.com"
                    value="{{ old('email', $vente->nom_patient ? '' : '') }}"
                    required autofocus>
                <div class="form-text">Peut être l'email du patient ou de la pharmacie.</div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success flex-grow-1 fw-semibold">
                    <i class="fas fa-paper-plane me-2"></i>Envoyer le reçu
                </button>
                <button type="button" onclick="document.getElementById('modalEmail').classList.remove('show')"
                    class="btn btn-outline-secondary">Annuler</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function partagerWhatsapp(e) {
    e.preventDefault();
    const lignes = @json($vente->lignes->map(fn($l) => ['dci' => $l->produit->dci, 'qte' => $l->quantite, 'prix' => $l->prix_unitaire, 'total' => $l->montant_total]));
    let detail = '';
    lignes.forEach(l => {
        detail += '\n• ' + l.dci + ' × ' + l.qte + ' = ' + new Intl.NumberFormat('fr-FR').format(l.total) + ' GNF';
    });
    const msg =
        '🧾 *Reçu SNGP*\n' +
        '━━━━━━━━━━━━━━\n' +
        '📋 N° : *{{ $vente->numero_vente }}*\n' +
        '📅 Date : {{ $vente->created_at->format("d/m/Y H:i") }}\n' +
        '🏥 {{ $vente->pharmacie->nom ?? "SNGP" }}\n' +
        '━━━━━━━━━━━━━━' +
        detail + '\n' +
        '━━━━━━━━━━━━━━\n' +
        '💰 *TOTAL : {{ number_format($vente->montant_total, 0, ",", " ") }} GNF*\n' +
        @if($vente->montant_paye > $vente->montant_total)
        '💵 Monnaie rendue : {{ number_format($vente->montant_paye - $vente->montant_total, 0, ",", " ") }} GNF\n' +
        @endif
        '✅ Merci de votre visite !';

    @if($vente->telephone_patient)
    const phone = '{{ preg_replace("/[^0-9]/", "", $vente->telephone_patient) }}';
    window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(msg), '_blank');
    @else
    window.open('https://wa.me/?text=' + encodeURIComponent(msg), '_blank');
    @endif
}
</script>
@endpush
