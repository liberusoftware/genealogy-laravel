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
    public function for(
        Person $root,
        int $generations = 3,
        bool $includeLiving = true,
        string $view = 'chart',
    ): array {
        $generations = max(0, min($generations, 12));
        $view = in_array($view, ['pedigree', 'descendants', 'fan', 'chart'], true) ? $view : 'chart';
        $ancestors = $view === 'descendants' ? [] : $this->walk($root, $generations, ancestors: true, includeLiving: $includeLiving);
        $descendants = $view === 'pedigree' ? [] : $this->walk($root, $generations, ancestors: false, includeLiving: $includeLiving);
        $nodes = $this->nodes($root, $ancestors, $descendants, $includeLiving);

        return [
            'view' => $view,
            'root' => $this->person($root, $includeLiving),
            'ancestors' => $ancestors,
            'descendants' => $descendants,
            'nodes' => $nodes,
            'edges' => $this->edges($ancestors, $descendants),
            'navigation' => [
                'root_person_id' => (string) $root->getKey(),
                'generations' => $generations,
                'available_views' => ['pedigree', 'descendants', 'fan', 'chart'],
                'can_expand' => $generations < 12,
            ],
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

        for ($depth = 1; $depth <= $generations && $frontier->isNotEmpty() && count($result) < 2000; $depth++) {
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
                    'from_person_id' => (string) $edge->person_id,
                    'to_person_id' => (string) $edge->related_person_id,
                ];
            }

            $frontier = $next;
        }

        return $result;
    }

    /** @param list<array<string, mixed>> $ancestors @param list<array<string, mixed>> $descendants */
    private function nodes(Person $root, array $ancestors, array $descendants, bool $includeLiving): array
    {
        $nodes = [(string) $root->getKey() => $this->person($root, $includeLiving)];

        foreach ([...$ancestors, ...$descendants] as $entry) {
            $person = $entry['person'];
            $nodes[$person['id']] = $person;
        }

        return array_values($nodes);
    }

    /** @param list<array<string, mixed>> $ancestors @param list<array<string, mixed>> $descendants */
    private function edges(array $ancestors, array $descendants): array
    {
        $edges = [];
        foreach ($ancestors as $entry) {
            $edges[] = [
                'from' => $entry['from_person_id'],
                'to' => $entry['to_person_id'],
                'relationship_id' => $entry['relationship_id'],
                'direction' => 'parent',
            ];
        }

        foreach ($descendants as $entry) {
            $edges[] = [
                'from' => $entry['from_person_id'],
                'to' => $entry['to_person_id'],
                'relationship_id' => $entry['relationship_id'],
                'direction' => 'child',
            ];
        }

        return $edges;
    }

    /** @return array<string, mixed> */
    private function person(Person $person, bool $includeLiving = true): array
    {
        if (! $includeLiving && $person->isLiving()) {
            return [
                'id' => (string) $person->getKey(),
                'name' => 'Living person',
                'given_name' => null,
                'family_name' => null,
                'birth_date' => null,
                'death_date' => null,
                'is_living' => true,
            ];
        }

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
