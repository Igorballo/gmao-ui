<?php

namespace App\Enums;

enum StatutMachine: string
{
    case Actif = 'actif';
    case Inactif = 'inactif';
    case Arret = 'arret';

    /** Libellé lisible pour l'interface. */
    public function libelle(): string
    {
        return match ($this) {
            self::Actif => 'Actif',
            self::Inactif => 'Inactif',
            self::Arret => 'Arrêt',
        };
    }
}
