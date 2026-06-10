<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('dci');
            $table->string('nom_commercial')->nullable();
            $table->string('code_barre')->unique()->nullable();
            $table->string('code_produit')->unique();
            $table->foreignId('categorie_id')->constrained('categories')->restrictOnDelete();
            $table->string('forme_galenique');
            $table->string('dosage');
            $table->string('unite');
            $table->boolean('necessite_ordonnance')->default(false);
            $table->decimal('prix_vente_recommande', 15, 2)->nullable();
            $table->enum('statut', ['actif', 'inactif', 'retire'])->default('actif');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};