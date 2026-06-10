<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PharmacieController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\MaladieController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\RetourController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\EpidemiologieController;
use App\Http\Controllers\EspaceFournisseurController;

/*
|--------------------------------------------------------------------------
| Routes Publiques (Guests)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Routes Authentifiées
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user.actif', 'pharmacie.active'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/change', [PasswordController::class, 'change'])->name('password.update');

    Route::middleware(['premiere.connexion'])->group(function () {

        Route::get('/', function () {
            return redirect()->route('dashboard');
        });

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profil/modifier-mot-de-passe', [PasswordController::class, 'showModifierForm'])->name('password.modifier.form');
        Route::post('/profil/modifier-mot-de-passe', [PasswordController::class, 'modifier'])->name('password.modifier');

        // Pharmacies (admin national)
        Route::middleware(['role:admin_national'])->group(function () {
            Route::post('pharmacies/clear-session', function () {
                session()->forget('nouvelle_pharmacie');
                return response()->json(['ok' => true]);
            })->name('pharmacies.clear-session');
            Route::resource('pharmacies', PharmacieController::class)->parameters(['pharmacies' => 'pharmacie']);
            Route::patch('pharmacies/{pharmacie}/suspendre', [PharmacieController::class, 'suspendre'])->name('pharmacies.suspendre');
            Route::patch('pharmacies/{pharmacie}/reactiver', [PharmacieController::class, 'reactiver'])->name('pharmacies.reactiver');
        });

        // Utilisateurs (admin national + admin pharmacie)
        Route::middleware(['role:admin_national|admin_pharmacie'])->group(function () {
            Route::post('utilisateurs/clear-session', function () {
                session()->forget('nouveau_utilisateur');
                return response()->json(['ok' => true]);
            })->name('utilisateurs.clear-session');
            Route::resource('utilisateurs', UtilisateurController::class)->parameters(['utilisateurs' => 'utilisateur']);
            Route::patch('utilisateurs/{utilisateur}/activer', [UtilisateurController::class, 'activer'])->name('utilisateurs.activer');
            Route::patch('utilisateurs/{utilisateur}/desactiver', [UtilisateurController::class, 'desactiver'])->name('utilisateurs.desactiver');
            Route::patch('utilisateurs/{utilisateur}/debloquer', [UtilisateurController::class, 'debloquer'])->name('utilisateurs.debloquer');
            Route::patch('utilisateurs/{utilisateur}/reinitialiser-mot-de-passe', [UtilisateurController::class, 'reinitialiserMotDePasse'])->name('utilisateurs.reinitialiser-mot-de-passe');
        });

        // Fournisseurs (admin national)
        Route::middleware(['role:admin_national'])->group(function () {
            Route::resource('fournisseurs', FournisseurController::class)->parameters(['fournisseurs' => 'fournisseur']);
            Route::patch('fournisseurs/{fournisseur}/valider', [FournisseurController::class, 'valider'])->name('fournisseurs.valider');
            Route::patch('fournisseurs/{fournisseur}/rejeter', [FournisseurController::class, 'rejeter'])->name('fournisseurs.rejeter');
            Route::patch('fournisseurs/{fournisseur}/suspendre', [FournisseurController::class, 'suspendre'])->name('fournisseurs.suspendre');
            Route::patch('fournisseurs/{fournisseur}/reactiver', [FournisseurController::class, 'reactiver'])->name('fournisseurs.reactiver');
        });

        // Produits (admin national + admin pharmacie)
        Route::middleware(['role:admin_national|admin_pharmacie'])->group(function () {
            Route::resource('produits', ProduitController::class)->parameters(['produits' => 'produit']);
        });

        // Catégories (admin national)
        Route::middleware(['role:admin_national'])->group(function () {
            Route::get('categories', [CategorieController::class, 'index'])->name('categories.index');
            Route::post('categories', [CategorieController::class, 'store'])->name('categories.store');
            Route::put('categories/{categorie}', [CategorieController::class, 'update'])->name('categories.update');
            Route::patch('categories/{categorie}/toggle', [CategorieController::class, 'toggleActif'])->name('categories.toggle');
            Route::delete('categories/{categorie}', [CategorieController::class, 'destroy'])->name('categories.destroy');
        });

        // Maladies (admin national)
        Route::middleware(['role:admin_national'])->group(function () {
            Route::get('maladies', [MaladieController::class, 'index'])->name('maladies.index');
            Route::post('maladies', [MaladieController::class, 'store'])->name('maladies.store');
            Route::put('maladies/{maladie}', [MaladieController::class, 'update'])->name('maladies.update');
            Route::patch('maladies/{maladie}/toggle', [MaladieController::class, 'toggleActif'])->name('maladies.toggle');
            Route::delete('maladies/{maladie}', [MaladieController::class, 'destroy'])->name('maladies.destroy');
        });

        // Stocks & Lots
        Route::middleware(['role:admin_pharmacie|pharmacien|gestionnaire_stock'])->group(function () {
            Route::resource('lots', LotController::class)->parameters(['lots' => 'lot']);
            Route::post('lots/{lot}/ajustement', [LotController::class, 'ajustement'])->name('lots.ajustement');
        });

        // Ventes
        Route::middleware(['role:admin_pharmacie|pharmacien|caissier|assistant_pharmacien'])->group(function () {
            Route::resource('ventes', VenteController::class)->parameters(['ventes' => 'vente'])
                ->only(['index', 'create', 'store', 'show', 'destroy']);
            Route::patch('ventes/{vente}/annuler', [VenteController::class, 'annuler'])->name('ventes.annuler');
            Route::get('ventes/{vente}/recu/pdf', [VenteController::class, 'recuPdf'])->name('ventes.recu.pdf');
            Route::get('ventes/{vente}/recu/thermique', [VenteController::class, 'recuThermique'])->name('ventes.recu.thermique');
            Route::post('ventes/{vente}/envoyer-email', [VenteController::class, 'envoyerEmail'])->name('ventes.envoyer.email');
        });

        // Commandes
        Route::middleware(['role:admin_pharmacie|pharmacien|gestionnaire_stock'])->group(function () {
            Route::resource('commandes', CommandeController::class)->parameters(['commandes' => 'commande']);
            Route::patch('commandes/{commande}/statut', [CommandeController::class, 'changerStatut'])->name('commandes.statut');
            Route::get('commandes/{commande}/reception', [CommandeController::class, 'reception'])->name('commandes.reception');
            Route::post('commandes/{commande}/reception', [CommandeController::class, 'storeReception'])->name('commandes.reception.store');
        });

        // Retours
        Route::middleware(['role:admin_pharmacie|pharmacien|caissier|assistant_pharmacien'])->group(function () {
            Route::resource('retours', RetourController::class)->parameters(['retours' => 'retour'])
                ->only(['index', 'create', 'store', 'show', 'destroy']);
            Route::patch('retours/{retour}/valider', [RetourController::class, 'valider'])->name('retours.valider');
            Route::patch('retours/{retour}/rejeter', [RetourController::class, 'rejeter'])->name('retours.rejeter');
        });

        // Statistiques
        Route::middleware(['role:admin_national|admin_pharmacie'])->group(function () {
            Route::get('statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');
        });

        // Journal d'audit
        Route::middleware(['role:admin_national|admin_pharmacie'])->group(function () {
            Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
        });

        // Rapports
Route::middleware(['role:admin_national|admin_pharmacie'])->group(function () {
    Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');
    Route::get('rapports/ventes/pdf', [RapportController::class, 'ventesPdf'])->name('rapports.ventes.pdf');
    Route::get('rapports/ventes/excel', [RapportController::class, 'ventesExcel'])->name('rapports.ventes.excel');
    Route::get('rapports/stocks/pdf', [RapportController::class, 'stocksPdf'])->name('rapports.stocks.pdf');
    Route::get('rapports/stocks/excel', [RapportController::class, 'stocksExcel'])->name('rapports.stocks.excel');
    Route::get('rapports/commandes/pdf', [RapportController::class, 'commandesPdf'])->name('rapports.commandes.pdf');
    Route::get('rapports/commandes/excel', [RapportController::class, 'commandesExcel'])->name('rapports.commandes.excel');
    Route::get('rapports/retours/pdf', [RapportController::class, 'retoursPdf'])->name('rapports.retours.pdf');
    Route::get('rapports/retours/excel', [RapportController::class, 'retoursExcel'])->name('rapports.retours.excel');
    Route::get('rapports/lots-expires/pdf', [RapportController::class, 'lotsExpiresPdf'])->name('rapports.lots-expires.pdf');
    Route::get('rapports/lots-expires/excel', [RapportController::class, 'lotsExpiresExcel'])->name('rapports.lots-expires.excel');
});

// Épidémiologie
Route::middleware(['role:admin_national|admin_pharmacie'])->group(function () {
    Route::get('epidemiologie', [EpidemiologieController::class, 'index'])->name('epidemiologie.index');
});

        // Espace Fournisseur
        Route::middleware(['role:fournisseur'])->prefix('espace-fournisseur')->name('fournisseur.espace.')->group(function () {
            Route::get('/', [EspaceFournisseurController::class, 'dashboard'])->name('dashboard');
            Route::get('/commandes', [EspaceFournisseurController::class, 'commandes'])->name('commandes');
            Route::get('/commandes/{commande}', [EspaceFournisseurController::class, 'showCommande'])->name('commande.show');
            Route::patch('/commandes/{commande}/statut', [EspaceFournisseurController::class, 'changerStatut'])->name('commande.statut');
        });

    });
});