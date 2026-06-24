<?php

namespace App\Notifications;

use App\Enums\NotificationType;

class PanneAssigneeNotification extends PanneNotification
{
    protected function type(): NotificationType
    {
        return NotificationType::PanneAssignee;
    }

    protected function title(): string
    {
        return 'Panne assignée';
    }

    protected function body(): string
    {
        $machine = $this->panne->machine?->nom ?? 'Machine';

        return "Vous êtes affecté à la panne sur {$machine}.";
    }
}
