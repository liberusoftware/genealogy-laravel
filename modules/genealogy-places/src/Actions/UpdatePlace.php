<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Places\Events\PlaceUpdated;
use Liberu\Genealogy\Places\Models\Place;

final class UpdatePlace
{
    /** @param array<string, mixed> $attributes */
    public function execute(Place $place, array $attributes): Place
    {
        if ((string) $place->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The place must belong to the active team.');
        }
        $values = Arr::only($attributes, ['name', 'parent_id', 'historical_names', 'latitude', 'longitude', 'jurisdiction', 'is_current', 'status', 'metadata']);
        $parentId = $values['parent_id'] ?? $place->parent_id;
        $this->assertParentChainIsValid($place, $parentId);
        (new CreatePlace())->validate(array_merge($place->toArray(), $values));
        $place->getConnection()->transaction(function () use ($place, $values): void {
            $place->update($values);
        });

        $place = $place->refresh();
        if (app()->bound('events')) {
            event(new PlaceUpdated($place));
        }

        return $place;
    }

    private function assertParentChainIsValid(Place $place, mixed $parentId): void
    {
        $visited = [(string) $place->getKey() => true];
        $depth = 0;

        while ($parentId !== null && $parentId !== '') {
            $parentKey = (string) $parentId;
            if (isset($visited[$parentKey])) {
                throw new InvalidArgumentException('A place hierarchy cannot contain a cycle.');
            }

            $visited[$parentKey] = true;
            $parent = Place::query()->find($parentKey);
            if ($parent === null) {
                return;
            }

            $parentId = $parent->parent_id;
            if (++$depth > 100) {
                throw new InvalidArgumentException('A place hierarchy exceeds the supported depth.');
            }
        }
    }
}
