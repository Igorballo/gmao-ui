<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'panne_id',
        'maintenancier_id',
        'demarree_le',
        'terminee_le',
        'cause',
        'operations',
    ];

    protected function casts(): array
    {
        return [
            'demarree_le' => 'datetime',
            'terminee_le' => 'datetime',
        ];
    }

    // --- Relations ---

    public function panne(): BelongsTo
    {
        return $this->belongsTo(Panne::class);
    }

    public function maintenancier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maintenancier_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PhotoIntervention::class);
    }

    public function pieces(): HasMany
    {
        return $this->hasMany(PieceIntervention::class);
    }
}
