<?php

namespace App\Console\Commands;

use App\Services\RapportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenererRapportJournalier extends Command
{
    protected $signature = 'rapports:generer {date? : Date au format Y-m-d (défaut : aujourd\'hui)}';

    protected $description = 'Génère le rapport technique journalier en PDF';

    public function handle(RapportService $service): int
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : now();

        $rapport = $service->genererPourDate($date);

        $this->info("Rapport généré pour le {$date->format('Y-m-d')} → {$rapport->chemin_pdf}");

        return self::SUCCESS;
    }
}
