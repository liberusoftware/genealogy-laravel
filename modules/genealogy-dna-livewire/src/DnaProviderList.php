<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Actions\CreateDnaProvider;
use Liberu\Genealogy\Dna\Actions\DeleteDnaProvider;
use Liberu\Genealogy\Dna\Actions\UpdateDnaProvider;
use Liberu\Genealogy\Dna\Models\DnaProvider;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaProviderList extends Component
{
    public string $status = '';

    public string $search = '';

    public string $name = '';

    public string $slug = '';

    public string $providerStatus = 'active';

    public string $website = '';

    public ?string $editingId = null;

    public function save(CreateDnaProvider $create, UpdateDnaProvider $update): void
    {
        abort_unless(auth()->check(), 403);
        $values = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'providerStatus' => ['required', 'in:'.implode(',', DnaProvider::STATUSES)],
            'website' => ['nullable', 'url', 'max:2048'],
        ]);
        $attributes = ['name' => $values['name'], 'slug' => $values['slug'], 'status' => $values['providerStatus'], 'website' => $values['website'] ?: null];
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);

        app(TeamContext::class)->run($teamId, function () use ($attributes, $create, $update): void {
            if ($this->editingId === null) {
                $create->execute($attributes);
            } else {
                $provider = DnaProvider::query()->findOrFail($this->editingId);
                $update->execute($provider, $attributes);
            }
        });
        $this->reset('name', 'slug', 'website', 'editingId');
        $this->providerStatus = 'active';
        $this->dispatch('genealogy-dna-provider-saved');
    }

    public function edit(string $id): void
    {
        abort_unless(auth()->check(), 403);
        $provider = $this->provider($id);
        $this->editingId = (string) $provider->getKey();
        $this->name = $provider->name;
        $this->slug = $provider->slug;
        $this->providerStatus = $provider->status;
        $this->website = $provider->website ?? '';
    }

    public function remove(string $id, DeleteDnaProvider $delete): void
    {
        abort_unless(auth()->check(), 403);
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);
        app(TeamContext::class)->run($teamId, fn () => $delete->execute(DnaProvider::query()->findOrFail($id)));
        $this->dispatch('genealogy-dna-provider-deleted');
    }

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null ? collect() : app(TeamContext::class)->run($teamId, fn () => DnaProvider::query()
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$this->search.'%')->orWhere('slug', 'like', '%'.$this->search.'%')))
            ->withCount('kits')->latest()->limit(25)->get());

        return view('genealogy-dna-livewire::providers', ['records' => $records]);
    }

    private function provider(string $id): DnaProvider
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        abort_unless($teamId !== null, 403);

        return app(TeamContext::class)->run($teamId, fn (): DnaProvider => DnaProvider::query()->findOrFail($id));
    }
}
