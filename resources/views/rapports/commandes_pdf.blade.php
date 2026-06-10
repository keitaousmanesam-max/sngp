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
        thead { background: #1E3A8A; color: white; }
        thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        tbody tr:nth-child(even) { background: #F9FAFB; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #E5E7EB; }
        .total-row { background: #1E3A8A !important; color: white; font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; color: #9CA3AF; font-size: 10px; border-top: 1px solid #E5E7EB; padding-top: 10px; }
        .badge-finalisee { color: #065F46; font-weight: bold; }
        .badge-annulee { color: #DC2626; font-weight: bold; }
        .badge-en_attente { color: #D97706; }
    </style>
</head>
<body>
<div class="header">
    <h1>🏥 SNGP — Rapport des Commandes</h1>
    <p>Système National de Gestion Pharmaceutique — République de Guinée</p>
    <p>Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</p>
</div>
<div class="meta">
    <div class="meta-item"><strong>Total commandes :</strong> {{ $commandes->count() }}</div>
    <div class="meta-item"><strong>Montant total :</strong> {{ number_format($totalMontant, 0, ',', ' ') }} GNF</div>
    <div class="meta-item"><strong>Généré le :</strong> {{ now()->format('d/m/Y à H:i') }}</div>
</div>
<table>
    <thead>
        <tr>
            <th>N° Commande</th>
            <th>Date</th>
            @if($isNational)<th>Pharmacie</th>@endif
            <th>Fournisseur</th>
            <th>Montant (GNF)</th>
            <th>Livraison Prévue</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($commandes as $commande)
        <tr>
            <td><strong>{{ $commande->numero_commande }}</strong></td>
            <td>{{ $commande->created_at->format('d/m/Y') }}</td>
            @if($isNational)<td>{{ $commande->pharmacie->nom ?? '—' }}</td>@endif
            <td>{{ $commande->fournisseur->nom ?? '—' }}</td>
            <td><strong>{{ number_format($commande->montant_total, 0, ',', ' ') }}</strong></td>
            <td>{{ $commande->date_livraison_prevue ? \Carbon\Carbon::parse($commande->date_livraison_prevue)->format('d/m/Y') : '—' }}</td>
            <td class="badge-{{ $commande->statut }}">{{ ucfirst($commande->statut) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="{{ $isNational ? 4 : 3 }}">TOTAL</td>
            <td>{{ number_format($totalMontant, 0, ',', ' ') }} GNF</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
<div class="footer">SNGP — Système National de Gestion Pharmaceutique — Ministère de la Santé — République de Guinée</div>
</body>
</html>