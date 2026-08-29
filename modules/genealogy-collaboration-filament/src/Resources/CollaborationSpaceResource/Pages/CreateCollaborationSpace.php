<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationSpace as CreateSpace;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource;

final class CreateCollaborationSpace extends CreateRecord
{
    protected static string $resource = CollaborationSpaceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateSpace::class)->execute($data);
    }
}
