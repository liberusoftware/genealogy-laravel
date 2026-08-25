<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Queries;

use Liberu\Genealogy\GenealogyCore\Contracts\ParentGraphReader;
use Liberu\Genealogy\GenealogyCore\Models\Tree;

/**
 * Computes bounded tree statistics without coupling core to the relationships
 * module. Parent edges are stored as parent -> child.
 *
 * @return array{total_people: int, total_ancestors: int, total_descendants: int, total_generations: int}
 */
final class TreeStatistics
{
    public function __construct(private readonly ?ParentGraphReader $graph = null) {}

    public function for(Tree $tree): array
    {
        $empty = [
            'total_people' => 0,
            'total_ancestors' => 0,
            'total_descendants' => 0,
            'total_generations' => 0,
        ];

        if ($tree->root_person_id === null || $this->graph === null) {
            return $empty;
        }

        $teamId = (string) $tree->team_id;
        $rootId = (string) $tree->root_person_id;
        $ancestors = $this->walk($teamId, [$rootId], ancestors: true);
        $descendants = $this->walk($teamId, [$rootId], ancestors: false);

        return [
            'total_people' => count(array_unique([$rootId, ...$ancestors, ...$descendants])),
            'total_ancestors' => count($ancestors),
            'total_descendants' => count($descendants),
            'total_generations' => max($this->depth($teamId, $rootId, ancestors: true), $this->depth($teamId, $rootId, ancestors: false)),
        ];
    }

    /** @param list<string> $roots @return list<string> */
    private function walk(string $teamId, array $roots, bool $ancestors): array
    {
        $visited = array_fill_keys($roots, true);
        $frontier = $roots;
        $result = [];

        while ($frontier !== []) {
            $next = $this->graph->relatedPeople($teamId, $frontier, $ancestors);
            $frontier = [];

            foreach ($next as $id) {
                if (isset($visited[$id])) {
                    continue;
                }
                $visited[$id] = true;
                $result[] = $id;
                $frontier[] = $id;
            }
        }

        return $result;
    }

    private function depth(string $teamId, string $rootId, bool $ancestors): int
    {
        $frontier = [$rootId];
        $visited = [$rootId => true];
        $depth = 0;

        while ($frontier !== []) {
            $next = $this->graph->relatedPeople($teamId, $frontier, $ancestors);
            $frontier = [];

            foreach ($next as $id) {
                if (isset($visited[$id])) {
                    continue;
                }
                $visited[$id] = true;
                $frontier[] = $id;
            }

            if ($frontier !== []) {
                $depth++;
            }
        }

        return $depth;
    }
}
