<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Filament\Resources;

use Liberu\Genealogy\Evidence\Models\Repository;

final class RepositoryResource extends EvidenceEntityResource
{
    protected static ?string $model = Repository::class;

    protected static ?string $navigationLabel = 'Repositories';
}
