<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_vente')->unique();
            $table->foreignId('pharmacie_id')->constrained('pharmacies')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type_vente', ['avec_ordonnance', 'sans_ordonnance']);
            $table->decimal('montant_total', 15, 2);
            $table->decimal('montant_paye', 15, 2);
            $table->decimal('monnaie_rendue', 15, 2)->default(0);
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'autre'])->default('especes');
            $table->enum('statut', ['completee', 'annulee'])->default('completee');
            $table->text('motif_annulation')->nullable();
            $table->timestamp('annulee_le')->nullable();
            $table->foreignId('annulee_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};