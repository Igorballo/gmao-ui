<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();

            // Une intervention par panne (unique)
            $table->foreignId('panne_id')->unique()->constrained('pannes')->cascadeOnDelete();

            // Maintenancier en charge
            $table->foreignId('maintenancier_id')->constrained('users')->restrictOnDelete();

            $table->dateTime('demarree_le')->nullable();
            $table->dateTime('terminee_le')->nullable();

            $table->text('cause')->nullable();        // cause identifiée de la panne
            $table->text('operations')->nullable();   // description des opérations effectuées

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
