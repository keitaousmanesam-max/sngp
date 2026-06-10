<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('telephone');
            $table->string('adresse');
            $table->string('ville')->nullable();
            $table->string('pays')->default('Guinée');
            $table->string('numero_registre')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'suspendu', 'rejete'])->default('en_attente');
            $table->text('observations')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};