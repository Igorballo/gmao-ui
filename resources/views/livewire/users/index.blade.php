<div>
    {{-- Barre d'actions --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <label class="input w-full max-w-sm">
            <svg class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Rechercher par nom ou e-mail…" />
        </label>

        <button wire:click="create" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nouvel utilisateur
        </button>
    </div>

    {{-- Tableau --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Utilisateur</th>
                        <th>Téléphone</th>
                        <th>Rôles</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="hover:bg-base-200/50">
                            <td class="text-base-content/50">{{ $users->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="w-9 rounded-full bg-primary text-primary-content">
                                            <span class="text-xs font-semibold">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn ($m) => mb_substr($m, 0, 1))->take(2)->implode('') }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $user->name }}</p>
                                        <p class="truncate text-sm text-base-content/60">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-base-content/70">{{ $user->telephone ?? '—' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($user->roles as $role)
                                        <span class="badge badge-ghost badge-sm">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-base-content/40">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td><span class="badge {{ $user->actif ? 'badge-success' : 'badge-error' }} badge-soft badge-sm">{{ $user->actif ? 'Actif' : 'Désactivé' }}</span></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="edit({{ $user->id }})" class="btn btn-ghost btn-square btn-sm" title="Modifier" aria-label="Modifier">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                    </button>
                                    <button wire:click="toggleActif({{ $user->id }})" class="btn btn-ghost btn-square btn-sm {{ $user->actif ? 'text-warning' : '' }}" title="{{ $user->actif ? 'Désactiver' : 'Activer' }}" aria-label="{{ $user->actif ? 'Désactiver' : 'Activer' }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/></svg>
                                    </button>
                                    @if ($user->id !== auth()->id())
                                        <button wire:click="confirmDelete({{ $user->id }})" class="btn btn-ghost btn-square btn-sm text-error" title="Supprimer" aria-label="Supprimer">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="mx-auto flex max-w-xs flex-col items-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-base-200">
                                        <svg class="h-6 w-6 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                                    </div>
                                    <p class="font-medium">Aucun utilisateur trouvé</p>
                                    <p class="mt-1 text-sm text-base-content/60">Ajustez votre recherche ou créez un utilisateur.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif

    {{-- Modal création / édition --}}
    @if ($showModal)
        <div class="modal modal-open">
            <div class="modal-box" wire:keydown.escape.window="$set('showModal', false)">
                <h3 class="mb-4 text-lg font-bold">{{ $editingId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nom</label>
                        <input type="text" wire:model="name" placeholder="Ex. Awa Diallo" class="input w-full @error('name') input-error @enderror">
                        @error('name') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">E-mail</label>
                            <input type="email" wire:model="email" placeholder="nom@entreprise.com" class="input w-full @error('email') input-error @enderror">
                            @error('email') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Téléphone</label>
                            <input type="text" wire:model="telephone" placeholder="+225 07 00 00 00 00" class="input w-full @error('telephone') input-error @enderror">
                            @error('telephone') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Rôle</label>
                        <select wire:model="role" class="select w-full @error('role') select-error @enderror" @disabled($roles->isEmpty())>
                            <option value="">— Choisir un rôle —</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                        @if ($roles->isEmpty())
                            <p class="mt-1.5 text-sm text-warning">Aucun rôle défini. Créez-en d'abord dans la section Rôles.</p>
                        @endif
                    </div>

                    @unless ($editingId)
                        <div class="rounded-lg bg-base-200/60 p-3 text-sm text-base-content/70">
                            <span class="font-medium text-base-content">Mot de passe :</span> il sera généré automatiquement et envoyé à l'utilisateur par e-mail.
                        </div>
                    @endunless

                    <div x-data="{ actif: @entangle('actif') }">
                        <label class="label w-fit cursor-pointer justify-start gap-2">
                            <input type="checkbox" x-model="actif" class="toggle toggle-sm toggle-primary" />
                            <span class="label-text font-medium" x-text="actif ? 'Compte actif' : 'Compte inactif'"></span>
                        </label>
                    </div>

                    <div class="modal-action">
                        <button type="button" wire:click="$set('showModal', false)" class="btn btn-ghost">Annuler</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showModal', false)"></div>
        </div>
    @endif

    {{-- Modal de confirmation de suppression --}}
    @if ($confirmingDeleteId)
        <div class="modal modal-open">
            <div class="modal-box" wire:keydown.escape.window="$set('confirmingDeleteId', null)">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-error/15 text-error">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold">Supprimer l'utilisateur</h3>
                        <p class="mt-1 text-sm text-base-content/70">Voulez-vous vraiment supprimer « {{ $confirmingDeleteNom }} » ? Cette action est irréversible.</p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" wire:click="$set('confirmingDeleteId', null)" class="btn btn-ghost">Annuler</button>
                    <button type="button" wire:click="delete" class="btn btn-error" wire:loading.attr="disabled" wire:target="delete">
                        <span wire:loading wire:target="delete" class="loading loading-spinner loading-sm"></span>
                        Supprimer
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('confirmingDeleteId', null)"></div>
        </div>
    @endif
</div>
