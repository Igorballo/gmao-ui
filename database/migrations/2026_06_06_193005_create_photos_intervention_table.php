<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos_intervention', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->cascadeOnDelete();
            $table->string('chemin');             // chemin du fichier (storage)
            $table->string('type');               // App\Enums\TypePhoto : avant / apres
            $table->timestamps();

            $table->index(['intervention_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos_intervention');
    }
};
