<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

abstract class ListEvidenceEntities extends ListRecords
{
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
