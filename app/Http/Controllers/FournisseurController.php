<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Mail\CompteUtilisateurCree;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        $query = Fournisseur::withCount('commandes');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('ville', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $fournisseurs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total'      => Fournisseur::count(),
            'valides'    => Fournisseur::where('statut', 'valide')->count(),
            'en_attente' => Fournisseur::where('statut', 'en_attente')->count(),
            'suspendus'  => Fournisseur::where('statut', 'suspendu')->count(),
        ];

        return view('fournisseurs.index', compact('fournisseurs', 'stats'));
    }

    public function create()
    {
        return view('fournisseurs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'             => 'required|string|max:255',
            'email'           => 'required|email|unique:fournisseurs,email',
            'telephone'       => 'required|string|max:20',
            'adresse'         => 'required|string',
            'ville'           => 'nullable|string|max:100',
            'pays'            => 'nullable|string|max:100',
            'numero_registre' => 'nullable|string|max:100',
            'observations'    => 'nullable|string',
        ], [
            'nom.required'       => 'Le nom est obligatoire.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.unique'       => 'Cet email est déjà utilisé.',
            'telephone.required' => 'Le téléphone est obligatoire.',
            'adresse.required'   => 'L\'adresse est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $fournisseur = Fournisseur::create([
                'nom'             => $request->nom,
                'email'           => $request->email,
                'telephone'       => $request->telephone,
                'adresse'         => $request->adresse,
                'ville'           => $request->ville,
                'pays'            => $request->pays ?? 'Guinée',
                'numero_registre' => $request->numero_registre,
                'observations'    => $request->observations,
                'statut'          => 'en_attente',
            ]);

            DB::commit();

            AuditService::log(
                'creation',
                'fournisseurs',
                'Fournisseur « ' . $fournisseur->nom . ' » créé — en attente de validation',
                $fournisseur
            );

            return redirect()->route('fournisseurs.index')
                ->with('success', "Fournisseur « {$fournisseur->nom} » créé avec succès. En attente de validation.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création fournisseur: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->load('commandes.pharmacie');
        $stats = [
            'total_commandes'     => $fournisseur->commandes()->count(),
            'commandes_en_cours'  => $fournisseur->commandes()->whereIn('statut', ['envoyee', 'en_traitement', 'expediee'])->count(),
            'commandes_finalisees'=> $fournisseur->commandes()->where('statut', 'finalisee')->count(),
            'montant_total'       => $fournisseur->commandes()->where('statut', 'finalisee')->sum('montant_total'),
        ];
        return view('fournisseurs.show', compact('fournisseur', 'stats'));
    }

    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $request->validate([
            'nom'             => 'required|string|max:255',
            'email'           => 'required|email|unique:fournisseurs,email,' . $fournisseur->id,
            'telephone'       => 'required|string|max:20',
            'adresse'         => 'required|string',
            'ville'           => 'nullable|string|max:100',
            'pays'            => 'nullable|string|max:100',
            'numero_registre' => 'nullable|string|max:100',
            'observations'    => 'nullable|string',
        ]);

        $fournisseur->update($request->only([
            'nom', 'email', 'telephone', 'adresse',
            'ville', 'pays', 'numero_registre', 'observations'
        ]));

        AuditService::log(
            'modification',
            'fournisseurs',
            'Fournisseur « ' . $fournisseur->nom . ' » modifié',
            $fournisseur
        );

        return redirect()->route('fournisseurs.show', $fournisseur)
            ->with('success', 'Fournisseur mis à jour avec succès.');
    }

    public function valider(Fournisseur $fournisseur)
    {
        $fournisseur->update([
            'statut'     => 'valide',
            'valide_par' => auth()->id(),
            'valide_le'  => now(),
        ]);

        $motDePasse = Str::random(10);
        $user = User::firstOrCreate(
            ['email' => $fournisseur->email],
            [
                'nom'          => 'Fournisseur',
                'prenom'       => $fournisseur->nom,
                'email'        => $fournisseur->email,
                'telephone'    => $fournisseur->telephone,
                'pharmacie_id' => null,
            ]
        );
        $user->update([
            'password'           => Hash::make($motDePasse),
            'premiere_connexion' => true,
            'actif'              => true,
        ]);
        if (!$user->hasRole('fournisseur')) {
            $user->assignRole('fournisseur');
        }

        AuditService::log(
            'modification',
            'fournisseurs',
            'Fournisseur « ' . $fournisseur->nom . ' » validé',
            $fournisseur
        );

        $emailEnvoye = false;
        try {
            Mail::to($fournisseur->email)->send(new CompteUtilisateurCree($user, $motDePasse, 'fournisseur'));
            $emailEnvoye = true;
        } catch (\Exception $e) {
            Log::warning('Email fournisseur non envoyé (' . $fournisseur->email . '): ' . $e->getMessage());
        }

        session([
            'nouveau_utilisateur' => [
                'nom_complet'  => $fournisseur->nom,
                'email'        => $fournisseur->email,
                'role'         => 'fournisseur',
                'mot_de_passe' => $motDePasse,
                'email_envoye' => $emailEnvoye,
            ]
        ]);

        return redirect()->route('fournisseurs.index');
    }

    public function rejeter(Fournisseur $fournisseur)
    {
        $fournisseur->update(['statut' => 'rejete']);

        AuditService::log(
            'modification',
            'fournisseurs',
            'Fournisseur « ' . $fournisseur->nom . ' » rejeté',
            $fournisseur
        );

        return back()->with('success', "Fournisseur « {$fournisseur->nom} » rejeté.");
    }

    public function suspendre(Fournisseur $fournisseur)
    {
        $fournisseur->update(['statut' => 'suspendu']);

        AuditService::log(
            'modification',
            'fournisseurs',
            'Fournisseur « ' . $fournisseur->nom . ' » suspendu',
            $fournisseur
        );

        return back()->with('success', "Fournisseur « {$fournisseur->nom} » suspendu.");
    }

    public function reactiver(Fournisseur $fournisseur)
    {
        $fournisseur->update(['statut' => 'valide']);

        AuditService::log(
            'modification',
            'fournisseurs',
            'Fournisseur « ' . $fournisseur->nom . ' » réactivé',
            $fournisseur
        );

        return back()->with('success', "Fournisseur « {$fournisseur->nom} » réactivé.");
    }

    public function destroy(Fournisseur $fournisseur)
    {
        AuditService::log(
            'suppression',
            'fournisseurs',
            'Fournisseur « ' . $fournisseur->nom . ' » supprimé',
            $fournisseur
        );

        $fournisseur->delete();
        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur supprimé avec succès.');
    }
}