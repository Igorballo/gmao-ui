<?php

namespace App\Livewire\Machines;

use App\Enums\StatutMachine;
use App\Enums\TypeMachine;
use App\Models\Machine;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts::app')]
class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $filtreStatut = '';

    // État du formulaire (modal)
    public bool $showModal = false;

    public ?int $editingId = null;

    // Confirmation de suppression
    public ?int $confirmingDeleteId = null;

    public ?string $confirmingDeleteNom = null;

    public string $nom = '';

    public string $description = '';

    public string $type = '';

    public string $date_mise_en_production = '';

    public string $statut = '';

    public ?TemporaryUploadedFile $photo = null;

    public bool $removePhoto = false;

    public ?string $existingPhoto = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreStatut(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        abort_unless(auth()->user()->can('machines.gerer'), 403);

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()->can('machines.gerer'), 403);

        $machine = Machine::findOrFail($id);

        $this->editingId = $machine->id;
        $this->nom = $machine->nom;
        $this->description = $machine->description ?? '';
        $this->type = $machine->type->value;
        $this->statut = $machine->statut->value;
        $this->date_mise_en_production = $machine->date_mise_en_production?->format('Y-m-d') ?? '';
        $this->existingPhoto = $machine->photo;
        $this->photo = null;
        $this->removePhoto = false;

        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('machines.gerer'), 403);

        $this->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('machines', 'nom')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', new Enum(TypeMachine::class)],
            'statut' => ['required', new Enum(StatutMachine::class)],
            'date_mise_en_production' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], attributes: [
            'nom' => 'nom',
            'type' => 'type',
            'statut' => 'statut',
            'date_mise_en_production' => 'date de mise en production',
            'photo' => 'photo',
        ]);

        $payload = [
            'nom' => $this->nom,
            'description' => $this->description ?: null,
            'type' => $this->type,
            'statut' => $this->statut,
            'date_mise_en_production' => $this->date_mise_en_production ?: null,
        ];

        $isUpdate = (bool) $this->editingId;
        $machine = $this->editingId ? Machine::findOrFail($this->editingId) : null;
        $oldPhoto = $machine?->photo;

        if ($this->removePhoto) {
            $payload['photo'] = null;
        }

        if ($this->photo) {
            $payload['photo'] = $this->photo->store('machines', 'public');
        }

        if ($this->editingId) {
            $machine->update($payload);
        } else {
            Machine::create($payload);
        }

        if (($this->removePhoto || $this->photo) && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify',
            message: $isUpdate ? 'Machine mise à jour.' : 'Machine créée avec succès.',
            type: $isUpdate ? 'info' : 'success',
        );
    }

    public function confirmDelete(int $id): void
    {
        abort_unless(auth()->user()->can('machines.gerer'), 403);

        $machine = Machine::findOrFail($id);
        $this->confirmingDeleteId = $machine->id;
        $this->confirmingDeleteNom = $machine->nom;
    }

    public function delete(): void
    {
        abort_unless(auth()->user()->can('machines.gerer'), 403);

        if (! $this->confirmingDeleteId) {
            return;
        }

        $machine = Machine::findOrFail($this->confirmingDeleteId);

        if ($machine->pannes()->exists()) {
            $this->dispatch('notify', message: "Impossible de supprimer « {$machine->nom} » : des pannes y sont rattachées.", type: 'error');
            $this->confirmingDeleteId = null;

            return;
        }

        if ($machine->photo) {
            Storage::disk('public')->delete($machine->photo);
        }

        $machine->delete();
        $this->confirmingDeleteId = null;
        $this->confirmingDeleteNom = null;
        $this->dispatch('notify', message: 'Machine supprimée.', type: 'delete');
    }

    public function clearPhoto(): void
    {
        $this->photo = null;
        $this->removePhoto = true;
        $this->existingPhoto = null;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'nom', 'description', 'type', 'date_mise_en_production', 'statut', 'photo', 'removePhoto', 'existingPhoto']);
        $this->statut = StatutMachine::Actif->value;
        $this->resetErrorBag();
    }

    public function render()
    {
        $machines = Machine::query()
            ->when($this->search, fn ($q) => $q->where('nom', 'like', "%{$this->search}%"))
            ->when($this->filtreStatut, fn ($q) => $q->where('statut', $this->filtreStatut))
            ->orderBy('nom')
            ->paginate(10);

        return view('livewire.machines.index', [
            'machines' => $machines,
            'types' => TypeMachine::cases(),
            'statuts' => StatutMachine::cases(),
        ]);
    }
}
