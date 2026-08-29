<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaNote;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class DeleteDnaNote
{
    public function execute(DnaNote $note): void
    {
        if ((string) $note->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA note must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $note->delete());
    }
}
