<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_retours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retour_id')->constrained('retours')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 2)->default(0);
            $table->decimal('sous_total', 12, 2)->default(0);
            $table->string('motif_ligne')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_retours');
    }
};