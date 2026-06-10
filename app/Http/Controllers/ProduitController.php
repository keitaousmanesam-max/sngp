<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Maladie;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::with('categorie')->withCount(['lots', 'lignesVente']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('dci', 'like', '%' . $request->search . '%')
                  ->orWhere('nom_commercial', 'like', '%' . $request->search . '%')
                  ->orWhere('code_produit', 'like', '%' . $request->search . '%')
                  ->orWhere('code_barre', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('ordonnance')) {
            $query->where('necessite_ordonnance', $request->ordonnance === 'oui');
        }

        $produits = $query->orderBy('dci')->paginate(15)->withQueryString();

        $stats = [
            'total'      => Produit::count(),
            'actifs'     => Produit::where('statut', 'actif')->count(),
            'ordonnance' => Produit::where('necessite_ordonnance', true)->count(),
            'categories' => Categorie::where('actif', true)->count(),
        ];

        $categories = Categorie::where('actif', true)->orderBy('nom')->get();

        return view('produits.index', compact('produits', 'stats', 'categories'));
    }

    public function create()
    {
        $categories = Categorie::where('actif', true)->orderBy('nom')->get();
        $maladies   = Maladie::where('actif', true)->orderBy('nom')->get();
        return view('produits.create', compact('categories', 'maladies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dci'                   => 'required|string|max:255',
            'nom_commercial'        => 'nullable|string|max:255',
            'code_produit'          => 'required|string|unique:produits,code_produit',
            'code_barre'            => 'nullable|string|unique:produits,code_barre',
            'categorie_id'          => 'required|exists:categories,id',
            'forme_galenique'       => 'required|string',
            'dosage'                => 'required|string',
            'unite'                 => 'required|string',
            'necessite_ordonnance'  => 'boolean',
            'prix_vente_recommande' => 'nullable|numeric|min:0',
            'description'           => 'nullable|string',
            'maladies'              => 'nullable|array',
            'maladies.*'            => 'exists:maladies,id',
        ], [
            'dci.required'             => 'La DCI est obligatoire.',
            'code_produit.required'    => 'Le code produit est obligatoire.',
            'code_produit.unique'      => 'Ce code produit existe déjà.',
            'categorie_id.required'    => 'La catégorie est obligatoire.',
            'forme_galenique.required' => 'La forme galénique est obligatoire.',
            'dosage.required'          => 'Le dosage est obligatoire.',
            'unite.required'           => 'L\'unité est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $produit = Produit::create([
                'dci'                   => $request->dci,
                'nom_commercial'        => $request->nom_commercial,
                'code_produit'          => strtoupper($request->code_produit),
                'code_barre'            => $request->code_barre,
                'categorie_id'          => $request->categorie_id,
                'forme_galenique'       => $request->forme_galenique,
                'dosage'                => $request->dosage,
                'unite'                 => $request->unite,
                'necessite_ordonnance'  => $request->boolean('necessite_ordonnance'),
                'prix_vente_recommande' => $request->prix_vente_recommande,
                'description'           => $request->description,
                'statut'                => 'actif',
            ]);

            if ($request->filled('maladies')) {
                $produit->maladies()->sync($request->maladies);
            }

            DB::commit();

            AuditService::log(
                'creation',
                'produits',
                'Produit « ' . $produit->dci . ' » créé — code : ' . $produit->code_produit,
                $produit
            );

            return redirect()->route('produits.index')
                ->with('success', "Produit « {$produit->dci} » créé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création produit: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Produit $produit)
    {
        $produit->load(['categorie', 'maladies', 'lots']);
        $stats = [
            'total_lots'       => $produit->lots()->count(),
            'lots_disponibles' => $produit->lots()->where('statut', 'disponible')->count(),
            'lots_expires'     => $produit->lots()->where('statut', 'expire')->count(),
            'total_vendu'      => $produit->lignesVente()->sum('quantite'),
        ];
        return view('produits.show', compact('produit', 'stats'));
    }

    public function edit(Produit $produit)
    {
        $produit->load('maladies');
        $categories = Categorie::where('actif', true)->orderBy('nom')->get();
        $maladies   = Maladie::where('actif', true)->orderBy('nom')->get();
        return view('produits.edit', compact('produit', 'categories', 'maladies'));
    }

    public function update(Request $request, Produit $produit)
    {
        $request->validate([
            'dci'                   => 'required|string|max:255',
            'nom_commercial'        => 'nullable|string|max:255',
            'code_produit'          => 'required|string|unique:produits,code_produit,' . $produit->id,
            'code_barre'            => 'nullable|string|unique:produits,code_barre,' . $produit->id,
            'categorie_id'          => 'required|exists:categories,id',
            'forme_galenique'       => 'required|string',
            'dosage'                => 'required|string',
            'unite'                 => 'required|string',
            'necessite_ordonnance'  => 'boolean',
            'prix_vente_recommande' => 'nullable|numeric|min:0',
            'description'           => 'nullable|string',
            'maladies'              => 'nullable|array',
            'maladies.*'            => 'exists:maladies,id',
        ]);

        DB::beginTransaction();
        try {
            $produit->update([
                'dci'                   => $request->dci,
                'nom_commercial'        => $request->nom_commercial,
                'code_produit'          => strtoupper($request->code_produit),
                'code_barre'            => $request->code_barre,
                'categorie_id'          => $request->categorie_id,
                'forme_galenique'       => $request->forme_galenique,
                'dosage'                => $request->dosage,
                'unite'                 => $request->unite,
                'necessite_ordonnance'  => $request->boolean('necessite_ordonnance'),
                'prix_vente_recommande' => $request->prix_vente_recommande,
                'description'           => $request->description,
                'statut'                => $request->statut ?? $produit->statut,
            ]);

            $produit->maladies()->sync($request->maladies ?? []);

            DB::commit();

            AuditService::log(
                'modification',
                'produits',
                'Produit « ' . $produit->dci . ' » modifié',
                $produit
            );

            return redirect()->route('produits.show', $produit)
                ->with('success', 'Produit mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(Produit $produit)
    {
        if ($produit->lots()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un produit ayant des lots en stock.');
        }

        AuditService::log(
            'suppression',
            'produits',
            'Produit « ' . $produit->dci . ' » supprimé',
            $produit
        );

        $produit->maladies()->detach();
        $produit->delete();
        return redirect()->route('produits.index')
            ->with('success', 'Produit supprimé avec succès.');
    }
}