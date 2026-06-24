<div class="space-y-6">
    @php
        $statutBadge = [
            'en_attente' => 'badge-warning',
            'assignee'   => 'badge-info',
            'en_cours'   => 'badge-primary',
            'cloturee'   => 'badge-success',
        ];
        $intervention = $panne->intervention;
    @endphp

    {{-- Retour --}}
    <a href="{{ route('pannes.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-base-content">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
        Retour aux pannes
    </a>

    {{-- Détail de la panne --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold">{{ $panne->machine->nom }}</h2>
                    <p class="text-sm text-base-content/60">Panne déclarée le {{ $panne->date_panne->format('d/m/Y à H:i') }}</p>
                </div>
                <span class="badge {{ $statutBadge[$panne->statut->value] }} badge-soft">{{ $panne->statut->libelle() }}</span>
            </div>

            <p class="mt-4 whitespace-pre-line text-base-content/80">{{ $panne->description }}</p>

            <div class="mt-4 grid grid-cols-1 gap-4 border-t border-base-300 pt-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-base-content/50">Déclarée par</p>
                    <p class="font-medium">{{ $panne->declarePar?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-base-content/50">Responsable</p>
                    <p class="font-medium">{{ $panne->responsable?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-base-content/50">Déléguée par</p>
                    <p class="font-medium">{{ $panne->delegueePar?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-base-content/50">Échéance</p>
                    <p class="font-medium">{{ $panne->deadline?->format('d/m/Y H:i') ?? '—' }}</p>
                </div>
            </div>

            @if ($panne->intervenants->isNotEmpty())
                <div class="mt-4 border-t border-base-300 pt-4">
                    <p class="mb-2 text-sm text-base-content/50">Intervenants</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($panne->intervenants as $intervenant)
                            <span class="badge badge-soft {{ $intervenant->id === $panne->responsable_id ? 'badge-primary' : 'badge-ghost' }} gap-1">
                                {{ $intervenant->name }}
                                @if ($intervenant->id === $panne->responsable_id)
                                    <span class="text-[10px] font-semibold uppercase">· resp.</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Intervention --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h3 class="card-title">Intervention</h3>

            {{-- En attente de délégation --}}
            @if ($panne->statut->value === 'en_attente')
                <div class="rounded-box bg-base-200/60 p-4 text-sm text-base-content/70">
                    Cette panne est <strong>en attente</strong> de délégation à un maintenancier.
                </div>

            {{-- Assignée : à démarrer --}}
            @elseif ($panne->statut->value === 'assignee' && ! $intervention)
                <div class="flex flex-col items-start gap-3 rounded-box bg-base-200/60 p-4">
                    <p class="text-sm text-base-content/70">La panne est assignée — responsable : <strong>{{ $panne->responsable?->name ?? '—' }}</strong>. L'intervention n'a pas encore démarré.</p>
                    @if ($peutIntervenir)
                        <button wire:click="demarrer" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="demarrer">
                            <span wire:loading wire:target="demarrer" class="loading loading-spinner loading-sm"></span>
                            Démarrer l'intervention
                        </button>
                    @endif
                </div>

            {{-- En cours ou clôturée : détails --}}
            @elseif ($intervention)
                @php $cloturee = $panne->statut->value === 'cloturee'; @endphp

                <div class="mb-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-3">
                    <div><p class="text-base-content/50">Maintenancier</p><p class="font-medium">{{ $intervention->maintenancier?->name ?? '—' }}</p></div>
                    <div><p class="text-base-content/50">Démarrée le</p><p class="font-medium">{{ $intervention->demarree_le?->format('d/m/Y H:i') ?? '—' }}</p></div>
                    <div><p class="text-base-content/50">Terminée le</p><p class="font-medium">{{ $intervention->terminee_le?->format('d/m/Y H:i') ?? '—' }}</p></div>
                </div>

                @if ($peutIntervenir)
                    @unless ($cloturee)
                        <form wire:submit="enregistrer" class="space-y-5">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium">Cause identifiée</label>
                                <textarea wire:model="cause" rows="2" placeholder="Cause de la panne…" class="textarea w-full @error('cause') textarea-error @enderror"></textarea>
                                @error('cause') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium">Opérations effectuées</label>
                                <textarea wire:model="operations" rows="3" placeholder="Décrivez les opérations réalisées…" class="textarea w-full @error('operations') textarea-error @enderror"></textarea>
                                @error('operations') <p class="mt-1.5 text-sm text-error">{{ $message }}</p> @enderror
                            </div>

                            {{-- Pièces de rechange --}}
                            <div>
                                <div class="mb-2 flex items-center justify-between">
                                    <label class="text-sm font-medium">Pièces de rechange utilisées</label>
                                    <button type="button" wire:click="ajouterPiece" class="btn btn-ghost btn-xs gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        Ajouter
                                    </button>
                                </div>
                                @forelse ($pieces as $i => $piece)
                                    <div class="mb-2 flex items-start gap-2">
                                        <div class="flex-1">
                                            <input type="text" wire:model="pieces.{{ $i }}.reference" placeholder="Référence de la pièce" class="input input-sm w-full @error('pieces.'.$i.'.reference') input-error @enderror">
                                            @error('pieces.'.$i.'.reference') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="w-24">
                                            <input type="number" min="1" wire:model="pieces.{{ $i }}.quantite" placeholder="Qté" class="input input-sm w-full @error('pieces.'.$i.'.quantite') input-error @enderror">
                                            @error('pieces.'.$i.'.quantite') <p class="mt-1 text-xs text-error">{{ $message }}</p> @enderror
                                        </div>
                                        <button type="button" wire:click="retirerPiece({{ $i }})" class="btn btn-ghost btn-square btn-sm text-error" title="Retirer">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-sm text-base-content/50">Aucune pièce ajoutée.</p>
                                @endforelse
                            </div>

                            {{-- Photos --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium">Photos « Avant »</label>
                                    <input type="file" wire:model="photosAvant" multiple accept="image/*" class="file-input file-input-sm w-full">
                                    <div wire:loading wire:target="photosAvant" class="mt-1 text-xs text-base-content/60">Chargement…</div>
                                    @error('photosAvant.*') <p class="mt-1 text-sm text-error">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium">Photos « Après »</label>
                                    <input type="file" wire:model="photosApres" multiple accept="image/*" class="file-input file-input-sm w-full">
                                    <div wire:loading wire:target="photosApres" class="mt-1 text-xs text-base-content/60">Chargement…</div>
                                    @error('photosApres.*') <p class="mt-1 text-sm text-error">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            @include('livewire.pannes._galerie', ['intervention' => $intervention])

                            <div class="flex flex-wrap justify-end gap-3 border-t border-base-300 pt-4">
                                <button type="submit" class="btn btn-ghost" wire:loading.attr="disabled" wire:target="enregistrer">
                                    <span wire:loading wire:target="enregistrer" class="loading loading-spinner loading-sm"></span>
                                    Enregistrer
                                </button>
                                <button type="button" wire:click="$set('confirmingCloture', true)" class="btn btn-success text-white">
                                    Clôturer l'intervention
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Lecture seule (clôturée) --}}
                        <div class="space-y-4">
                            <div><p class="text-sm text-base-content/50">Cause identifiée</p><p class="whitespace-pre-line">{{ $intervention->cause ?? '—' }}</p></div>
                            <div><p class="text-sm text-base-content/50">Opérations effectuées</p><p class="whitespace-pre-line">{{ $intervention->operations ?? '—' }}</p></div>
                            <div>
                                <p class="mb-1 text-sm text-base-content/50">Pièces utilisées</p>
                                @forelse ($intervention->pieces as $piece)
                                    <span class="badge badge-ghost badge-sm mr-1">{{ $piece->reference }} × {{ $piece->quantite }}</span>
                                @empty
                                    <span class="text-base-content/50">—</span>
                                @endforelse
                            </div>
                            @include('livewire.pannes._galerie', ['intervention' => $intervention])
                        </div>
                    @endunless
                @else
                    <p class="text-sm text-base-content/60">Vous ne faites pas partie des intervenants de cette panne.</p>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal de confirmation de clôture --}}
    @if ($confirmingCloture)
        <div class="modal modal-open">
            <div class="modal-box" wire:keydown.escape.window="$set('confirmingCloture', false)">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-success/15 text-success">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold">Clôturer l'intervention</h3>
                        <p class="mt-1 text-sm text-base-content/70">Confirmez-vous la clôture ? La machine sera <strong>remise en service</strong> et la panne marquée « Clôturée ».</p>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" wire:click="$set('confirmingCloture', false)" class="btn btn-ghost">Annuler</button>
                    <button type="button" wire:click="cloturer" class="btn btn-success text-white" wire:loading.attr="disabled" wire:target="cloturer">
                        <span wire:loading wire:target="cloturer" class="loading loading-spinner loading-sm"></span>
                        Clôturer
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('confirmingCloture', false)"></div>
        </div>
    @endif
</div>
