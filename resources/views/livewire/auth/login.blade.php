<div class="flex min-h-screen">
    {{-- ============ Colonne gauche : formulaire ============ --}}
    <div class="flex w-full items-center justify-center px-6 py-10 sm:px-12 lg:w-1/2">
        <div class="w-full max-w-md">
            {{-- Logo --}}
            <div class="mb-10">
                <span class="text-2xl font-semibold tracking-tight">GMAO<span class="text-primary">+</span></span>
            </div>

            <h1 class="text-4xl font-semibold tracking-tight">Bon retour&nbsp;!</h1>
            <p class="mt-3 text-base-content/60">Heureux de vous revoir. Connectez-vous pour accéder à votre espace.</p>

            {{-- Connexions externes --}}
            <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button type="button" class="btn btn-outline">
                    <svg class="h-5 w-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38Z"/></svg>
                    Google
                </button>
                <button type="button" class="btn btn-outline">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M16.37 12.78c.03 3.27 2.87 4.36 2.9 4.37-.02.08-.45 1.56-1.5 3.08-.9 1.31-1.84 2.61-3.32 2.64-1.45.03-1.92-.86-3.58-.86-1.66 0-2.18.83-3.55.89-1.43.05-2.51-1.42-3.42-2.72C2.5 19.55 1.1 14.7 3 11.42c.94-1.62 2.62-2.65 4.45-2.68 1.4-.03 2.71.94 3.58.94.86 0 2.46-1.16 4.15-.99.71.03 2.7.29 3.97 2.16-.1.06-2.38 1.39-2.35 4.14M13.6 6.86c.77-.93 1.28-2.22 1.14-3.51-1.1.05-2.44.74-3.23 1.66-.71.82-1.33 2.13-1.16 3.39 1.23.1 2.48-.62 3.25-1.54"/></svg>
                    Apple
                </button>
            </div>

            <div class="divider text-base-content/40">ou</div>

            {{-- Formulaire --}}
            <form wire:submit="login" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium" for="email">E-mail</label>
                    <input type="email" id="email" wire:model="email" autofocus autocomplete="username"
                           placeholder="Entrez votre e-mail"
                           class="input w-full @error('email') input-error @enderror">
                    @error('email') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium" for="password">Mot de passe</label>
                    <input type="password" id="password" wire:model="password" autocomplete="current-password"
                           placeholder="Entrez votre mot de passe"
                           class="input w-full @error('password') input-error @enderror">
                    @error('password') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="label cursor-pointer gap-2">
                        <input type="checkbox" wire:model="remember" class="checkbox checkbox-sm" />
                        <span class="label-text">Se souvenir de moi</span>
                    </label>
                    <span class="link link-hover text-sm text-base-content/60">Mot de passe oublié&nbsp;?</span>
                </div>

                <button type="submit" class="btn btn-neutral w-full" wire:loading.attr="disabled" wire:target="login">
                    <span wire:loading.remove wire:target="login">Connexion</span>
                    <span wire:loading wire:target="login" class="loading loading-spinner loading-sm"></span>
                    <span wire:loading wire:target="login">Connexion…</span>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-base-content/60">
                Besoin d'un accès&nbsp;? <span class="font-semibold text-base-content">Contactez votre administrateur</span>
            </p>
        </div>
    </div>

    {{-- ============ Colonne droite : panneau décoratif ============ --}}
    <div class="hidden p-4 lg:block lg:w-1/2">
        <div class="relative flex h-full flex-col justify-center overflow-hidden rounded-3xl p-10"
             style="background: radial-gradient(120% 120% at 70% 20%, #ffd8b0 0%, #ffb27a 45%, #ff8a4c 100%);">
            <p class="absolute left-10 right-10 top-8 text-2xl font-bold text-white">
                La maintenance, enfin sous contrôle.
            </p>

            <div class="ml-auto mb-6 w-full max-w-sm rounded-2xl bg-white/25 p-4 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/70 text-sm font-semibold text-orange-700">SJ</div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Igor BALLO</p>
                        <p class="text-xs text-slate-600">Directrice Technique</p>
                    </div>
                </div>
            </div>

            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 ring-slate-200">
                        <svg class="h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14v4M12 9v9M17 5v13"/></svg>
                    </div>
                    <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">Le mois dernier</span>
                </div>
                <div class="mb-6 flex items-center gap-3">
                    <span class="text-4xl font-bold tracking-tight text-slate-900">+84,32%</span>
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-100 text-green-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M17 7H9m8 0v8"/></svg>
                    </span>
                </div>
                <div class="flex items-end gap-4">
                    <div class="flex h-44 flex-col justify-between py-1 text-xs text-slate-400">
                        @foreach ([100, 80, 60, 40, 20, 0] as $tick)<span>{{ $tick }}</span>@endforeach
                    </div>
                    <div class="flex h-44 flex-1 items-end gap-3">
                        @foreach ([22 => 'bg-orange-200', 45 => 'bg-orange-300', 68 => 'bg-orange-400', 88 => 'bg-orange-500'] as $h => $couleur)
                            <div class="flex flex-1 items-end overflow-hidden rounded-lg bg-slate-100" style="height:100%">
                                <div class="w-full rounded-lg {{ $couleur }}" style="height: {{ $h }}%"></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
