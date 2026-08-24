<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Places\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Places\Models\Place;

final class CreatePlace
{
    public function execute(array $attributes): Place
    {
        return Place::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
