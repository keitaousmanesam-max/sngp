<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\Lot;
use App\Models\Commande;
use App\Models\Retour;
use App\Models\Produit;
use App\Models\Pharmacie;
use App\Models\LigneVente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isNational = $user->hasRole('admin_national');
        $pharmacieId = $user->pharmacie_id;

        $dateDebut = $request->filled('date_debut')
            ? $request->date_debut
            : now()->subDays(30)->format('Y-m-d');
        $dateFin = $request->filled('date_fin')
            ? $request->date_fin
            : now()->format('Y-m-d');

        // ===== VENTES =====
        $ventesQuery = Vente::whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin])
            ->where('statut', 'completee');
        if (!$isNational) $ventesQuery->where('pharmacie_id', $pharmacieId);

        $statsVentes = [
            'total'           => (clone $ventesQuery)->count(),
            'ca_total'        => (clone $ventesQuery)->sum('montant_total'),
            'ca_moyen'        => (clone $ventesQuery)->avg('montant_total') ?? 0,
            'avec_ordonnance' => (clone $ventesQuery)->where('avec_ordonnance', true)->count(),
        ];

        // Ventes par jour
        $ventesParJour = (clone $ventesQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as nb'), DB::raw('SUM(montant_total) as ca'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ===== TOP PRODUITS =====
        $topProduits = LigneVente::select(
                'produit_id',
                DB::raw('SUM(quantite) as total_vendu'),
                DB::raw('SUM(montant_total) as ca_total')
            )
            ->whereHas('vente', function($q) use ($dateDebut, $dateFin, $isNational, $pharmacieId) {
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin])
                  ->where('statut', 'completee');
                if (!$isNational) $q->where('pharmacie_id', $pharmacieId);
            })
            ->with('produit')
            ->groupBy('produit_id')
            ->orderBy('total_vendu', 'desc')
            ->limit(10)
            ->get();

        // ===== STOCKS =====
        $stocksQuery = Lot::query();
        if (!$isNational) $stocksQuery->where('pharmacie_id', $pharmacieId);

        $statsStocks = [
            'total_lots'        => (clone $stocksQuery)->count(),
            'disponibles'       => (clone $stocksQuery)->where('statut', 'disponible')->count(),
            'expires'           => (clone $stocksQuery)->where('date_expiration', '<', now())->where('quantite_disponible', '>', 0)->count(),
            'expiration_proche' => (clone $stocksQuery)->whereBetween('date_expiration', [now(), now()->addDays(30)])->where('quantite_disponible', '>', 0)->count(),
            'valeur_stock'      => DB::table('lots')
                ->join('produits', 'lots.produit_id', '=', 'produits.id')
                ->where('lots.statut', 'disponible')
                ->when(!$isNational, fn($q) => $q->where('lots.pharmacie_id', $pharmacieId))
                ->selectRaw('SUM(lots.quantite_disponible * produits.prix_vente_recommande) as total')
                ->value('total') ?? 0,
        ];

        // ===== COMMANDES =====
        $commandesQuery = Commande::whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin]);
        if (!$isNational) $commandesQuery->where('pharmacie_id', $pharmacieId);

        $statsCommandes = [
            'total'      => (clone $commandesQuery)->count(),
            'finalisees' => (clone $commandesQuery)->where('statut', 'finalisee')->count(),
            'en_cours'   => (clone $commandesQuery)->whereIn('statut', ['envoyee', 'en_traitement', 'expediee'])->count(),
            'montant'    => (clone $commandesQuery)->where('statut', 'finalisee')->sum('montant_total'),
        ];

        // ===== RETOURS =====
        $retoursQuery = Retour::whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin]);
        if (!$isNational) $retoursQuery->where('pharmacie_id', $pharmacieId);

        $statsRetours = [
            'total'   => (clone $retoursQuery)->count(),
            'valides' => (clone $retoursQuery)->where('statut', 'valide')->count(),
            'montant' => (clone $retoursQuery)->where('statut', 'valide')->sum('montant_rembourse'),
        ];

        // ===== PHARMACIES (admin national uniquement) =====
        $statsPharmacies = null;
        $topPharmacies = null;
        if ($isNational) {
            $statsPharmacies = [
                'total'      => Pharmacie::count(),
                'actives'    => Pharmacie::where('statut', 'active')->count(),
                'suspendues' => Pharmacie::where('statut', 'suspendue')->count(),
            ];

            $topPharmacies = Vente::select(
                    'pharmacie_id',
                    DB::raw('COUNT(*) as nb_ventes'),
                    DB::raw('SUM(montant_total) as ca_total')
                )
                ->whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin])
                ->where('statut', 'completee')
                ->with('pharmacie')
                ->groupBy('pharmacie_id')
                ->orderBy('ca_total', 'desc')
                ->limit(5)
                ->get();
        }

        return view('statistiques.index', compact(
            'statsVentes', 'ventesParJour',
            'topProduits', 'statsStocks',
            'statsCommandes', 'statsRetours',
            'statsPharmacies', 'topPharmacies',
            'dateDebut', 'dateFin', 'isNational'
        ));
    }
}