<?php

namespace App\Http\Controllers;

use App\Models\Retour;
use App\Models\LigneRetour;
use App\Models\Vente;
use App\Models\LigneVente;
use App\Models\Lot;
use App\Models\Stock;
use App\Models\Produit;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RetourController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $pharmacieId = $user->pharmacie_id;

        $query = Retour::with(['lignes.produit', 'user'])
            ->where('pharmacie_id', $pharmacieId);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_retour', 'like', '%' . $request->search . '%')
                  ->orWhereHas('lignes.produit', function($q2) use ($request) {
                      $q2->where('dci', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $retours = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total'      => Retour::where('pharmacie_id', $pharmacieId)->count(),
            'en_attente' => Retour::where('pharmacie_id', $pharmacieId)->where('statut', 'en_attente')->count(),
            'valides'    => Retour::where('pharmacie_id', $pharmacieId)->where('statut', 'valide')->count(),
            'rejetes'    => Retour::where('pharmacie_id', $pharmacieId)->where('statut', 'rejete')->count(),
        ];

        return view('retours.index', compact('retours', 'stats'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $pharmacieId = $user->pharmacie_id;

        $ventes = Vente::where('pharmacie_id', $pharmacieId)
            ->where('statut', 'completee')
            ->with('lignes.produit')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('retours.create', compact('ventes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vente_id'             => 'required|exists:ventes,id',
            'lignes'               => 'required|array|min:1',
            'lignes.*.produit_id'  => 'required|exists:produits,id',
            'lignes.*.quantite'    => 'required|integer|min:1',
            'lignes.*.motif_ligne' => 'nullable|string|max:255',
            'motif'                => 'required|string|max:500',
        ], [
            'vente_id.required'            => 'La vente d\'origine est obligatoire.',
            'lignes.required'              => 'Ajoutez au moins un produit.',
            'lignes.*.produit_id.required' => 'Sélectionnez un produit.',
            'lignes.*.quantite.required'   => 'La quantité est obligatoire.',
            'lignes.*.quantite.min'        => 'La quantité doit être au moins 1.',
            'motif.required'               => 'Le motif général est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $pharmacieId = $user->pharmacie_id;
            $montantTotal = 0;

            $lignesValides = array_filter($request->lignes, function($l) {
                return isset($l['quantite']) && intval($l['quantite']) > 0;
            });

            if (empty($lignesValides)) {
                return back()->withInput()->with('error', 'Veuillez saisir au moins une quantité supérieure à 0.');
            }

            foreach ($lignesValides as $ligne) {
                $ligneVente = LigneVente::where('vente_id', $request->vente_id)
                    ->where('produit_id', $ligne['produit_id'])
                    ->first();
                $prixUnitaire = $ligneVente ? $ligneVente->prix_unitaire : 0;
                $montantTotal += $ligne['quantite'] * $prixUnitaire;
            }

            $retour = Retour::create([
                'numero_retour'   => 'RTR-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'pharmacie_id'    => $pharmacieId,
                'vente_id'        => $request->vente_id,
                'demande_par'     => $user->id,
                'motif'           => $request->motif,
                'montant_rembourse' => $montantTotal,
                'statut'          => 'en_attente',
            ]);

            foreach ($lignesValides as $ligneData) {
                $ligneVente = LigneVente::where('vente_id', $request->vente_id)
                    ->where('produit_id', $ligneData['produit_id'])
                    ->first();
                $prixUnitaire = $ligneVente ? $ligneVente->prix_unitaire : 0;

                LigneRetour::create([
                    'retour_id'     => $retour->id,
                    'produit_id'    => $ligneData['produit_id'],
                    'quantite'      => $ligneData['quantite'],
                    'prix_unitaire' => $prixUnitaire,
                    'sous_total'    => $ligneData['quantite'] * $prixUnitaire,
                    'motif_ligne'   => $ligneData['motif_ligne'] ?? null,
                ]);
            }

            DB::commit();

            AuditService::log(
                'retour',
                'retours',
                'Retour ' . $retour->numero_retour . ' enregistré — ' . count($lignesValides) . ' produit(s) — ' . number_format($montantTotal, 0, ',', ' ') . ' GNF',
                $retour
            );

            return redirect()->route('retours.index')
                ->with('success', "Retour {$retour->numero_retour} enregistré. En attente de validation.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur retour: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Retour $retour)
    {
        $retour->load(['lignes.produit', 'vente', 'demandePar', 'validePar']);
        return view('retours.show', compact('retour'));
    }

    public function valider(Retour $retour)
    {
        if ($retour->statut !== 'en_attente') {
            return back()->with('error', 'Ce retour ne peut pas être validé.');
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();

            foreach ($retour->lignes as $ligne) {
                $lot = Lot::where('pharmacie_id', $retour->pharmacie_id)
                    ->where('produit_id', $ligne->produit_id)
                    ->where('statut', 'disponible')
                    ->orderBy('date_expiration', 'asc')
                    ->first();

                if ($lot) {
                    $lot->increment('quantite_disponible', $ligne->quantite);
                }

                Stock::where('produit_id', $ligne->produit_id)
                    ->where('pharmacie_id', $retour->pharmacie_id)
                    ->increment('quantite_disponible', $ligne->quantite);
            }

            $retour->update([
                'statut'     => 'valide',
                'valide_par' => $user->id,
                'valide_le'  => now(),
            ]);

            DB::commit();

            AuditService::log(
                'modification',
                'retours',
                'Retour ' . $retour->numero_retour . ' validé — ' . $retour->lignes->sum('quantite') . ' unité(s) remises en stock',
                $retour
            );

            return back()->with('success', "Retour {$retour->numero_retour} validé. Stock remis à jour.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function rejeter(Request $request, Retour $retour)
    {
        if ($retour->statut !== 'en_attente') {
            return back()->with('error', 'Ce retour ne peut pas être rejeté.');
        }

        $retour->update([
            'statut'      => 'rejete',
            'valide_par'  => auth()->id(),
            'valide_le'   => now(),
            'motif_rejet' => $request->motif_rejet ?? 'Retour non conforme',
        ]);

        AuditService::log(
            'modification',
            'retours',
            'Retour ' . $retour->numero_retour . ' rejeté — motif : ' . ($request->motif_rejet ?? 'Non conforme'),
            $retour
        );

        return back()->with('success', "Retour {$retour->numero_retour} rejeté.");
    }

    public function destroy(Retour $retour)
    {
        if ($retour->statut !== 'en_attente') {
            return back()->with('error', 'Seuls les retours en attente peuvent être supprimés.');
        }

        AuditService::log(
            'suppression',
            'retours',
            'Retour ' . $retour->numero_retour . ' supprimé',
            $retour
        );

        $retour->lignes()->delete();
        $retour->delete();
        return redirect()->route('retours.index')
            ->with('success', 'Retour supprimé avec succès.');
    }
}