<?php

namespace App\Enums;

/** Photo d'intervention : avant ou après l'opération. */
enum TypePhoto: string
{
    case Avant = 'avant';
    case Apres = 'apres';

    /** Libellé lisible pour l'interface. */
    public function libelle(): string
    {
        return match ($this) {
            self::Avant => 'Avant',
            self::Apres => 'Après',
        };
    }
}
