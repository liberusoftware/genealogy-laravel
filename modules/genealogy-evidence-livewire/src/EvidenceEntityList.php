<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\DeleteEvidenceEntity;
use Liberu\Genealogy\Evidence\Models\Assertion;
use Liberu\Genealogy\Evidence\Models\Citation;
use Liberu\Genealogy\Evidence\Models\Extract;
use Liberu\Genealogy\Evidence\Models\ProofConclusion;
use Liberu\Genealogy\Evidence\Models\Repository;
use Liberu\Genealogy\Evidence\Models\Source;
use Livewire\Component;

final class EvidenceEntityList extends Component
{
    public string $entity = 'sources';

    public string $search = '';

    public function delete(string $id, DeleteEvidenceEntity $delete): void
    {
        $this->guardAuthenticated();
        $this->modelClass()::query()->findOrFail($id)->tap(fn (Model $record): mixed => $delete->execute($record));
        $this->dispatch('evidence-entity-deleted', entity: $this->entity, id: $id);
    }

    /** @return array<int, array<string, mixed>> */
    public function records(): array
    {
        $this->guardAuthenticated();

        return $this->modelClass()::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Model $record): array => $record->toArray())
            ->all();
    }

    public function render(): View
    {
        $this->guardAuthenticated();

        return view('genealogy-evidence-livewire::entities', ['records' => $this->records()]);
    }

    private function guardAuthenticated(): void
    {
        abort_unless(auth()->check(), 403);
    }

    /** @return class-string<Model> */
    private function modelClass(): string
    {
        return match ($this->entity) {
            'sources' => Source::class,
            'repositories' => Repository::class,
            'citations' => Citation::class,
            'extracts' => Extract::class,
            'assertions' => Assertion::class,
            'proof-conclusions' => ProofConclusion::class,
            default => abort(404),
        };
    }
}
