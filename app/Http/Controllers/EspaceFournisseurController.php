<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Models\Commande;
use App\Services\AuditService;
use Illuminate\Http\Request;

class EspaceFournisseurController extends Controller
{
    private function getFournisseur(): Fournisseur
    {
        $fournisseur = Fournisseur::where('email', auth()->user()->email)->first();

        if (!$fournisseur) {
            abort(403, 'Aucun profil fournisseur n\'est associé à votre compte (' . auth()->user()->email . '). Contactez l\'administrateur national.');
        }

        return $fournisseur;
    }

    public function dashboard()
    {
        $fournisseur = $this->getFournisseur();

        $stats = [
            'total'       => $fournisseur->commandes()->count(),
            'nouvelles'   => $fournisseur->commandes()->where('statut', 'envoyee')->count(),
            'en_cours'    => $fournisseur->commandes()->where('statut', 'en_traitement')->count(),
            'expediees'   => $fournisseur->commandes()->where('statut', 'expediee')->count(),
            'finalisees'  => $fournisseur->commandes()->where('statut', 'finalisee')->count(),
            'ca_total'    => $fournisseur->commandes()->where('statut', 'finalisee')->sum('montant_total'),
        ];

        $commandes_actives = $fournisseur->commandes()
            ->with('pharmacie')
            ->whereIn('statut', ['envoyee', 'en_traitement'])
            ->orderBy('date_livraison_prevue', 'asc')
            ->get();

        $commandes_recentes = $fournisseur->commandes()
            ->with('pharmacie')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('espace-fournisseur.dashboard', compact(
            'fournisseur', 'stats', 'commandes_actives', 'commandes_recentes'
        ));
    }

    public function commandes(Request $request)
    {
        $fournisseur = $this->getFournisseur();

        $query = $fournisseur->commandes()->with(['pharmacie', 'lignes']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_commande', 'like', '%' . $request->search . '%')
                  ->orWhereHas('pharmacie', fn($q2) => $q2->where('nom', 'like', '%' . $request->search . '%'));
            });
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $commandes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('espace-fournisseur.commandes', compact('fournisseur', 'commandes'));
    }

    public function showCommande(Commande $commande)
    {
        $fournisseur = $this->getFournisseur();

        abort_if($commande->fournisseur_id !== $fournisseur->id, 403);

        $commande->load(['pharmacie', 'lignes.produit']);

        return view('espace-fournisseur.commande-detail', compact('fournisseur', 'commande'));
    }

    public function profil()
    {
        $fournisseur = $this->getFournisseur();
        $stats = [
            'total'      => $fournisseur->commandes()->count(),
            'nouvelles'  => $fournisseur->commandes()->where('statut', 'envoyee')->count(),
            'finalisees' => $fournisseur->commandes()->where('statut', 'finalisee')->count(),
            'ca_total'   => $fournisseur->commandes()->where('statut', 'finalisee')->sum('montant_total'),
        ];
        return view('espace-fournisseur.profil', compact('fournisseur', 'stats'));
    }

    public function updateProfil(Request $request)
    {
        $fournisseur = $this->getFournisseur();

        $request->validate([
            'nom'              => 'required|string|max:255',
            'telephone'        => 'nullable|string|max:50',
            'numero_registre'  => 'nullable|string|max:100',
            'adresse'          => 'nullable|string|max:500',
            'ville'            => 'nullable|string|max:100',
            'pays'             => 'nullable|string|max:100',
        ]);

        $fournisseur->update($request->only(['nom', 'telephone', 'numero_registre', 'adresse', 'ville', 'pays']));

        AuditService::log('modification', 'fournisseurs', 'Fournisseur ' . $fournisseur->nom . ' a mis à jour son profil', $fournisseur);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function changerStatut(Request $request, Commande $commande)
    {
        $fournisseur = $this->getFournisseur();

        abort_if($commande->fournisseur_id !== $fournisseur->id, 403);

        $request->validate([
            'statut' => 'required|in:en_traitement,expediee',
        ]);

        $transitions = [
            'envoyee'       => 'en_traitement',
            'en_traitement' => 'expediee',
        ];

        if (!isset($transitions[$commande->statut]) || $transitions[$commande->statut] !== $request->statut) {
            return back()->with('error', 'Transition de statut non autorisée.');
        }

        $commande->update(['statut' => $request->statut]);

        AuditService::log(
            'modification',
            'commandes',
            'Fournisseur ' . $fournisseur->nom . ' — commande ' . $commande->numero_commande . ' → ' . $request->statut,
            $commande
        );

        $messages = [
            'en_traitement' => 'Commande confirmée et mise en traitement.',
            'expediee'      => 'Commande marquée comme expédiée. La pharmacie peut maintenant réceptionner.',
        ];

        return back()->with('success', $messages[$request->statut]);
    }
}
