<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = AuditLog::with(['user']);

        // Admin pharmacie voit uniquement son pharmacie
        if ($user->hasRole('admin_pharmacie')) {
            $query->where('pharmacie_id', $user->pharmacie_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('action', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q2) use ($request) {
                      $q2->where('nom', 'like', '%' . $request->search . '%')
                         ->orWhere('prenom', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $audits = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $stats = [
            'total'       => AuditLog::count(),
            'aujourd_hui' => AuditLog::whereDate('created_at', today())->count(),
            'cette_semaine'=> AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'ce_mois'     => AuditLog::whereMonth('created_at', now()->month)->count(),
        ];

        // Actions disponibles pour le filtre
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('audit.index', compact('audits', 'stats', 'actions'));
    }
}