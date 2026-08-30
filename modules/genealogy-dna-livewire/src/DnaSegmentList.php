<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Models\DnaSegment;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaSegmentList extends Component
{
    public ?string $matchId = null;

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null
            ? collect()
            : app(TeamContext::class)->run($teamId, fn () => DnaSegment::query()
                ->when($this->matchId !== null, fn ($query) => $query->where('match_id', $this->matchId))
                ->latest()
                ->limit(25)
                ->get());

        return view('genealogy-dna-livewire::segments', ['records' => $records]);
    }
}
