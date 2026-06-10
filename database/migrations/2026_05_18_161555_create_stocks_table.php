<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacie_id')->constrained('pharmacies')->restrictOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->restrictOnDelete();
            $table->foreignId('lot_id')->constrained('lots')->restrictOnDelete();
            $table->integer('quantite_disponible')->default(0);
            $table->integer('seuil_alerte')->default(10);
            $table->timestamps();

            $table->unique(['pharmacie_id', 'produit_id', 'lot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};