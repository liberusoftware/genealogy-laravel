<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\EditEvidenceEntityRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource;

final class EditRepository extends EditEvidenceEntityRecord
{
    protected static string $resource = RepositoryResource::class;
}
