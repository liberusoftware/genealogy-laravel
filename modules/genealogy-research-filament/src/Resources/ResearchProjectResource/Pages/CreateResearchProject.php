<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Research\Filament\Resources\ResearchProjectResource;

final class CreateResearchProject extends CreateRecord
{
    protected static string $resource = ResearchProjectResource::class;
}
