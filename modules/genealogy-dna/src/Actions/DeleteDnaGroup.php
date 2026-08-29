<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaGroup;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDnaGroup
{
    public function execute(DnaGroup $group): void
    {
        if ((string) $group->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA group must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $group->delete());
    }
}
