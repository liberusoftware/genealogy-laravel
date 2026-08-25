<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationDiscussion;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource;

final class EditCollaborationDiscussion extends EditRecord
{
    protected static string $resource = CollaborationDiscussionResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateCollaborationDiscussion::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteCollaborationDiscussion::class)->execute($record))];
    }
}
