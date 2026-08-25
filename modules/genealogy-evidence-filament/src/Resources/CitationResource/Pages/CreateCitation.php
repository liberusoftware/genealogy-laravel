<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\CitationResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource;
use Liberu\Genealogy\Evidence\Filament\Resources\Pages\CreateEvidenceEntityRecord;

final class CreateCitation extends CreateEvidenceEntityRecord
{
    protected static string $resource = CitationResource::class;
}
