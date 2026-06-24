<?php

namespace App\Models;

use App\Enums\StatutPanne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Panne extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'declaree_par_id',
        'date_panne',
        'description',
        'statut',
        'responsable_id',
        'deleguee_par_id',
        'deleguee_le',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutPanne::class,
            'date_panne' => 'datetime',
            'deleguee_le' => 'datetime',
            'deadline' => 'datetime',
        ];
    }

    // --- Relations ---

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /** Chef d'équipe ayant déclaré la panne. */
    public function declarePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declaree_par_id');
    }

    /** Responsable de la tâche (l'un des intervenants). */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /** Tous les intervenants affectés à la panne. */
    public function intervenants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'panne_intervenants')->withTimestamps();
    }

    /** Responsable technique ayant délégué la panne. */
    public function delegueePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleguee_par_id');
    }

    public function intervention(): HasOne
    {
        return $this->hasOne(Intervention::class);
    }
}
