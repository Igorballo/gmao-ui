<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['cle', 'valeur'];

    /** Clé du rôle désigné comme « maintenancier » (pour la délégation des pannes). */
    public const ROLE_MAINTENANCIER = 'role_maintenancier';

    public static function get(string $cle, mixed $default = null): mixed
    {
        return static::query()->where('cle', $cle)->value('valeur') ?? $default;
    }

    public static function set(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }
}
