@php
    $avant = $intervention->photos->filter(fn ($p) => $p->type->value === 'avant');
    $apres = $intervention->photos->filter(fn ($p) => $p->type->value === 'apres');
@endphp

@if ($intervention->photos->isNotEmpty())
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/50">Photos — Avant</p>
            <div class="flex flex-wrap gap-2">
                @forelse ($avant as $photo)
                    <a href="{{ asset('storage/' . $photo->chemin) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/' . $photo->chemin) }}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-base-300 transition hover:opacity-80" alt="Photo avant">
                    </a>
                @empty
                    <span class="text-sm text-base-content/40">—</span>
                @endforelse
            </div>
        </div>
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/50">Photos — Après</p>
            <div class="flex flex-wrap gap-2">
                @forelse ($apres as $photo)
                    <a href="{{ asset('storage/' . $photo->chemin) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/' . $photo->chemin) }}" class="h-20 w-20 rounded-lg object-cover ring-1 ring-base-300 transition hover:opacity-80" alt="Photo après">
                    </a>
                @empty
                    <span class="text-sm text-base-content/40">—</span>
                @endforelse
            </div>
        </div>
    </div>
@endif
