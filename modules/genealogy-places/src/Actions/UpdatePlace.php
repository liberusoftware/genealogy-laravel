<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Places\Models\Place;

final class UpdatePlace
{
    /** @param array<string, mixed> $attributes */
    public function execute(Place $place, array $attributes): Place
    {
        $values = Arr::only($attributes, ['name', 'parent_id', 'historical_names', 'latitude', 'longitude', 'jurisdiction', 'is_current', 'status', 'metadata']);
        if (($values['parent_id'] ?? $place->parent_id) === $place->getKey()) {
            throw new InvalidArgumentException('A place cannot be its own parent.');
        }
        (new CreatePlace())->validate(array_merge($place->toArray(), $values));
        $place->update($values);

        return $place->refresh();
    }
}
