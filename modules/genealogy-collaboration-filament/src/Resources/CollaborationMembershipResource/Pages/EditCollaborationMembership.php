<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationMembershipResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\SetCollaborationMembershipRole;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationMembershipResource;

final class EditCollaborationMembership extends EditRecord
{
    protected static string $resource = CollaborationMembershipResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(SetCollaborationMembershipRole::class)->execute($record, (string) $data['role']);
    }
}
