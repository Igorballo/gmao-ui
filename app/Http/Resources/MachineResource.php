<?php

namespace App\Http\Resources;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Machine */
class MachineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stats = $this->computeArretStats();

        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => $this->type->libelle(),
            'type_value' => $this->type->value,
            'description' => $this->description,
            'date_mise_en_production' => $this->date_mise_en_production?->format('Y-m-d'),
            'photo_url' => $this->photo
                ? $request->getSchemeAndHttpHost().'/storage/'.$this->photo
                : null,
            'statut' => [
                'value' => $this->statut->value,
                'libelle' => $this->statut->libelle(),
            ],
            'temps_arret_minutes' => $stats['minutes'],
            'temps_arret_libelle' => $stats['libelle'],
            'derniere_date_arret' => $stats['derniere']?->toIso8601String(),
        ];
    }

    /** @return array{minutes: int, libelle: string, derniere: ?Carbon} */
    private function computeArretStats(): array
    {
        $minutes = 0;
        $derniere = null;

        foreach ($this->pannes as $panne) {
            if ($panne->date_panne && ($derniere === null || $panne->date_panne->gt($derniere))) {
                $derniere = $panne->date_panne;
            }

            $intervention = $panne->intervention;
            if (! $intervention?->demarree_le) {
                continue;
            }

            if ($derniere === null || $intervention->demarree_le->gt($derniere)) {
                $derniere = $intervention->demarree_le;
            }

            $fin = $intervention->terminee_le ?? now();
            $minutes += max(0, $intervention->demarree_le->diffInMinutes($fin));
        }

        return [
            'minutes' => (int) $minutes,
            'libelle' => self::formatMinutes((int) $minutes),
            'derniere' => $derniere,
        ];
    }

    public static function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours === 0) {
            return "{$mins} min";
        }

        return $mins > 0 ? "{$hours} h {$mins} min" : "{$hours} h";
    }
}
