<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordonnances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained('ventes')->cascadeOnDelete();
            $table->string('medecin_nom');
            $table->string('medecin_prenom');
            $table->date('date_prescription');
            $table->string('numero_ordonnance')->nullable();
            $table->string('etablissement_soin')->nullable();
            $table->string('patient_reference')->nullable();
            $table->string('scan_ordonnance')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordonnances');
    }
};