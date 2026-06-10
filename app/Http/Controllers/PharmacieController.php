<?php

namespace App\Http\Controllers;

use App\Models\Pharmacie;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PharmacieController extends Controller
{
    public function index(Request $request)
    {
        $query = Pharmacie::withCount(['utilisateurs', 'ventes', 'lots']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('numero_agrement', 'like', '%' . $request->search . '%')
                  ->orWhere('region', 'like', '%' . $request->search . '%')
                  ->orWhere('prefecture', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        $pharmacies = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total'      => Pharmacie::count(),
            'actives'    => Pharmacie::where('statut', 'active')->count(),
            'suspendues' => Pharmacie::where('statut', 'suspendue')->count(),
            'fermees'    => Pharmacie::where('statut', 'fermee')->count(),
        ];

        $regions = Pharmacie::distinct()->pluck('region')->filter()->sort()->values();

        return view('pharmacies.index', compact('pharmacies', 'stats', 'regions'));
    }

    public function create()
    {
        return view('pharmacies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'             => 'required|string|max:255',
            'numero_agrement' => 'required|string|unique:pharmacies,numero_agrement',
            'adresse'         => 'required|string',
            'region'          => 'required|string',
            'prefecture'      => 'required|string',
            'commune'         => 'nullable|string',
            'telephone'       => 'required|string',
            'email'           => 'required|email|unique:pharmacies,email',
            'date_agrement'   => 'required|date',
            'observations'    => 'nullable|string',
        ], [
            'nom.required'             => 'Le nom de la pharmacie est obligatoire.',
            'numero_agrement.required' => 'Le numéro d\'agrément est obligatoire.',
            'numero_agrement.unique'   => 'Ce numéro d\'agrément existe déjà.',
            'email.unique'             => 'Cet email est déjà utilisé.',
            'email.email'              => 'L\'email n\'est pas valide.',
            'date_agrement.required'   => 'La date d\'agrément est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $pharmacie = Pharmacie::create([
                'nom'             => $request->nom,
                'numero_agrement' => $request->numero_agrement,
                'adresse'         => $request->adresse,
                'region'          => $request->region,
                'prefecture'      => $request->prefecture,
                'commune'         => $request->commune,
                'telephone'       => $request->telephone,
                'email'           => $request->email,
                'date_agrement'   => $request->date_agrement,
                'statut'          => 'active',
                'observations'    => $request->observations,
            ]);

            $motDePasse = Str::random(10);

            $admin = User::create([
                'pharmacie_id'       => $pharmacie->id,
                'nom'                => 'Administrateur',
                'prenom'             => $pharmacie->nom,
                'email'              => $request->email,
                'telephone'          => $request->telephone,
                'password'           => Hash::make($motDePasse),
                'premiere_connexion' => true,
                'actif'              => true,
            ]);
            $admin->assignRole('admin_pharmacie');

            DB::commit();

            AuditService::log(
                'creation',
                'pharmacies',
                'Pharmacie « ' . $pharmacie->nom . ' » créée — agrément : ' . $pharmacie->numero_agrement,
                $pharmacie
            );

            try {
                \Illuminate\Support\Facades\Mail::to($request->email)
                    ->send(new \App\Mail\ComptePharmacieCreee($pharmacie, $admin, $motDePasse));
                $emailEnvoye = true;
            } catch (\Exception $e) {
                Log::warning('Email non envoyé: ' . $e->getMessage());
                $emailEnvoye = false;
            }

            session([
                'nouvelle_pharmacie' => [
                    'nom'          => $pharmacie->nom,
                    'agrement'     => $pharmacie->numero_agrement,
                    'email'        => $request->email,
                    'mot_de_passe' => $motDePasse,
                    'email_envoye' => $emailEnvoye,
                ]
            ]);

            return redirect()->route('pharmacies.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création pharmacie: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function show(Pharmacie $pharmacie)
    {
        $pharmacie->load(['utilisateurs.roles', 'lots.produit', 'ventes', 'commandes.fournisseur']);

        $stats = [
            'total_ventes'     => $pharmacie->ventes()->count(),
            'ca_total'         => $pharmacie->ventes()->sum('montant_total'),
            'lots_disponibles' => $pharmacie->lots()->where('statut', 'disponible')->count(),
            'lots_expires'     => $pharmacie->lots()->where('statut', 'expire')->count(),
            'total_employes'   => $pharmacie->utilisateurs()->count(),
            'commandes_cours'  => $pharmacie->commandes()->whereIn('statut', ['envoyee', 'en_traitement'])->count(),
        ];

        return view('pharmacies.show', compact('pharmacie', 'stats'));
    }

    public function edit(Pharmacie $pharmacie)
    {
        return view('pharmacies.edit', compact('pharmacie'));
    }

    public function update(Request $request, Pharmacie $pharmacie)
    {
        $request->validate([
            'nom'             => 'required|string|max:255',
            'numero_agrement' => 'required|string|unique:pharmacies,numero_agrement,' . $pharmacie->id,
            'adresse'         => 'required|string',
            'region'          => 'required|string',
            'prefecture'      => 'required|string',
            'commune'         => 'nullable|string',
            'telephone'       => 'required|string',
            'email'           => 'required|email|unique:pharmacies,email,' . $pharmacie->id,
            'date_agrement'   => 'required|date',
            'observations'    => 'nullable|string',
        ]);

        $pharmacie->update($request->only([
            'nom', 'numero_agrement', 'adresse', 'region',
            'prefecture', 'commune', 'telephone', 'email',
            'date_agrement', 'observations'
        ]));

        AuditService::log(
            'modification',
            'pharmacies',
            'Pharmacie « ' . $pharmacie->nom . ' » modifiée',
            $pharmacie
        );

        return redirect()->route('pharmacies.show', $pharmacie)
            ->with('success', 'Pharmacie mise à jour avec succès.');
    }

    public function suspendre(Pharmacie $pharmacie)
    {
        $pharmacie->update(['statut' => 'suspendue']);
        User::where('pharmacie_id', $pharmacie->id)->update(['actif' => false]);

        AuditService::log(
            'modification',
            'pharmacies',
            'Pharmacie « ' . $pharmacie->nom . ' » suspendue',
            $pharmacie
        );

        return back()->with('success', "Pharmacie « {$pharmacie->nom} » suspendue avec succès.");
    }

    public function reactiver(Pharmacie $pharmacie)
    {
        $pharmacie->update(['statut' => 'active']);
        User::where('pharmacie_id', $pharmacie->id)->update(['actif' => true]);

        AuditService::log(
            'modification',
            'pharmacies',
            'Pharmacie « ' . $pharmacie->nom . ' » réactivée',
            $pharmacie
        );

        return back()->with('success', "Pharmacie « {$pharmacie->nom} » réactivée avec succès.");
    }

    public function destroy(Pharmacie $pharmacie)
    {
        AuditService::log(
            'suppression',
            'pharmacies',
            'Pharmacie « ' . $pharmacie->nom . ' » supprimée',
            $pharmacie
        );

        $pharmacie->update(['statut' => 'fermee']);
        $pharmacie->delete();

        return redirect()->route('pharmacies.index')
            ->with('success', "Pharmacie supprimée avec succès.");
    }
}