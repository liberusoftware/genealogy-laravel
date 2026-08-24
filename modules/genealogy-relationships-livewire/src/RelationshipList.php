<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Livewire;

use Liberu\Genealogy\Relationships\Models\Relationship;
use Livewire\Component;

final class RelationshipList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-relationships-livewire::list', [
            'records' => Relationship::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
