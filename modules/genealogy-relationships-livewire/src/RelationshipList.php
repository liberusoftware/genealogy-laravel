<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Livewire\Component;

final class RelationshipList extends Component
{
    public string $type = '';

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return ['type' => ['nullable', Rule::in(Relationship::TYPES)]];
    }

    public function updatedType(): void
    {
        $this->validateOnly('type');
    }

    public function render(): mixed
    {
        return view('genealogy-relationships-livewire::list', [
            'records' => Relationship::query()
                ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
