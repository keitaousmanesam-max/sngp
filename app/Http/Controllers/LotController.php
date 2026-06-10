<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\MouvementStock;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LotController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $pharmacieId = $user->pharmacie_id;

        $query = Lot::with(['produit.categorie'])
            ->where('pharmacie_id', $pharmacieId);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_lot', 'like', '%' . $request->search . '%')
                  ->orWhereHas('produit', function($q2) use ($request) {
                      $q2->where('dci', 'like', '%' . $request->search . '%')
                         ->orWhere('nom_commercial', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        if ($request->filled('expiration')) {
            if ($request->expiration === 'expire') {
                $query->where('date_expiration', '<', now());
            } elseif ($request->expiration === 'proche') {
                $query->whereBetween('date_expiration', [now(), now()->addDays(30)]);
            } elseif ($request->expiration === 'ok') {
                $query->where('date_expiration', '>', now()->addDays(30));
            }
        }

        $lots = $query->orderBy('date_expiration', 'asc')->paginate(15)->withQueryString();

        $stats = [
            'total_lots'        => Lot::where('pharmacie_id', $pharmacieId)->count(),
            'disponibles'       => Lot::where('pharmacie_id', $pharmacieId)->where('statut', 'disponible')->count(),
            'expires'           => Lot::where('pharmacie_id', $pharmacieId)->where('statut', 'expire')->count(),
            'expiration_proche' => Lot::where('pharmacie_id', $pharmacieId)
                ->whereBetween('date_expiration', [now(), now()->addDays(30)])
                ->where('quantite_disponible', '>', 0)->count(),
            'valeur_stock'      => DB::table('lots')
                ->join('produits', 'lots.produit_id', '=', 'produits.id')
                ->where('lots.pharmacie_id', $pharmacieId)
                ->where('lots.statut', 'disponible')
                ->selectRaw('SUM(lots.quantite_disponible * produits.prix_vente_recommande) as total')
                ->value('total') ?? 0,
        ];

        $produits = Produit::where('statut', 'actif')->orderBy('dci')->get();

        return view('lots.index', compact('lots', 'stats', 'produits'));
    }

    public function create()
    {
        $produits = Produit::where('statut', 'actif')->orderBy('dci')->get();
        return view('lots.create', compact('produits'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'produit_id'        => 'required|exists:produits,id',
            'numero_lot'        => 'required|string|max:100',
            'date_fabrication'  => 'nullable|date',
            'date_expiration'   => 'required|date|after:today',
            'quantite_initiale' => 'required|integer|min:1',
            'prix_achat'        => 'required|numeric|min:0',
            'fournisseur_id'    => 'nullable|exists:fournisseurs,id',
        ], [
            'produit_id.required'        => 'Le produit est obligatoire.',
            'numero_lot.required'        => 'Le numéro de lot est obligatoire.',
            'date_expiration.required'   => 'La date d\'expiration est obligatoire.',
            'date_expiration.after'      => 'La date d\'expiration doit être dans le futur.',
            'quantite_initiale.required' => 'La quantité est obligatoire.',
            'quantite_initiale.min'      => 'La quantité doit être au moins 1.',
            'prix_achat.required'        => 'Le prix d\'achat est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $lot = Lot::create([
                'produit_id'          => $request->produit_id,
                'pharmacie_id'        => $user->pharmacie_id,
                'numero_lot'          => $request->numero_lot,
                'date_fabrication'    => $request->date_fabrication,
                'date_expiration'     => $request->date_expiration,
                'date_reception'      => now(),
                'quantite_recue'      => $request->quantite_initiale,
                'quantite_disponible' => $request->quantite_initiale,
                'prix_achat_unitaire' => $request->prix_achat,
                'fournisseur_id'      => $request->fournisseur_id,
                'statut'              => 'disponible',
            ]);

            $stock = Stock::where('produit_id', $request->produit_id)
                ->where('pharmacie_id', $user->pharmacie_id)
                ->first();

            if ($stock) {
                $stock->increment('quantite_disponible', $request->quantite_initiale);
            } else {
                Stock::create([
                    'produit_id'          => $request->produit_id,
                    'pharmacie_id'        => $user->pharmacie_id,
                    'lot_id'              => $lot->id,
                    'quantite_disponible' => $request->quantite_initiale,
                    'seuil_alerte'        => 10,
                ]);
            }

            MouvementStock::create([
                'lot_id'         => $lot->id,
                'produit_id'     => $request->produit_id,
                'pharmacie_id'   => $user->pharmacie_id,
                'user_id'        => $user->id,
                'type'           => 'entree',
                'quantite'       => $request->quantite_initiale,
                'quantite_avant' => 0,
                'quantite_apres' => $request->quantite_initiale,
                'motif'          => 'Réception lot',
            ]);

            DB::commit();

            AuditService::log(
                'creation',
                'lots',
                'Lot ' . $lot->numero_lot . ' créé — produit : ' . $lot->produit->dci . ' — quantité : ' . $request->quantite_initiale,
                $lot
            );

            return redirect()->route('lots.index')
                ->with('success', "Lot {$lot->numero_lot} ajouté avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création lot: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Lot $lot)
    {
        $lot->load(['produit.categorie', 'fournisseur', 'mouvements.user']);
        return view('lots.show', compact('lot'));
    }

    public function edit(Lot $lot)
    {
        $produits = Produit::where('statut', 'actif')->orderBy('dci')->get();
        return view('lots.edit', compact('lot', 'produits'));
    }

    public function update(Request $request, Lot $lot)
    {
        $request->validate([
            'date_expiration'     => 'required|date',
            'prix_achat_unitaire' => 'required|numeric|min:0',
            'statut'              => 'required|in:disponible,epuise,expire,retire',
        ]);

        $avant = [
            'date_expiration'     => $lot->date_expiration,
            'prix_achat_unitaire' => $lot->prix_achat_unitaire,
            'statut'              => $lot->statut,
        ];

        $lot->update([
            'date_expiration'     => $request->date_expiration,
            'prix_achat_unitaire' => $request->prix_achat_unitaire,
            'statut'              => $request->statut,
        ]);

        AuditService::log(
            'modification',
            'lots',
            'Lot ' . $lot->numero_lot . ' modifié',
            $lot,
            $avant,
            [
                'date_expiration'     => $request->date_expiration,
                'prix_achat_unitaire' => $request->prix_achat_unitaire,
                'statut'              => $request->statut,
            ]
        );

        return redirect()->route('lots.show', $lot)
            ->with('success', 'Lot mis à jour avec succès.');
    }

    public function ajustement(Request $request, Lot $lot)
    {
        $request->validate([
            'type'     => 'required|in:entree,sortie,ajustement',
            'quantite' => 'required|integer|min:1',
            'motif'    => 'required|string|max:255',
        ], [
            'type.required'     => 'Le type de mouvement est obligatoire.',
            'quantite.required' => 'La quantité est obligatoire.',
            'motif.required'    => 'Le motif est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $quantite = $request->quantite;
            $quantiteAvant = $lot->quantite_disponible;

            if ($request->type === 'sortie' || $request->type === 'ajustement') {
                if ($lot->quantite_disponible < $quantite) {
                    return back()->with('error', 'Quantité insuffisante dans ce lot.');
                }
                $lot->decrement('quantite_disponible', $quantite);

                Stock::where('produit_id', $lot->produit_id)
                    ->where('pharmacie_id', $lot->pharmacie_id)
                    ->decrement('quantite_disponible', $quantite);
            } else {
                $lot->increment('quantite_disponible', $quantite);

                Stock::where('produit_id', $lot->produit_id)
                    ->where('pharmacie_id', $lot->pharmacie_id)
                    ->increment('quantite_disponible', $quantite);
            }

            if ($lot->fresh()->quantite_disponible <= 0) {
                $lot->update(['statut' => 'epuise']);
            }

            $quantiteApres = $lot->fresh()->quantite_disponible;

            MouvementStock::create([
                'lot_id'         => $lot->id,
                'produit_id'     => $lot->produit_id,
                'pharmacie_id'   => $lot->pharmacie_id,
                'user_id'        => $user->id,
                'type'           => $request->type,
                'quantite'       => $quantite,
                'quantite_avant' => $quantiteAvant,
                'quantite_apres' => $quantiteApres,
                'motif'          => $request->motif,
            ]);

            DB::commit();

            AuditService::log(
                'modification',
                'lots',
                'Ajustement lot ' . $lot->numero_lot . ' — ' . $request->type . ' de ' . $quantite . ' unités — ' . $request->motif,
                $lot
            );

            return back()->with('success', 'Ajustement de stock effectué avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(Lot $lot)
    {
        if ($lot->quantite_disponible > 0) {
            return back()->with('error', 'Impossible de supprimer un lot avec du stock disponible.');
        }

        AuditService::log(
            'suppression',
            'lots',
            'Lot ' . $lot->numero_lot . ' supprimé',
            $lot
        );

        $lot->delete();
        return redirect()->route('lots.index')
            ->with('success', 'Lot supprimé avec succès.');
    }
}