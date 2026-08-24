<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource;

final class ListCollaborationSpaces extends ListRecords
{
    protected static string $resource = CollaborationSpaceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
