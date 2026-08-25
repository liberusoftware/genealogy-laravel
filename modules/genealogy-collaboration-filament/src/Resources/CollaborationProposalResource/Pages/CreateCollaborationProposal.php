<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource;

final class CreateCollaborationProposal extends CreateRecord
{
    protected static string $resource = CollaborationProposalResource::class;
}
