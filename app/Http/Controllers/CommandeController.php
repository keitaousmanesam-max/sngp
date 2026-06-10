<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Lot;
use App\Models\Stock;
use App\Models\MouvementStock;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommandeController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $pharmacieId = $user->pharmacie_id;

        $query = Commande::with(['fournisseur', 'lignes'])
            ->where('pharmacie_id', $pharmacieId);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_commande', 'like', '%' . $request->search . '%')
                  ->orWhereHas('fournisseur', function($q2) use ($request) {
                      $q2->where('nom', 'like', '%' . $request->search . '%');
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

        $commandes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total'         => Commande::where('pharmacie_id', $pharmacieId)->count(),
            'en_attente'    => Commande::where('pharmacie_id', $pharmacieId)->where('statut', 'en_attente')->count(),
            'en_cours'      => Commande::where('pharmacie_id', $pharmacieId)->whereIn('statut', ['envoyee', 'en_traitement', 'expediee'])->count(),
            'finalisees'    => Commande::where('pharmacie_id', $pharmacieId)->where('statut', 'finalisee')->count(),
            'montant_total' => Commande::where('pharmacie_id', $pharmacieId)->where('statut', 'finalisee')->sum('montant_total'),
        ];

        return view('commandes.index', compact('commandes', 'stats'));
    }

    public function create()
    {
        $fournisseurs = Fournisseur::where('statut', 'valide')->orderBy('nom')->get();
        $produits = Produit::where('statut', 'actif')->orderBy('dci')->get();
        return view('commandes.create', compact('fournisseurs', 'produits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fournisseur_id'        => 'required|exists:fournisseurs,id',
            'lignes'                => 'required|array|min:1',
            'lignes.*.produit_id'   => 'required|exists:produits,id',
            'lignes.*.quantite'     => 'required|integer|min:1',
            'date_livraison_prevue' => 'nullable|date|after:today',
            'observations'          => 'nullable|string',
        ], [
            'fournisseur_id.required'      => 'Le fournisseur est obligatoire.',
            'lignes.required'              => 'Ajoutez au moins un produit.',
            'lignes.*.produit_id.required' => 'Sélectionnez un produit.',
            'lignes.*.quantite.required'   => 'La quantité est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();

            $commande = Commande::create([
                'numero_commande'       => 'CMD-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'pharmacie_id'          => $user->pharmacie_id,
                'fournisseur_id'        => $request->fournisseur_id,
                'created_by'            => $user->id,
                'montant_total'         => 0,
                'statut'                => 'en_attente',
                'date_commande'         => today(),
                'date_livraison_prevue' => $request->date_livraison_prevue,
                'observations'          => $request->observations,
            ]);

            foreach ($request->lignes as $ligneData) {
                LigneCommande::create([
                    'commande_id'        => $commande->id,
                    'produit_id'         => $ligneData['produit_id'],
                    'quantite_commandee' => $ligneData['quantite'],
                    'quantite_recue'     => 0,
                    'prix_unitaire'      => 0,
                    'montant_total'      => 0,
                ]);
            }

            DB::commit();

            AuditService::log(
                'commande',
                'commandes',
                'Commande ' . $commande->numero_commande . ' créée — fournisseur : ' . $commande->fournisseur->nom,
                $commande
            );

            return redirect()->route('commandes.show', $commande)
                ->with('success', "Commande {$commande->numero_commande} créée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur commande: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Commande $commande)
    {
        $commande->load(['fournisseur', 'lignes.produit', 'user']);
        return view('commandes.show', compact('commande'));
    }

    public function edit(Commande $commande)
    {
        if (!in_array($commande->statut, ['en_attente'])) {
            return back()->with('error', 'Seules les commandes en attente peuvent être modifiées.');
        }
        $fournisseurs = Fournisseur::where('statut', 'valide')->orderBy('nom')->get();
        $produits = Produit::where('statut', 'actif')->orderBy('dci')->get();
        return view('commandes.edit', compact('commande', 'fournisseurs', 'produits'));
    }

    public function update(Request $request, Commande $commande)
    {
        if (!in_array($commande->statut, ['en_attente'])) {
            return back()->with('error', 'Seules les commandes en attente peuvent être modifiées.');
        }

        $request->validate([
            'fournisseur_id'        => 'required|exists:fournisseurs,id',
            'lignes'                => 'required|array|min:1',
            'lignes.*.produit_id'   => 'required|exists:produits,id',
            'lignes.*.quantite'     => 'required|integer|min:1',
            'date_livraison_prevue' => 'nullable|date',
            'observations'          => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $commande->update([
                'fournisseur_id'        => $request->fournisseur_id,
                'date_livraison_prevue' => $request->date_livraison_prevue,
                'observations'          => $request->observations,
            ]);

            $commande->lignes()->delete();

            foreach ($request->lignes as $ligneData) {
                LigneCommande::create([
                    'commande_id'        => $commande->id,
                    'produit_id'         => $ligneData['produit_id'],
                    'quantite_commandee' => $ligneData['quantite'],
                    'quantite_recue'     => 0,
                    'prix_unitaire'      => 0,
                    'montant_total'      => 0,
                ]);
            }

            DB::commit();

            AuditService::log(
                'modification',
                'commandes',
                'Commande ' . $commande->numero_commande . ' modifiée',
                $commande
            );

            return redirect()->route('commandes.show', $commande)
                ->with('success', 'Commande mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function changerStatut(Request $request, Commande $commande)
    {
        $request->validate([
            'statut' => 'required|in:envoyee,en_traitement,expediee,finalisee,annulee',
        ]);

        $transitions = [
            'en_attente'    => ['envoyee', 'annulee'],
            'envoyee'       => ['en_traitement', 'annulee'],
            'en_traitement' => ['expediee', 'annulee'],
            'expediee'      => ['finalisee', 'annulee'],
        ];

        if (!isset($transitions[$commande->statut]) ||
            !in_array($request->statut, $transitions[$commande->statut])) {
            return back()->with('error', 'Transition de statut non autorisée.');
        }

        $commande->update(['statut' => $request->statut]);

        AuditService::log(
            'modification',
            'commandes',
            'Commande ' . $commande->numero_commande . ' — statut changé en : ' . $request->statut,
            $commande
        );

        $messages = [
            'envoyee'       => 'Commande envoyée au fournisseur.',
            'en_traitement' => 'Commande en cours de traitement.',
            'expediee'      => 'Commande expédiée.',
            'finalisee'     => 'Commande finalisée et reçue.',
            'annulee'       => 'Commande annulée.',
        ];

        return back()->with('success', $messages[$request->statut]);
    }

    public function reception(Commande $commande)
    {
        if ($commande->statut !== 'expediee') {
            return back()->with('error', 'La réception est possible uniquement pour les commandes expédiées.');
        }
        $commande->load(['fournisseur', 'lignes.produit']);
        return view('commandes.reception', compact('commande'));
    }

    public function storeReception(Request $request, Commande $commande)
    {
        if ($commande->statut !== 'expediee') {
            return back()->with('error', 'La réception est possible uniquement pour les commandes expédiées.');
        }

        $request->validate([
            'lignes'                    => 'required|array',
            'lignes.*.prix_unitaire'    => 'required|numeric|min:0',
            'lignes.*.quantite_recue'   => 'required|integer|min:0',
            'lignes.*.date_expiration'  => 'required|date|after:today',
            'lignes.*.numero_lot'       => 'required|string|max:100',
            'lignes.*.date_fabrication' => 'nullable|date|before:today',
        ], [
            'lignes.*.prix_unitaire.required'   => 'Le prix unitaire est obligatoire.',
            'lignes.*.quantite_recue.required'  => 'La quantité reçue est obligatoire.',
            'lignes.*.date_expiration.required' => 'La date d\'expiration est obligatoire.',
            'lignes.*.date_expiration.after'    => 'La date d\'expiration doit être dans le futur.',
            'lignes.*.numero_lot.required'      => 'Le numéro de lot est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $pharmacieId = $user->pharmacie_id;
            $montantTotal = 0;

            foreach ($request->lignes as $ligneId => $data) {
                $ligneCommande = LigneCommande::findOrFail($ligneId);
                $prixUnitaire  = (float) $data['prix_unitaire'];
                $quantiteRecue = (int)   $data['quantite_recue'];
                $montantLigne  = $quantiteRecue * $prixUnitaire;
                $montantTotal += $montantLigne;

                $statutLigne = 'valide';
                if ($quantiteRecue === 0) {
                    $statutLigne = 'rejete';
                } elseif ($quantiteRecue < $ligneCommande->quantite_commandee) {
                    $statutLigne = 'partiel';
                }

                $ligneCommande->update([
                    'quantite_recue' => $quantiteRecue,
                    'prix_unitaire'  => $prixUnitaire,
                    'montant_total'  => $montantLigne,
                    'statut'         => $statutLigne,
                ]);

                if ($quantiteRecue > 0) {
                    $stockAvant = Stock::where('produit_id', $ligneCommande->produit_id)
                        ->where('pharmacie_id', $pharmacieId)
                        ->value('quantite_disponible') ?? 0;

                    $lot = Lot::create([
                        'numero_lot'          => $data['numero_lot'],
                        'produit_id'          => $ligneCommande->produit_id,
                        'fournisseur_id'      => $commande->fournisseur_id,
                        'pharmacie_id'        => $pharmacieId,
                        'commande_id'         => $commande->id,
                        'date_fabrication'    => $data['date_fabrication'] ?? null,
                        'date_expiration'     => $data['date_expiration'],
                        'quantite_recue'      => $quantiteRecue,
                        'quantite_disponible' => $quantiteRecue,
                        'prix_achat_unitaire' => $prixUnitaire,
                        'date_reception'      => today(),
                        'statut'              => 'disponible',
                    ]);

                    Stock::updateOrCreate(
                        ['produit_id' => $ligneCommande->produit_id, 'pharmacie_id' => $pharmacieId],
                        ['quantite_disponible' => DB::raw('quantite_disponible + ' . $quantiteRecue)]
                    );

                    MouvementStock::create([
                        'lot_id'         => $lot->id,
                        'produit_id'     => $ligneCommande->produit_id,
                        'pharmacie_id'   => $pharmacieId,
                        'user_id'        => $user->id,
                        'type'           => 'entree',
                        'quantite'       => $quantiteRecue,
                        'quantite_avant' => $stockAvant,
                        'quantite_apres' => $stockAvant + $quantiteRecue,
                        'motif'          => 'Réception commande ' . $commande->numero_commande,
                        'reference'      => $commande->numero_commande,
                    ]);
                }
            }

            $commande->update([
                'montant_total'        => $montantTotal,
                'statut'               => 'finalisee',
                'date_reception'       => today(),
                'date_livraison_reelle'=> today(),
            ]);

            DB::commit();

            AuditService::log(
                'reception',
                'commandes',
                'Réception commande ' . $commande->numero_commande . ' — ' . number_format($montantTotal, 0, ',', ' ') . ' GNF',
                $commande
            );

            return redirect()->route('commandes.show', $commande)
                ->with('success', "Commande {$commande->numero_commande} réceptionnée. Les lots ont été ajoutés au stock.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur réception commande: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(Commande $commande)
    {
        if ($commande->statut !== 'en_attente') {
            return back()->with('error', 'Seules les commandes en attente peuvent être supprimées.');
        }

        AuditService::log(
            'suppression',
            'commandes',
            'Commande ' . $commande->numero_commande . ' supprimée',
            $commande
        );

        $commande->lignes()->delete();
        $commande->delete();
        return redirect()->route('commandes.index')
            ->with('success', 'Commande supprimée avec succès.');
    }
}