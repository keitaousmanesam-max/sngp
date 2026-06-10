<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_commande', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->integer('quantite_commandee');
            $table->integer('quantite_recue')->default(0);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('montant_total', 15, 2);
            $table->enum('statut', ['en_attente', 'valide', 'rejete', 'partiel'])->default('en_attente');
            $table->text('motif_rejet')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_commande');
    }
};