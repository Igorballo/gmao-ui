<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            // date_rapport devient optionnelle (les rapports "période" n'en ont pas)
            $table->date('date_rapport')->nullable()->change();
            $table->date('date_debut')->nullable()->after('date_rapport');
            $table->date('date_fin')->nullable()->after('date_debut');
            $table->string('type')->default('journalier')->after('date_fin');
        });
    }

    public function down(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            $table->dropColumn(['date_debut', 'date_fin', 'type']);
            $table->date('date_rapport')->nullable(false)->change();
        });
    }
};
