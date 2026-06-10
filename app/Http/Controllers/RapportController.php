<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\Lot;
use App\Models\Commande;
use App\Models\Retour;
use App\Models\Pharmacie;
use App\Models\JournalAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VentesExport;
use App\Exports\StocksExport;
use App\Exports\CommandesExport;
use App\Exports\RetoursExport;
use App\Exports\LotsExpiresExport;

class RapportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isNational = $user->hasRole('admin_national');
        $pharmacieId = $user->pharmacie_id;

        $stats = [
            'ventes_mois'        => Vente::whereMonth('created_at', now()->month)
                ->where('statut', 'completee')
                ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
                ->count(),
            'ca_mois'            => Vente::whereMonth('created_at', now()->month)
                ->where('statut', 'completee')
                ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
                ->sum('montant_total'),
            'lots_expires'       => Lot::where('date_expiration', '<', now())
                ->where('quantite_disponible', '>', 0)
                ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
                ->count(),
            'valeur_stock'       => DB::table('lots')
                ->join('produits', 'lots.produit_id', '=', 'produits.id')
                ->where('lots.statut', 'disponible')
                ->when(!$isNational, fn($q) => $q->where('lots.pharmacie_id', $pharmacieId))
                ->selectRaw('SUM(lots.quantite_disponible * produits.prix_vente_recommande) as total')
                ->value('total') ?? 0,
            'commandes_mois'     => Commande::whereMonth('created_at', now()->month)
                ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
                ->count(),
            'retours_mois'       => Retour::whereMonth('created_at', now()->month)
                ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
                ->count(),
        ];

        return view('rapports.index', compact('stats', 'isNational'));
    }

    // ===== VENTES PDF =====
    public function ventesPdf(Request $request)
    {
        $user = auth()->user();
        $isNational = $user->hasRole('admin_national');
        $pharmacieId = $user->pharmacie_id;
        $dateDebut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin   = $request->date_fin   ?? now()->format('Y-m-d');

        $ventes = Vente::with(['user', 'pharmacie'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin])
            ->where('statut', 'completee')
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('created_at', 'desc')->get();

        $totalCA = $ventes->sum('montant_total');
        $pdf = Pdf::loadView('rapports.ventes_pdf', compact('ventes', 'totalCA', 'dateDebut', 'dateFin', 'isNational'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('rapport-ventes-' . $dateDebut . '-' . $dateFin . '.pdf');
    }

    // ===== STOCKS PDF =====
    public function stocksPdf(Request $request)
    {
        $user = auth()->user();
        $isNational = $user->hasRole('admin_national');
        $pharmacieId = $user->pharmacie_id;

        $lots = Lot::with(['produit.categorie', 'pharmacie'])
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('date_expiration', 'asc')->get();

        $valeurTotale = $lots->sum(fn($l) => $l->quantite_disponible * $l->prix_achat_unitaire);
        $pdf = Pdf::loadView('rapports.stocks_pdf', compact('lots', 'valeurTotale', 'isNational'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('rapport-stocks-' . now()->format('Y-m-d') . '.pdf');
    }

    // ===== COMMANDES PDF =====
    public function commandesPdf(Request $request)
    {
        $user = auth()->user();
        $isNational = $user->hasRole('admin_national');
        $pharmacieId = $user->pharmacie_id;
        $dateDebut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin   = $request->date_fin   ?? now()->format('Y-m-d');

        $commandes = Commande::with(['fournisseur', 'pharmacie', 'user'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin])
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('created_at', 'desc')->get();

        $totalMontant = $commandes->sum('montant_total');
        $pdf = Pdf::loadView('rapports.commandes_pdf', compact('commandes', 'totalMontant', 'dateDebut', 'dateFin', 'isNational'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('rapport-commandes-' . $dateDebut . '-' . $dateFin . '.pdf');
    }

    // ===== RETOURS PDF =====
    public function retoursPdf(Request $request)
    {
        $user = auth()->user();
        $isNational = $user->hasRole('admin_national');
        $pharmacieId = $user->pharmacie_id;
        $dateDebut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin   = $request->date_fin   ?? now()->format('Y-m-d');

        $retours = Retour::with(['pharmacie', 'produit'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateDebut, $dateFin])
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('created_at', 'desc')->get();

        $totalRembourse = $retours->where('statut', 'valide')->sum('montant_rembourse');
        $pdf = Pdf::loadView('rapports.retours_pdf', compact('retours', 'totalRembourse', 'dateDebut', 'dateFin', 'isNational'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('rapport-retours-' . $dateDebut . '-' . $dateFin . '.pdf');
    }

    // ===== LOTS EXPIRES PDF =====
    public function lotsExpiresPdf(Request $request)
    {
        $user = auth()->user();
        $isNational = $user->hasRole('admin_national');
        $pharmacieId = $user->pharmacie_id;

        $lots = Lot::with(['produit', 'pharmacie'])
            ->where(function($q) {
                $q->where('date_expiration', '<', now())
                  ->orWhereBetween('date_expiration', [now(), now()->addDays(90)]);
            })
            ->where('quantite_disponible', '>', 0)
            ->when(!$isNational, fn($q) => $q->where('pharmacie_id', $pharmacieId))
            ->orderBy('date_expiration', 'asc')->get();

        $pdf = Pdf::loadView('rapports.lots_expires_pdf', compact('lots', 'isNational'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('rapport-lots-expires-' . now()->format('Y-m-d') . '.pdf');
    }

    // ===== EXCEL EXPORTS =====
    public function ventesExcel(Request $request)
    {
        $dateDebut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin   = $request->date_fin   ?? now()->format('Y-m-d');
        return Excel::download(new VentesExport($dateDebut, $dateFin, auth()->user()), 'rapport-ventes-' . $dateDebut . '-' . $dateFin . '.xlsx');
    }

    public function stocksExcel(Request $request)
    {
        return Excel::download(new StocksExport(auth()->user()), 'rapport-stocks-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function commandesExcel(Request $request)
    {
        $dateDebut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin   = $request->date_fin   ?? now()->format('Y-m-d');
        return Excel::download(new CommandesExport($dateDebut, $dateFin, auth()->user()), 'rapport-commandes-' . $dateDebut . '-' . $dateFin . '.xlsx');
    }

    public function retoursExcel(Request $request)
    {
        $dateDebut = $request->date_debut ?? now()->startOfMonth()->format('Y-m-d');
        $dateFin   = $request->date_fin   ?? now()->format('Y-m-d');
        return Excel::download(new RetoursExport($dateDebut, $dateFin, auth()->user()), 'rapport-retours-' . $dateDebut . '-' . $dateFin . '.xlsx');
    }

    public function lotsExpiresExcel(Request $request)
    {
        return Excel::download(new LotsExpiresExport(auth()->user()), 'rapport-lots-expires-' . now()->format('Y-m-d') . '.xlsx');
    }
}