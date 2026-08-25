<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource;

final class CreateCollaborationSpace extends CreateRecord
{
    protected static string $resource = CollaborationSpaceResource::class;
}
