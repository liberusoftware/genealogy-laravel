<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\Media\Models\MediaLink;

final class CreateMediaLink
{
    public function execute(array $attributes): MediaLink
    {
        return MediaLink::query()->firstOrCreate(
            Arr::only($attributes, ['media_asset_id', 'linkable_type', 'linkable_id', 'role']),
            ['metadata' => $attributes['metadata'] ?? null],
        );
    }
}
