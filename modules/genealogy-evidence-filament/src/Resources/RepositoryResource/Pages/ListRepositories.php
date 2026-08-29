<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource\Pages;

use Liberu\Genealogy\Evidence\Filament\Resources\Pages\ListEvidenceEntities;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource;

final class ListRepositories extends ListEvidenceEntities
{
    protected static string $resource = RepositoryResource::class;
}
