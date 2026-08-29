<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Collaboration\Models\CollaborationSpace;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteCollaborationSpace
{
    public function execute(CollaborationSpace $space): void
    {
        if ((string) $space->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The collaboration space must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $space->delete());
    }
}
