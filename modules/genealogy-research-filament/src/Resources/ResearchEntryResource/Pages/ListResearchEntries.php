<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource;

final class ListResearchEntries extends ListRecords
{
    protected static string $resource = ResearchEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
