<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource;

final class EditResearchProject extends EditRecord
{
    protected static string $resource = ResearchProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
