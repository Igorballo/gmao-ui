<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    @php
        $h = intdiv($stats['tempsArretMinutes'], 60);
        $m = $stats['tempsArretMinutes'] % 60;
        $tempsArret = ($h > 0 ? $h.' h ' : '').$m.' min';
        $statutLib = ['en_attente' => 'En attente', 'assignee' => 'Assignée', 'en_cours' => 'En cours', 'cloturee' => 'Clôturée'];
    @endphp
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1e293b; font-size: 12px; margin: 0; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 12px; margin-bottom: 20px; }
        .brand { font-size: 22px; font-weight: bold; color: #0f172a; }
        .brand span { color: #4f46e5; }
        .title { font-size: 15px; margin-top: 4px; color: #334155; }
        .meta { font-size: 11px; color: #64748b; margin-top: 2px; }
        .stats { width: 100%; margin-bottom: 22px; border-collapse: collapse; }
        .stats td { width: 20%; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; }
        .stats .val { font-size: 18px; font-weight: bold; color: #0f172a; }
        .stats .lbl { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        h2 { font-size: 13px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin: 22px 0 10px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #f1f5f9; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; }
        table.data td { padding: 7px 8px; border-bottom: 1px solid #eef2f6; vertical-align: top; }
        .badge { font-size: 10px; padding: 2px 7px; border-radius: 10px; background: #e2e8f0; color: #334155; }
        .b-attente { background: #fef3c7; color: #92400e; }
        .b-assignee { background: #e0e7ff; color: #3730a3; }
        .b-cours { background: #dbeafe; color: #1e40af; }
        .b-cloturee { background: #dcfce7; color: #166534; }
        .muted { color: #94a3b8; }
        .footer { margin-top: 26px; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">GMAO<span>+</span></div>
        <div class="title">Rapport technique journalier — {{ $date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</div>
        <div class="meta">Généré le {{ $genereLe->format('d/m/Y à H:i') }}</div>
    </div>

    <table class="stats">
        <tr>
            <td><div class="val">{{ $stats['nbPannes'] }}</div><div class="lbl">Pannes</div></td>
            <td><div class="val">{{ $stats['nbInterventions'] }}</div><div class="lbl">Interventions</div></td>
            <td><div class="val">{{ $stats['nbEnCours'] }}</div><div class="lbl">En cours</div></td>
            <td><div class="val">{{ $stats['nbCloturees'] }}</div><div class="lbl">Clôturées</div></td>
            <td><div class="val">{{ $tempsArret }}</div><div class="lbl">Temps d'arrêt</div></td>
        </tr>
    </table>

    <h2>Pannes de la journée</h2>
    @if ($pannes->isEmpty())
        <p class="muted">Aucune panne signalée ce jour.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:20%">Machine</th>
                    <th style="width:34%">Description</th>
                    <th style="width:22%">Intervenants</th>
                    <th style="width:12%">Heure</th>
                    <th style="width:8%">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pannes as $panne)
                    @php
                        $cls = ['en_attente' => 'b-attente', 'assignee' => 'b-assignee', 'en_cours' => 'b-cours', 'cloturee' => 'b-cloturee'][$panne->statut->value] ?? '';
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $panne->machine->nom }}</td>
                        <td>{{ $panne->description }}</td>
                        <td>
                            @forelse ($panne->intervenants as $i)
                                {{ $i->name }}@if ($i->id === $panne->responsable_id) (resp.)@endif{{ ! $loop->last ? ', ' : '' }}
                            @empty
                                <span class="muted">—</span>
                            @endforelse
                        </td>
                        <td>{{ $panne->date_panne->format('H:i') }}</td>
                        <td><span class="badge {{ $cls }}">{{ $statutLib[$panne->statut->value] }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Interventions de la journée</h2>
    @if ($interventions->isEmpty())
        <p class="muted">Aucune intervention démarrée ce jour.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width:4%">#</th>
                    <th style="width:20%">Machine</th>
                    <th style="width:34%">Cause / opérations</th>
                    <th style="width:18%">Maintenancier</th>
                    <th style="width:12%">Début</th>
                    <th style="width:12%">Durée</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($interventions as $intervention)
                    @php
                        $duree = $intervention->demarree_le && $intervention->terminee_le
                            ? $intervention->demarree_le->diffInMinutes($intervention->terminee_le)
                            : null;
                        $dureeLib = $duree === null ? 'En cours' : (intdiv($duree, 60) > 0 ? intdiv($duree, 60).'h ' : '').($duree % 60).'min';
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $intervention->panne?->machine?->nom ?? '—' }}</td>
                        <td>
                            @if ($intervention->cause)<strong>Cause :</strong> {{ $intervention->cause }}<br>@endif
                            {{ $intervention->operations ?? '—' }}
                        </td>
                        <td>{{ $intervention->maintenancier?->name ?? '—' }}</td>
                        <td>{{ $intervention->demarree_le?->format('H:i') ?? '—' }}</td>
                        <td>{{ $dureeLib }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">GMAO+ — Document généré automatiquement · Usage interne</div>
</body>
</html>
