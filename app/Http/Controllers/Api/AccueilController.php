<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatutMachine;
use App\Enums\StatutPanne;
use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\Panne;
use Illuminate\Http\Request;

class AccueilController extends Controller
{
    public function index(Request $request)
    {
        $totalMachines = Machine::count();
        $machinesActives = Machine::where('statut', StatutMachine::Actif)->count();

        $stats = [
            'pannes_en_cours' => Panne::where('statut', StatutPanne::EnCours)->count(),
            'pannes_en_retard' => Panne::whereNotNull('deadline')
                ->where('deadline', '<', now())
                ->where('statut', '!=', StatutPanne::Cloturee->value)
                ->count(),
            'disponibilite' => $totalMachines > 0 ? (int) round($machinesActives / $totalMachines * 100) : 0,
        ];

        $recentes = Panne::with(['machine', 'responsable'])
            ->orderByDesc('date_panne')
            ->take(5)
            ->get()
            ->map(fn (Panne $p) => [
                'id' => $p->id,
                'machine' => $p->machine?->nom,
                'description' => $p->description,
                'date_panne' => $p->date_panne?->toIso8601String(),
                'responsable' => $p->responsable?->name,
                'statut' => [
                    'value' => $p->statut->value,
                    'libelle' => $p->statut->libelle(),
                ],
            ]);

        return response()->json([
            'stats' => $stats,
            'pannes_recentes' => $recentes,
        ]);
    }
}
