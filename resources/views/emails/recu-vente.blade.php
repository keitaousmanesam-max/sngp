<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
  .wrap { max-width: 520px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.10); }
  .header { background: linear-gradient(135deg,#065F46,#10B981); color: white; padding: 28px 32px; text-align: center; }
  .header h1 { margin: 0; font-size: 26px; letter-spacing: 2px; }
  .header p { margin: 6px 0 0; font-size: 13px; opacity: .85; }
  .body { padding: 28px 32px; }
  .greeting { font-size: 15px; color: #374151; margin-bottom: 20px; }
  .num { font-family: monospace; font-size: 18px; font-weight: 700; color: #065F46; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 10px 16px; display: inline-block; margin-bottom: 20px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  th { background: #F9FAFB; color: #374151; font-size: 12px; text-transform: uppercase; padding: 8px 10px; text-align: left; border-bottom: 2px solid #E5E7EB; }
  td { padding: 10px; border-bottom: 1px solid #F3F4F6; font-size: 13px; color: #1F2937; }
  .td-right { text-align: right; font-weight: 600; color: #065F46; }
  .total-row td { font-weight: 700; font-size: 15px; background: #F0FDF4; color: #065F46; border-top: 2px solid #10B981; }
  .info-box { background: #F9FAFB; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; font-size: 13px; color: #374151; }
  .info-box .row { display: flex; justify-content: space-between; padding: 4px 0; }
  .info-box .label { color: #6B7280; }
  .footer { background: #F9FAFB; padding: 18px 32px; text-align: center; font-size: 12px; color: #9CA3AF; border-top: 1px solid #E5E7EB; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>🏥 SNGP</h1>
    <p>Système National de Gestion Pharmaceutique</p>
    <p>République de Guinée — Ministère de la Santé</p>
  </div>
  <div class="body">
    <p class="greeting">
      Bonjour {{ $vente->nom_patient ?? 'cher client' }},<br>
      Merci pour votre achat. Voici votre reçu de vente.
    </p>

    <div class="num">{{ $vente->numero_vente }}</div>

    <div class="info-box">
      <div class="row"><span class="label">Date</span><span>{{ $vente->created_at->format('d/m/Y à H:i') }}</span></div>
      <div class="row"><span class="label">Pharmacie</span><span>{{ $vente->pharmacie->nom ?? 'SNGP' }}</span></div>
      <div class="row"><span class="label">Vendeur</span><span>{{ $vente->user->prenom ?? '' }} {{ $vente->user->nom ?? '' }}</span></div>
      @if($vente->avec_ordonnance)
      <div class="row"><span class="label">Type</span><span>Vente sur ordonnance</span></div>
      @endif
    </div>

    <table>
      <thead>
        <tr>
          <th>Médicament</th>
          <th style="text-align:right;">Qté</th>
          <th style="text-align:right;">P.U (GNF)</th>
          <th style="text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($vente->lignes as $ligne)
        <tr>
          <td>
            {{ $ligne->produit->dci }}
            @if($ligne->produit->nom_commercial)
            <br><small style="color:#6B7280;">({{ $ligne->produit->nom_commercial }})</small>
            @endif
          </td>
          <td style="text-align:right;">{{ $ligne->quantite }}</td>
          <td style="text-align:right;">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
          <td class="td-right">{{ number_format($ligne->montant_total, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="3">TOTAL</td>
          <td class="td-right">{{ number_format($vente->montant_total, 0, ',', ' ') }} GNF</td>
        </tr>
        @if($vente->montant_paye && $vente->montant_paye > $vente->montant_total)
        <tr>
          <td colspan="3" style="color:#6B7280;font-size:12px;">Monnaie rendue</td>
          <td style="text-align:right;color:#10B981;font-weight:600;font-size:12px;">{{ number_format($vente->montant_paye - $vente->montant_total, 0, ',', ' ') }} GNF</td>
        </tr>
        @endif
      </tbody>
    </table>

    <p style="font-size:13px;color:#6B7280;margin-top:16px;">
      Ce reçu est généré automatiquement par le système SNGP. Conservez-le comme preuve d'achat.
    </p>
  </div>
  <div class="footer">
    SNGP — Système National de Gestion Pharmaceutique<br>
    République de Guinée · Ministère de la Santé et de l'Hygiène Publique
  </div>
</div>
</body>
</html>
