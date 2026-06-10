<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserActif
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (!$user->actif) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'administrateur.']);
            }

            if ($user->bloque_le !== null) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Votre compte est bloqué. Contactez l\'administrateur.']);
            }
        }

        return $next($request);
    }
}