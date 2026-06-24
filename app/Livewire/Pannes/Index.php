<?php

namespace App\Livewire\Pannes;

use App\Enums\StatutPanne;
use App\Models\Machine;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filtreStatut = '';

    // --- Modal création / édition ---
    public bool $showCreateModal = false;

    public ?int $editingId = null;

    public string $machine_id = '';

    public string $date_panne = '';

    public string $description = '';

    // --- Modal délégation ---
    public bool $showDelegateModal = false;

    public ?int $delegatePanneId = null;

    /** @var array<int, string> IDs des intervenants sélectionnés */
    public array $intervenants = [];

    public string $responsable_id = '';

    public string $deadline = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreStatut(): void
    {
        $this->resetPage();
    }

    /** Garantit qu'il y a toujours un responsable parmi les intervenants. */
    public function updatedIntervenants(): void
    {
        if (! in_array($this->responsable_id, $this->intervenants, true)) {
            $this->responsable_id = $this->intervenants[0] ?? '';
        }
    }

    /** Retire un intervenant (depuis sa pastille). */
    public function removeIntervenant(string $id): void
    {
        $this->intervenants = array_values(array_filter(
            $this->intervenants,
            fn ($i) => (string) $i !== $id,
        ));
        $this->updatedIntervenants();
    }

    // ---------------------------------------------------------------- Création
    public function create(): void
    {
        abort_unless(auth()->user()->can('pannes.creer'), 403);

        $this->reset(['editingId', 'machine_id', 'date_panne', 'description']);
        $this->date_panne = now()->format('Y-m-d\TH:i');
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()->can('pannes.creer'), 403);

        $panne = Panne::findOrFail($id);
        $this->editingId = $panne->id;
        $this->machine_id = (string) $panne->machine_id;
        $this->date_panne = $panne->date_panne->format('Y-m-d\TH:i');
        $this->description = $panne->description;
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('pannes.creer'), 403);

        $this->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'date_panne' => ['required', 'date'],
            'description' => ['required', 'string', 'max:2000'],
        ], attributes: [
            'machine_id' => 'machine',
            'date_panne' => 'date de la panne',
            'description' => 'description',
        ]);

        if ($this->editingId) {
            Panne::findOrFail($this->editingId)->update([
                'machine_id' => $this->machine_id,
                'date_panne' => Carbon::parse($this->date_panne),
                'description' => $this->description,
            ]);
            $message = 'Panne mise à jour.';
            $type = 'info';
        } else {
            Panne::create([
                'machine_id' => $this->machine_id,
                'declaree_par_id' => auth()->id(),
                'date_panne' => Carbon::parse($this->date_panne),
                'description' => $this->description,
                'statut' => StatutPanne::EnAttente,
            ]);
            $message = 'Panne signalée avec succès.';
            $type = 'success';
        }

        $this->showCreateModal = false;
        $this->reset(['editingId', 'machine_id', 'date_panne', 'description']);
        $this->dispatch('notify', message: $message, type: $type);
    }

    // -------------------------------------------------------------- Délégation
    public function openDelegate(int $id): void
    {
        abort_unless(auth()->user()->can('pannes.deleguer'), 403);

        $panne = Panne::with('intervenants')->findOrFail($id);
        $this->delegatePanneId = $panne->id;
        $this->intervenants = $panne->intervenants->pluck('id')->map(fn ($i) => (string) $i)->toArray();
        $this->responsable_id = (string) ($panne->responsable_id ?? '');
        $this->deadline = $panne->deadline?->format('Y-m-d\TH:i') ?? '';
        $this->resetErrorBag();
        $this->showDelegateModal = true;
    }

    public function saveDelegate(): void
    {
        abort_unless(auth()->user()->can('pannes.deleguer'), 403);

        $this->validate([
            'intervenants' => ['required', 'array', 'min:1'],
            'intervenants.*' => ['exists:users,id'],
            'responsable_id' => ['required', Rule::in($this->intervenants)],
            'deadline' => ['required', 'date', 'after:now'],
        ], attributes: [
            'intervenants' => 'intervenants',
            'responsable_id' => 'responsable',
            'deadline' => 'échéance',
        ], messages: [
            'intervenants.required' => 'Sélectionnez au moins une personne.',
            'responsable_id.required' => 'Désignez un responsable.',
            'responsable_id.in' => 'Le responsable doit faire partie des intervenants.',
        ]);

        $panne = Panne::findOrFail($this->delegatePanneId);
        $panne->intervenants()->sync($this->intervenants);
        $panne->update([
            'responsable_id' => $this->responsable_id,
            'deleguee_par_id' => auth()->id(),
            'deleguee_le' => now(),
            'deadline' => Carbon::parse($this->deadline),
            'statut' => $panne->statut === StatutPanne::EnAttente ? StatutPanne::Assignee : $panne->statut,
        ]);

        $this->showDelegateModal = false;
        $this->reset(['delegatePanneId', 'intervenants', 'responsable_id', 'deadline']);
        $this->dispatch('notify', message: 'Panne assignée avec succès.', type: 'success');
    }

    public function render()
    {
        $pannes = Panne::query()
            ->with(['machine', 'declarePar', 'responsable', 'intervenants'])
            ->when($this->search, function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhereHas('machine', fn ($m) => $m->where('nom', 'like', "%{$this->search}%"));
            })
            ->when($this->filtreStatut, fn ($q) => $q->where('statut', $this->filtreStatut))
            ->orderByDesc('date_panne')
            ->paginate(10);

        return view('livewire.pannes.index', [
            'pannes' => $pannes,
            'machines' => Machine::orderBy('nom')->get(),
            'utilisateurs' => User::where('actif', true)->orderBy('name')->get(),
            'statuts' => StatutPanne::cases(),
        ]);
    }
}
