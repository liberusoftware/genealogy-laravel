<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Livewire;

use Liberu\Genealogy\Relationships\Queries\RelationshipCalculator as RelationshipCalculatorQuery;
use Livewire\Component;

final class RelationshipCalculator extends Component
{
    public string $firstPersonId = '';

    public string $secondPersonId = '';

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public function calculate(RelationshipCalculatorQuery $calculator): void
    {
        $values = $this->validate([
            'firstPersonId' => ['required', 'uuid'],
            'secondPersonId' => ['required', 'uuid', 'different:firstPersonId'],
        ]);

        $this->result = $calculator->between($values['firstPersonId'], $values['secondPersonId']);
    }

    public function render(): mixed
    {
        return view('genealogy-relationships-livewire::calculator');
    }
}
