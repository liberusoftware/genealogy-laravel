<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Events\ResearchEntryCreated;
use Liberu\Genealogy\Research\Models\ResearchEntry;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class CreateResearchEntry
{
    /** @param array<string, mixed> $attributes */
    public function execute(array $attributes): ResearchEntry
    {
        $values = Arr::only($attributes, ['research_project_id', 'kind', 'title', 'body', 'status', 'due_date', 'completed_at', 'metadata']);

        $this->validate($values);
        $values['team_id'] = app(TeamContext::class)->require();

        $entry = DB::transaction(fn (): ResearchEntry => ResearchEntry::query()->create($values));
        event(new ResearchEntryCreated($entry));

        return $entry;
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (! in_array($values['kind'] ?? null, ResearchEntry::KINDS, true)) {
            throw new InvalidArgumentException('The research entry kind is not supported.');
        }
        if (isset($values['status']) && ! in_array($values['status'], ResearchEntry::STATUSES, true)) {
            throw new InvalidArgumentException('The research entry status is not supported.');
        }
        if (! ResearchProject::query()->whereKey($values['research_project_id'] ?? null)->exists()) {
            throw new InvalidArgumentException('The research project must belong to the active team.');
        }
    }
}
