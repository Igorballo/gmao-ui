<?php

namespace App\Services;

use App\Enums\StatutPanne;
use App\Models\Intervention;
use App\Models\Panne;
use App\Models\Rapport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class RapportService
{
    public const DISK = 'local';

    /**
     * Génère (ou régénère) le rapport technique d'une journée et le stocke en PDF.
     */
    public function genererPourDate(Carbon $date): Rapport
    {
        $debut = $date->copy()->startOfDay();
        $fin = $date->copy()->endOfDay();

        // Pannes signalées ce jour-là
        $pannes = Panne::with(['machine', 'responsable', 'intervenants', 'intervention'])
            ->whereBetween('date_panne', [$debut, $fin])
            ->orderBy('date_panne')
            ->get();

        // Interventions démarrées ce jour-là
        $interventions = Intervention::with(['panne.machine', 'maintenancier'])
            ->whereBetween('demarree_le', [$debut, $fin])
            ->get();

        // Temps d'arrêt total (en minutes) : durée des interventions de la journée
        $tempsArretMinutes = $interventions->sum(function (Intervention $i) use ($fin) {
            if (! $i->demarree_le) {
                return 0;
            }
            $finCalcul = $i->terminee_le ?? min(now(), $fin);

            return max(0, $i->demarree_le->diffInMinutes($finCalcul));
        });

        $stats = [
            'nbPannes' => $pannes->count(),
            'nbCloturees' => $pannes->where('statut', StatutPanne::Cloturee)->count(),
            'nbEnCours' => $pannes->where('statut', StatutPanne::EnCours)->count(),
            'nbInterventions' => $interventions->count(),
            'tempsArretMinutes' => (int) $tempsArretMinutes,
        ];

        $pdf = Pdf::loadView('reports.daily', [
            'date' => $date,
            'pannes' => $pannes,
            'interventions' => $interventions,
            'stats' => $stats,
            'genereLe' => now(),
        ])->setPaper('a4');

        $chemin = "rapports/rapport-{$date->format('Y-m-d')}.pdf";
        Storage::disk(self::DISK)->put($chemin, $pdf->output());

        return Rapport::updateOrCreate(
            ['date_rapport' => $date->toDateString(), 'type' => 'journalier'],
            ['date_debut' => null, 'date_fin' => null, 'genere_le' => now(), 'chemin_pdf' => $chemin],
        );
    }

    /**
     * Génère (ou régénère) un rapport consolidé agrégé sur une période et le stocke en PDF.
     */
    public function genererPourPeriode(Carbon $debut, Carbon $fin): Rapport
    {
        $start = $debut->copy()->startOfDay();
        $end = $fin->copy()->endOfDay();

        // Pannes signalées sur la période
        $pannes = Panne::with(['machine', 'responsable', 'intervenants', 'intervention'])
            ->whereBetween('date_panne', [$start, $end])
            ->orderBy('date_panne')
            ->get();

        // Interventions démarrées sur la période
        $interventions = Intervention::with(['panne.machine', 'maintenancier'])
            ->whereBetween('demarree_le', [$start, $end])
            ->orderBy('demarree_le')
            ->get();

        // Temps d'arrêt total (en minutes) sur la période
        $tempsArretMinutes = $interventions->sum(function (Intervention $i) use ($end) {
            if (! $i->demarree_le) {
                return 0;
            }
            $finCalcul = $i->terminee_le ?? min(now(), $end);

            return max(0, $i->demarree_le->diffInMinutes($finCalcul));
        });

        $stats = [
            'nbPannes' => $pannes->count(),
            'nbCloturees' => $pannes->where('statut', StatutPanne::Cloturee)->count(),
            'nbEnCours' => $pannes->where('statut', StatutPanne::EnCours)->count(),
            'nbInterventions' => $interventions->count(),
            'tempsArretMinutes' => (int) $tempsArretMinutes,
            'nbJours' => $start->diffInDays($end) + 1,
        ];

        $pdf = Pdf::loadView('reports.period', [
            'debut' => $debut,
            'fin' => $fin,
            'pannes' => $pannes,
            'interventions' => $interventions,
            'stats' => $stats,
            'genereLe' => now(),
        ])->setPaper('a4');

        $chemin = "rapports/rapport-periode-{$debut->format('Y-m-d')}_{$fin->format('Y-m-d')}.pdf";
        Storage::disk(self::DISK)->put($chemin, $pdf->output());

        return Rapport::updateOrCreate(
            ['type' => 'periode', 'date_debut' => $debut->toDateString(), 'date_fin' => $fin->toDateString()],
            ['date_rapport' => null, 'genere_le' => now(), 'chemin_pdf' => $chemin],
        );
    }
}
