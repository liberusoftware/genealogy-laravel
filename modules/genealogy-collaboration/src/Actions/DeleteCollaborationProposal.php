<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationProposal;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteCollaborationProposal
{
    public function execute(CollaborationProposal $proposal): void
    {
        if ((string) $proposal->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The proposal must belong to the active team.');
        }

        $proposal->delete();
    }
}
