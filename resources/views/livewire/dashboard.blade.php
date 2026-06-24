<div class="space-y-6">
    @php
        $statutBadge = ['en_attente' => 'badge-warning', 'assignee' => 'badge-info', 'en_cours' => 'badge-primary', 'cloturee' => 'badge-success'];
        $h = intdiv($tempsArretMin, 60);
        $m = $tempsArretMin % 60;
        $tempsArretLib = ($h > 0 ? $h.' h ' : '').$m.' min';
    @endphp

    {{-- Cartes KPI --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Pannes en cours --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body gap-0 p-5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63"/></svg>
                </div>
                <p class="mt-4 text-sm text-base-content/60">Pannes en cours</p>
                <p class="mt-1 text-3xl font-bold tracking-tight">{{ $pannesEnCours }}</p>
            </div>
        </div>

        {{-- Pannes en retard --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body gap-0 p-5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $pannesEnRetard > 0 ? 'bg-error/10 text-error' : 'bg-base-200 text-base-content/70' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <p class="mt-4 text-sm text-base-content/60">Pannes en retard</p>
                <p class="mt-1 text-3xl font-bold tracking-tight {{ $pannesEnRetard > 0 ? 'text-error' : '' }}">{{ $pannesEnRetard }}</p>
            </div>
        </div>

        {{-- Disponibilité du parc --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body gap-0 p-5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-success/10 text-success">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25 4.5-4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <p class="mt-4 text-sm text-base-content/60">Disponibilité du parc</p>
                <p class="mt-1 text-3xl font-bold tracking-tight">{{ $disponibilite }} %</p>
                <p class="mt-1 text-xs text-base-content/50">{{ $machinesActives }} / {{ $totalMachines }} machines actives</p>
            </div>
        </div>

        {{-- Temps d'arrêt ce mois --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body gap-0 p-5">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <p class="mt-4 text-sm text-base-content/60">Temps d'arrêt (ce mois)</p>
                <p class="mt-1 text-3xl font-bold tracking-tight">{{ $tempsArretLib }}</p>
            </div>
        </div>
    </div>

    {{-- Ligne 1 : pannes récentes + donut --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="card-title text-base">Pannes récentes</h3>
                    <a href="{{ route('pannes.index') }}" wire:navigate class="link link-hover text-sm text-base-content/50">Voir tout</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Date</th>
                                <th>Responsable</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pannesRecentes as $panne)
                                <tr>
                                    <td class="font-medium">{{ $panne->machine->nom }}</td>
                                    <td class="text-base-content/70">{{ $panne->date_panne->format('d/m/Y H:i') }}</td>
                                    <td class="text-base-content/70">{{ $panne->responsable?->name ?? '—' }}</td>
                                    <td><span class="badge {{ $statutBadge[$panne->statut->value] }} badge-soft badge-sm">{{ $panne->statut->libelle() }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-sm text-base-content/50">Aucune panne enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-base">Pannes ouvertes par statut</h3>
                <p class="-mt-1 text-xs text-base-content/50">Charge en cours (hors clôturées)</p>
                @if (array_sum($statuts) === 0)
                    <p class="py-10 text-center text-sm text-base-content/50">Aucune panne ouverte 🎉</p>
                @else
                    <div class="relative mx-auto mt-2 h-44 w-44"><canvas id="chartStatuts"></canvas></div>
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-warning"></span>En attente</span><span class="font-semibold">{{ $statuts['en_attente'] }}</span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-info"></span>Assignée</span><span class="font-semibold">{{ $statuts['assignee'] }}</span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-primary"></span>En cours</span><span class="font-semibold">{{ $statuts['en_cours'] }}</span></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ligne 2 : courbe + top machines --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="card-title text-base">Pannes &amp; interventions (6 mois)</h3>
                    <div class="flex items-center gap-4 text-xs font-medium text-base-content/60">
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-warning"></span>Pannes</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-primary"></span>Interventions</span>
                    </div>
                </div>
                <div class="relative h-64"><canvas id="chartEvolution"></canvas></div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-base">Top machines en panne</h3>
                @if ($topMachines->isEmpty())
                    <p class="py-10 text-center text-sm text-base-content/50">Aucune panne enregistrée.</p>
                @else
                    <div class="relative h-64"><canvas id="chartMachines"></canvas></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ligne 3 : MTTR + respect des délais --}}
    @php
        $mh = intdiv($mttrMin, 60);
        $mm = $mttrMin % 60;
        $mttrLib = $mttrMin > 0 ? ($mh > 0 ? $mh.' h ' : '').$mm.' min' : '—';
    @endphp
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="card bg-base-100 shadow-sm lg:col-span-2">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h3 class="card-title text-base">Durée moyenne de réparation (MTTR)</h3>
                    <span class="text-2xl font-bold">{{ $mttrLib }}</span>
                </div>
                <p class="-mt-1 text-xs text-base-content/50">Évolution mensuelle, en heures</p>
                @if ($mttrCount === 0)
                    <p class="py-12 text-center text-sm text-base-content/50">Aucune intervention clôturée pour l'instant.</p>
                @else
                    <div class="relative mt-2 h-56"><canvas id="chartMttr"></canvas></div>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-base">Respect des délais</h3>
                <p class="-mt-1 text-xs text-base-content/50">Interventions clôturées avec échéance</p>
                @if (is_null($tauxRespect))
                    <p class="py-12 text-center text-sm text-base-content/50">Aucune donnée.</p>
                @else
                    <div class="relative mx-auto mt-2 h-40 w-40"><canvas id="chartDelais"></canvas></div>
                    <p class="mt-3 text-center text-3xl font-bold">{{ $tauxRespect }} %</p>
                    <p class="text-center text-xs text-base-content/50">clôturées à temps</p>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-success"></span>À temps</span><span class="font-semibold">{{ $aTemps }}</span></div>
                        <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-error"></span>En retard</span><span class="font-semibold">{{ $enRetard }}</span></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Données des graphiques (JSON, lu par le script) --}}
    @php
        $chartData = [
            'statuts' => array_values($statuts),
            'serieLabels' => $serie->pluck('label')->values(),
            'seriePannes' => $serie->pluck('pannes')->values(),
            'serieInterventions' => $serie->pluck('interventions')->values(),
            'machinesLabels' => $topMachines->pluck('nom')->values(),
            'machinesCounts' => $topMachines->pluck('pannes_count')->values(),
            'mttrLabels' => $mttrSerie->pluck('label')->values(),
            'mttrValues' => $mttrSerie->pluck('mttr')->values(),
            'delais' => [$aTemps, $enRetard],
        ];
    @endphp
    <script type="application/json" id="dashboard-data">@json($chartData)</script>

    {{-- Chart.js --}}
    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @endassets

    @script
        <script>
            Chart.defaults.font.family = 'Plus Jakarta Sans, ui-sans-serif, system-ui, sans-serif'
            Chart.defaults.color = '#94a3b8'
            Chart.defaults.plugins.legend.display = false
            const grid = 'rgba(148,163,184,.2)'

            const data = JSON.parse(document.getElementById('dashboard-data').textContent)

            const mk = (id, config) => {
                const el = document.getElementById(id)
                if (! el) return
                const existing = Chart.getChart(el)
                if (existing) existing.destroy()
                new Chart(el, config)
            }

            if (data.statuts.reduce((a, b) => a + b, 0) > 0) {
                mk('chartStatuts', {
                    type: 'doughnut',
                    data: {
                        labels: ['En attente', 'Assignée', 'En cours'],
                        datasets: [{ data: data.statuts, backgroundColor: ['#f59e0b', '#3b82f6', '#6366f1'], borderWidth: 0 }],
                    },
                    options: { cutout: '68%', responsive: true, maintainAspectRatio: false },
                })
            }

            mk('chartEvolution', {
                type: 'line',
                data: {
                    labels: data.serieLabels,
                    datasets: [
                        { label: 'Pannes', data: data.seriePannes, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.08)', fill: true, tension: .4, pointRadius: 3, pointBackgroundColor: '#f59e0b' },
                        { label: 'Interventions', data: data.serieInterventions, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,.08)', fill: true, tension: .4, pointRadius: 3, pointBackgroundColor: '#6366f1' },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { precision: 0 } }, x: { grid: { display: false }, border: { display: false } } },
                },
            })

            if (data.machinesLabels.length > 0) {
                mk('chartMachines', {
                    type: 'bar',
                    data: {
                        labels: data.machinesLabels,
                        datasets: [{ data: data.machinesCounts, backgroundColor: '#fb923c', borderRadius: 8, borderSkipped: false, maxBarThickness: 38 }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true, maintainAspectRatio: false,
                        scales: { x: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { precision: 0 } }, y: { grid: { display: false }, border: { display: false } } },
                    },
                })
            }

            if (document.getElementById('chartMttr')) {
                mk('chartMttr', {
                    type: 'line',
                    data: { labels: data.mttrLabels, datasets: [{ label: 'MTTR (h)', data: data.mttrValues, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.08)', fill: true, tension: .4, pointRadius: 3, pointBackgroundColor: '#0ea5e9' }] },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { callback: v => v + ' h' } }, x: { grid: { display: false }, border: { display: false } } } },
                })
            }

            if ((data.delais[0] + data.delais[1]) > 0) {
                mk('chartDelais', {
                    type: 'doughnut',
                    data: { labels: ['À temps', 'En retard'], datasets: [{ data: data.delais, backgroundColor: ['#22c55e', '#ef4444'], borderWidth: 0 }] },
                    options: { cutout: '68%', responsive: true, maintainAspectRatio: false },
                })
            }
        </script>
    @endscript
</div>
