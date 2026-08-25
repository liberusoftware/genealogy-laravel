<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Queries;

use Liberu\Genealogy\GenealogyCore\Contracts\ParentGraphReader;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class ParentGraph implements ParentGraphReader
{
    public function relatedPeople(string $teamId, array $personIds, bool $ancestors): array
    {
        $column = $ancestors ? 'related_person_id' : 'person_id';
        $nextColumn = $ancestors ? 'person_id' : 'related_person_id';

        return Relationship::query()
            ->forTeam($teamId)
            ->where('type', 'parent')
            ->whereIn($column, $personIds)
            ->pluck($nextColumn)
            ->map(fn (mixed $id): string => (string) $id)
            ->all();
    }
}
