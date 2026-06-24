<?php

namespace App\Livewire\Pannes;

use App\Enums\StatutMachine;
use App\Enums\StatutPanne;
use App\Enums\TypePhoto;
use App\Models\Panne;
use App\Services\PanneNotifier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts::app')]
class Show extends Component
{
    use WithFileUploads;

    public Panne $panne;

    public string $cause = '';

    public string $operations = '';

    /** @var array<int, array{reference: string, quantite: int}> */
    public array $pieces = [];

    public array $photosAvant = [];

    public array $photosApres = [];

    public bool $confirmingCloture = false;

    protected array $relations = [
        'machine', 'declarePar', 'responsable', 'intervenants', 'delegueePar',
        'intervention.maintenancier', 'intervention.pieces', 'intervention.photos',
    ];

    public function mount(Panne $panne): void
    {
        $this->panne = $panne->load($this->relations);
        $this->chargerIntervention();
    }

    /** L'utilisateur peut intervenir s'il fait partie des intervenants (ou s'il peut déléguer). */
    public function peutIntervenir(): bool
    {
        $user = auth()->user();

        return $user->can('interventions.prendre_en_charge')
            && ($this->panne->intervenants->contains($user->id) || $user->can('pannes.deleguer'));
    }

    protected function chargerIntervention(): void
    {
        $intervention = $this->panne->intervention;

        $this->cause = $intervention?->cause ?? '';
        $this->operations = $intervention?->operations ?? '';
        $this->pieces = $intervention
            ? $intervention->pieces->map(fn ($p) => ['reference' => $p->reference, 'quantite' => $p->quantite])->toArray()
            : [];
    }

    public function demarrer(): void
    {
        abort_unless($this->peutIntervenir(), 403);

        if ($this->panne->intervention || $this->panne->statut !== StatutPanne::Assignee) {
            return;
        }

        $this->panne->intervention()->create([
            'maintenancier_id' => $this->panne->responsable_id ?? auth()->id(),
            'demarree_le' => now(),
        ]);
        $this->panne->update(['statut' => StatutPanne::EnCours]);

        // La machine est mise à l'arrêt pendant l'intervention
        $this->panne->machine->update(['statut' => StatutMachine::Arret]);

        app(PanneNotifier::class)->interventionDemarree($this->panne, auth()->user());

        $this->rafraichir();
        $this->dispatch('notify', message: 'Intervention démarrée. Machine mise à l\'arrêt.', type: 'success');
    }

    public function ajouterPiece(): void
    {
        $this->pieces[] = ['reference' => '', 'quantite' => 1];
    }

    public function retirerPiece(int $index): void
    {
        unset($this->pieces[$index]);
        $this->pieces = array_values($this->pieces);
    }

    public function enregistrer(): void
    {
        abort_unless($this->peutIntervenir(), 403);

        $intervention = $this->panne->intervention;
        abort_if($intervention === null, 400);

        $this->validate([
            'cause' => ['nullable', 'string', 'max:2000'],
            'operations' => ['nullable', 'string', 'max:2000'],
            'pieces' => ['array'],
            'pieces.*.reference' => ['required', 'string', 'max:255'],
            'pieces.*.quantite' => ['required', 'integer', 'min:1'],
            'photosAvant.*' => ['image', 'max:12288'],
            'photosApres.*' => ['image', 'max:12288'],
        ], attributes: [
            'pieces.*.reference' => 'référence',
            'pieces.*.quantite' => 'quantité',
        ]);

        $intervention->update([
            'cause' => $this->cause ?: null,
            'operations' => $this->operations ?: null,
        ]);

        $intervention->pieces()->delete();
        foreach ($this->pieces as $piece) {
            $intervention->pieces()->create([
                'reference' => $piece['reference'],
                'quantite' => (int) $piece['quantite'],
            ]);
        }

        foreach ($this->photosAvant as $photo) {
            $intervention->photos()->create([
                'chemin' => $photo->store('interventions', 'public'),
                'type' => TypePhoto::Avant,
            ]);
        }
        foreach ($this->photosApres as $photo) {
            $intervention->photos()->create([
                'chemin' => $photo->store('interventions', 'public'),
                'type' => TypePhoto::Apres,
            ]);
        }

        $this->photosAvant = [];
        $this->photosApres = [];
        $this->rafraichir();
        $this->dispatch('notify', message: 'Intervention enregistrée.', type: 'success');
    }

    public function cloturer(): void
    {
        abort_unless($this->peutIntervenir(), 403);

        $intervention = $this->panne->intervention;
        abort_if($intervention === null, 400);

        $this->confirmingCloture = false;
        $this->enregistrer();

        $intervention->update(['terminee_le' => now()]);
        $this->panne->update(['statut' => StatutPanne::Cloturee]);

        // La machine repasse en service
        $this->panne->machine->update(['statut' => StatutMachine::Actif]);

        app(PanneNotifier::class)->interventionCloturee($this->panne, auth()->user());

        $this->rafraichir();
        $this->dispatch('notify', message: 'Intervention clôturée. Machine remise en service.', type: 'success');
    }

    protected function rafraichir(): void
    {
        $this->panne->refresh()->load($this->relations);
        $this->chargerIntervention();
    }

    public function render()
    {
        return view('livewire.pannes.show', [
            'peutIntervenir' => $this->peutIntervenir(),
        ]);
    }
}
