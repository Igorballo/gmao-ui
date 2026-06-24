<?php

namespace App\Services;

use App\Models\Panne;
use App\Models\User;
use App\Notifications\InterventionClotureeNotification;
use App\Notifications\InterventionDemarreeNotification;
use App\Notifications\PanneAssigneeNotification;
use App\Notifications\PanneSignaleeNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class PanneNotifier
{
    public function panneSignalee(Panne $panne, User $actor): void
    {
        $panne->loadMissing('machine');

        $recipients = User::permission('pannes.deleguer')
            ->where('actif', true)
            ->whereKeyNot($actor->id)
            ->get();

        $this->send($recipients, new PanneSignaleeNotification($panne));
    }

    public function panneAssignee(Panne $panne, User $actor): void
    {
        $panne->loadMissing(['machine', 'intervenants']);

        $ids = $panne->intervenants->pluck('id');
        if ($panne->responsable_id) {
            $ids->push($panne->responsable_id);
        }

        $recipients = User::query()
            ->whereIn('id', $ids->unique())
            ->where('actif', true)
            ->whereKeyNot($actor->id)
            ->get();

        $this->send($recipients, new PanneAssigneeNotification($panne));
    }

    public function interventionDemarree(Panne $panne, User $actor): void
    {
        $panne->loadMissing(['machine', 'declarePar', 'delegueePar']);

        $ids = collect([$panne->declaree_par_id, $panne->deleguee_par_id])
            ->filter()
            ->unique();

        $recipients = User::query()
            ->whereIn('id', $ids)
            ->where('actif', true)
            ->whereKeyNot($actor->id)
            ->get();

        $this->send($recipients, new InterventionDemarreeNotification($panne));
    }

    public function interventionCloturee(Panne $panne, User $actor): void
    {
        $panne->loadMissing(['machine', 'declarePar', 'delegueePar']);

        $ids = collect([$panne->declaree_par_id, $panne->deleguee_par_id])
            ->filter()
            ->unique();

        $recipients = User::query()
            ->whereIn('id', $ids)
            ->where('actif', true)
            ->whereKeyNot($actor->id)
            ->get();

        $this->send($recipients, new InterventionClotureeNotification($panne));
    }

  /** @param Collection<int, User> $recipients */
    protected function send(Collection $recipients, object $notification): void
    {
        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, $notification);
    }
}
