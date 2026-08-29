<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\SourceResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\ListEvidenceEntities;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource;

final class ListSources extends ListEvidenceEntities
{
    protected static string $resource = SourceResource::class;
}
