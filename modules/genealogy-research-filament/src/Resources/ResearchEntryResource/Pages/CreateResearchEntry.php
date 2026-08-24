<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Research\Filament\Resources\ResearchEntryResource;

final class CreateResearchEntry extends CreateRecord
{
    protected static string $resource = ResearchEntryResource::class;
}
