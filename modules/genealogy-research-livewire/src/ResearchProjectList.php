<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Livewire;

use Liberu\Genealogy\Research\Models\ResearchProject;
use Livewire\Component;

final class ResearchProjectList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-research-livewire::list', [
            'records' => ResearchProject::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
