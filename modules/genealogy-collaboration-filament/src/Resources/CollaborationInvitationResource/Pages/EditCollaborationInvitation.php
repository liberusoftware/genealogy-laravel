<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationInvitationResource;

final class EditCollaborationInvitation extends EditRecord
{
    protected static string $resource = CollaborationInvitationResource::class;
}
