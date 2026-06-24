<?php

namespace App\Models;

use App\Enums\TypePhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoIntervention extends Model
{
    use HasFactory;

    protected $table = 'photos_intervention';

    protected $fillable = [
        'intervention_id',
        'chemin',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypePhoto::class,
        ];
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }
}
