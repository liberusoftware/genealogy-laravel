<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource;

final class EditCollaborationDiscussion extends EditRecord
{
    protected static string $resource = CollaborationDiscussionResource::class;
}
