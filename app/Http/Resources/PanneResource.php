<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PanneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'date_panne' => $this->date_panne?->toIso8601String(),
            'deadline' => $this->deadline?->toIso8601String(),
            'statut' => [
                'value' => $this->statut->value,
                'libelle' => $this->statut->libelle(),
            ],
            'machine' => $this->whenLoaded('machine', fn () => [
                'id' => $this->machine->id,
                'nom' => $this->machine->nom,
                'type' => $this->machine->type->libelle(),
                'photo_url' => $this->machine->photo
                    ? $request->getSchemeAndHttpHost().'/storage/'.$this->machine->photo
                    : null,
            ]),
            'declare_par' => $this->whenLoaded('declarePar', fn () => $this->declarePar ? [
                'id' => $this->declarePar->id,
                'name' => $this->declarePar->name,
            ] : null),
            'responsable' => $this->whenLoaded('responsable', fn () => $this->responsable ? [
                'id' => $this->responsable->id,
                'name' => $this->responsable->name,
            ] : null),
            'intervenants' => $this->whenLoaded('intervenants', fn () => $this->intervenants->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'responsable' => $u->id === $this->responsable_id,
            ])),
            'intervention' => $this->whenLoaded('intervention', fn () => $this->intervention ? [
                'id' => $this->intervention->id,
                'demarree_le' => $this->intervention->demarree_le?->toIso8601String(),
                'terminee_le' => $this->intervention->terminee_le?->toIso8601String(),
                'cause' => $this->intervention->cause,
                'operations' => $this->intervention->operations,
                'maintenancier' => $this->intervention->maintenancier ? [
                    'id' => $this->intervention->maintenancier->id,
                    'name' => $this->intervention->maintenancier->name,
                ] : null,
                'pieces' => $this->intervention->relationLoaded('pieces')
                    ? $this->intervention->pieces->map(fn ($p) => ['reference' => $p->reference, 'quantite' => $p->quantite])
                    : [],
                'photos' => $this->intervention->relationLoaded('photos')
                    ? [
                        'avant' => $this->intervention->photos
                            ->filter(fn ($p) => $p->type->value === 'avant')
                            ->values()
                            ->map(fn ($p) => [
                                'url' => $request->getSchemeAndHttpHost().'/storage/'.$p->chemin,
                            ]),
                        'apres' => $this->intervention->photos
                            ->filter(fn ($p) => $p->type->value === 'apres')
                            ->values()
                            ->map(fn ($p) => [
                                'url' => $request->getSchemeAndHttpHost().'/storage/'.$p->chemin,
                            ]),
                    ]
                    : ['avant' => [], 'apres' => []],
            ] : null),
        ];
    }
}
