<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Research\Models\ResearchEntry;

final class CreateResearchEntry
{
    /** @param array<string, mixed> $attributes */
    public function execute(array $attributes): ResearchEntry
    {
        $values = Arr::only($attributes, ['research_project_id', 'kind', 'title', 'body', 'status', 'due_date', 'completed_at', 'metadata']);

        if (! in_array($values['kind'] ?? null, ResearchEntry::KINDS, true)) {
            throw new InvalidArgumentException('The research entry kind is not supported.');
        }

        return ResearchEntry::query()->create($values);
    }
}
