<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #DC2626; padding-bottom: 16px; }
        .header h1 { color: #DC2626; font-size: 20px; margin: 0 0 4px; }
        .header p { color: #6B7280; margin: 2px 0; font-size: 12px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 16px; }
        .meta-item { background: #FEF2F2; padding: 8px 12px; border-radius: 6px; font-size: 11px; }
        .meta-item strong { color: #DC2626; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #DC2626; color: white; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        tbody tr:nth-child(even) { background: #FEF2F2; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #E5E7EB; }
        .expire { color: #DC2626; font-weight: bold; }
        .proche { color: #D97706; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #9CA3AF; font-size: 10px; border-top: 1px solid #E5E7EB; padding-top: 10px; }
    </style>
</head>
<body>
<div class="header">
    <h1>⚠️ SNGP — Rapport Lots Expirés / Proches Expiration</h1>
    <p>Système National de Gestion Pharmaceutique — République de Guinée</p>
    <p>Généré le : {{ now()->format('d/m/Y à H:i') }} — Inclut les lots expirés et expirant dans les 90 jours</p>
</div>
<div class="meta">
    <div class="meta-item"><strong>Total lots :</strong> {{ $lots->count() }}</div>
    <div class="meta-item"><strong>Lots expirés :</strong> {{ $lots->filter(fn($l) => $l->date_expiration < now())->count() }}</div>
    <div class="meta-item"><strong>Expiration proche :</strong> {{ $lots->filter(fn($l) => $l->date_expiration >= now())->count() }}</div>
</div>
<table>
    <thead>
        <tr>
            <th>Produit</th>
            <th>N° Lot</th>
            @if($isNational)<th>Pharmacie</th>@endif
            <th>Qté Disponible</th>
            <th>Date Expiration</th>
            <th>Jours Restants</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lots as $lot)
        @php
            $jours = now()->diffInDays($lot->date_expiration, false);
            $classe = $jours < 0 ? 'expire' : 'proche';
            $label  = $jours < 0 ? 'EXPIRÉ (' . abs((int)$jours) . ' j)' : $jours . ' jours restants';
        @endphp
        <tr>
            <td><strong>{{ $lot->produit->dci ?? '—' }}</strong><br><small>{{ $lot->produit->dosage ?? '' }}</small></td>
            <td>{{ $lot->numero_lot }}</td>
            @if($isNational)<td>{{ $lot->pharmacie->nom ?? '—' }}</td>@endif
            <td>{{ number_format($lot->quantite_disponible) }}</td>
            <td class="{{ $classe }}">{{ $lot->date_expiration->format('d/m/Y') }}</td>
            <td class="{{ $classe }}">{{ $label }}</td>
            <td class="{{ $classe }}">{{ $jours < 0 ? 'EXPIRÉ' : 'Proche' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">SNGP — Système National de Gestion Pharmaceutique — Ministère de la Santé — République de Guinée</div>
</body>
</html>