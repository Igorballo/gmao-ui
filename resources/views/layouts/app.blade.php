@php $theme = request()->cookie('theme', 'light'); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $theme }}" style="background-color: {{ $theme === 'dark' ? '#1d232a' : '#ffffff' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GMAO+' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-base-200 antialiased">
@php
    $user = auth()->user();

    $nav = [
        ['label' => 'Tableau de bord', 'route' => 'dashboard',      'icon' => 'grid',   'can' => null],
        ['label' => 'Machines',        'route' => 'machines.index', 'icon' => 'cube',   'can' => 'machines.consulter'],
        ['label' => 'Pannes',          'route' => 'pannes.index',   'icon' => 'alert',  'can' => 'pannes.consulter'],
        ['label' => 'Rapports',        'route' => 'rapports.index', 'icon' => 'doc',    'can' => 'rapports.consulter'],
        ['label' => 'Utilisateurs',    'route' => 'users.index',    'icon' => 'users',  'can' => 'utilisateurs.gerer'],
        ['label' => 'Rôles',           'route' => 'roles.index',    'icon' => 'shield', 'can' => 'roles.gerer'],
    ];

    // Icônes Lucide (https://lucide.dev) — épurées et cohérentes
    $icons = [
        'grid'   => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        'cube'   => '<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>',
        'alert'  => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
        'doc'    => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/>',
        'users'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
        'help'   => '<circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/>',
    ];

    $titles = [
        'dashboard'      => 'Tableau de bord',
        'machines.index' => 'Machines',
        'pannes.index'   => 'Pannes',
        'pannes.show'    => 'Détail de la panne',
        'rapports.index' => 'Rapports',
        'users.index'    => 'Utilisateurs',
        'roles.index'    => 'Rôles',
    ];
    $pageTitle = $titles[request()->route()?->getName()] ?? 'GMAO+';

    $initiales = collect(explode(' ', $user->name))->map(fn ($m) => mb_substr($m, 0, 1))->take(2)->implode('');
@endphp

<div class="flex min-h-screen">
    {{-- ============ Sidebar ============ --}}
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-base-300 bg-base-100 lg:flex">
        <div class="flex h-16 items-center px-6">
            <a href="{{ route('dashboard') }}" wire:navigate class="text-2xl font-semibold tracking-tight">
                GMAO<span class="text-primary">+</span>
            </a>
        </div>

        <ul class="menu menu-md w-full flex-1 flex-nowrap gap-5 overflow-y-auto px-3 py-6 text-sm">
            @foreach ($nav as $item)
                @continue($item['can'] && ! $user->can($item['can']))
                @php
                    $isActive = $item['route'] && request()->routeIs(str_replace('.index', '.*', $item['route']));
                    $href = $item['route'] ? route($item['route']) : '#';
                @endphp
                <li>
                    <a href="{{ $href }}" @if ($item['route']) wire:navigate @endif class="{{ $isActive ? 'menu-active' : '' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$item['icon']] !!}</svg>
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="border-t border-base-300 p-3">
            <a href="#" class="btn btn-ghost w-full justify-start gap-3 font-medium">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons['help'] !!}</svg>
                Support
            </a>
        </div>
    </aside>

    {{-- ============ Zone principale ============ --}}
    <div class="flex min-w-0 flex-1 flex-col lg:pl-60">
        {{-- Topbar --}}
        <header class="navbar sticky top-0 z-20 min-h-16 border-b border-base-300 bg-base-100 px-4 sm:px-8">
            <div class="navbar-start">
                <h1 class="truncate text-xl font-bold tracking-tight">{{ $pageTitle }}</h1>
            </div>

            <div class="navbar-end gap-2">
                <label class="input input-sm hidden w-64 items-center gap-2 md:flex">
                    <svg class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                    <input type="search" placeholder="Rechercher…" />
                </label>

                {{-- Bascule thème clair / sombre --}}
                <button type="button" class="btn btn-ghost btn-circle"
                        x-data="{ dark: document.documentElement.getAttribute('data-theme') === 'dark' }"
                        @click="dark = !dark;
                                document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
                                document.documentElement.style.backgroundColor = dark ? '#1d232a' : '#ffffff';
                                document.cookie = 'theme=' + (dark ? 'dark' : 'light') + ';path=/;max-age=31536000;samesite=lax'"
                        aria-label="Changer de thème">
                    <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                    <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                </button>

                <button type="button" class="btn btn-ghost btn-circle" aria-label="Notifications">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                </button>

                {{-- Avatar + menu --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost gap-2 px-2">
                        <div class="avatar avatar-placeholder">
                            <div class="w-9 rounded-full bg-primary text-primary-content">
                                <span class="text-xs font-semibold">{{ $initiales }}</span>
                            </div>
                        </div>
                        <span class="hidden text-sm font-medium sm:inline">{{ $user->name }}</span>
                    </div>
                    <ul tabindex="0" class="menu dropdown-content z-40 mt-2 w-52 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
                        <li class="menu-title">{{ $user->email }}</li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-error">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Contenu --}}
        <main class="flex-1 px-4 py-6 sm:px-8">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- ============ Toasts (notifications) ============ --}}
<div class="toast toast-top toast-end z-[60] max-w-[calc(100vw-2rem)]"
     x-data="{
        toasts: [],
        titles: { success: 'Succès', info: 'Information', warning: 'Attention', error: 'Erreur', delete: 'Suppression' },
        add(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message: detail.message, type: detail.type || 'success' });
            setTimeout(() => this.remove(id), detail.timeout || 4500);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
     }"
     @notify.window="add($event.detail)">
    <template x-for="toast in toasts" :key="toast.id">
        <div role="alert"
             class="flex w-80 items-start gap-3 rounded-xl border border-l-4 border-base-300 bg-base-100 p-4 shadow-lg"
             :class="{
                'border-l-success': toast.type === 'success',
                'border-l-info': toast.type === 'info',
                'border-l-warning': toast.type === 'warning',
                'border-l-error': toast.type === 'error' || toast.type === 'delete',
             }"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-6"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                  :class="{
                     'bg-success/15 text-success': toast.type === 'success',
                     'bg-info/15 text-info': toast.type === 'info',
                     'bg-warning/15 text-warning': toast.type === 'warning',
                     'bg-error/15 text-error': toast.type === 'error' || toast.type === 'delete',
                  }">
                <svg x-show="toast.type === 'success'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                <svg x-show="toast.type === 'info'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                <svg x-show="toast.type === 'warning'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <svg x-show="toast.type === 'error'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <svg x-show="toast.type === 'delete'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold" x-text="titles[toast.type] || 'Notification'"></p>
                <p class="text-sm text-base-content/70" x-text="toast.message"></p>
            </div>
            <button type="button" class="text-base-content/40 transition hover:text-base-content" @click="remove(toast.id)" aria-label="Fermer">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
@livewireScripts
</body>
</html>
