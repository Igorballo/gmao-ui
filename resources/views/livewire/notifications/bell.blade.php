<div wire:poll.45s="refresh" class="dropdown dropdown-end notif-bell-dropdown">
    <button
        type="button"
        tabindex="0"
        class="notif-bell-trigger btn btn-ghost btn-circle"
        aria-label="Notifications"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
        </svg>
        @if ($unreadCount > 0)
            <span class="notif-bell-badge" aria-hidden="true">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div tabindex="0" class="dropdown-content z-50 mt-2 w-80 rounded-box border border-base-300 bg-base-100 p-0 shadow-xl sm:w-96">
        <div class="flex items-center justify-between gap-2 border-b border-base-300 px-4 py-3">
            <p class="text-sm font-bold">Notifications</p>
            @if ($unreadCount > 0)
                <button type="button" class="btn btn-ghost btn-xs text-primary" wire:click="markAllRead">
                    Tout marquer lu
                </button>
            @endif
        </div>

        <ul class="max-h-80 overflow-y-auto py-1">
            @forelse ($items as $notification)
                @php
                    $data = $notification->data;
                    $unread = $notification->read_at === null;
                    $panneId = $data['panne_id'] ?? null;
                @endphp
                <li>
                    <button
                        type="button"
                        class="flex w-full gap-3 px-4 py-3 text-left transition hover:bg-base-200 {{ $unread ? 'bg-primary/5' : '' }}"
                        wire:click="open('{{ $notification->id }}')"
                    >
                        <span class="mt-1.5 flex h-2 w-2 shrink-0 rounded-full {{ $unread ? 'bg-primary' : 'bg-transparent' }}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold leading-snug">{{ $data['title'] ?? 'Notification' }}</span>
                            <span class="mt-0.5 block text-xs leading-relaxed text-base-content/70">{{ $data['body'] ?? '' }}</span>
                            <span class="mt-1 block text-[11px] text-base-content/50">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        @if ($panneId && auth()->user()->can('pannes.consulter'))
                            <svg class="mt-1 h-4 w-4 shrink-0 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                            </svg>
                        @endif
                    </button>
                </li>
            @empty
                <li class="px-4 py-8 text-center">
                    <p class="text-sm font-medium text-base-content/70">Aucune notification</p>
                    <p class="mt-1 text-xs text-base-content/50">Les alertes liées aux pannes apparaîtront ici.</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
