<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaRelationship;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDnaRelationship
{
    public function execute(DnaRelationship $relationship): void
    {
        if ((string) $relationship->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA relationship must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $relationship->delete());
    }
}
