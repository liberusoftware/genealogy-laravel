<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Models\DnaGroup;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaGroupList extends Component
{
    public string $status = '';

    public string $search = '';

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null
            ? collect()
            : app(TeamContext::class)->run($teamId, fn () => DnaGroup::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                ->withCount('matches')
                ->latest()
                ->limit(25)
                ->get());

        return view('genealogy-dna-livewire::groups', [
            'records' => $records,
        ]);
    }
}
