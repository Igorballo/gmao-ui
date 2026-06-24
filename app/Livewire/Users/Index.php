<?php

namespace App\Livewire\Users;

use App\Mail\NouveauCompteMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts::app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // État du formulaire (modal)
    public bool $showModal = false;

    public ?int $editingId = null;

    // Confirmation de suppression
    public ?int $confirmingDeleteId = null;

    public ?string $confirmingDeleteNom = null;

    public string $name = '';

    public string $email = '';

    public string $telephone = '';

    public bool $actif = true;

    /** Nom du rôle sélectionné (obligatoire) */
    public string $role = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->telephone = $user->telephone ?? '';
        $this->actif = $user->actif;
        $this->role = $user->roles->first()?->name ?? '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'telephone' => ['nullable', 'string', 'max:30'],
            'actif' => ['boolean'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], attributes: [
            'name' => 'nom',
            'email' => 'adresse e-mail',
            'telephone' => 'téléphone',
            'role' => 'rôle',
        ]);

        $payload = [
            'name' => $this->name,
            'email' => $this->email,
            'telephone' => $this->telephone ?: null,
            'actif' => $this->actif,
        ];

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($payload);
            $user->syncRoles([$this->role]);

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('notify', message: 'Utilisateur mis à jour.', type: 'info');

            return;
        }

        // Création : mot de passe généré (8 caractères) puis envoyé par e-mail
        $motDePasse = Str::password(8);
        $payload['password'] = Hash::make($motDePasse);

        $user = User::create($payload);
        $user->syncRoles([$this->role]);

        // Envoi des identifiants par e-mail (driver "log" en local par défaut)
        try {
            Mail::to($user->email)->send(new NouveauCompteMail($user, $motDePasse));
            $message = "Utilisateur créé. Les identifiants ont été envoyés à {$user->email}.";
        } catch (\Throwable $e) {
            report($e);
            $message = "Utilisateur créé, mais l'envoi de l'e-mail a échoué (vérifiez la configuration mail).";
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function toggleActif(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['actif' => ! $user->actif]);

        $this->dispatch('notify',
            message: $user->actif ? 'Compte activé.' : 'Compte désactivé.',
            type: $user->actif ? 'success' : 'warning',
        );
    }

    public function confirmDelete(int $id): void
    {
        $user = User::findOrFail($id);
        $this->confirmingDeleteId = $user->id;
        $this->confirmingDeleteNom = $user->name;
    }

    public function delete(): void
    {
        if (! $this->confirmingDeleteId) {
            return;
        }

        // Sécurité : interdiction de supprimer son propre compte
        if ($this->confirmingDeleteId === auth()->id()) {
            $this->dispatch('notify', message: 'Vous ne pouvez pas supprimer votre propre compte.', type: 'error');
            $this->confirmingDeleteId = null;

            return;
        }

        User::findOrFail($this->confirmingDeleteId)->delete();

        $this->confirmingDeleteId = null;
        $this->confirmingDeleteNom = null;
        $this->dispatch('notify', message: 'Utilisateur supprimé.', type: 'delete');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'telephone', 'actif', 'role']);
        $this->actif = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }
}
