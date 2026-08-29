<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaSegment;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDnaSegment
{
    public function execute(DnaSegment $segment): void
    {
        if ((string) $segment->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA segment must belong to the active team.');
        }

        DB::transaction(fn (): mixed => $segment->delete());
    }
}
