<div>
    {{-- Génération d'un rapport --}}
    <div class="mb-6 flex flex-col gap-3 rounded-box border border-base-300 bg-base-100 p-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium">Générer un rapport</p>
            <p class="text-xs text-base-content/60">Choisissez une date pour produire (ou régénérer) le rapport technique journalier en PDF.</p>
        </div>
        <form wire:submit="generer" class="flex items-end gap-2">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-base-content/70">Date</label>
                <input type="date" wire:model="dateGeneration" max="{{ now()->toDateString() }}" class="input input-sm @error('dateGeneration') input-error @enderror">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="generer">
                <span wire:loading wire:target="generer" class="loading loading-spinner loading-sm"></span>
                <svg wire:loading.remove wire:target="generer" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                Générer
            </button>
        </form>
    </div>
    @error('dateGeneration') <p class="-mt-4 mb-4 text-sm text-error">{{ $message }}</p> @enderror

    {{-- Liste des rapports --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Date du rapport</th>
                        <th>Généré le</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rapports as $rapport)
                        <tr class="hover:bg-base-200/50">
                            <td class="text-base-content/50">{{ $rapports->firstItem() + $loop->index }}</td>
                            <td class="font-medium">{{ $rapport->date_rapport->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</td>
                            <td class="text-sm text-base-content/70">{{ $rapport->genere_le?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-right">
                                @if ($rapport->chemin_pdf)
                                    <a href="{{ route('rapports.telecharger', $rapport) }}" class="btn btn-ghost btn-sm gap-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Télécharger
                                    </a>
                                @else
                                    <span class="text-sm text-base-content/40">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-16 text-center">
                                <div class="mx-auto flex max-w-xs flex-col items-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-base-200">
                                        <svg class="h-6 w-6 text-base-content/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </div>
                                    <p class="font-medium">Aucun rapport</p>
                                    <p class="mt-1 text-sm text-base-content/60">Générez le rapport d'une journée pour commencer.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($rapports->hasPages())
        <div class="mt-4">{{ $rapports->links() }}</div>
    @endif
</div>
