<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\SourceResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\CreateEvidenceEntityRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource;

final class CreateSource extends CreateEvidenceEntityRecord
{
    protected static string $resource = SourceResource::class;
}
