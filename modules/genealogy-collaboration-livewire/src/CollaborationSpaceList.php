<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;
use Livewire\Component;

final class CollaborationSpaceList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-collaboration-livewire::list', [
            'records' => CollaborationSpace::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
