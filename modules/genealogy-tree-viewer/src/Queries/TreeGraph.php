<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Queries;

use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

/**
 * Builds a bounded, privacy-safe graph for a tree view.
 *
 * Relationship records use the canonical directed convention: for `parent`,
 * person_id is the parent and related_person_id is the child. The traversal
 * is deliberately iterative and keeps a visited set so malformed imported
 * data cannot recurse forever or duplicate people in a rendered tree.
 */
final class TreeGraph
{
    /** @return array<string, mixed> */
    public function for(Person $root, int $generations = 3, bool $includeLiving = true): array
    {
        $generations = max(0, min($generations, 12));

        return [
            'root' => $this->person($root),
            'ancestors' => $this->walk($root, $generations, ancestors: true, includeLiving: $includeLiving),
            'descendants' => $this->walk($root, $generations, ancestors: false, includeLiving: $includeLiving),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function walk(Person $root, int $generations, bool $ancestors, bool $includeLiving): array
    {
        if ($generations === 0) {
            return [];
        }

        $visited = [(string) $root->getKey() => true];
        $frontier = collect([$root]);
        $result = [];

        for ($depth = 1; $depth <= $generations && $frontier->isNotEmpty(); $depth++) {
            $ids = $frontier->map(fn (Person $person): string => (string) $person->getKey());
            $query = Relationship::query()->where('type', 'parent');

            $edges = $ancestors
                ? $query->whereIn('related_person_id', $ids)->get()
                : $query->whereIn('person_id', $ids)->get();

            $next = collect();

            foreach ($edges as $edge) {
                $personId = $ancestors ? $edge->person_id : $edge->related_person_id;
                $person = Person::query()->find($personId);

                if (! $person || (! $includeLiving && $person->isLiving()) || isset($visited[(string) $person->getKey()])) {
                    continue;
                }

                $visited[(string) $person->getKey()] = true;
                $next->push($person);
                $result[] = [
                    'person' => $this->person($person),
                    'generation' => $depth,
                    'relationship_id' => (string) $edge->getKey(),
                    'confidence' => (int) $edge->confidence,
                ];
            }

            $frontier = $next;
        }

        return $result;
    }

    /** @return array<string, mixed> */
    private function person(Person $person): array
    {
        return [
            'id' => (string) $person->getKey(),
            'name' => $person->display_name,
            'given_name' => $person->given_name,
            'family_name' => $person->family_name,
            'birth_date' => $person->birth_date?->toDateString(),
            'death_date' => $person->death_date?->toDateString(),
            'is_living' => $person->isLiving(),
        ];
    }
}
