<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Events\ResearchProjectDeleted;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class DeleteResearchProject
{
    public function execute(ResearchProject $project): void
    {
        if ((string) $project->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The research project must belong to the active team.');
        }
        DB::transaction(fn (): mixed => $project->delete());
        event(new ResearchProjectDeleted($project));
    }
}
