<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Queries;

use Liberu\Genealogy\Places\Models\Place;

final class PlaceHierarchy
{
    /** @return list<array<string, mixed>> */
    public function execute(bool $flat = false): array
    {
        $places = Place::query()->with('names')->orderBy('name')->get();
        $children = $places->groupBy(fn (Place $place): string => (string) ($place->parent_id ?? 'root'));
        $visited = [];
        $tree = $this->children($children, 'root', $visited, 0);

        // A damaged import may contain an orphan or a cycle. Keep those
        // records visible as additional roots instead of silently dropping
        // them from the hierarchy projection.
        foreach ($places as $place) {
            if (! isset($visited[(string) $place->getKey()])) {
                $tree[] = $this->node($children, $place, $visited, 0);
            }
        }

        return $flat ? $this->flatten($tree) : $tree;
    }

    private function children($children, string $parent, array &$visited, int $depth): array
    {
        $result = [];
        foreach ($children->get($parent, collect()) as $place) {
            if (! isset($visited[(string) $place->getKey()])) {
                $result[] = $this->node($children, $place, $visited, $depth);
            }
        }

        return $result;
    }

    /** @return array<string, mixed> */
    private function node($children, Place $place, array &$visited, int $depth): array
    {
        $id = (string) $place->getKey();
        $visited[$id] = true;

        return [
            'id' => $id,
            'name' => $place->name,
            'parent_id' => $place->parent_id,
            'jurisdiction' => $place->jurisdiction,
            'latitude' => $place->latitude,
            'longitude' => $place->longitude,
            'has_coordinates' => $place->hasCoordinates(),
            'map_url' => $place->mapUrl(),
            'historical_names' => $place->historical_names,
            'names' => $place->names->map(fn ($name): array => ['id' => (string) $name->getKey(), 'name' => $name->name, 'type' => $name->type, 'locale' => $name->locale, 'valid_from' => $name->valid_from?->toDateString(), 'valid_to' => $name->valid_to?->toDateString()])->values()->all(),
            'depth' => $depth,
            'children' => $this->children($children, $id, $visited, $depth + 1),
        ];
    }

    private function flatten(array $tree): array
    {
        $flat = [];
        foreach ($tree as $place) {
            $children = $place['children'];
            unset($place['children']);
            $flat[] = $place;
            $flat = [...$flat, ...$this->flatten($children)];
        }

        return $flat;
    }
}
