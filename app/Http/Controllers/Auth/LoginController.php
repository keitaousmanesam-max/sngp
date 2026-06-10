<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.email'       => 'L\'adresse email n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Aucun compte ne correspond à cette adresse email.']);
        }

        // Compte désactivé (jamais pour admin national)
        if (!$user->actif && !$user->hasRole('admin_national')) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'administrateur.']);
        }

        // Compte bloqué (jamais pour admin national)
        if ($user->bloque_le !== null && !$user->hasRole('admin_national')) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Votre compte est bloqué. Contactez l\'administrateur.']);
        }

        // Vérifier la pharmacie si l'utilisateur en a une
        if ($user->pharmacie_id) {
            $pharmacie = DB::table('pharmacies')
                ->where('id', $user->pharmacie_id)
                ->first();

            if (!$pharmacie || $pharmacie->deleted_at !== null) {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Votre compte n\'est associé à aucune pharmacie valide.']);
            }

            if ($pharmacie->statut === 'suspendue') {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Votre pharmacie est suspendue. Contactez l\'administrateur national.']);
            }

            if ($pharmacie->statut === 'fermee') {
                return back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Votre pharmacie est fermée. Contactez l\'administrateur national.']);
            }
        }

        // Vérifier le mot de passe
        if (!Hash::check($request->password, $user->password)) {
            // Ne jamais bloquer l'admin national
            if (!$user->hasRole('admin_national')) {
                $user->increment('tentatives_connexion');
                $maxTentatives = config('app.sngp.max_tentatives_connexion', 5);
                if ($user->tentatives_connexion >= $maxTentatives) {
                    $user->update(['bloque_le' => now()]);
                    return back()->withErrors(['email' => 'Compte bloqué après ' . $maxTentatives . ' tentatives échouées.']);
                }
            }
            return back()->withInput($request->only('email'))
                ->withErrors(['password' => 'Le mot de passe est incorrect.']);
        }

        // Connexion réussie
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Si admin national bloqué par erreur, débloquer automatiquement
        if ($user->hasRole('admin_national')) {
            $user->update([
                'tentatives_connexion' => 0,
                'bloque_le'            => null,
                'actif'                => true,
                'derniere_connexion'   => now(),
            ]);
        } else {
            $user->update([
                'tentatives_connexion' => 0,
                'derniere_connexion'   => now(),
            ]);
        }

        try {
            AuditLog::create([
                'user_id'      => $user->id,
                'pharmacie_id' => $user->pharmacie_id,
                'action'       => 'user.login',
                'module'       => 'authentification',
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'description'  => 'Connexion réussie',
            ]);
        } catch (\Exception $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }

        if ($user->premiere_connexion) {
            return redirect()->route('password.change')
                ->with('warning', 'Veuillez changer votre mot de passe avant de continuer.');
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Bienvenue, ' . $user->prenom . ' ' . $user->nom . ' !');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        try {
            AuditLog::create([
                'user_id'      => $user->id,
                'pharmacie_id' => $user->pharmacie_id,
                'action'       => 'user.logout',
                'module'       => 'authentification',
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'description'  => 'Déconnexion',
            ]);
        } catch (\Exception $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }
}