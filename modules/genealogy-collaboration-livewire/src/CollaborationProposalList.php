<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Livewire;

use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class CollaborationProposalList extends Component
{
    public string $status = '';

    public string $search = '';

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null
            ? collect()
            : app(TeamContext::class)->run($teamId, fn () => CollaborationProposal::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->when($this->search !== '', fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
                ->latest()
                ->limit(25)
                ->get());

        return view('genealogy-collaboration-livewire::proposals', ['records' => $records]);
    }
}
