<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Liberu\Genealogy\Places\Models\Place;

final class CreatePlace
{
    public function execute(array $attributes): Place
    {
        $values = Arr::only($attributes, ['name', 'parent_id', 'historical_names', 'latitude', 'longitude', 'jurisdiction', 'is_current', 'status', 'metadata']);
        $this->validate($values);

        return Place::query()->create($values);
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (($values['parent_id'] ?? null) !== null && ! Place::query()->whereKey($values['parent_id'])->exists()) {
            throw new InvalidArgumentException('The parent place must belong to the active team.');
        }
        if (($values['latitude'] ?? null) !== null && ((float) $values['latitude'] < -90 || (float) $values['latitude'] > 90)) {
            throw new InvalidArgumentException('Latitude must be between -90 and 90.');
        }
        if (($values['longitude'] ?? null) !== null && ((float) $values['longitude'] < -180 || (float) $values['longitude'] > 180)) {
            throw new InvalidArgumentException('Longitude must be between -180 and 180.');
        }

    }
}
