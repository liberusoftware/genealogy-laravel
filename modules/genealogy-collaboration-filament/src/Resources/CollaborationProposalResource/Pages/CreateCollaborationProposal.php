<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Collaboration\Actions\CreateCollaborationProposal as CreateProposal;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource;

final class CreateCollaborationProposal extends CreateRecord
{
    protected static string $resource = CollaborationProposalResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateProposal::class)->execute($data + ['proposer_id' => auth()->id()]);
    }
}
