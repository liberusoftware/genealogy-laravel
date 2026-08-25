<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource;

final class EditCollaborationProposal extends EditRecord
{
    protected static string $resource = CollaborationProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
