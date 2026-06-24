<?php

namespace App\Notifications;

use App\Enums\NotificationType;

class PanneSignaleeNotification extends PanneNotification
{
    protected function type(): NotificationType
    {
        return NotificationType::PanneSignalee;
    }

    protected function title(): string
    {
        return 'Nouvelle panne signalée';
    }

    protected function body(): string
    {
        $machine = $this->panne->machine?->nom ?? 'Machine';

        return "{$machine} — en attente de délégation.";
    }
}
