<?php

namespace App\Livewire\Roles;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts::app')]
class Index extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    // Confirmation de suppression
    public ?int $confirmingDeleteId = null;

    public ?string $confirmingDeleteNom = null;

    public string $name = '';

    /** @var array<int, string> Noms des permissions sélectionnées */
    public array $selectedPermissions = [];

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->editingId)],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', 'exists:permissions,name'],
        ], attributes: [
            'name' => 'nom du rôle',
        ]);

        $isUpdate = (bool) $this->editingId;

        $role = Role::updateOrCreate(
            ['id' => $this->editingId],
            ['name' => $this->name, 'guard_name' => 'web'],
        );

        $role->syncPermissions($this->selectedPermissions);

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify',
            message: $isUpdate ? 'Rôle mis à jour.' : 'Rôle créé avec succès.',
            type: $isUpdate ? 'info' : 'success',
        );
    }

    public function confirmDelete(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->confirmingDeleteId = $role->id;
        $this->confirmingDeleteNom = $role->name;
    }

    public function delete(): void
    {
        if (! $this->confirmingDeleteId) {
            return;
        }

        $role = Role::findOrFail($this->confirmingDeleteId);

        // Protection : on ne supprime pas un rôle encore attribué
        if ($role->users()->exists()) {
            $this->dispatch('notify', message: "Impossible de supprimer « {$role->name} » : il est encore attribué à des utilisateurs.", type: 'error');
            $this->confirmingDeleteId = null;

            return;
        }

        $role->delete();
        $this->confirmingDeleteId = null;
        $this->confirmingDeleteNom = null;
        $this->dispatch('notify', message: 'Rôle supprimé.', type: 'delete');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'selectedPermissions']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.roles.index', [
            'roles' => Role::withCount(['permissions', 'users'])->orderBy('name')->get(),
            // Permissions groupées par préfixe (ex. "pannes" => [pannes.creer, ...])
            'groupes' => Permission::orderBy('name')->get()->groupBy(
                fn ($p) => explode('.', $p->name)[0],
            ),
        ]);
    }
}
