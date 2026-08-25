<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource;

final class EditCollaborationSpace extends EditRecord
{
    protected static string $resource = CollaborationSpaceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
