<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Queries;

use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Relationships\Models\Relationship;

/**
 * Describes the family relationship between two people using parent edges.
 *
 * Parent relationships point from parent to child. The traversal is bounded
 * and keeps the nearest distance for each ancestor, so malformed or cyclic
 * imported data cannot make a request run forever.
 */
final class RelationshipCalculator
{
    /**
     * @return array{
     *     relationship: string,
     *     common_ancestor_id: string|null,
     *     generations_from_first: int|null,
     *     generations_from_second: int|null
     * }
     */
    public function between(string $firstPersonId, string $secondPersonId, ?string $teamId = null): array
    {
        $teamId ??= app(TeamContext::class)->require();

        if ($firstPersonId === $secondPersonId) {
            return $this->result('self', $firstPersonId, 0, 0);
        }

        $firstAncestors = $this->ancestorDistances($firstPersonId, $teamId);
        $secondAncestors = $this->ancestorDistances($secondPersonId, $teamId);
        $common = array_intersect_key($firstAncestors, $secondAncestors);

        if ($common === []) {
            return $this->result('no traceable relationship', null, null, null);
        }

        $commonAncestorId = null;
        $firstDistance = null;
        $secondDistance = null;
        $distanceSum = PHP_INT_MAX;

        foreach ($common as $ancestorId => $distance) {
            $otherDistance = $secondAncestors[$ancestorId];
            if ($distance + $otherDistance < $distanceSum) {
                $commonAncestorId = (string) $ancestorId;
                $firstDistance = $distance;
                $secondDistance = $otherDistance;
                $distanceSum = $distance + $otherDistance;
            }
        }

        return $this->result(
            $this->label($firstDistance, $secondDistance),
            $commonAncestorId,
            $firstDistance,
            $secondDistance,
        );
    }

    /** @return array<string, int> */
    private function ancestorDistances(string $personId, string $teamId): array
    {
        $distances = [$personId => 0];
        $frontier = [$personId];
        $maxGenerations = 50;

        for ($depth = 0; $frontier !== [] && $depth < $maxGenerations; $depth++) {
            $parents = Relationship::query()
                ->forTeam($teamId)
                ->where('type', 'parent')
                ->whereIn('related_person_id', $frontier)
                ->get(['person_id', 'related_person_id']);

            $next = [];
            foreach ($parents as $parent) {
                $parentId = (string) $parent->person_id;
                if (array_key_exists($parentId, $distances)) {
                    continue;
                }

                $distances[$parentId] = $depth + 1;
                $next[] = $parentId;
            }

            $frontier = $next;
        }

        return $distances;
    }

    private function label(int $firstDistance, int $secondDistance): string
    {
        $minimum = min($firstDistance, $secondDistance);
        $difference = abs($firstDistance - $secondDistance);

        if ($minimum === 0) {
            if ($difference === 0) {
                return 'self';
            }

            return $firstDistance === 0
                ? $this->directLabel($difference, 'parent')
                : $this->directLabel($difference, 'child');
        }

        if ($minimum === 1) {
            if ($difference === 0) {
                return 'sibling';
            }

            return $firstDistance < $secondDistance ? 'aunt/uncle' : 'niece/nephew';
        }

        $label = $this->ordinal($minimum - 1).' cousin';

        return $difference === 0 ? $label : $label.' '.$this->removed($difference);
    }

    private function directLabel(int $generations, string $direction): string
    {
        if ($generations === 1) {
            return $direction;
        }

        return str_repeat('great-', max(0, $generations - 2)).'grand'.$direction;
    }

    private function ordinal(int $number): string
    {
        $suffix = match (true) {
            in_array($number % 100, [11, 12, 13], true) => 'th',
            $number % 10 === 1 => 'st',
            $number % 10 === 2 => 'nd',
            $number % 10 === 3 => 'rd',
            default => 'th',
        };

        return $number.$suffix;
    }

    private function removed(int $generations): string
    {
        return match ($generations) {
            1 => 'once removed',
            2 => 'twice removed',
            default => $generations.' times removed',
        };
    }

    /** @return array{relationship: string, common_ancestor_id: string|null, generations_from_first: int|null, generations_from_second: int|null} */
    private function result(string $relationship, ?string $ancestorId, ?int $firstDistance, ?int $secondDistance): array
    {
        return [
            'relationship' => $relationship,
            'common_ancestor_id' => $ancestorId,
            'generations_from_first' => $firstDistance,
            'generations_from_second' => $secondDistance,
        ];
    }
}
