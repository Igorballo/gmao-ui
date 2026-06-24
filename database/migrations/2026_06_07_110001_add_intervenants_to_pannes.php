<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table de liaison : plusieurs intervenants par panne
        Schema::create('panne_intervenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panne_id')->constrained('pannes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['panne_id', 'user_id']);
        });

        Schema::table('pannes', function (Blueprint $table) {
            // Responsable de la tâche (l'un des intervenants)
            $table->foreignId('responsable_id')->nullable()->after('description')->constrained('users')->nullOnDelete();

            // Remplacé par la relation many-to-many ci-dessus
            $table->dropConstrainedForeignId('assignee_a_id');
        });
    }

    public function down(): void
    {
        Schema::table('pannes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsable_id');
            $table->foreignId('assignee_a_id')->nullable()->after('statut')->constrained('users')->nullOnDelete();
        });

        Schema::dropIfExists('panne_intervenants');
    }
};
