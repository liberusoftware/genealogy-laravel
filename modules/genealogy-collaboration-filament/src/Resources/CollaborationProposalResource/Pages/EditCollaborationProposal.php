<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\DeleteCollaborationProposal;
use Liberu\Genealogy\Collaboration\Actions\UpdateCollaborationProposal;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource;

final class EditCollaborationProposal extends EditRecord
{
    protected static string $resource = CollaborationProposalResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateCollaborationProposal::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->action(fn (Model $record): mixed => app(DeleteCollaborationProposal::class)->execute($record))];
    }
}
