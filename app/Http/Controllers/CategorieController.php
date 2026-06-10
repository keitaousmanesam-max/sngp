<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index(Request $request)
    {
        $query = Categorie::withCount('produits');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        $categories = $query->orderBy('nom')->paginate(10)->withQueryString();

        $stats = [
            'total'   => Categorie::count(),
            'actives' => Categorie::where('actif', true)->count(),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|unique:categories,nom',
            'code'        => 'required|string|unique:categories,code',
            'description' => 'nullable|string',
        ], [
            'nom.required'  => 'Le nom est obligatoire.',
            'nom.unique'    => 'Cette catégorie existe déjà.',
            'code.required' => 'Le code est obligatoire.',
            'code.unique'   => 'Ce code existe déjà.',
        ]);

        $categorie = Categorie::create([
            'nom'         => $request->nom,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
            'actif'       => true,
        ]);

        AuditService::log(
            'creation',
            'categories',
            'Catégorie « ' . $categorie->nom . ' » créée — code : ' . $categorie->code,
            $categorie
        );

        return back()->with('success', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, Categorie $categorie)
    {
        $request->validate([
            'nom'         => 'required|string|unique:categories,nom,' . $categorie->id,
            'code'        => 'required|string|unique:categories,code,' . $categorie->id,
            'description' => 'nullable|string',
        ]);

        $categorie->update([
            'nom'         => $request->nom,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
        ]);

        AuditService::log(
            'modification',
            'categories',
            'Catégorie « ' . $categorie->nom . ' » modifiée',
            $categorie
        );

        return back()->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function toggleActif(Categorie $categorie)
    {
        $categorie->update(['actif' => !$categorie->actif]);
        $msg = $categorie->actif ? 'activée' : 'désactivée';

        AuditService::log(
            'modification',
            'categories',
            'Catégorie « ' . $categorie->nom . ' » ' . $msg,
            $categorie
        );

        return back()->with('success', "Catégorie {$msg} avec succès.");
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->produits()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une catégorie contenant des produits.');
        }

        AuditService::log(
            'suppression',
            'categories',
            'Catégorie « ' . $categorie->nom . ' » supprimée',
            $categorie
        );

        $categorie->delete();
        return back()->with('success', 'Catégorie supprimée avec succès.');
    }
}