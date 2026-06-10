<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit_maladie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->foreignId('maladie_id')->constrained('maladies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['produit_id', 'maladie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_maladie');
    }
};