<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\InviteCollaborationMember;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource;

final class CreateCollaborationInvitation extends CreateRecord
{
    protected static string $resource = CollaborationInvitationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(InviteCollaborationMember::class)->execute($data);
    }
}
