<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ticket {{ $vente->numero_vente }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 6mm 4mm; color: #000; background: white; }
  .center { text-align: center; }
  .bold { font-weight: bold; }
  .separator { border: none; border-top: 1px dashed #000; margin: 6px 0; }
  .line { display: flex; justify-content: space-between; margin: 3px 0; font-size: 11px; }
  .total-line { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; margin: 5px 0; }
  .header-title { font-size: 18px; font-weight: bold; letter-spacing: 2px; }
  .num { font-size: 13px; font-weight: bold; margin: 6px 0; }
  @media print {
    @page { size: 80mm auto; margin: 0; }
    body { padding: 4mm; }
  }
</style>
</head>
<body>
<div class="center">
  <div class="header-title">SNGP</div>
  <div style="font-size:10px;">Sys. National de Gestion Pharmaceutique</div>
  @if(isset($vente->pharmacie) && $vente->pharmacie)
  <div style="font-size:10px;font-weight:bold;margin-top:3px;">{{ $vente->pharmacie->nom }}</div>
  @endif
</div>

<hr class="separator">

<div class="center">
  <div class="num">{{ $vente->numero_vente }}</div>
  <div style="font-size:10px;">{{ $vente->created_at->format('d/m/Y H:i') }}</div>
</div>

@if($vente->nom_patient)
<div style="font-size:10px;margin-top:4px;">Patient : {{ $vente->nom_patient }}</div>
@endif

<hr class="separator">

@foreach($vente->lignes as $ligne)
<div class="bold" style="font-size:11px;">{{ $ligne->produit->dci }}
  @if($ligne->produit->nom_commercial)({{ $ligne->produit->nom_commercial }})@endif
</div>
<div class="line">
  <span>  {{ $ligne->quantite }} x {{ number_format($ligne->prix_unitaire, 0, ',', '.') }} GNF</span>
  <span class="bold">{{ number_format($ligne->montant_total, 0, ',', '.') }} GNF</span>
</div>
@endforeach

<hr class="separator">

<div class="total-line">
  <span>TOTAL</span>
  <span>{{ number_format($vente->montant_total, 0, ',', '.') }} GNF</span>
</div>

@if($vente->montant_paye)
<div class="line">
  <span>Payé</span>
  <span>{{ number_format($vente->montant_paye, 0, ',', '.') }} GNF</span>
</div>
@if($vente->montant_paye > $vente->montant_total)
<div class="line bold">
  <span>Monnaie</span>
  <span>{{ number_format($vente->montant_paye - $vente->montant_total, 0, ',', '.') }} GNF</span>
</div>
@endif
@endif

<hr class="separator">

<div class="center" style="font-size:10px;margin-top:4px;line-height:1.5;">
  Merci de votre visite !<br>
  Republique de Guinee<br>
  Ministere de la Sante
</div>

<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
