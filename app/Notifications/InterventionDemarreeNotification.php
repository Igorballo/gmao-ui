<?php

namespace App\Notifications;

use App\Enums\NotificationType;

class InterventionDemarreeNotification extends PanneNotification
{
    protected function type(): NotificationType
    {
        return NotificationType::InterventionDemarree;
    }

    protected function title(): string
    {
        return 'Intervention démarrée';
    }

    protected function body(): string
    {
        $machine = $this->panne->machine?->nom ?? 'Machine';

        return "L'intervention sur {$machine} est en cours.";
    }
}
