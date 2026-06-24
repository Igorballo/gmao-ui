<div>
    @php
        $statutBadge = ['actif' => 'badge-success', 'inactif' => 'badge-ghost', 'arret' => 'badge-error'];
    @endphp

    {{-- Barre d'actions --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row">
            <label class="input w-full max-w-xs">
                <svg class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Rechercher une machine…" />
            </label>
            <select wire:model.live="filtreStatut" class="select sm:w-48">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)
                    <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                @endforeach
            </select>
        </div>

        @can('machines.gerer')
            <button wire:click="create" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Nouvelle machine
            </button>
        @endcan
    </div>

    {{-- Tableau --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Mise en production</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($machines as $machine)
                        <tr class="hover:bg-base-200/50">
                            <td class="text-base-content/50">{{ $machines->firstItem() + $loop->index }}</td>
                            <td class="font-medium">{{ $machine->nom }}</td>
                            <td class="text-base-content/70">{{ $machine->type->libelle() }}</td>
                            <td><span class="badge {{ $statutBadge[$machine->statut->value] }} badge-soft badge-sm">{{ $machine->statut->libelle() }}</span></td>
                            <td class="text-base-content/70">{{ $machine->date_mise_en_production?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-right">
                                @can('machines.gerer')
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="edit({{ $machine->id }})" class="btn btn-ghost btn-square btn-sm" title="Modifier" aria-label="Modifier">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $machine->id }})" class="btn btn-ghost btn-square btn-sm text-error" title="Supprimer" aria-label="Supprimer">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="mx-auto flex max-w-xs flex-col items-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-base-200">
                                        <svg class="h-6 w-6 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                                    </div>
                                    <p class="font-medium">Aucune machine</p>
                                    <p class="mt-1 text-sm text-base-content/60">Commencez par enregistrer votre première machine.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($machines->hasPages())
        <div class="mt-4">{{ $machines->links() }}</div>
    @endif

    {{-- Modal création / édition --}}
    @if ($showModal)
        <div class="modal modal-open">
            <div class="modal-box" wire:keydown.escape.window="$set('showModal', false)">
                <h3 class="mb-4 text-lg font-bold">{{ $editingId ? 'Modifier la machine' : 'Nouvelle machine' }}</h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Nom</label>
                        <input type="text" wire:model="nom" placeholder="Ex. Presse hydraulique #3" class="input w-full @error('nom') input-error @enderror">
                        @error('nom') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="Emplacement, caractéristiques, n° de série…" class="textarea w-full @error('description') textarea-error @enderror"></textarea>
                        @error('description') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Type</label>
                            <select wire:model="type" class="select w-full @error('type') select-error @enderror">
                                <option value="">— Choisir —</option>
                                @foreach ($types as $t)
                                    <option value="{{ $t->value }}">{{ $t->libelle() }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Statut</label>
                            <select wire:model="statut" class="select w-full @error('statut') select-error @enderror">
                                @foreach ($statuts as $s)
                                    <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                                @endforeach
                            </select>
                            @error('statut') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Date de mise en production</label>
                        <input type="date" wire:model="date_mise_en_production" class="input w-full @error('date_mise_en_production') input-error @enderror">
                        @error('date_mise_en_production') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Photo</label>
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="mb-3 h-32 w-full rounded-xl object-cover ring-1 ring-base-300" alt="Aperçu">
                        @elseif ($existingPhoto && ! $removePhoto)
                            <img src="{{ asset('storage/' . $existingPhoto) }}" class="mb-3 h-32 w-full rounded-xl object-cover ring-1 ring-base-300" alt="Photo actuelle">
                        @endif
                        <input type="file" wire:model="photo" accept="image/*" class="file-input file-input-bordered w-full @error('photo') file-input-error @enderror">
                        <div wire:loading wire:target="photo" class="mt-1.5 text-xs text-base-content/60">Chargement de l'image…</div>
                        @error('photo') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                        @if ($photo || ($existingPhoto && ! $removePhoto))
                            <button type="button" wire:click="clearPhoto" class="btn btn-ghost btn-sm mt-2 text-error">Supprimer la photo</button>
                        @endif
                        <p class="mt-1.5 text-xs text-base-content/50">JPG, PNG ou WebP — max. 5 Mo. Visible dans l'app mobile.</p>
                    </div>

                    <div class="modal-action">
                        <button type="button" wire:click="$set('showModal', false)" class="btn btn-ghost">Annuler</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save,photo">
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
                        <h3 class="text-lg font-bold">Supprimer la machine</h3>
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
