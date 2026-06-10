<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pharmacie;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\CompteUtilisateurCree;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UtilisateurController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::with(['roles', 'pharmacie']);

        if ($user->hasRole('admin_pharmacie')) {
            $query->where('pharmacie_id', $user->pharmacie_id);
        } else {
            $query->where('id', '!=', $user->id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        if ($request->filled('pharmacie_id') && $user->hasRole('admin_national')) {
            $query->where('pharmacie_id', $request->pharmacie_id);
        }

        $utilisateurs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $statsQuery = User::query();
        if ($user->hasRole('admin_pharmacie')) {
            $statsQuery->where('pharmacie_id', $user->pharmacie_id);
        } else {
            $statsQuery->where('id', '!=', $user->id);
        }

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'actifs'   => (clone $statsQuery)->where('actif', true)->count(),
            'inactifs' => (clone $statsQuery)->where('actif', false)->count(),
            'bloques'  => (clone $statsQuery)->whereNotNull('bloque_le')->count(),
        ];

        if ($user->hasRole('admin_national')) {
            $roles = Role::all();
        } else {
            $roles = Role::whereIn('name', [
                'pharmacien', 'caissier', 'gestionnaire_stock', 'assistant_pharmacien'
            ])->get();
        }

        $pharmacies = $user->hasRole('admin_national') ? Pharmacie::where('statut', 'active')->get() : collect();

        return view('utilisateurs.index', compact('utilisateurs', 'stats', 'roles', 'pharmacies'));
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('admin_national')) {
            $roles = Role::all();
            $pharmacies = Pharmacie::where('statut', 'active')->get();
        } else {
            $roles = Role::whereIn('name', [
                'pharmacien', 'caissier', 'gestionnaire_stock', 'assistant_pharmacien'
            ])->get();
            $pharmacies = collect();
        }

        return view('utilisateurs.create', compact('roles', 'pharmacies'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nom'          => 'required|string|max:255',
            'prenom'       => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'telephone'    => 'nullable|string|max:20',
            'role'         => 'required|string|exists:roles,name',
            'pharmacie_id' => 'nullable|exists:pharmacies,id',
        ], [
            'nom.required'    => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required'  => 'L\'email est obligatoire.',
            'email.unique'    => 'Cet email est déjà utilisé.',
            'role.required'   => 'Le rôle est obligatoire.',
        ]);

        DB::beginTransaction();
        try {
            $pharmacieId = $user->hasRole('admin_national')
                ? $request->pharmacie_id
                : $user->pharmacie_id;

            $motDePasse = Str::random(10);

            $newUser = User::create([
                'nom'                => $request->nom,
                'prenom'             => $request->prenom,
                'email'              => $request->email,
                'telephone'          => $request->telephone,
                'password'           => Hash::make($motDePasse),
                'pharmacie_id'       => $pharmacieId,
                'premiere_connexion' => true,
                'actif'              => true,
            ]);

            $newUser->assignRole($request->role);

            DB::commit();

            AuditService::log(
                'creation',
                'utilisateurs',
                'Utilisateur « ' . $request->prenom . ' ' . $request->nom . ' » créé — rôle : ' . $request->role,
                $newUser
            );

            $emailEnvoye = false;
            try {
                Mail::to($newUser->email)->send(new CompteUtilisateurCree($newUser, $motDePasse, $request->role));
                $emailEnvoye = true;
            } catch (\Exception $e) {
                Log::warning('Email utilisateur non envoyé (' . $newUser->email . '): ' . $e->getMessage());
            }

            session([
                'nouveau_utilisateur' => [
                    'nom_complet'  => $request->prenom . ' ' . $request->nom,
                    'email'        => $request->email,
                    'role'         => $request->role,
                    'mot_de_passe' => $motDePasse,
                    'email_envoye' => $emailEnvoye,
                ]
            ]);

            return redirect()->route('utilisateurs.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création utilisateur: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(User $utilisateur)
    {
        $utilisateur->load(['roles', 'pharmacie']);
        return view('utilisateurs.show', compact('utilisateur'));
    }

    public function edit(User $utilisateur)
    {
        $user = auth()->user();

        if ($user->hasRole('admin_national')) {
            $roles = Role::all();
            $pharmacies = Pharmacie::where('statut', 'active')->get();
        } else {
            $roles = Role::whereIn('name', [
                'pharmacien', 'caissier', 'gestionnaire_stock', 'assistant_pharmacien'
            ])->get();
            $pharmacies = collect();
        }

        return view('utilisateurs.edit', compact('utilisateur', 'roles', 'pharmacies'));
    }

    public function update(Request $request, User $utilisateur)
    {
        if ($utilisateur->hasRole('admin_national')) {
            return back()->with('error', 'Impossible de modifier l\'administrateur national.');
        }

        $request->validate([
            'nom'          => 'required|string|max:255',
            'prenom'       => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $utilisateur->id,
            'telephone'    => 'nullable|string|max:20',
            'role'         => 'required|string|exists:roles,name',
            'pharmacie_id' => 'nullable|exists:pharmacies,id',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();

            $pharmacieId = $user->hasRole('admin_national')
                ? $request->pharmacie_id
                : $utilisateur->pharmacie_id;

            $utilisateur->update([
                'nom'          => $request->nom,
                'prenom'       => $request->prenom,
                'email'        => $request->email,
                'telephone'    => $request->telephone,
                'pharmacie_id' => $pharmacieId,
            ]);

            $utilisateur->syncRoles([$request->role]);

            DB::commit();

            AuditService::log(
                'modification',
                'utilisateurs',
                'Utilisateur « ' . $utilisateur->prenom . ' ' . $utilisateur->nom . ' » modifié',
                $utilisateur
            );

            return redirect()->route('utilisateurs.index')
                ->with('success', 'Utilisateur mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function activer(User $utilisateur)
    {
        if ($utilisateur->hasRole('admin_national')) {
            return back()->with('error', 'Impossible de modifier l\'administrateur national.');
        }
        $utilisateur->update(['actif' => true, 'bloque_le' => null, 'tentatives_connexion' => 0]);
        AuditService::log('modification', 'utilisateurs', 'Compte de ' . $utilisateur->prenom . ' ' . $utilisateur->nom . ' activé', $utilisateur);
        return back()->with('success', "Compte de {$utilisateur->prenom} {$utilisateur->nom} activé.");
    }

    public function desactiver(User $utilisateur)
    {
        if ($utilisateur->hasRole('admin_national')) {
            return back()->with('error', 'Impossible de désactiver l\'administrateur national.');
        }
        $utilisateur->update(['actif' => false]);
        AuditService::log('modification', 'utilisateurs', 'Compte de ' . $utilisateur->prenom . ' ' . $utilisateur->nom . ' désactivé', $utilisateur);
        return back()->with('success', "Compte de {$utilisateur->prenom} {$utilisateur->nom} désactivé.");
    }

    public function debloquer(User $utilisateur)
    {
        $utilisateur->update(['bloque_le' => null, 'tentatives_connexion' => 0]);
        AuditService::log('modification', 'utilisateurs', 'Compte de ' . $utilisateur->prenom . ' ' . $utilisateur->nom . ' débloqué', $utilisateur);
        return back()->with('success', "Compte de {$utilisateur->prenom} {$utilisateur->nom} débloqué.");
    }

    public function reinitialiserMotDePasse(User $utilisateur)
    {
        if ($utilisateur->hasRole('admin_national')) {
            return back()->with('error', 'Impossible de réinitialiser le mot de passe de l\'administrateur national.');
        }

        $motDePasse = Str::random(10);
        $utilisateur->update([
            'password'             => Hash::make($motDePasse),
            'premiere_connexion'   => true,
            'bloque_le'            => null,
            'tentatives_connexion' => 0,
        ]);

        AuditService::log('modification', 'utilisateurs', 'Mot de passe de ' . $utilisateur->prenom . ' ' . $utilisateur->nom . ' réinitialisé', $utilisateur);

        $role = $utilisateur->getRoleNames()->first() ?? 'utilisateur';
        $emailEnvoye = false;
        try {
            Mail::to($utilisateur->email)->send(new CompteUtilisateurCree($utilisateur, $motDePasse, $role));
            $emailEnvoye = true;
        } catch (\Exception $e) {
            Log::warning('Email réinitialisation non envoyé (' . $utilisateur->email . '): ' . $e->getMessage());
        }

        session([
            'nouveau_utilisateur' => [
                'nom_complet'  => $utilisateur->prenom . ' ' . $utilisateur->nom,
                'email'        => $utilisateur->email,
                'role'         => $role,
                'mot_de_passe' => $motDePasse,
                'email_envoye' => $emailEnvoye,
            ]
        ]);

        return redirect()->route('utilisateurs.index');
    }

    public function destroy(User $utilisateur)
    {
        if ($utilisateur->hasRole('admin_national')) {
            return back()->with('error', 'Impossible de supprimer l\'administrateur national.');
        }
        if ($utilisateur->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        AuditService::log('suppression', 'utilisateurs', 'Utilisateur « ' . $utilisateur->prenom . ' ' . $utilisateur->nom . ' » supprimé', $utilisateur);

        $utilisateur->delete();
        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}