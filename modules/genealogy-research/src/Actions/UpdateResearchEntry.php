<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Events\ResearchEntryUpdated;
use Liberu\Genealogy\Research\Models\ResearchEntry;

final class UpdateResearchEntry
{
    /** @param array<string, mixed> $attributes */
    public function execute(ResearchEntry $entry, array $attributes): ResearchEntry
    {
        if ((string) $entry->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The research entry must belong to the active team.');
        }
        $values = Arr::only($attributes, ['kind', 'title', 'body', 'status', 'due_date', 'completed_at', 'metadata']);
        (new CreateResearchEntry())->validate(array_merge($entry->toArray(), $values));
        DB::transaction(function () use ($entry, $values): void {
            $entry->update($values);
        });
        event(new ResearchEntryUpdated($entry->refresh()));

        return $entry;
    }
}
