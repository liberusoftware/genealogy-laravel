<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Relationships\Queries;

use InvalidArgumentException;
use Liberu\Genealogy\Relationships\Models\Relationship;

/**
 * Validates relationship edges before they are committed.
 *
 * Parent edges are directed from parent to child. A new edge is invalid when
 * following existing parent edges from its child can reach its parent.
 */
final class GraphValidator
{
    /** @return array{valid: bool, reason: string|null} */
    public function validate(string $personId, string $relatedPersonId, string $type, ?string $ignoreRelationshipId = null): array
    {
        if ($personId === $relatedPersonId) {
            return ['valid' => false, 'reason' => 'A relationship must connect two different people.'];
        }

        if (! in_array($type, Relationship::TYPES, true)) {
            return ['valid' => false, 'reason' => 'The relationship type is not supported.'];
        }

        if (Relationship::query()
            ->where('person_id', $personId)
            ->where('related_person_id', $relatedPersonId)
            ->where('type', $type)
            ->when($ignoreRelationshipId !== null, fn ($query) => $query->where($query->getModel()->getQualifiedKeyName(), '<>', $ignoreRelationshipId))
            ->exists()) {
            return ['valid' => false, 'reason' => 'This relationship already exists.'];
        }

        if ($type === 'parent' && $this->wouldCreateParentCycle($personId, $relatedPersonId)) {
            return ['valid' => false, 'reason' => 'The parent relationship would create a cycle.'];
        }

        return ['valid' => true, 'reason' => null];
    }

    public function assertValid(string $personId, string $relatedPersonId, string $type, ?string $ignoreRelationshipId = null): void
    {
        $result = $this->validate($personId, $relatedPersonId, $type, $ignoreRelationshipId);

        if (! $result['valid']) {
            throw new InvalidArgumentException($result['reason'] ?? 'The relationship is invalid.');
        }
    }

    private function wouldCreateParentCycle(string $parentId, string $childId): bool
    {
        $visited = [];
        $frontier = [$childId];

        while ($frontier !== []) {
            $current = array_shift($frontier);

            if ($current === $parentId) {
                return true;
            }

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            $children = Relationship::query()
                ->where('type', 'parent')
                ->where('person_id', $current)
                ->pluck('related_person_id');

            foreach ($children as $next) {
                $frontier[] = (string) $next;
            }
        }

        return false;
    }
}
