<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatutPanne;
use App\Http\Controllers\Controller;
use App\Http\Resources\PanneResource;
use App\Models\Panne;
use App\Services\PanneNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PanneController extends Controller
{
    public function __construct(protected PanneNotifier $notifier) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('pannes.consulter'), 403);

        $pannes = Panne::query()
            ->with(['machine', 'responsable'])
            ->when($request->statut, fn ($q) => $q->where('statut', $request->statut))
            ->orderByDesc('date_panne')
            ->paginate(20);

        return PanneResource::collection($pannes);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('pannes.creer'), 403);

        $data = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'date_panne' => ['required', 'date'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $panne = Panne::create([
            'machine_id' => $data['machine_id'],
            'declaree_par_id' => $request->user()->id,
            'date_panne' => Carbon::parse($data['date_panne']),
            'description' => $data['description'],
            'statut' => StatutPanne::EnAttente,
        ]);

        $this->notifier->panneSignalee($panne, $request->user());

        return (new PanneResource($panne->load('machine')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Panne $panne)
    {
        abort_unless($request->user()->can('pannes.consulter'), 403);

        $panne->load([
            'machine', 'responsable', 'declarePar', 'intervenants',
            'intervention.maintenancier', 'intervention.pieces', 'intervention.photos',
        ]);

        return new PanneResource($panne);
    }

    public function deleguer(Request $request, Panne $panne)
    {
        abort_unless($request->user()->can('pannes.deleguer'), 403);

        $data = $request->validate([
            'intervenants' => ['required', 'array', 'min:1'],
            'intervenants.*' => ['exists:users,id'],
            'responsable_id' => ['required', Rule::in($request->input('intervenants', []))],
            'deadline' => ['required', 'date', 'after:now'],
        ]);

        $panne->intervenants()->sync($data['intervenants']);
        $panne->update([
            'responsable_id' => $data['responsable_id'],
            'deleguee_par_id' => $request->user()->id,
            'deleguee_le' => now(),
            'deadline' => Carbon::parse($data['deadline']),
            'statut' => $panne->statut === StatutPanne::EnAttente ? StatutPanne::Assignee : $panne->statut,
        ]);

        $this->notifier->panneAssignee($panne, $request->user());

        $panne->load([
            'machine', 'responsable', 'declarePar', 'intervenants',
            'intervention.maintenancier', 'intervention.pieces', 'intervention.photos',
        ]);

        return new PanneResource($panne);
    }
}
