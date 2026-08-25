<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationDiscussionResource;

final class ListCollaborationDiscussions extends ListRecords
{
    protected static string $resource = CollaborationDiscussionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
