<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Events\ResearchProjectUpdated;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class UpdateResearchProject
{
    /** @param array<string, mixed> $attributes */
    public function execute(ResearchProject $project, array $attributes): ResearchProject
    {
        if ((string) $project->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The research project must belong to the active team.');
        }
        $values = Arr::only($attributes, ['name', 'status', 'metadata']);
        DB::transaction(function () use ($project, $values): void {
            $project->update($values);
        });
        event(new ResearchProjectUpdated($project->refresh()));

        return $project;
    }
}
