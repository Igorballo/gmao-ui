<?php

use App\Enums\StatutPanne;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pannes', function (Blueprint $table) {
            $table->id();

            // Machine concernée
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();

            // Qui a déclaré la panne (chef d'équipe)
            $table->foreignId('declaree_par_id')->constrained('users')->restrictOnDelete();

            $table->dateTime('date_panne');
            $table->text('description');

            $table->string('statut')->default(StatutPanne::EnAttente->value); // App\Enums\StatutPanne

            // Délégation (responsable technique -> maintenancier)
            $table->foreignId('assignee_a_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleguee_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('deleguee_le')->nullable();
            $table->dateTime('deadline')->nullable();

            $table->timestamps();

            $table->index('statut');
            $table->index('date_panne');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pannes');
    }
};
