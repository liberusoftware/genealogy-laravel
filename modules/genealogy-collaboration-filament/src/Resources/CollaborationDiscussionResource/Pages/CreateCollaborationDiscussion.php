<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationDiscussion as CreateDiscussion;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource;

final class CreateCollaborationDiscussion extends CreateRecord
{
    protected static string $resource = CollaborationDiscussionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateDiscussion::class)->execute($data + ['author_id' => auth()->id()]);
    }
}
