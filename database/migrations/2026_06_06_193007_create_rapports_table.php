<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rapport technique journalier (généré automatiquement en fin de journée).
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->date('date_rapport')->unique();
            $table->dateTime('genere_le')->nullable();
            $table->string('chemin_pdf')->nullable();   // PDF généré (storage)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
