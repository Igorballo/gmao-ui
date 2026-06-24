<?php

namespace App\Notifications;

use App\Enums\NotificationType;

class InterventionClotureeNotification extends PanneNotification
{
    protected function type(): NotificationType
    {
        return NotificationType::InterventionCloturee;
    }

    protected function title(): string
    {
        return 'Intervention clôturée';
    }

    protected function body(): string
    {
        $machine = $this->panne->machine?->nom ?? 'Machine';

        return "La panne sur {$machine} est clôturée. Machine remise en service.";
    }
}
