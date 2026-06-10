<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== PERMISSIONS =====
        $permissions = [
            // Pharmacies
            'pharmacies.voir', 'pharmacies.creer', 'pharmacies.modifier',
            'pharmacies.supprimer', 'pharmacies.suspendre',

            // Utilisateurs
            'utilisateurs.voir', 'utilisateurs.creer', 'utilisateurs.modifier',
            'utilisateurs.supprimer', 'utilisateurs.activer',

            // Produits
            'produits.voir', 'produits.creer', 'produits.modifier',
            'produits.supprimer',

            // Lots
            'lots.voir', 'lots.creer', 'lots.modifier',
            'lots.bloquer',

            // Stocks
            'stocks.voir', 'stocks.ajuster', 'stocks.inventaire',

            // Fournisseurs
            'fournisseurs.voir', 'fournisseurs.creer', 'fournisseurs.modifier',
            'fournisseurs.valider', 'fournisseurs.suspendre',

            // Commandes
            'commandes.voir', 'commandes.creer', 'commandes.valider',
            'commandes.receptionner', 'commandes.annuler',

            // Ventes
            'ventes.voir', 'ventes.creer', 'ventes.annuler',
            'ventes.valider_ordonnance',

            // Retours
            'retours.voir', 'retours.creer', 'retours.valider',

            // Rapports
            'rapports.voir', 'rapports.exporter',

            // Maladies
            'maladies.voir', 'maladies.creer', 'maladies.modifier',

            // Audit
            'audit.voir',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ===== ROLES =====

        // Administrateur National (Ministère)
        $adminNational = Role::firstOrCreate(['name' => 'admin_national']);
        $adminNational->givePermissionTo(Permission::all());

        // Administrateur de Pharmacie
        $adminPharmacie = Role::firstOrCreate(['name' => 'admin_pharmacie']);
        $adminPharmacie->givePermissionTo([
            'utilisateurs.voir', 'utilisateurs.creer', 'utilisateurs.modifier',
            'utilisateurs.activer',
            'produits.voir', 'produits.creer', 'produits.modifier',
            'lots.voir', 'lots.creer', 'lots.modifier', 'lots.bloquer',
            'stocks.voir', 'stocks.ajuster', 'stocks.inventaire',
            'fournisseurs.voir',
            'commandes.voir', 'commandes.creer', 'commandes.valider',
            'commandes.receptionner', 'commandes.annuler',
            'ventes.voir', 'ventes.creer', 'ventes.annuler',
            'ventes.valider_ordonnance',
            'retours.voir', 'retours.creer', 'retours.valider',
            'rapports.voir', 'rapports.exporter',
            'maladies.voir',
            'audit.voir',
        ]);

        // Pharmacien
        $pharmacien = Role::firstOrCreate(['name' => 'pharmacien']);
        $pharmacien->givePermissionTo([
            'produits.voir',
            'lots.voir',
            'stocks.voir',
            'ventes.voir', 'ventes.creer', 'ventes.valider_ordonnance',
            'retours.voir', 'retours.valider',
            'commandes.voir',
            'rapports.voir',
        ]);

        // Caissier / Vendeur
        $caissier = Role::firstOrCreate(['name' => 'caissier']);
        $caissier->givePermissionTo([
            'produits.voir',
            'lots.voir',
            'stocks.voir',
            'ventes.voir', 'ventes.creer',
            'retours.voir', 'retours.creer',
        ]);

        // Gestionnaire de Stock
        $gestionnaireStock = Role::firstOrCreate(['name' => 'gestionnaire_stock']);
        $gestionnaireStock->givePermissionTo([
            'produits.voir',
            'lots.voir', 'lots.creer', 'lots.modifier', 'lots.bloquer',
            'stocks.voir', 'stocks.ajuster', 'stocks.inventaire',
            'commandes.voir', 'commandes.receptionner',
            'fournisseurs.voir',
        ]);

        // Assistant Pharmacien
        $assistant = Role::firstOrCreate(['name' => 'assistant_pharmacien']);
        $assistant->givePermissionTo([
            'produits.voir',
            'lots.voir',
            'stocks.voir',
            'ventes.voir', 'ventes.creer',
            'retours.voir', 'retours.creer',
        ]);

        // Fournisseur
        $fournisseur = Role::firstOrCreate(['name' => 'fournisseur']);
        $fournisseur->givePermissionTo([
            'commandes.voir',
        ]);

        $this->command->info('Rôles et permissions créés avec succès !');
    }
}