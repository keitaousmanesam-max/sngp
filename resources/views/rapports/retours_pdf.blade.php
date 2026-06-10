<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E3A8A; padding-bottom: 16px; }
        .header h1 { color: #1E3A8A; font-size: 20px; margin: 0 0 4px; }
        .header p { color: #6B7280; margin: 2px 0; font-size: 12px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 16px; }
        .meta-item { background: #F3F4F6; padding: 8px 12px; border-radius: 6px; font-size: 11px; }
        .meta-item strong { color: #1E3A8A; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead { background: #F59E0B; color: white; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #E5E7EB; }
        .total-row { background: #F59E0B !important; color: white; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #9CA3AF; font-size: 10px; border-top: 1px solid #E5E7EB; padding-top: 10px; }
        .valide { color: #065F46; font-weight: bold; }
        .rejete { color: #DC2626; font-weight: bold; }
        .en_attente { color: #D97706; }
    </style>
</head>
<body>
<div class="header">
    <h1>🏥 SNGP — Rapport des Retours</h1>
    <p>Système National de Gestion Pharmaceutique — République de Guinée</p>
    <p>Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
</div>
<div class="meta">
    <div class="meta-item"><strong>Total retours :</strong> {{ $retours->count() }}</div>
    <div class="meta-item"><strong>Montant remboursé :</strong> {{ number_format($totalRembourse, 0, ',', ' ') }} GNF</div>
    <div class="meta-item"><strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }}</div>
</div>
<table>
    <thead>
        <tr>
            <th>N° Retour</th>
            <th>Date</th>
            @if($isNational)<th>Pharmacie</th>@endif
            <th>Produit</th>
            <th>Quantité</th>
            <th>Montant Remboursé</th>
            <th>Motif</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($retours as $retour)
        <tr>
            <td><strong>{{ $retour->numero_retour }}</strong></td>
            <td>{{ $retour->created_at->format('d/m/Y') }}</td>
            @if($isNational)<td>{{ $retour->pharmacie->nom ?? '—' }}</td>@endif
            <td>{{ $retour->produit->dci ?? '—' }}</td>
            <td>{{ $retour->quantite }}</td>
            <td><strong>{{ number_format($retour->montant_rembourse, 0, ',', ' ') }} GNF</strong></td>
            <td>{{ ucfirst(str_replace('_', ' ', $retour->motif)) }}</td>
            <td class="{{ $retour->statut }}">{{ ucfirst($retour->statut) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="{{ $isNational ? 5 : 4 }}">TOTAL REMBOURSÉ</td>
            <td>{{ number_format($totalRembourse, 0, ',', ' ') }} GNF</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
<div class="footer">SNGP — Système National de Gestion Pharmaceutique — Ministère de la Santé — République de Guinée</div>
</body>
</html>