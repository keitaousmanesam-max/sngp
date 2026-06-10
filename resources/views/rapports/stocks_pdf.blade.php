<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E3A8A; padding-bottom: 16px; }
        .header h1 { color: #1E3A8A; font-size: 20px; margin: 0 0 4px; }
        .header p { color: #6B7280; margin: 0; font-size: 12px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 16px; }
        .meta-item { background: #F3F4F6; padding: 8px 12px; border-radius: 6px; font-size: 11px; }
        .meta-item strong { color: #1E3A8A; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #1E3A8A; color: white; }
        thead th { padding: 7px 8px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        tbody td { padding: 6px 8px; border-bottom: 1px solid #E5E7EB; }
        .expire { color: #DC2626; font-weight: bold; }
        .proche { color: #D97706; font-weight: bold; }
        .ok { color: #059669; }
        .total-row { background: #1E3A8A !important; color: white; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #9CA3AF; font-size: 10px; border-top: 1px solid #E5E7EB; padding-top: 10px; }
    </style>
</head>
<body>

<div class="header">
    <h1>🏥 SNGP — Rapport des Stocks</h1>
    <p>Système National de Gestion Pharmaceutique — République de Guinée</p>
    <p>Date : {{ now()->format('d/m/Y à H:i') }}</p>
</div>

<div class="meta">
    <div class="meta-item">
        <strong>Total lots :</strong> {{ $lots->count() }}
    </div>
    <div class="meta-item">
        <strong>Valeur totale :</strong> {{ number_format($valeurTotale, 0, ',', ' ') }} GNF
    </div>
    <div class="meta-item">
        <strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Produit</th>
            <th>N° Lot</th>
            @if($isNational)<th>Pharmacie</th>@endif
            <th>Qté Dispo</th>
            <th>Prix Achat</th>
            <th>Valeur (GNF)</th>
            <th>Expiration</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lots as $lot)
        @php
            $jours = now()->diffInDays($lot->date_expiration, false);
            $classe = $jours < 0 ? 'expire' : ($jours <= 30 ? 'proche' : 'ok');
        @endphp
        <tr>
            <td><strong>{{ $lot->produit->dci ?? '—' }}</strong><br><small>{{ $lot->produit->dosage ?? '' }}</small></td>
            <td>{{ $lot->numero_lot }}</td>
            @if($isNational)<td>{{ $lot->pharmacie->nom ?? '—' }}</td>@endif
            <td>{{ number_format($lot->quantite_disponible) }}</td>
            <td>{{ number_format($lot->prix_achat_unitaire, 0, ',', ' ') }}</td>
            <td>{{ number_format($lot->quantite_disponible * $lot->prix_achat_unitaire, 0, ',', ' ') }}</td>
            <td class="{{ $classe }}">{{ $lot->date_expiration->format('d/m/Y') }}</td>
            <td>{{ ucfirst($lot->statut) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="{{ $isNational ? 5 : 4 }}">TOTAL VALEUR STOCK</td>
            <td>{{ number_format($valeurTotale, 0, ',', ' ') }} GNF</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

<div class="footer">
    SNGP — Système National de Gestion Pharmaceutique — Ministère de la Santé — République de Guinée
</div>

</body>
</html>