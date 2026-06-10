<?php

namespace App\Http\Controllers;

use App\Models\Maladie;
use App\Services\AuditService;
use Illuminate\Http\Request;

class MaladieController extends Controller
{
    public function index(Request $request)
    {
        $query = Maladie::withCount('produits');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('code_cim10', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        $maladies = $query->orderBy('nom')->paginate(10)->withQueryString();

        $stats = [
            'total'   => Maladie::count(),
            'actives' => Maladie::where('actif', true)->count(),
        ];

        return view('maladies.index', compact('maladies', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|max:255',
            'code_cim10'  => 'nullable|string|unique:maladies,code_cim10',
            'description' => 'nullable|string',
            'categorie'   => 'nullable|string|max:100',
        ], [
            'nom.required'      => 'Le nom est obligatoire.',
            'code_cim10.unique' => 'Ce code CIM-10 existe déjà.',
        ]);

        $maladie = Maladie::create([
            'nom'         => $request->nom,
            'code_cim10'  => $request->code_cim10 ? strtoupper($request->code_cim10) : null,
            'description' => $request->description,
            'categorie'   => $request->categorie,
            'actif'       => true,
        ]);

        AuditService::log(
            'creation',
            'maladies',
            'Maladie « ' . $maladie->nom . ' » créée' . ($maladie->code_cim10 ? ' — CIM-10 : ' . $maladie->code_cim10 : ''),
            $maladie
        );

        return back()->with('success', 'Maladie créée avec succès.');
    }

    public function update(Request $request, Maladie $maladie)
    {
        $request->validate([
            'nom'         => 'required|string|max:255',
            'code_cim10'  => 'nullable|string|unique:maladies,code_cim10,' . $maladie->id,
            'description' => 'nullable|string',
            'categorie'   => 'nullable|string|max:100',
        ]);

        $maladie->update([
            'nom'         => $request->nom,
            'code_cim10'  => $request->code_cim10 ? strtoupper($request->code_cim10) : null,
            'description' => $request->description,
            'categorie'   => $request->categorie,
        ]);

        AuditService::log(
            'modification',
            'maladies',
            'Maladie « ' . $maladie->nom . ' » modifiée',
            $maladie
        );

        return back()->with('success', 'Maladie mise à jour avec succès.');
    }

    public function toggleActif(Maladie $maladie)
    {
        $maladie->update(['actif' => !$maladie->actif]);
        $msg = $maladie->actif ? 'activée' : 'désactivée';

        AuditService::log(
            'modification',
            'maladies',
            'Maladie « ' . $maladie->nom . ' » ' . $msg,
            $maladie
        );

        return back()->with('success', "Maladie {$msg} avec succès.");
    }

    public function destroy(Maladie $maladie)
    {
        AuditService::log(
            'suppression',
            'maladies',
            'Maladie « ' . $maladie->nom . ' » supprimée',
            $maladie
        );

        $maladie->produits()->detach();
        $maladie->delete();
        return back()->with('success', 'Maladie supprimée avec succès.');
    }
}