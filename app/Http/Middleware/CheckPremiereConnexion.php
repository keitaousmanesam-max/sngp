<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPremiereConnexion
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->premiere_connexion) {
                // Autoriser uniquement la route de changement de mot de passe
                if (!$request->routeIs('password.change') && !$request->routeIs('password.update')) {
                    return redirect()->route('password.change')
                        ->with('warning', 'Veuillez changer votre mot de passe avant de continuer.');
                }
            }
        }

        return $next($request);
    }
}