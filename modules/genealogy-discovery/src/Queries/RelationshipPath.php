<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Queries;

use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

final class RelationshipPath
{
    /** @return array{found: bool, nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>, depth: int|null} */
    public function execute(string $from, string $to, int $maxDepth = 6, bool $publicOnly = false): array
    {
        $maxDepth = min(max($maxDepth, 1), 12);
        $visible = Person::query()->when($publicOnly, fn ($query) => $query->where('is_public', true)->deceased())->whereIn('id', [$from, $to])->pluck('id');
        if ($visible->count() !== 2) {
            return ['found' => false, 'nodes' => [], 'edges' => [], 'depth' => null];
        }

        $queue = [[$from, []]];
        $visited = [$from => true];
        $path = null;
        while ($queue !== []) {
            [$current, $edges] = array_shift($queue);
            if ($current === $to) {
                $path = $edges;
                break;
            }
            if (count($edges) >= $maxDepth) {
                continue;
            }
            $relations = Relationship::query()->where(fn ($query) => $query->where('person_id', $current)->orWhere('related_person_id', $current))->get();
            foreach ($relations as $relationship) {
                $next = (string) ($relationship->person_id === $current ? $relationship->related_person_id : $relationship->person_id);
                if (isset($visited[$next])) {
                    continue;
                }
                $nextPerson = Person::query()->find($next);
                if ($nextPerson === null || ($publicOnly && (! $nextPerson->is_public || $nextPerson->isLiving()))) {
                    continue;
                }
                $visited[$next] = true;
                $queue[] = [$next, [...$edges, ['from' => $current, 'to' => $next, 'type' => $relationship->type, 'confidence' => $relationship->confidence]]];
            }
        }
        if ($path === null) {
            return ['found' => false, 'nodes' => [], 'edges' => [], 'depth' => null];
        }

        $ids = collect([$from, ...array_column($path, 'to')])->unique()->values();
        $people = Person::query()->whereIn('id', $ids)->get()->keyBy(fn (Person $person): string => (string) $person->getKey());

        return ['found' => true, 'nodes' => $ids->map(fn (string $id): array => ['id' => $id, 'name' => $people->get($id)?->fullName()])->all(), 'edges' => $path, 'depth' => count($path)];
    }
}
