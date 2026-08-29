<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource;

final class ListCollaborationInvitations extends ListRecords
{
    protected static string $resource = CollaborationInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
