<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Events\ResearchEntryDeleted;
use Liberu\Genealogy\Research\Models\ResearchEntry;

final class DeleteResearchEntry
{
    public function execute(ResearchEntry $entry): void
    {
        if ((string) $entry->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The research entry must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $entry->delete());
        event(new ResearchEntryDeleted($entry));
    }
}
