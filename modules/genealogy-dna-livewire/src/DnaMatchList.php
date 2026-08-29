<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaMatchList extends Component
{
    public string $status = '';

    public string $search = '';

    public bool $includePrivate = false;

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null
            ? collect()
            : app(TeamContext::class)->run($teamId, fn () => DnaMatch::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->when($this->search !== '', fn ($query) => $query->where('display_name', 'like', '%'.$this->search.'%'))
                ->when(! $this->includePrivate, fn ($query) => $query->where('is_private', false))
                ->latest()
                ->limit(25)
                ->get());

        return view('genealogy-dna-livewire::matches', [
            'records' => $records,
        ]);
    }
}
