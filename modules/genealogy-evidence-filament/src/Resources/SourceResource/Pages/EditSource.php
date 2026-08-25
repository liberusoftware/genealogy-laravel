<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\SourceResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\EditEvidenceEntityRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource;

final class EditSource extends EditEvidenceEntityRecord
{
    protected static string $resource = SourceResource::class;
}
