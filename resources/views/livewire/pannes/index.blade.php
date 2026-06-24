<div>
    @php
        $statutBadge = [
            'en_attente' => 'badge-warning',
            'assignee'   => 'badge-info',
            'en_cours'   => 'badge-primary',
            'cloturee'   => 'badge-success',
        ];
    @endphp

    {{-- Barre d'actions --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row">
            <label class="input w-full max-w-xs">
                <svg class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.34-4.34M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Rechercher (machine, description)…" />
            </label>
            <select wire:model.live="filtreStatut" class="select sm:w-48">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)
                    <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                @endforeach
            </select>
        </div>

        @can('pannes.creer')
            <button wire:click="create" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Signaler une panne
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
                        <th>Machine</th>
                        <th>Date</th>
                        <th>Intervenants</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pannes as $panne)
                        <tr class="hover:bg-base-200/50">
                            <td class="text-base-content/50">{{ $pannes->firstItem() + $loop->index }}</td>
                            <td>
                                <p class="font-medium">{{ $panne->machine->nom }}</p>
                                <p class="max-w-xs truncate text-sm text-base-content/60">{{ $panne->description }}</p>
                            </td>
                            <td class="whitespace-nowrap text-sm text-base-content/70">{{ $panne->date_panne->format('d/m/Y H:i') }}</td>
                            <td class="text-sm">
                                @if ($panne->responsable)
                                    <span class="font-medium">{{ $panne->responsable->name }}</span>
                                    <span class="badge badge-soft badge-primary badge-xs ml-1">Resp.</span>
                                    @if ($panne->intervenants->count() > 1)
                                        <span class="text-base-content/50"> +{{ $panne->intervenants->count() - 1 }}</span>
                                    @endif
                                @else
                                    <span class="text-base-content/50">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-sm text-base-content/70">{{ $panne->deadline?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td><span class="badge {{ $statutBadge[$panne->statut->value] }} badge-soft badge-sm">{{ $panne->statut->libelle() }}</span></td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('pannes.show', $panne) }}" wire:navigate class="btn btn-ghost btn-square btn-sm" title="Détail" aria-label="Détail">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                    </a>
                                    @can('pannes.creer')
                                        @if ($panne->statut->value !== 'cloturee')
                                            <button wire:click="edit({{ $panne->id }})" class="btn btn-ghost btn-square btn-sm" title="Modifier" aria-label="Modifier">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                            </button>
                                        @endif
                                    @endcan
                                    @can('pannes.deleguer')
                                        @if ($panne->statut->value !== 'cloturee')
                                            <button wire:click="openDelegate({{ $panne->id }})" class="btn btn-ghost btn-square btn-sm" title="Déléguer" aria-label="Déléguer">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="mx-auto flex max-w-xs flex-col items-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-base-200">
                                        <svg class="h-6 w-6 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                    </div>
                                    <p class="font-medium">Aucune panne</p>
                                    <p class="mt-1 text-sm text-base-content/60">Aucune panne ne correspond à votre recherche.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($pannes->hasPages())
        <div class="mt-4">{{ $pannes->links() }}</div>
    @endif

    {{-- Modal : signaler une panne --}}
    @if ($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box" wire:keydown.escape.window="$set('showCreateModal', false)">
                <h3 class="mb-4 text-lg font-bold">{{ $editingId ? 'Modifier la panne' : 'Signaler une panne' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Machine concernée</label>
                        <select wire:model="machine_id" class="select w-full @error('machine_id') select-error @enderror">
                            <option value="">— Choisir une machine —</option>
                            @foreach ($machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->nom }}</option>
                            @endforeach
                        </select>
                        @error('machine_id') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Date et heure de la panne</label>
                        <input type="datetime-local" wire:model="date_panne" class="input w-full @error('date_panne') input-error @enderror">
                        @error('date_panne') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Description de la panne</label>
                        <textarea wire:model="description" rows="4" placeholder="Décrivez le symptôme, le contexte…" class="textarea w-full @error('description') textarea-error @enderror"></textarea>
                        @error('description') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="modal-action">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="btn btn-ghost">Annuler</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                            {{ $editingId ? 'Enregistrer' : 'Signaler' }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showCreateModal', false)"></div>
        </div>
    @endif

    {{-- Modal : déléguer une panne --}}
    @if ($showDelegateModal)
        <div class="modal modal-open">
            <div class="modal-box overflow-visible" wire:keydown.escape.window="$set('showDelegateModal', false)">
                <h3 class="mb-4 text-lg font-bold">Déléguer la panne</h3>
                <form wire:submit="saveDelegate" class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Intervenants</label>
                        <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false">
                            {{-- Champ (pastilles des sélectionnés) --}}
                            <div @click="open = !open"
                                 class="input flex h-auto min-h-12 w-full cursor-pointer flex-wrap items-center gap-1.5 py-2 @error('intervenants') input-error @enderror">
                                @forelse ($utilisateurs->whereIn('id', $intervenants) as $sel)
                                    <span class="badge badge-primary badge-sm gap-1">
                                        {{ $sel->name }}
                                        <button type="button" wire:click="removeIntervenant('{{ $sel->id }}')" @click.stop class="transition hover:opacity-70" aria-label="Retirer">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                @empty
                                    <span class="text-sm text-base-content/40">Sélectionner des intervenants…</span>
                                @endforelse
                                <svg class="ml-auto h-4 w-4 shrink-0 text-base-content/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>

                            {{-- Panneau déroulant --}}
                            <div x-show="open" x-transition x-cloak
                                 class="absolute left-0 right-0 z-30 mt-1 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
                                <input type="text" x-model="search" @click.stop placeholder="Rechercher une personne…" class="input input-sm mb-2 w-full">
                                <div class="max-h-48 overflow-y-auto">
                                    @forelse ($utilisateurs as $u)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 transition hover:bg-base-200"
                                               x-show="!search || @js(\Illuminate\Support\Str::lower($u->name)).includes(search.toLowerCase())">
                                            <input type="checkbox" wire:model.live="intervenants" value="{{ $u->id }}" class="checkbox checkbox-sm checkbox-primary" />
                                            <span class="text-sm">{{ $u->name }}</span>
                                        </label>
                                    @empty
                                        <p class="px-2 py-1 text-sm text-base-content/50">Aucun utilisateur actif.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @error('intervenants') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    @if (count($intervenants) > 1)
                        <div>
                            <label class="mb-1.5 block text-sm font-medium">Responsable</label>
                            <select wire:model="responsable_id" class="select w-full @error('responsable_id') select-error @enderror">
                                <option value="">— Choisir le responsable —</option>
                                @foreach ($utilisateurs->whereIn('id', $intervenants) as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('responsable_id') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-base-content/50">Désigné parmi les intervenants sélectionnés.</p>
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Échéance (deadline)</label>
                        <input type="datetime-local" wire:model="deadline" class="input w-full @error('deadline') input-error @enderror">
                        @error('deadline') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="modal-action">
                        <button type="button" wire:click="$set('showDelegateModal', false)" class="btn btn-ghost">Annuler</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveDelegate">
                            <span wire:loading wire:target="saveDelegate" class="loading loading-spinner loading-sm"></span>
                            Déléguer
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showDelegateModal', false)"></div>
        </div>
    @endif
</div>
