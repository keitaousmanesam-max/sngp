<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\LigneVente;
use App\Models\Lot;
use App\Models\Ordonnance;
use App\Models\Stock;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Mail\RecuVente;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VenteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $pharmacieId = $user->pharmacie_id;

        $query = Vente::with(['lignes.produit', 'user'])
            ->where('pharmacie_id', $pharmacieId);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_vente', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $ventes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total_ventes' => Vente::where('pharmacie_id', $pharmacieId)->count(),
            'ventes_jour'  => Vente::where('pharmacie_id', $pharmacieId)->whereDate('created_at', today())->count(),
            'ca_jour'      => Vente::where('pharmacie_id', $pharmacieId)->whereDate('created_at', today())->where('statut', 'completee')->sum('montant_total'),
            'ca_mois'      => Vente::where('pharmacie_id', $pharmacieId)->whereMonth('created_at', now()->month)->where('statut', 'completee')->sum('montant_total'),
        ];

        return view('ventes.index', compact('ventes', 'stats'));
    }

    public function create()
    {
        $user = auth()->user();
        $pharmacieId = $user->pharmacie_id;

        $produits = Produit::with(['lots' => function($q) use ($pharmacieId) {
            $q->where('pharmacie_id', $pharmacieId)
              ->where('statut', 'disponible')
              ->where('quantite_disponible', '>', 0)
              ->where('date_expiration', '>', now())
              ->orderBy('date_expiration', 'asc');
        }])
        ->where('statut', 'actif')
        ->orderBy('dci')
        ->get();

        return view('ventes.create', compact('produits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lignes'              => 'required|array|min:1',
            'lignes.*.produit_id' => 'required|exists:produits,id',
            'lignes.*.quantite'   => 'required|integer|min:1',
            'lignes.*.prix_vente' => 'required|numeric|min:0',
        ], [
            'lignes.required'              => 'Ajoutez au moins un médicament.',
            'lignes.*.produit_id.required' => 'Sélectionnez un produit.',
            'lignes.*.quantite.required'   => 'La quantité est obligatoire.',
            'lignes.*.quantite.min'        => 'La quantité doit être au moins 1.',
            'lignes.*.prix_vente.required' => 'Le prix de vente est obligatoire.',
        ]);

        $produitIds = collect($request->lignes)->pluck('produit_id')->filter()->unique();
        $necessite_ordonnance = Produit::whereIn('id', $produitIds)->where('necessite_ordonnance', true)->exists();

        if ($necessite_ordonnance) {
            $request->validate([
                'ordonnance_medecin_prenom'   => 'required|string|max:255',
                'ordonnance_medecin_nom'      => 'required|string|max:255',
                'ordonnance_date_prescription'=> 'required|date',
                'ordonnance_numero'           => 'nullable|string|max:255',
                'ordonnance_etablissement'    => 'nullable|string|max:255',
                'ordonnance_patient_reference'=> 'nullable|string|max:255',
                'ordonnance_observations'     => 'nullable|string',
            ], [
                'ordonnance_medecin_prenom.required'    => 'Le prénom du médecin est obligatoire (produit sous ordonnance).',
                'ordonnance_medecin_nom.required'       => 'Le nom du médecin est obligatoire (produit sous ordonnance).',
                'ordonnance_date_prescription.required' => 'La date de prescription est obligatoire (produit sous ordonnance).',
            ]);
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();
            $pharmacieId = $user->pharmacie_id;
            $montantTotal = 0;

            foreach ($request->lignes as $ligne) {
                $montantTotal += $ligne['quantite'] * $ligne['prix_vente'];
            }

            $avecOrdonnance = $necessite_ordonnance || $request->boolean('avec_ordonnance');

            $vente = Vente::create([
                'numero_vente'   => 'VNT-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'pharmacie_id'   => $pharmacieId,
                'user_id'        => $user->id,
                'type_vente'     => $avecOrdonnance ? 'avec_ordonnance' : 'sans_ordonnance',
                'montant_total'  => $montantTotal,
                'montant_paye'   => $request->montant_paye ?? $montantTotal,
                'monnaie_rendue' => max(0, ($request->montant_paye ?? $montantTotal) - $montantTotal),
                'statut'         => 'completee',
                'avec_ordonnance'=> $avecOrdonnance,
            ]);

            if ($avecOrdonnance && $request->filled('ordonnance_medecin_nom')) {
                Ordonnance::create([
                    'vente_id'           => $vente->id,
                    'medecin_nom'        => $request->ordonnance_medecin_nom,
                    'medecin_prenom'     => $request->ordonnance_medecin_prenom,
                    'date_prescription'  => $request->ordonnance_date_prescription,
                    'numero_ordonnance'  => $request->ordonnance_numero,
                    'etablissement_soin' => $request->ordonnance_etablissement,
                    'patient_reference'  => $request->ordonnance_patient_reference,
                    'observations'       => $request->ordonnance_observations,
                ]);
            }

            foreach ($request->lignes as $ligneData) {
                $produitId = $ligneData['produit_id'];
                $quantiteRestante = $ligneData['quantite'];
                $prixUnitaire = $ligneData['prix_vente'];

                $lots = Lot::where('pharmacie_id', $pharmacieId)
                    ->where('produit_id', $produitId)
                    ->where('statut', 'disponible')
                    ->where('quantite_disponible', '>', 0)
                    ->where('date_expiration', '>', now())
                    ->orderBy('date_expiration', 'asc')
                    ->get();

                $stockTotal = $lots->sum('quantite_disponible');
                if ($stockTotal < $quantiteRestante) {
                    throw new \Exception("Stock insuffisant pour le produit ID {$produitId}.");
                }

                $lotPrincipal = $lots->first();

                LigneVente::create([
                    'vente_id'      => $vente->id,
                    'produit_id'    => $produitId,
                    'lot_id'        => $lotPrincipal->id,
                    'quantite'      => $ligneData['quantite'],
                    'prix_unitaire' => $prixUnitaire,
                    'montant_total' => $ligneData['quantite'] * $prixUnitaire,
                ]);

                foreach ($lots as $lot) {
                    if ($quantiteRestante <= 0) break;

                    $deduire = min($lot->quantite_disponible, $quantiteRestante);
                    $quantiteAvant = $lot->quantite_disponible;
                    $lot->decrement('quantite_disponible', $deduire);
                    $quantiteRestante -= $deduire;
                    $quantiteApres = $lot->fresh()->quantite_disponible;

                    if ($quantiteApres <= 0) {
                        $lot->update(['statut' => 'epuise']);
                    }

                    MouvementStock::create([
                        'lot_id'         => $lot->id,
                        'produit_id'     => $produitId,
                        'pharmacie_id'   => $pharmacieId,
                        'user_id'        => $user->id,
                        'type'           => 'sortie',
                        'quantite'       => $deduire,
                        'quantite_avant' => $quantiteAvant,
                        'quantite_apres' => $quantiteApres,
                        'motif'          => 'Vente ' . $vente->numero_vente,
                        'reference'      => $vente->numero_vente,
                    ]);
                }

                Stock::where('produit_id', $produitId)
                    ->where('pharmacie_id', $pharmacieId)
                    ->decrement('quantite_disponible', $ligneData['quantite']);
            }

            DB::commit();

            AuditService::log(
                'vente',
                'ventes',
                'Vente ' . $vente->numero_vente . ' — ' . number_format($montantTotal, 0, ',', ' ') . ' GNF',
                $vente
            );

            return redirect()->route('ventes.show', $vente)
                ->with('success', "Vente {$vente->numero_vente} enregistrée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur vente: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Vente $vente)
    {
        $vente->load(['lignes.produit', 'user', 'pharmacie', 'ordonnance', 'annuleePar']);
        return view('ventes.show', compact('vente'));
    }

    public function recuPdf(Vente $vente)
    {
        $vente->load(['lignes.produit', 'user', 'pharmacie']);
        $pdf = Pdf::loadView('ventes.recu_pdf', compact('vente'))
            ->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm thermal width
        return $pdf->download('recu-' . $vente->numero_vente . '.pdf');
    }

    public function recuThermique(Vente $vente)
    {
        $vente->load(['lignes.produit', 'user', 'pharmacie']);
        return view('ventes.recu_thermique', compact('vente'));
    }

    public function envoyerEmail(Request $request, Vente $vente)
    {
        $request->validate(['email' => 'required|email|max:255']);
        $vente->load(['lignes.produit', 'user', 'pharmacie']);
        try {
            Mail::to($request->email)->send(new RecuVente($vente));
            return back()->with('success_email', 'Reçu envoyé avec succès à ' . $request->email);
        } catch (\Exception $e) {
            Log::error('Email reçu vente: ' . $e->getMessage());
            return back()->with('error_email', 'Échec de l\'envoi : vérifiez la configuration email.');
        }
    }

    public function annuler(Vente $vente)
    {
        if ($vente->statut !== 'completee') {
            return back()->with('error', 'Cette vente ne peut pas être annulée.');
        }

        DB::beginTransaction();
        try {
            foreach ($vente->lignes as $ligne) {
                $lot = Lot::where('pharmacie_id', $vente->pharmacie_id)
                    ->where('produit_id', $ligne->produit_id)
                    ->orderBy('date_expiration', 'asc')
                    ->first();

                if ($lot) {
                    $quantiteAvant = $lot->quantite_disponible;
                    $lot->increment('quantite_disponible', $ligne->quantite);
                    if ($lot->statut === 'epuise') {
                        $lot->update(['statut' => 'disponible']);
                    }

                    MouvementStock::create([
                        'lot_id'         => $lot->id,
                        'produit_id'     => $ligne->produit_id,
                        'pharmacie_id'   => $vente->pharmacie_id,
                        'user_id'        => auth()->id(),
                        'type'           => 'retour',
                        'quantite'       => $ligne->quantite,
                        'quantite_avant' => $quantiteAvant,
                        'quantite_apres' => $lot->fresh()->quantite_disponible,
                        'motif'          => 'Annulation vente ' . $vente->numero_vente,
                        'reference'      => $vente->numero_vente,
                    ]);
                }

                Stock::where('produit_id', $ligne->produit_id)
                    ->where('pharmacie_id', $vente->pharmacie_id)
                    ->increment('quantite_disponible', $ligne->quantite);
            }

            $vente->update([
                'statut'      => 'annulee',
                'annulee_le'  => now(),
                'annulee_par' => auth()->id(),
            ]);

            DB::commit();

            AuditService::log(
                'modification',
                'ventes',
                'Vente ' . $vente->numero_vente . ' annulée',
                $vente
            );

            return back()->with('success', "Vente {$vente->numero_vente} annulée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(Vente $vente)
    {
        if ($vente->statut === 'completee') {
            return back()->with('error', 'Annulez la vente avant de la supprimer.');
        }

        AuditService::log(
            'suppression',
            'ventes',
            'Vente ' . $vente->numero_vente . ' supprimée',
            $vente
        );

        $vente->lignes()->delete();
        $vente->delete();
        return redirect()->route('ventes.index')
            ->with('success', 'Vente supprimée avec succès.');
    }
}