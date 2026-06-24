<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Génère automatiquement le rapport technique de la journée (fin de journée)
Schedule::command('rapports:generer')->dailyAt('23:55');
