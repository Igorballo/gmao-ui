<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pièces de rechange utilisées lors d'une intervention.
        // Pas de gestion de stock (hors MVP) : on enregistre référence + quantité.
        Schema::create('pieces_intervention', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->string('reference');
            $table->unsignedInteger('quantite')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pieces_intervention');
    }
};
