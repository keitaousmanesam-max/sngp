<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckPharmacieActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // L'admin national n'a pas de pharmacie — accès total
            if ($user->hasRole('admin_national')) {
                return $next($request);
            }

            // Utilisateur sans pharmacie assignée — accès autorisé
            if (!$user->pharmacie_id) {
                return $next($request);
            }

            // Requête directe sans SoftDeletes pour éviter les faux négatifs
            $pharmacie = DB::table('pharmacies')
                ->where('id', $user->pharmacie_id)
                ->first();

            // Pharmacie introuvable
            if (!$pharmacie) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Votre compte n\'est associé à aucune pharmacie valide. Contactez l\'administrateur national.']);
            }

            // Pharmacie supprimée (soft delete)
            if ($pharmacie->deleted_at !== null) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Votre pharmacie a été supprimée. Contactez l\'administrateur national.']);
            }

            // Pharmacie suspendue
            if ($pharmacie->statut === 'suspendue') {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Votre pharmacie est suspendue. Contactez l\'administrateur national.']);
            }

            // Pharmacie fermée
            if ($pharmacie->statut === 'fermee') {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Votre pharmacie est fermée. Contactez l\'administrateur national.']);
            }
        }

        return $next($request);
    }
}