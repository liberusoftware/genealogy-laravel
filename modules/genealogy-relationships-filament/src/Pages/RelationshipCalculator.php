<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Filament\Pages;

use Filament\Pages\Page;
use Liberu\Genealogy\Relationships\Queries\RelationshipCalculator as RelationshipCalculatorQuery;

final class RelationshipCalculator extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Genealogy';

    protected static ?string $navigationLabel = 'Relationship Calculator';

    protected static ?string $title = 'Relationship Calculator';

    protected string $view = 'genealogy-relationships-filament::relationship-calculator';

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
}
