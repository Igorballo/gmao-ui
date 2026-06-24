<div>
    {{-- Barre d'actions --}}
    <div class="mb-6 flex justify-end">
        <button wire:click="create" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nouveau rôle
        </button>
    </div>

    {{-- Tableau --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Rôle</th>
                        <th>Permissions</th>
                        <th>Utilisateurs</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr class="hover:bg-base-200/50">
                            <td class="text-base-content/50">{{ $loop->iteration }}</td>
                            <td class="font-medium">{{ $role->name }}</td>
                            <td class="text-base-content/70">{{ $role->permissions_count }}</td>
                            <td class="text-base-content/70">{{ $role->users_count }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="edit({{ $role->id }})" class="btn btn-ghost btn-square btn-sm" title="Modifier" aria-label="Modifier">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $role->id }})" class="btn btn-ghost btn-square btn-sm text-error" title="Supprimer" aria-label="Supprimer">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="mx-auto flex max-w-xs flex-col items-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-base-200">
                                        <svg class="h-6 w-6 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                    </div>
                                    <p class="font-medium">Aucun rôle</p>
                                    <p class="mt-1 text-sm text-base-content/60">Créez un rôle et attribuez-lui des permissions.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal création / édition --}}
    @if ($showModal)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-3xl" wire:keydown.escape.window="$set('showModal', false)">
                <h3 class="text-lg font-bold">{{ $editingId ? 'Modifier le rôle' : 'Nouveau rôle' }}</h3>
                <p class="mb-5 text-sm text-base-content/60">Nommez le rôle et cochez les permissions à lui attribuer.</p>

                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nom du rôle</label>
                        <input type="text" wire:model="name" placeholder="Ex. Responsable Technique" class="input w-full @error('name') input-error @enderror">
                        @error('name') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Permissions</label>
                        <div class="divide-y divide-base-300 overflow-hidden rounded-box border border-base-300">
                            @foreach ($groupes as $groupe => $permissions)
                                <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
                                    <div class="flex w-36 shrink-0 items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-primary/70"></span>
                                        <span class="font-medium capitalize">{{ $groupe }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-5 gap-y-2">
                                        @foreach ($permissions as $permission)
                                            <label class="flex cursor-pointer items-center gap-2 text-sm">
                                                <input type="checkbox" class="checkbox checkbox-sm checkbox-primary" wire:model="selectedPermissions" value="{{ $permission->name }}" />
                                                <span class="capitalize">{{ \Illuminate\Support\Str::of($permission->name)->after('.')->replace('_', ' ') }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
                        <h3 class="text-lg font-bold">Supprimer le rôle</h3>
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
