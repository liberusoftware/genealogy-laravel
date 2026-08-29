<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Liberu\Genealogy\Collaboration\Filament\Resources\CollaborationProposalResource;

final class ListCollaborationProposals extends ListRecords
{
    protected static string $resource = CollaborationProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
