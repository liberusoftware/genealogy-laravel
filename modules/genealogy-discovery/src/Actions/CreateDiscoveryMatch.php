<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;

final class CreateDiscoveryMatch
{
    public function execute(array $attributes): DiscoveryMatch
    {
        return DiscoveryMatch::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
