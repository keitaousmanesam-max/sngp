<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retours', function (Blueprint $table) {
            $table->id();
            $table->string('numero_retour')->unique();
            $table->foreignId('pharmacie_id')->constrained('pharmacies')->restrictOnDelete();
            $table->foreignId('vente_id')->constrained('ventes')->restrictOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->foreignId('lot_id')->constrained('lots')->restrictOnDelete();
            $table->foreignId('demande_par')->constrained('users')->restrictOnDelete();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('quantite');
            $table->decimal('montant_rembourse', 15, 2);
            $table->enum('motif', ['erreur_dispensation', 'defaut_qualite', 'autre']);
            $table->text('motif_detail')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->enum('destination', ['reintegre_stock', 'retourne_fournisseur'])->nullable();
            $table->timestamp('valide_le')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retours');
    }
};