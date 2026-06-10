<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(
        string $action,
        string $module,
        string $description,
        $model = null,
        array $donnees_avant = [],
        array $donnees_apres = []
    ): void {
        try {
            AuditLog::create([
                'user_id'       => Auth::id(),
                'pharmacie_id'  => Auth::user()?->pharmacie_id,
                'action'        => $action,
                'module'        => $module,
                'description'   => $description,
                'model_type'    => $model ? get_class($model) : null,
                'model_id'      => $model?->id,
                'donnees_avant' => $donnees_avant,
                'donnees_apres' => $donnees_apres,
                'ip_address'    => request()->ip(),
                'user_agent'    => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer si l'audit échoue
        }
    }
}