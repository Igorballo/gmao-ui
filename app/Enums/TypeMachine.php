<?php

namespace App\Enums;

/**
 * Types de machines.
 *
 * ⚠️ À ADAPTER au parc machine réel de l'usine. Comme c'est un enum,
 * ajouter/retirer un type nécessite une modification ici (puis un déploiement).
 * Si la liste doit devenir gérable par l'admin sans redéploiement,
 * il faudra passer sur une table de référence `types_machine`.
 */
enum TypeMachine: string
{
    case Tour = 'tour';
    case Fraiseuse = 'fraiseuse';
    case Presse = 'presse';
    case Convoyeur = 'convoyeur';
    case Compresseur = 'compresseur';
    case Pompe = 'pompe';
    case Autre = 'autre';

    /** Libellé lisible pour l'interface. */
    public function libelle(): string
    {
        return match ($this) {
            self::Tour => 'Tour',
            self::Fraiseuse => 'Fraiseuse',
            self::Presse => 'Presse',
            self::Convoyeur => 'Convoyeur',
            self::Compresseur => 'Compresseur',
            self::Pompe => 'Pompe',
            self::Autre => 'Autre',
        };
    }
}
