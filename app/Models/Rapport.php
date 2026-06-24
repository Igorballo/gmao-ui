<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_rapport',
        'date_debut',
        'date_fin',
        'type',
        'genere_le',
        'chemin_pdf',
    ];

    protected function casts(): array
    {
        return [
            'date_rapport' => 'date',
            'date_debut' => 'date',
            'date_fin' => 'date',
            'genere_le' => 'datetime',
        ];
    }
}
