<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource;

final class ListResearchProjects extends ListRecords
{
    protected static string $resource = ResearchProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
