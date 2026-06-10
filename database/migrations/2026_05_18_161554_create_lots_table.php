<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('numero_lot')->unique();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->restrictOnDelete();
            $table->foreignId('pharmacie_id')->constrained('pharmacies')->restrictOnDelete();
            $table->foreignId('commande_id')->nullable()->constrained('commandes')->nullOnDelete();
            $table->date('date_fabrication')->nullable();
            $table->date('date_expiration');
            $table->integer('quantite_recue');
            $table->integer('quantite_disponible');
            $table->decimal('prix_achat_unitaire', 15, 2);
            $table->date('date_reception');
            $table->enum('statut', ['disponible', 'expire', 'bloque', 'epuise'])->default('disponible');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};