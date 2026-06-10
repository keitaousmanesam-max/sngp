<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1F2937; background: white; padding: 20px; }
  .page { max-width: 500px; margin: 0 auto; }

  .header { text-align: center; border-bottom: 3px double #065F46; padding-bottom: 14px; margin-bottom: 14px; }
  .header .logo { font-size: 22px; font-weight: 700; color: #065F46; letter-spacing: 3px; }
  .header .sub { font-size: 10px; color: #6B7280; margin-top: 2px; }
  .header .pharmacie { font-size: 11px; font-weight: 600; color: #374151; margin-top: 6px; }
  .num-block { text-align: center; margin: 10px 0; }
  .num { font-family: monospace; font-size: 14px; font-weight: 700; color: #065F46; background: #F0FDF4; border: 1px solid #10B981; border-radius: 4px; padding: 4px 12px; display: inline-block; }
  .date { font-size: 10px; color: #9CA3AF; margin-top: 4px; }

  .meta { background: #F9FAFB; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; font-size: 11px; }
  .meta-row { display: flex; justify-content: space-between; padding: 2px 0; }
  .meta-label { color: #6B7280; }
  .meta-val { font-weight: 600; }

  .separator { border: none; border-top: 1px dashed #D1D5DB; margin: 10px 0; }

  table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  thead th { background: #065F46; color: white; padding: 6px 8px; font-size: 10px; text-transform: uppercase; }
  tbody td { padding: 7px 8px; border-bottom: 1px solid #F3F4F6; font-size: 11px; }
  tbody tr:last-child td { border-bottom: none; }
  .td-r { text-align: right; }
  .td-c { text-align: center; }

  .totals { border-top: 2px solid #065F46; padding-top: 8px; }
  .total-row { display: flex; justify-content: space-between; padding: 3px 4px; font-size: 12px; }
  .total-row.main { font-size: 15px; font-weight: 700; color: #065F46; padding-top: 6px; border-top: 1px solid #D1FAE5; margin-top: 4px; }
  .total-row .lbl { color: #374151; }
  .total-row .val { font-weight: 600; }

  .footer { text-align: center; margin-top: 16px; padding-top: 12px; border-top: 1px dashed #D1D5DB; font-size: 10px; color: #9CA3AF; line-height: 1.6; }
  .merci { font-size: 13px; font-weight: 700; color: #065F46; margin-bottom: 4px; }

  .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
  .badge-ok { background: #D1FAE5; color: #065F46; }
  .badge-red { background: #FEE2E2; color: #991B1B; }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="logo">🏥 SNGP</div>
    <div class="sub">Système National de Gestion Pharmaceutique</div>
    <div class="sub">République de Guinée — Ministère de la Santé</div>
    @if(isset($vente->pharmacie) && $vente->pharmacie)
    <div class="pharmacie">{{ $vente->pharmacie->nom }}</div>
    @endif
  </div>

  <div class="num-block">
    <div class="num">{{ $vente->numero_vente }}</div>
    <div class="date">{{ $vente->created_at->format('d/m/Y à H:i') }}</div>
  </div>

  <div class="meta">
    <div class="meta-row">
      <span class="meta-label">Patient</span>
      <span class="meta-val">{{ $vente->nom_patient ?? 'Anonyme' }}</span>
    </div>
    @if($vente->telephone_patient)
    <div class="meta-row">
      <span class="meta-label">Téléphone</span>
      <span class="meta-val">{{ $vente->telephone_patient }}</span>
    </div>
    @endif
    <div class="meta-row">
      <span class="meta-label">Vendeur</span>
      <span class="meta-val">{{ $vente->user->prenom ?? '' }} {{ $vente->user->nom ?? '' }}</span>
    </div>
    <div class="meta-row">
      <span class="meta-label">Statut</span>
      <span class="meta-val">
        @if($vente->statut == 'completee')
          <span class="badge badge-ok">Finalisée</span>
        @else
          <span class="badge badge-red">Annulée</span>
        @endif
      </span>
    </div>
    @if($vente->avec_ordonnance)
    <div class="meta-row">
      <span class="meta-label">Type</span>
      <span class="meta-val">Vente sur ordonnance</span>
    </div>
    @endif
  </div>

  <hr class="separator">

  <table>
    <thead>
      <tr>
        <th>Médicament</th>
        <th class="td-c">Qté</th>
        <th class="td-r">P.U (GNF)</th>
        <th class="td-r">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($vente->lignes as $ligne)
      <tr>
        <td>
          <strong>{{ $ligne->produit->dci }}</strong>
          @if($ligne->produit->nom_commercial)
          <br><span style="color:#6B7280;font-size:10px;">({{ $ligne->produit->nom_commercial }}) {{ $ligne->produit->dosage }}</span>
          @endif
        </td>
        <td class="td-c">{{ $ligne->quantite }}</td>
        <td class="td-r">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
        <td class="td-r" style="font-weight:600;color:#065F46;">{{ number_format($ligne->montant_total, 0, ',', ' ') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="totals">
    @if($vente->montant_paye && $vente->montant_paye != $vente->montant_total)
    <div class="total-row">
      <span class="lbl">Sous-total</span>
      <span class="val">{{ number_format($vente->montant_total, 0, ',', ' ') }} GNF</span>
    </div>
    <div class="total-row">
      <span class="lbl">Montant payé</span>
      <span class="val">{{ number_format($vente->montant_paye, 0, ',', ' ') }} GNF</span>
    </div>
    @if($vente->montant_paye > $vente->montant_total)
    <div class="total-row">
      <span class="lbl" style="color:#10B981;">Monnaie rendue</span>
      <span class="val" style="color:#10B981;">{{ number_format($vente->montant_paye - $vente->montant_total, 0, ',', ' ') }} GNF</span>
    </div>
    @endif
    @endif
    <div class="total-row main">
      <span class="lbl">TOTAL</span>
      <span class="val">{{ number_format($vente->montant_total, 0, ',', ' ') }} GNF</span>
    </div>
  </div>

  <div class="footer">
    <div class="merci">Merci de votre visite !</div>
    SNGP — Système National de Gestion Pharmaceutique<br>
    République de Guinée · Ministère de la Santé et de l'Hygiène Publique<br>
    Reçu généré le {{ now()->format('d/m/Y à H:i') }}
  </div>

</div>
</body>
</html>
