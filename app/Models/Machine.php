<?php

namespace App\Models;

use App\Enums\StatutMachine;
use App\Enums\TypeMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'photo',
        'type',
        'date_mise_en_production',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeMachine::class,
            'statut' => StatutMachine::class,
            'date_mise_en_production' => 'date',
        ];
    }

    // --- Relations ---

    /** Toutes les pannes (historique) de la machine. */
    public function pannes(): HasMany
    {
        return $this->hasMany(Panne::class);
    }
}
