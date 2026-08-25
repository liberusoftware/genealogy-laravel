<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Dna\Models\DnaGroup;

final class CreateDnaGroup
{
    public function execute(array $attributes): DnaGroup
    {
        return DnaGroup::query()->create(Arr::only($attributes, ['name', 'description', 'status', 'metadata']));
    }
}
