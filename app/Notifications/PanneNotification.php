<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Panne;
use Illuminate\Notifications\Notification;

abstract class PanneNotification extends Notification
{
    public function __construct(protected Panne $panne) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->push_enabled) {
            $channels[] = \App\Channels\ExpoPushChannel::class;
        }

        return $channels;
    }

    abstract protected function type(): NotificationType;

    abstract protected function title(): string;

    abstract protected function body(): string;

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    /** @return array<string, mixed> */
    public function toExpoPush(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'data' => $this->payload(),
        ];
    }

    /** @return array<string, mixed> */
    protected function payload(): array
    {
        $this->panne->loadMissing('machine');

        return [
            'type' => $this->type()->value,
            'title' => $this->title(),
            'body' => $this->body(),
            'panne_id' => $this->panne->id,
            'machine' => $this->panne->machine?->nom,
        ];
    }
}
