<?php

namespace App\Livewire;

use App\Enums\StatutMachine;
use App\Enums\StatutPanne;
use App\Models\Intervention;
use App\Models\Machine;
use App\Models\Panne;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Dashboard extends Component
{
    public function render()
    {
        // --- KPI ---
        $totalMachines = Machine::count();
        $machinesActives = Machine::where('statut', StatutMachine::Actif)->count();
        $disponibilite = $totalMachines > 0 ? round($machinesActives / $totalMachines * 100, 1) : 0;

        $pannesEnCours = Panne::where('statut', StatutPanne::EnCours)->count();

        $pannesEnRetard = Panne::whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->where('statut', '!=', StatutPanne::Cloturee->value)
            ->count();

        // Temps d'arrêt ce mois (minutes) — durées des interventions du mois
        $interventionsMois = Intervention::whereNotNull('demarree_le')
            ->where('demarree_le', '>=', now()->startOfMonth())
            ->get();
        $tempsArretMin = (int) $interventionsMois->sum(
            fn (Intervention $i) => $i->demarree_le->diffInMinutes($i->terminee_le ?? now()),
        );

        // --- Pannes OUVERTES par statut (donut) : la charge de travail actuelle.
        // On exclut les clôturées pour que le graphe reste lisible dans le temps.
        $counts = Panne::selectRaw('statut, COUNT(*) as c')->groupBy('statut')->pluck('c', 'statut');
        $statuts = [
            'en_attente' => (int) ($counts['en_attente'] ?? 0),
            'assignee' => (int) ($counts['assignee'] ?? 0),
            'en_cours' => (int) ($counts['en_cours'] ?? 0),
        ];

        // --- Série mensuelle (6 derniers mois) ---
        $serie = collect(range(5, 0))->map(function ($n) {
            $mois = now()->copy()->subMonths($n);
            $debut = $mois->copy()->startOfMonth();
            $fin = $mois->copy()->endOfMonth();

            return [
                'label' => $mois->locale('fr')->isoFormat('MMM'),
                'pannes' => Panne::whereBetween('date_panne', [$debut, $fin])->count(),
                'interventions' => Intervention::whereNotNull('demarree_le')->whereBetween('demarree_le', [$debut, $fin])->count(),
            ];
        });

        // --- Top machines les plus en panne ---
        $topMachines = Machine::withCount('pannes')
            ->having('pannes_count', '>', 0)
            ->orderByDesc('pannes_count')
            ->take(5)
            ->get();

        // --- MTTR (durée moyenne de réparation) ---
        $interventionsCloturees = Intervention::with('panne')
            ->whereNotNull('demarree_le')
            ->whereNotNull('terminee_le')
            ->get();

        $mttrMin = $interventionsCloturees->isNotEmpty()
            ? (int) round($interventionsCloturees->avg(fn (Intervention $i) => $i->demarree_le->diffInMinutes($i->terminee_le)))
            : 0;

        $mttrSerie = collect(range(5, 0))->map(function ($n) {
            $mois = now()->copy()->subMonths($n);
            $items = Intervention::whereNotNull('demarree_le')->whereNotNull('terminee_le')
                ->whereBetween('terminee_le', [$mois->copy()->startOfMonth(), $mois->copy()->endOfMonth()])
                ->get();

            return [
                'label' => $mois->locale('fr')->isoFormat('MMM'),
                'mttr' => $items->isNotEmpty() ? round($items->avg(fn (Intervention $i) => $i->demarree_le->diffInMinutes($i->terminee_le)) / 60, 1) : 0,
            ];
        });

        // --- Respect des délais (interventions clôturées ayant une échéance) ---
        $avecDeadline = $interventionsCloturees->filter(fn (Intervention $i) => $i->panne && $i->panne->deadline);
        $aTemps = $avecDeadline->filter(fn (Intervention $i) => $i->terminee_le->lte($i->panne->deadline))->count();
        $enRetard = $avecDeadline->count() - $aTemps;
        $tauxRespect = $avecDeadline->count() > 0 ? (int) round($aTemps / $avecDeadline->count() * 100) : null;

        return view('livewire.dashboard', [
            'disponibilite' => $disponibilite,
            'machinesActives' => $machinesActives,
            'totalMachines' => $totalMachines,
            'pannesEnCours' => $pannesEnCours,
            'pannesEnRetard' => $pannesEnRetard,
            'tempsArretMin' => $tempsArretMin,
            'statuts' => $statuts,
            'serie' => $serie,
            'topMachines' => $topMachines,
            'pannesRecentes' => Panne::with(['machine', 'responsable'])->orderByDesc('date_panne')->take(6)->get(),
            'mttrMin' => $mttrMin,
            'mttrSerie' => $mttrSerie,
            'mttrCount' => $interventionsCloturees->count(),
            'aTemps' => $aTemps,
            'enRetard' => $enRetard,
            'tauxRespect' => $tauxRespect,
        ]);
    }
}
