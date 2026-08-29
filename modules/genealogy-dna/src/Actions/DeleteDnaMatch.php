<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaMatch;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDnaMatch
{
    public function execute(DnaMatch $match): void
    {
        if ((string) $match->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA match must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $match->delete());
    }
}
