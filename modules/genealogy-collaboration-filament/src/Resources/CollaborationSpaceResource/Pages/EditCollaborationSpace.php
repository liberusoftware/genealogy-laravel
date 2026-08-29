<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationSpace;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationSpace;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationSpaceResource;

final class EditCollaborationSpace extends EditRecord
{
    protected static string $resource = CollaborationSpaceResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateCollaborationSpace::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteCollaborationSpace::class)->execute($record))];
    }
}
