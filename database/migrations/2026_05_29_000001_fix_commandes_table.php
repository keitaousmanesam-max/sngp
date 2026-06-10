<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            // date_commande devient nullable avec défaut today
            $table->date('date_commande')->nullable()->default(null)->change();

            // date_reception pour enregistrer la date de livraison effective
            $table->date('date_reception')->nullable()->after('date_livraison_reelle');
        });

        // Modifier l'enum statut pour ajouter 'en_attente'
        // (MySQL nécessite un ALTER COLUMN direct pour les enums)
        DB::statement("ALTER TABLE commandes MODIFY COLUMN statut ENUM(
            'en_attente',
            'brouillon',
            'envoyee',
            'recue',
            'en_traitement',
            'expediee',
            'receptionnee',
            'finalisee',
            'annulee'
        ) NOT NULL DEFAULT 'en_attente'");
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn('date_reception');
        });

        DB::statement("ALTER TABLE commandes MODIFY COLUMN statut ENUM(
            'brouillon',
            'envoyee',
            'recue',
            'en_traitement',
            'expediee',
            'receptionnee',
            'finalisee',
            'annulee'
        ) NOT NULL DEFAULT 'brouillon'");

        Schema::table('commandes', function (Blueprint $table) {
            $table->date('date_commande')->nullable(false)->change();
        });
    }
};
