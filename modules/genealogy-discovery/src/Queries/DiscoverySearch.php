<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Queries;

use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Places\Models\Place;

final class DiscoverySearch
{
    /** @return array{people: list<array<string, mixed>>, places: list<array<string, mixed>>, sources: list<array<string, mixed>>} */
    public function execute(string $term, array $options = []): array
    {
        $term = trim($term);
        if ($term === '') {
            return ['people' => [], 'places' => [], 'sources' => []];
        }

        $limit = min(max((int) ($options['limit'] ?? 25), 1), 100);
        $publicOnly = (bool) ($options['public_only'] ?? false);
        $includeLiving = (bool) ($options['include_living'] ?? true);
        $types = $options['types'] ?? ['people', 'places', 'sources'];

        $people = in_array('people', $types, true)
            ? Person::query()->where(function ($query) use ($term): void {
                $like = '%'.$term.'%';
                $query->where('given_name', 'like', $like)->orWhere('family_name', 'like', $like)->orWhere('display_name', 'like', $like)->orWhereJsonContains('aliases', $term);
            })->when($publicOnly, fn ($query) => $query->where('is_public', true))->when(! $includeLiving, fn ($query) => $query->deceased())->limit($limit)->get()
            : collect();

        $places = in_array('places', $types, true)
            ? Place::query()->where(function ($query) use ($term): void {
                $like = '%'.$term.'%';
                $query->where('name', 'like', $like)->orWhere('jurisdiction', 'like', $like)->orWhereJsonContains('historical_names', $term);
            })->limit($limit)->get()
            : collect();

        $sources = in_array('sources', $types, true)
            ? EvidenceRecord::query()->where(function ($query) use ($term): void {
                $like = '%'.$term.'%';
                $query->where('name', 'like', $like)->orWhere('repository', 'like', $like)->orWhere('citation', 'like', $like)->orWhere('extract', 'like', $like);
            })->limit($limit)->get()
            : collect();

        return [
            'people' => $people->map(fn (Person $person): array => ['id' => $person->getKey(), 'type' => 'person', 'name' => $person->fullName(), 'is_public' => $person->is_public, 'is_living' => $person->isLiving()])->values()->all(),
            'places' => $places->map(fn (Place $place): array => ['id' => $place->getKey(), 'type' => 'place', 'name' => $place->name, 'jurisdiction' => $place->jurisdiction])->values()->all(),
            'sources' => $sources->map(fn (EvidenceRecord $source): array => ['id' => $source->getKey(), 'type' => 'source', 'name' => $source->name, 'kind' => $source->kind, 'citation' => $source->citation])->values()->all(),
        ];
    }
}
