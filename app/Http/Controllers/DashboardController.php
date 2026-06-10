<?php

namespace App\Http\Controllers;

use App\Models\Pharmacie;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\Lot;
use App\Models\AuditLog;
use App\Models\Commande;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->hasRole('fournisseur')) {
            return redirect()->route('fournisseur.espace.dashboard');
        }

        try {
            $user          = Auth::user();
            $stats         = $this->getStatistics($user);
            $activites     = $this->getRecentActivities($user);
            $ventesParJour = $this->getVentesParJour($user);
            $alertes       = $this->getAlertes($user);

            return view('dashboard.index', compact('stats', 'activites', 'ventesParJour', 'alertes'));

        } catch (\Exception $e) {
            Log::error('Erreur Dashboard: ' . $e->getMessage());
            return view('dashboard.index', [
                'stats'         => $this->getDefaultStats(),
                'activites'     => collect(),
                'ventesParJour' => ['labels' => [], 'data' => []],
                'alertes'       => collect()
            ])->with('error', 'Erreur lors du chargement du tableau de bord.');
        }
    }

    private function getStatistics($user)
    {
        if ($user->hasRole('admin_national')) {
            return $this->getStatsAdminNational();
        }
        if ($user->hasRole('admin_pharmacie')) {
            return $this->getStatsAdminPharmacie($user->pharmacie_id);
        }
        if ($user->hasAnyRole(['pharmacien', 'caissier', 'gestionnaire_stock', 'assistant_pharmacien'])) {
            return $this->getStatsEmploye($user->pharmacie_id);
        }
        return $this->getDefaultStats();
    }

    private function getStatsAdminNational()
    {
        return [
            'pharmacies_actives'     => Pharmacie::where('statut', 'active')->count(),
            'total_pharmacies'       => Pharmacie::count(),
            'pharmacies_suspendues'  => Pharmacie::where('statut', 'suspendue')->count(),
            'produits_references'    => Produit::count(),
            'produits_actifs'        => Produit::where('statut', 'actif')->count(),
            'lots_expires'           => Lot::where('date_expiration', '<', now())->count(),
            'lots_expiration_proche' => Lot::whereBetween('date_expiration', [now(), now()->addDays(30)])->count(),
            'total_ventes'           => Vente::count(),
            'ventes_aujourd_hui'     => Vente::whereDate('created_at', today())->count(),
            'ventes_mois'            => Vente::whereMonth('created_at', now()->month)
                                             ->whereYear('created_at', now()->year)->count(),
            'ca_aujourd_hui'         => Vente::whereDate('created_at', today())->sum('montant_total') ?? 0,
            'ca_mois'                => Vente::whereMonth('created_at', now()->month)
                                             ->whereYear('created_at', now()->year)->sum('montant_total') ?? 0,
            'ca_annee'               => Vente::whereYear('created_at', now()->year)->sum('montant_total') ?? 0,
            'commandes_en_cours'     => Commande::whereIn('statut', ['envoyee', 'en_traitement', 'expediee'])->count(),
            'commandes_total'        => Commande::count(),
        ];
    }

    private function getStatsAdminPharmacie($pharmacie_id)
    {
        if (!$pharmacie_id) return $this->getDefaultStats();

        return [
            'pharmacie'              => Pharmacie::find($pharmacie_id),
            'produits_references'    => Produit::count(),
            'produits_en_stock'      => Lot::where('pharmacie_id', $pharmacie_id)
                                          ->where('quantite_disponible', '>', 0)
                                          ->where('date_expiration', '>', now())
                                          ->distinct('produit_id')->count('produit_id'),
            'lots_disponibles'       => Lot::where('pharmacie_id', $pharmacie_id)
                                          ->where('quantite_disponible', '>', 0)
                                          ->where('date_expiration', '>', now())->count(),
            'lots_expires'           => Lot::where('pharmacie_id', $pharmacie_id)
                                          ->where('date_expiration', '<', now())->count(),
            'lots_expiration_proche' => Lot::where('pharmacie_id', $pharmacie_id)
                                          ->whereBetween('date_expiration', [now(), now()->addDays(30)])->count(),
            'ventes_aujourd_hui'     => Vente::where('pharmacie_id', $pharmacie_id)
                                            ->whereDate('created_at', today())->count(),
            'ventes_mois'            => Vente::where('pharmacie_id', $pharmacie_id)
                                            ->whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)->count(),
            'ca_aujourd_hui'         => Vente::where('pharmacie_id', $pharmacie_id)
                                            ->whereDate('created_at', today())->sum('montant_total') ?? 0,
            'ca_mois'                => Vente::where('pharmacie_id', $pharmacie_id)
                                            ->whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)->sum('montant_total') ?? 0,
            'commandes_en_cours'     => Commande::where('pharmacie_id', $pharmacie_id)
                                               ->whereIn('statut', ['envoyee', 'en_traitement', 'expediee'])->count(),
            'stock_critique'         => Lot::where('pharmacie_id', $pharmacie_id)
                                          ->where('quantite_disponible', '<', 10)
                                          ->where('quantite_disponible', '>', 0)->count(),
        ];
    }

    private function getStatsEmploye($pharmacie_id)
    {
        if (!$pharmacie_id) return $this->getDefaultStats();

        return [
            'pharmacie'           => Pharmacie::find($pharmacie_id),
            'produits_references' => Produit::count(),
            'ventes_aujourd_hui'  => Vente::where('pharmacie_id', $pharmacie_id)
                                         ->whereDate('created_at', today())->count(),
            'alertes'             => Lot::where('pharmacie_id', $pharmacie_id)
                                       ->where('date_expiration', '<', now()->addDays(30))->count(),
        ];
    }

    private function getDefaultStats()
    {
        return [
            'pharmacies_actives'  => 0,
            'total_pharmacies'    => 0,
            'produits_references' => 0,
            'lots_expires'        => 0,
            'total_ventes'        => 0,
            'ventes_mois'         => 0,
            'ca_aujourd_hui'      => 0,
            'ca_mois'             => 0,
            'commandes_en_cours'  => 0,
        ];
    }

    private function getRecentActivities($user)
    {
        try {
            $query = AuditLog::with('user');
            if ($user->hasRole('admin_pharmacie') && $user->pharmacie_id) {
                $query->where('pharmacie_id', $user->pharmacie_id);
            } elseif (!$user->hasRole('admin_national') && $user->pharmacie_id) {
                $query->where('pharmacie_id', $user->pharmacie_id);
            }
            return $query->orderBy('created_at', 'desc')->limit(10)->get();
        } catch (\Exception $e) {
            Log::error('Erreur activités: ' . $e->getMessage());
            return collect();
        }
    }

    private function getVentesParJour($user)
    {
        try {
            $query = Vente::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )->where('created_at', '>=', now()->subDays(30));

            if ($user->hasRole('admin_pharmacie') && $user->pharmacie_id) {
                $query->where('pharmacie_id', $user->pharmacie_id);
            } elseif (!$user->hasRole('admin_national') && $user->pharmacie_id) {
                $query->where('pharmacie_id', $user->pharmacie_id);
            }

            $ventes  = $query->groupBy('date')->orderBy('date')->get();
            $labels  = [];
            $data    = [];

            for ($i = 29; $i >= 0; $i--) {
                $date     = now()->subDays($i);
                $dateStr  = $date->format('Y-m-d');
                $labels[] = $date->format('d/m');
                $vente    = $ventes->firstWhere('date', $dateStr);
                $data[]   = $vente ? $vente->total : 0;
            }

            return ['labels' => $labels, 'data' => $data];

        } catch (\Exception $e) {
            Log::error('Erreur graphique: ' . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    private function getAlertes($user)
    {
        try {
            $alertes      = collect();
            $pharmacie_id = null;

            if ($user->hasAnyRole(['admin_pharmacie', 'pharmacien', 'caissier', 'gestionnaire_stock'])) {
                $pharmacie_id = $user->pharmacie_id;
            }

            // Lots expirés
            $lotsExpires = Lot::with('produit')
                ->where('date_expiration', '<', now())
                ->where('quantite_disponible', '>', 0)
                ->when($pharmacie_id, fn($q) => $q->where('pharmacie_id', $pharmacie_id))
                ->limit(3)->get();

            foreach ($lotsExpires as $lot) {
                if ($lot->produit) {
                    $alertes->push([
                        'type'    => 'danger',
                        'icon'    => 'fa-exclamation-triangle',
                        'titre'   => 'Lot expiré',
                        'message' => $lot->produit->dci . ' — Expiré depuis ' . $lot->date_expiration->diffForHumans(),
                        'date'    => $lot->date_expiration,
                    ]);
                }
            }

            // Expiration proche
            $lotsExpProche = Lot::with('produit')
                ->whereBetween('date_expiration', [now(), now()->addDays(30)])
                ->where('quantite_disponible', '>', 0)
                ->when($pharmacie_id, fn($q) => $q->where('pharmacie_id', $pharmacie_id))
                ->limit(3)->get();

            foreach ($lotsExpProche as $lot) {
                if ($lot->produit) {
                    $jours = now()->diffInDays($lot->date_expiration);
                    $alertes->push([
                        'type'    => 'warning',
                        'icon'    => 'fa-clock',
                        'titre'   => 'Expiration proche',
                        'message' => $lot->produit->dci . ' expire dans ' . $jours . ' jour(s)',
                        'date'    => $lot->date_expiration,
                    ]);
                }
            }

            // Stock critique
            $stockCritique = Lot::with('produit')
                ->where('quantite_disponible', '<', 10)
                ->where('quantite_disponible', '>', 0)
                ->where('date_expiration', '>', now())
                ->when($pharmacie_id, fn($q) => $q->where('pharmacie_id', $pharmacie_id))
                ->limit(2)->get();

            foreach ($stockCritique as $lot) {
                if ($lot->produit) {
                    $alertes->push([
                        'type'    => 'info',
                        'icon'    => 'fa-box',
                        'titre'   => 'Stock critique',
                        'message' => $lot->produit->dci . ' — ' . $lot->quantite_disponible . ' unité(s) restante(s)',
                        'date'    => now(),
                    ]);
                }
            }

            // Commandes en attente
            if ($user->hasAnyRole(['admin_national', 'admin_pharmacie'])) {
                $commandes = Commande::with('fournisseur')
                    ->whereIn('statut', ['envoyee', 'en_traitement'])
                    ->when($pharmacie_id, fn($q) => $q->where('pharmacie_id', $pharmacie_id))
                    ->limit(2)->get();

                foreach ($commandes as $commande) {
                    $alertes->push([
                        'type'    => 'info',
                        'icon'    => 'fa-file-invoice',
                        'titre'   => 'Commande en attente',
                        'message' => 'Commande #' . $commande->numero_commande . ' — ' .
                                    ($commande->fournisseur->nom ?? 'Fournisseur') . ' — ' . $commande->statut,
                        'date'    => $commande->created_at,
                    ]);
                }
            }

            return $alertes->sortByDesc('date')->take(5)->values();

        } catch (\Exception $e) {
            Log::error('Erreur alertes: ' . $e->getMessage());
            return collect();
        }
    }
}