<?php

namespace App\Http\Controllers;

use App\Models\Maladie;
use App\Models\Vente;
use App\Models\LigneVente;
use App\Models\Pharmacie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EpidemiologieController extends Controller
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

        // Top maladies par nombre de ventes de médicaments associés
        $topMaladies = DB::table('maladies')
            ->join('produit_maladie', 'maladies.id', '=', 'produit_maladie.maladie_id')
            ->join('lignes_vente', 'produit_maladie.produit_id', '=', 'lignes_vente.produit_id')
            ->join('ventes', 'lignes_vente.vente_id', '=', 'ventes.id')
            ->where('ventes.statut', 'completee')
            ->whereBetween(DB::raw('DATE(ventes.created_at)'), [$dateDebut, $dateFin])
            ->whereNull('ventes.deleted_at')
            ->when(!$isNational, fn($q) => $q->where('ventes.pharmacie_id', $pharmacieId))
            ->select(
                'maladies.id',
                'maladies.nom',
                'maladies.code_cim10',
                'maladies.categorie',
                DB::raw('SUM(lignes_vente.quantite) as total_medicaments'),
                DB::raw('COUNT(DISTINCT ventes.id) as nb_ventes'),
                DB::raw('COUNT(DISTINCT lignes_vente.produit_id) as nb_produits')
            )
            ->groupBy('maladies.id', 'maladies.nom', 'maladies.code_cim10', 'maladies.categorie')
            ->orderBy('total_medicaments', 'desc')
            ->limit(15)
            ->get();

        // Évolution par semaine
        $evolutionHebdo = DB::table('maladies')
            ->join('produit_maladie', 'maladies.id', '=', 'produit_maladie.maladie_id')
            ->join('lignes_vente', 'produit_maladie.produit_id', '=', 'lignes_vente.produit_id')
            ->join('ventes', 'lignes_vente.vente_id', '=', 'ventes.id')
            ->where('ventes.statut', 'completee')
            ->whereBetween(DB::raw('DATE(ventes.created_at)'), [$dateDebut, $dateFin])
            ->whereNull('ventes.deleted_at')
            ->when(!$isNational, fn($q) => $q->where('ventes.pharmacie_id', $pharmacieId))
            ->select(
                DB::raw('YEARWEEK(ventes.created_at, 1) as semaine'),
                DB::raw('MIN(DATE(ventes.created_at)) as debut_semaine'),
                DB::raw('SUM(lignes_vente.quantite) as total')
            )
            ->groupBy('semaine')
            ->orderBy('semaine')
            ->get();

        // Top maladies par région (admin national uniquement)
        $maladiesParRegion = null;
        if ($isNational) {
            $maladiesParRegion = DB::table('maladies')
                ->join('produit_maladie', 'maladies.id', '=', 'produit_maladie.maladie_id')
                ->join('lignes_vente', 'produit_maladie.produit_id', '=', 'lignes_vente.produit_id')
                ->join('ventes', 'lignes_vente.vente_id', '=', 'ventes.id')
                ->join('pharmacies', 'ventes.pharmacie_id', '=', 'pharmacies.id')
                ->where('ventes.statut', 'completee')
                ->whereBetween(DB::raw('DATE(ventes.created_at)'), [$dateDebut, $dateFin])
                ->whereNull('ventes.deleted_at')
                ->select(
                    'pharmacies.region',
                    'maladies.nom as maladie',
                    DB::raw('SUM(lignes_vente.quantite) as total')
                )
                ->groupBy('pharmacies.region', 'maladies.nom')
                ->orderBy('pharmacies.region')
                ->orderBy('total', 'desc')
                ->get()
                ->groupBy('region');
        }

        // Maladies par catégorie
        $maladiesParCategorie = DB::table('maladies')
            ->join('produit_maladie', 'maladies.id', '=', 'produit_maladie.maladie_id')
            ->join('lignes_vente', 'produit_maladie.produit_id', '=', 'lignes_vente.produit_id')
            ->join('ventes', 'lignes_vente.vente_id', '=', 'ventes.id')
            ->where('ventes.statut', 'completee')
            ->whereBetween(DB::raw('DATE(ventes.created_at)'), [$dateDebut, $dateFin])
            ->whereNull('ventes.deleted_at')
            ->when(!$isNational, fn($q) => $q->where('ventes.pharmacie_id', $pharmacieId))
            ->select(
                DB::raw('COALESCE(maladies.categorie, "Non classé") as categorie'),
                DB::raw('SUM(lignes_vente.quantite) as total'),
                DB::raw('COUNT(DISTINCT maladies.id) as nb_maladies')
            )
            ->groupBy('categorie')
            ->orderBy('total', 'desc')
            ->get();

        // Stats globales
        $stats = [
            'total_maladies'   => Maladie::where('actif', true)->count(),
            'maladies_actives' => $topMaladies->count(),
            'total_doses'      => $topMaladies->sum('total_medicaments'),
            'total_ventes'     => $topMaladies->sum('nb_ventes'),
        ];

        return view('epidemiologie.index', compact(
            'topMaladies', 'evolutionHebdo', 'maladiesParRegion',
            'maladiesParCategorie', 'stats', 'dateDebut', 'dateFin', 'isNational'
        ));
    }
}