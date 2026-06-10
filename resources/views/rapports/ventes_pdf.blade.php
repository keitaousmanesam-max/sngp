<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E3A8A; padding-bottom: 16px; }
        .header h1 { color: #1E3A8A; font-size: 20px; margin: 0 0 4px; }
        .header p { color: #6B7280; margin: 0; font-size: 12px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 11px; }
        .meta-item { background: #F3F4F6; padding: 8px 12px; border-radius: 6px; }
        .meta-item strong { color: #1E3A8A; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #1E3A8A; color: white; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #E5E7EB; }
        .total-row { background: #1E3A8A !important; color: white; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #9CA3AF; font-size: 10px; border-top: 1px solid #E5E7EB; padding-top: 10px; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        .badge-success { background: #D1FAE5; color: #065F46; }
    </style>
</head>
<body>

<div class="header">
    <h1>🏥 SNGP — Rapport des Ventes</h1>
    <p>Système National de Gestion Pharmaceutique — République de Guinée</p>
    <p>Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
</div>

<div class="meta">
    <div class="meta-item">
        <strong>Total ventes :</strong> {{ $ventes->count() }}
    </div>
    <div class="meta-item">
        <strong>CA Total :</strong> {{ number_format($totalCA, 0, ',', ' ') }} GNF
    </div>
    <div class="meta-item">
        <strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>N° Vente</th>
            <th>Date</th>
            @if($isNational)<th>Pharmacie</th>@endif
            <th>Montant Total</th>
            <th>Montant Payé</th>
            <th>Type</th>
            <th>Vendeur</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ventes as $vente)
        <tr>
            <td><strong>{{ $vente->numero_vente }}</strong></td>
            <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
            @if($isNational)<td>{{ $vente->pharmacie->nom ?? '—' }}</td>@endif
            <td><strong>{{ number_format($vente->montant_total, 0, ',', ' ') }} GNF</strong></td>
            <td>{{ number_format($vente->montant_paye, 0, ',', ' ') }} GNF</td>
            <td>{{ $vente->type_vente ?? '—' }}</td>
            <td>{{ ($vente->user->prenom ?? '') . ' ' . ($vente->user->nom ?? '') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="{{ $isNational ? 3 : 2 }}">TOTAL</td>
            <td>{{ number_format($totalCA, 0, ',', ' ') }} GNF</td>
            <td colspan="3"></td>
        </tr>
    </tbody>
</table>

<div class="footer">
    SNGP — Système National de Gestion Pharmaceutique — Ministère de la Santé — République de Guinée
</div>

</body>
</html>