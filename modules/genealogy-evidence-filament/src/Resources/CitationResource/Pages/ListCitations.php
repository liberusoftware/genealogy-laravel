<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\CitationResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource;
use Liberu\Genealogy\Evidence\Filament\Resources\Pages\ListEvidenceEntities;

final class ListCitations extends ListEvidenceEntities
{
    protected static string $resource = CitationResource::class;
}
