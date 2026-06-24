<?php

namespace App\Enums;

/**
 * Cycle de vie d'une panne :
 * EnAttente (créée par le chef d'équipe)
 *   -> Assignee (déléguée à un maintenancier par le responsable technique)
 *   -> EnCours (intervention démarrée par le maintenancier)
 *   -> Cloturee (intervention terminée)
 */
enum StatutPanne: string
{
    case EnAttente = 'en_attente';
    case Assignee = 'assignee';
    case EnCours = 'en_cours';
    case Cloturee = 'cloturee';

    /** Libellé lisible pour l'interface. */
    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::Assignee => 'Assignée',
            self::EnCours => 'En cours',
            self::Cloturee => 'Clôturée',
        };
    }
}
