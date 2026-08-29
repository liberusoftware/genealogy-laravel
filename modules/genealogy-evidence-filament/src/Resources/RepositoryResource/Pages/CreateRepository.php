<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\CreateEvidenceEntityRecord;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource;

final class CreateRepository extends CreateEvidenceEntityRecord
{
    protected static string $resource = RepositoryResource::class;
}
