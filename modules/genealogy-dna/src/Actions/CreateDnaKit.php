<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Dna\Models\DnaKit;

final class CreateDnaKit
{
    public function execute(array $attributes): DnaKit
    {
        return DnaKit::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
