<?php

use App\Enums\StatutMachine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('type');                       // valeur de l'enum App\Enums\TypeMachine
            $table->date('date_mise_en_production')->nullable();
            $table->string('statut')->default(StatutMachine::Actif->value); // App\Enums\StatutMachine
            $table->timestamps();

            $table->index('statut');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
