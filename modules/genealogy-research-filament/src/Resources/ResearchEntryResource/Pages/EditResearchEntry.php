<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource;

final class EditResearchEntry extends EditRecord
{
    protected static string $resource = ResearchEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
