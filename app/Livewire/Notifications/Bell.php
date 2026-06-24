<?php

namespace App\Livewire\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Livewire\Component;

class Bell extends Component
{
    public int $unreadCount = 0;

    /** @var Collection<int, DatabaseNotification> */
    public Collection $items;

    public function mount(): void
    {
        $this->items = collect();
        $this->refresh();
    }

    public function refresh(): void
    {
        $user = auth()->user();

        $this->unreadCount = $user->unreadNotifications()->count();
        $this->items = $user->notifications()->latest()->limit(15)->get();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->refresh();
    }

    public function open(string $id): void
    {
        /** @var DatabaseNotification $notification */
        $notification = auth()->user()->notifications()->whereKey($id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $this->refresh();

        $panneId = $notification->data['panne_id'] ?? null;

        if ($panneId && auth()->user()->can('pannes.consulter')) {
            $this->redirect(route('pannes.show', $panneId), navigate: true);

            return;
        }

        $this->dispatch('notify', message: 'Notification marquée comme lue.', type: 'info');
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}
