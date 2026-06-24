<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatutMachine;
use App\Enums\StatutPanne;
use App\Enums\TypePhoto;
use App\Http\Controllers\Controller;
use App\Http\Resources\PanneResource;
use App\Models\Intervention;
use App\Models\Panne;
use Illuminate\Http\Request;

class InterventionController extends Controller
{
    protected array $relations = [
        'machine', 'responsable', 'declarePar', 'intervenants',
        'intervention.maintenancier', 'intervention.pieces', 'intervention.photos',
    ];

    /** Autorise si l'utilisateur peut prendre en charge ET est intervenant (ou peut déléguer). */
    protected function autoriser(Request $request, Panne $panne): void
    {
        $user = $request->user();
        $estIntervenant = $panne->intervenants()->whereKey($user->id)->exists();

        abort_unless(
            $user->can('interventions.prendre_en_charge') && ($estIntervenant || $user->can('pannes.deleguer')),
            403,
        );
    }

    protected function reponse(Panne $panne): PanneResource
    {
        return new PanneResource($panne->fresh()->load($this->relations));
    }

    public function demarrer(Request $request, Panne $panne)
    {
        $this->autoriser($request, $panne);

        if (! $panne->intervention && $panne->statut === StatutPanne::Assignee) {
            $panne->intervention()->create([
                'maintenancier_id' => $panne->responsable_id ?? $request->user()->id,
                'demarree_le' => now(),
            ]);
            $panne->update(['statut' => StatutPanne::EnCours]);
            $panne->machine->update(['statut' => StatutMachine::Arret]);
        }

        return $this->reponse($panne);
    }

    public function update(Request $request, Intervention $intervention)
    {
        $this->autoriser($request, $intervention->panne);

        $data = $request->validate([
            'cause' => ['nullable', 'string', 'max:2000'],
            'operations' => ['nullable', 'string', 'max:2000'],
            'pieces' => ['array'],
            'pieces.*.reference' => ['required', 'string', 'max:255'],
            'pieces.*.quantite' => ['required', 'integer', 'min:1'],
        ]);

        $intervention->update([
            'cause' => $data['cause'] ?? null,
            'operations' => $data['operations'] ?? null,
        ]);

        $intervention->pieces()->delete();
        foreach ($data['pieces'] ?? [] as $piece) {
            $intervention->pieces()->create([
                'reference' => $piece['reference'],
                'quantite' => (int) $piece['quantite'],
            ]);
        }

        return $this->reponse($intervention->panne);
    }

    public function ajouterPhoto(Request $request, Intervention $intervention)
    {
        $this->autoriser($request, $intervention->panne);

        $request->validate([
            'photo' => ['required', 'image', 'max:12288'],
            'type' => ['required', 'in:avant,apres'],
        ]);

        $intervention->photos()->create([
            'chemin' => $request->file('photo')->store('interventions', 'public'),
            'type' => $request->type === 'avant' ? TypePhoto::Avant : TypePhoto::Apres,
        ]);

        return $this->reponse($intervention->panne);
    }

    public function cloturer(Request $request, Intervention $intervention)
    {
        $this->autoriser($request, $intervention->panne);

        $intervention->update(['terminee_le' => now()]);
        $intervention->panne->update(['statut' => StatutPanne::Cloturee]);
        $intervention->panne->machine->update(['statut' => StatutMachine::Actif]);

        return $this->reponse($intervention->panne);
    }
}
