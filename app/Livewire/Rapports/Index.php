<?php

namespace App\Livewire\Rapports;

use App\Models\Rapport;
use App\Services\RapportService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
class Index extends Component
{
    use WithPagination;

    public string $dateGeneration = '';

    public function mount(): void
    {
        $this->dateGeneration = now()->toDateString();
    }

    public function generer(): void
    {
        abort_unless(auth()->user()->can('rapports.consulter'), 403);

        $this->validate([
            'dateGeneration' => ['required', 'date', 'before_or_equal:today'],
        ], attributes: [
            'dateGeneration' => 'date',
        ]);

        $date = Carbon::parse($this->dateGeneration);
        app(RapportService::class)->genererPourDate($date);

        $this->resetPage();
        $this->dispatch('notify', message: "Rapport généré pour le {$date->format('d/m/Y')}.", type: 'success');
    }

    public function render()
    {
        return view('livewire.rapports.index', [
            'rapports' => Rapport::orderByDesc('date_rapport')->paginate(15),
        ]);
    }
}
