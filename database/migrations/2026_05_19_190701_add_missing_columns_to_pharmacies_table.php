<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->string('nom')->after('id');
            $table->string('numero_agrement')->unique()->after('nom');
            $table->string('adresse')->after('numero_agrement');
            $table->string('region')->after('adresse');
            $table->string('prefecture')->after('region');
            $table->string('commune')->nullable()->after('prefecture');
            $table->string('telephone')->after('commune');
            $table->string('email')->unique()->after('telephone');
            $table->date('date_agrement')->after('email');
            $table->enum('statut', ['active', 'suspendue', 'fermee'])->default('active')->after('date_agrement');
            $table->string('logo')->nullable()->after('statut');
            $table->text('observations')->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn(['nom', 'numero_agrement', 'adresse', 'region', 'prefecture', 'commune', 'telephone', 'email', 'date_agrement', 'statut', 'logo', 'observations']);
        });
    }
};