<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Livewire;

use Liberu\Genealogy\Dna\Models\DnaConsent;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Livewire\Component;

final class DnaConsentList extends Component
{
    public ?string $kitId = null;

    public function render(): mixed
    {
        $teamId = app(TeamContext::class)->current() ?? auth()->user()?->currentTeam?->getKey();
        $records = $teamId === null
            ? collect()
            : app(TeamContext::class)->run($teamId, fn () => DnaConsent::query()
                ->when($this->kitId !== null, fn ($query) => $query->where('kit_id', $this->kitId))
                ->latest()
                ->limit(25)
                ->get());

        return view('genealogy-dna-livewire::consents', ['records' => $records]);
    }
}
