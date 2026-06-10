<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{
    // Formulaire changement mot de passe (première connexion)
    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    // Traiter changement mot de passe (première connexion ou volontaire)
    public function change(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required'         => 'Le nouveau mot de passe est obligatoire.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'        => 'La confirmation ne correspond pas.',
            'password.regex'            => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le nouveau mot de passe doit être différent de l\'ancien.']);
        }

        $user->update([
            'password'           => Hash::make($request->password),
            'premiere_connexion' => false,
        ]);

        try {
            AuditLog::create([
                'user_id'      => $user->id,
                'pharmacie_id' => $user->pharmacie_id,
                'action'       => 'user.password_change',
                'module'       => 'authentification',
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'description'  => 'Changement de mot de passe',
            ]);
        } catch (\Exception $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')
            ->with('success', 'Mot de passe changé avec succès.');
    }

    // Formulaire modification mot de passe (depuis le profil)
    public function showModifierForm()
    {
        return view('auth.modifier-password');
    }

    // Traiter modification mot de passe (depuis le profil)
    public function modifier(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required'         => 'Le nouveau mot de passe est obligatoire.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'        => 'La confirmation ne correspond pas.',
            'password.regex'            => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le nouveau mot de passe doit être différent de l\'ancien.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        try {
            AuditLog::create([
                'user_id'      => $user->id,
                'pharmacie_id' => $user->pharmacie_id,
                'action'       => 'user.password_change',
                'module'       => 'authentification',
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'description'  => 'Modification de mot de passe depuis le profil',
            ]);
        } catch (\Exception $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Mot de passe modifié avec succès.');
    }
}