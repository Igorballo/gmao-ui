<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rapport;
use App\Services\RapportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    protected function format(Rapport $rapport): array
    {
        return [
            'id' => $rapport->id,
            'type' => $rapport->type,
            'date_rapport' => $rapport->date_rapport?->toDateString(),
            'date_debut' => $rapport->date_debut?->toDateString(),
            'date_fin' => $rapport->date_fin?->toDateString(),
            'genere_le' => $rapport->genere_le?->toIso8601String(),
            'disponible' => $rapport->chemin_pdf
                && Storage::disk(RapportService::DISK)->exists($rapport->chemin_pdf),
        ];
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('rapports.consulter'), 403);

        $rapports = Rapport::orderByDesc('date_rapport')->get();

        return ['data' => $rapports->map(fn ($r) => $this->format($r))->all()];
    }

    public function generer(Request $request, RapportService $service)
    {
        abort_unless($request->user()->can('rapports.consulter'), 403);

        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $rapport = $service->genererPourDate(Carbon::parse($data['date']));

        return ['data' => $this->format($rapport)];
    }

    public function genererPeriode(Request $request, RapportService $service)
    {
        abort_unless($request->user()->can('rapports.consulter'), 403);

        $data = $request->validate([
            'date_debut' => ['required', 'date', 'before_or_equal:today'],
            'date_fin' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:date_debut'],
        ]);

        $rapport = $service->genererPourPeriode(
            Carbon::parse($data['date_debut']),
            Carbon::parse($data['date_fin']),
        );

        return ['data' => $this->format($rapport)];
    }

    public function telecharger(Request $request, Rapport $rapport)
    {
        abort_unless($request->user()->can('rapports.consulter'), 403);
        abort_unless(
            $rapport->chemin_pdf && Storage::disk(RapportService::DISK)->exists($rapport->chemin_pdf),
            404,
        );

        return Storage::disk(RapportService::DISK)->download(
            $rapport->chemin_pdf,
            'rapport-'.$rapport->date_rapport->format('Y-m-d').'.pdf',
        );
    }
}
